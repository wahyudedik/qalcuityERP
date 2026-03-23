<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'webhook_subscription_id', 'event', 'payload',
        'response_code', 'response_body', 'status', 'attempt',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(WebhookSubscription::class, 'webhook_subscription_id');
    }
}
