<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'user_id', 'chat_session_id', 'month',
        'message_count', 'token_count',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /** Tambah 1 pesan untuk tenant bulan ini, return total bulan ini */
    public static function track(int $tenantId, int $userId, int $tokens = 0): int
    {
        $month = now()->format('Y-m');

        $log = static::firstOrCreate(
            ['tenant_id' => $tenantId, 'user_id' => $userId, 'month' => $month],
            ['message_count' => 0, 'token_count' => 0]
        );

        static::where('id', $log->id)->update([
            'message_count' => \Illuminate\Support\Facades\DB::raw('message_count + 1'),
            'token_count'   => \Illuminate\Support\Facades\DB::raw("token_count + {$tokens}"),
        ]);

        return static::where('tenant_id', $tenantId)
            ->where('month', $month)
            ->sum('message_count');
    }

    /** Total pesan AI tenant bulan ini (semua user) */
    public static function tenantMonthlyCount(int $tenantId): int
    {
        return static::where('tenant_id', $tenantId)
            ->where('month', now()->format('Y-m'))
            ->sum('message_count');
    }
}
