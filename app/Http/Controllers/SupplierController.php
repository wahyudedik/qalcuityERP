<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $tid   = $this->tenantId();
        $query = Supplier::where('tenant_id', $tid);

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

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();

        $stats = [
            'total'    => Supplier::where('tenant_id', $tid)->count(),
            'active'   => Supplier::where('tenant_id', $tid)->where('is_active', true)->count(),
            'inactive' => Supplier::where('tenant_id', $tid)->where('is_active', false)->count(),
        ];

        return view('suppliers.index', compact('suppliers', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'company'      => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:20',
            'address'      => 'nullable|string',
            'npwp'         => 'nullable|string|max:30',
            'bank_name'    => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'bank_holder'  => 'nullable|string|max:255',
        ]);

        $tid = $this->tenantId();

        if (Supplier::where('tenant_id', $tid)->where('name', $data['name'])->exists()) {
            return back()->withErrors(['name' => 'Supplier dengan nama ini sudah ada.'])->withInput();
        }

        $supplier = Supplier::create(array_merge($data, [
            'tenant_id' => $tid,
            'is_active' => true,
        ]));

        ActivityLog::record('supplier_created', "Supplier baru: {$supplier->name}", $supplier, [], $supplier->toArray());

        return back()->with('success', "Supplier {$supplier->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Supplier $supplier)
    {
        abort_unless($supplier->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'company'      => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:20',
            'address'      => 'nullable|string',
            'npwp'         => 'nullable|string|max:30',
            'bank_name'    => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'bank_holder'  => 'nullable|string|max:255',
            'is_active'    => 'boolean',
        ]);

        $old = $supplier->getOriginal();
        $supplier->update($data);

        ActivityLog::record('supplier_updated', "Supplier diperbarui: {$supplier->name}", $supplier, $old, $supplier->fresh()->toArray());

        return back()->with('success', "Supplier {$supplier->name} berhasil diperbarui.");
    }

    public function toggleActive(Supplier $supplier)
    {
        abort_unless($supplier->tenant_id === $this->tenantId(), 403);

        $supplier->update(['is_active' => !$supplier->is_active]);
        $status = $supplier->is_active ? 'diaktifkan' : 'dinonaktifkan';

        ActivityLog::record('supplier_toggled', "Supplier {$supplier->name} {$status}", $supplier);

        return back()->with('success', "Supplier {$supplier->name} berhasil {$status}.");
    }

    public function destroy(Supplier $supplier)
    {
        abort_unless($supplier->tenant_id === $this->tenantId(), 403);

        if ($supplier->purchaseOrders()->exists()) {
            $supplier->update(['is_active' => false]);
            ActivityLog::record('supplier_deactivated', "Supplier dinonaktifkan (ada PO): {$supplier->name}", $supplier);
            return back()->with('success', "Supplier dinonaktifkan karena sudah memiliki Purchase Order.");
        }

        ActivityLog::record('supplier_deleted', "Supplier dihapus: {$supplier->name}", $supplier, $supplier->toArray());
        $supplier->delete();

        return back()->with('success', "Supplier {$supplier->name} berhasil dihapus.");
    }
}
