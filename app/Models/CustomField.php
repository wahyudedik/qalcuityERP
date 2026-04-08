<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'module', 'key', 'label', 'type',
        'options', 'required', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'options'    => 'array',
        'required'   => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function values(): HasMany   { return $this->hasMany(CustomFieldValue::class); }

    /** Modul yang didukung */
    public static function supportedModules(): array
    {
        return [
            'invoice'      => 'Invoice',
            'product'      => 'Produk',
            'customer'     => 'Customer',
            'supplier'     => 'Supplier',
            'employee'     => 'Karyawan',
            'sales_order'  => 'Sales Order',
            'purchase_order' => 'Purchase Order',
            'expense'      => 'Pengeluaran',
        ];
    }

    /** Tipe field yang didukung */
    public static function supportedTypes(): array
    {
        return [
            'text'     => 'Teks',
            'number'   => 'Angka',
            'date'     => 'Tanggal',
            'select'   => 'Pilihan (Dropdown)',
            'checkbox' => 'Centang (Ya/Tidak)',
            'textarea' => 'Teks Panjang',
        ];
    }
}
