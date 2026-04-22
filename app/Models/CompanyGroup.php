<?php

namespace App\Models;

use App\Models\SharedService;
use App\Models\TenantGroupMember;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyGroup extends Model
{
use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'name',
        'code',
        'currency_code',
        'description',
        'parent_tenant_id',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function parentTenant()
    {
        return $this->belongsTo(Tenant::class, 'parent_tenant_id');
    }
    public function members()
    {
        return $this->hasMany(TenantGroupMember::class);
    }
    public function transactions()
    {
        return $this->hasMany(InterCompanyTransaction::class);
    }
    public function reports()
    {
        return $this->hasMany(ConsolidatedReport::class);
    }
    public function sharedServices()
    {
        return $this->hasMany(SharedService::class);
    }
}
