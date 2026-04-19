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
 * Feature: erp-ai-agent, Property 12: Memory Confidence Decay
 *
 * Property 12: Memory Confidence Decay
 * Record dengan last_seen_at > 90 hari mendapat penurunan confidence 50%;
 * record dengan confidence hasil penurunan < 0.1 dihapus.
 *
 * Validates: Requirements 5.5
 */
class AiMemoryServiceDecayTest extends TestCase
{
    use TestTrait;

    private AiMemoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AiMemoryService();
    }

    // =========================================================================
    // Property 12: Memory Confidence Decay — 50% reduction for stale records
    //
    // Record dengan last_seen_at > 90 hari mendapat penurunan confidence 50%.
    //
    // Feature: erp-ai-agent, Property 12: Memory Confidence Decay
    // Validates: Requirements 5.5
    // =========================================================================

    #[ErisRepeat(repeat: 5)]
    public function testStaleRecordsGetFiftyPercentConfidenceReduction(): void
    {
        $this->forAll(
            // confidence_score awal: antara 0.2 dan 1.0 (setelah decay masih >= 0.1)
            Generators::map(
                fn(int $v) => round($v / 100, 2),
                Generators::choose(20, 100)
            ),
            // Berapa hari yang lalu last_seen_at: 91 hingga 365 hari
            Generators::choose(91, 365),
        )->then(function (float $initialScore, int $daysAgo) {
            $tenant = $this->createTenant();
            $user   = $this->createAdminUser($tenant);

            $expectedNewScore = $initialScore * 0.5;

            // Hanya buat record yang setelah decay TIDAK akan dihapus (newScore >= 0.1)
            if ($expectedNewScore < 0.1) {
                // Skip kasus ini — akan diuji di test penghapusan
                $this->assertTrue(true);
                return;
            }

            $memory = AiMemory::create([
                'tenant_id'        => $tenant->id,
                'user_id'          => $user->id,
                'key'              => 'preferred_payment_method',
                'value'            => 'transfer',
                'frequency'        => 5,
                'last_seen_at'     => now()->subDays($daysAgo),
                'first_observed_at' => now()->subDays($daysAgo + 10),
                'confidence_score' => $initialScore,
            ]);

            $this->service->pruneStaleMemoriesForTenant($tenant->id);

            // Record harus masih ada (tidak dihapus)
            $updated = AiMemory::withoutGlobalScopes()->find($memory->id);
            $this->assertNotNull(
                $updated,
                "Record dengan confidence {$initialScore} setelah decay {$expectedNewScore} tidak boleh dihapus"
            );

            // Confidence harus turun 50%
            $this->assertEqualsWithDelta(
                $expectedNewScore,
                $updated->confidence_score,
                0.001,
                "Confidence harus turun 50% dari {$initialScore} menjadi {$expectedNewScore}, "
                . "bukan {$updated->confidence_score}"
            );
        });
    }

    // =========================================================================
    // Property 12: Memory Confidence Decay — delete records below 0.1
    //
    // Record dengan confidence hasil penurunan < 0.1 dihapus.
    //
    // Feature: erp-ai-agent, Property 12: Memory Confidence Decay
    // Validates: Requirements 5.5
    // =========================================================================

    #[ErisRepeat(repeat: 5)]
    public function testStaleRecordsBelowThresholdAreDeleted(): void
    {
        $this->forAll(
            // confidence_score awal: antara 0.01 dan 0.19 (setelah decay * 0.5 < 0.1)
            Generators::map(
                fn(int $v) => round($v / 1000, 3),
                Generators::choose(10, 190)
            ),
            // Berapa hari yang lalu: 91 hingga 365
            Generators::choose(91, 365),
        )->then(function (float $initialScore, int $daysAgo) {
            $expectedNewScore = $initialScore * 0.5;

            // Hanya uji kasus di mana hasil decay < 0.1
            if ($expectedNewScore >= 0.1) {
                $this->assertTrue(true);
                return;
            }

            $tenant = $this->createTenant();
            $user   = $this->createAdminUser($tenant);

            $memory = AiMemory::create([
                'tenant_id'        => $tenant->id,
                'user_id'          => $user->id,
                'key'              => 'default_warehouse',
                'value'            => 'gudang_utama',
                'frequency'        => 1,
                'last_seen_at'     => now()->subDays($daysAgo),
                'first_observed_at' => now()->subDays($daysAgo + 5),
                'confidence_score' => $initialScore,
            ]);

            $this->service->pruneStaleMemoriesForTenant($tenant->id);

            // Record harus sudah dihapus
            $deleted = AiMemory::withoutGlobalScopes()->find($memory->id);
            $this->assertNull(
                $deleted,
                "Record dengan confidence {$initialScore} (decay → {$expectedNewScore} < 0.1) harus dihapus"
            );
        });
    }

    // =========================================================================
    // Property 12 (boundary): Record dengan last_seen_at tepat 90 hari tidak terpengaruh
    //
    // Hanya record dengan last_seen_at LEBIH DARI 90 hari yang terdampak.
    //
    // Feature: erp-ai-agent, Property 12: Memory Confidence Decay
    // Validates: Requirements 5.5
    // =========================================================================

    #[ErisRepeat(repeat: 5)]
    public function testRecentRecordsAreNotAffectedByPrune(): void
    {
        $this->forAll(
            // confidence_score awal: antara 0.1 dan 1.0
            Generators::map(
                fn(int $v) => round($v / 100, 2),
                Generators::choose(10, 100)
            ),
            // Berapa hari yang lalu: 0 hingga 90 hari (tidak stale)
            Generators::choose(0, 90),
        )->then(function (float $initialScore, int $daysAgo) {
            $tenant = $this->createTenant();
            $user   = $this->createAdminUser($tenant);

            $memory = AiMemory::create([
                'tenant_id'        => $tenant->id,
                'user_id'          => $user->id,
                'key'              => 'preferred_currency',
                'value'            => 'IDR',
                'frequency'        => 3,
                'last_seen_at'     => now()->subDays($daysAgo),
                'first_observed_at' => now()->subDays($daysAgo + 5),
                'confidence_score' => $initialScore,
            ]);

            $this->service->pruneStaleMemoriesForTenant($tenant->id);

            // Record tidak boleh terpengaruh
            $unchanged = AiMemory::withoutGlobalScopes()->find($memory->id);
            $this->assertNotNull(
                $unchanged,
                "Record dengan last_seen_at {$daysAgo} hari lalu tidak boleh dihapus"
            );
            $this->assertEqualsWithDelta(
                $initialScore,
                $unchanged->confidence_score,
                0.001,
                "Confidence record yang belum stale tidak boleh berubah"
            );
        });
    }

    // =========================================================================
    // Property 12 (tenant isolation): pruneStaleMemoriesForTenant hanya
    // mempengaruhi tenant yang ditentukan, bukan tenant lain.
    //
    // Feature: erp-ai-agent, Property 12: Memory Confidence Decay
    // Validates: Requirements 5.5, 5.4
    // =========================================================================

    public function testPruneOnlyAffectsSpecifiedTenant(): void
    {
        $tenantA = $this->createTenant(['name' => 'Tenant A ' . uniqid()]);
        $tenantB = $this->createTenant(['name' => 'Tenant B ' . uniqid()]);

        $userA = $this->createAdminUser($tenantA);
        $userB = $this->createAdminUser($tenantB);

        // Record stale untuk tenant A (akan terdampak)
        $memoryA = AiMemory::create([
            'tenant_id'        => $tenantA->id,
            'user_id'          => $userA->id,
            'key'              => 'preferred_payment_method',
            'value'            => 'transfer',
            'frequency'        => 2,
            'last_seen_at'     => now()->subDays(100),
            'first_observed_at' => now()->subDays(110),
            'confidence_score' => 0.6,
        ]);

        // Record stale untuk tenant B (tidak boleh terdampak)
        $memoryB = AiMemory::create([
            'tenant_id'        => $tenantB->id,
            'user_id'          => $userB->id,
            'key'              => 'preferred_payment_method',
            'value'            => 'cash',
            'frequency'        => 3,
            'last_seen_at'     => now()->subDays(120),
            'first_observed_at' => now()->subDays(130),
            'confidence_score' => 0.8,
        ]);

        // Prune hanya untuk tenant A
        $this->service->pruneStaleMemoriesForTenant($tenantA->id);

        // Tenant A: confidence harus turun 50%
        $updatedA = AiMemory::withoutGlobalScopes()->find($memoryA->id);
        $this->assertNotNull($updatedA, 'Record tenant A harus masih ada (0.6 * 0.5 = 0.3 >= 0.1)');
        $this->assertEqualsWithDelta(0.3, $updatedA->confidence_score, 0.001,
            'Confidence tenant A harus turun dari 0.6 ke 0.3');

        // Tenant B: tidak boleh terpengaruh sama sekali
        $unchangedB = AiMemory::withoutGlobalScopes()->find($memoryB->id);
        $this->assertNotNull($unchangedB, 'Record tenant B tidak boleh dihapus');
        $this->assertEqualsWithDelta(0.8, $unchangedB->confidence_score, 0.001,
            'Confidence tenant B tidak boleh berubah');
    }

    // =========================================================================
    // Edge case: Record dengan confidence tepat 0.2 (decay → 0.1) tidak dihapus
    //
    // Batas bawah: confidence_score hasil decay = 0.1 tidak dihapus (< 0.1 yang dihapus).
    //
    // Feature: erp-ai-agent, Property 12: Memory Confidence Decay
    // Validates: Requirements 5.5
    // =========================================================================

    public function testRecordWithDecayedScoreExactlyAtThresholdIsKept(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        // 0.2 * 0.5 = 0.1 — tepat di batas, tidak boleh dihapus
        $memory = AiMemory::create([
            'tenant_id'        => $tenant->id,
            'user_id'          => $user->id,
            'key'              => 'preferred_payment_method',
            'value'            => 'transfer',
            'frequency'        => 1,
            'last_seen_at'     => now()->subDays(100),
            'first_observed_at' => now()->subDays(110),
            'confidence_score' => 0.2,
        ]);

        $this->service->pruneStaleMemoriesForTenant($tenant->id);

        $result = AiMemory::withoutGlobalScopes()->find($memory->id);
        $this->assertNotNull($result, 'Record dengan decay score = 0.1 tidak boleh dihapus');
        $this->assertEqualsWithDelta(0.1, $result->confidence_score, 0.001);
    }

    // =========================================================================
    // Edge case: Record dengan confidence tepat di bawah 0.2 (decay → < 0.1) dihapus
    //
    // Feature: erp-ai-agent, Property 12: Memory Confidence Decay
    // Validates: Requirements 5.5
    // =========================================================================

    public function testRecordWithDecayedScoreJustBelowThresholdIsDeleted(): void
    {
        $tenant = $this->createTenant();
        $user   = $this->createAdminUser($tenant);

        // 0.19 * 0.5 = 0.095 < 0.1 — harus dihapus
        $memory = AiMemory::create([
            'tenant_id'        => $tenant->id,
            'user_id'          => $user->id,
            'key'              => 'default_warehouse',
            'value'            => 'gudang_utama',
            'frequency'        => 1,
            'last_seen_at'     => now()->subDays(95),
            'first_observed_at' => now()->subDays(100),
            'confidence_score' => 0.19,
        ]);

        $this->service->pruneStaleMemoriesForTenant($tenant->id);

        $result = AiMemory::withoutGlobalScopes()->find($memory->id);
        $this->assertNull($result, 'Record dengan decay score 0.095 < 0.1 harus dihapus');
    }
}
