<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErpNotification extends Model
{
    use BelongsToTenant;
    protected $table = 'erp_notifications';

    protected $fillable = ['tenant_id', 'user_id', 'type', 'module', 'title', 'body', 'data', 'read_at'];

    protected function casts(): array
    {
        return ['data' => 'array', 'read_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        // Auto-send push notification when ErpNotification is created
        static::created(function (self $notification) {
            try {
                if ($notification->user_id) {
                    app(\App\Services\WebPushService::class)->sendToUser(
                        $notification->user_id,
                        $notification->title,
                        $notification->body,
                        $notification->data['url'] ?? '/notifications',
                        'erp-' . $notification->type,
                    );
                }
            } catch (\Throwable $e) {
                // Don't fail the main operation if push fails
                \Illuminate\Support\Facades\Log::debug('Push notification failed: ' . $e->getMessage());
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public static function moduleMap(): array
    {
        return [
            'low_stock'               => 'inventory',
            'product_expiry'          => 'inventory',
            'expiry_soon'             => 'inventory',
            'expiry_expired'          => 'inventory',
            'invoice_overdue_batch'   => 'finance',
            'invoice_overdue_summary' => 'finance',
            'budget_alert'            => 'finance',
            'missing_report'          => 'hrm',
            'asset_maintenance_due'   => 'hrm',
            'ai_advisor'              => 'ai',
            'ai_digest'               => 'ai',
            'trial_expiry'            => 'system',
            'reminder'                => 'system',
            'ecommerce_sync'          => 'ecommerce',
            'ecommerce_stock_sync'    => 'ecommerce',
            'ecommerce_price_sync'    => 'ecommerce',
            'ecommerce_order'         => 'ecommerce',
            'ecommerce_error'         => 'ecommerce',
            'marketplace_sync'        => 'ecommerce',
        ];
    }

    public static function resolveModule(string $type): string
    {
        $map = self::moduleMap();
        foreach ($map as $key => $module) {
            if (str_starts_with($type, $key)) return $module;
        }
        return 'system';
    }
}
