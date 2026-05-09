<?php

namespace App\Services;

use App\Models\MarketPrice;
use App\Models\PriceAlert;

class MarketPriceMonitorService
{
    /**
     * Record market price manually
     */
    public function recordPrice(array $data, int $tenantId): MarketPrice
    {
        $commodity = $data['commodity'];
        $priceDate = $data['price_date'] ?? today();

        // Get previous price for comparison
        $previousPrice = MarketPrice::where('tenant_id', $tenantId)
            ->where('commodity', $commodity)
            ->where('price_date', '<', $priceDate)
            ->orderBy('price_date', 'desc')
            ->first();

        $priceChangePercent = 0;
        if ($previousPrice && $previousPrice->price_per_kg > 0) {
            $priceChangePercent = (($data['price_per_kg'] - $previousPrice->price_per_kg) / $previousPrice->price_per_kg) * 100;
        }

        return MarketPrice::create([
            'tenant_id' => $tenantId,
            'commodity' => $commodity,
            'market_name' => $data['market_name'] ?? null,
            'location' => $data['location'] ?? null,
            'price_per_kg' => $data['price_per_kg'],
            'currency' => $data['currency'] ?? 'IDR',
            'unit' => $data['unit'] ?? 'kg',
            'quality_grade' => $data['quality_grade'] ?? null,
            'price_date' => $priceDate,
            'price_source' => 'manual',
            'previous_price' => $previousPrice?->price_per_kg,
            'price_change_percent' => round($priceChangePercent, 2),
            'market_notes' => $data['market_notes'] ?? null,
        ]);
    }

    /**
     * Get current prices for commodities
     */
    public function getCurrentPrices(int $tenantId, array $commodities): array
    {
        $prices = [];

        foreach ($commodities as $commodity) {
            $latest = MarketPrice::where('tenant_id', $tenantId)
                ->where('commodity', $commodity)
                ->orderBy('price_date', 'desc')
                ->first();

            if ($latest) {
                $prices[$commodity] = [
                    'price_per_kg' => $latest->price_per_kg,
                    'currency' => $latest->currency,
                    'price_date' => $latest->price_date,
                    'change_percent' => $latest->price_change_percent,
                    'change_direction' => $latest->change_direction,
                    'market_name' => $latest->market_name,
                ];
            }
        }

        return $prices;
    }

    /**
     * Get price trends
     */
    public function getPriceTrends(int $tenantId, string $commodity, int $days = 30): array
    {
        $prices = MarketPrice::where('tenant_id', $tenantId)
            ->where('commodity', $commodity)
            ->where('price_date', '>=', now()->subDays($days))
            ->orderBy('price_date')
            ->get();

        if ($prices->isEmpty()) {
            return [
                'commodity' => $commodity,
                'data_points' => 0,
                'trend' => 'no_data',
                'prices' => [],
                'statistics' => null,
            ];
        }

        // Calculate trend
        $firstPrice = $prices->first()->price_per_kg;
        $lastPrice = $prices->last()->price_per_kg;
        $trendPercent = $firstPrice > 0 ? (($lastPrice - $firstPrice) / $firstPrice) * 100 : 0;

        $trend = match (true) {
            $trendPercent > 5 => 'upward',
            $trendPercent < -5 => 'downward',
            default => 'stable'
        };

        // Statistics
        $statistics = [
            'average' => round($prices->avg('price_per_kg'), 2),
            'min' => round($prices->min('price_per_kg'), 2),
            'max' => round($prices->max('price_per_kg'), 2),
            'current' => round($lastPrice, 2),
            'change_percent' => round($trendPercent, 2),
        ];

        return [
            'commodity' => $commodity,
            'data_points' => $prices->count(),
            'trend' => $trend,
            'prices' => $prices->map(fn ($p) => [
                'date' => $p->price_date,
                'price' => $p->price_per_kg,
                'change' => $p->price_change_percent,
            ])->toArray(),
            'statistics' => $statistics,
        ];
    }

    /**
     * Set price alert
     */
    public function setAlert(array $data, int $tenantId): PriceAlert
    {
        return PriceAlert::create([
            'tenant_id' => $tenantId,
            'commodity' => $data['commodity'],
            'target_price' => $data['target_price'],
            'condition' => $data['condition'], // above, below, equals
            'is_active' => true,
            'notification_channels' => $data['notification_channels'] ?? ['email'],
            'has_triggered' => false,
        ]);
    }

    /**
     * Check and trigger alerts
     */
    public function checkAlerts(int $tenantId): array
    {
        $alerts = PriceAlert::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('has_triggered', false)
            ->get();

        $triggered = [];

        foreach ($alerts as $alert) {
            // Get latest price for commodity
            $latestPrice = MarketPrice::where('tenant_id', $tenantId)
                ->where('commodity', $alert->commodity)
                ->orderBy('price_date', 'desc')
                ->first();

            if ($latestPrice) {
                if ($alert->checkAndTrigger($latestPrice->price_per_kg)) {
                    $triggered[] = [
                        'alert_id' => $alert->id,
                        'commodity' => $alert->commodity,
                        'target_price' => $alert->target_price,
                        'current_price' => $latestPrice->price_per_kg,
                        'condition' => $alert->condition,
                    ];
                }
            }
        }

        return $triggered;
    }

    /**
     * Get best selling time recommendation
     */
    public function getBestSellingTime(int $tenantId, string $commodity): array
    {
        $trend = $this->getPriceTrends($tenantId, $commodity, 90); // Last 90 days

        if (! $trend['statistics']) {
            return [
                'recommendation' => 'Insufficient data',
                'confidence' => 'low',
            ];
        }

        $stats = $trend['statistics'];
        $currentPrice = $stats['current'];
        $averagePrice = $stats['average'];
        $maxPrice = $stats['max'];

        if ($currentPrice >= $maxPrice * 0.95) {
            return [
                'recommendation' => 'SELL NOW - Price near peak',
                'confidence' => 'high',
                'reason' => "Current price (Rp {$currentPrice}) is within 5% of 90-day high (Rp {$maxPrice})",
                'action' => 'sell_immediately',
            ];
        } elseif ($currentPrice > $averagePrice * 1.1) {
            return [
                'recommendation' => 'GOOD TIME TO SELL',
                'confidence' => 'medium',
                'reason' => 'Price is 10% above average',
                'action' => 'consider_selling',
            ];
        } elseif ($trend['trend'] === 'upward') {
            return [
                'recommendation' => 'WAIT - Price trending up',
                'confidence' => 'medium',
                'reason' => "Prices have increased {$stats['change_percent']}% in 90 days",
                'action' => 'hold',
            ];
        } else {
            return [
                'recommendation' => 'HOLD - Wait for better price',
                'confidence' => 'low',
                'reason' => 'Current price below average',
                'action' => 'hold',
            ];
        }
    }

    /**
     * Get market summary
     */
    public function getMarketSummary(int $tenantId): array
    {
        $commodities = MarketPrice::where('tenant_id', $tenantId)
            ->select('commodity')
            ->distinct()
            ->pluck('commodity');

        $summary = [];
        foreach ($commodities as $commodity) {
            $latest = MarketPrice::where('tenant_id', $tenantId)
                ->where('commodity', $commodity)
                ->orderBy('price_date', 'desc')
                ->first();

            if ($latest) {
                $summary[] = [
                    'commodity' => $commodity,
                    'current_price' => $latest->price_per_kg,
                    'change_percent' => $latest->price_change_percent,
                    'trend' => $latest->change_direction,
                    'last_updated' => $latest->price_date,
                ];
            }
        }

        return $summary;
    }
}
