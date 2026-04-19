<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class SampleDataLog extends Model
{
    use BelongsToTenant;
protected $fillable = [
        'tenant_id',
        'user_id',
        'template_id',
        'status',
        'generated_data',
        'records_created',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'generated_data' => 'array',
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(SampleDataTemplate::class);
    }
}
