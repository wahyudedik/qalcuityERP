<?php

namespace App\Services;

use App\Models\PrintJob;
use Illuminate\Support\Facades\DB;

class PrintJobService
{
    /**
     * Create new print job
     */
    public function createJob(array $data): PrintJob
    {
        return DB::transaction(function () use ($data) {
            $job = new PrintJob;
            $job->tenant_id = auth()->user()->tenant_id;
            $job->job_number = $this->generateJobNumber();
            $job->customer_id = $data['customer_id'] ?? null;
            $job->job_name = $data['job_name'];
            $job->description = $data['description'] ?? null;
            $job->product_type = $data['product_type'];
            $job->status = 'queued';
            $job->priority = $data['priority'] ?? 'normal';
            $job->due_date = $data['due_date'] ?? null;
            $job->quantity = $data['quantity'] ?? 0;
            $job->paper_type = $data['paper_type'] ?? null;
            $job->paper_size_width = $data['paper_size_width'] ?? null;
            $job->paper_size_height = $data['paper_size_height'] ?? null;
            $job->colors_front = $data['colors_front'] ?? 4;
            $job->colors_back = $data['colors_back'] ?? 0;
            $job->finishing_type = $data['finishing_type'] ?? null;
            $job->specifications = $data['specifications'] ?? null;
            $job->file_path = $data['file_path'] ?? null;
            $job->estimated_cost = $data['estimated_cost'] ?? 0;
            $job->quoted_price = $data['quoted_price'] ?? 0;
            $job->special_instructions = $data['special_instructions'] ?? null;
            $job->notes = $data['notes'] ?? null;
            $job->save();

            return $job;
        });
    }

    /**
     * Generate unique job number
     */
    protected function generateJobNumber(): string
    {
        $prefix = 'PJ';
        $date = now()->format('Ymd');
        $lastJob = PrintJob::where('job_number', 'like', "{$prefix}{$date}%")
            ->orderByDesc('job_number')
            ->first();

        if ($lastJob) {
            $lastNumber = intval(substr($lastJob->job_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}{$date}{$newNumber}";
    }

    /**
     * Update job status
     */
    public function updateStatus(int $jobId, string $status): PrintJob
    {
        $job = PrintJob::findOrFail($jobId);

        $validTransitions = [
            'queued' => ['prepress', 'cancelled'],
            'prepress' => ['platemaking', 'queued'],
            'platemaking' => ['on_press', 'prepress'],
            'on_press' => ['finishing', 'on_press'],
            'finishing' => ['quality_check', 'finishing'],
            'quality_check' => ['completed', 'finishing'],
        ];

        if (
            isset($validTransitions[$job->status]) &&
            ! in_array($status, $validTransitions[$job->status])
        ) {
            throw new \InvalidArgumentException(
                "Cannot transition from {$job->status} to {$status}"
            );
        }

        $job->status = $status;

        if ($status === 'on_press' && ! $job->started_at) {
            $job->started_at = now();
        }

        if ($status === 'completed') {
            $job->completed_at = now();
        }

        $job->save();

        return $job;
    }

    /**
     * Assign operator to job
     */
    public function assignOperator(int $jobId, int $operatorId): PrintJob
    {
        $job = PrintJob::findOrFail($jobId);
        $job->assigned_operator = $operatorId;
        $job->save();

        return $job;
    }

    /**
     * Approve proof
     */
    public function approveProof(int $jobId, int $userId): PrintJob
    {
        $job = PrintJob::findOrFail($jobId);
        $job->proof_approved = true;
        $job->proof_approved_at = now();
        $job->approved_by = $userId;
        $job->save();

        return $job;
    }

    /**
     * Get job queue
     */
    public function getJobQueue(?string $status = null, ?string $priority = null)
    {
        $query = PrintJob::where('tenant_id', auth()->user()->tenant_id)
            ->with(['customer', 'assignedOperator'])
            ->active()
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($priority) {
            $query->byPriority($priority);
        }

        return $query->paginate(20);
    }

    /**
     * Get overdue jobs
     */
    public function getOverdueJobs()
    {
        return PrintJob::where('tenant_id', auth()->user()->tenant_id)
            ->overdue()
            ->with('customer')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        $tenantId = auth()->user()->tenant_id;
        $today = now()->toDateString();

        return [
            'total_jobs' => PrintJob::where('tenant_id', $tenantId)->count(),
            'active_jobs' => PrintJob::where('tenant_id', $tenantId)->active()->count(),
            'completed_today' => PrintJob::where('tenant_id', $tenantId)
                ->whereDate('completed_at', $today)
                ->count(),
            'overdue_jobs' => PrintJob::where('tenant_id', $tenantId)->overdue()->count(),
            'urgent_jobs' => PrintJob::where('tenant_id', $tenantId)
                ->byPriority('urgent')
                ->active()
                ->count(),
            'in_production' => PrintJob::where('tenant_id', $tenantId)
                ->where('status', 'on_press')
                ->count(),
            'in_finishing' => PrintJob::where('tenant_id', $tenantId)
                ->where('status', 'finishing')
                ->count(),
        ];
    }

    /**
     * Calculate estimated cost
     */
    public function calculateEstimatedCost(array $specs): float
    {
        // Simplified cost calculation
        $quantity = $specs['quantity'] ?? 0;
        $colors = ($specs['colors_front'] ?? 4) + ($specs['colors_back'] ?? 0);

        // Base costs
        $paperCost = ($specs['paper_cost_per_sheet'] ?? 0.5) * $quantity;
        $plateCost = ($specs['plates_needed'] ?? 4) * 50000; // Rp 50k per plate
        $inkCost = $colors * $quantity * 100; // Rp 100 per color per sheet
        $laborCost = $quantity * 50; // Rp 50 per sheet
        $machineCost = $quantity * 75; // Rp 75 per sheet
        $finishingCost = $specs['finishing_cost'] ?? 0;
        $overheadCost = ($paperCost + $plateCost + $inkCost + $laborCost + $machineCost) * 0.15;

        return $paperCost + $plateCost + $inkCost + $laborCost + $machineCost + $finishingCost + $overheadCost;
    }

    /**
     * Update actual costs
     */
    public function updateActualCosts(int $jobId, float $actualCost): PrintJob
    {
        $job = PrintJob::findOrFail($jobId);
        $job->actual_cost = $actualCost;
        $job->save();

        return $job;
    }
}
