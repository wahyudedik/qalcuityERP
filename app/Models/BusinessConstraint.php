<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessConstraint extends Model
{
    protected $fillable = [
        'tenant_id', 'key', 'label', 'value_type', 'value', 'is_active', 'description',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    /** Ambil nilai yang sudah di-cast sesuai value_type */
    public function typedValue(): mixed
    {
        return match ($this->value_type) {
            'boolean'    => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'percentage' => (float) $this->value,
            'amount'     => (float) $this->value,
            'integer'    => (int) $this->value,
            default      => $this->value,
        };
    }

    /**
     * Definisi constraint default yang akan di-seed untuk tenant baru.
     */
    public static function defaults(): array
    {
        return [
            [
                'key'         => 'no_sell_below_cost',
                'label'       => 'Tidak boleh jual di bawah HPP',
                'value_type'  => 'boolean',
                'value'       => 'false',
                'description' => 'Mencegah penjualan dengan harga di bawah harga pokok produk.',
            ],
            [
                'key'         => 'max_discount_pct',
                'label'       => 'Batas maksimal diskon (%)',
                'value_type'  => 'percentage',
                'value'       => '30',
                'description' => 'Persentase diskon maksimal yang diizinkan per transaksi.',
            ],
            [
                'key'         => 'min_cash_balance',
                'label'       => 'Saldo kas minimum (Rp)',
                'value_type'  => 'amount',
                'value'       => '0',
                'description' => 'Transaksi pengeluaran kas tidak boleh membuat saldo di bawah nilai ini.',
            ],
            [
                'key'         => 'confirm_above_amount',
                'label'       => 'Konfirmasi transaksi di atas nominal (Rp)',
                'value_type'  => 'amount',
                'value'       => '0',
                'description' => 'Transaksi di atas nominal ini memerlukan konfirmasi eksplisit. 0 = nonaktif.',
            ],
            [
                'key'         => 'require_cost_center',
                'label'       => 'Wajib isi Cost Center',
                'value_type'  => 'boolean',
                'value'       => 'false',
                'description' => 'Setiap transaksi wajib memiliki cost center yang dipilih.',
            ],
            [
                'key'         => 'allow_negative_stock',
                'label'       => 'Izinkan stok negatif',
                'value_type'  => 'boolean',
                'value'       => 'false',
                'description' => 'Jika false, transaksi yang membuat stok negatif akan ditolak.',
            ],
        ];
    }

    /** Seed constraint default untuk tenant baru */
    public static function seedForTenant(int $tenantId): void
    {
        foreach (self::defaults() as $def) {
            self::firstOrCreate(
                ['tenant_id' => $tenantId, 'key' => $def['key']],
                array_merge($def, ['tenant_id' => $tenantId, 'is_active' => true])
            );
        }
    }
}
