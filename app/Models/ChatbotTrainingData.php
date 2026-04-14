<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotTrainingData extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'category',
        'question',
        'answer',
        'context',
        'keywords',
        'intents',
        'usage_count',
        'effectiveness_score',
        'is_verified',
        'verified_by_user_id',
    ];

    protected $casts = [
        'context' => 'array',
        'keywords' => 'array',
        'intents' => 'array',
        'usage_count' => 'integer',
        'effectiveness_score' => 'decimal:4',
        'is_verified' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}