<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZeroInputLog extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'user_id', 'channel', 'status',
        'mapped_module', 'extracted_data', 'user_corrected_data',
        'confidence_score', 'was_corrected', 'feedback',
        'created_records', 'raw_input', 'file_path', 'error_message',
    ];

    protected $casts = [
        'extracted_data'       => 'array',
        'user_corrected_data'  => 'array',
        'created_records'      => 'array',
        'confidence_score'     => 'float',
        'was_corrected'        => 'boolean',
    ];

    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
