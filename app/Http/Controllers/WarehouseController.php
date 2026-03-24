<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $tid   = $this->tenantId();
        $query = Warehouse::where('tenant_id', $tid)->withCount('productStocks')->withSum('productStocks', 'quantity');

        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('code', 'like', "%$s%"));
        }
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $warehouses = $query->orderBy('name')->paginate(20)->withQueryString();

        $totalWarehouses  = Warehouse::where('tenant_id', $tid)->count();
        $activeWarehouses = Warehouse::where('tenant_id', $tid)->where('is_active', true)->count();

        return view('warehouses.index', compact('warehouses', 'totalWarehouses', 'activeWarehouses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $tid  = $this->tenantId();
        $code = $data['code'] ?? strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $data['name']), 0, 4)) . '-' . rand(10, 99);

        if (Warehouse::where('tenant_id', $tid)->where('name', $data['name'])->exists()) {
            return back()->withErrors(['name' => 'Gudang dengan nama ini sudah ada.'])->withInput();
        }

        $warehouse = Warehouse::create([
            'tenant_id' => $tid,
            'name'      => $data['name'],
            'code'      => $code,
            'address'   => $data['address'] ?? null,
            'is_active' => true,
        ]);

        ActivityLog::record('warehouse_created', "Gudang baru: {$warehouse->name} ({$warehouse->code})", $warehouse, [], $warehouse->toArray());

        return back()->with('success', "Gudang {$warehouse->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        abort_unless($warehouse->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $old = $warehouse->getOriginal();
        $warehouse->update($data);
        ActivityLog::record('warehouse_updated', "Gudang diperbarui: {$warehouse->name}", $warehouse, $old, $warehouse->fresh()->toArray());

        return back()->with('success', "Gudang {$warehouse->name} berhasil diperbarui.");
    }

    public function toggleActive(Warehouse $warehouse)
    {
        abort_unless($warehouse->tenant_id === $this->tenantId(), 403);
        $warehouse->update(['is_active' => !$warehouse->is_active]);
        $status = $warehouse->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Gudang {$warehouse->name} berhasil {$status}.");
    }

    public function destroy(Warehouse $warehouse)
    {
        abort_unless($warehouse->tenant_id === $this->tenantId(), 403);

        $hasStock = $warehouse->productStocks()->where('quantity', '>', 0)->exists();
        if ($hasStock) {
            return back()->with('error', 'Gudang tidak dapat dihapus karena masih memiliki stok produk.');
        }

        $hasMovements = $warehouse->stockMovements()->exists();
        if ($hasMovements) {
            $warehouse->update(['is_active' => false]);
            ActivityLog::record('warehouse_deactivated', "Gudang dinonaktifkan (ada riwayat mutasi): {$warehouse->name}", $warehouse);
            return back()->with('success', 'Gudang dinonaktifkan karena memiliki riwayat mutasi stok.');
        }

        ActivityLog::record('warehouse_deleted', "Gudang dihapus: {$warehouse->name} ({$warehouse->code})", $warehouse, $warehouse->toArray());
        $warehouse->delete();

        return back()->with('success', 'Gudang berhasil dihapus.');
    }
}
