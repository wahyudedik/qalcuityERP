<?php

namespace App\Services;

use App\Models\TenantApiSetting;
use App\Models\WeatherData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherIntegrationService
{
    protected string $apiKey;
    protected string $baseUrl;

    /**
     * Constructor - Pastikan selalu mendapat tenant_id
     * 
     * @param int|null $tenantId Tenant ID (opsional, akan fallback ke auth user jika null)
     */
    public function __construct(protected ?int $tenantId = null)
    {
        // Jika tenant_id tidak dipassing, coba ambil dari user yang login
        if (!$this->tenantId) {
            $this->tenantId = auth()->user()?->tenant_id;
        }

        // Read weather API key from tenant DB settings, fallback to config/.env
        // PENTING: Setiap tenant bisa punya API key sendiri via TenantApiSetting
        $this->apiKey = ($this->tenantId ? TenantApiSetting::get($this->tenantId, 'weather_api_key') : null)
            ?? config('services.weather.api_key', env('WEATHER_API_KEY', ''));

        $this->baseUrl = 'https://api.openweathermap.org/data/2.5';
    }

    /**
     * Get current weather for location
     */
    public function getCurrentWeather(float $lat, float $lng, int $tenantId): ?WeatherData
    {
        try {
            $cacheKey = "weather_current_{$lat}_{$lng}";

            return Cache::remember($cacheKey, 600, function () use ($lat, $lng, $tenantId) {
                $response = Http::get("{$this->baseUrl}/weather", [
                    'lat' => $lat,
                    'lon' => $lng,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                ]);

                if (!$response->ok()) {
                    Log::error('Weather API error: ' . $response->status());
                    return null;
                }

                $data = $response->json();

                return WeatherData::create([
                    'tenant_id' => $tenantId,
                    'location_name' => $data['name'] ?? null,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'temperature' => $data['main']['temp'],
                    'feels_like' => $data['main']['feels_like'],
                    'humidity' => $data['main']['humidity'],
                    'pressure' => $data['main']['pressure'],
                    'wind_speed' => $data['wind']['speed'],
                    'wind_direction' => $data['wind']['deg'] ?? null,
                    'rainfall' => $data['rain']['1h'] ?? 0,
                    'weather_condition' => $data['weather'][0]['main'] ?? null,
                    'weather_description' => $data['weather'][0]['description'] ?? null,
                    'visibility' => $data['visibility'] ?? null,
                    'uv_index' => null,
                    'forecast_date' => now(),
                    'forecast_type' => 'current',
                    'data_source' => 'openweathermap',
                    'raw_data' => $data,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('WeatherIntegrationService::getCurrentWeather failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get 7-day forecast
     */
    public function getForecast(float $lat, float $lng, int $tenantId): array
    {
        try {
            $cacheKey = "weather_forecast_{$lat}_{$lng}";

            return Cache::remember($cacheKey, 3600, function () use ($lat, $lng, $tenantId) {
                $response = Http::get("{$this->baseUrl}/forecast", [
                    'lat' => $lat,
                    'lon' => $lng,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'cnt' => 40, // 5 days * 8 (3-hour intervals)
                ]);

                if (!$response->ok()) {
                    return [];
                }

                $data = $response->json();
                $forecasts = [];

                foreach ($data['list'] as $item) {
                    $weather = WeatherData::create([
                        'tenant_id' => $tenantId,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'temperature' => $item['main']['temp'],
                        'feels_like' => $item['main']['feels_like'],
                        'humidity' => $item['main']['humidity'],
                        'pressure' => $item['main']['pressure'],
                        'wind_speed' => $item['wind']['speed'],
                        'rainfall' => $item['rain']['3h'] ?? 0,
                        'weather_condition' => $item['weather'][0]['main'] ?? null,
                        'weather_description' => $item['weather'][0]['description'] ?? null,
                        'forecast_date' => $item['dt_txt'],
                        'forecast_type' => 'hourly',
                        'data_source' => 'openweathermap',
                        'raw_data' => $item,
                    ]);

                    $forecasts[] = $weather;
                }

                return $forecasts;
            });
        } catch (\Throwable $e) {
            Log::error('WeatherIntegrationService::getForecast failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get farming recommendations based on weather
     */
    public function getFarmingRecommendations(float $lat, float $lng, int $tenantId): array
    {
        $weather = $this->getCurrentWeather($lat, $lng, $tenantId);

        if (!$weather) {
            return [];
        }

        return $weather->getFarmingRecommendations();
    }

    /**
     * Predict harvest readiness
     */
    public function predictHarvest(string $cropType, int $daysPlanted, float $lat, float $lng, int $tenantId): array
    {
        $weather = $this->getCurrentWeather($lat, $lng, $tenantId);

        if (!$weather) {
            return [
                'readiness_percentage' => 0,
                'estimated_days_to_harvest' => 0,
                'quality_prediction' => 'unknown',
                'risks' => ['Weather data unavailable'],
            ];
        }

        return $weather->predictHarvestReadiness($cropType, $daysPlanted);
    }

    /**
     * Check for severe weather alerts
     */
    public function checkSevereWeather(float $lat, float $lng): array
    {
        $tenantId = $this->tenantId ?? auth()->user()?->tenant_id;
        $weather = $this->getCurrentWeather($lat, $lng, $tenantId ?? 0);

        if (!$weather) {
            return [];
        }

        $alerts = [];

        if ($weather->rainfall > 50) {
            $alerts[] = [
                'type' => 'heavy_rain',
                'severity' => 'warning',
                'message' => 'Hujan sangat deras terdeteksi. Risiko banjir dan erosi.',
            ];
        }

        if ($weather->wind_speed > 40) {
            $alerts[] = [
                'type' => 'strong_wind',
                'severity' => 'warning',
                'message' => 'Angin kencang. Amankan struktur dan tanaman.',
            ];
        }

        if ($weather->temperature > 40) {
            $alerts[] = [
                'type' => 'heat_wave',
                'severity' => 'critical',
                'message' => 'Suhu ekstrem tinggi. Tingkatkan irigasi segera.',
            ];
        }

        if ($weather->temperature < 5) {
            $alerts[] = [
                'type' => 'frost',
                'severity' => 'warning',
                'message' => 'Suhu sangat rendah. Waspadai embun beku.',
            ];
        }

        return $alerts;
    }
}
