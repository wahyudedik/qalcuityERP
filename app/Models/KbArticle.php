<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KbArticle extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'title', 'slug', 'category', 'body',
        'is_published', 'views', 'user_id',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'views' => 'integer'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
