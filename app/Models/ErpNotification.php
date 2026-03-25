<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErpNotification extends Model
{
    protected $table = 'erp_notifications';

    protected $fillable = ['tenant_id', 'user_id', 'type', 'title', 'body', 'data', 'read_at'];

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

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }

    public function isRead(): bool { return $this->read_at !== null; }

    public function markRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
