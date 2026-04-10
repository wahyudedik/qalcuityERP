<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelemedicineSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'jitsi_server_url',
        'jitsi_app_id',
        'jitsi_secret',
        'enable_recording',
        'recording_storage_path',
        'enable_waiting_room',
        'enable_chat',
        'enable_screen_share',
        'reminder_enabled',
        'reminder_minutes_before',
        'send_email_reminder',
        'send_sms_reminder',
        'enable_feedback',
        'require_feedback',
        'consultation_timeout_minutes',
        'max_participants',
        'allow_group_consultation',
        'custom_logo_url',
        'welcome_message',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'enable_recording' => 'boolean',
        'enable_waiting_room' => 'boolean',
        'enable_chat' => 'boolean',
        'enable_screen_share' => 'boolean',
        'reminder_enabled' => 'boolean',
        'reminder_minutes_before' => 'integer',
        'send_email_reminder' => 'boolean',
        'send_sms_reminder' => 'boolean',
        'enable_feedback' => 'boolean',
        'require_feedback' => 'boolean',
        'consultation_timeout_minutes' => 'integer',
        'max_participants' => 'integer',
        'allow_group_consultation' => 'boolean',
    ];

    /**
     * Get the tenant that owns the setting.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get or create settings for tenant.
     */
    public static function getForTenant(int $tenantId): self
    {
        return self::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'jitsi_server_url' => 'https://meet.jit.si',
                'enable_recording' => true,
                'enable_waiting_room' => true,
                'enable_chat' => true,
                'enable_screen_share' => true,
                'reminder_enabled' => true,
                'reminder_minutes_before' => 30,
                'send_email_reminder' => true,
                'send_sms_reminder' => false,
                'enable_feedback' => true,
                'require_feedback' => false,
                'consultation_timeout_minutes' => 60,
                'max_participants' => 10,
                'allow_group_consultation' => false,
            ]
        );
    }

    /**
     * Check if Jitsi server is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->jitsi_server_url);
    }

    /**
     * Get Jitsi domain from URL.
     */
    public function getJitsiDomain(): string
    {
        return parse_url($this->jitsi_server_url, PHP_URL_HOST) ?? 'meet.jit.si';
    }

    /**
     * Check if using self-hosted Jitsi.
     */
    public function isSelfHosted(): bool
    {
        return $this->jitsi_server_url !== 'https://meet.jit.si';
    }
}
