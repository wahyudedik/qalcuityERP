<?php

namespace App\Services\AI;

use App\Models\DynamicPricingHistory;
use App\Models\DynamicPricingRule;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class DynamicPricingService
{
    /**
     * Calculate dynamic price for product
     */
    public function calculatePrice(Product $product): array
    {
        try {
            $basePrice = $product->price;
            $factors = [];

            // Factor 1: Demand analysis
            $demandFactor = $this->analyzeDemand($product);
            $factors['demand'] = $demandFactor;

            // Factor 2: Stock level
            $stockFactor = $this->analyzeStockLevel($product);
            $factors['stock'] = $stockFactor;

            // Factor 3: Seasonality
            $seasonFactor = $this->analyzeSeasonality($product);
            $factors['seasonality'] = $seasonFactor;

            // Factor 4: Time-based (day of week, hour)
            $timeFactor = $this->analyzeTimeBased($product);
            $factors['time'] = $timeFactor;

            // Factor 5: Competitor pricing (if available)
            $competitorFactor = $this->analyzeCompetitorPricing($product);
            $factors['competitor'] = $competitorFactor;

            // Calculate recommended price
            $multiplier = $this->calculateMultiplier($factors);
            $recommendedPrice = $basePrice * $multiplier;

            // Apply min/max constraints
            $minPrice = $basePrice * 0.8; // Min 80% of base
            $maxPrice = $basePrice * 1.5; // Max 150% of base
            $finalPrice = max($minPrice, min($maxPrice, $recommendedPrice));

            // Generate reason
            $reason = $this->generatePriceChangeReason($factors, $multiplier);

            return [
                'success' => true,
                'product_id' => $product->id,
                'base_price' => $basePrice,
                'recommended_price' => round($finalPrice, 0),
                'multiplier' => round($multiplier, 4),
                'factors' => $factors,
                'reason' => $reason,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
            ];

        } catch (\Throwable $e) {
            Log::error('Dynamic pricing calculation failed: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Analyze demand based on sales history
     */
    protected function analyzeDemand(Product $product): array
    {
        // Get sales data for last 30 days
        $salesLast30Days = $this->getSalesData($product, 30);
        $salesPrevious30Days = $this->getSalesData($product, 60, 30);

        $currentRate = count($salesLast30Days) / 30;
        $previousRate = count($salesPrevious30Days) / 30;

        $trend = $previousRate > 0 ? ($currentRate - $previousRate) / $previousRate : 0;

        return [
            'current_daily_sales' => round($currentRate, 2),
            'previous_daily_sales' => round($previousRate, 2),
            'trend_percentage' => round($trend * 100, 2),
            'demand_level' => $trend > 0.2 ? 'high' : ($trend < -0.2 ? 'low' : 'normal'),
            'impact_multiplier' => 1 + ($trend * 0.3), // Up to 30% adjustment
        ];
    }

    /**
     * Analyze stock level impact
     */
    protected function analyzeStockLevel(Product $product): array
    {
        $stock = $product->stock ?? 0;
        $avgDailySales = $this->getAverageDailySales($product);

        $daysOfStock = $avgDailySales > 0 ? $stock / $avgDailySales : 999;

        if ($daysOfStock < 7) {
            $multiplier = 1.2; // Increase price when low stock
            $level = 'critical';
        } elseif ($daysOfStock < 14) {
            $multiplier = 1.1;
            $level = 'low';
        } elseif ($daysOfStock > 90) {
            $multiplier = 0.9; // Decrease price when overstocked
            $level = 'excess';
        } else {
            $multiplier = 1.0;
            $level = 'optimal';
        }

        return [
            'current_stock' => $stock,
            'avg_daily_sales' => round($avgDailySales, 2),
            'days_of_stock' => round($daysOfStock, 1),
            'stock_level' => $level,
            'impact_multiplier' => $multiplier,
        ];
    }

    /**
     * Analyze seasonality
     */
    protected function analyzeSeasonality(Product $product): array
    {
        $currentMonth = now()->month;
        $category = $product->category ?? 'general';

        // Seasonal patterns by category
        $seasonalPatterns = [
            'food' => [12 => 1.3, 1 => 1.2], // Higher in Dec-Jan
            'clothing' => [6 => 1.2, 12 => 1.4], // Higher in Jun, Dec
            'electronics' => [11 => 1.3, 12 => 1.5], // Higher in Nov-Dec
            'general' => [],
        ];

        $pattern = $seasonalPatterns[$category] ?? [];
        $multiplier = $pattern[$currentMonth] ?? 1.0;

        return [
            'current_month' => $currentMonth,
            'category' => $category,
            'is_peak_season' => $multiplier > 1.0,
            'impact_multiplier' => $multiplier,
        ];
    }

    /**
     * Analyze time-based factors
     */
    protected function analyzeTimeBased(Product $product): array
    {
        $hour = now()->hour;
        $dayOfWeek = now()->dayOfWeek;

        // Weekend premium
        $weekendMultiplier = in_array($dayOfWeek, [0, 6]) ? 1.05 : 1.0;

        // Peak hours (10am-2pm, 6pm-9pm)
        $peakHoursMultiplier = (in_array($hour, range(10, 14)) || in_array($hour, range(18, 21)))
            ? 1.03
            : 1.0;

        return [
            'hour' => $hour,
            'day_of_week' => $dayOfWeek,
            'is_weekend' => in_array($dayOfWeek, [0, 6]),
            'is_peak_hour' => in_array($hour, range(10, 14)) || in_array($hour, range(18, 21)),
            'impact_multiplier' => $weekendMultiplier * $peakHoursMultiplier,
        ];
    }

    /**
     * Analyze competitor pricing
     */
    protected function analyzeCompetitorPricing(Product $product): array
    {
        // This would integrate with competitor price monitoring
        // For now, return neutral
        return [
            'competitors_found' => 0,
            'avg_competitor_price' => null,
            'our_position' => 'unknown',
            'impact_multiplier' => 1.0,
        ];
    }

    /**
     * Calculate final multiplier from all factors
     */
    protected function calculateMultiplier(array $factors): float
    {
        $weights = [
            'demand' => 0.35,
            'stock' => 0.25,
            'seasonality' => 0.20,
            'time' => 0.10,
            'competitor' => 0.10,
        ];

        $weightedSum = 0;
        foreach ($factors as $key => $factor) {
            $weight = $weights[$key] ?? 0;
            $weightedSum += $factor['impact_multiplier'] * $weight;
        }

        return $weightedSum;
    }

    /**
     * Generate human-readable reason for price change
     */
    protected function generatePriceChangeReason(array $factors, float $multiplier): string
    {
        $reasons = [];

        if ($factors['demand']['demand_level'] === 'high') {
            $reasons[] = "High demand (+{$factors['demand']['trend_percentage']}%)";
        } elseif ($factors['demand']['demand_level'] === 'low') {
            $reasons[] = "Low demand ({$factors['demand']['trend_percentage']}%)";
        }

        if ($factors['stock']['stock_level'] === 'critical') {
            $reasons[] = "Critical stock level ({$factors['stock']['days_of_stock']} days)";
        } elseif ($factors['stock']['stock_level'] === 'excess') {
            $reasons[] = "Excess inventory ({$factors['stock']['days_of_stock']} days)";
        }

        if ($factors['seasonality']['is_peak_season']) {
            $reasons[] = 'Peak season';
        }

        if ($factors['time']['is_weekend']) {
            $reasons[] = 'Weekend pricing';
        }

        return empty($reasons) ? 'Standard pricing' : implode(', ', $reasons);
    }

    /**
     * Apply dynamic pricing rule
     */
    public function applyRule(int $productId, int $ruleId, int $userId): array
    {
        $product = Product::find($productId);
        $rule = DynamicPricingRule::find($ruleId);

        if (! $product || ! $rule) {
            return ['success' => false, 'error' => 'Product or rule not found'];
        }

        // Calculate price
        $calculation = $this->calculatePrice($product);

        if (! $calculation['success']) {
            return $calculation;
        }

        // Save to history
        $history = DynamicPricingHistory::create([
            'tenant_id' => $product->tenant_id,
            'product_id' => $product->id,
            'rule_id' => $ruleId,
            'original_price' => $product->price,
            'recommended_price' => $calculation['recommended_price'],
            'applied_price' => $calculation['recommended_price'],
            'factors' => $calculation['factors'],
            'reason' => $calculation['reason'],
            'approved' => true,
            'approved_by_user_id' => $userId,
            'approved_at' => now(),
        ]);

        // Update product price
        $product->update(['price' => $calculation['recommended_price']]);

        return [
            'success' => true,
            'history_id' => $history->id,
            'old_price' => $calculation['base_price'],
            'new_price' => $calculation['recommended_price'],
            'change_percentage' => round((($calculation['recommended_price'] - $calculation['base_price']) / $calculation['base_price']) * 100, 2).'%',
        ];
    }

    /**
     * Get pricing recommendations for all products
     */
    public function getRecommendations(int $tenantId, int $limit = 50): array
    {
        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->limit($limit)
            ->get();

        $recommendations = [];
        foreach ($products as $product) {
            $calc = $this->calculatePrice($product);
            if ($calc['success'] && abs($calc['multiplier'] - 1.0) > 0.05) {
                $recommendations[] = array_merge($calc, [
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                ]);
            }
        }

        // Sort by potential impact
        usort($recommendations, function ($a, $b) {
            return abs($b['multiplier'] - 1.0) <=> abs($a['multiplier'] - 1.0);
        });

        return [
            'success' => true,
            'total_products_analyzed' => $products->count(),
            'recommendations_count' => count($recommendations),
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Helper: Get sales data
     */
    protected function getSalesData(Product $product, int $days, int $offset = 0): array
    {
        // This would query actual sales/invoice items
        // Simplified for now
        return [];
    }

    /**
     * Helper: Get average daily sales
     */
    protected function getAverageDailySales(Product $product): float
    {
        // This would calculate from historical sales
        // Simplified for now
        return $product->avg_daily_sales ?? 5.0;
    }

    /**
     * Create pricing rule
     */
    public function createRule(int $tenantId, string $name, array $conditions, array $formula): bool
    {
        DynamicPricingRule::create([
            'tenant_id' => $tenantId,
            'name' => $name,
            'conditions' => $conditions,
            'pricing_formula' => $formula,
            'is_active' => true,
        ]);

        return true;
    }

    /**
     * Get pricing history for product
     */
    public function getPricingHistory(int $productId, int $limit = 20): array
    {
        return DynamicPricingHistory::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
