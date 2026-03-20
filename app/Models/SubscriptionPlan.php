<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'price_monthly', 'price_yearly',
        'max_users', 'max_ai_messages', 'trial_days',
        'features', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features'   => 'array',
            'is_active'  => 'boolean',
            'price_monthly' => 'decimal:2',
            'price_yearly'  => 'decimal:2',
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function isUnlimitedUsers(): bool
    {
        return $this->max_users === -1;
    }

    public function isUnlimitedAi(): bool
    {
        return $this->max_ai_messages === -1;
    }

    public static function defaultPlans(): array
    {
        return [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'price_monthly' => 299000,
                'price_yearly'  => 2990000,
                'max_users' => 5,
                'max_ai_messages' => 100,
                'trial_days' => 14,
                'features' => ['Inventori', 'Penjualan', 'Pembelian', 'Laporan Dasar'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price_monthly' => 599000,
                'price_yearly'  => 5990000,
                'max_users' => 20,
                'max_ai_messages' => 500,
                'trial_days' => 14,
                'features' => ['Inventori', 'Penjualan', 'Pembelian', 'HRM', 'Keuangan', 'AI Chat', 'Laporan Lengkap'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'price_monthly' => 1499000,
                'price_yearly'  => 14990000,
                'max_users' => -1,
                'max_ai_messages' => -1,
                'trial_days' => 30,
                'features' => ['Semua Fitur Pro', 'User Tak Terbatas', 'AI Tak Terbatas', 'Prioritas Support', 'Custom Integrasi'],
                'sort_order' => 3,
            ],
        ];
    }
}
