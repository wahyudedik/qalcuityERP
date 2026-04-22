<?php

namespace App\Services;

use App\Models\CosmeticBatchRecord;
use App\Models\CosmeticFormula;
use App\Models\BatchQualityCheck;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Batch Production Service
 * 
 * @note Linter may show false positives for auth()->user() and auth()->id() - standard Laravel
 */
class BatchProductionService
{
    /**
     * TASK-2.29: Enhanced batch record generation from formula
     */
    public function createBatchFromFormula(int $formulaId, array $data): CosmeticBatchRecord
    {
        $formula = CosmeticFormula::with('ingredients')->findOrFail($formulaId);

        if ($formula->status !== 'production') {
            throw new \InvalidArgumentException('Formula must be in production status');
        }

        return DB::transaction(function () use ($formula, $data) {
            $batch = new CosmeticBatchRecord();
            $batch->tenant_id = Auth::check() ? Auth::user()->tenant_id : null;
            $batch->batch_number = $data['batch_number'] ?? CosmeticBatchRecord::getNextBatchNumber();
            $batch->formula_id = $formula->id;
            $batch->production_date = $data['production_date'];
            $batch->expiry_date = $data['expiry_date'] ?? $this->calculateExpiryDate($formula, $data['production_date']);
            $batch->planned_quantity = $data['planned_quantity'];
            $batch->actual_quantity = 0;
            $batch->yield_percentage = 0;
            $batch->status = 'draft';
            $batch->production_notes = $data['production_notes'] ?? null;
            $batch->created_by = Auth::id();
            $batch->save();

            // Auto-generate QC checkpoints from formula
            $this->generateQualityCheckpoints($batch, $formula);

            Log::info('Batch created from formula', [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'formula_id' => $formula->id,
            ]);

            return $batch;
        });
    }

    /**
     * TASK-2.30: Implement batch production workflow
     */
    public function startProduction(CosmeticBatchRecord $batch, int $userId): CosmeticBatchRecord
    {
        if (!$batch->isDraft()) {
            throw new \InvalidArgumentException('Batch must be in draft status');
        }

        $batch->status = 'in_progress';
        $batch->produced_by = $userId;
        $batch->save();

        Log::info('Production started', [
            'batch_id' => $batch->id,
            'user_id' => $userId,
        ]);

        return $batch;
    }

    /**
     * Record actual production quantity
     */
    public function recordProductionQuantity(CosmeticBatchRecord $batch, float $actualQuantity): CosmeticBatchRecord
    {
        if (!$batch->isInProgress()) {
            throw new \InvalidArgumentException('Batch must be in progress');
        }

        $batch->actual_quantity = $actualQuantity;
        $batch->calculateYield();
        $batch->save();

        Log::info('Production quantity recorded', [
            'batch_id' => $batch->id,
            'actual_quantity' => $actualQuantity,
            'yield_percentage' => $batch->yield_percentage,
        ]);

        return $batch;
    }

    /**
     * Submit batch for QC
     */
    public function submitForQC(CosmeticBatchRecord $batch): CosmeticBatchRecord
    {
        if (!$batch->isInProgress()) {
            throw new \InvalidArgumentException('Batch must be in progress');
        }

        if (!$batch->actual_quantity || $batch->actual_quantity <= 0) {
            throw new \InvalidArgumentException('Actual quantity must be recorded');
        }

        $batch->status = 'qc_pending';
        $batch->save();

        Log::info('Batch submitted for QC', [
            'batch_id' => $batch->id,
        ]);

        return $batch;
    }

    /**
     * TASK-2.31: Enhanced batch yield tracking with analysis
     */
    public function analyzeYield(CosmeticBatchRecord $batch): array
    {
        $yield = $batch->yield_percentage;
        $planned = $batch->planned_quantity;
        $actual = $batch->actual_quantity;
        $loss = $planned - $actual;

        // Get historical yield for this formula
        $historicalYields = CosmeticBatchRecord::where('formula_id', $batch->formula_id)
            ->where('status', 'released')
            ->whereNotNull('yield_percentage')
            ->pluck('yield_percentage')
            ->toArray();

        $averageYield = !empty($historicalYields) ? array_sum($historicalYields) / count($historicalYields) : null;
        $bestYield = !empty($historicalYields) ? max($historicalYields) : null;
        $worstYield = !empty($historicalYields) ? min($historicalYields) : null;

        // Determine yield status
        $yieldStatus = 'unknown';
        if ($yield >= 98) {
            $yieldStatus = 'excellent';
        } elseif ($yield >= 95) {
            $yieldStatus = 'good';
        } elseif ($yield >= 90) {
            $yieldStatus = 'acceptable';
        } elseif ($yield >= 85) {
            $yieldStatus = 'below_average';
        } else {
            $yieldStatus = 'poor';
        }

        // Get rework losses
        $reworkLosses = $batch->reworkLogs()
            ->whereNotNull('loss_quantity')
            ->sum('loss_quantity');

        return [
            'current_yield' => $yield,
            'yield_status' => $yieldStatus,
            'planned_quantity' => $planned,
            'actual_quantity' => $actual,
            'loss_quantity' => $loss,
            'loss_percentage' => $planned > 0 ? round(($loss / $planned) * 100, 2) : 0,
            'rework_losses' => $reworkLosses,
            'historical_average' => $averageYield ? round($averageYield, 2) : null,
            'historical_best' => $bestYield,
            'historical_worst' => $worstYield,
            'vs_average' => $averageYield ? round($yield - $averageYield, 2) : null,
            'total_batches_analyzed' => count($historicalYields),
        ];
    }

    /**
     * Get yield trends for a formula
     */
    public function getYieldTrends(int $formulaId, int $months = 6): array
    {
        $batches = CosmeticBatchRecord::where('formula_id', $formulaId)
            ->where('status', 'released')
            ->whereNotNull('yield_percentage')
            ->where('production_date', '>=', now()->subMonths($months))
            ->orderBy('production_date')
            ->get(['production_date', 'yield_percentage', 'batch_number', 'actual_quantity', 'planned_quantity']);

        $trends = $batches->map(function ($batch) {
            return [
                'date' => $batch->production_date ? $batch->production_date->format('Y-m-d') : null,
                'batch_number' => $batch->batch_number,
                'yield' => $batch->yield_percentage,
                'actual' => $batch->actual_quantity,
                'planned' => $batch->planned_quantity,
            ];
        });

        $yields = $trends->pluck('yield')->toArray();

        return [
            'trends' => $trends,
            'average' => !empty($yields) ? round(array_sum($yields) / count($yields), 2) : 0,
            'min' => !empty($yields) ? min($yields) : 0,
            'max' => !empty($yields) ? max($yields) : 0,
            'total_batches' => count($trends),
        ];
    }

    /**
     * Release batch with full validation
     */
    public function releaseBatch(CosmeticBatchRecord $batch, int $userId): CosmeticBatchRecord
    {
        if (!$batch->canBeReleased()) {
            throw new \InvalidArgumentException('Batch cannot be released. Check QC and rework status.');
        }

        $batch->release($userId);

        // Create accounting entries for production
        $this->createProductionJournal($batch);

        // Update inventory with produced quantity
        $this->updateInventoryForReleasedBatch($batch);

        Log::info('Batch released', [
            'batch_id' => $batch->id,
            'user_id' => $userId,
            'yield' => $batch->yield_percentage,
        ]);

        return $batch;
    }

    /**
     * Generate quality checkpoints from formula requirements
     */
    protected function generateQualityCheckpoints(CosmeticBatchRecord $batch, CosmeticFormula $formula): void
    {
        $checkpoints = [
            [
                'check_point' => 'mixing',
                'parameter' => 'pH Level',
                'target_value' => $formula->target_ph,
                'lower_limit' => $formula->target_ph ? $formula->target_ph - 0.5 : null,
                'upper_limit' => $formula->target_ph ? $formula->target_ph + 0.5 : null,
            ],
            [
                'check_point' => 'mixing',
                'parameter' => 'Viscosity',
            ],
            [
                'check_point' => 'mixing',
                'parameter' => 'Temperature',
            ],
            [
                'check_point' => 'filling',
                'parameter' => 'Fill Weight',
            ],
            [
                'check_point' => 'filling',
                'parameter' => 'Seal Integrity',
            ],
            [
                'check_point' => 'packaging',
                'parameter' => 'Label Accuracy',
            ],
            [
                'check_point' => 'packaging',
                'parameter' => 'Package Integrity',
            ],
            [
                'check_point' => 'final',
                'parameter' => 'Appearance',
            ],
            [
                'check_point' => 'final',
                'parameter' => 'Odor',
            ],
            [
                'check_point' => 'final',
                'parameter' => 'Microbial Test',
            ],
        ];

        foreach ($checkpoints as $checkpoint) {
            $check = new BatchQualityCheck();
            $check->tenant_id = $batch->tenant_id;
            $check->batch_id = $batch->id;
            $check->check_point = $checkpoint['check_point'];
            $check->parameter = $checkpoint['parameter'];
            $check->target_value = $checkpoint['target_value'] ?? null;
            $check->lower_limit = $checkpoint['lower_limit'] ?? null;
            $check->upper_limit = $checkpoint['upper_limit'] ?? null;
            $check->result = 'pending';
            $check->save();
        }
    }

    /**
     * Create production journal entry when batch is released
     */
    protected function createProductionJournal(CosmeticBatchRecord $batch): void
    {
        try {
            $formula = $batch->formula;
            if (!$formula) {
                Log::warning('Formula not found for batch journal creation', [
                    'batch_id' => $batch->id,
                ]);
                return;
            }

            $totalCost = ($formula->total_cost ?? 0) * $batch->actual_quantity;

            // Get accounts from tenant settings
            $finishedGoodsAccount = $this->getFinishedGoodsAccount($batch->tenant_id);
            $wipAccount = $this->getWIPAccount($batch->tenant_id);

            if (!$finishedGoodsAccount || !$wipAccount) {
                Log::warning('Missing accounting accounts for batch production', [
                    'batch_id' => $batch->id,
                    'tenant_id' => $batch->tenant_id,
                    'has_fg_account' => (bool) $finishedGoodsAccount,
                    'has_wip_account' => (bool) $wipAccount,
                ]);
                return;
            }

            // Create journal entry
            $journalEntry = JournalEntry::create([
                'tenant_id' => $batch->tenant_id,
                'user_id' => Auth::id(),
                'number' => JournalEntry::generateNumber($batch->tenant_id, 'AUTO'),
                'date' => now()->toDateString(),
                'description' => "Produksi batch {$batch->batch_number}",
                'reference_type' => 'cosmetic_batch',
                'reference_id' => $batch->id,
                'status' => 'draft',
            ]);

            // Add journal lines
            // Debit: Finished Goods
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $finishedGoodsAccount->id,
                'debit' => $totalCost,
                'credit' => 0,
                'description' => "Barang jadi dari batch {$batch->batch_number}",
            ]);

            // Credit: Work in Progress
            JournalEntryLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $wipAccount->id,
                'debit' => 0,
                'credit' => $totalCost,
                'description' => "Penyelesaian WIP untuk batch {$batch->batch_number}",
            ]);

            // Post the journal entry
            $journalEntry->post(Auth::id());

            Log::info('Production journal entry created', [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'journal_id' => $journalEntry->id,
                'total_cost' => $totalCost,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create production journal entry', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Get finished goods account for tenant
     */
    protected function getFinishedGoodsAccount(int $tenantId): ?ChartOfAccount
    {
        return ChartOfAccount::where('tenant_id', $tenantId)
            ->where('type', 'asset')
            ->where('is_active', true)
            ->where('name', 'like', '%Barang Jadi%')
            ->orWhere('name', 'like', '%Finished Goods%')
            ->first();
    }

    /**
     * Get work in progress account for tenant
     */
    protected function getWIPAccount(int $tenantId): ?ChartOfAccount
    {
        return ChartOfAccount::where('tenant_id', $tenantId)
            ->where('type', 'asset')
            ->where('is_active', true)
            ->where('name', 'like', '%Barang Dalam Proses%')
            ->orWhere('name', 'like', '%Work in Progress%')
            ->first();
    }

    /**
     * Update inventory when batch is released
     */
    protected function updateInventoryForReleasedBatch(CosmeticBatchRecord $batch): void
    {
        $warehouse = Warehouse::where('tenant_id', $batch->tenant_id)
            ->where('is_active', true)
            ->first();

        if (!$warehouse) {
            Log::warning('No active warehouse found for batch inventory update', [
                'batch_id' => $batch->id,
                'tenant_id' => $batch->tenant_id,
            ]);
            return;
        }

        try {
            StockMovement::create([
                'tenant_id' => $batch->tenant_id,
                'product_id' => $batch->formula_id,
                'warehouse_id' => $warehouse->id,
                'user_id' => Auth::id(),
                'type' => 'production',
                'quantity' => $batch->actual_quantity,
                'reference' => $batch->batch_number,
                'notes' => "Batch {$batch->batch_number} released from production",
            ]);

            Log::info('Stock movement created for batch', [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'quantity' => $batch->actual_quantity,
                'warehouse_id' => $warehouse->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create stock movement for batch', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate expiry date based on formula shelf life
     */
    protected function calculateExpiryDate(CosmeticFormula $formula, string $productionDate): string
    {
        if (!$formula->shelf_life_months) {
            return now()->addMonths(24)->format('Y-m-d');
        }

        return \Carbon\Carbon::parse($productionDate)
            ->addMonths($formula->shelf_life_months)
            ->format('Y-m-d');
    }

    /**
     * Get batch production statistics
     */
    public function getProductionStats(int $tenantId): array
    {
        $totalBatches = CosmeticBatchRecord::where('tenant_id', $tenantId)->count();
        $inProgress = CosmeticBatchRecord::where('tenant_id', $tenantId)->where('status', 'in_progress')->count();
        $qcPending = CosmeticBatchRecord::where('tenant_id', $tenantId)->where('status', 'qc_pending')->count();
        $released = CosmeticBatchRecord::where('tenant_id', $tenantId)->where('status', 'released')->count();
        $rejected = CosmeticBatchRecord::where('tenant_id', $tenantId)->where('status', 'rejected')->count();

        // Average yield for released batches
        $avgYield = CosmeticBatchRecord::where('tenant_id', $tenantId)
            ->where('status', 'released')
            ->whereNotNull('yield_percentage')
            ->avg('yield_percentage');

        // Batches expiring soon
        $expiringSoon = CosmeticBatchRecord::where('tenant_id', $tenantId)
            ->released()
            ->expiringSoon(30)
            ->count();

        return [
            'total_batches' => $totalBatches,
            'in_progress' => $inProgress,
            'qc_pending' => $qcPending,
            'released' => $released,
            'rejected' => $rejected,
            'average_yield' => $avgYield ? round($avgYield, 2) : 0,
            'expiring_soon' => $expiringSoon,
            'rejection_rate' => $totalBatches > 0 ? round(($rejected / $totalBatches) * 100, 2) : 0,
        ];
    }
}
