<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pond;
use App\Models\FishStock;
use App\Models\FisheryHarvest;
use App\Models\WaterQuality;
use Illuminate\Http\Request;

class FisheriesApiController extends ApiBaseController
{
    public function ponds(Request $request)
    {
        $query = Pond::where('tenant_id', $this->getTenantId())
            ->with(['fishStocks']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $ponds = $query->latest()->paginate($request->get('per_page', 20));
        return $this->success($ponds);
    }

    public function createPond(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'size' => 'required|numeric|min:0',
            'type' => 'required|in:earthen,concrete,tarpaulin,cage',
            'location' => 'nullable|string',
            'status' => 'nullable|in:empty,stocked,harvesting,maintenance',
        ]);

        $pond = Pond::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'status' => $validated['status'] ?? 'empty',
        ]));

        return $this->success($pond, 'Pond created successfully', 201);
    }

    public function fishStocks(Request $request)
    {
        $query = FishStock::where('tenant_id', $this->getTenantId())
            ->with(['pond']);

        if ($request->filled('pond_id')) {
            $query->where('pond_id', $request->pond_id);
        }

        $stocks = $query->latest()->paginate($request->get('per_page', 20));
        return $this->success($stocks);
    }

    public function stockFish(Request $request)
    {
        $validated = $request->validate([
            'pond_id' => 'required|exists:ponds,id',
            'fish_type' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'average_weight' => 'nullable|numeric',
            'source' => 'nullable|string',
        ]);

        $stock = FishStock::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'stock_date' => now(),
        ]));

        return $this->success($stock, 'Fish stocked successfully', 201);
    }

    public function harvests(Request $request)
    {
        $query = FisheryHarvest::where('tenant_id', $this->getTenantId())
            ->with(['pond']);

        $harvests = $query->latest()->paginate($request->get('per_page', 20));
        return $this->success($harvests);
    }

    public function recordHarvest(Request $request)
    {
        $validated = $request->validate([
            'pond_id' => 'required|exists:ponds,id',
            'quantity' => 'required|numeric|min:0',
            'average_weight' => 'nullable|numeric',
            'quality' => 'nullable|in:premium,standard,low',
        ]);

        $harvest = FisheryHarvest::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'harvest_date' => now(),
        ]));

        return $this->success($harvest, 'Harvest recorded successfully', 201);
    }

    public function waterQuality(Request $request)
    {
        $query = WaterQuality::where('tenant_id', $this->getTenantId())
            ->with(['pond']);

        if ($request->filled('pond_id')) {
            $query->where('pond_id', $request->pond_id);
        }

        $records = $query->latest()->paginate($request->get('per_page', 20));
        return $this->success($records);
    }

    public function recordWaterQuality(Request $request)
    {
        $validated = $request->validate([
            'pond_id' => 'required|exists:ponds,id',
            'ph' => 'nullable|numeric',
            'temperature' => 'nullable|numeric',
            'dissolved_oxygen' => 'nullable|numeric',
            'ammonia' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $record = WaterQuality::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'measurement_date' => now(),
        ]));

        return $this->success($record, 'Water quality recorded successfully', 201);
    }
}
