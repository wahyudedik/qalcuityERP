<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CheckAiCostThresholds;
use App\Models\AiUsageCostLog;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\AiCostThresholdExceededNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Test untuk CheckAiCostThresholds job.
 *
 * Requirements: 6.10
 */
class CheckAiCostThresholdsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Cache::flush();
    }

    /** @test */
    public function test_it_sends_notification_when_tenant_exceeds_cost_threshold(): void
    {
        // Arrange
        Config::set('ai.cost_threshold_idr', 1000);

        $tenant = Tenant::factory()->create(['is_active' => true]);
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        // Buat cost logs yang melebihi threshold
        AiUsageCostLog::factory()->create([
            'tenant_id' => $tenant->id,
            'estimated_cost_idr' => 600,
            'created_at' => now()->startOfMonth(),
        ]);

        AiUsageCostLog::factory()->create([
            'tenant_id' => $tenant->id,
            'estimated_cost_idr' => 500,
            'created_at' => now(),
        ]);

        // Act
        $job = new CheckAiCostThresholds();
        $job->handle();

        // Assert
        Notification::assertSentTo(
            $superAdmin,
            AiCostThresholdExceededNotification::class,
            function ($notification) use ($tenant) {
                return $notification->tenantId === $tenant->id
                    && $notification->totalCost === 1100.0
                    && $notification->threshold === 1000.0;
            }
        );
    }

    /** @test */
    public function test_it_does_not_send_notification_when_below_threshold(): void
    {
        // Arrange
        Config::set('ai.cost_threshold_idr', 1000);

        $tenant = Tenant::factory()->create(['is_active' => true]);
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        // Buat cost logs di bawah threshold
        AiUsageCostLog::factory()->create([
            'tenant_id' => $tenant->id,
            'estimated_cost_idr' => 500,
            'created_at' => now(),
        ]);

        // Act
        $job = new CheckAiCostThresholds();
        $job->handle();

        // Assert
        Notification::assertNotSentTo($superAdmin, AiCostThresholdExceededNotification::class);
    }

    /** @test */
    public function test_it_does_not_send_duplicate_notifications_for_same_period(): void
    {
        // Arrange
        Config::set('ai.cost_threshold_idr', 1000);

        $tenant = Tenant::factory()->create(['is_active' => true]);
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        AiUsageCostLog::factory()->create([
            'tenant_id' => $tenant->id,
            'estimated_cost_idr' => 1500,
            'created_at' => now(),
        ]);

        // Act - jalankan job dua kali
        $job1 = new CheckAiCostThresholds();
        $job1->handle();

        $job2 = new CheckAiCostThresholds();
        $job2->handle();

        // Assert - notifikasi hanya dikirim sekali
        Notification::assertSentToTimes($superAdmin, AiCostThresholdExceededNotification::class, 1);
    }

    /** @test */
    public function test_it_only_counts_current_month_costs(): void
    {
        // Arrange
        Config::set('ai.cost_threshold_idr', 1000);

        $tenant = Tenant::factory()->create(['is_active' => true]);
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        // Cost log bulan lalu (tidak dihitung)
        AiUsageCostLog::factory()->create([
            'tenant_id' => $tenant->id,
            'estimated_cost_idr' => 2000,
            'created_at' => now()->subMonth(),
        ]);

        // Cost log bulan ini (di bawah threshold)
        AiUsageCostLog::factory()->create([
            'tenant_id' => $tenant->id,
            'estimated_cost_idr' => 500,
            'created_at' => now(),
        ]);

        // Act
        $job = new CheckAiCostThresholds();
        $job->handle();

        // Assert - tidak ada notifikasi karena bulan ini di bawah threshold
        Notification::assertNotSentTo($superAdmin, AiCostThresholdExceededNotification::class);
    }

    /** @test */
    public function test_it_skips_inactive_tenants(): void
    {
        // Arrange
        Config::set('ai.cost_threshold_idr', 1000);

        $tenant = Tenant::factory()->create(['is_active' => false]);
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        AiUsageCostLog::factory()->create([
            'tenant_id' => $tenant->id,
            'estimated_cost_idr' => 1500,
            'created_at' => now(),
        ]);

        // Act
        $job = new CheckAiCostThresholds();
        $job->handle();

        // Assert
        Notification::assertNotSentTo($superAdmin, AiCostThresholdExceededNotification::class);
    }
}
