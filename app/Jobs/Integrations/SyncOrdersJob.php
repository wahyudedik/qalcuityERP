<?php

namespace App\Jobs\Integrations;

use App\Models\Integration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Integration $integration;

    public $timeout = 300;

    public $tries = 3;

    public $backoff = 60;

    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
    }

    public function handle(): void
    {
        Log::info('Starting order sync job', [
            'integration' => $this->integration->slug,
        ]);

        try {
            $connectorClass = $this->integration->getConnectorClass();
            $connector = new $connectorClass($this->integration);

            if (! $connector->isConnected()) {
                Log::error('Integration not connected', [
                    'integration' => $this->integration->slug,
                ]);

                return;
            }

            $result = $connector->syncOrders();
            $this->integration->updateLastSync();

            Log::info('Order sync job completed', [
                'integration' => $this->integration->slug,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Order sync job failed', [
                'integration' => $this->integration->slug,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->integration->markAsError();

        Log::critical('Order sync job failed permanently', [
            'integration' => $this->integration->slug,
            'error' => $exception->getMessage(),
        ]);
    }
}
