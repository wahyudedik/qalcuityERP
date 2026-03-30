<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HarvestLogWorker extends Model
{
    protected $fillable = ['harvest_log_id', 'employee_id', 'worker_name', 'quantity_picked', 'unit', 'wage'];

    protected function casts(): array
    {
        return ['quantity_picked' => 'decimal:3', 'wage' => 'decimal:2'];
    }

    public function harvestLog(): BelongsTo { return $this->belongsTo(HarvestLog::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
