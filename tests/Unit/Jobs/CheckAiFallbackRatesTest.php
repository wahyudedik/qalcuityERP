<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CheckAiFallbackRates;
use App\Models\AiProviderSwitchLog;
use App\Models\AiUsageCostLog;
use App\Models\User;
use App\Notifications\AiFallbackAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Test untuk CheckAiFallbackRates job.
 *
 * Requirements: 10.3
 */
class CheckAiFallbackRatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Cache::flush();
    }

    /** @test */
    public function test_it_sends_notification_when_fallback_rate_exceeds_threshold(): void
    {
        // Arrange
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $useCase = 'financial_report';

        // Buat 100 request, 25 fallback (25% > 20% threshold)
        for ($i = 0; $i < 100; $i++) {
            AiUsageCostLog::factory()->create([
                'use_case' => $useCase,
                'created_at' => now()->subMinutes(30),
            ]);
        }

        for ($i = 0; $i < 25; $i++) {
            AiProviderSwitchLog::factory()->create([
                'use_case' => $useCase,
                'created_at' => now()->subMinutes(30),
            ]);
        }

        // Act
        $job = new CheckAiFallbackRates;
        $job->handle();

        // Assert
        Notification::assertSentTo(
            $superAdmin,
            AiFallbackAlertNotification::class,
            function ($notification) use ($useCase) {
                return $notification->useCase === $useCase
                    && $notification->totalRequests === 100
                    && $notification->fallbackCount === 25
                    && $notification->fallbackPercent === 25.0;
            }
        );
    }

    /** @test */
    public function test_it_does_not_send_notification_when_below_threshold(): void
    {
        // Arrange
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $useCase = 'chatbot';

        // Buat 100 request, 10 fallback (10% < 20% threshold)
        for ($i = 0; $i < 100; $i++) {
            AiUsageCostLog::factory()->create([
                'use_case' => $useCase,
                'created_at' => now()->subMinutes(30),
            ]);
        }

        for ($i = 0; $i < 10; $i++) {
            AiProviderSwitchLog::factory()->create([
                'use_case' => $useCase,
                'created_at' => now()->subMinutes(30),
            ]);
        }

        // Act
        $job = new CheckAiFallbackRates;
        $job->handle();

        // Assert
        Notification::assertNotSentTo($superAdmin, AiFallbackAlertNotification::class);
    }

    /** @test */
    public function test_it_does_not_send_duplicate_notifications_for_same_hour(): void
    {
        // Arrange
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $useCase = 'forecasting';

        // Buat 100 request, 30 fallback (30% > 20% threshold)
        for ($i = 0; $i < 100; $i++) {
            AiUsageCostLog::factory()->create([
                'use_case' => $useCase,
                'created_at' => now()->subMinutes(30),
            ]);
        }

        for ($i = 0; $i < 30; $i++) {
            AiProviderSwitchLog::factory()->create([
                'use_case' => $useCase,
                'created_at' => now()->subMinutes(30),
            ]);
        }

        // Act - jalankan job dua kali
        $job1 = new CheckAiFallbackRates;
        $job1->handle();

        $job2 = new CheckAiFallbackRates;
        $job2->handle();

        // Assert - notifikasi hanya dikirim sekali
        Notification::assertSentToTimes($superAdmin, AiFallbackAlertNotification::class, 1);
    }

    /** @test */
    public function test_it_only_counts_last_hour_events(): void
    {
        // Arrange
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $useCase = 'audit_analysis';

        // Fallback 2 jam lalu (tidak dihitung)
        for ($i = 0; $i < 50; $i++) {
            AiUsageCostLog::factory()->create([
                'use_case' => $useCase,
                'created_at' => now()->subHours(2),
            ]);
            AiProviderSwitchLog::factory()->create([
                'use_case' => $useCase,
                'created_at' => now()->subHours(2),
            ]);
        }

        // Request 1 jam terakhir (di bawah threshold)
        for ($i = 0; $i < 100; $i++) {
            AiUsageCostLog::factory()->create([
                'use_case' => $useCase,
                'created_at' => now()->subMinutes(30),
            ]);
        }

        for ($i = 0; $i < 10; $i++) {
            AiProviderSwitchLog::factory()->create([
                'use_case' => $useCase,
                'created_at' => now()->subMinutes(30),
            ]);
        }

        // Act
        $job = new CheckAiFallbackRates;
        $job->handle();

        // Assert - tidak ada notifikasi karena 1 jam terakhir di bawah threshold
        Notification::assertNotSentTo($superAdmin, AiFallbackAlertNotification::class);
    }

    /** @test */
    public function test_it_skips_use_cases_with_no_requests(): void
    {
        // Arrange
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        // Tidak ada request sama sekali

        // Act
        $job = new CheckAiFallbackRates;
        $job->handle();

        // Assert - tidak ada notifikasi
        Notification::assertNothingSent();
    }

    /** @test */
    public function test_it_checks_all_registered_use_cases(): void
    {
        // Arrange
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        // Buat fallback tinggi untuk dua use case berbeda
        $useCases = ['chatbot', 'financial_report'];

        foreach ($useCases as $useCase) {
            for ($i = 0; $i < 100; $i++) {
                AiUsageCostLog::factory()->create([
                    'use_case' => $useCase,
                    'created_at' => now()->subMinutes(30),
                ]);
            }

            for ($i = 0; $i < 25; $i++) {
                AiProviderSwitchLog::factory()->create([
                    'use_case' => $useCase,
                    'created_at' => now()->subMinutes(30),
                ]);
            }
        }

        // Act
        $job = new CheckAiFallbackRates;
        $job->handle();

        // Assert - notifikasi dikirim untuk kedua use case
        Notification::assertSentToTimes($superAdmin, AiFallbackAlertNotification::class, 2);
    }
}
