<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeveloperAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'bio',
        'website',
        'github_profile',
        'skills',
        'total_earnings',
        'available_balance',
        'payout_method',
        'payout_details',
        'status',
    ];

    protected $casts = [
        'skills' => 'array',
        'total_earnings' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'payout_details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function apps()
    {
        return $this->hasMany(MarketplaceApp::class, 'developer_id');
    }
    public function earnings()
    {
        return $this->hasMany(DeveloperEarning::class);
    }
    public function payouts()
    {
        return $this->hasMany(DeveloperPayout::class);
    }
}
