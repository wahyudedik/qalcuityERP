<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceInstallment;
use App\Models\Payable;
use App\Models\Supplier;
use App\Services\GlPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceivablesController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Piutang (Receivables) ─────────────────────────────────────

    public function receivables(Request $request)
    {
        $query = Invoice::with('customer')
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('number', 'like', "%$s%")
                ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%$s%")));
        }
        if ($request->boolean('overdue')) {
            $query->whereIn('status', ['unpaid', 'partial'])->where('due_date', '<', today());
        }

        $invoices = $query->orderBy('due_date')->paginate(20)->withQueryString();

        $stats = [
            'total_outstanding' => Invoice::where('tenant_id', $this->tid())
                ->whereIn('status', ['unpaid', 'partial'])->sum('remaining_amount'),
            'overdue_count'     => Invoice::where('tenant_id', $this->tid())
                ->whereIn('status', ['unpaid', 'partial'])->where('due_date', '<', today())->count(),
            'unpaid_count'      => Invoice::where('tenant_id', $this->tid())->where('status', 'unpaid')->count(),
            'partial_count'     => Invoice::where('tenant_id', $this->tid())->where('status', 'partial')->count(),
        ];

        $customers = Customer::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();

        return view('receivables.index', compact('invoices', 'stats', 'customers'));
    }

    public function recordReceivablePayment(Request $request, Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $invoice->remaining_amount,
            'method' => 'required|in:cash,transfer,qris,other',
            'notes'  => 'nullable|string|max:500',
        ]);

        $invoice->payments()->create([
            'tenant_id'      => $this->tid(),
            'amount'         => $data['amount'],
            'payment_method' => $data['method'],
            'notes'          => $data['notes'] ?? null,
            'payment_date'   => today(),
            'user_id'        => auth()->id(),
        ]);

        $invoice->updatePaymentStatus();

        // GL Auto-Posting: Dr Kas/Bank / Cr Piutang Usaha
        $glResult = app(GlPostingService::class)->postInvoicePayment(
            tenantId:      $this->tid(),
            userId:        auth()->id(),
            invoiceNumber: $invoice->number . '-PAY-' . now()->format('His'),
            invoiceId:     $invoice->id,
            amount:        (float) $data['amount'],
            method:        $data['method'],
            date:          today()->toDateString(),
        );
        if ($glResult->isFailed()) {
            return back()->with('success', 'Pembayaran piutang berhasil dicatat.')
                ->with('warning', $glResult->warningMessage());
        }

        return back()->with('success', 'Pembayaran piutang berhasil dicatat.');
    }

    // ── Aging Analysis ────────────────────────────────────────────

    public function aging(Request $request)
    {
        $tid = $this->tid();

        // Ambil semua invoice outstanding, group by customer
        $invoices = Invoice::with('customer')
            ->where('tenant_id', $tid)
            ->whereIn('status', ['unpaid', 'partial'])
            ->get();

        // Build aging buckets per customer
        $aging = [];
        foreach ($invoices as $inv) {
            $cid    = $inv->customer_id;
            $bucket = $inv->agingBucket();
            $name   = $inv->customer?->name ?? 'Unknown';

            if (! isset($aging[$cid])) {
                $aging[$cid] = [
                    'customer'  => $name,
                    'credit_limit' => (float) ($inv->customer?->credit_limit ?? 0),
                    'current'   => 0,
                    '1-30'      => 0,
                    '31-60'     => 0,
                    '61-90'     => 0,
                    '90+'       => 0,
                    'total'     => 0,
                ];
            }

            $aging[$cid][$bucket] += (float) $inv->remaining_amount;
            $aging[$cid]['total'] += (float) $inv->remaining_amount;
        }

        // Sort by total descending
        usort($aging, fn($a, $b) => $b['total'] <=> $a['total']);

        $summary = [
            'current' => collect($aging)->sum('current'),
            '1-30'    => collect($aging)->sum('1-30'),
            '31-60'   => collect($aging)->sum('31-60'),
            '61-90'   => collect($aging)->sum('61-90'),
            '90+'     => collect($aging)->sum('90+'),
            'total'   => collect($aging)->sum('total'),
        ];

        return view('receivables.aging', compact('aging', 'summary'));
    }

    // ── Installment Management ────────────────────────────────────

    public function installments(Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== $this->tid(), 403);
        $invoice->load(['customer', 'installments']);
        return view('receivables.installments', compact('invoice'));
    }

    public function storeInstallments(Request $request, Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'installments'             => 'required|array|min:1',
            'installments.*.amount'    => 'required|numeric|min:1',
            'installments.*.due_date'  => 'required|date',
            'installments.*.notes'     => 'nullable|string|max:255',
        ]);

        // Validasi total installment = total invoice
        $total = collect($data['installments'])->sum('amount');
        if (abs($total - (float) $invoice->total_amount) > 1) {
            return back()->withErrors(['installments' => 'Total cicilan harus sama dengan total invoice (Rp ' . number_format($invoice->total_amount, 0, ',', '.') . ').'])->withInput();
        }

        DB::transaction(function () use ($invoice, $data) {
            // Hapus installment lama jika ada
            $invoice->installments()->delete();

            foreach ($data['installments'] as $i => $inst) {
                InvoiceInstallment::create([
                    'tenant_id'          => $invoice->tenant_id,
                    'invoice_id'         => $invoice->id,
                    'installment_number' => $i + 1,
                    'amount'             => $inst['amount'],
                    'due_date'           => $inst['due_date'],
                    'paid_amount'        => 0,
                    'status'             => 'unpaid',
                    'notes'              => $inst['notes'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Jadwal cicilan berhasil disimpan.');
    }

    public function payInstallment(Request $request, InvoiceInstallment $installment)
    {
        abort_if($installment->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $installment->remaining(),
        ]);

        $installment->paid_amount += $data['amount'];
        $installment->status = $installment->paid_amount >= $installment->amount ? 'paid' : 'partial';
        $installment->paid_date = today();
        $installment->save();

        // Sync ke invoice payment
        $installment->invoice->payments()->create([
            'tenant_id'      => $this->tid(),
            'amount'         => $data['amount'],
            'payment_method' => $request->input('method', 'transfer'),
            'notes'          => "Cicilan #{$installment->installment_number}",
            'payment_date'   => today(),
            'user_id'        => auth()->id(),
        ]);
        $installment->invoice->updatePaymentStatus();

        return back()->with('success', "Cicilan #{$installment->installment_number} berhasil dibayar.");
    }

    // ── Hutang (Payables) ─────────────────────────────────────────

    public function payables(Request $request)
    {
        $query = Payable::with(['supplier', 'purchaseOrder'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('number', 'like', "%$s%")
                ->orWhereHas('supplier', fn($c) => $c->where('name', 'like', "%$s%")));
        }
        if ($request->boolean('overdue')) {
            $query->whereIn('status', ['unpaid', 'partial'])->where('due_date', '<', today());
        }

        $payables = $query->orderBy('due_date')->paginate(20)->withQueryString();

        $stats = [
            'total_outstanding' => Payable::where('tenant_id', $this->tid())
                ->whereIn('status', ['unpaid', 'partial'])->sum('remaining_amount'),
            'overdue_count'     => Payable::where('tenant_id', $this->tid())
                ->whereIn('status', ['unpaid', 'partial'])->where('due_date', '<', today())->count(),
            'unpaid_count'      => Payable::where('tenant_id', $this->tid())->where('status', 'unpaid')->count(),
            'partial_count'     => Payable::where('tenant_id', $this->tid())->where('status', 'partial')->count(),
        ];

        $suppliers = Supplier::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();

        return view('receivables.payables', compact('payables', 'stats', 'suppliers'));
    }

    public function recordPayablePayment(Request $request, Payable $payable)
    {
        abort_if($payable->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $payable->remaining_amount,
            'method' => 'required|in:cash,transfer,qris,other',
            'notes'  => 'nullable|string|max:500',
        ]);

        $payable->payments()->create([
            'tenant_id'      => $this->tid(),
            'amount'         => $data['amount'],
            'payment_method' => $data['method'],
            'notes'          => $data['notes'] ?? null,
            'payment_date'   => today(),
            'user_id'        => auth()->id(),
        ]);

        $payable->updatePaymentStatus();

        // GL Auto-Posting: Dr Hutang Usaha / Cr Kas/Bank
        $glResult = app(GlPostingService::class)->postPurchasePayment(
            tenantId: $this->tid(),
            userId:   auth()->id(),
            poNumber: $payable->number . '-PAY-' . now()->format('His'),
            poId:     $payable->id,
            amount:   (float) $data['amount'],
            method:   $data['method'],
            date:     today()->toDateString(),
        );
        if ($glResult->isFailed()) {
            return back()->with('success', 'Pembayaran hutang berhasil dicatat.')
                ->with('warning', $glResult->warningMessage());
        }

        return back()->with('success', 'Pembayaran hutang berhasil dicatat.');
    }
}
