<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\BomLine;
use App\Models\ConcreteMixDesign;
use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConcreteMixDesignController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $mixDesigns = ConcreteMixDesign::where('tenant_id', $this->tid())
            ->where('is_active', true)
            ->when($request->search, fn ($q, $s) => $q->where('grade', 'like', "%$s%")->orWhere('name', 'like', "%$s%"))
            ->orderByRaw("CAST(REPLACE(REPLACE(grade, 'K-', ''), 'fc', '') AS UNSIGNED)")
            ->paginate(20)
            ->withQueryString();

        $calculation   = null;
        $costAnalysis  = null;
        $availability  = null;
        $recommendation = null;
        $selectedMix   = null;

        // Run calculation if requested
        if ($request->filled('mix_design_id') && $request->filled('volume')) {
            $selectedMix = ConcreteMixDesign::where('tenant_id', $this->tid())
                ->find($request->mix_design_id);

            if ($selectedMix) {
                $volume       = (float) $request->input('volume', 1);
                $wastePercent = (float) $request->input('waste_percent', 5);
                $volumeWithWaste = $volume * (1 + $wastePercent / 100);
                $needs        = $selectedMix->calculateNeeds($volumeWithWaste);
                $cost         = $selectedMix->estimateCostPerM3($this->tid());

                $calculation = ['adjusted' => array_merge($needs, [
                    'grade'         => $selectedMix->grade,
                    'volume_m3'     => $volume,
                    'waste_percent' => $wastePercent,
                ])];

                if (!empty($cost) && isset($cost['total'])) {
                    $costAnalysis = [
                        'cost_per_m3'         => $cost,
                        'total_cost'          => round($cost['total'] * $volume, 0),
                        'cost_per_sack_cement' => isset($cost['cement']) ? round($cost['cement'] / max(1, $needs['cement_kg'] / 50), 0) : 0,
                        'breakdown_percent'   => collect($cost)
                            ->filter(fn ($v, $k) => $k !== 'total' && $cost['total'] > 0)
                            ->map(fn ($v) => round($v / $cost['total'] * 100, 1))
                            ->toArray(),
                    ];
                }

                if (method_exists($selectedMix, 'checkMaterialAvailability')) {
                    $availability = $selectedMix->checkMaterialAvailability($needs, $this->tid());
                }
            }
        }

        // Recommendation
        if ($request->filled('required_strength')) {
            $strength  = (int) $request->required_strength;
            $recVolume = (float) $request->input('rec_volume', 1);
            $maxBudget = $request->filled('max_budget') ? (float) $request->max_budget : null;

            $recommended = ConcreteMixDesign::where('tenant_id', $this->tid())
                ->where('is_active', true)
                ->where('target_strength', '>=', $strength)
                ->orderBy('target_strength')
                ->first();

            if ($recommended) {
                $recCost = $recommended->estimateCostPerM3($this->tid());
                if (!$maxBudget || (isset($recCost['total']) && $recCost['total'] <= $maxBudget)) {
                    $recommendation = [
                        'status'          => 'success',
                        'recommended_mix' => $recommended,
                        'cost_analysis'   => [
                            'cost_per_m3' => $recCost,
                            'total_cost'  => round(($recCost['total'] ?? 0) * $recVolume, 0),
                        ],
                    ];
                } else {
                    $recommendation = ['status' => 'no_match'];
                }
            } else {
                $recommendation = ['status' => 'no_match'];
            }
        }

        return view('manufacturing.mix-design', compact(
            'mixDesigns', 'calculation', 'costAnalysis', 'availability', 'recommendation', 'selectedMix'
        ));
    }

    public function seedStandards()
    {
        $count = ConcreteMixDesign::seedStandards($this->tid());

        if ($count === 0) {
            return back()->with('success', 'Semua mutu beton standar sudah ada.');
        }

        ActivityLog::record('mix_design_seeded', "Seed {$count} mutu beton standar SNI");
        return back()->with('success', "{$count} mutu beton standar berhasil ditambahkan.");
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'grade'             => 'required|string|max:20',
            'name'              => 'required|string|max:255',
            'target_strength'   => 'required|numeric|min:0',
            'strength_unit'     => 'required|in:K,fc',
            'slump_min'         => 'nullable|numeric|min:0',
            'slump_max'         => 'nullable|numeric|min:0',
            'water_cement_ratio'=> 'nullable|numeric|min:0.2|max:1',
            'cement_kg'         => 'required|numeric|min:0',
            'water_liter'       => 'required|numeric|min:0',
            'fine_agg_kg'       => 'required|numeric|min:0',
            'coarse_agg_kg'     => 'required|numeric|min:0',
            'admixture_liter'   => 'nullable|numeric|min:0',
            'cement_type'       => 'nullable|string|max:50',
            'agg_max_size'      => 'nullable|string|max:20',
            'notes'             => 'nullable|string',
        ]);

        ConcreteMixDesign::create(array_merge($data, [
            'tenant_id'   => $this->tid(),
            'is_standard' => false,
            'is_active'   => true,
        ]));

        ActivityLog::record('mix_design_created', "Mix design dibuat: {$data['grade']} — {$data['name']}");
        return back()->with('success', "Mix design {$data['grade']} berhasil dibuat.");
    }

    public function update(Request $request, ConcreteMixDesign $mixDesign)
    {
        abort_if($mixDesign->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'target_strength'   => 'required|numeric|min:0',
            'slump_min'         => 'nullable|numeric|min:0',
            'slump_max'         => 'nullable|numeric|min:0',
            'water_cement_ratio'=> 'nullable|numeric|min:0.2|max:1',
            'cement_kg'         => 'required|numeric|min:0',
            'water_liter'       => 'required|numeric|min:0',
            'fine_agg_kg'       => 'required|numeric|min:0',
            'coarse_agg_kg'     => 'required|numeric|min:0',
            'admixture_liter'   => 'nullable|numeric|min:0',
            'cement_type'       => 'nullable|string|max:50',
            'agg_max_size'      => 'nullable|string|max:20',
            'notes'             => 'nullable|string',
        ]);

        $mixDesign->update($data);
        return back()->with('success', "Mix design {$mixDesign->grade} berhasil diperbarui.");
    }

    public function destroy(ConcreteMixDesign $mixDesign)
    {
        abort_if($mixDesign->tenant_id !== $this->tid(), 403);
        $grade = $mixDesign->grade;
        $mixDesign->delete();
        return back()->with('success', "Mix design {$grade} berhasil dihapus.");
    }

    /**
     * Calculate material needs for a given volume.
     */
    public function calculate(Request $request, ConcreteMixDesign $mixDesign)
    {
        abort_if($mixDesign->tenant_id !== $this->tid(), 403);

        $volume = (float) $request->input('volume', 1);
        $needs = $mixDesign->calculateNeeds($volume);
        $cost = $mixDesign->estimateCostPerM3($this->tid());

        return response()->json([
            'grade'       => $mixDesign->grade,
            'volume_m3'   => $volume,
            'needs'       => $needs,
            'cost_per_m3' => $cost,
            'total_cost'  => round($cost['total'] * $volume, 0),
        ]);
    }

    /**
     * Generate a BOM from this mix design.
     */
    public function generateBom(ConcreteMixDesign $mixDesign)
    {
        abort_if($mixDesign->tenant_id !== $this->tid(), 403);

        if ($mixDesign->bom_id) {
            return back()->with('error', 'Mix design ini sudah memiliki BOM terhubung.');
        }

        $tid = $this->tid();

        // Find or create material products
        $findOrCreate = function (string $name, string $unit, string $category) use ($tid) {
            return Product::firstOrCreate(
                ['tenant_id' => $tid, 'name' => $name],
                [
                    'sku'       => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 6)) . '-' . rand(100, 999),
                    'unit'      => $unit,
                    'category'  => $category,
                    'is_active' => true,
                    'price_buy' => 0,
                    'price_sell'=> 0,
                    'stock_min' => 0,
                ]
            );
        };

        // Create the finished product
        $betonProduct = $findOrCreate("Beton {$mixDesign->grade}", 'm3', 'Beton');

        DB::transaction(function () use ($mixDesign, $betonProduct, $findOrCreate, $tid) {
            $bom = Bom::create([
                'tenant_id'  => $tid,
                'product_id' => $betonProduct->id,
                'name'       => "Mix Design {$mixDesign->grade}",
                'batch_size' => 1,
                'batch_unit' => 'm3',
                'is_active'  => true,
                'notes'      => "Auto-generated dari mix design {$mixDesign->grade}",
            ]);

            $materials = [
                ['name' => 'Semen ' . $mixDesign->cement_type, 'qty' => $mixDesign->cement_kg, 'unit' => 'kg', 'cat' => 'Material'],
                ['name' => 'Air',                               'qty' => $mixDesign->water_liter, 'unit' => 'liter', 'cat' => 'Material'],
                ['name' => 'Pasir / Agregat Halus',            'qty' => $mixDesign->fine_agg_kg, 'unit' => 'kg', 'cat' => 'Material'],
                ['name' => 'Kerikil / Agregat Kasar',          'qty' => $mixDesign->coarse_agg_kg, 'unit' => 'kg', 'cat' => 'Material'],
            ];

            if ($mixDesign->admixture_liter > 0) {
                $materials[] = ['name' => 'Admixture', 'qty' => $mixDesign->admixture_liter, 'unit' => 'liter', 'cat' => 'Material'];
            }

            foreach ($materials as $i => $mat) {
                if ($mat['qty'] <= 0) continue;
                $product = $findOrCreate($mat['name'], $mat['unit'], $mat['cat']);
                BomLine::create([
                    'bom_id'             => $bom->id,
                    'product_id'         => $product->id,
                    'quantity_per_batch' => $mat['qty'],
                    'unit'               => $mat['unit'],
                    'sort_order'         => $i,
                ]);
            }

            $mixDesign->update(['bom_id' => $bom->id]);
        });

        ActivityLog::record('mix_design_bom_generated', "BOM di-generate dari mix design {$mixDesign->grade}");
        return back()->with('success', "BOM untuk {$mixDesign->grade} berhasil di-generate. Produk material otomatis dibuat jika belum ada.");
    }
}
