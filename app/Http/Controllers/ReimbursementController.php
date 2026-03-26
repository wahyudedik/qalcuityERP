<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Reimbursement;
use App\Services\GlPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReimbursementController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = Reimbursement::with(['employee', 'requester', 'approver'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('category'))    $query->where('category', $request->category);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('number', 'like', "%$s%")
                ->orWhere('description', 'like', "%$s%"));
        }

        $reimbursements = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'submitted' => Reimbursement::where('tenant_id', $this->tid())->where('status', 'submitted')->count(),
            'approved'  => Reimbursement::where('tenant_id', $this->tid())->where('status', 'approved')->count(),
            'total_pending' => Reimbursement::where('tenant_id', $this->tid())->whereIn('status', ['submitted', 'approved'])->sum('amount'),
            'paid_month'    => Reimbursement::where('tenant_id', $this->tid())->where('status', 'paid')
                ->whereMonth('paid_at', now()->month)->sum('amount'),
        ];

        $employees = Employee::where('tenant_id', $this->tid())->where('status', 'active')->orderBy('name')->get();

        return view('reimbursement.index', compact('reimbursements', 'stats', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'  => 'required|exists:employees,id',
            'category'     => 'required|in:transport,meal,medical,office,travel,training,other',
            'description'  => 'required|string|max:255',
            'expense_date' => 'required|date',
            'amount'       => 'required|numeric|min:1000',
            'receipt_image'=> 'nullable|image|max:2048',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $imagePath = null;
        if ($request->hasFile('receipt_image')) {
            $imagePath = $request->file('receipt_image')->store('reimbursements', 'public');
        }

        Reimbursement::create([
            'tenant_id'     => $this->tid(),
            'number'        => Reimbursement::generateNumber($this->tid()),
            'employee_id'   => $data['employee_id'],
            'requested_by'  => auth()->id(),
            'category'      => $data['category'],
            'description'   => $data['description'],
            'expense_date'  => $data['expense_date'],
            'amount'        => $data['amount'],
            'receipt_image' => $imagePath,
            'status'        => 'submitted',
            'notes'         => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Reimbursement berhasil diajukan.');
    }

    public function approve(Reimbursement $reimbursement)
    {
        abort_if($reimbursement->tenant_id !== $this->tid(), 403);
        if ($reimbursement->status !== 'submitted') return back()->with('error', 'Hanya status submitted yang bisa di-approve.');

        $reimbursement->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Reimbursement di-approve.');
    }

    public function reject(Request $request, Reimbursement $reimbursement)
    {
        abort_if($reimbursement->tenant_id !== $this->tid(), 403);
        if ($reimbursement->status !== 'submitted') return back()->with('error', 'Hanya status submitted yang bisa di-reject.');

        $reimbursement->update([
            'status'        => 'rejected',
            'approved_by'   => auth()->id(),
            'approved_at'   => now(),
            'reject_reason' => $request->reason ?? 'Ditolak oleh admin',
        ]);

        return back()->with('success', 'Reimbursement ditolak.');
    }

    public function pay(Request $request, Reimbursement $reimbursement, GlPostingService $glService)
    {
        abort_if($reimbursement->tenant_id !== $this->tid(), 403);
        if ($reimbursement->status !== 'approved') return back()->with('error', 'Approve dulu sebelum bayar.');

        $data = $request->validate([
            'payment_method'    => 'required|in:cash,transfer',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($reimbursement, $data, $glService) {
            $reimbursement->update([
                'status'            => 'paid',
                'paid_by'           => auth()->id(),
                'paid_at'           => now(),
                'payment_method'    => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
            ]);

            // GL: Dr Beban Reimbursement (5208) / Cr Kas (1101) or Bank (1102)
            $amount = (float) $reimbursement->amount;
            $cashCode = $data['payment_method'] === 'cash' ? '1101' : '1102';
            $ref = $reimbursement->number;

            $glResult = $glService->postReimbursement(
                $this->tid(), auth()->id(), $ref, $reimbursement->id, $amount, $cashCode
            );

            if ($glResult->isSuccess()) {
                $reimbursement->update(['journal_entry_id' => $glResult->journal->id]);
            }
            if ($glResult->isFailed()) {
                session()->flash('gl_warning', $glResult->warningMessage());
            }
        });

        return back()->with('success', 'Reimbursement dibayar. Rp ' . number_format($reimbursement->amount, 0, ',', '.'));
    }

    public function destroy(Reimbursement $reimbursement)
    {
        abort_if($reimbursement->tenant_id !== $this->tid(), 403);
        if (!in_array($reimbursement->status, ['draft', 'submitted'])) {
            return back()->with('error', 'Hanya draft/submitted yang bisa dihapus.');
        }
        $reimbursement->delete();
        return back()->with('success', 'Reimbursement dihapus.');
    }

    // ── Self-Service: My Reimbursements ───────────────────────────

    public function myReimbursements()
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->where('tenant_id', $user->tenant_id)->first();

        $reimbursements = $employee
            ? Reimbursement::where('employee_id', $employee->id)->latest()->paginate(20)
            : collect()->paginate(20);

        return view('reimbursement.my', compact('reimbursements', 'employee'));
    }

    public function submitMy(Request $request)
    {
        $user = auth()->user();
        $employee = Employee::where('user_id', $user->id)->where('tenant_id', $user->tenant_id)->firstOrFail();

        $data = $request->validate([
            'category'     => 'required|in:transport,meal,medical,office,travel,training,other',
            'description'  => 'required|string|max:255',
            'expense_date' => 'required|date',
            'amount'       => 'required|numeric|min:1000',
            'receipt_image'=> 'nullable|image|max:2048',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $imagePath = null;
        if ($request->hasFile('receipt_image')) {
            $imagePath = $request->file('receipt_image')->store('reimbursements', 'public');
        }

        Reimbursement::create([
            'tenant_id'     => $user->tenant_id,
            'number'        => Reimbursement::generateNumber($user->tenant_id),
            'employee_id'   => $employee->id,
            'requested_by'  => $user->id,
            'category'      => $data['category'],
            'description'   => $data['description'],
            'expense_date'  => $data['expense_date'],
            'amount'        => $data['amount'],
            'receipt_image' => $imagePath,
            'status'        => 'submitted',
            'notes'         => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Pengajuan reimbursement berhasil dikirim.');
    }
}
