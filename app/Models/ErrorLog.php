<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorLog extends Model
{
    protected $fillable = [
        'level', 'message', 'trace', 'file', 'line',
        'url', 'method', 'ip', 'user_id', 'tenant_id',
        'user_agent', 'context', 'is_resolved', 'resolved_at',
    ];

    protected $casts = [
        'context'     => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    public static function capture(\Throwable $e, string $level = 'error'): void
    {
        try {
            $request = request();
            static::create([
                'level'      => $level,
                'message'    => substr($e->getMessage(), 0, 500),
                'trace'      => substr($e->getTraceAsString(), 0, 5000),
                'file'       => substr($e->getFile(), 0, 300),
                'line'       => $e->getLine(),
                'url'        => $request ? substr($request->fullUrl(), 0, 500) : null,
                'method'     => $request?->method(),
                'ip'         => $request?->ip(),
                'user_id'    => auth()->id(),
                'tenant_id'  => auth()->user()?->tenant_id,
                'user_agent' => substr($request?->userAgent() ?? '', 0, 300),
                'context'    => ['exception' => get_class($e)],
            ]);
        } catch (\Throwable) {
            // Jangan sampai error logging menyebabkan infinite loop
        }
    }

    public function levelColor(): string
    {
        return match($this->level) {
            'critical' => 'text-red-400 bg-red-500/15',
            'error'    => 'text-orange-400 bg-orange-500/15',
            'warning'  => 'text-amber-400 bg-amber-500/15',
            default    => 'text-blue-400 bg-blue-500/15',
        };
    }
}
