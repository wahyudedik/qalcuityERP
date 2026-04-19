<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'bank_account_id',
        'transaction_id',
        'transaction_date',
        'description',
        'transaction_type',
        'amount',
        'balance_after',
        'category',
        'invoice_id',
        'expense_id',
        'reconciled',
        'auto_matched',
        'raw_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'datetime',
        'reconciled' => 'boolean',
        'auto_matched' => 'boolean',
        'raw_data' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}