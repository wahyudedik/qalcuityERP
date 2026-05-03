<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk mencatat setiap peralihan provider AI.
 *
 * Log-only model — immutable setelah dibuat.
 * Tidak menggunakan updated_at (hanya created_at).
 *
 * Requirements: 3.4, 7.2
 */
class AiProviderSwitchLog extends Model
{
    use BelongsToTenant;

    /**
     * Hanya gunakan created_at, tidak ada updated_at.
     */
    public $timestamps = false;

    /**
     * Konstanta untuk nama kolom created_at.
     * Diperlukan agar Eloquent tahu kolom mana yang diisi saat create.
     */
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'tenant_id',
        'from_provider',
        'to_provider',
        'reason',
        'use_case',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
