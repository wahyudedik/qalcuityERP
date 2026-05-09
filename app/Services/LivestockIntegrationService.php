<?php

namespace App\Services;

use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\DairyMilkRecord;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LivestockFeedLog;
use App\Models\LivestockHerd;
use App\Models\LivestockMovement;
use App\Models\PoultryEggProduction;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\WarehouseStock;
use App\Models\WasteManagementLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * LivestockIntegrationService — Integrasi modul Livestock dengan Inventory dan Accounting.
 *
 * Menangani:
 * - Pembelian ternak → Jurnal: Dr Aset Ternak / Cr Kas/Hutang
 * - Penjualan ternak → Jurnal: Dr Kas/Piutang / Cr Pendapatan Penjualan Ternak + HPP
 * - Konsumsi pakan → Update stok Inventory + Jurnal: Dr Beban Pakan / Cr Persediaan Pakan
 * - Produksi susu/telur → Jurnal: Dr Persediaan Produk / Cr Pendapatan Produksi
 * - Penjualan limbah → Jurnal: Dr Kas / Cr Pendapatan Lain-lain
 */
class LivestockIntegrationService
{
    // COA codes for livestock accounting
    private const COA_LIVESTOCK_ASSET = '1106';      // Aset Ternak

    private const COA_CASH = '1101';                  // Kas

    private const COA_BANK = '1102';                  // Bank

    private const COA_ACCOUNTS_PAYABLE = '2101';      // Hutang Usaha

    private const COA_ACCOUNTS_RECEIVABLE = '1103';   // Piutang Usaha

    private const COA_LIVESTOCK_REVENUE = '4103';     // Pendapatan Penjualan Ternak

    private const COA_LIVESTOCK_COGS = '5103';        // HPP Ternak

    private const COA_FEED_EXPENSE = '5301';          // Beban Pakan Ternak

    private const COA_FEED_INVENTORY = '1109';        // Persediaan Pakan

    private const COA_DAIRY_INVENTORY = '1110';       // Persediaan Produk Susu

    private const COA_EGG_INVENTORY = '1111';         // Persediaan Telur

    private const COA_PRODUCTION_REVENUE = '4104';    // Pendapatan Produksi Peternakan

    private const COA_OTHER_REVENUE = '4199';         // Pendapatan Lain-lain

    private const COA_VETERINARY_EXPENSE = '5302';    // Beban Kesehatan Ternak

    private array $accountCache = [];

    /**
     * Post journal entry for livestock purchase.
     * Dr Aset Ternak / Cr Kas atau Hutang Usaha
     */
    public function postLivestockPurchase(
        int $tenantId,
        int $userId,
        LivestockHerd $herd,
        string $paymentMethod = 'credit'
    ): GlPostingResult {
        $amount = (float) $herd->purchase_price;
        if ($amount <= 0) {
            return GlPostingResult::skipped('Harga pembelian 0, tidak perlu jurnal.');
        }

        $reference = "LST-PUR-{$herd->code}";
        $date = $herd->entry_date?->toDateString() ?? today()->toDateString();

        $creditCode = $paymentMethod === 'cash' ? self::COA_CASH : self::COA_ACCOUNTS_PAYABLE;
        $creditDesc = $paymentMethod === 'cash'
            ? "Bayar tunai pembelian ternak {$herd->code}"
            : "Hutang pembelian ternak {$herd->code}";

        return $this->createAndPost(
            refType: 'livestock_purchase',
            reference: $reference,
            refId: $herd->id,
            tenantId: $tenantId,
            userId: $userId,
            date: $date,
            description: "Auto: Pembelian Ternak {$herd->code} - {$herd->animalLabel()} ({$herd->initial_count} ekor)",
            lines: [
                ['code' => self::COA_LIVESTOCK_ASSET, 'debit' => $amount, 'credit' => 0, 'desc' => "Aset ternak {$herd->code}"],
                ['code' => $creditCode, 'debit' => 0, 'credit' => $amount, 'desc' => $creditDesc],
            ]
        );
    }

    /**
     * Post journal entry for livestock sale.
     * Dr Kas/Piutang / Cr Pendapatan Penjualan Ternak
     * Dr HPP Ternak / Cr Aset Ternak
     */
    public function postLivestockSale(
        int $tenantId,
        int $userId,
        LivestockMovement $movement,
        string $paymentMethod = 'credit'
    ): GlPostingResult {
        $revenue = (float) $movement->price_total;
        if ($revenue <= 0) {
            return GlPostingResult::skipped('Nilai penjualan 0, tidak perlu jurnal.');
        }

        $herd = $movement->herd;
        $reference = "LST-SALE-{$herd->code}-{$movement->id}";
        $date = $movement->date?->toDateString() ?? today()->toDateString();

        // Calculate COGS based on average cost per animal
        $avgCostPerAnimal = $herd->initial_count > 0
            ? (float) $herd->purchase_price / $herd->initial_count
            : 0;
        $cogs = $avgCostPerAnimal * abs($movement->quantity);

        $debitCode = $paymentMethod === 'cash' ? self::COA_CASH : self::COA_ACCOUNTS_RECEIVABLE;
        $debitDesc = $paymentMethod === 'cash'
            ? "Terima kas penjualan ternak {$herd->code}"
            : "Piutang penjualan ternak {$herd->code}";

        $lines = [
            ['code' => $debitCode, 'debit' => $revenue, 'credit' => 0, 'desc' => $debitDesc],
            ['code' => self::COA_LIVESTOCK_REVENUE, 'debit' => 0, 'credit' => $revenue, 'desc' => "Pendapatan penjualan ternak {$herd->code}"],
        ];

        if ($cogs > 0) {
            $lines[] = ['code' => self::COA_LIVESTOCK_COGS, 'debit' => $cogs, 'credit' => 0, 'desc' => "HPP ternak {$herd->code}"];
            $lines[] = ['code' => self::COA_LIVESTOCK_ASSET, 'debit' => 0, 'credit' => $cogs, 'desc' => "Kurangi aset ternak {$herd->code}"];
        }

        return $this->createAndPost(
            refType: 'livestock_sale',
            reference: $reference,
            refId: $movement->id,
            tenantId: $tenantId,
            userId: $userId,
            date: $date,
            description: "Auto: Penjualan Ternak {$herd->code} - ".abs($movement->quantity).' ekor',
            lines: $lines
        );
    }

    /**
     * Post journal entry for feed consumption and update inventory stock.
     * Dr Beban Pakan / Cr Persediaan Pakan
     */
    public function postFeedConsumption(
        int $tenantId,
        int $userId,
        LivestockFeedLog $feedLog,
        ?int $productId = null,
        ?int $warehouseId = null
    ): GlPostingResult {
        $cost = (float) $feedLog->cost;
        if ($cost <= 0) {
            return GlPostingResult::skipped('Biaya pakan 0, tidak perlu jurnal.');
        }

        $herd = $feedLog->herd;
        $reference = "LST-FEED-{$herd->code}-{$feedLog->id}";
        $date = $feedLog->date?->toDateString() ?? today()->toDateString();

        // Update inventory stock if product and warehouse are specified
        if ($productId && $warehouseId) {
            $this->deductFeedStock($tenantId, $productId, $warehouseId, $feedLog->quantity_kg, $feedLog->id);
        }

        return $this->createAndPost(
            refType: 'livestock_feed',
            reference: $reference,
            refId: $feedLog->id,
            tenantId: $tenantId,
            userId: $userId,
            date: $date,
            description: "Auto: Konsumsi Pakan {$feedLog->feed_type} - {$herd->code} ({$feedLog->quantity_kg} kg)",
            lines: [
                ['code' => self::COA_FEED_EXPENSE, 'debit' => $cost, 'credit' => 0, 'desc' => "Beban pakan {$feedLog->feed_type} untuk {$herd->code}"],
                ['code' => self::COA_FEED_INVENTORY, 'debit' => 0, 'credit' => $cost, 'desc' => "Kurangi persediaan pakan {$feedLog->feed_type}"],
            ]
        );
    }

    /**
     * Post journal entry for dairy milk production revenue.
     * Dr Persediaan Produk Susu / Cr Pendapatan Produksi
     */
    public function postDairyProduction(
        int $tenantId,
        int $userId,
        DairyMilkRecord $milkRecord,
        float $pricePerLiter = 0
    ): GlPostingResult {
        $volume = (float) $milkRecord->milk_volume_liters;
        $value = $volume * $pricePerLiter;

        if ($value <= 0) {
            return GlPostingResult::skipped('Nilai produksi susu 0, tidak perlu jurnal.');
        }

        $herd = $milkRecord->herd;
        $reference = "LST-DAIRY-{$herd->code}-{$milkRecord->id}";
        $date = $milkRecord->record_date?->toDateString() ?? today()->toDateString();

        return $this->createAndPost(
            refType: 'dairy_production',
            reference: $reference,
            refId: $milkRecord->id,
            tenantId: $tenantId,
            userId: $userId,
            date: $date,
            description: "Auto: Produksi Susu {$herd->code} - {$volume} liter ({$milkRecord->milking_session})",
            lines: [
                ['code' => self::COA_DAIRY_INVENTORY, 'debit' => $value, 'credit' => 0, 'desc' => "Persediaan susu dari {$herd->code}"],
                ['code' => self::COA_PRODUCTION_REVENUE, 'debit' => 0, 'credit' => $value, 'desc' => "Pendapatan produksi susu {$herd->code}"],
            ]
        );
    }

    /**
     * Post journal entry for egg production revenue.
     * Dr Persediaan Telur / Cr Pendapatan Produksi
     */
    public function postEggProduction(
        int $tenantId,
        int $userId,
        PoultryEggProduction $eggRecord,
        float $pricePerEgg = 0
    ): GlPostingResult {
        $goodEggs = $eggRecord->eggs_collected - ($eggRecord->eggs_broken ?? 0);
        $value = $goodEggs * $pricePerEgg;

        if ($value <= 0) {
            return GlPostingResult::skipped('Nilai produksi telur 0, tidak perlu jurnal.');
        }

        $herd = $eggRecord->herd;
        $reference = "LST-EGG-{$herd->code}-{$eggRecord->id}";
        $date = $eggRecord->record_date?->toDateString() ?? today()->toDateString();

        return $this->createAndPost(
            refType: 'egg_production',
            reference: $reference,
            refId: $eggRecord->id,
            tenantId: $tenantId,
            userId: $userId,
            date: $date,
            description: "Auto: Produksi Telur {$herd->code} - {$goodEggs} butir",
            lines: [
                ['code' => self::COA_EGG_INVENTORY, 'debit' => $value, 'credit' => 0, 'desc' => "Persediaan telur dari {$herd->code}"],
                ['code' => self::COA_PRODUCTION_REVENUE, 'debit' => 0, 'credit' => $value, 'desc' => "Pendapatan produksi telur {$herd->code}"],
            ]
        );
    }

    /**
     * Post journal entry for waste management revenue (e.g., compost/manure sales).
     * Dr Kas / Cr Pendapatan Lain-lain
     */
    public function postWasteRevenue(
        int $tenantId,
        int $userId,
        WasteManagementLog $wasteLog
    ): GlPostingResult {
        $revenue = (float) $wasteLog->revenue_amount;
        if ($revenue <= 0) {
            return GlPostingResult::skipped('Pendapatan limbah 0, tidak perlu jurnal.');
        }

        $reference = "LST-WASTE-{$wasteLog->id}";
        $date = $wasteLog->collection_date?->toDateString() ?? today()->toDateString();
        $wasteType = $wasteLog->waste_type_label ?? $wasteLog->waste_type;
        $endProduct = $wasteLog->end_product ?? 'limbah ternak';

        return $this->createAndPost(
            refType: 'waste_revenue',
            reference: $reference,
            refId: $wasteLog->id,
            tenantId: $tenantId,
            userId: $userId,
            date: $date,
            description: "Auto: Penjualan Limbah Ternak - {$endProduct} ({$wasteLog->quantity_kg} kg)",
            lines: [
                ['code' => self::COA_CASH, 'debit' => $revenue, 'credit' => 0, 'desc' => "Terima kas penjualan {$endProduct}"],
                ['code' => self::COA_OTHER_REVENUE, 'debit' => 0, 'credit' => $revenue, 'desc' => "Pendapatan penjualan {$endProduct}"],
            ]
        );
    }

    /**
     * Post journal entry for veterinary/health expenses.
     * Dr Beban Kesehatan Ternak / Cr Kas
     */
    public function postVeterinaryExpense(
        int $tenantId,
        int $userId,
        int $healthRecordId,
        string $herdCode,
        float $cost,
        string $description,
        ?string $date = null
    ): GlPostingResult {
        if ($cost <= 0) {
            return GlPostingResult::skipped('Biaya kesehatan 0, tidak perlu jurnal.');
        }

        $reference = "LST-VET-{$herdCode}-{$healthRecordId}";
        $date = $date ?? today()->toDateString();

        return $this->createAndPost(
            refType: 'veterinary_expense',
            reference: $reference,
            refId: $healthRecordId,
            tenantId: $tenantId,
            userId: $userId,
            date: $date,
            description: "Auto: Biaya Kesehatan Ternak {$herdCode} - {$description}",
            lines: [
                ['code' => self::COA_VETERINARY_EXPENSE, 'debit' => $cost, 'credit' => 0, 'desc' => "Beban kesehatan {$herdCode}: {$description}"],
                ['code' => self::COA_CASH, 'debit' => 0, 'credit' => $cost, 'desc' => "Bayar biaya kesehatan {$herdCode}"],
            ]
        );
    }

    /**
     * Post journal entry for vaccination costs.
     * Dr Beban Kesehatan Ternak / Cr Kas
     */
    public function postVaccinationCost(
        int $tenantId,
        int $userId,
        int $vaccinationId,
        string $herdCode,
        string $vaccineName,
        float $cost,
        ?string $date = null
    ): GlPostingResult {
        if ($cost <= 0) {
            return GlPostingResult::skipped('Biaya vaksinasi 0, tidak perlu jurnal.');
        }

        $reference = "LST-VAC-{$herdCode}-{$vaccinationId}";
        $date = $date ?? today()->toDateString();

        return $this->createAndPost(
            refType: 'vaccination_cost',
            reference: $reference,
            refId: $vaccinationId,
            tenantId: $tenantId,
            userId: $userId,
            date: $date,
            description: "Auto: Vaksinasi {$vaccineName} - {$herdCode}",
            lines: [
                ['code' => self::COA_VETERINARY_EXPENSE, 'debit' => $cost, 'credit' => 0, 'desc' => "Beban vaksinasi {$vaccineName} untuk {$herdCode}"],
                ['code' => self::COA_CASH, 'debit' => 0, 'credit' => $cost, 'desc' => "Bayar vaksinasi {$vaccineName}"],
            ]
        );
    }

    /**
     * Deduct feed stock from inventory.
     */
    private function deductFeedStock(
        int $tenantId,
        int $productId,
        int $warehouseId,
        float $quantityKg,
        int $feedLogId
    ): void {
        try {
            DB::transaction(function () use ($tenantId, $productId, $warehouseId, $quantityKg, $feedLogId) {
                $stock = WarehouseStock::where('tenant_id', $tenantId)
                    ->where('warehouse_id', $warehouseId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    Log::warning("Livestock feed: Stock not found for product {$productId} in warehouse {$warehouseId}");

                    return;
                }

                if ($stock->quantity < $quantityKg) {
                    Log::warning("Livestock feed: Insufficient stock for product {$productId}. Available: {$stock->quantity}, Required: {$quantityKg}");
                    // Still deduct what's available
                    $quantityKg = $stock->quantity;
                }

                $stock->decrement('quantity', $quantityKg);

                // Record stock movement
                StockMovement::create([
                    'tenant_id' => $tenantId,
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'type' => 'out',
                    'quantity' => -$quantityKg,
                    'reference_type' => 'livestock_feed',
                    'reference_id' => $feedLogId,
                    'notes' => 'Konsumsi pakan ternak',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Livestock feed stock deduction failed: '.$e->getMessage());
        }
    }

    /**
     * Create and post journal entry.
     */
    private function createAndPost(
        string $refType,
        string $reference,
        int $refId,
        int $tenantId,
        int $userId,
        string $date,
        string $description,
        array $lines
    ): GlPostingResult {
        try {
            return DB::transaction(function () use ($refType, $reference, $refId, $tenantId, $userId, $date, $description, $lines) {
                // Check if journal already exists
                $exists = JournalEntry::where('tenant_id', $tenantId)
                    ->where('reference_type', $refType)
                    ->where('reference_id', $refId)
                    ->exists();

                if ($exists) {
                    Log::info("Livestock GL Auto-Post skipped (already exists): {$refType} {$reference}");

                    return GlPostingResult::skipped("Jurnal sudah ada untuk {$refType} {$reference}");
                }

                // Resolve account IDs
                $resolvedLines = [];
                $missingCodes = [];

                foreach ($lines as $line) {
                    $accountId = $this->resolveAccount($tenantId, $line['code']);
                    if (! $accountId) {
                        $missingCodes[] = $line['code'];
                    } else {
                        $resolvedLines[] = [
                            'account_id' => $accountId,
                            'debit' => round((float) $line['debit'], 2),
                            'credit' => round((float) $line['credit'], 2),
                            'description' => $line['desc'] ?? $description,
                        ];
                    }
                }

                if (! empty($missingCodes)) {
                    $codesStr = implode(', ', $missingCodes);
                    Log::warning("Livestock GL Auto-Post: akun [{$codesStr}] tidak ditemukan untuk tenant {$tenantId}. Ref: {$refType} {$reference}");

                    return GlPostingResult::failed(
                        "Akun COA tidak ditemukan: {$codesStr}. Silakan tambahkan akun tersebut di Chart of Accounts.",
                        $missingCodes
                    );
                }

                // Balance check
                $totalDebit = array_sum(array_column($resolvedLines, 'debit'));
                $totalCredit = array_sum(array_column($resolvedLines, 'credit'));
                if (abs($totalDebit - $totalCredit) > 0.01) {
                    $msg = "Jurnal tidak balance (D={$totalDebit} C={$totalCredit})";
                    Log::warning("Livestock GL Auto-Post: {$msg} untuk {$refType} {$reference}");

                    return GlPostingResult::failed($msg);
                }

                // Check period lock
                $periodLockService = app(PeriodLockService::class);
                if ($periodLockService->isLocked($tenantId, $date)) {
                    $lockInfo = $periodLockService->getLockInfo($tenantId, $date);
                    $msg = "Periode {$lockInfo} sudah dikunci. Tidak dapat membuat jurnal untuk tanggal {$date}.";
                    Log::warning("Livestock GL Auto-Post: {$msg} Ref: {$refType} {$reference}");

                    return GlPostingResult::failed($msg);
                }

                // Find accounting period
                $period = AccountingPeriod::findForDate($tenantId, $date);

                // Create journal entry
                $je = JournalEntry::create([
                    'tenant_id' => $tenantId,
                    'period_id' => $period?->id,
                    'user_id' => $userId,
                    'number' => JournalEntry::generateNumber($tenantId, 'LST'),
                    'date' => $date,
                    'description' => $description,
                    'reference' => $reference,
                    'reference_type' => $refType,
                    'reference_id' => $refId,
                    'currency_code' => 'IDR',
                    'currency_rate' => 1,
                    'status' => 'draft',
                ]);

                // Create journal entry lines
                foreach ($resolvedLines as $line) {
                    JournalEntryLine::create(array_merge($line, ['journal_entry_id' => $je->id]));
                }

                // Post the journal
                $je->post($userId);

                Log::info("Livestock GL Auto-Post success: {$refType} {$reference} → JE {$je->number}");

                return GlPostingResult::success($je);
            });
        } catch (\Throwable $e) {
            Log::error("Livestock GL Auto-Post exception for {$refType} {$reference}: ".$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return GlPostingResult::failed('Exception: '.$e->getMessage());
        }
    }

    /**
     * Resolve account code to account ID.
     */
    private function resolveAccount(int $tenantId, string $code): ?int
    {
        $key = "{$tenantId}:{$code}";
        if (isset($this->accountCache[$key])) {
            return $this->accountCache[$key];
        }

        $id = ChartOfAccount::where('tenant_id', $tenantId)
            ->where('code', $code)
            ->where('is_active', true)
            ->value('id');

        $this->accountCache[$key] = $id;

        return $id;
    }

    /**
     * Get summary of livestock accounting for a tenant.
     */
    public function getAccountingSummary(int $tenantId, ?string $fromDate = null, ?string $toDate = null): array
    {
        $fromDate = $fromDate ?? now()->startOfMonth()->toDateString();
        $toDate = $toDate ?? now()->endOfMonth()->toDateString();

        return [
            'livestock_purchases' => JournalEntry::where('tenant_id', $tenantId)
                ->where('reference_type', 'livestock_purchase')
                ->whereBetween('date', [$fromDate, $toDate])
                ->where('status', 'posted')
                ->sum(fn ($je) => $je->totalDebit()),
            'livestock_sales' => JournalEntry::where('tenant_id', $tenantId)
                ->where('reference_type', 'livestock_sale')
                ->whereBetween('date', [$fromDate, $toDate])
                ->where('status', 'posted')
                ->count(),
            'feed_expenses' => JournalEntry::where('tenant_id', $tenantId)
                ->where('reference_type', 'livestock_feed')
                ->whereBetween('date', [$fromDate, $toDate])
                ->where('status', 'posted')
                ->count(),
            'dairy_production' => JournalEntry::where('tenant_id', $tenantId)
                ->where('reference_type', 'dairy_production')
                ->whereBetween('date', [$fromDate, $toDate])
                ->where('status', 'posted')
                ->count(),
            'egg_production' => JournalEntry::where('tenant_id', $tenantId)
                ->where('reference_type', 'egg_production')
                ->whereBetween('date', [$fromDate, $toDate])
                ->where('status', 'posted')
                ->count(),
            'waste_revenue' => JournalEntry::where('tenant_id', $tenantId)
                ->where('reference_type', 'waste_revenue')
                ->whereBetween('date', [$fromDate, $toDate])
                ->where('status', 'posted')
                ->count(),
            'veterinary_expenses' => JournalEntry::where('tenant_id', $tenantId)
                ->whereIn('reference_type', ['veterinary_expense', 'vaccination_cost'])
                ->whereBetween('date', [$fromDate, $toDate])
                ->where('status', 'posted')
                ->count(),
        ];
    }
}
