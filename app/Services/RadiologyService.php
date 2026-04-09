<?php

namespace App\Services;

use App\Models\RadiologyExam;
use App\Models\RadiologyOrder;
use App\Models\RadiologyResult;
use App\Models\PacsIntegration;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RadiologyService
{
    /**
     * Create radiology order
     */
    public function createOrder(array $orderData): RadiologyOrder
    {
        return DB::transaction(function () use ($orderData) {
            $exam = RadiologyExam::findOrFail($orderData['exam_id']);

            $order = RadiologyOrder::create([
                'patient_id' => $orderData['patient_id'],
                'visit_id' => $orderData['visit_id'] ?? null,
                'exam_id' => $exam->id,
                'ordered_by' => $orderData['ordered_by'],
                'order_number' => $this->generateOrderNumber(),
                'order_date' => now(),
                'scheduled_date' => $orderData['scheduled_date'] ?? null,
                'clinical_indication' => $orderData['clinical_indication'],
                'clinical_history' => $orderData['clinical_history'] ?? null,
                'icd10_code' => $orderData['icd10_code'] ?? null,
                'priority' => $orderData['priority'] ?? 'routine',
                'status' => 'ordered',
                'contrast_required' => $exam->requires_contrast,
                'contrast_type' => $exam->contrast_type,
                'special_instructions' => $orderData['special_instructions'] ?? null,
            ]);

            Log::info("Radiology order created", [
                'order_number' => $order->order_number,
                'exam' => $exam->exam_name,
                'priority' => $order->priority,
            ]);

            return $order;
        });
    }

    /**
     * Schedule radiology exam
     */
    public function scheduleExam(int $orderId, array $scheduleData): RadiologyOrder
    {
        return DB::transaction(function () use ($orderId, $scheduleData) {
            $order = RadiologyOrder::findOrFail($orderId);

            $order->update([
                'scheduled_date' => $scheduleData['scheduled_date'],
                'radiologist_id' => $scheduleData['radiologist_id'] ?? null,
                'technologist_id' => $scheduleData['technologist_id'] ?? null,
                'room_number' => $scheduleData['room_number'] ?? null,
                'equipment_id' => $scheduleData['equipment_id'] ?? null,
                'status' => 'scheduled',
            ]);

            return $order;
        });
    }

    /**
     * Start radiology exam
     */
    public function startExam(int $orderId, int $technologistId): RadiologyOrder
    {
        return DB::transaction(function () use ($orderId, $technologistId) {
            $order = RadiologyOrder::findOrFail($orderId);

            $order->update([
                'technologist_id' => $technologistId,
                'started_at' => now(),
                'status' => 'in_progress',
            ]);

            return $order;
        });
    }

    /**
     * Complete radiology exam
     */
    public function completeExam(int $orderId): RadiologyOrder
    {
        return DB::transaction(function () use ($orderId) {
            $order = RadiologyOrder::findOrFail($orderId);

            $order->update([
                'completed_at' => now(),
                'status' => 'completed',
            ]);

            return $order;
        });
    }

    /**
     * Create radiology report
     */
    public function createReport(int $orderId, array $reportData): RadiologyResult
    {
        return DB::transaction(function () use ($orderId, $reportData) {
            $order = RadiologyOrder::findOrFail($orderId);

            $report = RadiologyResult::create([
                'order_id' => $orderId,
                'patient_id' => $order->patient_id,
                'reported_by' => $reportData['reported_by'],
                'report_number' => $this->generateReportNumber(),
                'exam_date' => $order->completed_at ?? now(),
                'reported_at' => now(),
                'clinical_history' => $reportData['clinical_history'] ?? $order->clinical_history,
                'examination_performed' => $order->exam->exam_name,
                'technique' => $reportData['technique'] ?? null,
                'comparison' => $reportData['comparison'] ?? null,
                'findings' => $reportData['findings'],
                'impression' => $reportData['impression'],
                'recommendations' => $reportData['recommendations'] ?? null,
                'status' => 'preliminary',
                'image_urls' => $reportData['image_urls'] ?? null,
                'dicom_study_uid' => $reportData['dicom_study_uid'] ?? null,
                'series_count' => $reportData['series_count'] ?? 0,
                'image_count' => $reportData['image_count'] ?? 0,
            ]);

            // Update order status
            $order->update([
                'radiologist_id' => $reportData['reported_by'],
                'reported_at' => now(),
                'status' => 'reported',
            ]);

            Log::info("Radiology report created", [
                'report_number' => $report->report_number,
                'order_number' => $order->order_number,
            ]);

            return $report;
        });
    }

    /**
     * Verify radiology report
     */
    public function verifyReport(int $reportId, int $verifiedBy): RadiologyResult
    {
        return DB::transaction(function () use ($reportId, $verifiedBy) {
            $report = RadiologyResult::findOrFail($reportId);

            $report->update([
                'verified_by' => $verifiedBy,
                'verified_at' => now(),
                'status' => 'final',
                'is_signed' => true,
                'signed_at' => now(),
            ]);

            return $report;
        });
    }

    /**
     * Record PACS integration
     */
    public function recordPacsIntegration(array $pacsData): PacsIntegration
    {
        return DB::transaction(function () use ($pacsData) {
            $pacs = PacsIntegration::create([
                'patient_id' => $pacsData['patient_id'],
                'order_id' => $pacsData['order_id'] ?? null,
                'result_id' => $pacsData['result_id'] ?? null,
                'study_instance_uid' => $pacsData['study_instance_uid'],
                'accession_number' => $pacsData['accession_number'] ?? null,
                'modality' => $pacsData['modality'],
                'study_description' => $pacsData['study_description'] ?? null,
                'study_date' => $pacsData['study_date'] ?? today(),
                'study_time' => $pacsData['study_time'] ?? now()->format('H:i:s'),
                'series_count' => $pacsData['series_count'] ?? 0,
                'image_count' => $pacsData['image_count'] ?? 0,
                'series_details' => $pacsData['series_details'] ?? null,
                'pacs_server' => $pacsData['pacs_server'] ?? null,
                'pacs_ae_title' => $pacsData['pacs_ae_title'] ?? null,
                'storage_path' => $pacsData['storage_path'] ?? null,
                'storage_size_bytes' => $pacsData['storage_size_bytes'] ?? 0,
                'viewer_url' => $pacsData['viewer_url'] ?? null,
                'thumbnail_url' => $pacsData['thumbnail_url'] ?? null,
                'image_urls' => $pacsData['image_urls'] ?? null,
                'status' => 'received',
                'received_at' => now(),
                'dicom_metadata' => $pacsData['dicom_metadata'] ?? null,
            ]);

            Log::info("PACS integration recorded", [
                'study_uid' => $pacs->study_instance_uid,
                'modality' => $pacs->modality,
                'image_count' => $pacs->image_count,
            ]);

            return $pacs;
        });
    }

    /**
     * Get scheduled exams
     */
    public function getScheduledExams($date = null): array
    {
        $date = $date ? \Carbon\Carbon::parse($date) : today();

        return RadiologyOrder::whereDate('scheduled_date', $date)
            ->where('status', 'scheduled')
            ->with(['patient', 'exam', 'radiologist', 'technologist'])
            ->orderBy('scheduled_date')
            ->get()
            ->toArray();
    }

    /**
     * Get exams in progress
     */
    public function getExamsInProgress(): array
    {
        return RadiologyOrder::where('status', 'in_progress')
            ->with(['patient', 'exam', 'technologist'])
            ->get()
            ->toArray();
    }

    /**
     * Get pending reports
     */
    public function getPendingReports(): array
    {
        return RadiologyOrder::where('status', 'completed')
            ->whereDoesntHave('result')
            ->with(['patient', 'exam'])
            ->get()
            ->toArray();
    }

    /**
     * Get radiology dashboard data
     */
    public function getDashboardData(): array
    {
        return [
            'orders_today' => RadiologyOrder::whereDate('order_date', today())->count(),
            'scheduled_today' => RadiologyOrder::whereDate('scheduled_date', today())
                ->where('status', 'scheduled')->count(),
            'in_progress' => RadiologyOrder::where('status', 'in_progress')->count(),
            'completed_today' => RadiologyOrder::whereDate('completed_at', today())->count(),
            'pending_reports' => RadiologyOrder::where('status', 'completed')
                ->whereDoesntHave('result')->count(),
            'stat_orders' => RadiologyOrder::where('priority', 'stat')
                ->whereIn('status', ['ordered', 'scheduled', 'in_progress'])->count(),
            'exams_by_modality' => RadiologyOrder::whereIn('status', ['completed', 'reported'])
                ->whereDate('created_at', today())
                ->with('exam')
                ->get()
                ->groupBy('exam.modality')
                ->map->count()
                ->toArray(),
        ];
    }

    /**
     * Generate order number
     */
    protected function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'RAD-ORD-' . $date;

        $lastOrder = RadiologyOrder::where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $newNumber;
    }

    /**
     * Generate report number
     */
    protected function generateReportNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'RAD-RPT-' . $date;

        $lastReport = RadiologyResult::where('report_number', 'like', $prefix . '%')
            ->orderBy('report_number', 'desc')
            ->first();

        if ($lastReport) {
            $lastNumber = (int) substr($lastReport->report_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $newNumber;
    }
}
