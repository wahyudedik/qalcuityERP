<?php

namespace App\Http\Controllers;

use App\Models\LandedCost;
use App\Models\LandedCostAllocation;
use App\Models\LandedCostComponent;
use App\Models\PurchaseOrder;
use App\Services\GlPostingService;
use App\Services\LandedCostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandedCostController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $query = LandedCost::with(['purchaseOrder', 'user'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('number', 'like', "%$s%")
                ->orWhere('description', 'like', "%$s%"));
        }

        $landedCosts = $query->latest()->paginate(20)->withQueryString();

        $purchaseOrders = PurchaseOrder::where('tenant_id', $this->tid())
            ->whereIn('status', ['confirmed', 'partial', 'received'])
            ->with('supplier')
            ->latest()->limit(50)->get();

        return view('landed-cost.index', compact('landedCosts', 'purchaseOrders'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'date' => 'required|date',
            'allocation_method' => 'required|in:by_value,by_quantity,by_weight,equal',
            'description' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'components' => 'required|array|min:1',
            'components.*.name' => 'required|string|max:255',
            'components.*.type' => 'required|in:freight,customs,insurance,handling,other',
            'components.*.amount' => 'required|numeric|min:0.01',
            'components.*.vendor' => 'nullable|string|max:255',
            'components.*.reference' => 'nullable|string|max:255',
        ]);

        $po = PurchaseOrder::with('items.product')->findOrFail($data['purchase_order_id']);
        abort_if($po->tenant_id !== $this->tid(), 403);

        DB::transaction(function () use ($data, $po) {
            $lc = LandedCost::create([
                'tenant_id' => $this->tid(),
                'number' => LandedCost::generateNumber($this->tid()),
                'purchase_order_id' => $po->id,
                'date' => $data['date'],
                'description' => $data['description'] ?? "Landed Cost untuk PO {$po->number}",
                'allocation_method' => $data['allocation_method'],
                'status' => 'draft',
                'user_id' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            // Save cost components
            $totalCost = 0;
            foreach ($data['components'] as $comp) {
                LandedCostComponent::create(array_merge($comp, [
                    'landed_cost_id' => $lc->id,
                ]));
                $totalCost += $comp['amount'];
            }
            $lc->update(['total_additional_cost' => $totalCost]);

            // Auto-create allocation lines from PO items
            foreach ($po->items as $item) {
                LandedCostAllocation::create([
                    'landed_cost_id' => $lc->id,
                    'product_id' => $item->product_id,
                    'original_cost' => $item->total,
                    'quantity' => $item->quantity_ordered,
                    'weight' => null,
                ]);
            }
        });

        return back()->with('success', 'Landed Cost berhasil dibuat. Lanjutkan ke alokasi.');
    }

    public function show(LandedCost $landedCost)
    {
        abort_if($landedCost->tenant_id !== $this->tid(), 403);
        $landedCost->load(['purchaseOrder.supplier', 'purchaseOrder.items.product', 'components', 'allocations.product', 'journalEntry', 'user']);

        return view('landed-cost.show', compact('landedCost'));
    }

    public function allocate(LandedCost $landedCost, LandedCostService $service)
    {
        abort_if($landedCost->tenant_id !== $this->tid(), 403);
        if ($landedCost->status === 'posted') {
            return back()->with('error', 'Sudah diposting, tidak bisa dialokasi ulang.');
        }

        $result = $service->allocate($landedCost);
        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        $landedCost->update(['status' => 'allocated']);

        return back()->with('success', "Alokasi berhasil. {$result['lines']} item, total biaya Rp ".number_format($result['total_cost'], 0, ',', '.'));
    }

    public function post(LandedCost $landedCost, GlPostingService $glService, LandedCostService $lcService)
    {
        abort_if($landedCost->tenant_id !== $this->tid(), 403);
        if ($landedCost->status !== 'allocated') {
            return back()->with('error', 'Alokasi dulu sebelum posting.');
        }

        $totalCost = (float) $landedCost->total_additional_cost;
        if ($totalCost <= 0) {
            return back()->with('error', 'Total biaya = 0.');
        }

        // GL: Dr Persediaan (1105) / Cr Hutang Usaha (2101)
        $glResult = $glService->postLandedCost(
            $this->tid(), auth()->id(), $landedCost->number, $landedCost->id, $totalCost, $landedCost->date->toDateString()
        );

        if ($glResult->isSuccess()) {
            $landedCost->update([
                'status' => 'posted',
                'journal_entry_id' => $glResult->journal->id,
            ]);

            // Update product price_buy with landed cost
            $updated = $lcService->updateProductCosts($landedCost);

            return back()->with('success', "Landed Cost diposting. Jurnal: {$glResult->journal->number}. {$updated} produk diperbarui.");
        }

        if ($glResult->isFailed()) {
            session()->flash('gl_warning', $glResult->warningMessage());
        }

        return back()->with('error', 'Gagal posting jurnal. '.($glResult->reason ?? ''));
    }

    public function updateWeight(Request $request, LandedCostAllocation $allocation)
    {
        $lc = $allocation->landedCost;
        abort_if($lc->tenant_id !== $this->tid(), 403);

        $data = $request->validate(['weight' => 'required|numeric|min:0.001']);
        $allocation->update($data);

        return back()->with('success', 'Berat diperbarui.');
    }

    public function destroy(LandedCost $landedCost)
    {
        abort_if($landedCost->tenant_id !== $this->tid(), 403);
        if ($landedCost->status === 'posted') {
            return back()->with('error', 'Landed Cost yang sudah diposting tidak bisa dihapus.');
        }
        $landedCost->delete();

        return back()->with('success', 'Landed Cost berhasil dihapus.');
    }
}
