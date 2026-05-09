<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomModule extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'version',
        'schema',
        'ui_config',
        'permissions',
        'is_active',
        'created_by_user_id',
    ];

    protected $casts = [
        'schema' => 'array',
        'ui_config' => 'array',
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function records()
    {
        return $this->hasMany(CustomModuleRecord::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
