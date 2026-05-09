<?php

namespace App\Http\Controllers\Manufacturing;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Manufacturing\PredictiveMRPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PredictiveMRPController extends Controller
{
    protected PredictiveMRPService $predictiveService;

    public function __construct(PredictiveMRPService $predictiveService)
    {
        $this->predictiveService = $predictiveService;
    }

    private function tid(): int
    {
        return Auth::user()->tenant_id ?? abort(401, 'Unauthenticated.');
    }

    /**
     * Main Predictive MRP Dashboard
     */
    public function dashboard(Request $request)
    {
        $tenantId = $this->tid();
        $months = $request->input('months', 3);
        $productId = $request->input('product_id');

        // Generate forecast
        $forecast = $this->predictiveService->forecastDemand($tenantId, $months, $productId);

        // Get predictive insights
        $insights = $this->predictiveService->getPredictiveInsights($tenantId);

        // Get all products for filter
        $products = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('manufacturing.predictive-mrp', compact(
            'forecast',
            'insights',
            'months',
            'productId',
            'products'
        ));
    }

    /**
     * Refresh forecast (clear cache)
     */
    public function refreshForecast(Request $request)
    {
        $tenantId = $this->tid();
        $months = $request->input('months', 3);
        $productId = $request->input('product_id');

        // Clear cache
        $cacheKey = "predictive_mrp_forecast_{$tenantId}_{$months}_{$productId}";
        Cache::forget($cacheKey);

        // Regenerate
        $forecast = $this->predictiveService->forecastDemand($tenantId, $months, $productId);

        return back()->with('success', 'Forecast refreshed successfully using '.($forecast['model'] === 'gemini-2.5-flash' ? 'AI' : 'Statistical').' model.');
    }
}
