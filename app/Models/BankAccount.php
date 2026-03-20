<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = ['tenant_id', 'bank_name', 'account_number', 'account_name', 'balance', 'is_active'];
    protected $casts = ['balance' => 'float', 'is_active' => 'boolean'];

    public function statements() { return $this->hasMany(BankStatement::class); }
}
