<?php

namespace App\Jobs\Integrations;

use App\Services\Integrations\WebhookDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryWebhookDeliveriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 2;
    public $backoff = 300; // 5 minutes

    public function __construct()
    {
        //
    }

    public function handle(WebhookDeliveryService $webhookService): void
    {
        Log::info('Starting webhook retry job');

        try {
            $retried = $webhookService->retryFailedDeliveries();

            Log::info('Webhook retry job completed', [
                'retried_count' => $retried,
            ]);
        } catch (\Throwable $e) {
            Log::error('Webhook retry job failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
