<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class AssetDepreciation extends Model
{
    use BelongsToTenant;
    protected $fillable = ['tenant_id', 'asset_id', 'period', 'depreciation_amount', 'book_value_after', 'journal_entry_id'];
    protected $casts = ['depreciation_amount' => 'float', 'book_value_after' => 'float'];

    public function asset() { return $this->belongsTo(Asset::class); }
    public function journalEntry() { return $this->belongsTo(\App\Models\JournalEntry::class); }
}
