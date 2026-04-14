<?php

namespace App\Services;

use App\Models\ExportJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Service untuk handle large exports dengan queuing
 * 
 * BUG-REP-002 FIX: Prevents PHP timeout on 100K+ row exports
 * by queueing exports and providing progress tracking
 */
class ExportService
{
    /**
     * Queue an export job and return job ID for progress tracking
     * 
     * @param string $exportClass Export class to instantiate
     * @param array $constructorArgs Constructor arguments for export class
     * @param string $filename Output filename
     * @param string $disk Storage disk (default: public)
     * @return string Job ID for tracking progress
     */
    public function queueExport(string $exportClass, array $constructorArgs, string $filename, string $disk = 'public'): string
    {
        $jobId = (string) \Illuminate\Support\Str::uuid();
        $userId = Auth::id();
        $tenantId = Auth::user()->tenant_id ?? 0;

        // Use tenant-isolated file path for storage security (Bug 1.26 fix)
        $fileUuid = (string) \Illuminate\Support\Str::uuid();
        $extension = pathinfo($filename, PATHINFO_EXTENSION) ?: 'xlsx';
        $isolatedFilePath = "exports/{$tenantId}/{$fileUuid}.{$extension}";

        // Create export job record
        $exportJob = ExportJob::create([
            'job_id' => $jobId,
            'user_id' => $userId,
            'tenant_id' => $tenantId,
            'export_type' => class_basename($exportClass),
            'filename' => $filename,
            'file_path' => $isolatedFilePath,
            'disk' => $disk,
            'status' => 'pending',
            'total_rows' => 0,
            'processed_rows' => 0,
            'started_at' => now(),
        ]);

        // Initialize progress in cache
        Cache::put("export_progress:{$jobId}", [
            'status' => 'pending',
            'total_rows' => 0,
            'processed_rows' => 0,
            'percentage' => 0,
            'message' => 'Export queued, waiting to start...',
        ], now()->addHours(24));

        // Dispatch queued export job
        dispatch(new \App\Jobs\ProcessQueuedExport(
            $jobId,
            $exportClass,
            $constructorArgs,
            $filename,
            $disk,
            $userId,
            $tenantId
        ))->onQueue('exports');

        return $jobId;
    }

    /**
     * Get export progress by job ID
     * 
     * @param string $jobId
     * @return array
     */
    public function getProgress(string $jobId): array
    {
        // Try cache first (faster)
        $progress = Cache::get("export_progress:{$jobId}");

        if ($progress) {
            return $progress;
        }

        // Fallback to database
        $exportJob = ExportJob::where('job_id', $jobId)->first();

        if (!$exportJob) {
            return [
                'status' => 'not_found',
                'message' => 'Export job not found',
            ];
        }

        return [
            'status' => $exportJob->status,
            'total_rows' => $exportJob->total_rows,
            'processed_rows' => $exportJob->processed_rows,
            'percentage' => $exportJob->total_rows > 0
                ? round(($exportJob->processed_rows / $exportJob->total_rows) * 100, 2)
                : 0,
            'message' => $exportJob->error_message ?? $exportJob->status,
            'download_url' => $exportJob->status === 'completed' ? $exportJob->download_url : null,
            'file_size' => $exportJob->file_size,
        ];
    }

    /**
     * Update export progress (called by job)
     * 
     * @param string $jobId
     * @param string $status
     * @param int $totalRows
     * @param int $processedRows
     * @param string $message
     * @param array $extra
     */
    public function updateProgress(
        string $jobId,
        string $status,
        int $totalRows = 0,
        int $processedRows = 0,
        string $message = '',
        array $extra = []
    ): void {
        $percentage = $totalRows > 0 ? round(($processedRows / $totalRows) * 100, 2) : 0;

        $progress = array_merge([
            'status' => $status,
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'percentage' => $percentage,
            'message' => $message ?: $status,
        ], $extra);

        // Update cache (fast access)
        Cache::put("export_progress:{$jobId}", $progress, now()->addHours(24));

        // Update database (persistent)
        $updateData = [
            'status' => $status,
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
        ];

        if ($status === 'completed') {
            $updateData['completed_at'] = now();
            $updateData['download_url'] = $extra['download_url'] ?? null;
            $updateData['file_size'] = $extra['file_size'] ?? null;
        }

        if ($status === 'failed') {
            $updateData['error_message'] = $message;
            $updateData['failed_at'] = now();
        }

        ExportJob::where('job_id', $jobId)->update($updateData);
    }

    /**
     * Check if export should use queue (based on estimated row count)
     * 
     * @param int $estimatedRows
     * @return bool
     */
    public function shouldQueue(int $estimatedRows): bool
    {
        $threshold = config('excel.exports.queue_threshold', 5000);
        return $estimatedRows > $threshold;
    }

    /**
     * Get estimated row count for a query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return int
     */
    public function estimateRowCount($query): int
    {
        try {
            return $query->count();
        } catch (\Throwable $e) {
            \Log::warning('Failed to estimate row count', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Download completed export file with tenant ownership validation (Bug 1.26 fix).
     *
     * Validates that the requesting user's tenant_id matches the export job's tenant_id
     * to prevent cross-tenant file access.
     *
     * @param string $jobId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadExport(string $jobId)
    {
        $tenantId = Auth::user()?->tenant_id;

        // Build query with tenant ownership validation
        $query = ExportJob::where('job_id', $jobId)
            ->where('status', 'completed');

        // Validate tenant ownership — prevent cross-tenant file access
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $exportJob = $query->first();

        if (!$exportJob) {
            abort(404, 'File export tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $filePath = $exportJob->file_path;
        $disk = $exportJob->disk ?? 'public';

        if (!Storage::disk($disk)->exists($filePath)) {
            abort(404, 'File export tidak ditemukan.');
        }

        return Storage::disk($disk)->download($filePath, $exportJob->filename);
    }

    /**
     * Clean up old export jobs and files
     * 
     * @param int $daysOld
     * @return int Number of jobs cleaned up
     */
    public function cleanupOldExports(int $daysOld = 7): int
    {
        $cutoffDate = now()->subDays($daysOld);

        $oldJobs = ExportJob::where('created_at', '<', $cutoffDate)->get();
        $cleanedCount = 0;

        foreach ($oldJobs as $job) {
            // Delete file if exists
            if ($job->file_path && Storage::disk($job->disk)->exists($job->file_path)) {
                Storage::disk($job->disk)->delete($job->file_path);
            }

            // Delete job record
            $job->delete();
            $cleanedCount++;
        }

        return $cleanedCount;
    }
}
