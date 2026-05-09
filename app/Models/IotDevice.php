<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class IotDevice extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'device_id',
        'device_token',
        'device_type',
        'location',
        'target_module',
        'sensor_types',
        'firmware_version',
        'is_active',
        'is_connected',
        'last_seen_at',
        'config',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sensor_types' => 'array',
            'config' => 'array',
            'is_active' => 'boolean',
            'is_connected' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function telemetryLogs(): HasMany
    {
        return $this->hasMany(IotTelemetryLog::class);
    }

    public function latestTelemetry(): HasMany
    {
        return $this->hasMany(IotTelemetryLog::class)->latest('recorded_at')->limit(10);
    }

    /** Generate token unik untuk device baru */
    public static function generateToken(): string
    {
        return Str::random(32).bin2hex(random_bytes(16));
    }

    public static function deviceTypes(): array
    {
        return ['esp32' => 'ESP32', 'arduino' => 'Arduino', 'raspberry_pi' => 'Raspberry Pi', 'generic' => 'Generic'];
    }

    public static function targetModules(): array
    {
        return [
            'inventory' => 'Inventory / Gudang',
            'manufacturing' => 'Manufacturing / Produksi',
            'livestock' => 'Peternakan',
            'fisheries' => 'Perikanan',
            'agriculture' => 'Pertanian',
            'hrm' => 'HRM / Karyawan',
            'healthcare' => 'Healthcare',
            'general' => 'Umum',
        ];
    }

    public static function sensorTypes(): array
    {
        return [
            'temperature' => 'Suhu (°C)',
            'humidity' => 'Kelembaban (%)',
            'weight' => 'Berat (kg)',
            'counter' => 'Counter / Hitungan',
            'ph' => 'pH Air',
            'turbidity' => 'Kekeruhan Air',
            'gps' => 'GPS / Lokasi',
            'motion' => 'Sensor Gerak',
            'door' => 'Sensor Pintu',
            'custom' => 'Custom',
        ];
    }
}
