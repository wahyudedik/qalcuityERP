<?php

namespace App\Http\Controllers\Livestock;

use App\Http\Controllers\Controller;
use App\Models\DairyMilkRecord;
use App\Models\LivestockHerd;
use App\Services\LivestockIntegrationService;
use Illuminate\Http\Request;

class DairyController extends Controller
{
    protected LivestockIntegrationService $integrationService;

    public function __construct(LivestockIntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * Display milk production records
     */
    public function milkRecords(Request $request)
    {
        $stats = [
            'total_records' => DairyMilkRecord::where('tenant_id', auth()->user()->tenant_id)->count(),
            'today_production' => DairyMilkRecord::where('tenant_id', auth()->user()->tenant_id)
                ->whereDate('record_date', today())
                ->sum('milk_volume_liters'),
            'avg_daily_production' => DairyMilkRecord::where('tenant_id', auth()->user()->tenant_id)
                ->whereBetween('record_date', [now()->subDays(7), now()])
                ->avg('milk_volume_liters') ?? 0,
            'high_quality_percentage' => 0,
        ];

        // Calculate high quality percentage
        $totalRecords = DairyMilkRecord::where('tenant_id', auth()->user()->tenant_id)
            ->whereNotNull('somatic_cell_count')
            ->count();

        if ($totalRecords > 0) {
            $highQuality = DairyMilkRecord::where('tenant_id', auth()->user()->tenant_id)
                ->where('somatic_cell_count', '<', 400000)
                ->count();
            $stats['high_quality_percentage'] = round(($highQuality / $totalRecords) * 100, 2);
        }

        $records = DairyMilkRecord::with(['herd', 'recordedBy'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderByDesc('record_date')
            ->orderBy('milking_session')
            ->paginate(20);

        $herds = LivestockHerd::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('animal_type', ['sapi', 'kambing'])
            ->active()
            ->get();

        return view('livestock.dairy.milk-records', compact('stats', 'records', 'herds'));
    }

    /**
     * Store new milk record
     */
    public function storeMilkRecord(Request $request)
    {
        $validated = $request->validate([
            'livestock_herd_id' => 'required|exists:livestock_herds,id',
            'animal_id' => 'nullable|string',
            'record_date' => 'required|date',
            'milking_session' => 'required|in:morning,afternoon,evening',
            'milk_volume_liters' => 'required|numeric|min:0',
            'fat_percentage' => 'nullable|numeric|min:0|max:10',
            'protein_percentage' => 'nullable|numeric|min:0|max:10',
            'lactose_percentage' => 'nullable|numeric|min:0|max:10',
            'somatic_cell_count' => 'nullable|integer|min:0',
            'quality_grade' => 'nullable|in:A,B,C',
            'notes' => 'nullable|string',
        ]);

        try {
            $record = new DairyMilkRecord();
            $record->tenant_id = auth()->user()->tenant_id;
            $record->fill($validated);
            $record->recorded_by = auth()->id();
            $record->save();

            // Post journal entry for dairy production (integration with Accounting)
            // Price per liter can be configured in tenant settings or passed via request
            $pricePerLiter = $request->input('price_per_liter', 0);
            if ($pricePerLiter > 0) {
                $result = $this->integrationService->postDairyProduction(
                    auth()->user()->tenant_id,
                    auth()->id(),
                    $record,
                    (float) $pricePerLiter
                );
                if ($result->isFailed()) {
                    \Illuminate\Support\Facades\Log::warning("Dairy production journal failed: " . $result->reason);
                }
            }

            return back()->with('success', 'Milk record saved successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display milking sessions
     */
    public function milkingSessions(Request $request)
    {
        $sessions = \App\Models\DairyMilkingSession::where('tenant_id', auth()->user()->tenant_id)
            ->orderByDesc('session_date')
            ->paginate(20);

        return view('livestock.dairy.sessions', compact('sessions'));
    }

    /**
     * Store milking session
     */
    public function storeSession(Request $request)
    {
        $validated = $request->validate([
            'session_date' => 'required|date',
            'session_type' => 'required|in:morning,afternoon,evening',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'total_animals_milked' => 'required|integer|min:0',
            'total_milk_volume' => 'required|numeric|min:0',
            'operator_name' => 'nullable|string',
            'equipment_notes' => 'nullable|string',
            'issues' => 'nullable|string',
        ]);

        try {
            $session = new \App\Models\DairyMilkingSession();
            $session->tenant_id = auth()->user()->tenant_id;
            $session->session_code = 'MS-' . now()->format('Ymd') . '-' . str_pad(\App\Models\DairyMilkingSession::count() + 1, 4, '0', STR_PAD_LEFT);
            $session->fill($validated);
            $session->created_by = auth()->id();

            // Calculate average
            if ($session->total_animals_milked > 0) {
                $session->average_milk_per_animal = round(
                    $session->total_milk_volume / $session->total_animals_milked,
                    2
                );
            }

            $session->save();

            return back()->with('success', 'Milking session recorded!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
