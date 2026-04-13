<?php

namespace App\Http\Controllers\Livestock;

use App\Http\Controllers\Controller;
use App\Models\LivestockHealthRecord;
use App\Models\LivestockVaccination;
use App\Models\LivestockHerd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HealthController extends Controller
{
    /**
     * Get authenticated user's tenant ID
     */
    // tenantId() inherited from parent Controller

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
                ->where('status', 'ongoing')
                ->count(),
            'completed_treatments' => LivestockHealthRecord::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->count(),
        ];

        $treatments = LivestockHealthRecord::with(['herd', 'veterinarian'])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('treatment_date')
            ->paginate(20);

        $herds = LivestockHerd::where('tenant_id', $tenantId)
            ->active()
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
            'treatment_date' => 'required|date',
            'diagnosis' => 'required|string',
            'treatment' => 'required|string',
            'medication' => 'nullable|string',
            'dosage' => 'nullable|string',
            'veterinarian' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:ongoing,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        try {
            $record = new LivestockHealthRecord();
            $record->tenant_id = $this->tenantId();
            $record->fill($validated);
            $record->status = $validated['status'] ?? 'ongoing';
            $record->recorded_by = $this->userId();
            $record->save();

            return back()->with('success', 'Treatment record added successfully!');
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
                ->where('vaccination_date', '>=', now())
                ->count(),
            'completed_vaccinations' => LivestockVaccination::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->count(),
        ];

        $vaccinations = LivestockVaccination::with(['herd', 'administeredBy'])
            ->where('tenant_id', $tenantId)
            ->orderByDesc('vaccination_date')
            ->paginate(20);

        $herds = LivestockHerd::where('tenant_id', $tenantId)
            ->active()
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
            'vaccination_date' => 'required|date',
            'vaccine_name' => 'required|string',
            'batch_number' => 'nullable|string',
            'dosage' => 'nullable|string',
            'administered_by' => 'nullable|string',
            'next_due_date' => 'nullable|date',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:scheduled,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        try {
            $vaccination = new LivestockVaccination();
            $vaccination->tenant_id = $this->tenantId();
            $vaccination->fill($validated);
            $vaccination->status = $validated['status'] ?? 'scheduled';
            $vaccination->recorded_by = $this->userId();
            $vaccination->save();

            return back()->with('success', 'Vaccination record added successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
