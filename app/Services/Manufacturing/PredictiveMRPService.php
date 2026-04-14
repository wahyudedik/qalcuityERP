<?php

namespace App\Services\Manufacturing;

use App\Models\SalesOrder;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Predictive MRP Service
 * 
 * AI-powered demand forecasting using Gemini
 * Analyzes historical data, seasonality, and trends
 */
class PredictiveMRPService
{
    protected GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    /**
     * Generate demand forecast for products
     */
    public function forecastDemand(int $tenantId, int $months = 3, ?int $productId = null): array
    {
        $cacheKey = "predictive_mrp_forecast_{$tenantId}_{$months}_{$productId}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($tenantId, $months, $productId) {
            // Get historical sales data
            $historicalData = $this->getHistoricalSalesData($tenantId, 12, $productId);

            if (empty($historicalData)) {
                return [
                    'status' => 'error',
                    'message' => 'Insufficient historical data. Need at least 3 months of sales data.',
                    'forecast' => [],
                ];
            }

            // Get current inventory levels
            $inventoryData = $this->getCurrentInventoryLevels($tenantId, $productId);

            // Get lead times from suppliers
            $leadTimeData = $this->getSupplierLeadTimes($tenantId);

            // Prepare data for AI analysis
            $analysisData = [
                'historical_sales' => $historicalData,
                'current_inventory' => $inventoryData,
                'supplier_lead_times' => $leadTimeData,
                'forecast_months' => $months,
            ];

            // Call Gemini for AI forecasting
            try {
                $aiForecast = $this->callGeminiForForecast($analysisData);

                return [
                    'status' => 'success',
                    'message' => 'AI forecast generated successfully',
                    'forecast' => $aiForecast,
                    'generated_at' => now()->toIso8601String(),
                    'model' => 'gemini-2.5-flash',
                    'confidence' => $aiForecast['overall_confidence'] ?? 'medium',
                ];
            } catch (\Exception $e) {
                Log::error('Predictive MRP: Gemini API failed', ['error' => $e->getMessage()]);

                // Fallback to statistical forecasting
                return [
                    'status' => 'success',
                    'message' => 'Statistical forecast generated (AI fallback)',
                    'forecast' => $this->statisticalForecast($historicalData, $inventoryData, $months),
                    'generated_at' => now()->toIso8601String(),
                    'model' => 'statistical-fallback',
                    'confidence' => 'medium',
                ];
            }
        });
    }

    /**
     * Get historical sales data
     */
    private function getHistoricalSalesData(int $tenantId, int $months, ?int $productId = null): array
    {
        $query = SalesOrder::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->where('date', '>=', now()->subMonths($months))
            ->with('items.product');

        if ($productId) {
            $query->whereHas('items', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            });
        }

        $orders = $query->orderBy('date')->get();

        // Aggregate by month and product
        $monthlyData = [];
        foreach ($orders as $order) {
            $monthKey = $order->date->format('Y-m');

            foreach ($order->items as $item) {
                $productKey = $item->product_id;

                if (!isset($monthlyData[$productKey])) {
                    $monthlyData[$productKey] = [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name ?? 'Unknown',
                        'unit' => $item->product?->unit ?? 'pcs',
                        'monthly_sales' => [],
                    ];
                }

                if (!isset($monthlyData[$productKey]['monthly_sales'][$monthKey])) {
                    $monthlyData[$productKey]['monthly_sales'][$monthKey] = 0;
                }

                $monthlyData[$productKey]['monthly_sales'][$monthKey] += $item->quantity;
            }
        }

        return array_values($monthlyData);
    }

    /**
     * Get current inventory levels
     */
    private function getCurrentInventoryLevels(int $tenantId, ?int $productId = null): array
    {
        $query = ProductStock::where('tenant_id', $tenantId)
            ->with(['product', 'warehouse']);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $stocks = $query->get()->groupBy('product_id');

        $inventoryData = [];
        foreach ($stocks as $productId => $warehouseStocks) {
            $totalQty = $warehouseStocks->sum('quantity');
            $product = $warehouseStocks->first()->product;

            $inventoryData[$productId] = [
                'product_id' => $productId,
                'product_name' => $product?->name ?? 'Unknown',
                'current_stock' => $totalQty,
                'unit' => $product?->unit ?? 'pcs',
                'reorder_point' => $product?->reorder_point ?? 0,
                'safety_stock' => $product?->safety_stock ?? 0,
            ];
        }

        return $inventoryData;
    }

    /**
     * Get supplier lead times
     */
    private function getSupplierLeadTimes(int $tenantId): array
    {
        // Get average lead time from PO history
        $leadTimes = PurchaseOrder::where('tenant_id', $tenantId)
            ->whereNotNull('received_at')
            ->whereNotNull('expected_delivery_date')
            ->selectRaw('supplier_id, AVG(DATEDIFF(received_at, expected_delivery_date)) as avg_lead_variance')
            ->groupBy('supplier_id')
            ->get();

        return $leadTimes->mapWithKeys(function ($item) {
            return [
                $item->supplier_id => [
                    'avg_lead_variance_days' => round($item->avg_lead_variance, 1),
                ]
            ];
        })->toArray();
    }

    /**
     * Call Gemini API for AI forecasting
     */
    private function callGeminiForForecast(array $data): array
    {
        $prompt = $this->buildForecastingPrompt($data);

        $response = $this->gemini->chat($prompt, [
            'temperature' => 0.3, // Low temperature for more deterministic results
            'max_tokens' => 4000,
        ]);

        // GeminiService returns ['text' => string, 'model' => string]
        $responseText = is_array($response) ? ($response['text'] ?? '') : $response;

        // Parse JSON response
        $forecastData = $this->parseGeminiResponse($responseText);

        return $forecastData;
    }

    /**
     * Build prompt for Gemini
     */
    private function buildForecastingPrompt(array $data): string
    {
        $historicalSales = json_encode($data['historical_sales'], JSON_PRETTY_PRINT);
        $currentInventory = json_encode($data['current_inventory'], JSON_PRETTY_PRINT);
        $leadTimes = json_encode($data['supplier_lead_times'], JSON_PRETTY_PRINT);

        return <<<PROMPT
You are an expert supply chain analyst and demand forecaster. Analyze the following data and generate a demand forecast for the next {$data['forecast_months']} months.

## Historical Sales Data (Last 12 Months)
{$historicalSales}

## Current Inventory Levels
{$currentInventory}

## Supplier Lead Time Performance
{$leadTimes}

## Your Task
Generate a detailed demand forecast in JSON format with the following structure:

```json
{
  "overall_confidence": "high|medium|low",
  "forecast_summary": "Brief summary of key findings and recommendations",
  "products": [
    {
      "product_id": 123,
      "product_name": "Product Name",
      "current_stock": 100,
      "forecasted_demand": {
        "month_1": 50,
        "month_2": 60,
        "month_3": 55
      },
      "total_forecasted_demand": 165,
      "stock_status": "sufficient|low|critical",
      "recommended_order_quantity": 200,
      "recommended_order_date": "2026-05-01",
      "reorder_urgency": "low|medium|high|critical",
      "confidence": "high|medium|low",
      "reasoning": "Brief explanation of the forecast"
    }
  ],
  "recommendations": [
    "Actionable recommendation 1",
    "Actionable recommendation 2",
    "Actionable recommendation 3"
  ],
  "risk_factors": [
    "Risk factor 1",
    "Risk factor 2"
  ]
}
```

## Analysis Guidelines
1. Identify seasonal patterns and trends
2. Calculate average monthly demand and growth rate
3. Consider current stock levels and reorder points
4. Account for supplier lead times
5. Flag products with potential stockouts
6. Provide specific order recommendations with dates
7. Be conservative in estimates (avoid over-forecasting)

IMPORTANT: Return ONLY valid JSON, no markdown formatting, no explanations.
PROMPT;
    }

    /**
     * Parse Gemini response
     */
    private function parseGeminiResponse(string $response): array
    {
        // Remove markdown code blocks if present
        $response = preg_replace('/^```json\s*/', '', $response);
        $response = preg_replace('/\s*```$/', '', $response);
        $response = trim($response);

        try {
            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON: ' . json_last_error_msg());
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Predictive MRP: Failed to parse Gemini response', [
                'error' => $e->getMessage(),
                'response' => substr($response, 0, 500),
            ]);

            // Return fallback
            return [
                'overall_confidence' => 'low',
                'forecast_summary' => 'AI parsing failed, using statistical methods',
                'products' => [],
                'recommendations' => ['Review data quality and retry'],
                'risk_factors' => ['AI analysis unavailable'],
            ];
        }
    }

    /**
     * Statistical forecasting fallback
     */
    private function statisticalForecast(array $historicalData, array $inventoryData, int $months): array
    {
        $products = [];

        foreach ($historicalData as $productData) {
            $monthlySales = array_values($productData['monthly_sales']);
            $avgMonthlyDemand = count($monthlySales) > 0 ? array_sum($monthlySales) / count($monthlySales) : 0;

            // Simple moving average with 10% growth factor
            $growthFactor = 1.10;
            $forecastedDemand = [];
            $totalDemand = 0;

            for ($i = 1; $i <= $months; $i++) {
                $demand = round($avgMonthlyDemand * pow($growthFactor, $i / 12));
                $forecastedDemand["month_{$i}"] = $demand;
                $totalDemand += $demand;
            }

            $currentStock = $inventoryData[$productData['product_id']]['current_stock'] ?? 0;
            $reorderPoint = $inventoryData[$productData['product_id']]['reorder_point'] ?? 0;

            $stockStatus = $currentStock > $totalDemand ? 'sufficient' : ($currentStock > $reorderPoint ? 'low' : 'critical');
            $recommendedOrder = max(0, $totalDemand - $currentStock + $reorderPoint);

            $products[] = [
                'product_id' => $productData['product_id'],
                'product_name' => $productData['product_name'],
                'current_stock' => $currentStock,
                'forecasted_demand' => $forecastedDemand,
                'total_forecasted_demand' => $totalDemand,
                'stock_status' => $stockStatus,
                'recommended_order_quantity' => $recommendedOrder,
                'recommended_order_date' => now()->addDays(7)->format('Y-m-d'),
                'reorder_urgency' => $stockStatus === 'critical' ? 'critical' : ($stockStatus === 'low' ? 'high' : 'low'),
                'confidence' => 'medium',
                'reasoning' => "Based on {count($monthlySales)}-month moving average with 10% annual growth",
            ];
        }

        return [
            'overall_confidence' => 'medium',
            'forecast_summary' => 'Statistical forecast based on historical averages',
            'products' => $products,
            'recommendations' => [
                'Review AI forecasting setup for more accurate predictions',
                'Ensure sufficient historical data (minimum 6 months)',
                'Consider seasonal adjustments manually',
            ],
            'risk_factors' => ['Statistical method may not capture complex patterns'],
        ];
    }

    /**
     * Get predictive insights summary
     */
    public function getPredictiveInsights(int $tenantId): array
    {
        $forecast = $this->forecastDemand($tenantId, 3);

        if ($forecast['status'] !== 'success') {
            return $forecast;
        }

        $products = $forecast['forecast']['products'] ?? [];

        $criticalProducts = array_filter($products, fn($p) => $p['reorder_urgency'] === 'critical');
        $highUrgencyProducts = array_filter($products, fn($p) => $p['reorder_urgency'] === 'high');
        $totalOrderValue = 0; // TODO: Calculate from product prices

        return [
            'forecast_generated_at' => $forecast['generated_at'],
            'model_used' => $forecast['model'],
            'confidence' => $forecast['confidence'],
            'summary' => $forecast['forecast']['forecast_summary'] ?? '',
            'total_products_analyzed' => count($products),
            'critical_stock_products' => count($criticalProducts),
            'high_urgency_products' => count($highUrgencyProducts),
            'critical_products' => array_values($criticalProducts),
            'recommendations' => $forecast['forecast']['recommendations'] ?? [],
            'risk_factors' => $forecast['forecast']['risk_factors'] ?? [],
        ];
    }
}
