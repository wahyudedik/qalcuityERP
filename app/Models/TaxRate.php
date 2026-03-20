<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = ['tenant_id', 'name', 'code', 'type', 'rate', 'is_active'];
    protected $casts = ['rate' => 'float', 'is_active' => 'boolean'];
}
