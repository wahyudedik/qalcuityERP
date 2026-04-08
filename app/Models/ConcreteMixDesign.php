<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConcreteMixDesign extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'grade', 'name', 'target_strength', 'strength_unit',
        'slump_min', 'slump_max', 'water_cement_ratio',
        'cement_kg', 'water_liter', 'fine_agg_kg', 'coarse_agg_kg', 'admixture_liter',
        'cement_type', 'agg_max_size', 'is_standard', 'is_active', 'bom_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'target_strength'   => 'decimal:2',
            'slump_min'         => 'decimal:1',
            'slump_max'         => 'decimal:1',
            'water_cement_ratio' => 'decimal:2',
            'cement_kg'         => 'decimal:2',
            'water_liter'       => 'decimal:2',
            'fine_agg_kg'       => 'decimal:2',
            'coarse_agg_kg'     => 'decimal:2',
            'admixture_liter'   => 'decimal:3',
            'is_standard'       => 'boolean',
            'is_active'         => 'boolean',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function bom(): BelongsTo { return $this->belongsTo(Bom::class); }

    /**
     * Total berat material per m³ (kg).
     */
    public function totalWeightPerM3(): float
    {
        return $this->cement_kg + $this->water_liter + $this->fine_agg_kg
            + $this->coarse_agg_kg + $this->admixture_liter;
    }

    /**
     * Hitung kebutuhan material untuk volume tertentu.
     */
    public function calculateNeeds(float $volumeM3): array
    {
        return [
            'volume_m3'      => $volumeM3,
            'grade'          => $this->grade,
            'cement_kg'      => round($this->cement_kg * $volumeM3, 1),
            'cement_sak'     => ceil($this->cement_kg * $volumeM3 / 50), // 1 sak = 50 kg
            'water_liter'    => round($this->water_liter * $volumeM3, 1),
            'fine_agg_kg'    => round($this->fine_agg_kg * $volumeM3, 1),
            'fine_agg_m3'    => round($this->fine_agg_kg * $volumeM3 / 1400, 2), // ~1400 kg/m³
            'coarse_agg_kg'  => round($this->coarse_agg_kg * $volumeM3, 1),
            'coarse_agg_m3'  => round($this->coarse_agg_kg * $volumeM3 / 1500, 2), // ~1500 kg/m³
            'admixture_liter'=> round($this->admixture_liter * $volumeM3, 2),
        ];
    }

    /**
     * Estimasi biaya per m³ berdasarkan harga material di inventory.
     */
    public function estimateCostPerM3(int $tenantId): array
    {
        $prices = $this->getMaterialPrices($tenantId);

        $cementCost   = $this->cement_kg * ($prices['cement'] ?? 0);
        $waterCost    = $this->water_liter * ($prices['water'] ?? 0);
        $fineAggCost  = $this->fine_agg_kg * ($prices['fine_agg'] ?? 0);
        $coarseAggCost= $this->coarse_agg_kg * ($prices['coarse_agg'] ?? 0);
        $admixCost    = $this->admixture_liter * ($prices['admixture'] ?? 0);
        $total        = $cementCost + $waterCost + $fineAggCost + $coarseAggCost + $admixCost;

        return [
            'cement'    => round($cementCost, 0),
            'water'     => round($waterCost, 0),
            'fine_agg'  => round($fineAggCost, 0),
            'coarse_agg'=> round($coarseAggCost, 0),
            'admixture' => round($admixCost, 0),
            'total'     => round($total, 0),
        ];
    }

    /**
     * Lookup material prices from inventory by common names.
     */
    private function getMaterialPrices(int $tenantId): array
    {
        $find = fn (array $keywords) => Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('name', 'like', "%{$kw}%");
                }
            })
            ->value('price_buy') ?? 0;

        return [
            'cement'     => $find(['semen', 'cement', 'PCC', 'OPC']) / 50, // per kg (1 sak = 50 kg)
            'water'      => $find(['air']) ?: 0,
            'fine_agg'   => $find(['pasir', 'fine aggregate', 'agregat halus']) / 1400, // per kg
            'coarse_agg' => $find(['kerikil', 'split', 'batu pecah', 'coarse aggregate']) / 1500,
            'admixture'  => $find(['admixture', 'additive', 'sikament', 'plasticizer']),
        ];
    }

    /**
     * Standard Indonesian concrete mix designs (SNI).
     * Approximate compositions per 1 m³.
     */
    public const STANDARD_GRADES = [
        'K-175' => ['strength' => 175, 'cement' => 326, 'water' => 215, 'fine' => 760, 'coarse' => 1029, 'wcr' => 0.66],
        'K-200' => ['strength' => 200, 'cement' => 352, 'water' => 215, 'fine' => 731, 'coarse' => 1031, 'wcr' => 0.61],
        'K-225' => ['strength' => 225, 'cement' => 371, 'water' => 215, 'fine' => 698, 'coarse' => 1047, 'wcr' => 0.58],
        'K-250' => ['strength' => 250, 'cement' => 384, 'water' => 215, 'fine' => 692, 'coarse' => 1039, 'wcr' => 0.56],
        'K-275' => ['strength' => 275, 'cement' => 406, 'water' => 215, 'fine' => 684, 'coarse' => 1026, 'wcr' => 0.53],
        'K-300' => ['strength' => 300, 'cement' => 413, 'water' => 215, 'fine' => 681, 'coarse' => 1021, 'wcr' => 0.52],
        'K-350' => ['strength' => 350, 'cement' => 448, 'water' => 215, 'fine' => 667, 'coarse' => 1000, 'wcr' => 0.48],
        'K-400' => ['strength' => 400, 'cement' => 478, 'water' => 215, 'fine' => 660, 'coarse' => 977,  'wcr' => 0.45],
        'K-450' => ['strength' => 450, 'cement' => 504, 'water' => 215, 'fine' => 645, 'coarse' => 966,  'wcr' => 0.43],
        'K-500' => ['strength' => 500, 'cement' => 533, 'water' => 215, 'fine' => 630, 'coarse' => 952,  'wcr' => 0.40],
    ];

    /**
     * Seed standard grades for a tenant.
     */
    public static function seedStandards(int $tenantId): int
    {
        $count = 0;
        foreach (self::STANDARD_GRADES as $grade => $spec) {
            if (self::where('tenant_id', $tenantId)->where('grade', $grade)->exists()) continue;

            self::create([
                'tenant_id'         => $tenantId,
                'grade'             => $grade,
                'name'              => "Beton Mutu {$grade}",
                'target_strength'   => $spec['strength'],
                'strength_unit'     => 'K',
                'slump_min'         => 8,
                'slump_max'         => 12,
                'water_cement_ratio'=> $spec['wcr'],
                'cement_kg'         => $spec['cement'],
                'water_liter'       => $spec['water'],
                'fine_agg_kg'       => $spec['fine'],
                'coarse_agg_kg'     => $spec['coarse'],
                'admixture_liter'   => 0,
                'cement_type'       => 'PCC',
                'agg_max_size'      => '20mm',
                'is_standard'       => true,
                'is_active'         => true,
            ]);
            $count++;
        }
        return $count;
    }
}
