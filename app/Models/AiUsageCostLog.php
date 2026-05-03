<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Model untuk mencatat setiap penggunaan AI beserta estimasi biayanya.
 *
 * Log-only model — immutable setelah dibuat.
 * Tidak menggunakan updated_at (hanya created_at).
 *
 * Requirements: 6.2, 6.3
 */
class AiUsageCostLog extends Model
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
        'user_id',
        'use_case',
        'provider',
        'model',
        'input_tokens',
        'output_tokens',
        'estimated_cost_idr',
        'response_time_ms',
        'fallback_degraded',
        'created_at',
    ];

    protected $casts = [
        'input_tokens'       => 'integer',
        'output_tokens'      => 'integer',
        'estimated_cost_idr' => 'float',
        'response_time_ms'   => 'integer',
        'fallback_degraded'  => 'boolean',
        'created_at'         => 'datetime',
    ];

    /**
     * Scope untuk filter berdasarkan rentang tanggal created_at.
     */
    public function scopeInDateRange(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
    }

    /**
     * Scope untuk filter berdasarkan use case.
     */
    public function scopeForUseCase(Builder $query, string $useCase): Builder
    {
        return $query->where('use_case', $useCase);
    }

    /**
     * Buat record baru untuk mencatat penggunaan AI.
     *
     * @param  array{
     *     tenant_id: int,
     *     user_id?: int|null,
     *     use_case: string,
     *     provider: string,
     *     model: string,
     *     input_tokens?: int,
     *     output_tokens?: int,
     *     estimated_cost_idr?: float,
     *     response_time_ms?: int|null,
     *     fallback_degraded?: bool,
     *     created_at?: string|\DateTimeInterface|null,
     * } $data
     */
    public static function record(array $data): self
    {
        return static::create($data);
    }
}
