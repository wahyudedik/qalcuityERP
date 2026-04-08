<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerPortalController extends Controller
{
    /**
     * Show customer portal dashboard
     */
    public function index()
    {
        $customer = $this->getAuthenticatedCustomer();

        if (!$customer) {
            return redirect()->route('login')->withErrors(['error' => 'Please login as customer']);
        }

        // Dashboard statistics
        $stats = [
            'total_orders' => SalesOrder::where('customer_id', $customer->id)->count(),
            'pending_orders' => SalesOrder::where('customer_id', $customer->id)
                ->whereIn('status', ['pending', 'confirmed'])->count(),
            'total_invoices' => Invoice::where('customer_id', $customer->id)->count(),
            'unpaid_invoices' => Invoice::where('customer_id', $customer->id)
                ->where('status', '!=', 'paid')->count(),
            'outstanding_balance' => Invoice::where('customer_id', $customer->id)
                ->where('status', '!=', 'paid')
                ->sum('total') - Invoice::where('customer_id', $customer->id)
                    ->where('status', 'paid')
                    ->sum('total'),
            'active_tickets' => SupportTicket::where('customer_id', $customer->id)
                ->where('status', 'open')->count(),
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

        return view('customer-portal.orders.index', compact('orders'));
    }

    /**
     * Show order detail with tracking
     */
    public function showOrder(SalesOrder $order)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (!$customer) {
            abort(403, 'Customer not found');
        }

        // BUG-CRM-002 FIX: Verify order belongs to customer AND same tenant
        if ($order->customer_id !== $customer->id || $order->tenant_id !== $customer->tenant_id) {
            \Log::warning('Unauthorized order access attempt', [
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'order_customer_id' => $order->customer_id,
                'order_tenant_id' => $order->tenant_id,
            ]);
            abort(403, 'Unauthorized access');
        }

        $order->load(['items.product', 'invoices', 'shipments']);

        // Tracking timeline
        $tracking = $this->getOrderTracking($order);

        return view('customer-portal.orders.show', compact('order', 'tracking'));
    }

    /**
     * List all invoices
     */
    public function invoices(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer();

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

        return view('customer-portal.invoices.index', compact('invoices'));
    }

    /**
     * Show invoice detail
     */
    public function showInvoice(Invoice $invoice)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (!$customer) {
            abort(403, 'Customer not found');
        }

        // BUG-CRM-002 FIX: Verify invoice belongs to customer AND same tenant
        if ($invoice->customer_id !== $customer->id || $invoice->tenant_id !== $customer->tenant_id) {
            \Log::warning('Unauthorized invoice access attempt', [
                'customer_id' => $customer->id,
                'invoice_id' => $invoice->id,
                'invoice_customer_id' => $invoice->customer_id,
                'invoice_tenant_id' => $invoice->tenant_id,
            ]);
            abort(403, 'Unauthorized access');
        }

        $invoice->load(['items', 'payments']);

        return view('customer-portal.invoices.show', compact('invoice'));
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoice(Invoice $invoice)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (!$customer) {
            abort(403, 'Customer not found');
        }

        // BUG-CRM-002 FIX: Verify invoice belongs to customer AND same tenant
        if ($invoice->customer_id !== $customer->id || $invoice->tenant_id !== $customer->tenant_id) {
            \Log::warning('Unauthorized invoice download attempt', [
                'customer_id' => $customer->id,
                'invoice_id' => $invoice->id,
            ]);
            abort(403, 'Unauthorized access');
        }

        // Generate or get existing PDF
        $pdfPath = $this->generateInvoicePdf($invoice);

        return response()->download($pdfPath);
    }

    /**
     * Update customer profile
     */
    public function updateProfile(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        $customer->update($validated);

        return back()->with('success', 'Profile updated successfully');
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!Auth::guard('customer')->attempt(['email' => $customer->email, 'password' => $validated['current_password']])) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $customer->update([
            'password' => bcrypt($validated['password']),
        ]);

        return back()->with('success', 'Password changed successfully');
    }

    /**
     * List support tickets
     */
    public function tickets(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer();

        $query = SupportTicket::where('customer_id', $customer->id);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $tickets = $query->latest()->paginate(20);

        return view('customer-portal.tickets.index', compact('tickets'));
    }

    /**
     * Create support ticket
     */
    public function createTicket(Request $request)
    {
        $customer = $this->getAuthenticatedCustomer();

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'category' => 'nullable|string|max:100',
        ]);

        $ticket = SupportTicket::create([
            'customer_id' => $customer->id,
            'tenant_id' => $customer->tenant_id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'priority' => $validated['priority'] ?? 'medium',
            'category' => $validated['category'] ?? 'general',
            'status' => 'open',
            'ticket_number' => $this->generateTicketNumber(),
        ]);

        return redirect()->route('customer-portal.tickets.show', $ticket)
            ->with('success', 'Support ticket created successfully');
    }

    /**
     * Show ticket detail
     */
    public function showTicket(SupportTicket $ticket)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (!$customer) {
            abort(403, 'Customer not found');
        }

        // BUG-CRM-002 FIX: Verify ticket belongs to customer AND same tenant
        if ($ticket->customer_id !== $customer->id || $ticket->tenant_id !== $customer->tenant_id) {
            \Log::warning('Unauthorized ticket access attempt', [
                'customer_id' => $customer->id,
                'ticket_id' => $ticket->id,
            ]);
            abort(403, 'Unauthorized access');
        }

        $ticket->load(['replies.user', 'replies.customer']);

        return view('customer-portal.tickets.show', compact('ticket'));
    }

    /**
     * Reply to ticket
     */
    public function replyTicket(Request $request, SupportTicket $ticket)
    {
        $customer = $this->getAuthenticatedCustomer();

        if (!$customer) {
            abort(403, 'Customer not found');
        }

        // BUG-CRM-002 FIX: Verify ticket belongs to customer AND same tenant
        if ($ticket->customer_id !== $customer->id || $ticket->tenant_id !== $customer->tenant_id) {
            \Log::warning('Unauthorized ticket reply attempt', [
                'customer_id' => $customer->id,
                'ticket_id' => $ticket->id,
            ]);
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $ticket->replies()->create([
            'customer_id' => $customer->id,
            'message' => $validated['message'],
            'is_customer' => true,
        ]);

        // Reopen if closed
        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'open']);
        }

        return back()->with('success', 'Reply sent successfully');
    }

    /**
     * Get authenticated customer
     * BUG-CRM-002 FIX: Ensure strict customer isolation
     */
    protected function getAuthenticatedCustomer()
    {
        $user = Auth::user();

        // Must have authenticated user
        if (!$user) {
            return null;
        }

        // If user has direct customer relation, use it
        if ($user->customer) {
            // BUG-CRM-002 FIX: Verify customer belongs to user's tenant
            if ($user->customer->tenant_id !== $user->tenant_id) {
                \Log::warning('Customer tenant mismatch detected', [
                    'user_id' => $user->id,
                    'user_tenant_id' => $user->tenant_id,
                    'customer_id' => $user->customer->id,
                    'customer_tenant_id' => $user->customer->tenant_id,
                ]);
                abort(403, 'Customer account configuration error');
            }

            return $user->customer;
        }

        // If user IS a customer (customer role), find their customer record
        if (in_array($user->role, ['customer', 'Customer'])) {
            $customer = Customer::where('user_id', $user->id)
                ->where('tenant_id', $user->tenant_id)
                ->first();

            if (!$customer) {
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
            'status' => 'Order Created',
            'date' => $order->created_at,
            'icon' => 'check-circle',
            'completed' => true,
        ];

        if (in_array($order->status, ['confirmed', 'processing', 'shipped', 'completed'])) {
            $timeline[] = [
                'status' => 'Confirmed',
                'date' => $order->updated_at,
                'icon' => 'check-circle',
                'completed' => true,
            ];
        }

        if (in_array($order->status, ['processing', 'shipped', 'completed'])) {
            $timeline[] = [
                'status' => 'Processing',
                'date' => $order->updated_at,
                'icon' => 'spinner',
                'completed' => true,
            ];
        }

        if (in_array($order->status, ['shipped', 'completed'])) {
            $timeline[] = [
                'status' => 'Shipped',
                'date' => $order->updated_at,
                'icon' => 'truck',
                'completed' => true,
            ];
        }

        if ($order->status === 'completed') {
            $timeline[] = [
                'status' => 'Delivered',
                'date' => $order->updated_at,
                'icon' => 'check-circle',
                'completed' => true,
            ];
        }

        return $timeline;
    }

    /**
     * Generate invoice PDF
     */
    protected function generateInvoicePdf(Invoice $invoice): string
    {
        // Use existing PDF generation service or create new one
        $pdfPath = storage_path("app/invoices/invoice-{$invoice->invoice_number}.pdf");

        if (!file_exists($pdfPath)) {
            // Generate PDF using existing service
            // This should integrate with your existing PDF generation
            \Log::info("Generating PDF for invoice: {$invoice->invoice_number}");

            // Placeholder - integrate with your PDF service
            $pdf = \PDF::loadView('invoices.pdf', compact('invoice'));
            $pdf->save($pdfPath);
        }

        return $pdfPath;
    }

    /**
     * Generate unique ticket number
     */
    protected function generateTicketNumber(): string
    {
        return 'TKT-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
