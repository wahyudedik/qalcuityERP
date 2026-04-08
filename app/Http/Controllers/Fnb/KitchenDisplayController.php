<?php

namespace App\Http\Controllers\Fnb;

use App\Http\Controllers\Controller;
use App\Models\KitchenOrderTicket;
use App\Services\KitchenDisplayService;
use Illuminate\Http\Request;

class KitchenDisplayController extends Controller
{
    protected $kdsService;

    public function __construct(KitchenDisplayService $kdsService)
    {
        $this->kdsService = $kdsService;
    }

    /**
     * Display Kitchen Display System
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $station = $request->input('station');

        $tickets = $this->kdsService->getActiveTickets($tenantId, $station);
        $stats = $this->kdsService->getKdsStats($tenantId);
        $overdue = $this->kdsService->getOverdueTickets($tenantId);

        $stations = ['all', 'grill', 'fry', 'salad', 'dessert', 'bar'];

        return view('fnb.kds.index', compact('tickets', 'stats', 'overdue', 'stations', 'station'));
    }

    /**
     * BUG-FB-002 FIX: Validate and cleanup duplicate tickets
     */
    public function validateTickets(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $orderId = $request->input('order_id');

        if (!$orderId) {
            return response()->json(['error' => 'order_id required'], 400);
        }

        $order = \App\Models\FbOrder::where('tenant_id', $tenantId)->findOrFail($orderId);
        $ticketService = new \App\Services\KitchenTicketService();

        // Validate ticket count
        $validation = $ticketService->validateTicketCount($order);

        return response()->json($validation);
    }

    /**
     * BUG-FB-002 FIX: Cleanup duplicate tickets
     */
    public function cleanupDuplicates(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $orderId = $request->input('order_id');

        if (!$orderId) {
            return response()->json(['error' => 'order_id required'], 400);
        }

        $order = \App\Models\FbOrder::where('tenant_id', $tenantId)->findOrFail($orderId);
        $ticketService = new \App\Services\KitchenTicketService();

        // Cleanup duplicates
        $result = $ticketService->cleanupDuplicateTickets($order);

        return response()->json($result);
    }

    /**
     * Start preparing ticket
     */
    public function startTicket(KitchenOrderTicket $ticket)
    {
        $this->authorizeAccess($ticket);

        $this->kdsService->startTicket($ticket);

        return response()->json(['success' => true, 'message' => 'Ticket started']);
    }

    /**
     * Complete ticket
     */
    public function completeTicket(KitchenOrderTicket $ticket)
    {
        $this->authorizeAccess($ticket);

        $this->kdsService->completeTicket($ticket);

        return response()->json(['success' => true, 'message' => 'Ticket completed']);
    }

    /**
     * Get ticket details
     */
    public function showTicket(KitchenOrderTicket $ticket)
    {
        $this->authorizeAccess($ticket);

        return response()->json([
            'ticket' => $ticket->load(['order.guest', 'items.menuItem']),
            'elapsed_time' => $ticket->getElapsedTime(),
            'is_overdue' => $ticket->isOverdue(),
        ]);
    }

    /**
     * Update ticket priority
     */
    public function updatePriority(Request $request, KitchenOrderTicket $ticket)
    {
        $this->authorizeAccess($ticket);

        $validated = $request->validate([
            'priority' => 'required|in:normal,rush,vip',
        ]);

        $ticket->update(['priority' => $validated['priority']]);

        return response()->json(['success' => true]);
    }

    /**
     * Add chef notes
     */
    public function addChefNotes(Request $request, KitchenOrderTicket $ticket)
    {
        $this->authorizeAccess($ticket);

        $validated = $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        $ticket->update(['chef_notes' => $validated['notes']]);

        return response()->json(['success' => true]);
    }

    /**
     * Get KDS statistics API
     */
    public function getStats()
    {
        $tenantId = auth()->user()->tenant_id;
        $stats = $this->kdsService->getKdsStats($tenantId);

        return response()->json($stats);
    }

    private function authorizeAccess($model): void
    {
        if ($model->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access');
        }
    }
}
