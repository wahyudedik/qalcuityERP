<?php

namespace App\Http\Controllers\Livestock;

use App\Http\Controllers\Controller;
use App\Models\LivestockHealthRecord;
use App\Models\LivestockHerd;
use App\Models\LivestockVaccination;
use App\Services\LivestockIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    protected LivestockIntegrationService $integrationService;

    public function __construct(LivestockIntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * Get authenticated user's ID
     */
    private function userId(): int
    {
        return Auth::id() ?? abort(401, 'Unauthenticated.');
    }

    /**
     * Display treatment records
     */
    public function treatments(Request $request)
    {
        $tenantId = $this->tenantId();

        $stats = [
            'total_treatments' => LivestockHealthRecord::where('tenant_id', $tenantId)->count(),
            'active_treatments' => LivestockHealthRecord::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->count(),
            'completed_treatments' => LivestockHealthRecord::where('tenant_id', $tenantId)
                ->where('status', 'resolved')
                ->count(),
        ];

        $treatments = LivestockHealthRecord::with(['herd', 'veterinarian'])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('date')
            ->paginate(20);

        $herds = LivestockHerd::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();

        return view('livestock.health.treatments', compact('stats', 'treatments', 'herds'));
    }

    /**
     * Store treatment record
     */
    public function storeTreatment(Request $request)
    {
        $validated = $request->validate([
            'livestock_herd_id' => 'required|exists:livestock_herds,id',
            'date' => 'required|date',
            'type' => 'required|in:illness,treatment,observation,quarantine,recovery',
            'condition' => 'required|string|max:255',
            'affected_count' => 'nullable|integer|min:0',
            'death_count' => 'nullable|integer|min:0',
            'symptoms' => 'nullable|string|max:500',
            'medication' => 'nullable|string|max:255',
            'medication_cost' => 'nullable|numeric|min:0',
            'administered_by' => 'nullable|string|max:100',
            'severity' => 'nullable|in:low,medium,high,critical',
            'notes' => 'nullable|string',
        ]);

        try {
            $record = new LivestockHealthRecord;
            $record->tenant_id = $this->tenantId();
            $record->fill($validated);
            $record->status = $validated['type'] === 'recovery' ? 'resolved' : 'active';
            $record->user_id = $this->userId();
            $record->save();

            // Post journal entry for medication cost (integration with Accounting)
            if (($validated['medication_cost'] ?? 0) > 0) {
                $herd = LivestockHerd::find($validated['livestock_herd_id']);
                $result = $this->integrationService->postVeterinaryExpense(
                    $this->tenantId(),
                    $this->userId(),
                    $record->id,
                    $herd->code ?? 'UNKNOWN',
                    (float) $validated['medication_cost'],
                    $validated['medication'] ?? $validated['condition'],
                    $validated['date']
                );
                if ($result->isFailed()) {
                    Log::warning('Livestock health journal failed: '.$result->reason);
                }
            }

            return back()->with('success', 'Catatan kesehatan berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display vaccination records
     */
    public function vaccinations(Request $request)
    {
        $tenantId = $this->tenantId();

        $stats = [
            'total_vaccinations' => LivestockVaccination::where('tenant_id', $tenantId)->count(),
            'upcoming_vaccinations' => LivestockVaccination::where('tenant_id', $tenantId)
                ->where('scheduled_date', '>=', now())
                ->where('status', 'scheduled')
                ->count(),
            'completed_vaccinations' => LivestockVaccination::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->count(),
        ];

        $vaccinations = LivestockVaccination::with(['herd', 'administeredBy'])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('scheduled_date')
            ->paginate(20);

        $herds = LivestockHerd::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();

        return view('livestock.health.vaccinations', compact('stats', 'vaccinations', 'herds'));
    }

    /**
     * Store vaccination record
     */
    public function storeVaccination(Request $request)
    {
        $validated = $request->validate([
            'livestock_herd_id' => 'required|exists:livestock_herds,id',
            'vaccine_name' => 'required|string|max:255',
            'scheduled_date' => 'required|date',
            'administered_date' => 'nullable|date',
            'dose_age_days' => 'nullable|integer|min:0',
            'dose_method' => 'nullable|string|max:100',
            'vaccinated_count' => 'nullable|integer|min:0',
            'cost' => 'nullable|numeric|min:0',
            'administered_by' => 'nullable|string|max:100',
            'batch_number' => 'nullable|string|max:50',
            'status' => 'nullable|in:scheduled,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        try {
            $vaccination = new LivestockVaccination;
            $vaccination->tenant_id = $this->tenantId();
            $vaccination->fill($validated);
            $vaccination->status = $validated['status'] ?? 'scheduled';
            $vaccination->user_id = $this->userId();
            $vaccination->save();

            // Post journal entry for vaccination cost (integration with Accounting)
            if (($validated['cost'] ?? 0) > 0 && ($validated['status'] ?? 'scheduled') === 'completed') {
                $herd = LivestockHerd::find($validated['livestock_herd_id']);
                $result = $this->integrationService->postVaccinationCost(
                    $this->tenantId(),
                    $this->userId(),
                    $vaccination->id,
                    $herd->code ?? 'UNKNOWN',
                    $validated['vaccine_name'],
                    (float) $validated['cost'],
                    $validated['administered_date'] ?? $validated['scheduled_date']
                );
                if ($result->isFailed()) {
                    Log::warning('Livestock vaccination journal failed: '.$result->reason);
                }
            }

            return back()->with('success', 'Catatan vaksinasi berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
