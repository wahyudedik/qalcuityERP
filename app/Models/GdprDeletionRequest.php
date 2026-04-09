<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GdprDeletionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reason',
        'status',
        'requested_at',
        'approved_by',
        'approved_at',
        'completed_at',
        'anonymization_method',
        'error_message',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
