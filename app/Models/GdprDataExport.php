<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GdprDataExport extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'user_id',
        'export_type',
        'modules',
        'status',
        'file_path',
        'file_size',
        'requested_at',
        'completed_at',
        'expires_at',
        'error_message',
    ];

    protected $casts = [
        'modules' => 'array',
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'file_size' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
