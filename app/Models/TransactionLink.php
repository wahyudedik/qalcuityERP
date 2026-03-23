<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TransactionLink extends Model
{
    protected $fillable = [
        'tenant_id',
        'source_type', 'source_id', 'source_number',
        'target_type', 'target_id', 'target_number',
        'link_type', 'amount',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    public function source(): MorphTo { return $this->morphTo('source'); }
    public function target(): MorphTo { return $this->morphTo('target'); }

    /**
     * Buat link antara dua transaksi.
     */
    public static function link(
        int    $tenantId,
        object $source,
        object $target,
        string $linkType,
        ?float $amount = null
    ): self {
        return self::firstOrCreate([
            'tenant_id'   => $tenantId,
            'source_type' => get_class($source),
            'source_id'   => $source->id,
            'target_type' => get_class($target),
            'target_id'   => $target->id,
            'link_type'   => $linkType,
        ], [
            'source_number' => $source->number ?? null,
            'target_number' => $target->number ?? null,
            'amount'        => $amount,
        ]);
    }

    /**
     * Ambil semua transaksi yang terhubung ke sebuah model (ke atas dan ke bawah).
     * Return array berisi chain lengkap.
     */
    public static function chainFor(int $tenantId, object $model): array
    {
        $type = get_class($model);
        $id   = $model->id;

        // Upstream: transaksi yang menjadi sumber dari model ini
        $upstream = self::where('tenant_id', $tenantId)
            ->where('target_type', $type)
            ->where('target_id', $id)
            ->get();

        // Downstream: transaksi yang lahir dari model ini
        $downstream = self::where('tenant_id', $tenantId)
            ->where('source_type', $type)
            ->where('source_id', $id)
            ->get();

        return [
            'upstream'   => $upstream,
            'downstream' => $downstream,
        ];
    }

    /** Label yang mudah dibaca untuk link_type */
    public function linkTypeLabel(): string
    {
        return match ($this->link_type) {
            'so_to_do'           => 'SO → Surat Jalan',
            'do_to_invoice'      => 'Surat Jalan → Invoice',
            'so_to_invoice'      => 'SO → Invoice',
            'invoice_to_payment' => 'Invoice → Pembayaran',
            'invoice_to_gl'      => 'Invoice → Jurnal GL',
            'so_to_gl'           => 'SO → Jurnal GL',
            'payment_to_gl'      => 'Pembayaran → Jurnal GL',
            'return_to_invoice'  => 'Retur → Invoice',
            'dp_to_invoice'      => 'Uang Muka → Invoice',
            'bulk_to_invoice'    => 'Bulk Payment → Invoice',
            default              => str_replace('_', ' → ', $this->link_type),
        };
    }

    /** Short class name untuk display */
    public function sourceShortType(): string
    {
        return class_basename($this->source_type);
    }

    public function targetShortType(): string
    {
        return class_basename($this->target_type);
    }
}
