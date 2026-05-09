<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebToPrintOrder extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'order_number',
        'customer_id',
        'customer_email',
        'customer_name',
        'product_template',
        'customization_data',
        'uploaded_file_path',
        'quantity',
        'unit_price',
        'total_price',
        'payment_status',
        'fulfillment_status',
        'print_job_id',
        'shipping_address',
        'tracking_number',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'special_instructions',
    ];

    protected $casts = [
        'customization_data' => 'array',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function printJob()
    {
        return $this->belongsTo(PrintJob::class);
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'yellow',
            'paid' => 'green',
            'refunded' => 'red',
            default => 'gray'
        };
    }

    public function getFulfillmentStatusColorAttribute(): string
    {
        return match ($this->fulfillment_status) {
            'pending' => 'gray',
            'in_production' => 'blue',
            'shipped' => 'purple',
            'delivered' => 'green',
            default => 'gray'
        };
    }
}
