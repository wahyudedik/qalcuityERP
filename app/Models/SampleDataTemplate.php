<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SampleDataTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'industry',
        'template_name',
        'description',
        'modules_included',
        'data_config',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'modules_included' => 'array',
        'data_config' => 'array',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
    ];

    public function logs()
    {
        return $this->hasMany(SampleDataLog::class);
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
