<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentimentAnalysis extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'source_type',
        'source_id',
        'content',
        'sentiment',
        'confidence',
        'polarity',
        'subjectivity',
        'emotions',
        'key_phrases',
        'topics',
        'requires_attention',
        'assigned_to_user_id',
        'status',
        'response_suggestion',
    ];

    protected $casts = [
        'confidence' => 'decimal:4',
        'polarity' => 'decimal:4',
        'subjectivity' => 'decimal:4',
        'emotions' => 'array',
        'key_phrases' => 'array',
        'topics' => 'array',
        'requires_attention' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
