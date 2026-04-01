<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'slug', 'email', 'phone', 'address', 'logo',
        'plan', 'is_active', 'trial_ends_at', 'plan_expires_at',
        'subscription_plan_id', 'business_type', 'business_description',
        'onboarding_completed',
        // Company profile
        'costing_method',
        'npwp', 'website', 'city', 'province', 'postal_code',
        'bank_name', 'bank_account', 'bank_account_name', 'tagline',
        'stamp_image', 'director_signature', 'invoice_footer_notes',
        'invoice_payment_terms', 'letter_head_color', 'doc_number_prefix',
        // Module visibility
        'enabled_modules',
    ];

    protected function casts(): array
    {
        return [
            'is_active'             => 'boolean',
            'trial_ends_at'         => 'datetime',
            'plan_expires_at'       => 'datetime',
            'onboarding_completed'  => 'boolean',
            'enabled_modules'       => 'array',
        ];
    }

    /**
     * Check if a module is enabled for this tenant.
     * null = all enabled (backward compat for existing tenants).
     */
    public function isModuleEnabled(string $key): bool
    {
        if ($this->enabled_modules === null) return true;
        return in_array($key, $this->enabled_modules, true);
    }

    /** Return list of enabled module keys, or all keys if null. */
    public function enabledModules(): array
    {
        return $this->enabled_modules ?? \App\Services\ModuleRecommendationService::ALL_MODULES;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'admin');
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /** Apakah masa trial sudah berakhir */
    public function isTrialExpired(): bool
    {
        return $this->plan === 'trial'
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isPast();
    }

    /** Apakah langganan berbayar sudah berakhir */
    public function isPlanExpired(): bool
    {
        return $this->plan !== 'trial'
            && $this->plan_expires_at !== null
            && $this->plan_expires_at->isPast();
    }

    /** Apakah tenant masih boleh akses (aktif + belum expired) */
    public function canAccess(): bool
    {
        if (! $this->is_active) return false;
        if ($this->isTrialExpired()) return false;
        if ($this->isPlanExpired()) return false;
        return true;
    }

    /** Batas user berdasarkan plan */
    public function maxUsers(): int
    {
        if ($this->subscriptionPlan) {
            return $this->subscriptionPlan->max_users;
        }
        return match($this->plan) {
            'starter'      => 2,
            'basic'        => 5,   // legacy
            'business'     => 10,
            'pro'          => 20,  // legacy
            'professional' => 25,
            'enterprise'   => -1,
            default        => 3,   // trial
        };
    }

    /** Batas AI messages per bulan */
    public function maxAiMessages(): int
    {
        if ($this->subscriptionPlan) {
            return $this->subscriptionPlan->max_ai_messages;
        }
        return match($this->plan) {
            'starter'      => 50,
            'basic'        => 100,  // legacy
            'business'     => 300,
            'pro'          => 500,  // legacy
            'professional' => 1000,
            'enterprise'   => -1,
            default        => 20,   // trial
        };
    }

    /** Label status langganan */
    public function subscriptionStatus(): string
    {
        if (! $this->is_active) return 'nonaktif';
        if ($this->isTrialExpired()) return 'trial_expired';
        if ($this->isPlanExpired()) return 'expired';
        if ($this->plan === 'trial') return 'trial';
        return 'active';
    }

    /** Label jenis bisnis */
    public function businessTypeLabel(): string
    {
        return match ($this->business_type) {
            'warung_makan'  => 'Warung Makan / Rumah Makan',
            'kafe'          => 'Kafe / Coffee Shop',
            'toko_retail'   => 'Toko Retail / Minimarket',
            'konveksi'      => 'Konveksi / Garmen',
            'distributor'   => 'Distributor / Grosir',
            'jasa'          => 'Usaha Jasa',
            'construction'  => 'Konstruksi / Kontraktor',
            'agriculture'   => 'Pertanian / Perkebunan',
            'livestock'     => 'Peternakan',
            'manufacture'   => 'Manufaktur / Pabrik',
            default         => 'Bisnis Umum',
        };
    }

    /** Konteks bisnis untuk system prompt AI */
    public function aiBusinessContext(): string
    {
        $lines = ["Nama bisnis: {$this->name}"];

        if ($this->business_type) {
            $lines[] = "Jenis bisnis: {$this->businessTypeLabel()}";
        }
        if ($this->business_description) {
            $lines[] = "Deskripsi: {$this->business_description}";
        }

        // Tambahkan tips kontekstual per jenis bisnis
        $tips = match ($this->business_type) {
            'warung_makan', 'kafe' =>
                "Fokus pada: pencatatan penjualan cepat (POS), stok bahan baku, dan rekap omzet harian.",
            'toko_retail' =>
                "Fokus pada: manajemen stok produk, harga jual, dan laporan penjualan per periode.",
            'konveksi' =>
                "Fokus pada: order produksi, bahan baku kain/benang, dan pengiriman ke pelanggan.",
            'distributor' =>
                "Fokus pada: purchase order ke supplier, stok gudang, dan distribusi ke pelanggan.",
            'jasa' =>
                "Fokus pada: pencatatan pendapatan jasa, pengeluaran operasional, dan laporan keuangan.",
            default => null,
        };

        if ($tips) {
            $lines[] = $tips;
        }

        return implode("\n", $lines);
    }
}
