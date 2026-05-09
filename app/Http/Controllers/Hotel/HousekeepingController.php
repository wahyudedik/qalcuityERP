<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\HousekeepingSupply;
use App\Models\HousekeepingTask;
use App\Models\LinenInventory;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\User;
use App\Services\HousekeepingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HousekeepingController extends Controller
{
    public function __construct(
        protected HousekeepingService $housekeepingService
    ) {}

    /**
     * Display housekeeping dashboard/board
     */
    public function index(Request $request)
    {
        $tenantId = $this->tenantId();

        // Get statistics
        $stats = $this->housekeepingService->getDashboardStats($tenantId);

        // Get rooms by status
        $rooms = Room::where('tenant_id', $tenantId)
            ->with(['roomType', 'housekeepingTasks' => function ($query) {
                $query->where('status', 'pending');
            }])
            ->orderBy('number')
            ->get()
            ->groupBy('status');

        // Get pending tasks
        $pendingTasks = HousekeepingTask::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->with(['room', 'assignedTo'])
            ->orderBy('priority')
            ->orderBy('scheduled_at')
            ->get();

        // Get urgent maintenance
        $urgentMaintenance = MaintenanceRequest::where('tenant_id', $tenantId)
            ->whereIn('priority', ['urgent', 'high'])
            ->where('status', '!=', 'completed')
            ->with(['room', 'assignedTo'])
            ->get();

        return view('hotel.housekeeping.index', compact('stats', 'rooms', 'pendingTasks', 'urgentMaintenance'));
    }

    /**
     * Display room status board
     */
    public function roomBoard(Request $request)
    {
        $tenantId = $this->tenantId();
        $filter = $request->get('filter', 'all'); // all, dirty, clean, inspected, ooo

        // Get rooms with their tasks
        $query = Room::where('tenant_id', $tenantId)
            ->with(['roomType', 'housekeepingTasks']);

        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        $rooms = $query->orderBy('floor')->orderBy('number')->get();

        // Get all housekeeping tasks for today grouped by status
        $today = now()->toDateString();
        $tasks = HousekeepingTask::where('tenant_id', $tenantId)
            ->with(['room.roomType', 'assignedTo'])
            ->whereDate('created_at', $today)
            ->orderBy('priority')
            ->orderBy('created_at')
            ->get()
            ->groupBy('status');

        // Create board data structure based on actual enum values
        $board = [
            'pending' => ($tasks['pending'] ?? collect())->concat($tasks['assigned'] ?? collect()),
            'in_progress' => $tasks['in_progress'] ?? collect(),
            'completed' => $tasks['completed'] ?? collect(),
            'inspected' => collect(), // Not used in current schema, keeping for UI compatibility
        ];

        // Get task types and priorities
        $taskTypes = HousekeepingTask::TYPES;
        $priorities = ['low', 'normal', 'high', 'urgent'];

        // Get housekeeping staff
        $users = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['housekeeping', 'maintenance'])
            ->orderBy('name')
            ->get();

        return view('hotel.housekeeping.room-board', compact('rooms', 'filter', 'board', 'taskTypes', 'priorities', 'users'));
    }

    /**
     * Update room status
     */
    public function updateRoomStatus(Request $request, int $roomId)
    {
        $request->validate([
            'status' => ['required', Rule::in(Room::STATUSES)],
        ]);

        $room = Room::findOrFail($roomId);
        $room->updateStatus($request->status, auth()->id());

        return back()->with('success', 'Room status updated successfully');
    }

    /**
     * Display tasks list
     */
    public function tasks(Request $request)
    {
        $tenantId = $this->tenantId();

        $query = HousekeepingTask::where('tenant_id', $tenantId)
            ->with(['room', 'assignedTo', 'inspectedBy']);

        // Filters
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($assignedTo = $request->get('assigned_to')) {
            $query->where('assigned_to', $assignedTo);
        }

        $tasks = $query->orderByDesc('created_at')->paginate(20);
        $staff = User::where('tenant_id', $tenantId)->where('role', 'housekeeping')->get();

        return view('hotel.housekeeping.tasks.index', compact('tasks', 'staff'));
    }

    /**
     * Store new housekeeping task
     */
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'type' => ['required', Rule::in(HousekeepingTask::TYPES)],
            'priority' => 'required|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $this->housekeepingService->createCleaningTask(
            $request->room_id,
            $request->type,
            $request->priority,
            null,
            $request->notes
        );

        // If assigned_to is provided, assign the task
        if ($request->filled('assigned_to')) {
            $latestTask = HousekeepingTask::where('tenant_id', $this->tenantId())
                ->latest()
                ->first();

            if ($latestTask) {
                $this->housekeepingService->assignTask($latestTask->id, $request->assigned_to);
            }
        }

        return back()->with('success', 'Housekeeping task created successfully');
    }

    /**
     * Assign task to staff
     */
    public function assignTask(Request $request, int $taskId)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $task = HousekeepingTask::findOrFail($taskId);
        $this->housekeepingService->assignTask($taskId, $request->assigned_to);

        return back()->with('success', 'Task assigned successfully');
    }

    /**
     * Start working on task
     */
    public function startTask(int $taskId)
    {
        $task = HousekeepingTask::findOrFail($taskId);
        $this->housekeepingService->startTask($taskId, auth()->id());

        return back()->with('success', 'Task started');
    }

    /**
     * Complete task
     */
    public function completeTask(Request $request, int $taskId)
    {
        $request->validate([
            'checklist' => 'nullable|array',
            'notes' => 'nullable|string',
            'photos' => 'nullable|array',
        ]);

        $task = HousekeepingTask::findOrFail($taskId);

        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store('housekeeping/' . $task->id, 'public');
            }
        }

        $this->housekeepingService->completeTask(
            $taskId,
            $request->checklist ?? [],
            $request->notes
        );

        if (! empty($photos)) {
            $task->update(['photos' => $photos]);
        }

        return back()->with('success', 'Task completed successfully');
    }

    /**
     * Create maintenance request
     */
    public function createMaintenanceRequest(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        $this->housekeepingService->createMaintenanceRequest(
            $request->room_id,
            $request->title,
            $request->category,
            $request->description,
            $request->priority,
            auth()->id()
        );

        return back()->with('success', 'Maintenance request created');
    }

    /**
     * Display maintenance requests
     */
    public function maintenance(Request $request)
    {
        $tenantId = $this->tenantId();

        $query = MaintenanceRequest::where('tenant_id', $tenantId)
            ->with(['room', 'reportedBy', 'assignedTo']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }

        $requests = $query->orderByDesc('created_at')->paginate(20);

        return view('hotel.housekeeping.maintenance.index', compact('requests'));
    }

    /**
     * Assign maintenance request
     */
    public function assignMaintenanceRequest(Request $request, int $requestId)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $maintenanceRequest = MaintenanceRequest::findOrFail($requestId);
        $this->housekeepingService->assignMaintenanceRequest($requestId, $request->assigned_to);

        return back()->with('success', 'Maintenance request assigned');
    }

    /**
     * Complete maintenance request
     */
    public function completeMaintenanceRequest(Request $request, int $requestId)
    {
        $request->validate([
            'resolution_notes' => 'required|string',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $maintenanceRequest = MaintenanceRequest::findOrFail($requestId);
        $this->housekeepingService->completeMaintenanceRequest(
            $requestId,
            $request->resolution_notes,
            $request->cost ?? 0
        );

        return back()->with('success', 'Maintenance request completed');
    }

    /**
     * Display linen inventory
     */
    public function linenInventory(Request $request)
    {
        $tenantId = $this->tenantId();

        $query = LinenInventory::where('tenant_id', $tenantId);

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($request->get('low_stock')) {
            $items = $query->get()->filter(fn($item) => $item->isBelowParLevel());
        } else {
            $items = $query->orderBy('item_name')->get();
        }

        return view('hotel.housekeeping.linen.index', compact('items'));
    }

    /**
     * Record linen movement
     */
    public function recordLinenMovement(Request $request)
    {
        $request->validate([
            'linen_inventory_id' => 'required|exists:linen_inventories,id',
            'movement_type' => 'required|in:add,remove,transfer,damage,laundry_out,laundry_in',
            'quantity' => 'required|integer|min:1',
            'room_id' => 'nullable|exists:rooms,id',
            'reason' => 'nullable|string',
        ]);

        $inventory = LinenInventory::findOrFail($request->linen_inventory_id);
        $inventory->recordMovement(
            $request->movement_type,
            $request->quantity,
            $request->room_id,
            null,
            null,
            $request->reason,
            auth()->id()
        );

        return back()->with('success', 'Linen movement recorded');
    }

    /**
     * Display supplies inventory
     */
    public function supplies(Request $request)
    {
        $tenantId = $this->tenantId();

        $query = HousekeepingSupply::where('tenant_id', $tenantId);

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($request->get('needs_reorder')) {
            $supplies = $query->get()->filter(fn($item) => $item->needsReorder());
        } else {
            $supplies = $query->orderBy('item_name')->get();
        }

        return view('hotel.housekeeping.supplies.index', compact('supplies'));
    }

    /**
     * Record supply usage
     */
    public function recordSupplyUsage(Request $request)
    {
        $request->validate([
            'housekeeping_supply_id' => 'required|exists:housekeeping_supplies,id',
            'quantity_used' => 'required|integer|min:1',
            'room_id' => 'nullable|exists:rooms,id',
            'housekeeping_task_id' => 'nullable|exists:housekeeping_tasks,id',
            'notes' => 'nullable|string',
        ]);

        $supply = HousekeepingSupply::findOrFail($request->housekeeping_supply_id);
        $supply->recordUsage(
            $request->quantity_used,
            $request->room_id,
            $request->housekeeping_task_id,
            $request->notes,
            auth()->id()
        );

        return back()->with('success', 'Supply usage recorded');
    }

    /**
     * Generate daily report
     */
    public function dailyReport(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        $report = $this->housekeepingService->generateDailyReport(
            $this->tenantId(),
            Carbon::parse($date)
        );

        return response()->json($report);
    }
}
