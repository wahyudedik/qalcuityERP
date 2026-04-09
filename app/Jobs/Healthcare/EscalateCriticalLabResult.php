<?php

namespace App\Jobs\Healthcare;

use App\Models\LabResult;
use App\Models\ErpNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EscalateCriticalLabResult implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    protected $resultId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $resultId)
    {
        $this->resultId = $resultId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $result = LabResult::with(['labOrder', 'labOrder.visit.patient'])->find($this->resultId);

        if (!$result || !$result->requires_urgent_review) {
            return;
        }

        // Check if result has been reviewed
        if ($result->verified_at) {
            return;
        }

        Log::warning("Critical lab result not reviewed - escalating", [
            'result_id' => $result->id,
            'patient_id' => $result->sample->patient_id ?? null,
            'parameter' => $result->parameter_name,
        ]);

        // Escalate to department head
        $order = $result->sample->labOrder;
        if ($order) {
            $doctor = $order->orderedBy;
            if ($doctor && $doctor->department_head_id) {
                ErpNotification::create([
                    'user_id' => $doctor->department_head_id,
                    'type' => 'critical_lab_result_escalation',
                    'title' => '🚨 ESCALATION - Hasil Lab Kritis Belum Direview',
                    'body' => "Hasil lab kritis untuk pasien {$result->sample->patient->full_name} " .
                        "belum direview lebih dari 15 menit.\n" .
                        "Parameter: {$result->parameter_name}\n" .
                        "Nilai: {$result->result_value} {$result->unit}",
                    'data' => [
                        'result_id' => $result->id,
                        'original_doctor_id' => $doctor->id,
                        'escalation_level' => 1,
                        'patient_id' => $result->sample->patient_id,
                    ],
                    'is_read' => false,
                    'priority' => 'critical',
                ]);
            }
        }

        // Update escalation status
        $result->update([
            'escalation_status' => 'escalated',
            'escalated_at' => now(),
        ]);
    }
}
