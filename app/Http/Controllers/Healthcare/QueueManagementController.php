<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\QueueTicket;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Patient;
use Illuminate\Http\Request;

class QueueManagementController extends Controller
{
    /**
     * Display queue dashboard.
     */
    public function index(Request $request)
    {
        $query = QueueTicket::query()->with(['patient', 'doctor', 'department']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->orderBy('queue_number')->paginate(50);

        $departments = Department::where('is_active', true)->get();

        $statistics = [
            'total_queue' => QueueTicket::whereDate('created_at', today())->count(),
            'waiting' => QueueTicket::where('status', 'waiting')->count(),
            'in_service' => QueueTicket::where('status', 'in_service')->count(),
            'completed' => QueueTicket::where('status', 'completed')->whereDate('created_at', today())->count(),
            'no_show' => QueueTicket::where('status', 'no_show')->whereDate('created_at', today())->count(),
            'avg_wait_time' => QueueTicket::where('status', 'completed')->whereNotNull('served_at')
                ->avg(fn($q) => $q->whereRaw('TIMESTAMPDIFF(MINUTE, created_at, served_at)')) ?? 0,
        ];

        return view('healthcare.queue.index', compact('tickets', 'departments', 'statistics'));
    }

    /**
     * Create new queue ticket.
     */
    public function create(Request $request)
    {
        $departments = Department::where('is_active', true)->get();
        $doctors = Doctor::where('is_active', true)->get();

        $patient = null;
        if ($request->filled('patient_id')) {
            $patient = Patient::find($request->patient_id);
        }

        return view('healthcare.queue.create', compact('departments', 'doctors', 'patient'));
    }

    /**
     * Store new queue ticket.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'department_id' => 'required|exists:departments,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'visit_type' => 'required|in:consultation,follow_up,emergency,checkup',
            'priority' => 'required|in:regular,urgent,elderly,pregnant,disabled',
            'appointment_id' => 'nullable|exists:appointments,id',
            'notes' => 'nullable|string',
        ]);

        // Generate queue number
        $validated['queue_number'] = $this->generateQueueNumber($validated['department_id']);
        $validated['status'] = 'waiting';
        $validated['queue_time'] = now();

        $ticket = QueueTicket::create($validated);

        return redirect()->route('healthcare.queue.show', $ticket)
            ->with('success', 'Queue ticket created: ' . $ticket->queue_number);
    }

    /**
     * Display the specified queue ticket.
     */
    public function show(QueueTicket $ticket)
    {
        $ticket->load(['patient', 'doctor', 'department']);

        $queuePosition = QueueTicket::where('department_id', $ticket->department_id)
            ->where('status', 'waiting')
            ->where('created_at', '<=', $ticket->created_at)
            ->count();

        return view('healthcare.queue.show', compact('ticket', 'queuePosition'));
    }

    /**
     * Call next patient in queue.
     */
    public function callNext(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'doctor_id' => 'nullable|exists:doctors,id',
        ]);

        $query = QueueTicket::where('department_id', $validated['department_id'])
            ->where('status', 'waiting')
            ->orderBy('priority')
            ->orderBy('queue_number');

        if ($validated['doctor_id']) {
            $query->where('doctor_id', $validated['doctor_id']);
        }

        $nextTicket = $query->first();

        if (!$nextTicket) {
            return response()->json([
                'success' => false,
                'message' => 'No patients in queue',
            ], 404);
        }

        $nextTicket->update([
            'status' => 'in_service',
            'served_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $nextTicket,
            'message' => 'Calling patient: ' . $nextTicket->queue_number,
        ]);
    }

    /**
     * Update queue ticket status.
     */
    public function updateStatus(Request $request, QueueTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:waiting,in_service,completed,no_show,cancelled,transferred',
        ]);

        $ticket->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Queue status updated successfully',
        ]);
    }

    /**
     * Display current queue display.
     */
    public function displayBoard(Request $request)
    {
        $query = QueueTicket::with(['patient', 'doctor', 'department'])
            ->whereDate('created_at', today())
            ->whereIn('status', ['waiting', 'in_service']);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $currentQueue = $query->orderBy('queue_number')->get();

        return view('healthcare.queue.display-board', compact('currentQueue'));
    }

    /**
     * Get queue statistics.
     */
    public function statistics()
    {
        $today = now()->toDateString();

        $stats = [
            'today_total' => QueueTicket::whereDate('created_at', $today)->count(),
            'today_completed' => QueueTicket::whereDate('created_at', $today)->where('status', 'completed')->count(),
            'current_waiting' => QueueTicket::where('status', 'waiting')->count(),
            'current_serving' => QueueTicket::where('status', 'in_service')->count(),
            'avg_wait_time_minutes' => QueueTicket::where('status', 'completed')
                ->whereDate('created_at', $today)
                ->whereNotNull('served_at')
                ->avg(fn($q) => $q->whereRaw('TIMESTAMPDIFF(MINUTE, created_at, served_at)')) ?? 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Generate queue number.
     */
    private function generateQueueNumber($departmentId)
    {
        $department = Department::find($departmentId);
        $prefix = $department ? strtoupper(substr($department->department_code, 0, 3)) : 'GEN';
        $date = now()->format('Ymd');

        $lastTicket = QueueTicket::whereDate('created_at', today())
            ->where('department_id', $departmentId)
            ->orderBy('queue_number', 'desc')
            ->first();

        $sequence = $lastTicket ? (intval(substr($lastTicket->queue_number, -4)) + 1) : 1;

        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Remove the specified queue ticket.
     */
    public function destroy(QueueTicket $ticket)
    {
        if ($ticket->status === 'in_service') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a ticket that is being served',
            ], 400);
        }

        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Queue ticket deleted successfully',
        ]);
    }
}
