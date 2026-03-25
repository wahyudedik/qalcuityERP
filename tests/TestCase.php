<?php

namespace Tests;

use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    // ── Tenant & User helpers ─────────────────────────────────────

    protected function createTenant(array $attrs = []): Tenant
    {
        return Tenant::create(array_merge([
            'name'      => 'Test Company',
            'slug'      => 'test-company-' . uniqid(),
            'email'     => 'test-' . uniqid() . '@example.com',
            'plan'      => 'pro',
            'is_active' => true,
        ], $attrs));
    }

    protected function createAdminUser(Tenant $tenant, array $attrs = []): User
    {
        return User::create(array_merge([
            'tenant_id'         => $tenant->id,
            'name'              => 'Admin Test',
            'email'             => 'admin-' . uniqid() . '@test.com',
            'password'          => bcrypt('password'),
            'role'              => 'admin',
            'is_active'         => true,
            'email_verified_at' => now(),
        ], $attrs));
    }

    // ── Accounting helpers ────────────────────────────────────────

    /**
     * Seed minimal COA accounts needed for GL auto-posting.
     * Codes: 1101 Kas, 1102 Bank, 1103 Piutang, 1105 Persediaan,
     *        2101 Hutang Usaha, 2103 PPN Keluaran,
     *        4101 Pendapatan Penjualan, 5101 HPP
     */
    protected function seedCoa(int $tenantId): void
    {
        $accounts = [
            ['code' => '1101', 'name' => 'Kas',                  'type' => 'asset',     'normal_balance' => 'debit'],
            ['code' => '1102', 'name' => 'Bank',                 'type' => 'asset',     'normal_balance' => 'debit'],
            ['code' => '1103', 'name' => 'Piutang Usaha',        'type' => 'asset',     'normal_balance' => 'debit'],
            ['code' => '1105', 'name' => 'Persediaan',           'type' => 'asset',     'normal_balance' => 'debit'],
            ['code' => '2101', 'name' => 'Hutang Usaha',         'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '2103', 'name' => 'PPN Keluaran',         'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '4101', 'name' => 'Pendapatan Penjualan', 'type' => 'revenue',   'normal_balance' => 'credit'],
            ['code' => '5101', 'name' => 'HPP Barang',           'type' => 'expense',   'normal_balance' => 'debit'],
            ['code' => '5204', 'name' => 'Beban Penyusutan',     'type' => 'expense',   'normal_balance' => 'debit'],
            ['code' => '1202', 'name' => 'Akumulasi Penyusutan', 'type' => 'asset',     'normal_balance' => 'credit'],
            ['code' => '5201', 'name' => 'Beban Gaji',           'type' => 'expense',   'normal_balance' => 'debit'],
            ['code' => '2108', 'name' => 'Hutang Gaji',          'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '2104', 'name' => 'PPh 21 Terutang',      'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '2109', 'name' => 'Hutang BPJS',          'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '5209', 'name' => 'Beban BPJS',           'type' => 'expense',   'normal_balance' => 'debit'],
        ];

        foreach ($accounts as $acc) {
            ChartOfAccount::create(array_merge($acc, [
                'tenant_id'  => $tenantId,
                'level'      => 3,
                'is_header'  => false,
                'is_active'  => true,
            ]));
        }
    }

    // ── Domain object helpers ─────────────────────────────────────

    protected function createCustomer(int $tenantId, array $attrs = []): Customer
    {
        return Customer::create(array_merge([
            'tenant_id' => $tenantId,
            'name'      => 'Customer Test',
            'is_active' => true,
        ], $attrs));
    }

    protected function createWarehouse(int $tenantId, array $attrs = []): Warehouse
    {
        return Warehouse::create(array_merge([
            'tenant_id' => $tenantId,
            'name'      => 'Gudang Utama',
            'code'      => 'GDG-' . uniqid(),
            'is_active' => true,
        ], $attrs));
    }

    protected function createProduct(int $tenantId, array $attrs = []): Product
    {
        return Product::create(array_merge([
            'tenant_id'  => $tenantId,
            'name'       => 'Produk Test',
            'sku'        => 'SKU-' . uniqid(),
            'unit'       => 'pcs',
            'price_sell' => 100000,
            'price_buy'  => 70000,
            'is_active'  => true,
        ], $attrs));
    }

    protected function setStock(int $productId, int $warehouseId, float $qty): ProductStock
    {
        return ProductStock::updateOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['quantity' => $qty]
        );
    }

    // ── Assertion helpers ─────────────────────────────────────────

    protected function assertJournalPosted(int $tenantId, string $refType, string $reference): void
    {
        $this->assertDatabaseHas('journal_entries', [
            'tenant_id'      => $tenantId,
            'reference_type' => $refType,
            'reference'      => $reference,
            'status'         => 'posted',
        ]);
    }

    protected function assertJournalNotPosted(int $tenantId, string $refType): void
    {
        $this->assertDatabaseMissing('journal_entries', [
            'tenant_id'      => $tenantId,
            'reference_type' => $refType,
            'status'         => 'posted',
        ]);
    }
}
