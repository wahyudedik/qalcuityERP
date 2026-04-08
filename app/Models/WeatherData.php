<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * WeatherData Model
 * 
 * Menyimpan data cuaca dari API external untuk prediksi panen
 * dan rekomendasi aktivitas pertanian.
 */
class WeatherData extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'location_name',
        'latitude',
        'longitude',
        'temperature',
        'feels_like',
        'humidity',
        'pressure',
        'wind_speed',
        'wind_direction',
        'rainfall',
        'weather_condition',
        'weather_description',
        'visibility',
        'uv_index',
        'forecast_date',
        'forecast_type', // current, hourly, daily
        'data_source',   // openweathermap, accuweather, etc
        'raw_data',      // JSON response from API
    ];

    protected $casts = [
        'temperature' => 'float',
        'feels_like' => 'float',
        'humidity' => 'float',
        'pressure' => 'float',
        'wind_speed' => 'float',
        'rainfall' => 'float',
        'uv_index' => 'float',
        'visibility' => 'float',
        'forecast_date' => 'datetime',
        'raw_data' => 'array',
    ];

    /**
     * Relationships
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scopes
     */
    public function scopeCurrent($query)
    {
        return $query->where('forecast_type', 'current');
    }

    public function scopeForecast($query)
    {
        return $query->where('forecast_type', '!=', 'current');
    }

    public function scopeLocation($query, $lat, $lng)
    {
        return $query->where('latitude', $lat)
            ->where('longitude', $lng);
    }

    /**
     * Check if weather is suitable for farming activities
     */
    public function isSuitableForFarming(): bool
    {
        // Not suitable if heavy rain or extreme conditions
        if ($this->rainfall > 50)
            return false; // Heavy rain
        if ($this->wind_speed > 40)
            return false; // Strong wind
        if ($this->temperature < 5 || $this->temperature > 40)
            return false; // Extreme temp

        return true;
    }

    /**
     * Get farming recommendations based on weather
     */
    public function getFarmingRecommendations(): array
    {
        $recommendations = [];

        if ($this->rainfall > 20) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Hujan deras diprediksi. Tunda penyemprotan pestisida.',
                'action' => 'postpone_spraying'
            ];
        }

        if ($this->humidity > 80) {
            $recommendations[] = [
                'type' => 'alert',
                'message' => 'Kelembaban tinggi. Waspadai serangan jamur.',
                'action' => 'check_fungal_disease'
            ];
        }

        if ($this->temperature > 35) {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'Suhu tinggi. Tingkatkan frekuensi irigasi.',
                'action' => 'increase_irrigation'
            ];
        }

        if ($this->wind_speed > 20) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Angin kencang. Amankan tanaman muda.',
                'action' => 'secure_young_plants'
            ];
        }

        if ($this->isSuitableForFarming()) {
            $recommendations[] = [
                'type' => 'success',
                'message' => 'Cuaca ideal untuk aktivitas pertanian.',
                'action' => 'proceed_normal'
            ];
        }

        return $recommendations;
    }

    /**
     * Predict harvest readiness based on weather patterns
     */
    public function predictHarvestReadiness(string $cropType, int $daysPlanted): array
    {
        $predictions = [
            'readiness_percentage' => 0,
            'estimated_days_to_harvest' => 0,
            'quality_prediction' => 'unknown',
            'risks' => [],
        ];

        // Simple prediction logic (can be enhanced with ML)
        switch ($cropType) {
            case 'rice':
                $harvestDays = 90;
                break;
            case 'corn':
                $harvestDays = 75;
                break;
            case 'tomato':
                $harvestDays = 60;
                break;
            default:
                $harvestDays = 90;
        }

        $progress = min(100, ($daysPlanted / $harvestDays) * 100);
        $remainingDays = max(0, $harvestDays - $daysPlanted);

        // Adjust based on weather
        if ($this->rainfall < 10 && $daysPlanted > $harvestDays * 0.8) {
            $predictions['quality_prediction'] = 'excellent';
        } elseif ($this->rainfall > 50) {
            $predictions['risks'][] = 'Excessive rain may reduce quality';
            $predictions['quality_prediction'] = 'fair';
        } else {
            $predictions['quality_prediction'] = 'good';
        }

        $predictions['readiness_percentage'] = round($progress, 2);
        $predictions['estimated_days_to_harvest'] = $remainingDays;

        return $predictions;
    }
}
