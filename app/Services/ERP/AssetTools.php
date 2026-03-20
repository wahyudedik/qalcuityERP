<?php

namespace App\Services\ERP;

use App\Models\Asset;
use App\Models\AssetDepreciation;
use App\Models\AssetMaintenance;
use Carbon\Carbon;

class AssetTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'create_asset',
                'description' => 'Daftarkan aset baru (kendaraan, mesin, peralatan, furniture, bangunan).',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'name'              => ['type' => 'string', 'description' => 'Nama aset'],
                        'category'          => ['type' => 'string', 'description' => 'vehicle, machine, equipment, furniture, building'],
                        'brand'             => ['type' => 'string', 'description' => 'Merek/brand (opsional)'],
                        'model'             => ['type' => 'string', 'description' => 'Model/tipe (opsional)'],
                        'serial_number'     => ['type' => 'string', 'description' => 'Nomor seri (opsional)'],
                        'location'          => ['type' => 'string', 'description' => 'Lokasi aset (opsional)'],
                        'purchase_date'     => ['type' => 'string', 'description' => 'Tanggal beli YYYY-MM-DD'],
                        'purchase_price'    => ['type' => 'number', 'description' => 'Harga perolehan (Rp)'],
                        'salvage_value'     => ['type' => 'number', 'description' => 'Nilai sisa/residu (Rp, default 0)'],
                        'useful_life_years' => ['type' => 'integer', 'description' => 'Umur ekonomis (tahun, default 5)'],
                        'depreciation_method' => ['type' => 'string', 'description' => 'straight_line atau declining_balance'],
                    ],
                    'required' => ['name', 'purchase_price'],
                ],
            ],
            [
                'name'        => 'list_assets',
                'description' => 'Tampilkan daftar aset perusahaan beserta nilai buku dan status.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'category' => ['type' => 'string', 'description' => 'Filter kategori (opsional)'],
                        'status'   => ['type' => 'string', 'description' => 'active, maintenance, disposed (opsional)'],
                    ],
                ],
            ],
            [
                'name'        => 'calculate_depreciation',
                'description' => 'Hitung dan catat depresiasi aset untuk periode tertentu.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'asset_name' => ['type' => 'string', 'description' => 'Nama aset (kosong = semua aset aktif)'],
                        'period'     => ['type' => 'string', 'description' => 'Periode YYYY-MM (default: bulan ini)'],
                    ],
                ],
            ],
            [
                'name'        => 'schedule_maintenance',
                'description' => 'Jadwalkan atau catat maintenance aset.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'asset_name'     => ['type' => 'string', 'description' => 'Nama aset'],
                        'type'           => ['type' => 'string', 'description' => 'scheduled, corrective, preventive'],
                        'description'    => ['type' => 'string', 'description' => 'Deskripsi pekerjaan maintenance'],
                        'scheduled_date' => ['type' => 'string', 'description' => 'Tanggal jadwal YYYY-MM-DD'],
                        'cost'           => ['type' => 'number', 'description' => 'Estimasi/aktual biaya (opsional)'],
                        'vendor'         => ['type' => 'string', 'description' => 'Nama vendor/bengkel (opsional)'],
                    ],
                    'required' => ['asset_name', 'description'],
                ],
            ],
            [
                'name'        => 'get_maintenance_schedule',
                'description' => 'Lihat jadwal maintenance aset yang akan datang atau riwayat maintenance.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'asset_name' => ['type' => 'string', 'description' => 'Nama aset (opsional, kosong = semua)'],
                        'status'     => ['type' => 'string', 'description' => 'pending, in_progress, completed (opsional)'],
                    ],
                ],
            ],
            [
                'name'        => 'update_asset_status',
                'description' => 'Update status aset (aktif, maintenance, disposed, retired).',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'asset_name' => ['type' => 'string', 'description' => 'Nama aset'],
                        'status'     => ['type' => 'string', 'description' => 'active, maintenance, disposed, retired'],
                        'notes'      => ['type' => 'string', 'description' => 'Keterangan (opsional)'],
                    ],
                    'required' => ['asset_name', 'status'],
                ],
            ],
        ];
    }

    public function createAsset(array $args): array
    {
        $count = Asset::where('tenant_id', $this->tenantId)->count() + 1;
        $code  = 'AST-' . now()->format('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $asset = Asset::create([
            'tenant_id'           => $this->tenantId,
            'asset_code'          => $code,
            'name'                => $args['name'],
            'category'            => $args['category'] ?? 'equipment',
            'brand'               => $args['brand'] ?? null,
            'model'               => $args['model'] ?? null,
            'serial_number'       => $args['serial_number'] ?? null,
            'location'            => $args['location'] ?? null,
            'purchase_date'       => $args['purchase_date'] ?? today()->toDateString(),
            'purchase_price'      => $args['purchase_price'],
            'current_value'       => $args['purchase_price'],
            'salvage_value'       => $args['salvage_value'] ?? 0,
            'useful_life_years'   => $args['useful_life_years'] ?? 5,
            'depreciation_method' => $args['depreciation_method'] ?? 'straight_line',
            'status'              => 'active',
        ]);

        $monthly = $asset->monthlyDepreciation();

        return [
            'status'  => 'success',
            'message' => "Aset **{$asset->name}** (kode: {$code}) berhasil didaftarkan.\n"
                . "Harga perolehan: Rp " . number_format($asset->purchase_price, 0, ',', '.') . "\n"
                . "Depresiasi per bulan: Rp " . number_format($monthly, 0, ',', '.') . " ({$asset->depreciation_method})\n"
                . "Umur ekonomis: {$asset->useful_life_years} tahun",
        ];
    }

    public function listAssets(array $args): array
    {
        $query = Asset::where('tenant_id', $this->tenantId);
        if (!empty($args['category'])) $query->where('category', $args['category']);
        if (!empty($args['status']))   $query->where('status', $args['status']);

        $assets = $query->orderBy('name')->get();

        if ($assets->isEmpty()) {
            return ['status' => 'success', 'message' => 'Belum ada aset yang terdaftar.'];
        }

        $totalValue = $assets->sum('current_value');

        return [
            'status'      => 'success',
            'total_assets'=> $assets->count(),
            'total_value' => 'Rp ' . number_format($totalValue, 0, ',', '.'),
            'data'        => $assets->map(fn($a) => [
                'code'          => $a->asset_code,
                'name'          => $a->name,
                'category'      => $a->category,
                'purchase_price'=> 'Rp ' . number_format($a->purchase_price, 0, ',', '.'),
                'current_value' => 'Rp ' . number_format($a->current_value, 0, ',', '.'),
                'depreciation'  => 'Rp ' . number_format($a->monthlyDepreciation(), 0, ',', '.') . '/bln',
                'status'        => $a->status,
                'location'      => $a->location ?? '-',
            ])->toArray(),
        ];
    }

    public function calculateDepreciation(array $args): array
    {
        $period = $args['period'] ?? now()->format('Y-m');
        $query  = Asset::where('tenant_id', $this->tenantId)->where('status', 'active');

        if (!empty($args['asset_name'])) {
            $query->where('name', 'like', "%{$args['asset_name']}%");
        }

        $assets = $query->get();
        if ($assets->isEmpty()) {
            return ['status' => 'error', 'message' => 'Tidak ada aset aktif yang ditemukan.'];
        }

        $results = [];
        $totalDepreciation = 0;

        foreach ($assets as $asset) {
            // Skip jika sudah dihitung periode ini
            $exists = AssetDepreciation::where('asset_id', $asset->id)->where('period', $period)->exists();
            if ($exists) {
                $results[] = ['asset' => $asset->name, 'status' => 'sudah dihitung'];
                continue;
            }

            $dep = $asset->monthlyDepreciation();
            $newValue = max($asset->salvage_value, $asset->current_value - $dep);

            AssetDepreciation::create([
                'tenant_id'          => $this->tenantId,
                'asset_id'           => $asset->id,
                'period'             => $period,
                'depreciation_amount'=> $dep,
                'book_value_after'   => $newValue,
            ]);

            $asset->update(['current_value' => $newValue]);
            $totalDepreciation += $dep;

            $results[] = [
                'asset'       => $asset->name,
                'depreciation'=> 'Rp ' . number_format($dep, 0, ',', '.'),
                'book_value'  => 'Rp ' . number_format($newValue, 0, ',', '.'),
            ];
        }

        return [
            'status'             => 'success',
            'period'             => $period,
            'total_depreciation' => 'Rp ' . number_format($totalDepreciation, 0, ',', '.'),
            'data'               => $results,
        ];
    }

    public function scheduleMaintenance(array $args): array
    {
        $asset = Asset::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['asset_name']}%")
            ->first();

        if (!$asset) {
            return ['status' => 'error', 'message' => "Aset '{$args['asset_name']}' tidak ditemukan."];
        }

        $maintenance = AssetMaintenance::create([
            'tenant_id'      => $this->tenantId,
            'asset_id'       => $asset->id,
            'type'           => $args['type'] ?? 'scheduled',
            'description'    => $args['description'],
            'scheduled_date' => $args['scheduled_date'] ?? today()->toDateString(),
            'cost'           => $args['cost'] ?? 0,
            'vendor'         => $args['vendor'] ?? null,
            'status'         => 'pending',
        ]);

        return [
            'status'  => 'success',
            'message' => "Maintenance **{$asset->name}** dijadwalkan pada "
                . Carbon::parse($maintenance->scheduled_date)->format('d M Y')
                . ". Deskripsi: {$maintenance->description}"
                . ($maintenance->vendor ? ". Vendor: {$maintenance->vendor}" : ''),
        ];
    }

    public function getMaintenanceSchedule(array $args): array
    {
        $query = AssetMaintenance::where('tenant_id', $this->tenantId)->with('asset');

        if (!empty($args['asset_name'])) {
            $query->whereHas('asset', fn($q) => $q->where('name', 'like', "%{$args['asset_name']}%"));
        }
        if (!empty($args['status'])) {
            $query->where('status', $args['status']);
        }

        $records = $query->orderBy('scheduled_date')->get();

        if ($records->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada jadwal maintenance.'];
        }

        return [
            'status' => 'success',
            'data'   => $records->map(fn($m) => [
                'asset'          => $m->asset->name,
                'type'           => $m->type,
                'description'    => $m->description,
                'scheduled_date' => $m->scheduled_date?->format('d M Y'),
                'cost'           => 'Rp ' . number_format($m->cost, 0, ',', '.'),
                'vendor'         => $m->vendor ?? '-',
                'status'         => $m->status,
            ])->toArray(),
        ];
    }

    public function updateAssetStatus(array $args): array
    {
        $asset = Asset::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['asset_name']}%")
            ->first();

        if (!$asset) {
            return ['status' => 'error', 'message' => "Aset '{$args['asset_name']}' tidak ditemukan."];
        }

        $asset->update(['status' => $args['status'], 'notes' => $args['notes'] ?? $asset->notes]);

        return [
            'status'  => 'success',
            'message' => "Status aset **{$asset->name}** diubah menjadi **{$args['status']}**.",
        ];
    }
}
