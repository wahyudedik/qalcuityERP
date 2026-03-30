<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HarvestLogGrade extends Model
{
    protected $fillable = ['harvest_log_id', 'grade', 'quantity', 'unit', 'price_per_unit', 'notes'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'price_per_unit' => 'decimal:2'];
    }

    public function harvestLog(): BelongsTo { return $this->belongsTo(HarvestLog::class); }

    public function subtotal(): float { return (float) $this->quantity * (float) $this->price_per_unit; }
}
