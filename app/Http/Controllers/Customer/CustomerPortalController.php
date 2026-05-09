<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\HelpdeskTicket;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SalesOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerPortalController extends Controller
{
    /**
     * Show customer portal dashboard
     */
    public function index()
    {
        $customer = $this->getAuthenticatedCustomer();

        if (! $customer) {
            return redirect()->route('login')->withErrors(['error' => 'Silakan login sebagai pelanggan']);
        }

        // Dashboard statistics
        $stats = [
            'total_orders' => SalesOrder::where('customer_id', $customer->id)->count(),
            'pending_orders' => SalesOrder::where('customer_id', $customer->id)
                ->whereIn('status', ['pending', 'confirmed', 'processing'])->count(),
            'total_invoices' => Invoice::where('customer_id', $customer->id)->count(),
            'unpaid_invoices' => Invoice::where('customer_id', $customer->id)
                ->whereNotIn('status', ['paid', 'voided', 'cancelled'])->count(),
            'outstanding_balance' => Invoice::where('customer_id', $customer->id)
                ->whereNotIn('status', ['paid', 'voided', 'cancelled'])
                ->sum('remaining_amount'),
            'active_tickets' => HelpdeskTicket::where('customer_id', $customer->id)
                ->whereNotIn('status', ['closed', 'resolved'])->count(),
        ];

        // Recent orders
        $recentOrders = SalesOrder::where('customer_id', $customer->id)
            ->with(['items.product'])
            ->latest()
            ->take(5)
            ->get();

        // Recent invoices
        $recentInvoices = Invoice::where('customer_id', $customer->id)
            ->latest()
            ->take(5)
            ->get();

        return view('customer-portal.dashboard', compact('customer', 'stats', 'recentOrders', 'recentInvoices'));
    }

    /**
     * List all orders
     */
    public function orders(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (! $customer) {
            return redirect()->route('login')->withErrors(['error' => 'Silakan login sebagai pelanggan']);
        }

        $query = SalesOrder::where('customer_id', $customer->id)
            ->with(['items.product']);

        // Filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(20);

        return view('customer-portal.orders.index', compact('orders', 'customer'));
    }

    /**
     * Show order detail with tracking
     */
    public function showOrder(SalesOrder $order)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (! $customer) {
            abort(403, 'Pelanggan tidak ditemukan');
        }

        // Verify order belongs to customer AND same tenant
        if ($order->customer_id !== $customer->id || $order->tenant_id !== $customer->tenant_id) {
            \Log::warning('Unauthorized order access attempt', [
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'order_customer_id' => $order->customer_id,
                'order_tenant_id' => $order->tenant_id,
            ]);
            abort(403, 'Akses tidak diizinkan');
        }

        $order->load(['items.product', 'invoices', 'deliveryOrders']);

        // Tracking timeline
        $tracking = $this->getOrderTracking($order);

        return view('customer-portal.orders.show', compact('order', 'tracking', 'customer'));
    }

    /**
     * List all invoices
     */
    public function invoices(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (! $customer) {
            return redirect()->route('login')->withErrors(['error' => 'Silakan login sebagai pelanggan']);
        }

        $query = Invoice::where('customer_id', $customer->id);

        // Filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->latest()->paginate(20);

        return view('customer-portal.invoices.index', compact('invoices', 'customer'));
    }

    /**
     * Show invoice detail
     */
    public function showInvoice(Invoice $invoice)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (! $customer) {
            abort(403, 'Pelanggan tidak ditemukan');
        }

        // Verify invoice belongs to customer AND same tenant
        if ($invoice->customer_id !== $customer->id || $invoice->tenant_id !== $customer->tenant_id) {
            \Log::warning('Unauthorized invoice access attempt', [
                'customer_id' => $customer->id,
                'invoice_id' => $invoice->id,
                'invoice_customer_id' => $invoice->customer_id,
                'invoice_tenant_id' => $invoice->tenant_id,
            ]);
            abort(403, 'Akses tidak diizinkan');
        }

        $invoice->load(['salesOrder.items.product', 'payments']);

        return view('customer-portal.invoices.show', compact('invoice', 'customer'));
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoice(Invoice $invoice)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (! $customer) {
            abort(403, 'Pelanggan tidak ditemukan');
        }

        // Verify invoice belongs to customer AND same tenant
        if ($invoice->customer_id !== $customer->id || $invoice->tenant_id !== $customer->tenant_id) {
            \Log::warning('Unauthorized invoice download attempt', [
                'customer_id' => $customer->id,
                'invoice_id' => $invoice->id,
            ]);
            abort(403, 'Akses tidak diizinkan');
        }

        $invoice->load(['salesOrder.items.product', 'customer', 'payments']);

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        return $pdf->download("invoice-{$invoice->number}.pdf");
    }

    /**
     * Make payment for an invoice
     */
    public function payInvoice(Request $request, Invoice $invoice)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (! $customer) {
            abort(403, 'Pelanggan tidak ditemukan');
        }

        // Verify invoice belongs to customer AND same tenant
        if ($invoice->customer_id !== $customer->id || $invoice->tenant_id !== $customer->tenant_id) {
            abort(403, 'Akses tidak diizinkan');
        }

        // Validate payment
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:'.$invoice->remaining_amount,
            'payment_method' => 'required|in:bank_transfer,credit_card,qris',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check invoice is payable
        if (in_array($invoice->status, ['paid', 'voided', 'cancelled'])) {
            return back()->withErrors(['error' => 'Invoice ini tidak dapat dibayar.']);
        }

        // Create payment record
        $payment = $invoice->payments()->create([
            'tenant_id' => $customer->tenant_id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'reference' => $validated['payment_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'payment_date' => now(),
            'status' => 'pending',
        ]);

        // Update invoice payment status
        $invoice->updatePaymentStatus();

        return redirect()->route('customer-portal.invoices.show', $invoice)
            ->with('success', 'Pembayaran berhasil diajukan. Menunggu konfirmasi.');
    }

    /**
     * Transaction history
     */
    public function transactions(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (! $customer) {
            return redirect()->route('login')->withErrors(['error' => 'Silakan login sebagai pelanggan']);
        }

        $payments = Payment::whereHasMorph('payable', [Invoice::class], function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        })->latest('payment_date')->paginate(20);

        return view('customer-portal.transactions.index', compact('payments', 'customer'));
    }

    /**
     * Update customer profile
     */
    public function updateProfile(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (! $customer) {
            abort(403, 'Pelanggan tidak ditemukan');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:customers,email,'.$customer->id,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'company' => 'nullable|string|max:255',
        ]);

        $customer->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui');
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password berhasil diubah');
    }

    /**
     * List support tickets
     */
    public function tickets(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (! $customer) {
            return redirect()->route('login')->withErrors(['error' => 'Silakan login sebagai pelanggan']);
        }

        $query = HelpdeskTicket::where('customer_id', $customer->id);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $tickets = $query->latest()->paginate(20);

        return view('customer-portal.tickets.index', compact('tickets', 'customer'));
    }

    /**
     * Create support ticket
     */
    public function createTicket(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (! $customer) {
            abort(403, 'Pelanggan tidak ditemukan');
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'category' => 'nullable|string|max:100',
        ]);

        $ticket = HelpdeskTicket::create([
            'customer_id' => $customer->id,
            'tenant_id' => $customer->tenant_id,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'] ?? 'medium',
            'category' => $validated['category'] ?? 'general',
            'status' => 'open',
            'ticket_number' => HelpdeskTicket::generateNumber($customer->tenant_id),
            'contact_name' => $customer->name,
            'contact_email' => $customer->email,
            'contact_phone' => $customer->phone,
        ]);

        return redirect()->route('customer-portal.tickets.show', $ticket)
            ->with('success', 'Tiket support berhasil dibuat');
    }

    /**
     * Show ticket detail
     */
    public function showTicket(HelpdeskTicket $ticket)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (! $customer) {
            abort(403, 'Pelanggan tidak ditemukan');
        }

        // Verify ticket belongs to customer AND same tenant
        if ($ticket->customer_id !== $customer->id || $ticket->tenant_id !== $customer->tenant_id) {
            \Log::warning('Unauthorized ticket access attempt', [
                'customer_id' => $customer->id,
                'ticket_id' => $ticket->id,
            ]);
            abort(403, 'Akses tidak diizinkan');
        }

        $ticket->load(['replies.user']);

        return view('customer-portal.tickets.show', compact('ticket', 'customer'));
    }

    /**
     * Reply to ticket
     */
    public function replyTicket(Request $request, HelpdeskTicket $ticket)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (! $customer) {
            abort(403, 'Pelanggan tidak ditemukan');
        }

        // Verify ticket belongs to customer AND same tenant
        if ($ticket->customer_id !== $customer->id || $ticket->tenant_id !== $customer->tenant_id) {
            \Log::warning('Unauthorized ticket reply attempt', [
                'customer_id' => $customer->id,
                'ticket_id' => $ticket->id,
            ]);
            abort(403, 'Akses tidak diizinkan');
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $ticket->replies()->create([
            'user_id' => Auth::id(),
            'body' => $validated['message'],
            'is_internal' => false,
        ]);

        // Reopen if closed/resolved
        if (in_array($ticket->status, ['closed', 'resolved'])) {
            $ticket->update(['status' => 'open']);
        }

        return back()->with('success', 'Balasan berhasil dikirim');
    }

    /**
     * Get authenticated customer
     * Ensures strict customer isolation by tenant
     */
    protected function getAuthenticatedCustomer(): ?Customer
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        // If user has direct customer relation via user_id, use it
        $customer = Customer::where('email', $user->email)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if ($customer) {
            return $customer;
        }

        // If user IS a customer (customer role), try to find by name match
        if (in_array($user->role, ['customer', 'Customer'])) {
            $customer = Customer::where('tenant_id', $user->tenant_id)
                ->where('name', $user->name)
                ->first();

            if (! $customer) {
                \Log::warning('Customer record not found for user', [
                    'user_id' => $user->id,
                    'tenant_id' => $user->tenant_id,
                ]);
            }

            return $customer;
        }

        return null;
    }

    /**
     * Get order tracking timeline
     */
    protected function getOrderTracking(SalesOrder $order): array
    {
        $timeline = [];

        $timeline[] = [
            'status' => 'Pesanan Dibuat',
            'date' => $order->created_at,
            'icon' => 'check-circle',
            'completed' => true,
        ];

        if (in_array($order->status, ['confirmed', 'processing', 'shipped', 'completed', 'delivered'])) {
            $timeline[] = [
                'status' => 'Dikonfirmasi',
                'date' => $order->updated_at,
                'icon' => 'check-circle',
                'completed' => true,
            ];
        }

        if (in_array($order->status, ['processing', 'shipped', 'completed', 'delivered'])) {
            $timeline[] = [
                'status' => 'Diproses',
                'date' => $order->updated_at,
                'icon' => 'cog',
                'completed' => true,
            ];
        }

        if (in_array($order->status, ['shipped', 'completed', 'delivered'])) {
            $timeline[] = [
                'status' => 'Dikirim',
                'date' => $order->updated_at,
                'icon' => 'truck',
                'completed' => true,
            ];
        }

        if (in_array($order->status, ['completed', 'delivered'])) {
            $timeline[] = [
                'status' => 'Diterima',
                'date' => $order->updated_at,
                'icon' => 'check-circle',
                'completed' => true,
            ];
        }

        return $timeline;
    }
}
