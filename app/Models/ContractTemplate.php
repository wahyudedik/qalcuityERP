<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractTemplate extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'category', 'body_template',
        'default_terms', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function contracts(): HasMany { return $this->hasMany(Contract::class, 'template_id'); }
}
