<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'notification_type',
        'in_app',
        'email',
        'push',
        'whatsapp',
        'digest_frequency',
        'quiet_hours_start',
        'quiet_hours_end',
        'is_dnd',
        'module_preferences',
    ];

    protected function casts(): array
    {
        return [
            'in_app' => 'boolean',
            'email' => 'boolean',
            'push' => 'boolean',
            'whatsapp' => 'boolean',
            'is_dnd' => 'boolean',
            'module_preferences' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all available notification types grouped by module.
     */
    public static function availableTypes(): array
    {
        return [
            'inventory' => [
                'low_stock' => 'Stok Menipis',
                'product_expiry' => 'Produk Kedaluwarsa',
            ],
            'finance' => [
                'invoice_overdue' => 'Faktur Jatuh Tempo',
                'budget_alert' => 'Peringatan Anggaran',
            ],
            'hrm' => [
                'missing_report' => 'Laporan Belum Diisi',
                'asset_maintenance_due' => 'Jadwal Maintenance Aset',
            ],
            'ai' => [
                'ai_advisor' => 'Rekomendasi AI Advisor',
                'ai_digest' => 'Ringkasan AI Digest',
            ],
            'system' => [
                'trial_expiry' => 'Masa Trial Berakhir',
                'reminder' => 'Pengingat',
            ],
        ];
    }

    /**
     * Check if user wants this notification type via specific channel.
     */
    public static function isEnabled(int $userId, string $type, string $channel = 'in_app'): bool
    {
        // Normalize type (e.g., "expiry_soon_123" -> "product_expiry")
        $normalizedType = self::normalizeType($type);

        $pref = self::where('user_id', $userId)
            ->where('notification_type', $normalizedType)
            ->first();

        // Default: enabled if no preference record exists
        if (!$pref)
            return true;

        return (bool) $pref->{$channel};
    }

    public static function normalizeType(string $type): string
    {
        if (str_starts_with($type, 'expiry_'))
            return 'product_expiry';
        if (str_starts_with($type, 'invoice_overdue'))
            return 'invoice_overdue';
        return $type;
    }

    /**
     * Check if user is currently in quiet hours (DND mode).
     */
    public function isInQuietHours(): bool
    {
        if (!$this->is_dnd) {
            return false;
        }

        if (!$this->quiet_hours_start || !$this->quiet_hours_end) {
            return false;
        }

        $now = now();
        $start = \Carbon\Carbon::parse($this->quiet_hours_start);
        $end = \Carbon\Carbon::parse($this->quiet_hours_end);

        // Handle overnight quiet hours (e.g., 22:00 - 06:00)
        if ($start > $end) {
            return $now->gte($start) || $now->lte($end);
        }

        return $now->between($start, $end);
    }

    /**
     * Check if a specific module is enabled for notifications.
     */
    public function isModuleEnabled(string $module): bool
    {
        if (!$this->module_preferences) {
            return true; // Default enabled if not set
        }

        return $this->module_preferences[$module] ?? true;
    }

    /**
     * Get available digest frequencies.
     */
    public static function getDigestFrequencies(): array
    {
        return [
            'realtime' => 'Real-time (Setiap ada notifikasi)',
            'daily' => 'Daily (Sekali sehari)',
            'weekly' => 'Weekly (Sekali seminggu)',
            'never' => 'Never (Tidak pernah)',
        ];
    }
}
