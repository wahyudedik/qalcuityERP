<?php

namespace Tests\Feature\Audit;

use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\DairyMilkRecord;
use App\Models\LivestockFeedLog;
use App\Models\LivestockHerd;
use App\Models\LivestockMovement;
use App\Models\PoultryEggProduction;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WasteManagementLog;
use App\Services\LivestockIntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test Livestock module integration with Inventory and Accounting.
 * Validates: Task 28.4 - Verifikasi integrasi Livestock dengan modul Inventory dan Accounting
 */
class LivestockIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected LivestockIntegrationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant and user
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        // Create accounting period
        AccountingPeriod::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Period '.now()->format('Y-m'),
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'status' => 'open',
        ]);

        // Create required COA accounts for livestock
        $this->createLivestockCoaAccounts();

        $this->service = app(LivestockIntegrationService::class);
    }

    protected function createLivestockCoaAccounts(): void
    {
        $accounts = [
            ['code' => '1101', 'name' => 'Kas', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1102', 'name' => 'Bank', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1103', 'name' => 'Piutang Usaha', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1106', 'name' => 'Aset Ternak', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1109', 'name' => 'Persediaan Pakan', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1110', 'name' => 'Persediaan Produk Susu', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1111', 'name' => 'Persediaan Telur', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '2101', 'name' => 'Hutang Usaha', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '4103', 'name' => 'Pendapatan Penjualan Ternak', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '4104', 'name' => 'Pendapatan Produksi Peternakan', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '4199', 'name' => 'Pendapatan Lain-lain', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '5103', 'name' => 'HPP Ternak', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5301', 'name' => 'Beban Pakan Ternak', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5302', 'name' => 'Beban Kesehatan Ternak', 'type' => 'expense', 'normal_balance' => 'debit'],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::create(array_merge($account, [
                'tenant_id' => $this->tenant->id,
                'is_active' => true,
                'is_header' => false,
                'level' => 1,
            ]));
        }
    }

    /** @test */
    public function test_livestock_purchase_creates_journal_entry()
    {
        $herd = LivestockHerd::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'HRD-001',
            'name' => 'Test Herd',
            'animal_type' => 'sapi',
            'initial_count' => 10,
            'current_count' => 10,
            'entry_date' => now(),
            'purchase_price' => 50000000, // 50 juta
            'status' => 'active',
        ]);

        $result = $this->service->postLivestockPurchase(
            $this->tenant->id,
            $this->user->id,
            $herd,
            'credit'
        );

        $this->assertTrue($result->isSuccess());
        $this->assertNotNull($result->journal);
        $this->assertEquals('posted', $result->journal->status);
        $this->assertTrue($result->journal->isBalanced());

        // Verify journal lines
        $this->assertEquals(50000000, $result->journal->totalDebit());
        $this->assertEquals(50000000, $result->journal->totalCredit());
    }

    /** @test */
    public function test_livestock_sale_creates_journal_entry_with_cogs()
    {
        $herd = LivestockHerd::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'HRD-002',
            'name' => 'Test Herd for Sale',
            'animal_type' => 'sapi',
            'initial_count' => 10,
            'current_count' => 10,
            'entry_date' => now()->subMonths(6),
            'purchase_price' => 50000000,
            'status' => 'active',
        ]);

        $movement = LivestockMovement::create([
            'livestock_herd_id' => $herd->id,
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'date' => now(),
            'type' => 'sold',
            'quantity' => -5,
            'count_after' => 5,
            'price_total' => 35000000, // 35 juta (profit)
        ]);

        $result = $this->service->postLivestockSale(
            $this->tenant->id,
            $this->user->id,
            $movement,
            'cash'
        );

        $this->assertTrue($result->isSuccess());
        $this->assertNotNull($result->journal);
        $this->assertTrue($result->journal->isBalanced());

        // Verify COGS is calculated (5 animals * 5 juta each = 25 juta)
        $cogsLine = $result->journal->lines->where('description', 'like', '%HPP%')->first();
        $this->assertNotNull($cogsLine);
        $this->assertEquals(25000000, $cogsLine->debit);
    }

    /** @test */
    public function test_feed_consumption_creates_journal_entry()
    {
        $herd = LivestockHerd::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'HRD-003',
            'name' => 'Test Herd for Feed',
            'animal_type' => 'ayam_broiler',
            'initial_count' => 1000,
            'current_count' => 1000,
            'entry_date' => now(),
            'status' => 'active',
        ]);

        $feedLog = LivestockFeedLog::create([
            'livestock_herd_id' => $herd->id,
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'date' => now(),
            'feed_type' => 'Pakan Starter',
            'quantity_kg' => 100,
            'cost' => 500000,
            'population_at_feeding' => 1000,
        ]);

        $result = $this->service->postFeedConsumption(
            $this->tenant->id,
            $this->user->id,
            $feedLog
        );

        $this->assertTrue($result->isSuccess());
        $this->assertNotNull($result->journal);
        $this->assertTrue($result->journal->isBalanced());
        $this->assertEquals(500000, $result->journal->totalDebit());
    }

    /** @test */
    public function test_dairy_production_creates_journal_entry()
    {
        $herd = LivestockHerd::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'HRD-004',
            'name' => 'Dairy Herd',
            'animal_type' => 'sapi',
            'initial_count' => 20,
            'current_count' => 20,
            'entry_date' => now(),
            'status' => 'active',
        ]);

        $milkRecord = DairyMilkRecord::create([
            'livestock_herd_id' => $herd->id,
            'tenant_id' => $this->tenant->id,
            'record_date' => now(),
            'milking_session' => 'morning',
            'milk_volume_liters' => 200,
            'recorded_by' => $this->user->id,
        ]);

        $pricePerLiter = 8000; // Rp 8.000 per liter

        $result = $this->service->postDairyProduction(
            $this->tenant->id,
            $this->user->id,
            $milkRecord,
            $pricePerLiter
        );

        $this->assertTrue($result->isSuccess());
        $this->assertNotNull($result->journal);
        $this->assertTrue($result->journal->isBalanced());
        $this->assertEquals(1600000, $result->journal->totalDebit()); // 200 * 8000
    }

    /** @test */
    public function test_egg_production_creates_journal_entry()
    {
        $herd = LivestockHerd::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'FLK-001',
            'name' => 'Layer Flock',
            'animal_type' => 'ayam_layer',
            'initial_count' => 5000,
            'current_count' => 5000,
            'entry_date' => now(),
            'status' => 'active',
        ]);

        $eggRecord = PoultryEggProduction::create([
            'livestock_herd_id' => $herd->id,
            'tenant_id' => $this->tenant->id,
            'record_date' => now(),
            'eggs_collected' => 4000,
            'eggs_broken' => 50,
            'recorded_by' => $this->user->id,
        ]);

        $pricePerEgg = 2000; // Rp 2.000 per butir

        $result = $this->service->postEggProduction(
            $this->tenant->id,
            $this->user->id,
            $eggRecord,
            $pricePerEgg
        );

        $this->assertTrue($result->isSuccess());
        $this->assertNotNull($result->journal);
        $this->assertTrue($result->journal->isBalanced());
        // Good eggs = 4000 - 50 = 3950
        $this->assertEquals(7900000, $result->journal->totalDebit()); // 3950 * 2000
    }

    /** @test */
    public function test_waste_revenue_creates_journal_entry()
    {
        $wasteLog = WasteManagementLog::create([
            'tenant_id' => $this->tenant->id,
            'collection_date' => now(),
            'waste_type' => 'manure_solid',
            'quantity_kg' => 1000,
            'disposal_method' => 'sale',
            'end_product' => 'Pupuk Kompos',
            'revenue_amount' => 500000,
            'recorded_by' => $this->user->id,
        ]);

        $result = $this->service->postWasteRevenue(
            $this->tenant->id,
            $this->user->id,
            $wasteLog
        );

        $this->assertTrue($result->isSuccess());
        $this->assertNotNull($result->journal);
        $this->assertTrue($result->journal->isBalanced());
        $this->assertEquals(500000, $result->journal->totalDebit());
    }

    /** @test */
    public function test_veterinary_expense_creates_journal_entry()
    {
        $result = $this->service->postVeterinaryExpense(
            $this->tenant->id,
            $this->user->id,
            1, // health record ID
            'HRD-001',
            250000,
            'Antibiotik untuk infeksi',
            now()->toDateString()
        );

        $this->assertTrue($result->isSuccess());
        $this->assertNotNull($result->journal);
        $this->assertTrue($result->journal->isBalanced());
        $this->assertEquals(250000, $result->journal->totalDebit());
    }

    /** @test */
    public function test_vaccination_cost_creates_journal_entry()
    {
        $result = $this->service->postVaccinationCost(
            $this->tenant->id,
            $this->user->id,
            1, // vaccination ID
            'FLK-001',
            'ND-IB Vaccine',
            150000,
            now()->toDateString()
        );

        $this->assertTrue($result->isSuccess());
        $this->assertNotNull($result->journal);
        $this->assertTrue($result->journal->isBalanced());
        $this->assertEquals(150000, $result->journal->totalDebit());
    }

    /** @test */
    public function test_journal_entry_skipped_when_amount_is_zero()
    {
        $herd = LivestockHerd::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'HRD-005',
            'name' => 'Free Herd',
            'animal_type' => 'kambing',
            'initial_count' => 5,
            'current_count' => 5,
            'entry_date' => now(),
            'purchase_price' => 0, // Free/donated
            'status' => 'active',
        ]);

        $result = $this->service->postLivestockPurchase(
            $this->tenant->id,
            $this->user->id,
            $herd,
            'cash'
        );

        $this->assertTrue($result->isSkipped());
        $this->assertNull($result->journal);
    }

    /** @test */
    public function test_duplicate_journal_entry_is_skipped()
    {
        $herd = LivestockHerd::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'HRD-006',
            'name' => 'Test Herd',
            'animal_type' => 'sapi',
            'initial_count' => 5,
            'current_count' => 5,
            'entry_date' => now(),
            'purchase_price' => 25000000,
            'status' => 'active',
        ]);

        // First call - should succeed
        $result1 = $this->service->postLivestockPurchase(
            $this->tenant->id,
            $this->user->id,
            $herd,
            'credit'
        );
        $this->assertTrue($result1->isSuccess());

        // Second call - should be skipped (idempotent)
        $result2 = $this->service->postLivestockPurchase(
            $this->tenant->id,
            $this->user->id,
            $herd,
            'credit'
        );
        $this->assertTrue($result2->isSkipped());
    }

    /** @test */
    public function test_journal_fails_when_coa_not_found()
    {
        // Delete all COA accounts
        ChartOfAccount::where('tenant_id', $this->tenant->id)->delete();

        $herd = LivestockHerd::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'HRD-007',
            'name' => 'Test Herd',
            'animal_type' => 'sapi',
            'initial_count' => 5,
            'current_count' => 5,
            'entry_date' => now(),
            'purchase_price' => 25000000,
            'status' => 'active',
        ]);

        $result = $this->service->postLivestockPurchase(
            $this->tenant->id,
            $this->user->id,
            $herd,
            'credit'
        );

        $this->assertTrue($result->isFailed());
        $this->assertNotEmpty($result->missingCoa);
    }
}
