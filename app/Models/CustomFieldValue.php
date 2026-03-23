<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomFieldValue extends Model
{
    protected $fillable = ['tenant_id', 'custom_field_id', 'model_type', 'model_id', 'value'];

    public function customField(): BelongsTo { return $this->belongsTo(CustomField::class); }
    public function model(): MorphTo         { return $this->morphTo(); }
    public function tenant(): BelongsTo      { return $this->belongsTo(Tenant::class); }
}
