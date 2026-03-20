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
    ];

    protected function casts(): array
    {
        return [
            'is_active'      => 'boolean',
            'trial_ends_at'  => 'datetime',
            'plan_expires_at' => 'datetime',
        ];
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
            'basic'      => 5,
            'pro'        => 20,
            'enterprise' => -1,
            default      => 3, // trial
        };
    }

    /** Batas AI messages per bulan */
    public function maxAiMessages(): int
    {
        if ($this->subscriptionPlan) {
            return $this->subscriptionPlan->max_ai_messages;
        }
        return match($this->plan) {
            'basic'      => 100,
            'pro'        => 500,
            'enterprise' => -1,
            default      => 20, // trial
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
