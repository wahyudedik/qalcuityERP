<?php

namespace App\Jobs;

use App\Models\EcommerceChannel;
use App\Models\ErpNotification;
use App\Models\User;
use App\Services\EcommerceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncEcommerceOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 180;

    public function __construct(
        public readonly ?int $tenantId = null, // null = semua tenant
    ) {}

    public function handle(EcommerceService $service): void
    {
        $query = EcommerceChannel::where('is_active', true);

        if ($this->tenantId) {
            $query->where('tenant_id', $this->tenantId);
        }

        $channels = $query->get();

        if ($channels->isEmpty()) {
            return;
        }

        $totalNew = 0;
        $totalErrors = 0;

        foreach ($channels as $channel) {
            try {
                $newOrders = $service->syncOrders($channel);
                $totalNew += $newOrders;

                // Update last_synced_at
                $channel->update(['last_synced_at' => now()]);

                if ($newOrders > 0) {
                    $admin = User::where('tenant_id', $channel->tenant_id)
                        ->where('role', 'admin')
                        ->first();

                    if ($admin) {
                        ErpNotification::create([
                            'tenant_id' => $channel->tenant_id,
                            'user_id' => $admin->id,
                            'type' => 'ecommerce_sync',
                            'title' => '🛒 Order E-Commerce Baru',
                            'body' => "{$newOrders} order baru dari {$channel->platform} ({$channel->name}) berhasil disinkronkan.",
                            'data' => [
                                'channel_id' => $channel->id,
                                'platform' => $channel->platform,
                                'new_orders' => $newOrders,
                            ],
                        ]);
                    }
                }

                Log::info("SyncEcommerceOrders: channel={$channel->id} platform={$channel->platform} new={$newOrders}");

            } catch (\Throwable $e) {
                $totalErrors++;
                Log::error("SyncEcommerceOrders error channel={$channel->id}: ".$e->getMessage());

                // Notifikasi error ke admin
                $admin = User::where('tenant_id', $channel->tenant_id)
                    ->where('role', 'admin')
                    ->first();

                if ($admin) {
                    ErpNotification::create([
                        'tenant_id' => $channel->tenant_id,
                        'user_id' => $admin->id,
                        'type' => 'ecommerce_sync_error',
                        'title' => '❌ Gagal Sinkronisasi E-Commerce',
                        'body' => "Gagal sinkronisasi {$channel->platform} ({$channel->name}): ".$e->getMessage(),
                        'data' => ['channel_id' => $channel->id, 'error' => $e->getMessage()],
                    ]);
                }
            }
        }

        Log::info("SyncEcommerceOrders: total_new={$totalNew} errors={$totalErrors}");
    }
}
