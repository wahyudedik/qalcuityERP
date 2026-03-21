<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\ErpNotification;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

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
            'total'   => Invoice::where('tenant_id', $this->tenantId())->count(),
            'unpaid'  => Invoice::where('tenant_id', $this->tenantId())->where('status', 'unpaid')->count(),
            'partial' => Invoice::where('tenant_id', $this->tenantId())->where('status', 'partial')->count(),
            'paid'    => Invoice::where('tenant_id', $this->tenantId())->where('status', 'paid')->count(),
            'overdue' => Invoice::where('tenant_id', $this->tenantId())
                ->whereIn('status', ['unpaid', 'partial'])
                ->where('due_date', '<', now())
                ->count(),
        ];

        return view('invoices.index', compact('invoices', 'stats'));
    }

    public function create()
    {
        $customers = Customer::where('tenant_id', $this->tenantId())->where('is_active', true)->orderBy('name')->get();
        $orders    = SalesOrder::with('customer')
            ->where('tenant_id', $this->tenantId())
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('date')
            ->get();

        return view('invoices.create', compact('customers', 'orders'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'total_amount'   => 'required|numeric|min:0',
            'due_date'       => 'required|date',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $number = 'INV-' . date('Ymd') . '-' . str_pad(
            Invoice::where('tenant_id', $this->tenantId())->whereDate('created_at', today())->count() + 1,
            3, '0', STR_PAD_LEFT
        );

        Invoice::create([
            'tenant_id'        => $this->tenantId(),
            'number'           => $number,
            'customer_id'      => $data['customer_id'],
            'sales_order_id'   => $data['sales_order_id'] ?? null,
            'total_amount'     => $data['total_amount'],
            'paid_amount'      => 0,
            'remaining_amount' => $data['total_amount'],
            'status'           => 'unpaid',
            'due_date'         => $data['due_date'],
            'notes'            => $data['notes'] ?? null,
        ]);

        // In-app notification
        ErpNotification::create([
            'tenant_id' => $this->tenantId(),
            'user_id'   => auth()->id(),
            'type'      => 'invoice_created',
            'title'     => '🧾 Invoice Dibuat',
            'body'      => "Invoice {$number} senilai Rp " . number_format($data['total_amount'], 0, ',', '.') . " berhasil dibuat.",
            'data'      => ['number' => $number],
        ]);

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
            'notes'  => 'nullable|string|max:500',
        ]);

        $invoice->payments()->create([
            'tenant_id'      => $this->tenantId(),
            'amount'         => $data['amount'],
            'payment_method' => $data['method'],
            'notes'          => $data['notes'] ?? null,
            'payment_date'   => today(),
            'user_id'        => auth()->id(),
        ]);

        $invoice->updatePaymentStatus();

        // In-app notification jika lunas
        if ($invoice->fresh()->status === 'paid') {
            ErpNotification::create([
                'tenant_id' => $this->tenantId(),
                'user_id'   => auth()->id(),
                'type'      => 'invoice_paid',
                'title'     => '✅ Invoice Lunas',
                'body'      => "Invoice {$invoice->number} telah lunas dibayar.",
                'data'      => ['invoice_id' => $invoice->id],
            ]);
        }

        return back()->with('success', 'Pembayaran berhasil dicatat.');
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

        if (! $invoice->customer?->email) {
            return back()->with('error', 'Customer tidak memiliki alamat email.');
        }

        $invoice->load(['customer', 'salesOrder.items.product', 'tenant']);

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        $pdfContent = $pdf->output();
        $filename   = 'invoice-' . $invoice->number . '.pdf';
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
