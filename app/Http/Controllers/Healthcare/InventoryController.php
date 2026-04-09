<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display inventory dashboard.
     */
    public function index()
    {
        $statistics = [
            'total_supplies' => 0, // Will use MedicalSupply model
            'low_stock' => 0,
            'out_of_stock' => 0,
            'expiring_soon' => 0,
            'pending_requests' => 0,
        ];

        return view('healthcare.inventory.index', compact('statistics'));
    }

    /**
     * Display supplies list.
     */
    public function supplies(Request $request)
    {
        $query = []; // Will use MedicalSupply model

        if ($request->filled('category')) {
            // Filter by category
        }

        if ($request->filled('stock_status')) {
            // Filter by stock status
        }

        $supplies = [];

        return view('healthcare.inventory.supplies', compact('supplies'));
    }

    /**
     * Store new supply.
     */
    public function storeSupply(Request $request)
    {
        $validated = $request->validate([
            'supply_code' => 'required|string|unique:medical_supplies,supply_code',
            'supply_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'unit_of_measure' => 'required|string|max:50',
            'current_stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'has_expiry' => 'boolean',
            'expiry_date' => 'nullable|date|required_if:has_expiry,true',
            'storage_location' => 'nullable|string|max:255',
        ]);

        // Create medical supply record

        return back()->with('success', 'Supply added successfully');
    }

    /**
     * Receive supply stock.
     */
    public function receive($id, Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'batch_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Create supply transaction (receipt)
        // Update stock quantity

        return back()->with('success', 'Stock received successfully');
    }

    /**
     * Issue supply stock.
     */
    public function issue($id, Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'department_id' => 'nullable|exists:departments,id',
            'requested_by' => 'required|exists:users,id',
            'purpose' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Check stock availability
        // Create supply transaction (issue)
        // Update stock quantity

        return back()->with('success', 'Stock issued successfully');
    }

    /**
     * Display expiring soon items.
     */
    public function expiringSoon()
    {
        $items = []; // Will fetch supplies expiring within 90 days

        return view('healthcare.inventory.expiring-soon', compact('items'));
    }

    /**
     * Display low stock items.
     */
    public function lowStock()
    {
        $items = []; // Will fetch supplies below reorder level

        return view('healthcare.inventory.low-stock', compact('items'));
    }

    /**
     * Store supply request.
     */
    public function storeRequest(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'urgency' => 'required|in:low,normal,urgent,critical',
            'required_by_date' => 'nullable|date|after:today',
            'items' => 'required|array',
            'items.*.supply_id' => 'required|exists:medical_supplies,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        // Create medical supply request
        // Create request items

        return back()->with('success', 'Supply request submitted successfully');
    }

    /**
     * Record sterilization.
     */
    public function recordSterilization(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'nullable|exists:medical_equipment,id',
            'equipment_name' => 'nullable|string|max:255',
            'sterilization_method' => 'required|in:autoclave,ethylene_oxide,hydrogen_peroxide,steam,dry_heat,chemical,radiation,uv',
            'sterilization_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'temperature' => 'nullable|numeric',
            'pressure' => 'nullable|numeric',
            'items_count' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        // Create sterilization log

        return back()->with('success', 'Sterilization recorded successfully');
    }

    /**
     * Display inventory dashboard.
     */
    public function dashboard()
    {
        $statistics = [
            'total_supplies' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0,
            'expiring_soon' => 0,
            'pending_requests' => 0,
            'total_value' => 0,
        ];

        $recentTransactions = [];
        $pendingRequests = [];

        return view('healthcare.inventory.dashboard', compact('statistics', 'recentTransactions', 'pendingRequests'));
    }
}
