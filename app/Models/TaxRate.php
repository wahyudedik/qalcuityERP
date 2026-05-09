<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'code', 'type', 'tax_type', 'rate', 'is_active', 'is_withholding', 'account_code'];

    protected $casts = ['rate' => 'float', 'is_active' => 'boolean', 'is_withholding' => 'boolean'];

    public function getTypeLabel(): string
    {
        return match ($this->tax_type ?? 'ppn') {
            'ppn' => 'PPN',
            'pph21' => 'PPh 21',
            'pph23' => 'PPh 23',
            'pph4ayat2' => 'PPh 4 Ayat 2',
            default => strtoupper($this->tax_type ?? 'Custom'),
        };
    }
}
