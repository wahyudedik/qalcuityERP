<?php

namespace Tests\Feature\Agent;

use App\Models\AiMemory;
use App\Services\AiMemoryService;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Tests\TestCase;

/**
 * Property-Based Test for AiMemoryService.
 *
 * Feature: erp-ai-agent, Property 11: Memory Isolation Per Tenant-User
 *
 * Property 11: Memory Isolation Per Tenant-User
 * getPreferences(tenantA, userA) tidak pernah mengembalikan data dari
 * kombinasi (tenantB, userB) yang berbeda.
 *
 * Validates: Requirements 5.4
 */
class AiMemoryServiceIsolationTest extends TestCase
{
    use TestTrait;

    private AiMemoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AiMemoryService;
    }

    // =========================================================================
    // Property 11: Memory Isolation Per Tenant-User
    //
    // getPreferences(tenantA, userA) tidak pernah mengembalikan data dari
    // kombinasi (tenantB, userB) yang berbeda.
    //
    // Feature: erp-ai-agent, Property 11: Memory Isolation Per Tenant-User
    // Validates: Requirements 5.4
    // =========================================================================

    #[ErisRepeat(repeat: 5)]
    public function test_memory_isolation_per_tenant_user(): void
    {
        $this->forAll(
            // Nilai preferensi unik untuk tenant A / user A
            Generators::elements('transfer', 'cash', 'credit_card', 'qris', 'virtual_account'),
            // Nilai preferensi unik untuk tenant B / user B
            Generators::elements('gudang_utara', 'gudang_selatan', 'gudang_pusat', 'gudang_barat'),
        )->then(function (string $paymentMethodA, string $warehouseB) {
            // Buat dua tenant yang berbeda
            $tenantA = $this->createTenant(['name' => 'Tenant A '.uniqid()]);
            $tenantB = $this->createTenant(['name' => 'Tenant B '.uniqid()]);

            // Buat user untuk masing-masing tenant
            $userA = $this->createAdminUser($tenantA);
            $userB = $this->createAdminUser($tenantB);

            // Seed memori untuk (tenantA, userA)
            AiMemory::create([
                'tenant_id' => $tenantA->id,
                'user_id' => $userA->id,
                'key' => 'preferred_payment_method',
                'value' => $paymentMethodA,
                'frequency' => 5,
                'last_seen_at' => now(),
                'first_observed_at' => now()->subDays(10),
                'confidence_score' => 0.8,
            ]);

            // Seed memori untuk (tenantB, userB) — data yang berbeda
            AiMemory::create([
                'tenant_id' => $tenantB->id,
                'user_id' => $userB->id,
                'key' => 'default_warehouse',
                'value' => $warehouseB,
                'frequency' => 3,
                'last_seen_at' => now(),
                'first_observed_at' => now()->subDays(5),
                'confidence_score' => 0.6,
            ]);

            // Ambil preferensi untuk (tenantA, userA)
            $prefsA = $this->service->getPreferences($tenantA->id, $userA->id);

            // ── Assert: preferensi A mengandung data A ──
            $this->assertSame(
                $paymentMethodA,
                $prefsA['preferred_payment_method'],
                'getPreferences(tenantA, userA) harus mengembalikan data milik tenantA/userA'
            );

            // ── Assert: preferensi A TIDAK mengandung data dari B ──
            $this->assertNull(
                $prefsA['default_warehouse'],
                'getPreferences(tenantA, userA) tidak boleh mengembalikan data dari tenantB/userB'
            );

            // Ambil preferensi untuk (tenantB, userB)
            $prefsB = $this->service->getPreferences($tenantB->id, $userB->id);

            // ── Assert: preferensi B mengandung data B ──
            $this->assertSame(
                $warehouseB,
                $prefsB['default_warehouse'],
                'getPreferences(tenantB, userB) harus mengembalikan data milik tenantB/userB'
            );

            // ── Assert: preferensi B TIDAK mengandung data dari A ──
            $this->assertNull(
                $prefsB['preferred_payment_method'],
                'getPreferences(tenantB, userB) tidak boleh mengembalikan data dari tenantA/userA'
            );
        });
    }

    // =========================================================================
    // Property 11 (cross-user same tenant): Isolasi antar user dalam tenant yang sama
    //
    // Dua user dalam tenant yang sama tidak boleh saling melihat memori masing-masing.
    //
    // Feature: erp-ai-agent, Property 11: Memory Isolation Per Tenant-User
    // Validates: Requirements 5.4
    // =========================================================================

    #[ErisRepeat(repeat: 5)]
    public function test_memory_isolation_between_users_in_same_tenant(): void
    {
        $this->forAll(
            Generators::elements('transfer', 'cash', 'credit_card', 'qris'),
            Generators::elements('gudang_a', 'gudang_b', 'gudang_c', 'gudang_d'),
        )->then(function (string $paymentUser1, string $warehouseUser2) {
            $tenant = $this->createTenant();
            $user1 = $this->createAdminUser($tenant);
            $user2 = $this->createAdminUser($tenant, [
                'email' => 'user2-'.uniqid().'@test.com',
                'role' => 'staff',
            ]);

            // Seed memori untuk user1
            AiMemory::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user1->id,
                'key' => 'preferred_payment_method',
                'value' => $paymentUser1,
                'frequency' => 4,
                'last_seen_at' => now(),
                'first_observed_at' => now()->subDays(7),
                'confidence_score' => 0.7,
            ]);

            // Seed memori untuk user2
            AiMemory::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user2->id,
                'key' => 'default_warehouse',
                'value' => $warehouseUser2,
                'frequency' => 2,
                'last_seen_at' => now(),
                'first_observed_at' => now()->subDays(3),
                'confidence_score' => 0.5,
            ]);

            $prefsUser1 = $this->service->getPreferences($tenant->id, $user1->id);
            $prefsUser2 = $this->service->getPreferences($tenant->id, $user2->id);

            // User1 harus mendapat datanya sendiri, bukan data user2
            $this->assertSame($paymentUser1, $prefsUser1['preferred_payment_method']);
            $this->assertNull($prefsUser1['default_warehouse'],
                'User1 tidak boleh melihat default_warehouse milik user2');

            // User2 harus mendapat datanya sendiri, bukan data user1
            $this->assertSame($warehouseUser2, $prefsUser2['default_warehouse']);
            $this->assertNull($prefsUser2['preferred_payment_method'],
                'User2 tidak boleh melihat preferred_payment_method milik user1');
        });
    }

    // =========================================================================
    // Property 11 (edge case): Tenant baru tanpa data mengembalikan semua null
    //
    // Feature: erp-ai-agent, Property 11: Memory Isolation Per Tenant-User
    // Validates: Requirements 5.4
    // =========================================================================

    public function test_empty_tenant_returns_all_null_preferences(): void
    {
        $tenantWithData = $this->createTenant();
        $emptyTenant = $this->createTenant();

        $userWithData = $this->createAdminUser($tenantWithData);
        $emptyUser = $this->createAdminUser($emptyTenant);

        // Seed data hanya untuk tenantWithData
        AiMemory::create([
            'tenant_id' => $tenantWithData->id,
            'user_id' => $userWithData->id,
            'key' => 'preferred_payment_method',
            'value' => 'transfer',
            'frequency' => 10,
            'last_seen_at' => now(),
            'first_observed_at' => now()->subDays(30),
            'confidence_score' => 1.0,
        ]);

        // Tenant kosong harus mendapat semua null
        $emptyPrefs = $this->service->getPreferences($emptyTenant->id, $emptyUser->id);

        foreach (AiMemoryService::KEYS as $key) {
            $this->assertNull(
                $emptyPrefs[$key],
                "Preferensi '{$key}' untuk tenant kosong harus null, bukan data dari tenant lain"
            );
        }
    }
}
