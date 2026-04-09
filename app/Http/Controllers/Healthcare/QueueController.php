<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\QueueManagement;
use App\Models\Appointment;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    /**
     * Display queue management.
     */
    public function index(Request $request)
    {
        $query = QueueManagement::with(['patient', 'appointment', 'doctor']);

        if ($request->filled('queue_type')) {
            $query->where('queue_type', $request->queue_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            $query->whereDate('created_at', today());
        }

        $queues = $query->orderBy('queue_number')
            ->paginate(50);

        $statistics = [
            'waiting' => QueueManagement::where('status', 'waiting')->whereDate('created_at', today())->count(),
            'called' => QueueManagement::where('status', 'called')->whereDate('created_at', today())->count(),
            'serving' => QueueManagement::where('status', 'serving')->whereDate('created_at', today())->count(),
            'completed' => QueueManagement::where('status', 'completed')->whereDate('created_at', today())->count(),
            'skipped' => QueueManagement::where('status', 'skipped')->whereDate('created_at', today())->count(),
        ];

        return view('healthcare.queue.index', compact('queues', 'statistics'));
    }

    /**
     * Assign queue number.
     */
    public function assignNumber(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'queue_type' => 'required|in:outpatient,specialist,pharmacy,laboratory,radiology,billing,registration',
            'priority' => 'nullable|boolean',
            'doctor_id' => 'nullable|exists:doctors,id',
        ]);

        $queue = QueueManagement::create([
            'patient_id' => $validated['patient_id'],
            'appointment_id' => $validated['appointment_id'] ?? null,
            'queue_type' => $validated['queue_type'],
            'doctor_id' => $validated['doctor_id'] ?? null,
            'priority' => $validated['priority'] ?? false,
            'status' => 'waiting',
        ]);

        return response()->json([
            'success' => true,
            'queue_number' => $queue->queue_number,
            'token_number' => $queue->token_number,
            'estimated_wait' => $queue->estimatedWaitTime() . ' minutes',
        ]);
    }

    /**
     * Display queue board.
     */
    public function display(Request $request)
    {
        $queueType = $request->get('type', 'outpatient');

        $waiting = QueueManagement::with('patient')
            ->where('queue_type', $queueType)
            ->where('status', 'waiting')
            ->whereDate('created_at', today())
            ->orderBy('priority', 'desc')
            ->orderBy('queue_number')
            ->get();

        $called = QueueManagement::with('patient')
            ->where('queue_type', $queueType)
            ->whereIn('status', ['called', 'serving'])
            ->whereDate('created_at', today())
            ->latest('called_at')
            ->first();

        $completed = QueueManagement::where('queue_type', $queueType)
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->count();

        return view('healthcare.queue.display', compact('waiting', 'called', 'completed', 'queueType'));
    }

    /**
     * Get current queue status.
     */
    public function current(Request $request)
    {
        $queueType = $request->get('type', 'outpatient');

        $current = QueueManagement::with('patient')
            ->where('queue_type', $queueType)
            ->whereIn('status', ['called', 'serving'])
            ->whereDate('created_at', today())
            ->latest('called_at')
            ->first();

        $next = QueueManagement::with('patient')
            ->where('queue_type', $queueType)
            ->where('status', 'waiting')
            ->whereDate('created_at', today())
            ->orderBy('priority', 'desc')
            ->orderBy('queue_number')
            ->first();

        return response()->json([
            'current' => $current,
            'next' => $next,
        ]);
    }

    /**
     * Call next patient in queue.
     */
    public function callNext(Request $request)
    {
        $validated = $request->validate([
            'queue_type' => 'required|in:outpatient,specialist,pharmacy,laboratory,radiology,billing,registration',
        ]);

        // Get next patient (priority first, then FIFO)
        $nextQueue = QueueManagement::where('queue_type', $validated['queue_type'])
            ->where('status', 'waiting')
            ->whereDate('created_at', today())
            ->orderBy('priority', 'desc')
            ->orderBy('queue_number')
            ->first();

        if (!$nextQueue) {
            return back()->with('error', 'No patients in queue');
        }

        $nextQueue->call();

        return back()->with('success', 'Calling patient: ' . $nextQueue->token_number);
    }

    /**
     * Skip patient in queue.
     */
    public function skip(Request $request)
    {
        $validated = $request->validate([
            'queue_id' => 'required|exists:queue_managements,id',
        ]);

        $queue = QueueManagement::findOrFail($validated['queue_id']);
        $queue->skip();

        return back()->with('success', 'Patient skipped');
    }

    /**
     * Queue analytics.
     */
    public function analytics(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth());
        $dateTo = $request->get('date_to', now()->endOfMonth());

        $analytics = [
            'total_patients' => QueueManagement::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'completed' => QueueManagement::where('status', 'completed')
                ->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'skipped' => QueueManagement::where('status', 'skipped')
                ->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'avg_wait_time' => QueueManagement::where('status', 'completed')
                ->whereNotNull('called_at')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, called_at)) as avg')
                ->value('avg'),
            'by_type' => QueueManagement::whereBetween('created_at', [$dateFrom, $dateTo])
                ->selectRaw('queue_type, COUNT(*) as count')
                ->groupBy('queue_type')
                ->pluck('count', 'queue_type'),
        ];

        return view('healthcare.queue.analytics', compact('analytics', 'dateFrom', 'dateTo'));
    }
}
