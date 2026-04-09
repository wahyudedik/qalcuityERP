<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'subject',
        'message',
        'priority',
        'is_read',
        'read_at',
        'parent_id',
        'attachment_path',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PatientMessage::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(PatientMessage::class, 'parent_id');
    }
}
