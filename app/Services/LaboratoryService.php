<?php

namespace App\Services;

use App\Models\LabTestCatalog;
use App\Models\LabSample;
use App\Models\LabResultDetail;
use App\Models\LabEquipment;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LaboratoryService
{
    /**
     * Create lab sample from order
     */
    public function createSample(array $sampleData): LabSample
    {
        return DB::transaction(function () use ($sampleData) {
            $sample = LabSample::create([
                'lab_order_id' => $sampleData['lab_order_id'],
                'collected_by' => $sampleData['collected_by'],
                'sample_number' => $this->generateSampleNumber(),
                'sample_type' => $sampleData['sample_type'],
                'container_type' => $sampleData['container_type'] ?? null,
                'collection_time' => $sampleData['collection_time'] ?? now(),
                'collection_site' => $sampleData['collection_site'] ?? null,
                'collection_notes' => $sampleData['collection_notes'] ?? null,
                'status' => 'collected',
            ]);

            Log::info("Lab sample created", [
                'sample_number' => $sample->sample_number,
                'sample_type' => $sample->sample_type,
            ]);

            return $sample;
        });
    }

    /**
     * Receive sample in lab
     */
    public function receiveSample(int $sampleId, int $receivedBy, string $condition = 'acceptable'): LabSample
    {
        return DB::transaction(function () use ($sampleId, $receivedBy, $condition) {
            $sample = LabSample::findOrFail($sampleId);

            $updateData = [
                'received_by' => $receivedBy,
                'received_at' => now(),
                'sample_condition' => $condition,
                'status' => $condition === 'acceptable' ? 'received' : 'rejected',
            ];

            if ($condition !== 'acceptable') {
                $updateData['rejection_reason'] = "Sample condition: {$condition}";
            }

            $sample->update($updateData);

            Log::info("Lab sample received", [
                'sample_number' => $sample->sample_number,
                'condition' => $condition,
            ]);

            return $sample;
        });
    }

    /**
     * Start processing sample
     */
    public function startProcessing(int $sampleId, int $testedBy): LabSample
    {
        return DB::transaction(function () use ($sampleId, $testedBy) {
            $sample = LabSample::findOrFail($sampleId);

            // Check if equipment is available
            $this->checkEquipmentAvailability($sample->sample_type);

            $sample->update([
                'tested_by' => $testedBy,
                'processing_started_at' => now(),
                'status' => 'in_progress',
            ]);

            return $sample;
        });
    }

    /**
     * Add lab result
     */
    public function addLabResult(int $sampleId, array $resultData): LabResultDetail
    {
        return DB::transaction(function () use ($sampleId, $resultData) {
            $sample = LabSample::findOrFail($sampleId);

            // Determine flag based on reference range
            $flag = $this->determineFlag(
                $resultData['result_value'],
                $resultData['reference_range_min'] ?? null,
                $resultData['reference_range_max'] ?? null
            );

            $result = LabResultDetail::create([
                'sample_id' => $sampleId,
                'test_id' => $resultData['test_id'] ?? null,
                'parameter_name' => $resultData['parameter_name'],
                'parameter_code' => $resultData['parameter_code'] ?? null,
                'test_method' => $resultData['test_method'] ?? null,
                'result_value' => $resultData['result_value'],
                'unit' => $resultData['unit'] ?? null,
                'reference_range_min' => $resultData['reference_range_min'] ?? null,
                'reference_range_max' => $resultData['reference_range_max'] ?? null,
                'reference_range_display' => $resultData['reference_range_display'] ?? null,
                'flag' => $flag,
                'is_critical' => in_array($flag, ['critical_low', 'critical_high']),
                'is_abnormal' => $flag !== 'normal',
                'validation_status' => 'preliminary',
                'interpretation' => $resultData['interpretation'] ?? null,
                'clinical_notes' => $resultData['clinical_notes'] ?? null,
            ]);

            // Check for critical values and alert
            if ($result->is_critical) {
                $this->handleCriticalValue($result);
            }

            return $result;
        });
    }

    /**
     * Verify lab result
     */
    public function verifyResult(int $resultId, int $verifiedBy): LabResultDetail
    {
        return DB::transaction(function () use ($resultId, $verifiedBy) {
            $result = LabResultDetail::findOrFail($resultId);

            $result->update([
                'verified_by' => $verifiedBy,
                'verified_at' => now(),
                'validation_status' => 'verified',
            ]);

            return $result;
        });
    }

    /**
     * Complete sample processing
     */
    public function completeSample(int $sampleId): LabSample
    {
        return DB::transaction(function () use ($sampleId) {
            $sample = LabSample::findOrFail($sampleId);

            $sample->update([
                'processing_completed_at' => now(),
                'status' => 'completed',
            ]);

            return $sample;
        });
    }

    /**
     * Add QC log
     */
    public function addQCLog(array $qcData)
    {
        return DB::transaction(function () use ($qcData) {
            // Check if QC is in control
            $qcStatus = $this->evaluateQCStatus(
                $qcData['control_results'],
                $qcData['target_value'],
                $qcData['acceptable_range_min'],
                $qcData['acceptable_range_max']
            );

            $qcLog = \App\Models\LabQcLog::create([
                'equipment_id' => $qcData['equipment_id'] ?? null,
                'test_id' => $qcData['test_id'] ?? null,
                'performed_by' => $qcData['performed_by'],
                'qc_lot_number' => $qcData['qc_lot_number'] ?? null,
                'qc_date' => $qcData['qc_date'] ?? today(),
                'control_results' => $qcData['control_results'],
                'control_level' => $qcData['control_level'],
                'target_value' => $qcData['target_value'],
                'acceptable_range_min' => $qcData['acceptable_range_min'],
                'acceptable_range_max' => $qcData['acceptable_range_max'],
                'qc_status' => $qcStatus,
                'corrective_actions' => $qcData['corrective_actions'] ?? null,
                'westgard_rules_violated' => $qcData['westgard_rules_violated'] ?? null,
            ]);

            // If QC is out of control, prevent testing
            if ($qcStatus === 'out_of_control') {
                throw new Exception('QC is out of control. Testing cannot proceed.');
            }

            return $qcLog;
        });
    }

    /**
     * Add equipment calibration
     */
    public function addCalibration(int $equipmentId, array $calibrationData)
    {
        return DB::transaction(function () use ($equipmentId, $calibrationData) {
            $equipment = LabEquipment::findOrFail($equipmentId);

            $calibration = \App\Models\LabEquipmentCalibration::create([
                'equipment_id' => $equipmentId,
                'performed_by' => $calibrationData['performed_by'],
                'verified_by' => $calibrationData['verified_by'] ?? null,
                'calibration_date' => $calibrationData['calibration_date'] ?? today(),
                'calibration_procedure' => $calibrationData['calibration_procedure'] ?? null,
                'calibration_results' => $calibrationData['calibration_results'] ?? null,
                'calibration_status' => $calibrationData['calibration_status'],
                'standard_reference' => $calibrationData['standard_reference'] ?? null,
                'standards_used' => $calibrationData['standards_used'] ?? null,
                'next_calibration_date' => $calibrationData['next_calibration_date'],
                'certificate_number' => $calibrationData['certificate_number'] ?? null,
                'certificate_path' => $calibrationData['certificate_path'] ?? null,
            ]);

            // Update equipment calibration dates
            $equipment->update([
                'last_calibration_date' => $calibrationData['calibration_date'] ?? today(),
                'next_calibration_date' => $calibrationData['next_calibration_date'],
                'status' => $calibrationData['calibration_status'] === 'passed' ? 'operational' : 'out_of_service',
            ]);

            return $calibration;
        });
    }

    /**
     * Get equipment due for calibration
     */
    public function getEquipmentDueForCalibration(int $daysAhead = 30): array
    {
        $dueDate = now()->addDays($daysAhead);

        return LabEquipment::where('next_calibration_date', '<=', $dueDate)
            ->where('is_active', true)
            ->where('status', '!=', 'decommissioned')
            ->orderBy('next_calibration_date', 'asc')
            ->get()
            ->map(function ($equipment) {
                $equipment->days_until_due = now()->diffInDays($equipment->next_calibration_date, false);
                return $equipment;
            })
            ->toArray();
    }

    /**
     * Generate sample number
     */
    protected function generateSampleNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'SAMPLE-' . $date;

        $lastSample = LabSample::where('sample_number', 'like', $prefix . '%')
            ->orderBy('sample_number', 'desc')
            ->first();

        if ($lastSample) {
            $lastNumber = (int) substr($lastSample->sample_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $newNumber;
    }

    /**
     * Determine result flag
     */
    protected function determineFlag($value, $min = null, $max = null): string
    {
        if ($min === null || $max === null) {
            return 'normal';
        }

        $numericValue = is_numeric($value) ? (float) $value : null;
        $numericMin = is_numeric($min) ? (float) $min : null;
        $numericMax = is_numeric($max) ? (float) $max : null;

        if ($numericValue === null || $numericMin === null || $numericMax === null) {
            return 'normal';
        }

        // Critical ranges (typically 20% beyond normal)
        $criticalLow = $numericMin * 0.8;
        $criticalHigh = $numericMax * 1.2;

        if ($numericValue < $criticalLow) {
            return 'critical_low';
        } elseif ($numericValue > $criticalHigh) {
            return 'critical_high';
        } elseif ($numericValue < $numericMin) {
            return 'low';
        } elseif ($numericValue > $numericMax) {
            return 'high';
        }

        return 'normal';
    }

    /**
     * Handle critical value
     */
    protected function handleCriticalValue(LabResultDetail $result): void
    {
        Log::critical("Critical lab value detected", [
            'sample_id' => $result->sample_id,
            'parameter' => $result->parameter_name,
            'value' => $result->result_value,
            'flag' => $result->flag,
        ]);

        // Send immediate notification to ordering doctor
        $this->sendCriticalValueNotification($result);

        // Flag in patient record
        $this->flagCriticalResultInPatientRecord($result);

        // Require immediate verification
        $this->markRequiresUrgentVerification($result);
    }

    /**
     * Send critical value notification to doctor
     */
    protected function sendCriticalValueNotification(LabResultDetail $result): void
    {
        $sample = $result->sample;
        if (!$sample)
            return;

        $order = $sample->labOrder;
        if (!$order)
            return;

        $doctor = $order->orderedBy;
        if (!$doctor)
            return;

        // Create urgent notification
        \App\Models\ErpNotification::create([
            'user_id' => $doctor->user_id ?? null,
            'type' => 'critical_lab_result',
            'title' => '⚠️ HASIL LAB KRITIS - Perlu Tindakan Segera',
            'body' => "Pasien: {$sample->patient->full_name}\n" .
                "Parameter: {$result->parameter_name}\n" .
                "Nilai: {$result->result_value} {$result->unit}\n" .
                "Status: KRITIS ({$result->flag})\n" .
                "Waktu: " . now()->format('d/m/Y H:i'),
            'data' => [
                'lab_order_id' => $order->id,
                'sample_id' => $sample->id,
                'result_id' => $result->id,
                'patient_id' => $sample->patient_id,
                'priority' => 'critical',
                'requires_acknowledgment' => true,
                'escalation_minutes' => 15,
            ],
            'is_read' => false,
            'priority' => 'critical',
        ]);

        // TODO: Send SMS for life-threatening values
        // TODO: Send WhatsApp notification
        // TODO: Push notification to doctor mobile app
    }

    /**
     * Flag critical result in patient record
     */
    protected function flagCriticalResultInPatientRecord(LabResultDetail $result): void
    {
        $sample = $result->sample;
        if (!$sample)
            return;

        // Add flag to patient's medical record
        $visit = $sample->labOrder->visit ?? null;
        if ($visit) {
            // Update visit with critical flag
            $visit->update([
                'has_critical_results' => true,
                'critical_results_count' => ($visit->critical_results_count ?? 0) + 1,
            ]);
        }

        // Log in audit trail
        \App\Models\ActivityLog::log(
            $sample->patient,
            'critical_result_flagged',
            [
                'parameter' => $result->parameter_name,
                'value' => $result->result_value,
                'flag' => $result->flag,
                'lab_order_id' => $sample->labOrder->id,
            ],
            'laboratory'
        );
    }

    /**
     * Mark result as requiring urgent verification
     */
    protected function markRequiresUrgentVerification(LabResultDetail $result): void
    {
        $result->update([
            'requires_urgent_review' => true,
            'review_deadline' => now()->addMinutes(15),
            'escalation_status' => 'pending',
        ]);

        // Schedule escalation job if not reviewed within 15 minutes
        \Illuminate\Support\Facades\Bus::dispatch(
            new \App\Jobs\Healthcare\EscalateCriticalLabResult($result->id)
        )->delay(now()->addMinutes(15));
    }

    /**
     * Check equipment availability
     */
    protected function checkEquipmentAvailability(string $sampleType): void
    {
        $operationalEquipment = LabEquipment::where('status', 'operational')
            ->where('is_active', true)
            ->count();

        if ($operationalEquipment === 0) {
            throw new Exception('No operational equipment available for testing.');
        }
    }

    /**
     * Evaluate QC status
     */
    protected function evaluateQCStatus($controlResults, $target, $rangeMin, $rangeMax): string
    {
        // Simple evaluation - can be enhanced with Westgard rules
        $numericResults = array_map('floatval', (array) $controlResults);
        $numericTarget = floatval($target);
        $numericMin = floatval($rangeMin);
        $numericMax = floatval($rangeMax);

        $allInRange = true;
        $anyCritical = false;

        foreach ($numericResults as $result) {
            if ($result < $numericMin || $result > $numericMax) {
                $allInRange = false;
            }

            // Check if significantly out of range (>2 SD)
            $deviation = abs($result - $numericTarget);
            $range = $numericMax - $numericMin;
            if ($deviation > ($range * 0.5)) {
                $anyCritical = true;
            }
        }

        if ($anyCritical) {
            return 'out_of_control';
        } elseif (!$allInRange) {
            return 'warning';
        }

        return 'in_control';
    }

    /**
     * Get lab dashboard data
     */
    public function getDashboardData(): array
    {
        return [
            'samples_collected_today' => LabSample::whereDate('collection_time', today())->count(),
            'samples_in_progress' => LabSample::where('status', 'in_progress')->count(),
            'samples_completed_today' => LabSample::where('status', 'completed')
                ->whereDate('processing_completed_at', today())->count(),
            'pending_verification' => LabResultDetail::where('validation_status', 'preliminary')->count(),
            'critical_results' => LabResultDetail::where('is_critical', true)
                ->where('validation_status', 'preliminary')->count(),
            'equipment_operational' => LabEquipment::where('status', 'operational')->count(),
            'equipment_due_calibration' => $this->getEquipmentDueForCalibration(7),
            'qc_status_today' => \App\Models\LabQcLog::whereDate('qc_date', today())
                ->groupBy('qc_status')
                ->selectRaw('qc_status, count(*) as count')
                ->pluck('count', 'qc_status')
                ->toArray(),
        ];
    }
}
