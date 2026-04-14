<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageRecognitionResult extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'image_path',
        'recognition_type',
        'detected_objects',
        'labels',
        'confidence_score',
        'metadata',
        'description',
        'verified',
    ];

    protected $casts = [
        'detected_objects' => 'array',
        'labels' => 'array',
        'confidence_score' => 'decimal:4',
        'metadata' => 'array',
        'verified' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}