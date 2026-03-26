<?php

namespace App\Http\Controllers;

use App\Services\ForecastService;
use Illuminate\Http\Request;

class ForecastController extends Controller
{
    public function index(Request $request, ForecastService $service)
    {
        $tenantId = auth()->user()->tenant_id;
        $months = (int) ($request->months ?? 6);

        $revenue   = $service->revenueForcast($tenantId, 6, $months);
        $cashFlow  = $service->cashFlowForecast($tenantId, 6, $months);
        $demand    = $service->demandForecast($tenantId, 3, 10);
        $receivables = $service->receivablesForecast($tenantId);

        return view('forecast.index', compact('revenue', 'cashFlow', 'demand', 'receivables', 'months'));
    }
}
