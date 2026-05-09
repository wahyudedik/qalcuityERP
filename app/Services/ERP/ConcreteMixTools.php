<?php

namespace App\Services\ERP;

use App\Models\ConcreteMixDesign;

class ConcreteMixTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name' => 'get_mix_design',
                'description' => 'Lihat daftar mix design / mutu beton yang tersedia, atau detail komposisi mutu tertentu. '
                    .'Gunakan untuk: "mutu beton apa saja?", "komposisi K-300", "mix design K-225", '
                    .'"daftar mutu beton", "spesifikasi beton K-400".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'grade' => [
                            'type' => 'string',
                            'description' => 'Mutu beton spesifik (K-225, K-300, dll). Kosong = tampilkan semua.',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'calculate_concrete_needs',
                'description' => 'Hitung kebutuhan material beton untuk volume tertentu. '
                    .'Gunakan untuk: "hitung kebutuhan K-300 untuk 50 m3", '
                    .'"berapa semen untuk 10 m3 beton K-225?", '
                    .'"kebutuhan material beton K-400 volume 120 m3".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'grade' => ['type' => 'string', 'description' => 'Mutu beton (K-225, K-300, dll)'],
                        'volume_m3' => ['type' => 'number', 'description' => 'Volume beton dalam m³'],
                    ],
                    'required' => ['grade', 'volume_m3'],
                ],
            ],
            [
                'name' => 'setup_concrete_standards',
                'description' => 'Load mutu beton standar SNI (K-175 s/d K-500) ke sistem. '
                    .'Gunakan untuk: "load mutu beton standar", "setup mix design SNI", '
                    .'"tambahkan semua mutu beton standar".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [],
                ],
            ],
            [
                'name' => 'create_mix_design',
                'description' => 'Buat mix design / mutu beton custom. '
                    .'Gunakan untuk: "buat mix design K-350 custom", '
                    .'"tambah mutu beton fc30 semen 450 kg pasir 650 kg kerikil 1000 kg air 200 liter".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'grade' => ['type' => 'string', 'description' => 'Kode mutu (K-350, fc30, dll)'],
                        'name' => ['type' => 'string', 'description' => 'Nama lengkap (opsional, default: "Beton Mutu {grade}")'],
                        'target_strength' => ['type' => 'number', 'description' => 'Kuat tekan target'],
                        'strength_unit' => ['type' => 'string', 'description' => 'K (kg/cm²) atau fc (MPa). Default: K'],
                        'cement_kg' => ['type' => 'number', 'description' => 'Semen per m³ (kg)'],
                        'water_liter' => ['type' => 'number', 'description' => 'Air per m³ (liter)'],
                        'fine_agg_kg' => ['type' => 'number', 'description' => 'Pasir/agregat halus per m³ (kg)'],
                        'coarse_agg_kg' => ['type' => 'number', 'description' => 'Kerikil/agregat kasar per m³ (kg)'],
                        'admixture_liter' => ['type' => 'number', 'description' => 'Admixture per m³ (liter, opsional)'],
                        'water_cement_ratio' => ['type' => 'number', 'description' => 'Rasio air-semen (opsional)'],
                    ],
                    'required' => ['grade', 'cement_kg', 'water_liter', 'fine_agg_kg', 'coarse_agg_kg'],
                ],
            ],
        ];
    }

    // ─── Executors ────────────────────────────────────────────────

    public function getMixDesign(array $args): array
    {
        $grade = trim($args['grade'] ?? '');

        if ($grade) {
            $design = ConcreteMixDesign::where('tenant_id', $this->tenantId)
                ->where('is_active', true)
                ->where('grade', 'like', "%{$grade}%")
                ->first();

            if (! $design) {
                return ['status' => 'not_found', 'message' => "Mutu beton \"{$grade}\" tidak ditemukan. Gunakan `setup_concrete_standards` untuk load mutu standar SNI."];
            }

            $cost = $design->estimateCostPerM3($this->tenantId);

            return [
                'status' => 'success',
                'message' => "Mix Design **{$design->grade}** — {$design->name}",
                'data' => [
                    'grade' => $design->grade,
                    'nama' => $design->name,
                    'kuat_tekan' => $design->target_strength.' '.($design->strength_unit === 'K' ? 'kg/cm²' : 'MPa'),
                    'slump' => "{$design->slump_min}–{$design->slump_max} cm",
                    'w_c_ratio' => $design->water_cement_ratio,
                    'komposisi_per_m3' => [
                        'semen' => "{$design->cement_kg} kg",
                        'air' => "{$design->water_liter} liter",
                        'pasir' => "{$design->fine_agg_kg} kg",
                        'kerikil' => "{$design->coarse_agg_kg} kg",
                        'admixture' => $design->admixture_liter > 0 ? "{$design->admixture_liter} liter" : '-',
                    ],
                    'tipe_semen' => $design->cement_type,
                    'estimasi_biaya_per_m3' => $cost['total'] > 0 ? 'Rp '.number_format($cost['total'], 0, ',', '.') : 'Belum ada harga material di inventori',
                    'has_bom' => $design->bom_id ? true : false,
                ],
            ];
        }

        // List all
        $designs = ConcreteMixDesign::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->orderByRaw("CAST(REPLACE(REPLACE(grade, 'K-', ''), 'fc', '') AS UNSIGNED)")
            ->get();

        if ($designs->isEmpty()) {
            return [
                'status' => 'empty',
                'message' => 'Belum ada mix design. Gunakan `setup_concrete_standards` untuk load mutu beton standar SNI (K-175 s/d K-500).',
            ];
        }

        $list = $designs->map(fn ($d) => [
            'grade' => $d->grade,
            'kuat_tekan' => $d->target_strength.' '.($d->strength_unit === 'K' ? 'kg/cm²' : 'MPa'),
            'semen' => "{$d->cement_kg} kg",
            'pasir' => "{$d->fine_agg_kg} kg",
            'kerikil' => "{$d->coarse_agg_kg} kg",
            'w_c' => $d->water_cement_ratio,
            'standar' => $d->is_standard ? 'SNI' : 'Custom',
        ]);

        return [
            'status' => 'success',
            'message' => "Daftar mix design beton ({$designs->count()} mutu)",
            'data' => ['designs' => $list->toArray()],
        ];
    }

    public function calculateConcreteNeeds(array $args): array
    {
        $grade = trim($args['grade']);
        $volume = (float) $args['volume_m3'];

        if ($volume <= 0) {
            return ['status' => 'error', 'message' => 'Volume harus lebih dari 0 m³.'];
        }

        $design = ConcreteMixDesign::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->where('grade', 'like', "%{$grade}%")
            ->first();

        if (! $design) {
            return ['status' => 'not_found', 'message' => "Mutu beton \"{$grade}\" tidak ditemukan."];
        }

        $needs = $design->calculateNeeds($volume);
        $cost = $design->estimateCostPerM3($this->tenantId);
        $totalCost = round($cost['total'] * $volume, 0);

        $fmt = fn ($v) => number_format($v, $v == (int) $v ? 0 : 1, ',', '.');

        return [
            'status' => 'success',
            'message' => "Kebutuhan material **{$design->grade}** untuk **{$fmt($volume)} m³**:"
                ."\n\n| Material | Kebutuhan |"
                ."\n|----------|-----------|"
                ."\n| Semen | {$fmt($needs['cement_kg'])} kg ({$needs['cement_sak']} sak) |"
                ."\n| Air | {$fmt($needs['water_liter'])} liter |"
                ."\n| Pasir | {$fmt($needs['fine_agg_kg'])} kg ({$fmt($needs['fine_agg_m3'])} m³) |"
                ."\n| Kerikil | {$fmt($needs['coarse_agg_kg'])} kg ({$fmt($needs['coarse_agg_m3'])} m³) |"
                .($needs['admixture_liter'] > 0 ? "\n| Admixture | {$fmt($needs['admixture_liter'])} liter |" : '')
                .($totalCost > 0 ? "\n\n💰 Estimasi biaya: **Rp ".number_format($totalCost, 0, ',', '.').'** (Rp '.number_format($cost['total'], 0, ',', '.').'/m³)' : ''),
            'data' => [
                'grade' => $design->grade,
                'volume_m3' => $volume,
                'needs' => $needs,
                'cost_per_m3' => $cost['total'],
                'total_cost' => $totalCost,
            ],
        ];
    }

    public function setupConcreteStandards(array $args): array
    {
        $count = ConcreteMixDesign::seedStandards($this->tenantId);

        if ($count === 0) {
            return [
                'status' => 'success',
                'message' => 'Semua mutu beton standar SNI sudah ada di sistem.',
            ];
        }

        return [
            'status' => 'success',
            'message' => "**{$count} mutu beton standar SNI** berhasil ditambahkan (K-175 s/d K-500)."
                ."\n\nGunakan `get_mix_design` untuk melihat daftar lengkap, atau `calculate_concrete_needs` untuk menghitung kebutuhan material.",
        ];
    }

    public function createMixDesign(array $args): array
    {
        $grade = trim($args['grade']);

        if (ConcreteMixDesign::where('tenant_id', $this->tenantId)->where('grade', $grade)->exists()) {
            return ['status' => 'error', 'message' => "Mutu beton \"{$grade}\" sudah ada."];
        }

        $wcr = $args['water_cement_ratio'] ?? (($args['water_liter'] ?? 215) / max(1, $args['cement_kg']));

        $design = ConcreteMixDesign::create([
            'tenant_id' => $this->tenantId,
            'grade' => $grade,
            'name' => $args['name'] ?? "Beton Mutu {$grade}",
            'target_strength' => $args['target_strength'] ?? 0,
            'strength_unit' => $args['strength_unit'] ?? 'K',
            'slump_min' => 8,
            'slump_max' => 12,
            'water_cement_ratio' => round($wcr, 2),
            'cement_kg' => (float) $args['cement_kg'],
            'water_liter' => (float) $args['water_liter'],
            'fine_agg_kg' => (float) $args['fine_agg_kg'],
            'coarse_agg_kg' => (float) $args['coarse_agg_kg'],
            'admixture_liter' => (float) ($args['admixture_liter'] ?? 0),
            'cement_type' => 'PCC',
            'agg_max_size' => '20mm',
            'is_standard' => false,
            'is_active' => true,
        ]);

        return [
            'status' => 'success',
            'message' => "Mix design **{$design->grade}** berhasil dibuat."
                ."\n- Semen: {$design->cement_kg} kg/m³"
                ."\n- Air: {$design->water_liter} L/m³"
                ."\n- Pasir: {$design->fine_agg_kg} kg/m³"
                ."\n- Kerikil: {$design->coarse_agg_kg} kg/m³"
                ."\n- w/c ratio: {$design->water_cement_ratio}",
            'data' => ['id' => $design->id, 'grade' => $design->grade],
        ];
    }
}
