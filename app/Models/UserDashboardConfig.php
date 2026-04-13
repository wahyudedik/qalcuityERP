<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDashboardConfig extends Model
{
    protected $fillable = ['user_id', 'widgets', 'template_name', 'saved_templates'];

    protected function casts(): array
    {
        return [
            'widgets' => 'array',
            'saved_templates' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get template name
     */
    public function getTemplateNameAttribute(): ?string
    {
        return $this->attributes['template_name'] ?? null;
    }

    /**
     * Get saved templates
     */
    public function getSavedTemplatesAttribute(): array
    {
        return $this->attributes['saved_templates'] ?? [];
    }
}
