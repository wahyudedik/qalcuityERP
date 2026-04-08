<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use \App\Traits\DispatchesWebhooks;

    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $tid = $this->tenantId();
        $query = Customer::where('tenant_id', $tid);

        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")
                ->orWhere('company', 'like', "%$s%")
                ->orWhere('email', 'like', "%$s%")
                ->orWhere('phone', 'like', "%$s%"));
        }

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $customers = $query->orderBy('name')->paginate(20)->withQueryString();

        $stats = [
            'total' => Customer::where('tenant_id', $tid)->count(),
            'active' => Customer::where('tenant_id', $tid)->where('is_active', true)->count(),
            'inactive' => Customer::where('tenant_id', $tid)->where('is_active', false)->count(),
        ];

        return view('customers.index', compact('customers', 'stats'));
    }

    /**
     * Bulk operations for customers
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,activate,deactivate,update_credit_limit',
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'exists:customers,id',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $tenantId = $this->tenantId();
        $customerIds = $request->customer_ids;
        $action = $request->action;
        $affected = 0;

        // Verify all customers belong to tenant
        $customers = Customer::where('tenant_id', $tenantId)
            ->whereIn('id', $customerIds)
            ->get();

        if ($customers->count() !== count($customerIds)) {
            return back()->withErrors(['error' => 'Beberapa customer tidak valid atau bukan milik tenant Anda.']);
        }

        try {
            switch ($action) {
                case 'delete':
                    $affected = Customer::where('tenant_id', $tenantId)
                        ->whereIn('id', $customerIds)
                        ->delete();
                    break;

                case 'activate':
                    $affected = Customer::where('tenant_id', $tenantId)
                        ->whereIn('id', $customerIds)
                        ->update(['is_active' => true]);
                    break;

                case 'deactivate':
                    $affected = Customer::where('tenant_id', $tenantId)
                        ->whereIn('id', $customerIds)
                        ->update(['is_active' => false]);
                    break;

                case 'update_credit_limit':
                    $affected = Customer::where('tenant_id', $tenantId)
                        ->whereIn('id', $customerIds)
                        ->update(['credit_limit' => $request->credit_limit]);
                    break;
            }

            // Log activity
            ActivityLog::create([
                'tenant_id' => $tenantId,
                'user_id' => auth()->id(),
                'action' => "bulk_{$action}",
                'description' => "Bulk {$action} performed on {$affected} customers",
                'metadata' => [
                    'customer_ids' => $customerIds,
                    'count' => $affected,
                ],
            ]);

            $actionLabels = [
                'delete' => 'dihapus',
                'activate' => 'diaktifkan',
                'deactivate' => 'dinonaktifkan',
                'update_credit_limit' => 'diperbarui credit limitnya',
            ];

            return back()->with('success', "Berhasil: {$affected} customer {$actionLabels[$action]}");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal melakukan bulk action: ' . $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'npwp' => 'nullable|string|max:30',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $tid = $this->tenantId();

        if (Customer::where('tenant_id', $tid)->where('name', $data['name'])->exists()) {
            return back()->withErrors(['name' => 'Customer dengan nama ini sudah ada.'])->withInput();
        }

        $customer = Customer::create(array_merge($data, [
            'tenant_id' => $tid,
            'is_active' => true,
        ]));

        ActivityLog::record('customer_created', "Customer baru: {$customer->name}", $customer, [], $customer->toArray());

        $this->fireWebhook('customer.created', $customer->toArray());

        return back()->with('success', "Customer {$customer->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Customer $customer)
    {
        abort_unless($customer->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'npwp' => 'nullable|string|max:30',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $old = $customer->getOriginal();
        $customer->update($data);

        ActivityLog::record('customer_updated', "Customer diperbarui: {$customer->name}", $customer, $old, $customer->fresh()->toArray());

        $this->fireWebhook('customer.updated', $customer->fresh()->toArray());

        return back()->with('success', "Customer {$customer->name} berhasil diperbarui.");
    }

    public function toggleActive(Customer $customer)
    {
        abort_unless($customer->tenant_id === $this->tenantId(), 403);

        $customer->update(['is_active' => !$customer->is_active]);
        $status = $customer->is_active ? 'diaktifkan' : 'dinonaktifkan';

        ActivityLog::record('customer_toggled', "Customer {$customer->name} {$status}", $customer);

        return back()->with('success', "Customer {$customer->name} berhasil {$status}.");
    }

    public function destroy(Customer $customer)
    {
        abort_unless($customer->tenant_id === $this->tenantId(), 403);

        $hasTransactions = $customer->salesOrders()->exists()
            || $customer->invoices()->exists()
            || $customer->quotations()->exists();

        if ($hasTransactions) {
            $customer->update(['is_active' => false]);
            ActivityLog::record('customer_deactivated', "Customer dinonaktifkan (ada transaksi): {$customer->name}", $customer);
            return back()->with('success', "Customer dinonaktifkan karena sudah memiliki transaksi.");
        }

        ActivityLog::record('customer_deleted', "Customer dihapus: {$customer->name}", $customer, $customer->toArray());
        $this->fireWebhook('customer.deleted', $customer->toArray());
        $customer->delete();

        return back()->with('success', "Customer {$customer->name} berhasil dihapus.");
    }
}
