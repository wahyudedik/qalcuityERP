<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankStatement extends Model
{
    protected $fillable = [
        'tenant_id', 'bank_account_id', 'transaction_date', 'description',
        'type', 'amount', 'balance', 'reference', 'status', 'matched_transaction_id',
    ];

    protected $casts = ['transaction_date' => 'date', 'amount' => 'float', 'balance' => 'float'];

    public function bankAccount() { return $this->belongsTo(BankAccount::class); }
    public function matchedTransaction() { return $this->belongsTo(Transaction::class, 'matched_transaction_id'); }
}
