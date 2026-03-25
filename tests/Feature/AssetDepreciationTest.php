<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetDepreciation;
use App\Models\JournalEntry;
use Tests\TestCase;

class AssetDepreciationTest extends TestCase
{
    private $tenant;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = $this->createTenant();
        $this->user   = $this->createAdminUser($this->tenant);
        $this->seedCoa($this->tenant->id);
    }

    private function createAsset(array $attrs = []): Asset
    {
        return Asset::create(array_merge([
            'tenant_id'           => $this->tenant->id,
            'asset_code'          => 'AST-' . uniqid(),
            'name'                => 'Laptop Test',
            'category'            => 'equipment',
            'purchase_date'       => now()->subYear(),
            'purchase_price'      => 12000000,
            'current_value'       => 12000000,
            'salvage_value'       => 1000000,
            'useful_life_years'   => 5,
            'depreciation_method' => 'straight_line',
            'status'              => 'active',
        ], $attrs));
    }

    // ── Manual depreciation via controller ───────────────────────

    public function test_calculates_depreciation_and_reduces_asset_value(): void
    {
        $asset = $this->createAsset();
        $originalValue = $asset->current_value;
        $monthlyDep    = $asset->monthlyDepreciation();

        $this->actingAs($this->user);

        $response = $this->post(route('assets.depreciate'), [
            'period' => '2026-03',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Nilai aset berkurang
        $asset->refresh();
        $this->assertLessThan($originalValue, $asset->current_value);
        $this->assertEquals(
            round($originalValue - $monthlyDep, 2),
            round($asset->current_value, 2)
        );

        // Record depresiasi tersimpan
        $this->assertDatabaseHas('asset_depreciations', [
            'tenant_id' => $this->tenant->id,
            'asset_id'  => $asset->id,
            'period'    => '2026-03',
        ]);
    }

    public function test_posts_gl_journal_on_depreciation(): void
    {
        $this->createAsset();

        $this->actingAs($this->user);

        $this->post(route('assets.depreciate'), ['period' => '2026-03']);

        // Jurnal GL diposting
        $journal = JournalEntry::where('tenant_id', $this->tenant->id)
            ->where('reference_type', 'asset_depreciation')
            ->where('status', 'posted')
            ->with('lines.account')
            ->first();

        $this->assertNotNull($journal, 'GL journal should be created for depreciation');

        // Dr Beban Penyusutan (5204)
        $debitLine = $journal->lines->where('debit', '>', 0)->first();
        $this->assertEquals('5204', $debitLine->account->code);

        // Cr Akumulasi Penyusutan (1202)
        $creditLine = $journal->lines->where('credit', '>', 0)->first();
        $this->assertEquals('1202', $creditLine->account->code);

        // Balance
        $this->assertEquals(
            round($journal->lines->sum('debit'), 2),
            round($journal->lines->sum('credit'), 2)
        );
    }

    public function test_links_journal_to_depreciation_records(): void
    {
        $this->createAsset();

        $this->actingAs($this->user);

        $this->post(route('assets.depreciate'), ['period' => '2026-03']);

        $dep = AssetDepreciation::where('tenant_id', $this->tenant->id)
            ->where('period', '2026-03')
            ->first();

        $this->assertNotNull($dep->journal_entry_id,
            'AssetDepreciation should be linked to journal entry');
    }

    public function test_batches_multiple_assets_into_one_journal(): void
    {
        $this->createAsset(['name' => 'Laptop 1']);
        $this->createAsset(['name' => 'Laptop 2', 'asset_code' => 'AST-2-' . uniqid()]);
        $this->createAsset(['name' => 'Printer',  'asset_code' => 'AST-3-' . uniqid()]);

        $this->actingAs($this->user);

        $this->post(route('assets.depreciate'), ['period' => '2026-03']);

        // Hanya 1 jurnal untuk semua aset (batch)
        $journalCount = JournalEntry::where('tenant_id', $this->tenant->id)
            ->where('reference_type', 'asset_depreciation')
            ->count();

        $this->assertEquals(1, $journalCount, 'All assets should be batched into one journal');

        // Tapi 3 AssetDepreciation records
        $depCount = AssetDepreciation::where('tenant_id', $this->tenant->id)
            ->where('period', '2026-03')
            ->count();

        $this->assertEquals(3, $depCount);
    }

    public function test_prevents_duplicate_depreciation_for_same_period(): void
    {
        $asset = $this->createAsset();

        $this->actingAs($this->user);

        // Jalankan 2x untuk periode yang sama
        $this->post(route('assets.depreciate'), ['period' => '2026-03']);
        $this->post(route('assets.depreciate'), ['period' => '2026-03']);

        // Hanya 1 record depresiasi
        $this->assertEquals(1, AssetDepreciation::where('asset_id', $asset->id)
            ->where('period', '2026-03')->count());
    }

    public function test_does_not_depreciate_below_salvage_value(): void
    {
        // Aset dengan current_value hampir sama dengan salvage_value
        $asset = $this->createAsset([
            'current_value' => 1100000,
            'salvage_value' => 1000000,
        ]);

        $this->actingAs($this->user);

        $this->post(route('assets.depreciate'), ['period' => '2026-03']);

        $asset->refresh();
        $this->assertGreaterThanOrEqual(1000000, $asset->current_value,
            'Asset value should not go below salvage value');
    }

    public function test_still_calculates_depreciation_even_when_coa_missing(): void
    {
        \App\Models\ChartOfAccount::where('tenant_id', $this->tenant->id)->delete();

        $asset = $this->createAsset();

        $this->actingAs($this->user);

        $response = $this->post(route('assets.depreciate'), ['period' => '2026-03']);

        // Depresiasi tetap dihitung
        $this->assertDatabaseHas('asset_depreciations', [
            'asset_id' => $asset->id,
            'period'   => '2026-03',
        ]);

        // Tapi ada warning
        $response->assertSessionHas('warning');

        // Tidak ada jurnal
        $this->assertDatabaseMissing('journal_entries', [
            'tenant_id'      => $this->tenant->id,
            'reference_type' => 'asset_depreciation',
        ]);
    }
}
