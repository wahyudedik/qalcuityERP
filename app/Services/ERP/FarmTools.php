<?php

namespace App\Services\ERP;

use App\Models\CropCycle;
use App\Models\FarmPlot;
use App\Models\FarmPlotActivity;

class FarmTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'create_farm_plot',
                'description' => 'Tambah lahan/blok kebun baru. Gunakan untuk: '
                    . '"tambah lahan A1 sawah 2.5 hektar", "buat blok kebun B2 kelapa sawit 5 ha", '
                    . '"daftarkan lahan baru".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'code'            => ['type' => 'string', 'description' => 'Kode lahan (A1, B2, Blok-01)'],
                        'name'            => ['type' => 'string', 'description' => 'Nama lahan (Sawah Utara, Kebun Kelapa)'],
                        'area_size'       => ['type' => 'number', 'description' => 'Luas lahan'],
                        'area_unit'       => ['type' => 'string', 'description' => 'Satuan: ha, are, m2 (default: ha)'],
                        'current_crop'    => ['type' => 'string', 'description' => 'Tanaman saat ini (padi, jagung, kelapa sawit, dll)'],
                        'location'        => ['type' => 'string', 'description' => 'Lokasi/alamat (opsional)'],
                        'soil_type'       => ['type' => 'string', 'description' => 'Jenis tanah (opsional)'],
                        'irrigation_type' => ['type' => 'string', 'description' => 'Jenis irigasi (opsional)'],
                        'ownership'       => ['type' => 'string', 'description' => 'owned/rented/shared (default: owned)'],
                    ],
                    'required' => ['code', 'name', 'area_size'],
                ],
            ],
            [
                'name'        => 'get_farm_plots',
                'description' => 'Lihat daftar lahan/blok kebun dan statusnya. Gunakan untuk: '
                    . '"daftar lahan", "status semua blok", "lahan mana yang siap panen?", '
                    . '"blok yang sedang ditanam", "lahan kosong".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'status' => ['type' => 'string', 'description' => 'Filter status: idle, preparing, planted, growing, ready_harvest, harvesting, post_harvest. Kosong = semua.'],
                    ],
                ],
            ],
            [
                'name'        => 'update_plot_status',
                'description' => 'Update status lahan. Gunakan untuk: '
                    . '"blok A1 sudah ditanam padi", "lahan B2 siap panen", '
                    . '"mulai persiapan lahan C3", "blok A1 sedang dipanen".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'plot_code'       => ['type' => 'string', 'description' => 'Kode lahan (A1, B2)'],
                        'status'          => ['type' => 'string', 'description' => 'Status baru: idle, preparing, planted, growing, ready_harvest, harvesting, post_harvest'],
                        'current_crop'    => ['type' => 'string', 'description' => 'Tanaman (opsional, untuk status planted)'],
                        'planted_at'      => ['type' => 'string', 'description' => 'Tanggal tanam YYYY-MM-DD (opsional)'],
                        'expected_harvest'=> ['type' => 'string', 'description' => 'Estimasi panen YYYY-MM-DD (opsional)'],
                    ],
                    'required' => ['plot_code', 'status'],
                ],
            ],
            [
                'name'        => 'record_farm_activity',
                'description' => 'Catat aktivitas di lahan: pemupukan, penyemprotan, panen, dll. Gunakan untuk: '
                    . '"pupuk urea 50 kg di blok A1", "panen 500 kg padi dari lahan B2", '
                    . '"semprot pestisida 2 liter di blok C3", "olah tanah lahan A1 biaya 500 ribu".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'plot_code'      => ['type' => 'string', 'description' => 'Kode lahan'],
                        'activity_type'  => ['type' => 'string', 'description' => 'Jenis: planting, fertilizing, spraying, watering, weeding, pruning, harvesting, soil_prep, other'],
                        'description'    => ['type' => 'string', 'description' => 'Deskripsi aktivitas'],
                        'input_product'  => ['type' => 'string', 'description' => 'Nama produk input (Urea, Pestisida X, dll)'],
                        'input_quantity' => ['type' => 'number', 'description' => 'Jumlah input'],
                        'input_unit'     => ['type' => 'string', 'description' => 'Satuan input (kg, liter, sak)'],
                        'cost'           => ['type' => 'number', 'description' => 'Biaya aktivitas (Rp)'],
                        'harvest_qty'    => ['type' => 'number', 'description' => 'Jumlah panen (khusus harvesting)'],
                        'harvest_unit'   => ['type' => 'string', 'description' => 'Satuan panen (kg, ton, ikat)'],
                        'harvest_grade'  => ['type' => 'string', 'description' => 'Grade panen (A, B, Premium, Standar)'],
                    ],
                    'required' => ['plot_code', 'activity_type', 'description'],
                ],
            ],
            // ── Crop Cycles ───────────────────────────────────────
            [
                'name'        => 'start_crop_cycle',
                'description' => 'Mulai siklus tanam baru di lahan tertentu. Gunakan untuk: '
                    . '"mulai tanam padi di blok A1", "siklus baru jagung di lahan B2 target 5 ton", '
                    . '"mulai musim tanam 1 padi IR64 di A1 rencana panen 4 bulan lagi".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'plot_code'        => ['type' => 'string', 'description' => 'Kode lahan (A1, B2)'],
                        'crop_name'        => ['type' => 'string', 'description' => 'Nama tanaman (Padi IR64, Jagung Hibrida)'],
                        'crop_variety'     => ['type' => 'string', 'description' => 'Varietas (opsional)'],
                        'season'           => ['type' => 'string', 'description' => 'Musim tanam (MT1, Gadu, Rendeng)'],
                        'plan_plant_date'  => ['type' => 'string', 'description' => 'Rencana tanam YYYY-MM-DD'],
                        'plan_harvest_date'=> ['type' => 'string', 'description' => 'Rencana panen YYYY-MM-DD'],
                        'target_yield_qty' => ['type' => 'number', 'description' => 'Target panen (kg/ton)'],
                        'target_yield_unit'=> ['type' => 'string', 'description' => 'Satuan target (kg, ton, kuintal). Default: kg'],
                        'estimated_budget' => ['type' => 'number', 'description' => 'Anggaran biaya (Rp)'],
                    ],
                    'required' => ['plot_code', 'crop_name'],
                ],
            ],
            [
                'name'        => 'get_crop_cycles',
                'description' => 'Lihat daftar siklus tanam dan statusnya. Gunakan untuk: '
                    . '"daftar siklus tanam", "siklus aktif", "siklus yang sudah selesai", '
                    . '"progress tanam semua lahan".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'phase' => ['type' => 'string', 'description' => 'Filter fase: planning, land_prep, planting, vegetative, generative, harvest, post_harvest, completed. Kosong = semua aktif.'],
                    ],
                ],
            ],
            [
                'name'        => 'advance_crop_phase',
                'description' => 'Majukan fase siklus tanam. Gunakan untuk: '
                    . '"siklus A1 masuk fase tanam", "blok B2 mulai panen", '
                    . '"siklus CC-A1-2026-01 selesai", "lahan A1 masuk masa vegetatif".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'cycle_ref' => ['type' => 'string', 'description' => 'Nomor siklus (CC-A1-2026-01) atau kode lahan (A1, akan ambil siklus aktif)'],
                        'phase'     => ['type' => 'string', 'description' => 'Fase tujuan: land_prep, planting, vegetative, generative, harvest, post_harvest, completed, cancelled'],
                    ],
                    'required' => ['cycle_ref', 'phase'],
                ],
            ],
            [
                'name'        => 'log_harvest',
                'description' => 'Catat hasil panen detail dengan grade, pekerja, dan biaya. Gunakan untuk: '
                    . '"panen 500 kg padi dari blok A1 grade A 300 kg grade B 200 kg", '
                    . '"catat panen jagung 2 ton dari lahan B2 pekerja Siti dan Budi", '
                    . '"panen hari ini blok C3: 800 kg, reject 50 kg, kadar air 14%".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'plot_code'        => ['type' => 'string', 'description' => 'Kode lahan (A1, B2)'],
                        'crop_name'        => ['type' => 'string', 'description' => 'Nama tanaman'],
                        'total_qty'        => ['type' => 'number', 'description' => 'Total jumlah panen'],
                        'unit'             => ['type' => 'string', 'description' => 'Satuan (kg, ton, kuintal). Default: kg'],
                        'reject_qty'       => ['type' => 'number', 'description' => 'Jumlah reject/sortiran (opsional)'],
                        'moisture_pct'     => ['type' => 'number', 'description' => 'Kadar air % (opsional)'],
                        'grades'           => [
                            'type' => 'array',
                            'description' => 'Breakdown per grade. Contoh: [{"grade":"A","quantity":300,"price":8000},{"grade":"B","quantity":200,"price":6000}]',
                            'items' => ['type' => 'object', 'properties' => [
                                'grade'    => ['type' => 'string'],
                                'quantity' => ['type' => 'number'],
                                'price'    => ['type' => 'number', 'description' => 'Harga per unit grade ini'],
                            ]],
                        ],
                        'workers'          => [
                            'type' => 'array',
                            'description' => 'Daftar pekerja panen. Contoh: [{"name":"Siti","qty":200,"wage":50000}]',
                            'items' => ['type' => 'object', 'properties' => [
                                'name' => ['type' => 'string'],
                                'qty'  => ['type' => 'number'],
                                'wage' => ['type' => 'number'],
                            ]],
                        ],
                        'labor_cost'       => ['type' => 'number', 'description' => 'Total upah panen (Rp)'],
                        'transport_cost'   => ['type' => 'number', 'description' => 'Biaya angkut (Rp)'],
                        'storage_location' => ['type' => 'string', 'description' => 'Gudang tujuan penyimpanan'],
                    ],
                    'required' => ['plot_code', 'crop_name', 'total_qty'],
                ],
            ],
            // ── Analytics ─────────────────────────────────────────
            [
                'name'        => 'get_farm_cost_analysis',
                'description' => 'Analisis biaya dan produktivitas per lahan. Hitung HPP per kg, biaya per hektar, yield per hektar, dan perbandingan antar lahan. Gunakan untuk: '
                    . '"biaya per lahan", "HPP per kg dari blok A1", "lahan mana yang paling efisien?", '
                    . '"perbandingan produktivitas semua lahan", "biaya per hektar", '
                    . '"breakdown biaya pupuk pestisida per lahan".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'plot_code' => ['type' => 'string', 'description' => 'Kode lahan spesifik (A1, B2). Kosong = bandingkan semua lahan.'],
                    ],
                ],
            ],
        ];
    }

    // ─── Executors ────────────────────────────────────────────────

    public function createFarmPlot(array $args): array
    {
        if (FarmPlot::where('tenant_id', $this->tenantId)->where('code', $args['code'])->exists()) {
            return ['status' => 'error', 'message' => "Lahan dengan kode \"{$args['code']}\" sudah ada."];
        }

        $plot = FarmPlot::create([
            'tenant_id'       => $this->tenantId,
            'code'            => $args['code'],
            'name'            => $args['name'],
            'area_size'       => (float) $args['area_size'],
            'area_unit'       => $args['area_unit'] ?? 'ha',
            'current_crop'    => $args['current_crop'] ?? null,
            'location'        => $args['location'] ?? null,
            'soil_type'       => $args['soil_type'] ?? null,
            'irrigation_type' => $args['irrigation_type'] ?? null,
            'ownership'       => $args['ownership'] ?? 'owned',
            'status'          => 'idle',
            'is_active'       => true,
        ]);

        return [
            'status'  => 'success',
            'message' => "Lahan **{$plot->code} — {$plot->name}** berhasil ditambahkan."
                . "\n- Luas: **{$plot->area_size} {$plot->area_unit}**"
                . ($plot->current_crop ? "\n- Tanaman: **{$plot->current_crop}**" : '')
                . "\n- Status: **{$plot->statusLabel()}**",
            'data'    => ['id' => $plot->id, 'code' => $plot->code],
        ];
    }

    public function getFarmPlots(array $args): array
    {
        $query = FarmPlot::where('tenant_id', $this->tenantId)->where('is_active', true);

        if (!empty($args['status'])) {
            $query->where('status', $args['status']);
        }

        $plots = $query->orderBy('code')->get();

        if ($plots->isEmpty()) {
            return ['status' => 'empty', 'message' => 'Belum ada lahan terdaftar. Gunakan `create_farm_plot` untuk menambahkan.'];
        }

        $fmt = fn ($v) => number_format($v, $v == (int)$v ? 0 : 1, ',', '.');

        $rows = $plots->map(fn ($p) => [
            'kode'    => $p->code,
            'nama'    => $p->name,
            'luas'    => "{$fmt($p->area_size)} {$p->area_unit}",
            'tanaman' => $p->current_crop ?? '-',
            'status'  => $p->statusLabel(),
            'tanam'   => $p->planted_at?->format('d M Y') ?? '-',
            'panen'   => $p->expected_harvest?->format('d M Y') ?? '-',
            'overdue' => $p->isHarvestOverdue() ? '⚠️ Terlambat' : '',
        ]);

        $totalArea = $plots->sum('area_size');
        $byStatus = $plots->groupBy('status')->map->count();

        return [
            'status'  => 'success',
            'message' => "Daftar lahan ({$plots->count()} blok, total {$fmt($totalArea)} ha)",
            'data'    => [
                'total'     => $plots->count(),
                'total_area'=> "{$fmt($totalArea)} ha",
                'per_status'=> $byStatus->toArray(),
                'plots'     => $rows->toArray(),
                'url'       => '/farm/plots',
            ],
        ];
    }

    public function updatePlotStatus(array $args): array
    {
        $plot = FarmPlot::where('tenant_id', $this->tenantId)
            ->where('code', $args['plot_code'])
            ->first();

        if (!$plot) {
            return ['status' => 'not_found', 'message' => "Lahan \"{$args['plot_code']}\" tidak ditemukan."];
        }

        $updates = ['status' => $args['status']];
        if (!empty($args['current_crop'])) $updates['current_crop'] = $args['current_crop'];
        if (!empty($args['planted_at'])) $updates['planted_at'] = $args['planted_at'];
        if (!empty($args['expected_harvest'])) $updates['expected_harvest'] = $args['expected_harvest'];

        $plot->update($updates);
        $plot->refresh();

        $msg = "Status lahan **{$plot->code}** diperbarui ke **{$plot->statusLabel()}**";
        if ($plot->current_crop) $msg .= "\n- Tanaman: **{$plot->current_crop}**";
        if ($plot->planted_at) $msg .= "\n- Tanam: **{$plot->planted_at->format('d M Y')}**";
        if ($plot->expected_harvest) $msg .= "\n- Est. panen: **{$plot->expected_harvest->format('d M Y')}**";

        return ['status' => 'success', 'message' => $msg, 'data' => ['code' => $plot->code, 'status' => $plot->status]];
    }

    public function recordFarmActivity(array $args): array
    {
        $plot = FarmPlot::where('tenant_id', $this->tenantId)
            ->where('code', $args['plot_code'])
            ->first();

        if (!$plot) {
            return ['status' => 'not_found', 'message' => "Lahan \"{$args['plot_code']}\" tidak ditemukan."];
        }

        $activity = FarmPlotActivity::create([
            'farm_plot_id'   => $plot->id,
            'tenant_id'      => $this->tenantId,
            'user_id'        => $this->userId,
            'activity_type'  => $args['activity_type'],
            'date'           => today(),
            'description'    => $args['description'],
            'input_product'  => $args['input_product'] ?? null,
            'input_quantity' => (float) ($args['input_quantity'] ?? 0),
            'input_unit'     => $args['input_unit'] ?? null,
            'cost'           => (float) ($args['cost'] ?? 0),
            'harvest_qty'    => (float) ($args['harvest_qty'] ?? 0),
            'harvest_unit'   => $args['harvest_unit'] ?? null,
            'harvest_grade'  => $args['harvest_grade'] ?? null,
        ]);

        // Auto-update status
        $autoStatus = match ($args['activity_type']) {
            'soil_prep'  => 'preparing',
            'planting'   => 'planted',
            'harvesting' => 'harvesting',
            default      => null,
        };
        if ($autoStatus && $plot->status !== $autoStatus) {
            $plot->update(['status' => $autoStatus]);
        }

        $label = FarmPlotActivity::ACTIVITY_TYPES[$args['activity_type']] ?? $args['activity_type'];
        $msg = "Aktivitas **{$label}** dicatat di lahan **{$plot->code}**"
            . "\n- {$args['description']}";
        if (!empty($args['input_product'])) $msg .= "\n- Input: {$args['input_product']} {$args['input_quantity']} {$args['input_unit']}";
        if (($args['cost'] ?? 0) > 0) $msg .= "\n- Biaya: Rp " . number_format($args['cost'], 0, ',', '.');
        if (($args['harvest_qty'] ?? 0) > 0) $msg .= "\n- 🌾 Panen: **{$args['harvest_qty']} {$args['harvest_unit']}**" . ($args['harvest_grade'] ? " (Grade {$args['harvest_grade']})" : '');

        return ['status' => 'success', 'message' => $msg, 'data' => ['plot' => $plot->code, 'activity' => $activity->id]];
    }

    // ─── Crop Cycle Executors ─────────────────────────────────────

    public function startCropCycle(array $args): array
    {
        $plot = FarmPlot::where('tenant_id', $this->tenantId)->where('code', $args['plot_code'])->first();
        if (!$plot) return ['status' => 'not_found', 'message' => "Lahan \"{$args['plot_code']}\" tidak ditemukan."];

        // Check for existing active cycle
        $active = $plot->activeCycle();
        if ($active) {
            return ['status' => 'error', 'message' => "Lahan {$plot->code} sudah memiliki siklus aktif: **{$active->number}** ({$active->crop_name}, fase {$active->phaseLabel()}). Selesaikan dulu sebelum mulai siklus baru."];
        }

        $cycle = CropCycle::create([
            'farm_plot_id'      => $plot->id,
            'tenant_id'         => $this->tenantId,
            'number'            => CropCycle::generateNumber($this->tenantId, $plot->code),
            'crop_name'         => $args['crop_name'],
            'crop_variety'      => $args['crop_variety'] ?? null,
            'season'            => $args['season'] ?? null,
            'plan_plant_date'   => $args['plan_plant_date'] ?? null,
            'plan_harvest_date' => $args['plan_harvest_date'] ?? null,
            'target_yield_qty'  => (float) ($args['target_yield_qty'] ?? 0),
            'target_yield_unit' => $args['target_yield_unit'] ?? 'kg',
            'estimated_budget'  => (float) ($args['estimated_budget'] ?? 0),
            'phase'             => 'planning',
        ]);

        $plot->update([
            'current_crop'    => $args['crop_name'],
            'expected_harvest'=> $args['plan_harvest_date'] ?? null,
        ]);

        $msg = "Siklus tanam **{$cycle->number}** dimulai di lahan **{$plot->code}**"
            . "\n- Tanaman: **{$cycle->crop_name}**"
            . ($cycle->season ? "\n- Musim: **{$cycle->season}**" : '')
            . ($cycle->plan_harvest_date ? "\n- Rencana panen: **{$cycle->plan_harvest_date->format('d M Y')}**" : '')
            . ($cycle->target_yield_qty > 0 ? "\n- Target: **" . number_format($cycle->target_yield_qty, 0) . " {$cycle->target_yield_unit}**" : '');

        return ['status' => 'success', 'message' => $msg, 'data' => ['cycle' => $cycle->number, 'plot' => $plot->code]];
    }

    public function getCropCycles(array $args): array
    {
        $query = CropCycle::where('tenant_id', $this->tenantId)->with('plot');

        if (!empty($args['phase'])) {
            $query->where('phase', $args['phase']);
        } else {
            $query->whereNotIn('phase', ['completed', 'cancelled']);
        }

        $cycles = $query->orderByDesc('created_at')->get();

        if ($cycles->isEmpty()) {
            return ['status' => 'empty', 'message' => 'Tidak ada siklus tanam' . (!empty($args['phase']) ? " dengan fase \"{$args['phase']}\"" : ' aktif') . '. Gunakan `start_crop_cycle` untuk memulai.'];
        }

        $rows = $cycles->map(fn ($c) => [
            'nomor'   => $c->number,
            'lahan'   => $c->plot?->code,
            'tanaman' => $c->crop_name,
            'fase'    => $c->phaseLabel(),
            'progress'=> $c->progressPercent() . '%',
            'panen'   => $c->plan_harvest_date?->format('d M Y') ?? '-',
            'hasil'   => $c->actual_yield_qty > 0 ? number_format($c->actual_yield_qty, 0) . ' ' . $c->target_yield_unit : '-',
            'biaya'   => $c->actual_cost > 0 ? 'Rp ' . number_format($c->actual_cost, 0, ',', '.') : '-',
        ]);

        return [
            'status'  => 'success',
            'message' => "Siklus tanam ({$cycles->count()} siklus)",
            'data'    => ['cycles' => $rows->toArray(), 'url' => '/farm/cycles'],
        ];
    }

    public function advanceCropPhase(array $args): array
    {
        $ref = $args['cycle_ref'];
        $cycle = CropCycle::where('tenant_id', $this->tenantId)
            ->where(fn ($q) => $q->where('number', 'like', "%{$ref}%")
                ->orWhereHas('plot', fn ($p) => $p->where('code', $ref)))
            ->whereNotIn('phase', ['completed', 'cancelled'])
            ->first();

        if (!$cycle) return ['status' => 'not_found', 'message' => "Siklus tanam untuk \"{$ref}\" tidak ditemukan."];

        $oldPhase = $cycle->phase;
        $targetPhase = $args['phase'];

        $cycle->update(['phase' => $targetPhase]);

        // Set actual dates
        $dates = [];
        if ($targetPhase === 'land_prep' && !$cycle->actual_prep_start) $dates['actual_prep_start'] = today();
        if ($targetPhase === 'planting' && !$cycle->actual_plant_date) $dates['actual_plant_date'] = today();
        if ($targetPhase === 'harvest' && !$cycle->actual_harvest_date) $dates['actual_harvest_date'] = today();
        if (in_array($targetPhase, ['completed', 'cancelled']) && !$cycle->actual_end_date) $dates['actual_end_date'] = today();
        if ($dates) $cycle->update($dates);

        // Sync plot status
        $plotStatus = match ($targetPhase) {
            'land_prep' => 'preparing', 'planting' => 'planted',
            'vegetative', 'generative' => 'growing',
            'harvest' => 'harvesting', 'post_harvest' => 'post_harvest',
            'completed' => 'idle', default => null,
        };
        if ($plotStatus) $cycle->plot->update(['status' => $plotStatus]);

        $cycle->refresh();

        return [
            'status'  => 'success',
            'message' => "Siklus **{$cycle->number}** ({$cycle->crop_name}) diperbarui:"
                . "\n- Fase: **{$oldPhase}** → **{$cycle->phaseLabel()}**"
                . "\n- Progress: **{$cycle->progressPercent()}%**"
                . "\n- Lahan {$cycle->plot->code}: **{$cycle->plot->statusLabel()}**",
            'data'    => ['cycle' => $cycle->number, 'phase' => $cycle->phase],
        ];
    }

    public function logHarvest(array $args): array
    {
        $plot = FarmPlot::where('tenant_id', $this->tenantId)->where('code', $args['plot_code'])->first();
        if (!$plot) return ['status' => 'not_found', 'message' => "Lahan \"{$args['plot_code']}\" tidak ditemukan."];

        $cycle = $plot->activeCycle();
        $unit = $args['unit'] ?? 'kg';
        $totalQty = (float) $args['total_qty'];
        $rejectQty = (float) ($args['reject_qty'] ?? 0);

        $log = \App\Models\HarvestLog::create([
            'farm_plot_id'     => $plot->id,
            'crop_cycle_id'    => $cycle?->id,
            'tenant_id'        => $this->tenantId,
            'user_id'          => $this->userId,
            'number'           => \App\Models\HarvestLog::generateNumber($plot->code),
            'harvest_date'     => today(),
            'crop_name'        => $args['crop_name'],
            'total_qty'        => $totalQty,
            'unit'             => $unit,
            'reject_qty'       => $rejectQty,
            'moisture_pct'     => $args['moisture_pct'] ?? null,
            'storage_location' => $args['storage_location'] ?? null,
            'labor_cost'       => (float) ($args['labor_cost'] ?? 0),
            'transport_cost'   => (float) ($args['transport_cost'] ?? 0),
        ]);

        // Save grades
        foreach ($args['grades'] ?? [] as $g) {
            \App\Models\HarvestLogGrade::create([
                'harvest_log_id' => $log->id,
                'grade'          => $g['grade'],
                'quantity'       => (float) $g['quantity'],
                'unit'           => $unit,
                'price_per_unit' => (float) ($g['price'] ?? 0),
            ]);
        }

        // Save workers
        foreach ($args['workers'] ?? [] as $w) {
            $emp = \App\Models\Employee::where('tenant_id', $this->tenantId)
                ->where('name', 'like', "%{$w['name']}%")->first();
            \App\Models\HarvestLogWorker::create([
                'harvest_log_id'  => $log->id,
                'employee_id'     => $emp?->id,
                'worker_name'     => $w['name'],
                'quantity_picked' => (float) ($w['qty'] ?? 0),
                'unit'            => $unit,
                'wage'            => (float) ($w['wage'] ?? 0),
            ]);
        }

        // Sync to crop cycle
        if ($cycle) {
            FarmPlotActivity::create([
                'farm_plot_id'  => $plot->id,
                'crop_cycle_id' => $cycle->id,
                'tenant_id'     => $this->tenantId,
                'user_id'       => $this->userId,
                'activity_type' => 'harvesting',
                'date'          => today(),
                'description'   => "Panen {$totalQty} {$unit} ({$log->number})",
                'harvest_qty'   => $totalQty,
                'harvest_unit'  => $unit,
                'cost'          => $log->totalCost(),
            ]);
            $cycle->recalculate();
        }

        $fmt = fn ($v) => number_format($v, $v == (int)$v ? 0 : 1, ',', '.');
        $netQty = $log->netQty();

        $msg = "🌾 Panen dicatat: **{$log->number}**"
            . "\n- Lahan: **{$plot->code}** — {$args['crop_name']}"
            . "\n- Total: **{$fmt($totalQty)} {$unit}**"
            . ($rejectQty > 0 ? " | Reject: **{$fmt($rejectQty)} {$unit}** ({$log->rejectPercent()}%)" : '')
            . "\n- Bersih: **{$fmt($netQty)} {$unit}**";

        if (!empty($args['grades'])) {
            $msg .= "\n- Grade: " . collect($args['grades'])->map(fn ($g) => "{$g['grade']}: {$fmt($g['quantity'])} {$unit}")->implode(', ');
        }
        if (!empty($args['workers'])) {
            $msg .= "\n- Pekerja: " . collect($args['workers'])->pluck('name')->implode(', ');
        }
        if ($log->totalCost() > 0) {
            $msg .= "\n- Biaya: Rp {$fmt($log->totalCost())}";
        }
        if ($cycle) {
            $msg .= "\n- Siklus {$cycle->number}: total panen **{$fmt($cycle->actual_yield_qty)} {$cycle->target_yield_unit}** ({$cycle->yieldPercent()}% target)";
        }

        return ['status' => 'success', 'message' => $msg, 'data' => ['harvest' => $log->number, 'plot' => $plot->code]];
    }

    // ─── Analytics Executor ───────────────────────────────────────

    public function getFarmCostAnalysis(array $args): array
    {
        $svc = app(\App\Services\FarmAnalyticsService::class);
        $fmt = fn ($v) => number_format($v, $v == (int)$v ? 0 : 1, ',', '.');

        // Single plot detail
        if (!empty($args['plot_code'])) {
            $plot = FarmPlot::where('tenant_id', $this->tenantId)->where('code', $args['plot_code'])->first();
            if (!$plot) return ['status' => 'not_found', 'message' => "Lahan \"{$args['plot_code']}\" tidak ditemukan."];

            $breakdown = $svc->plotCostBreakdown($plot->id);
            $costPerHa = $svc->costPerHectare($plot);
            $hpp = $svc->hppPerKg($plot);
            $yieldPerHa = $svc->yieldPerHectare($plot);
            $totalCost = collect($breakdown)->sum('cost');
            $totalHarvest = $plot->totalHarvest();

            $msg = "📊 Analisis Biaya Lahan **{$plot->code}** — {$plot->name}"
                . "\n\n| Metrik | Nilai |"
                . "\n|--------|-------|"
                . "\n| Luas | {$fmt($plot->area_size)} {$plot->area_unit} |"
                . "\n| Total Biaya | Rp {$fmt($totalCost)} |"
                . "\n| Biaya/Ha | Rp {$fmt($costPerHa)} |"
                . "\n| Total Panen | {$fmt($totalHarvest)} kg |"
                . "\n| Yield/Ha | {$fmt($yieldPerHa)} kg |"
                . "\n| **HPP/kg** | **" . ($hpp ? "Rp {$fmt($hpp)}" : '-') . "** |";

            if (!empty($breakdown)) {
                $msg .= "\n\n**Breakdown Biaya:**\n| Aktivitas | Biaya | % |"
                    . "\n|-----------|-------|---|";
                foreach ($breakdown as $b) {
                    $msg .= "\n| {$b['label']} | Rp {$fmt($b['cost'])} | {$b['pct']}% |";
                }
            }

            return [
                'status'  => 'success',
                'message' => $msg,
                'data'    => [
                    'plot'        => $plot->code,
                    'total_cost'  => $totalCost,
                    'cost_per_ha' => $costPerHa,
                    'hpp_per_kg'  => $hpp,
                    'yield_per_ha'=> $yieldPerHa,
                    'breakdown'   => $breakdown,
                    'url'         => '/farm/analytics',
                ],
            ];
        }

        // Compare all plots
        $comparison = $svc->comparePlots($this->tenantId);

        if (empty($comparison)) {
            return ['status' => 'empty', 'message' => 'Belum ada data lahan untuk dianalisis.'];
        }

        $totalCost = collect($comparison)->sum('total_cost');
        $totalHarvest = collect($comparison)->sum('total_harvest');
        $avgHpp = $totalHarvest > 0 ? round($totalCost / $totalHarvest, 2) : null;

        $msg = "📊 **Perbandingan Biaya & Produktivitas Semua Lahan**"
            . "\n\nRata-rata HPP: **" . ($avgHpp ? "Rp {$fmt($avgHpp)}/kg" : '-') . "**"
            . "\n\n| Lahan | Tanaman | Biaya/Ha | Yield/Ha | HPP/kg | Reject |"
            . "\n|-------|---------|----------|----------|--------|--------|";

        $ranked = collect($comparison)->filter(fn ($p) => $p['hpp_per_kg'] !== null)->sortBy('hpp_per_kg');

        foreach ($comparison as $p) {
            $msg .= "\n| {$p['code']} | {$p['crop']} | Rp {$fmt($p['cost_per_ha'])} | {$fmt($p['yield_per_ha'])} kg | "
                . ($p['hpp_per_kg'] !== null ? "Rp {$fmt($p['hpp_per_kg'])}" : '-') . " | {$p['reject_pct']}% |";
        }

        if ($ranked->isNotEmpty()) {
            $best = $ranked->first();
            $worst = $ranked->last();
            $msg .= "\n\n🏆 Paling efisien: **{$best['code']}** (HPP Rp {$fmt($best['hpp_per_kg'])}/kg)";
            if ($ranked->count() > 1) {
                $msg .= "\n⚠️ Paling mahal: **{$worst['code']}** (HPP Rp {$fmt($worst['hpp_per_kg'])}/kg)";
            }
        }

        return [
            'status'  => 'success',
            'message' => $msg,
            'data'    => [
                'plots'        => $comparison,
                'avg_hpp'      => $avgHpp,
                'total_cost'   => $totalCost,
                'total_harvest'=> $totalHarvest,
                'url'          => '/farm/analytics',
            ],
        ];
    }
}
