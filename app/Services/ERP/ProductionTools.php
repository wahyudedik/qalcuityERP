<?php

namespace App\Services\ERP;

use App\Models\Product;
use App\Models\ProductionOutput;
use App\Models\ProductStock;
use App\Models\Recipe;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductionTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    // ─── Tool Definitions ─────────────────────────────────────────

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'create_work_order',
                'description' => 'Buat Work Order (perintah produksi) baru. '
                    . 'Gunakan untuk manufaktur/konveksi/produksi terencana: '
                    . '"produksi 1000 paving", "buat WO 100 kaos ukuran L", '
                    . '"rencanakan produksi 500 bata merah", "perintah produksi 200 meja".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'product_name'    => ['type' => 'string',  'description' => 'Nama produk yang akan diproduksi'],
                        'target_quantity' => ['type' => 'number',  'description' => 'Target jumlah produksi'],
                        'recipe_name'     => ['type' => 'string',  'description' => 'Nama resep/BOM yang digunakan (opsional, auto-detect dari produk)'],
                        'labor_cost'      => ['type' => 'number',  'description' => 'Biaya tenaga kerja (opsional, default: 0)'],
                        'overhead_cost'   => ['type' => 'number',  'description' => 'Biaya overhead (opsional, default: 0)'],
                        'notes'           => ['type' => 'string',  'description' => 'Catatan work order (opsional)'],
                    ],
                    'required' => ['product_name', 'target_quantity'],
                ],
            ],
            [
                'name'        => 'update_work_order_status',
                'description' => 'Update status Work Order. '
                    . 'Gunakan untuk: "mulai WO-001", "WO-ABC selesai", "batalkan work order WO-XYZ", '
                    . '"WO-001 sudah selesai dikerjakan", "start produksi WO-002".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'work_order_number' => ['type' => 'string', 'description' => 'Nomor work order (misal: WO-ABCD1234)'],
                        'status'            => ['type' => 'string', 'description' => 'Status baru: in_progress, completed, cancelled'],
                        'notes'             => ['type' => 'string', 'description' => 'Catatan perubahan status (opsional)'],
                    ],
                    'required' => ['work_order_number', 'status'],
                ],
            ],
            [
                'name'        => 'record_production_output',
                'description' => 'Catat hasil produksi aktual (good + reject/cacat) untuk sebuah Work Order. '
                    . 'Gunakan untuk: "WO-001 hasil 95 bagus 5 reject", '
                    . '"catat produksi WO-ABC: 980 paving bagus, 20 cacat", '
                    . '"output WO-XYZ: 100 kaos OK, 3 reject jahitan lepas".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'work_order_number' => ['type' => 'string', 'description' => 'Nomor work order'],
                        'good_qty'          => ['type' => 'number', 'description' => 'Jumlah produk bagus/lolos QC'],
                        'reject_qty'        => ['type' => 'number', 'description' => 'Jumlah produk cacat/reject (default: 0)'],
                        'reject_reason'     => ['type' => 'string', 'description' => 'Alasan reject/cacat (opsional)'],
                        'auto_complete'     => ['type' => 'boolean', 'description' => 'true = otomatis set WO ke completed setelah catat output (default: false)'],
                        'notes'             => ['type' => 'string', 'description' => 'Catatan output (opsional)'],
                    ],
                    'required' => ['work_order_number', 'good_qty'],
                ],
            ],
            [
                'name'        => 'get_production_summary',
                'description' => 'Ringkasan dan laporan produksi — jumlah WO, output, reject, biaya, yield rate. '
                    . 'Gunakan untuk: "progress produksi hari ini", "laporan produksi minggu ini", '
                    . '"barang reject berapa?", "summary work order", "biaya produksi bulan ini".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'today, this_week, this_month, last_month, atau YYYY-MM'],
                        'status' => ['type' => 'string', 'description' => 'Filter status WO: pending, in_progress, completed, cancelled (opsional)'],
                    ],
                ],
            ],
            [
                'name'        => 'get_work_order_detail',
                'description' => 'Lihat detail sebuah Work Order beserta semua output yang sudah dicatat. '
                    . 'Gunakan untuk: "detail WO-001", "status WO-ABC", "progress WO-XYZ".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'work_order_number' => ['type' => 'string', 'description' => 'Nomor work order'],
                    ],
                    'required' => ['work_order_number'],
                ],
            ],
        ];
    }

    // ─── Executors ────────────────────────────────────────────────

    public function createWorkOrder(array $args): array
    {
        $product = Product::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['product_name']}%")
            ->first();

        if (!$product) {
            return ['status' => 'error', 'message' => "Produk \"{$args['product_name']}\" tidak ditemukan."];
        }

        // Cari recipe — spesifik atau auto-detect dari produk
        $recipe = null;
        if (!empty($args['recipe_name'])) {
            $recipe = Recipe::where('tenant_id', $this->tenantId)
                ->where('name', 'like', "%{$args['recipe_name']}%")
                ->where('is_active', true)
                ->first();
        } else {
            $recipe = Recipe::where('tenant_id', $this->tenantId)
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->first();
        }

        $number = 'WO-' . strtoupper(Str::random(8));

        $wo = WorkOrder::create([
            'tenant_id'       => $this->tenantId,
            'product_id'      => $product->id,
            'recipe_id'       => $recipe?->id,
            'user_id'         => $this->userId,
            'number'          => $number,
            'target_quantity' => $args['target_quantity'],
            'unit'            => $product->unit,
            'status'          => 'pending',
            'labor_cost'      => $args['labor_cost'] ?? 0,
            'overhead_cost'   => $args['overhead_cost'] ?? 0,
            'notes'           => $args['notes'] ?? null,
        ]);

        $recipeInfo = $recipe
            ? "\nResep: **{$recipe->name}**"
            : "\n_Belum ada resep — biaya bahan baku tidak akan dihitung otomatis._";

        return [
            'status'  => 'success',
            'message' => "Work Order **{$number}** berhasil dibuat.\n"
                . "Produk: **{$product->name}**\n"
                . "Target: **{$args['target_quantity']} {$product->unit}**"
                . $recipeInfo . "\n"
                . "Status: **Pending** — gunakan `update_work_order_status` untuk mulai produksi.",
            'data' => [
                'number'          => $number,
                'product'         => $product->name,
                'target_quantity' => $args['target_quantity'],
                'has_recipe'      => $recipe !== null,
            ],
        ];
    }

    public function updateWorkOrderStatus(array $args): array
    {
        $wo = $this->findWorkOrder($args['work_order_number']);
        if (!$wo) {
            return ['status' => 'not_found', 'message' => "Work Order \"{$args['work_order_number']}\" tidak ditemukan."];
        }

        $newStatus = $args['status'];

        if (!$wo->canTransitionTo($newStatus)) {
            $allowed = WorkOrder::VALID_TRANSITIONS[$wo->status] ?? [];
            $allowedStr = empty($allowed)
                ? 'tidak ada (status final)'
                : implode(', ', $allowed);

            return [
                'status'  => 'error',
                'message' => "Tidak bisa mengubah status WO **{$wo->number}** dari **{$wo->status}** ke **{$newStatus}**.\n"
                    . "Transisi yang diizinkan dari `{$wo->status}`: **{$allowedStr}**.",
            ];
        }

        $updates = ['status' => $newStatus];

        if ($newStatus === 'in_progress' && !$wo->started_at) {
            $updates['started_at'] = now();
        }

        if ($newStatus === 'completed') {
            $updates['completed_at'] = now();
            // Hitung total_cost saat completed
            $updates['total_cost'] = $wo->material_cost + $wo->labor_cost + $wo->overhead_cost;
        }

        if (!empty($args['notes'])) {
            $updates['notes'] = $args['notes'];
        }

        $wo->update($updates);

        $statusLabel = match ($newStatus) {
            'in_progress' => 'Sedang Dikerjakan',
            'completed'   => 'Selesai',
            'cancelled'   => 'Dibatalkan',
            default       => $newStatus,
        };

        $extra = '';
        if ($newStatus === 'completed') {
            $good   = $wo->totalGoodQty();
            $reject = $wo->totalRejectQty();
            $yield  = $wo->yieldRate();
            $extra  = "\nTotal output: **{$good} {$wo->unit}** bagus, **{$reject}** reject"
                . ($yield !== null ? " (yield: **{$yield}%**)" : '')
                . "\nTotal biaya: **Rp " . number_format($wo->total_cost, 0, ',', '.') . "**";
        }

        return [
            'status'  => 'success',
            'message' => "Work Order **{$wo->number}** diupdate ke status **{$statusLabel}**.{$extra}",
            'data'    => [
                'number'     => $wo->number,
                'old_status' => $wo->getOriginal('status'),
                'new_status' => $newStatus,
            ],
        ];
    }

    public function recordProductionOutput(array $args): array
    {
        $wo = $this->findWorkOrder($args['work_order_number']);
        if (!$wo) {
            return ['status' => 'not_found', 'message' => "Work Order \"{$args['work_order_number']}\" tidak ditemukan."];
        }

        if ($wo->status === 'cancelled') {
            return ['status' => 'error', 'message' => "Work Order **{$wo->number}** sudah dibatalkan. Tidak bisa mencatat output."];
        }

        if ($wo->status === 'pending') {
            return ['status' => 'error', 'message' => "Work Order **{$wo->number}** masih pending. Mulai dulu dengan `update_work_order_status` ke `in_progress`."];
        }

        $goodQty   = (float) $args['good_qty'];
        $rejectQty = (float) ($args['reject_qty'] ?? 0);

        if ($goodQty < 0 || $rejectQty < 0) {
            return ['status' => 'error', 'message' => 'Nilai good_qty dan reject_qty tidak boleh negatif.'];
        }

        $autoComplete = $args['auto_complete'] ?? false;

        return DB::transaction(function () use ($wo, $goodQty, $rejectQty, $args, $autoComplete) {
            // Simpan output
            $output = ProductionOutput::create([
                'work_order_id' => $wo->id,
                'tenant_id'     => $this->tenantId,
                'user_id'       => $this->userId,
                'good_qty'      => $goodQty,
                'reject_qty'    => $rejectQty,
                'reject_reason' => $args['reject_reason'] ?? null,
                'notes'         => $args['notes'] ?? null,
            ]);

            // Jika WO completed (atau auto_complete), tambah stok produk jadi sebesar good_qty
            $shouldAddStock = $wo->status === 'completed' || $autoComplete;

            if ($autoComplete && $wo->status === 'in_progress') {
                $wo->update([
                    'status'       => 'completed',
                    'completed_at' => now(),
                    'total_cost'   => $wo->material_cost + $wo->labor_cost + $wo->overhead_cost,
                ]);
                $wo->refresh();
            }

            $stockMsg = '';
            if ($shouldAddStock && $goodQty > 0) {
                $warehouse = Warehouse::where('tenant_id', $this->tenantId)->where('is_active', true)->first();

                if ($warehouse) {
                    $finishedStock = ProductStock::firstOrCreate(
                        ['product_id' => $wo->product_id, 'warehouse_id' => $warehouse->id],
                        ['quantity'   => 0]
                    );

                    $before = $finishedStock->quantity;
                    $finishedStock->increment('quantity', $goodQty);

                    StockMovement::create([
                        'tenant_id'       => $this->tenantId,
                        'product_id'      => $wo->product_id,
                        'warehouse_id'    => $warehouse->id,
                        'user_id'         => $this->userId,
                        'type'            => 'in',
                        'quantity'        => $goodQty,
                        'quantity_before' => $before,
                        'quantity_after'  => $before + $goodQty,
                        'reference'       => $wo->number,
                        'notes'           => "Output produksi WO {$wo->number}",
                    ]);

                    // Deduct bahan baku jika WO punya recipe
                    if ($wo->recipe_id) {
                        $this->deductIngredientsForOutput($wo, $goodQty + $rejectQty, $warehouse);
                    }

                    $stockMsg = "\nStok **{$wo->product->name}** bertambah **{$goodQty} {$wo->unit}** → total: **" . ($before + $goodQty) . " {$wo->unit}**";
                }
            }

            $outputQty  = $goodQty + $rejectQty;
            $yieldRate  = $outputQty > 0 ? round(($goodQty / $outputQty) * 100, 1) : 100;
            $rejectInfo = $rejectQty > 0
                ? "\nReject: **{$rejectQty} {$wo->unit}**" . (!empty($args['reject_reason']) ? " ({$args['reject_reason']})" : '')
                : '';

            return [
                'status'  => 'success',
                'message' => "Output produksi WO **{$wo->number}** berhasil dicatat.\n"
                    . "Bagus: **{$goodQty} {$wo->unit}**{$rejectInfo}\n"
                    . "Yield rate: **{$yieldRate}%**"
                    . $stockMsg,
                'data' => [
                    'work_order' => $wo->number,
                    'good_qty'   => $goodQty,
                    'reject_qty' => $rejectQty,
                    'yield_rate' => $yieldRate,
                ],
            ];
        });
    }

    public function getProductionSummary(array $args): array
    {
        $query = WorkOrder::where('tenant_id', $this->tenantId)
            ->with(['product', 'outputs']);

        // Filter periode
        if (!empty($args['period'])) {
            $query = $this->applyPeriod($query, $args['period']);
        }

        // Filter status
        if (!empty($args['status'])) {
            $query->where('status', $args['status']);
        }

        $workOrders = $query->latest()->get();

        if ($workOrders->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada work order pada periode ini.'];
        }

        // Agregasi per status
        $byStatus = $workOrders->groupBy('status')->map(fn($g) => $g->count());

        $totalGood   = $workOrders->sum(fn($wo) => $wo->totalGoodQty());
        $totalReject = $workOrders->sum(fn($wo) => $wo->totalRejectQty());
        $totalOutput = $totalGood + $totalReject;
        $totalCost   = $workOrders->where('status', 'completed')->sum('total_cost');
        $overallYield = $totalOutput > 0 ? round(($totalGood / $totalOutput) * 100, 1) : null;

        // Detail per WO (max 20)
        $details = $workOrders->take(20)->map(fn($wo) => [
            'nomor'       => $wo->number,
            'produk'      => $wo->product->name,
            'target'      => $wo->target_quantity . ' ' . $wo->unit,
            'good'        => $wo->totalGoodQty(),
            'reject'      => $wo->totalRejectQty(),
            'yield'       => $wo->yieldRate() !== null ? $wo->yieldRate() . '%' : '-',
            'status'      => $wo->status,
            'biaya'       => $wo->status === 'completed' ? 'Rp ' . number_format($wo->total_cost, 0, ',', '.') : '-',
            'mulai'       => $wo->started_at?->format('d M Y H:i') ?? '-',
            'selesai'     => $wo->completed_at?->format('d M Y H:i') ?? '-',
        ])->toArray();

        return [
            'status' => 'success',
            'data'   => [
                'periode'       => $args['period'] ?? 'semua',
                'total_wo'      => $workOrders->count(),
                'per_status'    => $byStatus->toArray(),
                'total_good'    => $totalGood . ' unit',
                'total_reject'  => $totalReject . ' unit',
                'overall_yield' => $overallYield !== null ? $overallYield . '%' : '-',
                'total_biaya'   => 'Rp ' . number_format($totalCost, 0, ',', '.'),
                'work_orders'   => $details,
            ],
        ];
    }

    public function getWorkOrderDetail(array $args): array
    {
        $wo = $this->findWorkOrder($args['work_order_number']);
        if (!$wo) {
            return ['status' => 'not_found', 'message' => "Work Order \"{$args['work_order_number']}\" tidak ditemukan."];
        }

        $wo->load(['product', 'recipe', 'outputs', 'user']);

        $outputs = $wo->outputs->map(fn($o) => [
            'tanggal'    => $o->created_at->format('d M Y H:i'),
            'good'       => $o->good_qty + 0,
            'reject'     => $o->reject_qty + 0,
            'total'      => $o->output_qty + 0,
            'alasan'     => $o->reject_reason ?? '-',
            'catatan'    => $o->notes ?? '-',
        ])->toArray();

        $totalGood   = $wo->totalGoodQty();
        $totalReject = $wo->totalRejectQty();
        $progress    = $wo->target_quantity > 0
            ? round(($totalGood / $wo->target_quantity) * 100, 1)
            : 0;

        return [
            'status' => 'success',
            'data'   => [
                'nomor'          => $wo->number,
                'produk'         => $wo->product->name,
                'resep'          => $wo->recipe?->name ?? '-',
                'target'         => $wo->target_quantity . ' ' . $wo->unit,
                'status'         => $wo->status,
                'progress'       => $progress . '%',
                'total_good'     => $totalGood . ' ' . $wo->unit,
                'total_reject'   => $totalReject . ' ' . $wo->unit,
                'yield_rate'     => $wo->yieldRate() !== null ? $wo->yieldRate() . '%' : '-',
                'material_cost'  => 'Rp ' . number_format($wo->material_cost, 0, ',', '.'),
                'labor_cost'     => 'Rp ' . number_format($wo->labor_cost, 0, ',', '.'),
                'overhead_cost'  => 'Rp ' . number_format($wo->overhead_cost, 0, ',', '.'),
                'total_cost'     => 'Rp ' . number_format($wo->total_cost, 0, ',', '.'),
                'cost_per_unit'  => $wo->costPerGoodUnit() !== null
                    ? 'Rp ' . number_format($wo->costPerGoodUnit(), 0, ',', '.')
                    : '-',
                'dibuat_oleh'    => $wo->user->name,
                'mulai'          => $wo->started_at?->format('d M Y H:i') ?? 'Belum dimulai',
                'selesai'        => $wo->completed_at?->format('d M Y H:i') ?? '-',
                'catatan'        => $wo->notes ?? '-',
                'outputs'        => $outputs,
            ],
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────

    protected function findWorkOrder(string $number): ?WorkOrder
    {
        return WorkOrder::where('tenant_id', $this->tenantId)
            ->where(fn($q) => $q->where('number', $number)
                ->orWhere('number', 'like', "%{$number}%"))
            ->with(['product', 'recipe'])
            ->first();
    }

    /**
     * Deduct stok bahan baku berdasarkan output_qty * qty_per_unit dari recipe.
     * Dipanggil saat WO completed dan punya recipe.
     */
    protected function deductIngredientsForOutput(WorkOrder $wo, float $outputQty, Warehouse $warehouse): void
    {
        $recipe = $wo->recipe()->with('ingredients.product')->first();
        if (!$recipe) return;

        foreach ($recipe->ingredients as $ingredient) {
            $qtyNeeded = ($ingredient->quantity_per_batch / $recipe->batch_size) * $outputQty;

            $stock = ProductStock::where('product_id', $ingredient->product_id)
                ->where('warehouse_id', $warehouse->id)
                ->first();

            if (!$stock || $stock->quantity <= 0) continue;

            $deduct = min($qtyNeeded, $stock->quantity); // tidak boleh negatif
            $before = $stock->quantity;
            $stock->decrement('quantity', $deduct);

            StockMovement::create([
                'tenant_id'       => $this->tenantId,
                'product_id'      => $ingredient->product_id,
                'warehouse_id'    => $warehouse->id,
                'user_id'         => $this->userId,
                'type'            => 'out',
                'quantity'        => $deduct,
                'quantity_before' => $before,
                'quantity_after'  => $before - $deduct,
                'reference'       => $wo->number,
                'notes'           => "Bahan baku WO {$wo->number}",
            ]);
        }

        // Update material_cost di WO
        $materialCost = $recipe->ingredients->sum(fn($ing) =>
            ($ing->product->price_buy ?? 0) * ($ing->quantity_per_batch / $recipe->batch_size) * $outputQty
        );

        $wo->update([
            'material_cost' => $materialCost,
            'total_cost'    => $materialCost + $wo->labor_cost + $wo->overhead_cost,
        ]);
    }

    protected function applyPeriod($query, string $period)
    {
        return match ($period) {
            'today'      => $query->whereDate('created_at', today()),
            'this_week'  => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'this_month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            'last_month' => $query->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year),
            default      => strlen($period) === 7
                ? $query->whereYear('created_at', substr($period, 0, 4))->whereMonth('created_at', substr($period, 5, 2))
                : $query,
        };
    }
}
