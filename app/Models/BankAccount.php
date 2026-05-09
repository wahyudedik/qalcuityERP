<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'bank_name',
        'account_number',
        'account_name',
        'account_type',
        'currency',
        'current_balance',
        'is_active',
        'auto_import',
        'import_method',
        'configuration',
        'last_import_at',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'auto_import' => 'boolean',
        'configuration' => 'array',
        'last_import_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function transactions()
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function statements()
    {
        return $this->hasMany(BankStatement::class);
    }
}
