<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\PharmacyInventory;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PharmacyController extends Controller
{
    /**
     * Display pharmacy dashboard.
     */
    public function index()
    {
        $statistics = [
            'total_items' => PharmacyInventory::count(),
            'low_stock' => PharmacyInventory::whereColumn('stock_quantity', '<=', 'reorder_level')->count(),
            'out_of_stock' => PharmacyInventory::where('stock_quantity', 0)->count(),
            'expiring_soon' => PharmacyInventory::where('has_expiry', true)
                ->where('expiry_date', '<=', now()->addDays(30))
                ->count(),
            'total_value' => PharmacyInventory::sumRaw('stock_quantity * unit_cost'),
        ];

        $lowStockItems = PharmacyInventory::whereColumn('stock_quantity', '<=', 'reorder_level')
            ->orderBy('stock_quantity')
            ->limit(10)
            ->get();

        $expiringSoon = PharmacyInventory::where('has_expiry', true)
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        return view('healthcare.pharmacy.index', compact('statistics', 'lowStockItems', 'expiringSoon'));
    }

    /**
     * Display pharmacy inventory.
     */
    public function inventory(Request $request)
    {
        $query = PharmacyInventory::query();

        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        if ($request->filled('medication_type')) {
            $query->where('medication_type', $request->medication_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereColumn('stock_quantity', '<=', 'reorder_level');
            } elseif ($request->stock_status === 'out') {
                $query->where('stock_quantity', 0);
            } elseif ($request->stock_status === 'expiring') {
                $query->where('has_expiry', true)
                    ->where('expiry_date', '<=', now()->addDays(30));
            }
        }

        $items = $query->orderBy('item_name')->paginate(50);

        return view('healthcare.pharmacy.inventory', compact('items'));
    }

    /**
     * Store new pharmacy inventory item.
     */
    public function storeInventory(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required|string|unique:pharmacy_inventories,item_code',
            'item_name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'item_type' => 'required|in:medication,supply,equipment,cosmetic,other',
            'medication_type' => 'nullable|in:tablet,capsule,syrup,injection,cream,ointment,drop,spray,patch,gel',
            'category' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'max_stock_level' => 'nullable|integer|min:0',
            'unit_of_measure' => 'required|string|max:50',
            'storage_requirement' => 'nullable|in:room_temp,refrigerated,frozen,controlled_substance',
            'requires_prescription' => 'boolean',
            'controlled_substance' => 'boolean',
            'has_expiry' => 'boolean',
            'expiry_date' => 'nullable|date|required_if:has_expiry,true',
            'batch_number' => 'nullable|string|max:255',
            'bpom_number' => 'nullable|string|max:255',
        ]);

        PharmacyInventory::create($validated);

        return back()->with('success', 'Inventory item added successfully');
    }

    /**
     * Display prescriptions.
     */
    public function prescriptions(Request $request)
    {
        $query = Prescription::with(['patient', 'doctor', 'pharmacyInventory']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('prescribed_date', $request->date);
        }

        $prescriptions = $query->latest()->paginate(20);

        return view('healthcare.pharmacy.prescriptions', compact('prescriptions'));
    }

    /**
     * Display prescription details.
     */
    public function showPrescription(Prescription $prescription)
    {
        $prescription->load(['patient', 'doctor', 'pharmacyInventory']);

        return view('healthcare.pharmacy.prescription-show', compact('prescription'));
    }

    /**
     * Dispense prescription.
     */
    public function dispense(Prescription $prescription, Request $request)
    {
        if ($prescription->status !== 'verified') {
            return back()->with('error', 'Prescription must be verified before dispensing');
        }

        $validated = $request->validate([
            'quantity_dispensed' => 'required|integer|min:1',
            'batch_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Check stock availability
        $inventory = PharmacyInventory::where('item_name', $prescription->medication_name)->first();
        
        if (!$inventory || $inventory->stock_quantity < $validated['quantity_dispensed']) {
            return back()->with('error', 'Insufficient stock');
        }

        $inventory->issueStock($validated['quantity_dispensed']);

        $prescription->update([
            'status' => 'dispensed',
            'dispensed_at' => now(),
            'dispensed_by' => auth()->id(),
            'quantity_dispensed' => $validated['quantity_dispensed'],
            'batch_number' => $validated['batch_number'],
            'dispensing_notes' => $validated['notes'],
        ]);

        return back()->with('success', 'Prescription dispensed successfully');
    }

    /**
     * Verify prescription.
     */
    public function verify(Prescription $prescription)
    {
        if ($prescription->status !== 'pending') {
            return back()->with('error', 'Only pending prescriptions can be verified');
        }

        $prescription->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        return back()->with('success', 'Prescription verified successfully');
    }

    /**
     * Display stock alerts.
     */
    public function stockAlerts()
    {
        $lowStock = PharmacyInventory::whereColumn('stock_quantity', '<=', 'reorder_level')
            ->orderBy('stock_quantity')
            ->get();

        $outOfStock = PharmacyInventory::where('stock_quantity', 0)
            ->orderBy('item_name')
            ->get();

        return view('healthcare.pharmacy.stock-alerts', compact('lowStock', 'outOfStock'));
    }

    /**
     * Display expiring soon items.
     */
    public function expiringSoon()
    {
        $items = PharmacyInventory::where('has_expiry', true)
            ->where('expiry_date', '<=', now()->addDays(90))
            ->where('expiry_date', '>', now())
            ->orderBy('expiry_date')
            ->get();

        return view('healthcare.pharmacy.expiring-soon', compact('items'));
    }

    /**
     * Record stock opname (stock count).
     */
    public function stockOpname(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.inventory_id' => 'required|exists:pharmacy_inventories,id',
            'items.*.actual_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $adjustments = [];

        foreach ($validated['items'] as $item) {
            $inventory = PharmacyInventory::findOrFail($item['inventory_id']);
            $difference = $item['actual_quantity'] - $inventory->stock_quantity;

            if ($difference !== 0) {
                $inventory->adjustStock($item['actual_quantity'], 'stock_opname', auth()->id(), $validated['notes'] ?? null);
                
                $adjustments[] = [
                    'item' => $inventory->item_name,
                    'previous' => $inventory->stock_quantity,
                    'actual' => $item['actual_quantity'],
                    'difference' => $difference,
                ];
            }
        }

        return back()->with('success', 'Stock opname completed. ' . count($adjustments) . ' adjustments made');
    }

    /**
     * Display pharmacy dashboard.
     */
    public function dashboard()
    {
        $statistics = [
            'total_items' => PharmacyInventory::count(),
            'low_stock' => PharmacyInventory::whereColumn('stock_quantity', '<=', 'reorder_level')->count(),
            'pending_prescriptions' => Prescription::where('status', 'pending')->count(),
            'verified_prescriptions' => Prescription::where('status', 'verified')->count(),
            'today_dispensed' => Prescription::whereDate('dispensed_at', today())->count(),
        ];

        $pendingPrescriptionsList = Prescription::with(['patient', 'doctor'])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        $lowStockItemsList = PharmacyInventory::whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->orderBy('stock_quantity')
            ->limit(5)
            ->get();

        $recentActivities = collect(); // Placeholder — extend with actual activity log if available

        return view('healthcare.pharmacy.dashboard', compact(
            'statistics',
            'pendingPrescriptionsList',
            'lowStockItemsList',
            'recentActivities'
        ));
    }

    /**
     * Display pharmacy reports.
     */
    public function reports(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth());
        $dateTo = $request->get('date_to', now()->endOfMonth());

        $report = [
            'total_dispensed' => Prescription::whereDate('dispensed_at', '>=', $dateFrom)
                ->whereDate('dispensed_at', '<=', $dateTo)->count(),
            'total_revenue' => Prescription::whereDate('dispensed_at', '>=', $dateFrom)
                ->whereDate('dispensed_at', '<=', $dateTo)
                ->sum('total_price'),
            'most_dispensed' => Prescription::whereDate('dispensed_at', '>=', $dateFrom)
                ->whereDate('dispensed_at', '<=', $dateTo)
                ->selectRaw('medication_name, COUNT(*) as count')
                ->groupBy('medication_name')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
        ];

        return view('healthcare.pharmacy.reports', compact('report', 'dateFrom', 'dateTo'));
    }
}
