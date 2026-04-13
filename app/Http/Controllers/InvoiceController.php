<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\ActivityLog;
use App\Models\ErpNotification;
use App\Models\TaxRate;
use App\Models\User;
use App\Services\CurrencyService;
use App\Services\DocumentNumberService;
use App\Services\GlPostingService;
use App\Services\InvoicePaymentService;
use App\Services\TaxService;
use App\Services\TransactionStateMachine;
use App\Exceptions\TransactionException;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    use \App\Traits\DispatchesWebhooks;

    // tenantId() inherited from parent Controller

    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'salesOrder'])
            ->where('tenant_id', $this->tenantId());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('number', 'like', '%' . $request->search . '%')
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        $invoices = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $stats = [
            'total' => Invoice::where('tenant_id', $this->tenantId())->count(),
            'unpaid' => Invoice::where('tenant_id', $this->tenantId())->where('status', 'unpaid')->count(),
            'partial' => Invoice::where('tenant_id', $this->tenantId())->where('status', 'partial')->count(),
            'paid' => Invoice::where('tenant_id', $this->tenantId())->where('status', 'paid')->count(),
            'overdue' => Invoice::where('tenant_id', $this->tenantId())
                ->whereIn('status', ['unpaid', 'partial'])
                ->where('due_date', '<', now())
                ->count(),
        ];

        return view('invoices.index', compact('invoices', 'stats'));
    }

    public function create()
    {
        $tid = $this->tenantId();
        $customers = Customer::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();
        $orders = SalesOrder::with('customer')
            ->where('tenant_id', $tid)
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('date')
            ->get();
        $taxRates = TaxRate::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();
        $currencies = (new CurrencyService())->activeCurrencies($tid);

        return view('invoices.create', compact('customers', 'orders', 'taxRates', 'currencies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'subtotal_amount' => 'required|numeric|min:0',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'due_date' => 'required|date',
            'currency_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:1000',
        ]);

        $tid = $this->tenantId();
        $taxService = new TaxService();

        // Cek period lock
        app(\App\Services\PeriodLockService::class)->assertNotLocked($tid, now()->toDateString(), 'Invoice');

        // BUG-SALES-003 FIX: Gunakan accounting-compliant calculation
        $subtotal = (float) $data['subtotal_amount'];
        $taxAmount = $data['tax_rate_id'] ? $taxService->calculate($subtotal, (int) $data['tax_rate_id']) : 0;

        // BUG-SALES-003 FIX: Hitung total dengan roundAccounting untuk mencegah rounding errors
        $total = $taxService->calculateTotal($subtotal, $taxAmount);

        $currCode = $data['currency_code'] ?? 'IDR';
        $currRate = (new CurrencyService())->getRate($currCode);

        // Task 37: Nomor sequential via DocumentNumberService
        $numberSvc = app(DocumentNumberService::class);
        $number = $numberSvc->generate($tid, 'invoice');

        $invoice = Invoice::create([
            'tenant_id' => $tid,
            'number' => $number,
            'doc_sequence' => (int) substr($number, strrpos($number, '-') + 1),
            'doc_year' => date('Y'),
            'customer_id' => $data['customer_id'],
            'sales_order_id' => $data['sales_order_id'] ?? null,
            'subtotal_amount' => $subtotal,
            'tax_rate_id' => $data['tax_rate_id'] ?? null,
            'tax_amount' => $taxAmount,
            'total_amount' => $total,
            'paid_amount' => 0,
            'remaining_amount' => $total,
            'status' => 'unpaid',
            'posting_status' => 'draft',  // Task 35: mulai sebagai draft
            'due_date' => $data['due_date'],
            'currency_code' => $currCode,
            'currency_rate' => $currRate,
            'notes' => $data['notes'] ?? null,
        ]);

        ActivityLog::record('invoice_created', "Invoice dibuat: {$number} (Rp " . number_format($data['total_amount'], 0, ',', '.') . ")", $invoice, [], $invoice->toArray());

        // GL Auto-Posting — hanya untuk invoice standalone (bukan dari SO, SO sudah di-post saat dibuat)
        if (empty($data['sales_order_id'])) {
            $glResult = app(GlPostingService::class)->postInvoiceCreated(
                tenantId: $tid,
                userId: auth()->id(),
                invoiceNumber: $number,
                invoiceId: $invoice->id,
                subtotal: $subtotal,
                taxAmount: $taxAmount,
                total: $total,
                date: today()->toDateString(),
            );
            if ($glResult->isFailed()) {
                session()->flash('warning', $glResult->warningMessage());
            }
        }

        // In-app notification
        ErpNotification::create([
            'tenant_id' => $this->tenantId(),
            'user_id' => auth()->id(),
            'type' => 'invoice_created',
            'title' => '🧾 Invoice Dibuat',
            'body' => "Invoice {$number} senilai Rp " . number_format($data['total_amount'], 0, ',', '.') . " berhasil dibuat.",
            'data' => ['number' => $number],
        ]);

        $this->fireWebhook('invoice.created', $invoice->load('customer')->toArray());

        return redirect()->route('invoices.index')->with('success', 'Invoice berhasil dibuat.');
    }

    public function show(Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== $this->tenantId(), 403);
        $invoice->load(['customer', 'salesOrder.items.product', 'payments']);
        return view('invoices.show', compact('invoice'));
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== $this->tenantId(), 403);

        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $invoice->remaining_amount,
            'method' => 'required|in:cash,transfer,qris,other',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            // Use atomic invoice payment service with full transaction support
            $paymentService = app(InvoicePaymentService::class);
            $result = $paymentService->processPayment(
                invoice: $invoice,
                data: $data,
                userId: auth()->id()
            );

            // Show GL warning if posting failed (but payment succeeded)
            if (!$result['gl_success']) {
                return back()->with('success', 'Pembayaran berhasil dicatat.')
                    ->with('warning', $result['gl_result']->warningMessage());
            }

            // Fire webhook for external systems
            $this->fireWebhook('payment.received', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->number,
                'amount' => (float) $data['amount'],
                'method' => $data['method'],
                'status' => $result['invoice']->status,
            ]);

            return back()->with('success', 'Pembayaran berhasil dicatat.');

        } catch (TransactionException $e) {
            Log::error("Payment transaction failed", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'context' => $e->getContext()
            ]);

            return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    // ── Task 35: State Machine Actions ───────────────────────────

    public function post(Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== $this->tenantId(), 403);

        try {
            app(TransactionStateMachine::class)->postInvoice($invoice, auth()->id());

            // GL Auto-Posting saat invoice diposting (jika belum ada dari store)
            if (empty($invoice->sales_order_id)) {
                $glResult = app(GlPostingService::class)->postInvoiceCreated(
                    tenantId: $this->tenantId(),
                    userId: auth()->id(),
                    invoiceNumber: $invoice->number,
                    invoiceId: $invoice->id,
                    subtotal: (float) $invoice->subtotal_amount,
                    taxAmount: (float) $invoice->tax_amount,
                    total: (float) $invoice->total_amount,
                    date: today()->toDateString(),
                );
                if ($glResult->isFailed()) {
                    return back()->with('success', "Invoice {$invoice->number} berhasil diposting.")
                        ->with('warning', $glResult->warningMessage());
                }
            }
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Invoice {$invoice->number} berhasil diposting.");
    }

    public function cancel(Request $request, Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== $this->tenantId(), 403);

        $data = $request->validate(['reason' => 'required|string|max:255']);

        try {
            app(TransactionStateMachine::class)->cancelInvoice($invoice, auth()->id(), $data['reason']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Invoice {$invoice->number} berhasil dibatalkan.");
    }

    public function void(Request $request, Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== $this->tenantId(), 403);

        $data = $request->validate(['reason' => 'required|string|max:255']);

        try {
            app(TransactionStateMachine::class)->voidInvoice($invoice, auth()->id(), $data['reason']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Invoice {$invoice->number} berhasil di-void.");
    }

    public function downloadPdf(Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== $this->tenantId(), 403);
        $invoice->load(['customer', 'salesOrder.items.product', 'tenant']);

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('invoice-' . $invoice->number . '.pdf');
    }

    public function sendEmail(Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== $this->tenantId(), 403);

        if (!$invoice->customer?->email) {
            return back()->with('error', 'Customer tidak memiliki alamat email.');
        }

        $invoice->load(['customer', 'salesOrder.items.product', 'tenant']);

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        $pdfContent = $pdf->output();
        $filename = 'invoice-' . $invoice->number . '.pdf';
        $tenantName = $invoice->tenant->name;
        $customerName = $invoice->customer->name;

        Mail::send([], [], function ($message) use ($invoice, $pdfContent, $filename, $tenantName, $customerName) {
            $message->to($invoice->customer->email, $customerName)
                ->subject("Invoice {$invoice->number} dari {$tenantName}")
                ->html(view('invoices.email', compact('invoice', 'tenantName'))->render())
                ->attachData($pdfContent, $filename, ['mime' => 'application/pdf']);
        });

        return back()->with('success', "Invoice berhasil dikirim ke {$invoice->customer->email}");
    }
}
