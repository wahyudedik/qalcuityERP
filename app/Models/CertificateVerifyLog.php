<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateVerifyLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'certificate_number',
        'ip_address',
        'result',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }
}
