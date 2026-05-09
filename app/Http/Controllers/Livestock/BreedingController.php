<?php

namespace App\Http\Controllers\Livestock;

use App\Http\Controllers\Controller;
use App\Models\AnimalPedigree;
use App\Models\BreedingRecord;
use App\Models\LivestockHerd;
use Illuminate\Http\Request;

class BreedingController extends Controller
{
    /**
     * Display breeding records
     */
    public function index(Request $request)
    {
        $stats = [
            'total_records' => BreedingRecord::where('tenant_id', auth()->user()->tenant_id)->count(),
            'pregnant_count' => BreedingRecord::where('tenant_id', auth()->user()->tenant_id)
                ->where('status', 'pregnant')->count(),
            'upcoming_births' => BreedingRecord::where('tenant_id', auth()->user()->tenant_id)
                ->where('status', 'pregnant')
                ->whereBetween('expected_due_date', [now(), now()->addDays(30)])
                ->count(),
            'success_rate' => 0,
        ];

        // Calculate breeding success rate
        $completed = BreedingRecord::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('status', ['born', 'failed'])->count();

        if ($completed > 0) {
            $born = BreedingRecord::where('tenant_id', auth()->user()->tenant_id)
                ->where('status', 'born')->count();
            $stats['success_rate'] = round(($born / $completed) * 100, 2);
        }

        $records = BreedingRecord::with(['herd', 'recordedBy'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderByDesc('mating_date')
            ->paginate(20);

        $herds = LivestockHerd::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('animal_type', ['sapi', 'kambing', 'ayam_layer'])
            ->active()
            ->get();

        return view('livestock.breeding.records', compact('stats', 'records', 'herds'));
    }

    /**
     * Store breeding record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'livestock_herd_id' => 'nullable|exists:livestock_herds,id',
            'dam_id' => 'required|string',
            'sire_id' => 'required|string',
            'mating_date' => 'required|date',
            'mating_type' => 'required|in:natural,artificial_insemination,embryo_transfer',
            'expected_due_date' => 'nullable|date|after:mating_date',
            'genetics_line' => 'nullable|string',
            'genetic_traits' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        try {
            $record = new BreedingRecord;
            $record->tenant_id = auth()->user()->tenant_id;
            $record->fill($validated);
            $record->status = 'pending';
            $record->recorded_by = auth()->id();
            $record->save();

            return back()->with('success', 'Breeding record created successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display pedigrees
     */
    public function pedigrees(Request $request)
    {
        $pedigrees = AnimalPedigree::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('breed')
            ->orderBy('birth_date', 'desc')
            ->paginate(20);

        return view('livestock.breeding.pedigrees', compact('pedigrees'));
    }

    /**
     * Store pedigree record
     */
    public function storePedigree(Request $request)
    {
        $validated = $request->validate([
            'animal_id' => 'required|string|unique:animal_pedigrees,animal_id',
            'animal_name' => 'nullable|string',
            'breed' => 'required|string',
            'birth_date' => 'required|date',
            'gender' => 'required|in:male,female',
            'dam_id' => 'nullable|string',
            'sire_id' => 'nullable|string',
            'genetic_line' => 'nullable|string',
            'genetic_markers' => 'nullable|array',
            'birth_weight_kg' => 'nullable|numeric|min:0',
            'performance_data' => 'nullable|string',
            'registration_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $pedigree = new AnimalPedigree;
            $pedigree->tenant_id = auth()->user()->tenant_id;
            $pedigree->fill($validated);
            $pedigree->save();

            return back()->with('success', 'Pedigree registered successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Update breeding record status
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,pregnant,born,failed',
            'actual_birth_date' => 'nullable|date',
            'offspring_count' => 'nullable|integer|min:0',
            'live_births' => 'nullable|integer|min:0',
            'stillbirths' => 'nullable|integer|min:0',
            'birth_weight_avg_kg' => 'nullable|numeric|min:0',
        ]);

        try {
            $record = BreedingRecord::findOrFail($id);
            $record->status = $validated['status'];

            if (isset($validated['actual_birth_date'])) {
                $record->actual_birth_date = $validated['actual_birth_date'];
            }

            if (isset($validated['offspring_count'])) {
                $record->offspring_count = $validated['offspring_count'];
            }

            if (isset($validated['live_births'])) {
                $record->live_births = $validated['live_births'];
            }

            if (isset($validated['stillbirths'])) {
                $record->stillbirths = $validated['stillbirths'];
            }

            if (isset($validated['birth_weight_avg_kg'])) {
                $record->birth_weight_avg_kg = $validated['birth_weight_avg_kg'];
            }

            $record->save();

            return back()->with('success', 'Breeding status updated!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
