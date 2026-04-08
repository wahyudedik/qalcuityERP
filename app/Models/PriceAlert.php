<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceAlert extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'commodity',
        'target_price',
        'condition',
        'is_active',
        'notification_channels',
        'triggered_at',
        'has_triggered',
    ];

    protected $casts = [
        'target_price' => 'decimal:2',
        'is_active' => 'boolean',
        'has_triggered' => 'boolean',
        'notification_channels' => 'array',
        'triggered_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function checkAndTrigger(float $currentPrice): bool
    {
        if (!$this->is_active || $this->has_triggered) {
            return false;
        }

        $triggered = match ($this->condition) {
            'above' => $currentPrice >= $this->target_price,
            'below' => $currentPrice <= $this->target_price,
            'equals' => abs($currentPrice - $this->target_price) < 0.01,
            default => false
        };

        if ($triggered) {
            $this->update([
                'has_triggered' => true,
                'triggered_at' => now(),
            ]);

            // Send notifications
            $this->sendNotifications($currentPrice);
        }

        return $triggered;
    }

    protected function sendNotifications(float $currentPrice): void
    {
        foreach ($this->notification_channels as $channel) {
            match ($channel) {
                'email' => $this->sendEmailNotification($currentPrice),
                'sms' => $this->sendSmsNotification($currentPrice),
                default => null
            };
        }
    }

    protected function sendEmailNotification(float $currentPrice): void
    {
        // Implementation for email notification
        \Illuminate\Support\Facades\Log::info("Price alert email sent for {$this->commodity}");
    }

    protected function sendSmsNotification(float $currentPrice): void
    {
        // Implementation for SMS notification
        \Illuminate\Support\Facades\Log::info("Price alert SMS sent for {$this->commodity}");
    }

    public function reset(): void
    {
        $this->update([
            'has_triggered' => false,
            'triggered_at' => null,
        ]);
    }
}
