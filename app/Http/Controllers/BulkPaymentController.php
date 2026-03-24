<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BulkPayment;
use App\Models\BulkPaymentItem;
use App\Models\Customer;
use App\Models\CustomerBalance;
use App\Models\Invoice;
use App\Services\DocumentNumberService;
use App\Services\GlPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkPaymentController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $query = BulkPayment::where('tenant_id', $this->tid());

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('search')) $query->where('number', 'like', '%' . $request->search . '%');

        $payments = $query->with('party')->latest('payment_date')->paginate(20)->withQueryString();

        $customers = Customer::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();

        return view('bulk-payments.index', compact('payments', 'customers'));
    }

    public function create(Request $request)
    {
        $tid       = $this->tid();
        $customers = Customer::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();

        $selectedCustomer = null;
        $pendingInvoices  = collect();

        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::where('tenant_id', $tid)->find($request->customer_id);
            if ($selectedCustomer) {
                $pendingInvoices = Invoice::where('tenant_id', $tid)
                    ->where('customer_id', $selectedCustomer->id)
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->orderBy('due_date')
                    ->get();
            }
        }

        return view('bulk-payments.create', compact('customers', 'selectedCustomer', 'pendingInvoices'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'payment_date'   => 'required|date',
            'total_amount'   => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,transfer,qris,other',
            'notes'          => 'nullable|string|max:500',
            'invoices'       => 'required|array|min:1',
            'invoices.*.invoice_id' => 'required|exists:invoices,id',
            'invoices.*.amount'     => 'required|numeric|min:0',
        ]);

        $tid      = $this->tid();
        $customer = Customer::where('tenant_id', $tid)->findOrFail($data['customer_id']);

        // Validasi semua invoice milik customer & tenant
        $invoiceLines = [];
        $totalApplied = 0;

        foreach ($data['invoices'] as $line) {
            if ($line['amount'] <= 0) continue;

            $invoice = Invoice::where('tenant_id', $tid)
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->findOrFail($line['invoice_id']);

            $applied = min($line['amount'], (float) $invoice->remaining_amount);
            $invoiceLines[] = ['invoice' => $invoice, 'amount' => $applied];
            $totalApplied  += $applied;
        }

        $overpayment = max(0, (float) $data['total_amount'] - $totalApplied);

        DB::transaction(function () use ($data, $tid, $customer, $invoiceLines, $totalApplied, $overpayment) {
            $number = app(DocumentNumberService::class)->generate($tid, 'BP');

            $bp = BulkPayment::create([
                'tenant_id'      => $tid,
                'number'         => $number,
                'type'           => 'customer',
                'party_id'       => $customer->id,
                'party_type'     => Customer::class,
                'payment_date'   => $data['payment_date'],
                'total_amount'   => $data['total_amount'],
                'applied_amount' => $totalApplied,
                'overpayment'    => $overpayment,
                'payment_method' => $data['payment_method'],
                'status'         => 'applied',
                'created_by'     => auth()->id(),
                'notes'          => $data['notes'] ?? null,
            ]);

            $glLines = [];

            foreach ($invoiceLines as $line) {
                /** @var Invoice $invoice */
                $invoice = $line['invoice'];
                $amount  = $line['amount'];

                BulkPaymentItem::create([
                    'bulk_payment_id' => $bp->id,
                    'invoice_id'      => $invoice->id,
                    'amount'          => $amount,
                ]);

                $invoice->payments()->create([
                    'tenant_id'      => $tid,
                    'amount'         => $amount,
                    'payment_method' => $data['payment_method'],
                    'notes'          => "Bulk Payment {$number}",
                    'payment_date'   => $data['payment_date'],
                    'user_id'        => auth()->id(),
                ]);
                $invoice->updatePaymentStatus();

                $glLines[] = ['invoice_number' => $invoice->number, 'amount' => $amount];
            }

            // Overpayment → customer balance
            if ($overpayment > 0) {
                $balance = CustomerBalance::firstOrCreate(
                    ['tenant_id' => $tid, 'customer_id' => $customer->id],
                    ['balance' => 0]
                );
                $balance->credit($overpayment, 'bulk_payment', $number, $bp->id);
            }

            // GL Posting
            $glResult = app(GlPostingService::class)->postBulkPayment(
                tenantId:     $tid,
                userId:       auth()->id(),
                bpNumber:     $number,
                bpId:         $bp->id,
                totalPaid:    (float) $data['total_amount'],
                invoiceLines: $glLines,
                overpayment:  $overpayment,
                method:       $data['payment_method'],
                date:         $data['payment_date'],
            );
            if ($glResult->isFailed()) {
                session()->flash('warning', $glResult->warningMessage());
            }

            ActivityLog::record('bulk_payment_created', "Bulk payment {$number} diterapkan ke " . count($invoiceLines) . " invoice", $bp);
        });

        return redirect()->route('bulk-payments.index')->with('success', 'Bulk payment berhasil diterapkan.');
    }

    /** AJAX: ambil invoice outstanding milik customer */
    public function customerInvoices(Request $request)
    {
        $tid      = $this->tid();
        $customer = Customer::where('tenant_id', $tid)->findOrFail($request->customer_id);

        $invoices = Invoice::where('tenant_id', $tid)
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('due_date')
            ->get(['id', 'number', 'total_amount', 'remaining_amount', 'due_date', 'status']);

        return response()->json($invoices);
    }
}
