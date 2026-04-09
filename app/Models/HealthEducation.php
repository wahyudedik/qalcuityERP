<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthEducation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'content',
        'summary',
        'target_audience',
        'language',
        'author_id',
        'status',
        'published_at',
        'attachment_path',
        'view_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'view_count' => 'integer',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
