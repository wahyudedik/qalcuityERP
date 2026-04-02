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
    ];

    protected function casts(): array
    {
        return [
            'in_app' => 'boolean',
            'email'  => 'boolean',
            'push'   => 'boolean',
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
                'low_stock'       => 'Stok Menipis',
                'product_expiry'  => 'Produk Kedaluwarsa',
            ],
            'finance' => [
                'invoice_overdue' => 'Faktur Jatuh Tempo',
                'budget_alert'    => 'Peringatan Anggaran',
            ],
            'hrm' => [
                'missing_report'         => 'Laporan Belum Diisi',
                'asset_maintenance_due'  => 'Jadwal Maintenance Aset',
            ],
            'ai' => [
                'ai_advisor' => 'Rekomendasi AI Advisor',
                'ai_digest'  => 'Ringkasan AI Digest',
            ],
            'system' => [
                'trial_expiry' => 'Masa Trial Berakhir',
                'reminder'     => 'Pengingat',
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
        if (!$pref) return true;

        return (bool) $pref->{$channel};
    }

    public static function normalizeType(string $type): string
    {
        if (str_starts_with($type, 'expiry_'))       return 'product_expiry';
        if (str_starts_with($type, 'invoice_overdue')) return 'invoice_overdue';
        return $type;
    }
}
