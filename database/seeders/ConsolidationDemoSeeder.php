<?php

namespace Database\Seeders;

use App\Models\CompanyGroup;
use App\Models\Tenant;
use App\Models\User;
use App\Models\ConsolidationMasterAccount;
use App\Models\ConsolidationAccountMapping;
use App\Models\ConsolidationOwnership;
use App\Services\ConsolidationService;
use Illuminate\Database\Seeder;

class ConsolidationDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Find or create Enterprise tenant for demo
        $enterpriseTenant = Tenant::where('plan', 'enterprise')->first();
        
        if (!$enterpriseTenant) {
            $enterpriseTenant = Tenant::create([
                'name' => 'PT Holding Sejahtera',
                'slug' => 'holding-sejahtera',
                'email' => 'holding@example.com',
                'phone' => '021-12345678',
                'plan' => 'enterprise',
                'is_active' => true,
                'trial_ends_at' => null,
                'plan_expires_at' => now()->addYear(),
                'business_type' => 'manufacture',
                'onboarding_completed' => true,
            ]);
        }

        // Create admin user for this tenant
        $adminUser = User::where('tenant_id', $enterpriseTenant->id)
            ->where('role', 'admin')
            ->first();

        if (!$adminUser) {
            $adminUser = User::create([
                'tenant_id' => $enterpriseTenant->id,
                'name' => 'Admin Holding',
                'email' => 'admin.holding@example.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]);
        }

        // Create subsidiary tenants
        $subsidiary1 = Tenant::firstOrCreate(
            ['slug' => 'pt-anak-1'],
            [
                'name' => 'PT Anak Perusahaan 1',
                'email' => 'anak1@example.com',
                'phone' => '021-11111111',
                'plan' => 'professional',
                'is_active' => true,
                'business_type' => 'manufacture',
                'onboarding_completed' => true,
            ]
        );

        $subsidiary2 = Tenant::firstOrCreate(
            ['slug' => 'pt-anak-2'],
            [
                'name' => 'PT Anak Perusahaan 2',
                'email' => 'anak2@example.com',
                'phone' => '021-22222222',
                'plan' => 'professional',
                'is_active' => true,
                'business_type' => 'distributor',
                'onboarding_completed' => true,
            ]
        );

        // Create Company Group
        $group = CompanyGroup::firstOrCreate(
            ['owner_user_id' => $adminUser->id, 'name' => 'Holding Sejahtera Group'],
            ['currency_code' => 'IDR']
        );

        // Add members to group
        if (!$group->members()->where('tenant_id', $enterpriseTenant->id)->exists()) {
            $group->members()->attach($enterpriseTenant->id, ['role' => 'owner']);
        }
        if (!$group->members()->where('tenant_id', $subsidiary1->id)->exists()) {
            $group->members()->attach($subsidiary1->id, ['role' => 'member']);
        }
        if (!$group->members()->where('tenant_id', $subsidiary2->id)->exists()) {
            $group->members()->attach($subsidiary2->id, ['role' => 'member']);
        }

        // Create Master COA for consolidation
        $this->createMasterCOA($group);

        // Setup account mappings
        $consolidationService = app(ConsolidationService::class);
        $consolidationService->setupAccountMapping($group, $enterpriseTenant->id);
        $consolidationService->setupAccountMapping($group, $subsidiary1->id);
        $consolidationService->setupAccountMapping($group, $subsidiary2->id);

        // Create ownership structure
        ConsolidationOwnership::firstOrCreate(
            [
                'company_group_id' => $group->id,
                'parent_tenant_id' => $enterpriseTenant->id,
                'subsidiary_tenant_id' => $subsidiary1->id,
            ],
            [
                'ownership_percentage' => 100.00,
                'effective_from' => now()->subYear(),
                'consolidation_method' => 'full',
                'notes' => 'Wholly owned subsidiary',
            ]
        );

        ConsolidationOwnership::firstOrCreate(
            [
                'company_group_id' => $group->id,
                'parent_tenant_id' => $enterpriseTenant->id,
                'subsidiary_tenant_id' => $subsidiary2->id,
            ],
            [
                'ownership_percentage' => 75.00,
                'effective_from' => now()->subYear(),
                'consolidation_method' => 'proportional',
                'notes' => 'Majority owned subsidiary',
            ]
        );

        $this->command->info('✓ Consolidation demo data seeded successfully!');
        $this->command->info("  - Company Group: {$group->name}");
        $this->command->info("  - Members: {$group->members->count()} companies");
        $this->command->info("  - Login: admin.holding@example.com / password");
    }

    private function createMasterCOA(CompanyGroup $group): void
    {
        $masterAccounts = [
            // Assets
            ['code' => '1000', 'name' => 'ASET', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 1, 'is_header' => true, 'parent_id' => null],
            ['code' => '1100', 'name' => 'Aset Lancar', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => true],
            ['code' => '1101', 'name' => 'Kas & Bank', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false],
            ['code' => '1102', 'name' => 'Piutang Usaha', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false],
            ['code' => '1103', 'name' => 'Persediaan', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false],
            ['code' => '1200', 'name' => 'Aset Tetap', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => true],
            ['code' => '1201', 'name' => 'Tanah & Bangunan', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false],
            ['code' => '1202', 'name' => 'Mesin & Peralatan', 'type' => 'asset', 'normal_balance' => 'debit', 'level' => 3, 'is_header' => false],

            // Liabilities
            ['code' => '2000', 'name' => 'KEWAJIBAN', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 1, 'is_header' => true, 'parent_id' => null],
            ['code' => '2100', 'name' => 'Kewajiban Lancar', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => true],
            ['code' => '2101', 'name' => 'Hutang Usaha', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false],
            ['code' => '2102', 'name' => 'Hutang Bank', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false],

            // Equity
            ['code' => '3000', 'name' => 'EKUITAS', 'type' => 'equity', 'normal_balance' => 'credit', 'level' => 1, 'is_header' => true, 'parent_id' => null],
            ['code' => '3100', 'name' => 'Modal Saham', 'type' => 'equity', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false],
            ['code' => '3200', 'name' => 'Laba Ditahan', 'type' => 'equity', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false],

            // Revenue
            ['code' => '4000', 'name' => 'PENDAPATAN', 'type' => 'revenue', 'normal_balance' => 'credit', 'level' => 1, 'is_header' => true, 'parent_id' => null],
            ['code' => '4100', 'name' => 'Pendapatan Penjualan', 'type' => 'revenue', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false],
            ['code' => '4200', 'name' => 'Pendapatan Lain-lain', 'type' => 'revenue', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => false],

            // Expenses
            ['code' => '5000', 'name' => 'BEBAN', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 1, 'is_header' => true, 'parent_id' => null],
            ['code' => '5100', 'name' => 'Harga Pokok Penjualan', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false],
            ['code' => '5200', 'name' => 'Beban Operasional', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false],
            ['code' => '5300', 'name' => 'Beban Gaji & Upah', 'type' => 'expense', 'normal_balance' => 'debit', 'level' => 2, 'is_header' => false],
        ];

        $parentMap = [];

        foreach ($masterAccounts as $account) {
            // Find parent_id if this is not a top-level account
            $parentId = null;
            if ($account['level'] > 1) {
                // Find parent based on code prefix
                $parentCode = substr($account['code'], 0, -2) . '00';
                if (isset($parentMap[$parentCode])) {
                    $parentId = $parentMap[$parentCode];
                }
            }

            $created = ConsolidationMasterAccount::firstOrCreate(
                [
                    'company_group_id' => $group->id,
                    'code' => $account['code'],
                ],
                [
                    'parent_id' => $parentId,
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'normal_balance' => $account['normal_balance'],
                    'level' => $account['level'],
                    'is_header' => $account['is_header'],
                    'is_active' => true,
                ]
            );

            $parentMap[$account['code']] = $created->id;
        }
    }
}
