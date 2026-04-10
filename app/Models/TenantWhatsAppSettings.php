<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantWhatsAppSettings extends Model
{
    protected $table = 'tenant_whatsapp_settings';

    protected $fillable = [
        'tenant_id',
        'provider',
        'api_key',
        'api_secret',
        'phone_number',
        'webhook_url',
        'is_active',
        'enable_invoice_notifications',
        'enable_appointment_reminders',
        'enable_payment_reminders',
        'enable_general_notifications',
        'max_messages_per_day',
        'current_messages_today',
        'last_reset_date',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'enable_invoice_notifications' => 'boolean',
            'enable_appointment_reminders' => 'boolean',
            'enable_payment_reminders' => 'boolean',
            'enable_general_notifications' => 'boolean',
            'max_messages_per_day' => 'integer',
            'current_messages_today' => 'integer',
            'last_reset_date' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get WhatsApp settings for a tenant.
     */
    public static function getForTenant(int $tenantId): ?self
    {
        return self::where('tenant_id', $tenantId)->first();
    }

    /**
     * Check if tenant has WhatsApp configured.
     */
    public static function isConfigured(int $tenantId): bool
    {
        $settings = self::getForTenant($tenantId);
        return $settings && $settings->is_active && !empty($settings->api_key);
    }

    /**
     * Reset daily message counter.
     */
    public function resetDailyCounter(): void
    {
        if (!$this->last_reset_date || $this->last_reset_date->isToday()) {
            return;
        }

        $this->update([
            'current_messages_today' => 0,
            'last_reset_date' => now(),
        ]);
    }

    /**
     * Check if tenant can send more messages today.
     */
    public function canSendMessages(): bool
    {
        $this->resetDailyCounter();
        return $this->current_messages_today < $this->max_messages_per_day;
    }

    /**
     * Increment message counter.
     */
    public function incrementMessageCount(): void
    {
        $this->resetDailyCounter();
        $this->increment('current_messages_today');
    }

    /**
     * Get available providers.
     */
    public static function getProviders(): array
    {
        return [
            'fonnte' => 'Fonnte (Recommended for Indonesia)',
            'wablas' => 'Wablas',
            'twilio' => 'Twilio WhatsApp API',
            'ultramsg' => 'Ultramsg',
            'custom' => 'Custom Webhook',
        ];
    }
}
