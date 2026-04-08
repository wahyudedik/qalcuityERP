<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityGrade extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'grade_code',
        'grade_name',
        'description',
        'price_multiplier',
        'criteria',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price_multiplier' => 'decimal:2',
        'criteria' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function catchLogs()
    {
        return $this->hasMany(CatchLog::class, 'grade_id');
    }

    public function calculatePrice(float $basePrice): float
    {
        return $basePrice * $this->price_multiplier;
    }
}
