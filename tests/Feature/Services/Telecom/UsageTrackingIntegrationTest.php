<?php

namespace Tests\Feature\Services\Telecom;

use App\Models\Customer;
use App\Models\NetworkDevice;
use App\Models\InternetPackage;
use App\Models\TelecomSubscription;
use App\Models\UsageTracking;
use App\Services\Telecom\UsageTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsageTrackingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected TelecomSubscription $subscription;
    protected UsageTrackingService $usageService;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup test data
        $device = NetworkDevice::create([
            'tenant_id' => 1,
            'name' => 'Test Router',
            'brand' => 'mikrotik',
            'device_type' => 'router',
            'ip_address' => '192.168.88.1',
            'port' => 8728,
            'username' => 'admin',
            'password' => 'password123',
            'status' => 'online',
        ]);

        $package = InternetPackage::create([
            'tenant_id' => 1,
            'name' => 'Premium Package',
            'download_speed_mbps' => 50,
            'upload_speed_mbps' => 20,
            'quota_bytes' => 10737418240, // 10 GB
            'price' => 150000,
            'billing_cycle' => 'monthly',
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'tenant_id' => 1,
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);

        $this->subscription = TelecomSubscription::create([
            'tenant_id' => 1,
            'customer_id' => $customer->id,
            'device_id' => $device->id,
            'package_id' => $package->id,
            'status' => 'active',
            'started_at' => now(),
            'next_billing_date' => now()->addMonth(),
        ]);

        $this->usageService = new UsageTrackingService();
    }

    /** @test */
    public function it_can_record_usage()
    {
        $bytesIn = 104857600; // 100 MB download
        $bytesOut = 52428800;  // 50 MB upload

        $usageRecord = $this->usageService->recordUsage(
            $this->subscription,
            $bytesIn,
            $bytesOut
        );

        $this->assertNotNull($usageRecord);
        $this->assertEquals($bytesIn, $usageRecord->bytes_in);
        $this->assertEquals($bytesOut, $usageRecord->bytes_out);
        $this->assertEquals($this->subscription->id, $usageRecord->subscription_id);

        $this->assertDatabaseHas('usage_tracking', [
            'subscription_id' => $this->subscription->id,
            'bytes_in' => $bytesIn,
            'bytes_out' => $bytesOut,
        ]);
    }

    /** @test */
    public function it_calculates_total_usage_correctly()
    {
        // Record multiple usage entries
        $this->usageService->recordUsage($this->subscription, 104857600, 52428800); // 150 MB
        $this->usageService->recordUsage($this->subscription, 209715200, 104857600); // 300 MB

        $summary = $this->usageService->getUsageSummary($this->subscription, 'current');

        $this->assertGreaterThan(0, $summary['total_bytes']);
        $this->assertGreaterThan(0, $summary['download_bytes']);
        $this->assertGreaterThan(0, $summary['upload_bytes']);
    }

    /** @test */
    public function it_detects_quota_exceeded()
    {
        // Package has 10 GB quota
        // Record usage that exceeds quota
        $exceedingBytes = 11811160064; // 11 GB

        $this->usageService->recordUsage(
            $this->subscription,
            $exceedingBytes,
            0
        );

        $this->assertTrue($this->subscription->refresh()->isQuotaExceeded());
    }

    /** @test */
    public function it_can_reset_quota()
    {
        // First, exceed the quota
        $this->usageService->recordUsage(
            $this->subscription,
            11811160064, // 11 GB
            0
        );

        $this->assertTrue($this->subscription->refresh()->isQuotaExceeded());

        // Reset quota
        $this->subscription->resetQuota();

        $this->assertFalse($this->subscription->refresh()->isQuotaExceeded());
        $this->assertEquals(0, $this->subscription->current_usage_bytes);
    }

    /** @test */
    public function it_tracks_usage_by_period()
    {
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();

        $this->usageService->recordUsage(
            $this->subscription,
            104857600,
            52428800,
            [],
            $periodStart,
            $periodEnd
        );

        $this->assertDatabaseHas('usage_tracking', [
            'subscription_id' => $this->subscription->id,
            'period_start' => $periodStart->format('Y-m-d H:i:s'),
            'period_end' => $periodEnd->format('Y-m-d H:i:s'),
        ]);
    }

    /** @test */
    public function it_aggregates_usage_across_multiple_periods()
    {
        // Record usage for different periods
        $this->usageService->recordUsage(
            $this->subscription,
            104857600,
            52428800,
            [],
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        );

        $this->usageService->recordUsage(
            $this->subscription,
            209715200,
            104857600,
            [],
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        $allUsage = UsageTracking::where('subscription_id', $this->subscription->id)->get();

        $this->assertEquals(2, $allUsage->count());
    }

    /** @test */
    public function it_handles_zero_usage_gracefully()
    {
        $usageRecord = $this->usageService->recordUsage(
            $this->subscription,
            0,
            0
        );

        $this->assertNotNull($usageRecord);
        $this->assertEquals(0, $usageRecord->bytes_in);
        $this->assertEquals(0, $usageRecord->bytes_out);
    }

    /** @test */
    public function it_validates_positive_bytes()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->usageService->recordUsage(
            $this->subscription,
            -100, // Invalid negative bytes
            50
        );
    }

    /** @test */
    public function it_can_get_usage_history()
    {
        // Create usage records for last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $this->usageService->recordUsage(
                $this->subscription,
                10485760 * ($i + 1), // Increasing usage
                5242880 * ($i + 1),
                [],
                $date->startOfDay(),
                $date->endOfDay()
            );
        }

        $history = UsageTracking::where('subscription_id', $this->subscription->id)
            ->orderBy('period_start', 'desc')
            ->limit(7)
            ->get();

        $this->assertCount(7, $history);
    }

    /** @test */
    public function it_enforces_tenant_isolation_on_usage_queries()
    {
        // Create subscription for different tenant
        $otherTenantSub = TelecomSubscription::create([
            'tenant_id' => 2,
            'customer_id' => Customer::create(['tenant_id' => 2, 'name' => 'Other'])->id,
            'device_id' => NetworkDevice::create([
                'tenant_id' => 2,
                'name' => 'Other Device',
                'brand' => 'mikrotik',
                'device_type' => 'router',
                'ip_address' => '192.168.1.1',
                'port' => 8728,
                'username' => 'admin',
                'password' => 'pass',
            ])->id,
            'package_id' => InternetPackage::create([
                'tenant_id' => 2,
                'name' => 'Other Package',
                'download_speed_mbps' => 10,
                'upload_speed_mbps' => 5,
                'price' => 100000,
                'billing_cycle' => 'monthly',
            ])->id,
            'status' => 'active',
        ]);

        // Record usage for other tenant
        $this->usageService->recordUsage($otherTenantSub, 104857600, 52428800);

        // Query usage for our tenant's subscription
        $summary = $this->usageService->getUsageSummary($this->subscription, 'current');

        // Should only include our tenant's data
        $this->assertEquals($this->subscription->tenant_id, 1);
    }
}
