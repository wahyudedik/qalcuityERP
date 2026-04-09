<?php

namespace App\Services;

use App\Models\OperatingRoom;
use App\Models\SurgerySchedule;
use App\Models\SurgeryTeam;
use App\Models\MedicalEquipment;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OperatingRoomScheduler
{
    /**
     * Schedule surgery with conflict detection
     */
    public function scheduleSurgery(array $surgeryData): SurgerySchedule
    {
        return DB::transaction(function () use ($surgeryData) {
            // Check for conflicts
            $conflicts = $this->detectConflicts(
                $surgeryData['scheduled_date'],
                $surgeryData['scheduled_start_time'],
                $surgeryData['scheduled_end_time'],
                $surgeryData['operating_room_id'],
                $surgeryData['surgeon_id'],
                $surgeryData['anesthesiologist_id'] ?? null
            );

            if (!empty($conflicts['room_conflicts'])) {
                throw new Exception('Operating room is not available at the selected time.');
            }

            if (!empty($conflicts['surgeon_conflicts'])) {
                throw new Exception('Surgeon has another surgery scheduled at this time.');
            }

            if (!empty($conflicts['anesthesiologist_conflicts'])) {
                throw new Exception('Anesthesiologist has another surgery scheduled at this time.');
            }

            // Create surgery schedule
            $surgery = SurgerySchedule::create([
                'patient_id' => $surgeryData['patient_id'],
                'surgeon_id' => $surgeryData['surgeon_id'],
                'operating_room_id' => $surgeryData['operating_room_id'],
                'admission_id' => $surgeryData['admission_id'] ?? null,
                'surgery_number' => $this->generateSurgeryNumber(),
                'scheduled_date' => $surgeryData['scheduled_date'],
                'scheduled_start_time' => $surgeryData['scheduled_start_time'],
                'scheduled_end_time' => $surgeryData['scheduled_end_time'],
                'estimated_duration' => $surgeryData['estimated_duration'] ?? 0,
                'procedure_name' => $surgeryData['procedure_name'],
                'procedure_code' => $surgeryData['procedure_code'] ?? null,
                'procedure_description' => $surgeryData['procedure_description'] ?? null,
                'surgery_type' => $surgeryData['surgery_type'] ?? 'elective',
                'icd10_code' => $surgeryData['icd10_code'] ?? null,
                'pre_operative_diagnosis' => $surgeryData['pre_operative_diagnosis'] ?? null,
                'anesthesiologist_id' => $surgeryData['anesthesiologist_id'] ?? null,
                'anesthesia_type' => $surgeryData['anesthesia_type'] ?? null,
                'priority' => $surgeryData['priority'] ?? 'elective',
                'preoperative_notes' => $surgeryData['preoperative_notes'] ?? null,
                'status' => 'scheduled',
            ]);

            // Add surgery team
            if (!empty($surgeryData['team'])) {
                foreach ($surgeryData['team'] as $teamMember) {
                    $this->addTeamMember($surgery->id, $teamMember);
                }
            }

            Log::info("Surgery scheduled", [
                'surgery_number' => $surgery->surgery_number,
                'date' => $surgery->scheduled_date,
                'room' => $surgery->operating_room_id,
                'surgeon' => $surgery->surgeon_id,
            ]);

            return $surgery;
        });
    }

    /**
     * Detect scheduling conflicts
     */
    public function detectConflicts($date, $startTime, $endTime, $roomId, $surgeonId, $anesthesiologistId = null): array
    {
        $conflicts = [
            'room_conflicts' => [],
            'surgeon_conflicts' => [],
            'anesthesiologist_conflicts' => [],
        ];

        // Check room conflicts
        $roomConflicts = SurgerySchedule::where('operating_room_id', $roomId)
            ->whereDate('scheduled_date', $date)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('scheduled_start_time', [$startTime, $endTime])
                    ->orWhereBetween('scheduled_end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('scheduled_start_time', '<=', $startTime)
                            ->where('scheduled_end_time', '>=', $endTime);
                    });
            })
            ->with('patient')
            ->get();

        if ($roomConflicts->isNotEmpty()) {
            $conflicts['room_conflicts'] = $roomConflicts->map(function ($surgery) {
                return [
                    'surgery_number' => $surgery->surgery_number,
                    'patient' => $surgery->patient->name ?? 'Unknown',
                    'procedure' => $surgery->procedure_name,
                    'time' => "{$surgery->scheduled_start_time} - {$surgery->scheduled_end_time}",
                ];
            })->toArray();
        }

        // Check surgeon conflicts
        $surgeonConflicts = SurgerySchedule::where('surgeon_id', $surgeonId)
            ->whereDate('scheduled_date', $date)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('scheduled_start_time', [$startTime, $endTime])
                    ->orWhereBetween('scheduled_end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('scheduled_start_time', '<=', $startTime)
                            ->where('scheduled_end_time', '>=', $endTime);
                    });
            })
            ->get();

        if ($surgeonConflicts->isNotEmpty()) {
            $conflicts['surgeon_conflicts'] = $surgeonConflicts->map(function ($surgery) {
                return [
                    'surgery_number' => $surgery->surgery_number,
                    'procedure' => $surgery->procedure_name,
                    'room' => $surgery->operatingRoom->room_number ?? 'Unknown',
                    'time' => "{$surgery->scheduled_start_time} - {$surgery->scheduled_end_time}",
                ];
            })->toArray();
        }

        // Check anesthesiologist conflicts
        if ($anesthesiologistId) {
            $anesthesiologistConflicts = SurgerySchedule::where('anesthesiologist_id', $anesthesiologistId)
                ->whereDate('scheduled_date', $date)
                ->where('status', '!=', 'cancelled')
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->whereBetween('scheduled_start_time', [$startTime, $endTime])
                        ->orWhereBetween('scheduled_end_time', [$startTime, $endTime])
                        ->orWhere(function ($q2) use ($startTime, $endTime) {
                            $q2->where('scheduled_start_time', '<=', $startTime)
                                ->where('scheduled_end_time', '>=', $endTime);
                        });
                })
                ->get();

            if ($anesthesiologistConflicts->isNotEmpty()) {
                $conflicts['anesthesiologist_conflicts'] = $anesthesiologistConflicts->map(function ($surgery) {
                    return [
                        'surgery_number' => $surgery->surgery_number,
                        'procedure' => $surgery->procedure_name,
                        'room' => $surgery->operatingRoom->room_number ?? 'Unknown',
                        'time' => "{$surgery->scheduled_start_time} - {$surgery->scheduled_end_time}",
                    ];
                })->toArray();
            }
        }

        return $conflicts;
    }

    /**
     * Start surgery
     */
    public function startSurgery(int $surgeryId): SurgerySchedule
    {
        return DB::transaction(function () use ($surgeryId) {
            $surgery = SurgerySchedule::findOrFail($surgeryId);

            $surgery->update([
                'actual_start_time' => now()->format('H:i:s'),
                'status' => 'in_progress',
            ]);

            // Update OR status
            $surgery->operatingRoom->update([
                'status' => 'occupied',
            ]);

            Log::info("Surgery started", [
                'surgery_number' => $surgery->surgery_number,
                'start_time' => $surgery->actual_start_time,
            ]);

            return $surgery;
        });
    }

    /**
     * Complete surgery
     */
    public function completeSurgery(int $surgeryId, array $completionData): SurgerySchedule
    {
        return DB::transaction(function () use ($surgeryId, $completionData) {
            $surgery = SurgerySchedule::findOrFail($surgeryId);

            $endTime = now()->format('H:i:s');
            $startTime = now()->parse($surgery->actual_start_time);
            $endTimeParsed = now()->parse($endTime);
            $duration = $startTime->diffInMinutes($endTimeParsed);

            $surgery->update([
                'actual_end_time' => $endTime,
                'actual_duration' => $duration,
                'post_operative_diagnosis' => $completionData['post_operative_diagnosis'] ?? null,
                'intraoperative_notes' => $completionData['intraoperative_notes'] ?? null,
                'postoperative_notes' => $completionData['postoperative_notes'] ?? null,
                'complications' => $completionData['complications'] ?? null,
                'surgeon_notes' => $completionData['surgeon_notes'] ?? null,
                'outcome' => $completionData['outcome'] ?? 'successful',
                'blood_loss_ml' => $completionData['blood_loss_ml'] ?? null,
                'implants_used' => $completionData['implants_used'] ?? null,
                'status' => 'completed',
            ]);

            // Update OR status to cleaning
            $surgery->operatingRoom->update([
                'status' => 'cleaning',
            ]);

            // Log OR utilization
            $this->logUtilization($surgery);

            Log::info("Surgery completed", [
                'surgery_number' => $surgery->surgery_number,
                'duration' => $duration,
                'outcome' => $surgery->outcome,
            ]);

            return $surgery;
        });
    }

    /**
     * Add team member to surgery
     */
    public function addTeamMember(int $surgeryId, array $teamData): SurgeryTeam
    {
        return SurgeryTeam::create([
            'surgery_id' => $surgeryId,
            'staff_id' => $teamData['staff_id'],
            'staff_type' => $teamData['staff_type'] ?? 'staff',
            'role' => $teamData['role'],
            'notes' => $teamData['notes'] ?? null,
        ]);
    }

    /**
     * Schedule equipment maintenance
     */
    public function scheduleMaintenance(int $equipmentId, array $maintenanceData)
    {
        return DB::transaction(function () use ($equipmentId, $maintenanceData) {
            $equipment = MedicalEquipment::findOrFail($equipmentId);

            // Update equipment status
            $equipment->update([
                'status' => 'maintenance',
                'last_maintenance' => $maintenanceData['maintenance_date'] ?? today(),
                'next_maintenance' => $maintenanceData['next_maintenance_date'] ?? null,
                'maintenance_notes' => $maintenanceData['notes'] ?? null,
            ]);

            Log::info("Equipment maintenance scheduled", [
                'equipment_code' => $equipment->equipment_code,
                'maintenance_date' => $equipment->last_maintenance,
            ]);

            return $equipment;
        });
    }

    /**
     * Complete equipment maintenance
     */
    public function completeMaintenance(int $equipmentId, array $maintenanceData)
    {
        return DB::transaction(function () use ($equipmentId, $maintenanceData) {
            $equipment = MedicalEquipment::findOrFail($equipmentId);

            $equipment->update([
                'status' => 'available',
                'condition' => $maintenanceData['condition'] ?? 'good',
                'next_maintenance' => $maintenanceData['next_maintenance_date'] ??
                    now()->addDays($equipment->maintenance_interval_days),
            ]);

            Log::info("Equipment maintenance completed", [
                'equipment_code' => $equipment->equipment_code,
                'condition' => $equipment->condition,
            ]);

            return $equipment;
        });
    }

    /**
     * Get equipment due for maintenance
     */
    public function getEquipmentDueForMaintenance(int $daysAhead = 7): array
    {
        $dueDate = now()->addDays($daysAhead);

        return MedicalEquipment::where('next_maintenance', '<=', $dueDate)
            ->where('is_active', true)
            ->whereNotIn('status', ['retired', 'missing'])
            ->orderBy('next_maintenance')
            ->get()
            ->map(function ($equipment) {
                $equipment->days_until_due = now()->diffInDays($equipment->next_maintenance, false);
                return $equipment;
            })
            ->toArray();
    }

    /**
     * Get OR utilization analytics
     */
    public function getUtilizationAnalytics($startDate, $endDate): array
    {
        $rooms = OperatingRoom::where('is_active', true)->get();

        $analytics = [
            'overall_utilization' => 0,
            'rooms' => [],
            'total_surgeries' => 0,
            'average_duration' => 0,
            'by_type' => [],
            'by_status' => [],
        ];

        $totalUtilization = 0;
        $roomCount = 0;

        foreach ($rooms as $room) {
            $utilization = $room->getUtilizationRate($startDate, $endDate);
            $totalUtilization += $utilization;
            $roomCount++;

            $surgeries = SurgerySchedule::where('operating_room_id', $room->id)
                ->whereBetween('scheduled_date', [$startDate, $endDate])
                ->get();

            $analytics['rooms'][] = [
                'room_number' => $room->room_number,
                'room_name' => $room->room_name,
                'type' => $room->type,
                'utilization_rate' => $utilization,
                'total_surgeries' => $surgeries->count(),
                'completed_surgeries' => $surgeries->where('status', 'completed')->count(),
                'cancelled_surgeries' => $surgeries->where('status', 'cancelled')->count(),
                'average_duration' => $surgeries->where('status', 'completed')->avg('actual_duration') ?? 0,
            ];
        }

        $analytics['overall_utilization'] = $roomCount > 0 ? round($totalUtilization / $roomCount, 2) : 0;
        $analytics['total_surgeries'] = SurgerySchedule::whereBetween('scheduled_date', [$startDate, $endDate])->count();
        $analytics['average_duration'] = SurgerySchedule::whereBetween('scheduled_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->avg('actual_duration') ?? 0;

        // By type
        $analytics['by_type'] = SurgerySchedule::whereBetween('scheduled_date', [$startDate, $endDate])
            ->groupBy('surgery_type')
            ->selectRaw('surgery_type, count(*) as count')
            ->pluck('count', 'surgery_type')
            ->toArray();

        // By status
        $analytics['by_status'] = SurgerySchedule::whereBetween('scheduled_date', [$startDate, $endDate])
            ->groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->pluck('count', 'status')
            ->toArray();

        return $analytics;
    }

    /**
     * Get surgery dashboard
     */
    public function getDashboardData(): array
    {
        return [
            'surgeries_today' => SurgerySchedule::whereDate('scheduled_date', today())->count(),
            'in_progress' => SurgerySchedule::where('status', 'in_progress')->count(),
            'completed_today' => SurgerySchedule::where('status', 'completed')
                ->whereDate('updated_at', today())->count(),
            'available_rooms' => OperatingRoom::available()->count(),
            'equipment_due_maintenance' => $this->getEquipmentDueForMaintenance(7),
            'today_schedule' => SurgerySchedule::whereDate('scheduled_date', today())
                ->with(['patient', 'operatingRoom', 'surgeon'])
                ->orderBy('scheduled_start_time')
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Generate surgery number
     */
    protected function generateSurgeryNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'SUR-' . $date;

        $last = SurgerySchedule::where('surgery_number', 'like', $prefix . '%')
            ->orderBy('surgery_number', 'desc')
            ->first();

        return $prefix . '-' . str_pad(
            $last ? (int) substr($last->surgery_number, -4) + 1 : 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Log OR utilization
     */
    protected function logUtilization(SurgerySchedule $surgery): void
    {
        \App\Models\OrUtilizationLog::create([
            'operating_room_id' => $surgery->operating_room_id,
            'surgery_id' => $surgery->id,
            'log_date' => $surgery->scheduled_date,
            'start_time' => $surgery->actual_start_time,
            'end_time' => $surgery->actual_end_time,
            'duration_minutes' => $surgery->actual_duration,
            'utilization_type' => 'surgery',
            'case_number' => $surgery->surgery_number,
        ]);
    }
}
