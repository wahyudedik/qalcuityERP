<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Affiliate extends Model
{
    use BelongsToTenant;
protected $fillable = [
        'user_id', 'demo_tenant_id', 'code', 'company_name', 'phone',
        'bank_name', 'bank_account', 'bank_holder',
        'commission_rate', 'total_earned', 'total_paid', 'balance',
        'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'total_earned'    => 'decimal:2',
            'total_paid'      => 'decimal:2',
            'balance'         => 'decimal:2',
            'is_active'       => 'boolean',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function demoTenant(): BelongsTo { return $this->belongsTo(Tenant::class, 'demo_tenant_id'); }
    public function referrals(): HasMany { return $this->hasMany(AffiliateReferral::class); }
    public function commissions(): HasMany { return $this->hasMany(AffiliateCommission::class); }
    public function payouts(): HasMany { return $this->hasMany(AffiliatePayout::class); }
    public function auditLogs(): HasMany { return $this->hasMany(AffiliateAuditLog::class); }

    public function referralUrl(): string
    {
        return url('/register?ref=' . $this->code);
    }

    public static function generateCode(): string
    {
        do {
            $code = 'AFF-' . strtoupper(Str::random(6));
        } while (self::where('code', $code)->exists());
        return $code;
    }

    public function recalculateBalance(): void
    {
        $earned = $this->commissions()->whereIn('status', ['approved', 'paid'])->sum('commission_amount');
        $paid = $this->payouts()->where('status', 'completed')->sum('amount');
        $this->update([
            'total_earned' => $earned,
            'total_paid'   => $paid,
            'balance'      => $earned - $paid,
        ]);
    }
}
