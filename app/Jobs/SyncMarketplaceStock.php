<?php

namespace App\Jobs;

use App\Exceptions\MarketplaceApiException;
use App\Exceptions\RateLimitException;
use App\Models\EcommerceChannel;
use App\Models\ErpNotification;
use App\Models\User;
use App\Services\MarketplaceSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMarketplaceStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 10;

    public function __construct(public ?int $tenantId = null) {}

    public function handle(): void
    {
        $service = app(MarketplaceSyncService::class);

        $query = EcommerceChannel::where('is_active', true)
            ->where('stock_sync_enabled', true);

        if ($this->tenantId) {
            $query->where('tenant_id', $this->tenantId);
        }

        $channels = $query->get();

        foreach ($channels as $channel) {
            try {
                $result = $service->syncStock($channel);

                $channel->update([
                    'last_stock_sync_at' => now(),
                    'sync_errors' => $result['failed'] > 0
                        ? array_merge($channel->sync_errors ?? [], array_map(fn ($e) => ['type' => 'stock', 'time' => now()->toIso8601String(), 'message' => $e], array_slice($result['errors'], 0, 5)))
                        : $channel->sync_errors,
                ]);

                if ($result['failed'] > 0) {
                    $admin = User::where('tenant_id', $channel->tenant_id)->whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first();
                    if ($admin) {
                        ErpNotification::create([
                            'tenant_id' => $channel->tenant_id,
                            'user_id' => $admin->id,
                            'type' => 'marketplace_sync',
                            'title' => 'Sync Stok Marketplace Gagal Sebagian',
                            'body' => "{$result['failed']} produk gagal sync stok ke {$channel->platform} ({$channel->shop_name}). {$result['success']} berhasil.",
                            'data' => json_encode($result),
                        ]);
                    }
                }

                Log::info("Marketplace stock sync completed for channel {$channel->id}: {$result['success']} success, {$result['failed']} failed");
            } catch (RateLimitException $e) {
                $delay = min(600, pow(2, $this->attempts()) * 10);
                Log::info('Marketplace rate limit hit, releasing with backoff', ['delay' => $delay, 'channel_id' => $channel->id]);
                $this->release($delay);

                return;
            } catch (MarketplaceApiException $e) {
                throw $e;
            } catch (\Throwable $e) {
                Log::error("Marketplace stock sync failed for channel {$channel->id}: {$e->getMessage()}");
            }
        }
    }
}
