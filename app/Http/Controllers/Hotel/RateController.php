<?php

namespace App\Http\Controllers\Hotel;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\RoomRate;
use App\Models\RoomType;
use App\Services\RateManagementService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RateController extends Controller
{
    private RateManagementService $rateService;

    public function __construct(RateManagementService $rateService)
    {
        $this->rateService = $rateService;
    }

    // tenantId() inherited from parent Controller

    public function index(Request $request)
    {
        $tid = $this->tenantId();

        // Get room types with their rates
        $roomTypes = RoomType::with('rates')
            ->where('tenant_id', $tid)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Rate types
        $rateTypes = ['standard', 'weekend', 'seasonal', 'promo', 'dynamic'];

        return view('hotel.rates.index', compact('roomTypes', 'rateTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'rate_type' => 'required|in:standard,weekend,seasonal,promo,dynamic',
            'amount' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'day_of_week' => 'nullable|array',
            'day_of_week.*' => 'integer|min:0|max:6',
            'description' => 'nullable|string|max:255',
        ]);

        $tid = $this->tenantId();

        // Verify room type belongs to tenant
        $roomType = RoomType::where('id', $data['room_type_id'])->where('tenant_id', $tid)->first();
        if (! $roomType) {
            return back()->withErrors(['room_type_id' => 'Invalid room type.'])->withInput();
        }

        $rate = RoomRate::create([
            'tenant_id' => $tid,
            'room_type_id' => $data['room_type_id'],
            'rate_type' => $data['rate_type'],
            'amount' => $data['amount'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'day_of_week' => $data['day_of_week'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);

        ActivityLog::record('room_rate_created', "Room rate created: {$roomType->name} - {$data['rate_type']}", $rate);

        return back()->with('success', 'Rate created successfully.');
    }

    public function update(Request $request, RoomRate $rate)
    {
        abort_unless($rate->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'rate_type' => 'required|in:standard,weekend,seasonal,promo,dynamic',
            'amount' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'day_of_week' => 'nullable|array',
            'day_of_week.*' => 'integer|min:0|max:6',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $old = $rate->getOriginal();
        $rate->update($data);

        ActivityLog::record('room_rate_updated', "Room rate updated: {$rate->roomType->name}", $rate, $old, $rate->fresh()->toArray());

        return back()->with('success', 'Rate updated successfully.');
    }

    public function destroy(RoomRate $rate)
    {
        abort_unless($rate->tenant_id === $this->tenantId(), 403);

        ActivityLog::record('room_rate_deleted', "Room rate deleted: {$rate->roomType->name} - {$rate->rate_type}", $rate);
        $rate->delete();

        return back()->with('success', 'Rate deleted successfully.');
    }

    public function calendar(Request $request)
    {
        $tid = $this->tenantId();

        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $startOfMonth = Carbon::create($year, $month, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Get room types
        $roomTypes = RoomType::where('tenant_id', $tid)->where('is_active', true)->orderBy('name')->get();

        // Build rate calendar
        $calendar = [];
        $current = $startOfMonth->copy();

        while ($current->lte($endOfMonth)) {
            $dateStr = $current->toDateString();
            $dayRates = [];

            foreach ($roomTypes as $roomType) {
                $effectiveRate = $this->rateService->getEffectiveRate($roomType->id, $dateStr, $tid);
                $dayRates[$roomType->id] = [
                    'name' => $roomType->name,
                    'base_rate' => $roomType->base_rate,
                    'effective_rate' => $effectiveRate,
                ];
            }

            $calendar[$dateStr] = [
                'date' => $dateStr,
                'day_of_week' => $current->dayOfWeek,
                'is_weekend' => $current->isWeekend(),
                'rates' => $dayRates,
            ];

            $current->addDay();
        }

        return view('hotel.rates.calendar', compact('calendar', 'roomTypes', 'month', 'year'));
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'rates' => 'required|array',
            'rates.*.room_type_id' => 'required|exists:room_types,id',
            'rates.*.rate_type' => 'required|in:standard,weekend,seasonal,promo,dynamic',
            'rates.*.amount' => 'required|numeric|min:0',
            'rates.*.start_date' => 'nullable|date',
            'rates.*.end_date' => 'nullable|date|after_or_equal:start_date',
            'rates.*.day_of_week' => 'nullable|array',
            'rates.*.day_of_week.*' => 'integer|min:0|max:6',
        ]);

        $tid = $this->tenantId();

        // Add tenant_id to each rate
        $rates = collect($data['rates'])->map(function ($rate) use ($tid) {
            $rate['tenant_id'] = $tid;

            return $rate;
        })->toArray();

        try {
            $result = $this->rateService->bulkUpdateRates($rates);

            ActivityLog::record('room_rates_bulk_updated', "Bulk rate update: {$result['created']} created, {$result['updated']} updated");

            return back()->with('success', "Rates updated: {$result['created']} created, {$result['updated']} updated.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
