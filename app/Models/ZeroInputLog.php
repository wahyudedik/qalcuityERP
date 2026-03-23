<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZeroInputLog extends Model
{
    protected $fillable = [
        'tenant_id', 'user_id', 'channel', 'status',
        'mapped_module', 'extracted_data', 'created_records',
        'raw_input', 'file_path', 'error_message',
    ];

    protected $casts = [
        'extracted_data'  => 'array',
        'created_records' => 'array',
    ];

    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
