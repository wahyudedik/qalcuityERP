<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Models\ActivityLog;
use App\Models\ChartOfAccount;
use App\Models\ErpNotification;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payable;
use App\Models\User;
use App\Models\Writeoff;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WriteoffController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $writeoffs = Writeoff::where('tenant_id', $this->tid())
            ->with(['requester', 'approver'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(20)->withQueryString();

        return view('writeoffs.index', compact('writeoffs'));
    }

    public function create(Request $request)
    {
        $tid = $this->tid();
        $type = $request->get('type', 'receivable');

        if ($type === 'receivable') {
            $items = Invoice::where('tenant_id', $tid)
                ->whereIn('status', ['unpaid', 'partial'])
                ->with('customer')
                ->orderBy('due_date')
                ->get();
        } else {
            $items = Payable::where('tenant_id', $tid)
                ->whereIn('status', ['unpaid', 'partial'])
                ->with('supplier')
                ->orderBy('due_date')
                ->get();
        }

        return view('writeoffs.create', compact('type', 'items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:receivable,payable',
            'reference_id' => 'required|integer',
            'writeoff_amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
        ]);

        $tid = $this->tid();

        // Resolve reference
        if ($data['type'] === 'receivable') {
            $ref = Invoice::where('tenant_id', $tid)->findOrFail($data['reference_id']);
            $refNumber = $ref->number;
            $refType = Invoice::class;
            $remaining = (float) $ref->remaining_amount;
        } else {
            $ref = Payable::where('tenant_id', $tid)->findOrFail($data['reference_id']);
            $refNumber = $ref->number;
            $refType = Payable::class;
            $remaining = (float) $ref->remaining_amount;
        }

        if ($data['writeoff_amount'] > $remaining) {
            return back()->withErrors(['writeoff_amount' => "Jumlah write-off melebihi sisa ({$remaining})."]);
        }

        $writeoff = Writeoff::create([
            'tenant_id' => $tid,
            'requested_by' => auth()->id(),
            'number' => app(DocumentNumberService::class)->generate($tid, 'writeoff', 'WO'),
            'type' => $data['type'],
            'reference_type' => $refType,
            'reference_id' => $ref->id,
            'reference_number' => $refNumber,
            'original_amount' => $remaining,
            'writeoff_amount' => $data['writeoff_amount'],
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        // Notifikasi ke admin/manager
        $approvers = User::where('tenant_id', $tid)
            ->whereIn('role', ['admin', 'manager'])
            ->where('id', '!=', auth()->id())
            ->get();

        foreach ($approvers as $approver) {
            ErpNotification::create([
                'tenant_id' => $tid,
                'user_id' => $approver->id,
                'type' => 'writeoff_request',
                'title' => '📋 Permintaan Write-off '.$writeoff->typeLabel(),
                'body' => auth()->user()->name." mengajukan write-off {$writeoff->typeLabel()} {$refNumber} sebesar Rp ".number_format($data['writeoff_amount'], 0, ',', '.'),
                'data' => ['writeoff_id' => $writeoff->id],
            ]);
        }

        ActivityLog::record('writeoff_requested', "Write-off {$writeoff->number} diajukan", $writeoff);

        return redirect()->route('writeoffs.index')->with('success', "Permintaan write-off {$writeoff->number} berhasil diajukan dan menunggu persetujuan.");
    }

    public function approve(Request $request, Writeoff $writeoff)
    {
        abort_if($writeoff->tenant_id !== $this->tid(), 403);
        abort_if(! $writeoff->isPending(), 403, 'Hanya write-off pending yang bisa disetujui.');

        $writeoff->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        ErpNotification::create([
            'tenant_id' => $writeoff->tenant_id,
            'user_id' => $writeoff->requested_by,
            'type' => 'writeoff_approved',
            'title' => '✅ Write-off Disetujui',
            'body' => "Write-off {$writeoff->number} telah disetujui oleh ".auth()->user()->name.'.',
            'data' => ['writeoff_id' => $writeoff->id],
        ]);

        ActivityLog::record('writeoff_approved', "Write-off {$writeoff->number} disetujui", $writeoff);

        return back()->with('success', "Write-off {$writeoff->number} disetujui. Silakan posting jurnal.");
    }

    public function reject(Request $request, Writeoff $writeoff)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        abort_if($writeoff->tenant_id !== $this->tid(), 403);
        abort_if(! $writeoff->isPending(), 403);

        $writeoff->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'rejection_reason' => $request->reason,
        ]);

        ErpNotification::create([
            'tenant_id' => $writeoff->tenant_id,
            'user_id' => $writeoff->requested_by,
            'type' => 'writeoff_rejected',
            'title' => '❌ Write-off Ditolak',
            'body' => "Write-off {$writeoff->number} ditolak. Alasan: {$request->reason}",
            'data' => ['writeoff_id' => $writeoff->id],
        ]);

        ActivityLog::record('writeoff_rejected', "Write-off {$writeoff->number} ditolak", $writeoff);

        return back()->with('success', 'Write-off ditolak.');
    }

    public function post(Writeoff $writeoff)
    {
        abort_if($writeoff->tenant_id !== $this->tid(), 403);
        abort_if(! $writeoff->isApproved(), 403, 'Hanya write-off yang sudah disetujui yang bisa diposting.');

        DB::transaction(function () use ($writeoff) {
            $tid = $writeoff->tenant_id;
            $amount = (float) $writeoff->writeoff_amount;
            $date = today()->toDateString();

            // Resolve COA codes
            // Receivable write-off: Dr Bad Debt Expense (6101) / Cr Piutang Usaha (1103)
            // Payable write-off:    Dr Hutang Usaha (2101) / Cr Pendapatan Lain-lain (4201)
            if ($writeoff->type === 'receivable') {
                $debitCode = '6101'; // Bad Debt Expense
                $creditCode = '1103'; // Piutang Usaha
                $desc = "Write-off piutang {$writeoff->reference_number}: {$writeoff->reason}";
            } else {
                $debitCode = '2101'; // Hutang Usaha
                $creditCode = '4201'; // Pendapatan Lain-lain
                $desc = "Write-off hutang {$writeoff->reference_number}: {$writeoff->reason}";
            }

            $debitAccount = ChartOfAccount::where('tenant_id', $tid)->where('code', $debitCode)->first();
            $creditAccount = ChartOfAccount::where('tenant_id', $tid)->where('code', $creditCode)->first();

            if (! $debitAccount || ! $creditAccount) {
                throw new \RuntimeException('Akun COA untuk write-off tidak ditemukan. Pastikan COA default sudah dimuat.');
            }

            $period = AccountingPeriod::findForDate($tid, $date);

            $je = JournalEntry::create([
                'tenant_id' => $tid,
                'period_id' => $period?->id,
                'user_id' => auth()->id(),
                'number' => JournalEntry::generateNumber($tid, 'AUTO'),
                'date' => $date,
                'description' => $desc,
                'reference' => $writeoff->number,
                'reference_type' => 'writeoff',
                'reference_id' => $writeoff->id,
                'currency_code' => 'IDR',
                'currency_rate' => 1,
                'status' => 'draft',
            ]);

            JournalEntryLine::create(['journal_entry_id' => $je->id, 'account_id' => $debitAccount->id,  'debit' => $amount, 'credit' => 0,      'description' => $desc]);
            JournalEntryLine::create(['journal_entry_id' => $je->id, 'account_id' => $creditAccount->id, 'debit' => 0,       'credit' => $amount, 'description' => $desc]);

            $je->post(auth()->id());

            // Update reference document
            $ref = $writeoff->reference_type::find($writeoff->reference_id);
            if ($ref) {
                $newRemaining = max(0, (float) $ref->remaining_amount - $amount);
                $newStatus = $newRemaining <= 0 ? 'paid' : 'partial';
                $ref->update(['remaining_amount' => $newRemaining, 'status' => $newStatus]);
            }

            $writeoff->update([
                'status' => 'posted',
                'journal_entry_id' => $je->id,
                'posted_at' => now(),
            ]);

            ActivityLog::record('writeoff_posted', "Write-off {$writeoff->number} diposting → JE {$je->number}", $writeoff);
        });

        return back()->with('success', "Write-off {$writeoff->number} berhasil diposting ke jurnal.");
    }
}
