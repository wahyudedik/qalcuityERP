<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $fillable = [
        'tenant_id', 'period', 'status', 'total_gross',
        'total_deductions', 'total_net', 'processed_by', 'processed_at',
        'journal_entry_id', 'payment_journal_entry_id', 'paid_at', 'paid_by',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'paid_at'      => 'datetime',
        'total_gross'  => 'float',
        'total_deductions' => 'float',
        'total_net'    => 'float',
    ];

    public function items() { return $this->hasMany(PayrollItem::class, 'payroll_run_id'); }
    public function journalEntry() { return $this->belongsTo(\App\Models\JournalEntry::class); }
    public function paymentJournalEntry() { return $this->belongsTo(\App\Models\JournalEntry::class, 'payment_journal_entry_id'); }
}
