<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRateHistory extends Model
{
    protected $fillable = ['tenant_id', 'currency_code', 'rate_to_idr', 'date'];
    protected $casts = ['rate_to_idr' => 'float', 'date' => 'date'];
}
