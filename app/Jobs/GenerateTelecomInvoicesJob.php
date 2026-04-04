<?php

namespace App\Jobs;

use App\Services\Telecom\TelecomBillingIntegrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateTelecomInvoicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(TelecomBillingIntegrationService $billingService): void
    {
        Log::info('Starting telecom invoice generation job');

        try {
            $result = $billingService->generateDueInvoices();

            Log::info('Telecom invoice generation completed', [
                'total_processed' => $result['total_processed'],
                'success_count' => $result['success_count'],
                'failed_count' => $result['failed_count'],
            ]);

            if ($result['failed_count'] > 0) {
                Log::warning('Some telecom invoices failed to generate', [
                    'failures' => $result['failed'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Telecom invoice generation job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
