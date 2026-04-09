<?php

namespace App\Services;

use App\Models\OutpatientVisit;
use App\Models\QueueManagement;
use App\Models\QueueSetting;
use App\Models\Patient;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QueueManagementService
{
    /**
     * Register new patient to queue
     */
    public function registerToQueue(array $data): OutpatientVisit
    {
        return DB::transaction(function () use ($data) {
            $queueSetting = QueueSetting::findOrFail($data['queue_setting_id']);

            // Check if queue is open
            if (!$queueSetting->isOpenNow()) {
                throw new Exception('Queue is currently closed. Please check operating hours.');
            }

            // Check max queue limit
            $todayCount = OutpatientVisit::where('queue_setting_id', $queueSetting->id)
                ->whereDate('visit_date', today())
                ->count();

            if ($queueSetting->max_queue_per_day > 0 && $todayCount >= $queueSetting->max_queue_per_day) {
                throw new Exception('Maximum queue limit reached for today.');
            }

            // Create visit
            $visit = OutpatientVisit::create([
                'patient_id' => $data['patient_id'],
                'doctor_id' => $data['doctor_id'] ?? null,
                'queue_setting_id' => $queueSetting->id,
                'visit_date' => $data['visit_date'] ?? today(),
                'visit_time' => $data['visit_time'] ?? now()->format('H:i:s'),
                'visit_type' => $data['visit_type'] ?? 'first_visit',
                'visit_category' => $data['visit_category'] ?? 'general',
                'chief_complaint' => $data['chief_complaint'] ?? null,
                'status' => 'registered',
                'payment_method' => $data['payment_method'] ?? null,
                'is_insurance' => $data['is_insurance'] ?? false,
                'insurance_provider' => $data['insurance_provider'] ?? null,
                'insurance_policy_number' => $data['insurance_policy_number'] ?? null,
            ]);

            // Create queue management entry
            $position = $this->calculateQueuePosition($queueSetting->id);
            $estimatedWait = $position * ($queueSetting->service_time ?? 15);

            QueueManagement::create([
                'outpatient_visit_id' => $visit->id,
                'queue_setting_id' => $queueSetting->id,
                'queue_position' => $position,
                'total_ahead' => max(0, $position - 1),
                'estimated_wait_minutes' => $estimatedWait,
                'status' => 'waiting',
                'enqueued_at' => now(),
                'is_priority' => $data['is_priority'] ?? false,
                'priority_level' => $data['priority_level'] ?? 0,
            ]);

            // Update visit with queue info
            $visit->update([
                'queue_position' => $position,
                'estimated_wait_minutes' => $estimatedWait,
            ]);

            Log::info("Patient registered to queue", [
                'visit_id' => $visit->id,
                'visit_number' => $visit->visit_number,
                'queue_position' => $position,
            ]);

            return $visit;
        });
    }

    /**
     * Call next patient in queue
     */
    public function callNextPatient(int $queueSettingId, int $counterId = null): ?QueueManagement
    {
        return DB::transaction(function () use ($queueSettingId, $counterId) {
            // Get next waiting patient
            $nextQueue = QueueManagement::where('queue_setting_id', $queueSettingId)
                ->where('status', 'waiting')
                ->orderBy('is_priority', 'desc')
                ->orderBy('priority_level', 'desc')
                ->orderBy('queue_position', 'asc')
                ->first();

            if (!$nextQueue) {
                return null;
            }

            // Mark as called
            $nextQueue->update([
                'status' => 'called',
                'counter_id' => $counterId,
                'called_at' => now(),
                'call_count' => DB::raw('call_count + 1'),
                'last_called_at' => now(),
            ]);

            // Update visit status
            $nextQueue->outpatientVisit->markAsCalled();

            Log::info("Patient called from queue", [
                'queue_id' => $nextQueue->id,
                'visit_number' => $nextQueue->outpatientVisit->visit_number,
                'queue_number' => $nextQueue->outpatientVisit->queue_number,
            ]);

            return $nextQueue;
        });
    }

    /**
     * Start serving patient
     */
    public function startServing(int $queueManagementId): QueueManagement
    {
        return DB::transaction(function () use ($queueManagementId) {
            $queue = QueueManagement::findOrFail($queueManagementId);

            $queue->update([
                'status' => 'serving',
                'serving_started_at' => now(),
            ]);

            $queue->outpatientVisit->markAsInConsultation();

            return $queue;
        });
    }

    /**
     * Complete queue service
     */
    public function completeService(int $queueManagementId): QueueManagement
    {
        return DB::transaction(function () use ($queueManagementId) {
            $queue = QueueManagement::findOrFail($queueManagementId);

            $queue->update([
                'status' => 'completed',
                'serving_ended_at' => now(),
            ]);

            $queue->outpatientVisit->markAsCompleted();

            // Recalculate positions for remaining queue
            $this->recalculateQueuePositions($queue->queue_setting_id);

            return $queue;
        });
    }

    /**
     * Skip patient
     */
    public function skipPatient(int $queueManagementId, string $reason = ''): QueueManagement
    {
        return DB::transaction(function () use ($queueManagementId, $reason) {
            $queue = QueueManagement::findOrFail($queueManagementId);

            $queue->update([
                'skip_count' => DB::raw('skip_count + 1'),
                'last_skipped_at' => now(),
                'notes' => $reason,
            ]);

            // Move to end of queue or mark as no-show after 3 skips
            if ($queue->skip_count >= 3) {
                $queue->outpatientVisit->update(['status' => 'no_show']);
                $queue->update(['status' => 'cancelled']);

                // Recalculate positions
                $this->recalculateQueuePositions($queue->queue_setting_id);
            }

            return $queue;
        });
    }

    /**
     * Cancel queue registration
     */
    public function cancelQueue(int $queueManagementId, string $reason = ''): QueueManagement
    {
        return DB::transaction(function () use ($queueManagementId, $reason) {
            $queue = QueueManagement::findOrFail($queueManagementId);

            $queue->update([
                'status' => 'cancelled',
                'notes' => $reason,
            ]);

            $queue->outpatientVisit->update(['status' => 'cancelled']);

            // Recalculate positions
            $this->recalculateQueuePositions($queue->queue_setting_id);

            return $queue;
        });
    }

    /**
     * Get current queue status
     */
    public function getQueueStatus(int $queueSettingId): array
    {
        $queueSetting = QueueSetting::findOrFail($queueSettingId);

        $waiting = QueueManagement::where('queue_setting_id', $queueSettingId)
            ->where('status', 'waiting')
            ->count();

        $serving = QueueManagement::where('queue_setting_id', $queueSettingId)
            ->where('status', 'serving')
            ->count();

        $completed = QueueManagement::where('queue_setting_id', $queueSettingId)
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();

        $avgWaitTime = QueueManagement::where('queue_setting_id', $queueSettingId)
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->whereNotNull('called_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, enqueued_at, called_at)) as avg_wait')
            ->value('avg_wait') ?? 0;

        return [
            'queue_setting' => $queueSetting,
            'is_open' => $queueSetting->isOpenNow(),
            'waiting_count' => $waiting,
            'serving_count' => $serving,
            'completed_count' => $completed,
            'average_wait_time' => round($avgWaitTime, 2),
            'estimated_service_time' => $queueSetting->service_time,
        ];
    }

    /**
     * Get patient queue info
     */
    public function getPatientQueueInfo(int $outpatientVisitId): array
    {
        $queue = QueueManagement::where('outpatient_visit_id', $outpatientVisitId)->firstOrFail();

        $aheadCount = QueueManagement::where('queue_setting_id', $queue->queue_setting_id)
            ->where('status', 'waiting')
            ->where('queue_position', '<', $queue->queue_position)
            ->count();

        $estimatedWait = $aheadCount * ($queue->queueSetting->service_time ?? 15);

        return [
            'queue_number' => $queue->outpatientVisit->queue_number,
            'queue_position' => $queue->queue_position,
            'patients_ahead' => $aheadCount,
            'estimated_wait_minutes' => $estimatedWait,
            'status' => $queue->status,
            'is_priority' => $queue->is_priority,
        ];
    }

    /**
     * Get current display queue (for TV display board)
     */
    public function getDisplayQueue(int $queueSettingId, int $limit = 10): array
    {
        // Currently serving
        $currentlyServing = QueueManagement::where('queue_setting_id', $queueSettingId)
            ->where('status', 'called')
            ->with(['outpatientVisit.patient', 'outpatientVisit.doctor'])
            ->first();

        // Next in queue
        $nextQueue = QueueManagement::where('queue_setting_id', $queueSettingId)
            ->where('status', 'waiting')
            ->orderBy('queue_position')
            ->limit($limit)
            ->with(['outpatientVisit.patient'])
            ->get();

        // Recently completed
        $recentlyCompleted = QueueManagement::where('queue_setting_id', $queueSettingId)
            ->where('status', 'completed')
            ->whereDate('updated_at', today())
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->with(['outpatientVisit.patient'])
            ->get();

        return [
            'currently_serving' => $currentlyServing,
            'next_queue' => $nextQueue,
            'recently_completed' => $recentlyCompleted,
        ];
    }

    /**
     * Calculate queue position
     */
    protected function calculateQueuePosition(int $queueSettingId): int
    {
        return QueueManagement::where('queue_setting_id', $queueSettingId)
            ->whereDate('created_at', today())
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->count() + 1;
    }

    /**
     * Recalculate queue positions
     */
    protected function recalculateQueuePositions(int $queueSettingId): void
    {
        $queues = QueueManagement::where('queue_setting_id', $queueSettingId)
            ->whereDate('created_at', today())
            ->whereIn('status', ['waiting'])
            ->orderBy('queue_position')
            ->get();

        foreach ($queues as $index => $queue) {
            $newPosition = $index + 1;
            $ahead = max(0, $newPosition - 1);
            $estimatedWait = $ahead * ($queue->queueSetting->service_time ?? 15);

            $queue->update([
                'queue_position' => $newPosition,
                'total_ahead' => $ahead,
                'estimated_wait_minutes' => $estimatedWait,
            ]);
        }
    }

    /**
     * Generate queue analytics for a date
     */
    public function generateDailyAnalytics(int $queueSettingId, $date = null): array
    {
        $date = $date ? Carbon::parse($date) : today();

        $visits = OutpatientVisit::where('queue_setting_id', $queueSettingId)
            ->whereDate('visit_date', $date)
            ->get();

        $analytics = [
            'total_registered' => $visits->count(),
            'total_served' => $visits->where('status', 'completed')->count(),
            'total_no_show' => $visits->where('status', 'no_show')->count(),
            'total_cancelled' => $visits->where('status', 'cancelled')->count(),
            'currently_waiting' => $visits->whereIn('status', ['registered', 'waiting', 'called'])->count(),
        ];

        // Calculate wait times
        $completedVisits = $visits->where('status', 'completed')
            ->whereNotNull('actual_wait_minutes');

        if ($completedVisits->count() > 0) {
            $waitTimes = $completedVisits->pluck('actual_wait_minutes')->sort()->values();

            $analytics['avg_wait_time'] = round($waitTimes->avg(), 2);
            $analytics['min_wait_time'] = $waitTimes->min();
            $analytics['max_wait_time'] = $waitTimes->max();
            $analytics['median_wait_time'] = $waitTimes->count() % 2 === 0
                ? ($waitTimes->get($waitTimes->count() / 2 - 1) + $waitTimes->get($waitTimes->count() / 2)) / 2
                : $waitTimes->get(floor($waitTimes->count() / 2));
        } else {
            $analytics['avg_wait_time'] = 0;
            $analytics['min_wait_time'] = 0;
            $analytics['max_wait_time'] = 0;
            $analytics['median_wait_time'] = 0;
        }

        return $analytics;
    }

    /**
     * Get queue performance metrics
     */
    public function getPerformanceMetrics(int $queueSettingId, $startDate = null, $endDate = null): array
    {
        $startDate = $startDate ? Carbon::parse($startDate) : today()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : today()->endOfMonth();

        $visits = OutpatientVisit::where('queue_setting_id', $queueSettingId)
            ->whereBetween('visit_date', [$startDate, $endDate])
            ->get();

        $completedVisits = $visits->where('status', 'completed')
            ->whereNotNull('actual_wait_minutes');

        $totalWaitTime = $completedVisits->sum('actual_wait_minutes');
        $avgWaitTime = $completedVisits->count() > 0
            ? $totalWaitTime / $completedVisits->count()
            : 0;

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'total_patients' => $visits->count(),
            'total_served' => $completedVisits->count(),
            'no_show_rate' => $visits->count() > 0
                ? round(($visits->where('status', 'no_show')->count() / $visits->count()) * 100, 2)
                : 0,
            'cancellation_rate' => $visits->count() > 0
                ? round(($visits->where('status', 'cancelled')->count() / $visits->count()) * 100, 2)
                : 0,
            'average_wait_time_minutes' => round($avgWaitTime, 2),
            'average_service_time_minutes' => round($completedVisits->avg('consultation_duration_minutes') ?? 0, 2),
            'peak_hours' => $this->getPeakHours($visits),
        ];
    }

    /**
     * Get peak hours analysis
     */
    protected function getPeakHours($visits): array
    {
        $hourlyDistribution = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $count = $visits->filter(function ($visit) use ($hour) {
                return $visit->visit_time && (int) substr($visit->visit_time, 0, 2) === $hour;
            })->count();

            $hourlyDistribution[$hour] = $count;
        }

        arsort($hourlyDistribution);

        return array_slice($hourlyDistribution, 0, 5, true);
    }
}
