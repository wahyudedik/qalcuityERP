<?php

namespace Tests\Feature\Api\Telecom;

use App\Models\User;
use App\Models\NetworkDevice;
use App\Models\InternetPackage;
use App\Models\Customer;
use App\Models\TelecomSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TelecomApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $apiToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'tenant_id' => 1,
        ]);

        Sanctum::actingAs($this->user, ['*']);
    }

    /** @test */
    public function it_can_register_new_device()
    {
        $deviceData = [
            'name' => 'Test Router',
            'brand' => 'mikrotik',
            'device_type' => 'router',
            'ip_address' => '192.168.88.1',
            'port' => 8728,
            'username' => 'admin',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/telecom/devices', $deviceData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'brand',
                    'device_type',
                    'ip_address',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('network_devices', [
            'name' => 'Test Router',
            'brand' => 'mikrotik',
            'tenant_id' => 1,
        ]);
    }

    /** @test */
    public function it_can_get_device_status()
    {
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

        $response = $this->getJson("/api/telecom/devices/{$device->id}/status");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'device_id',
                    'status',
                    'last_seen_at',
                ],
            ]);
    }

    /** @test */
    public function it_can_create_hotspot_user()
    {
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

        $customer = Customer::create([
            'tenant_id' => 1,
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);

        $userData = [
            'device_id' => $device->id,
            'customer_id' => $customer->id,
            'username' => 'testuser',
            'password' => 'testpass',
            'profile' => 'default',
        ];

        $response = $this->postJson('/api/telecom/hotspot/users', $userData);

        // Should return success or validation error (depends on router connection)
        $response->assertStatus(201);
    }

    /** @test */
    public function it_can_get_customer_usage()
    {
        $customer = Customer::create([
            'tenant_id' => 1,
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);

        $response = $this->getJson("/api/telecom/usage/{$customer->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'customer_id',
                    'current_period',
                    'usage_bytes',
                    'quota_bytes',
                ],
            ]);
    }

    /** @test */
    public function it_can_generate_voucher()
    {
        $package = InternetPackage::create([
            'tenant_id' => 1,
            'name' => 'Test Package',
            'download_speed_mbps' => 10,
            'upload_speed_mbps' => 5,
            'price' => 100000,
            'billing_cycle' => 'monthly',
            'is_active' => true,
        ]);

        $voucherData = [
            'package_id' => $package->id,
            'quantity' => 1,
            'validity_hours' => 24,
        ];

        $response = $this->postJson('/api/telecom/vouchers/generate', $voucherData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'code',
                    'package_id',
                    'valid_until',
                ],
            ]);

        $this->assertDatabaseHas('voucher_codes', [
            'package_id' => $package->id,
            'status' => 'unused',
        ]);
    }

    /** @test */
    public function it_requires_authentication_for_protected_endpoints()
    {
        Sanctum::logout();

        $response = $this->getJson('/api/telecom/devices');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_device_creation_input()
    {
        $invalidData = [
            'name' => '', // Required
            'brand' => 'invalid_brand', // Must be in allowed list
            'ip_address' => 'not-an-ip', // Must be valid IP
        ];

        $response = $this->postJson('/api/telecom/devices', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'brand', 'ip_address']);
    }

    /** @test */
    public function it_enforces_tenant_isolation()
    {
        // Create device for tenant 2
        $otherTenantDevice = NetworkDevice::create([
            'tenant_id' => 2,
            'name' => 'Other Tenant Device',
            'brand' => 'mikrotik',
            'device_type' => 'router',
            'ip_address' => '192.168.1.1',
            'port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'online',
        ]);

        // Try to access from tenant 1
        $response = $this->getJson("/api/telecom/devices/{$otherTenantDevice->id}/status");

        // Should return 404 (not found) or 403 (forbidden)
        $response->assertStatus(fn($status) => in_array($status, [403, 404]));
    }

    /** @test */
    public function it_can_list_all_devices()
    {
        NetworkDevice::create([
            'tenant_id' => 1,
            'name' => 'Router 1',
            'brand' => 'mikrotik',
            'device_type' => 'router',
            'ip_address' => '192.168.88.1',
            'port' => 8728,
            'username' => 'admin',
            'password' => 'password123',
            'status' => 'online',
        ]);

        $response = $this->getJson('/api/telecom/devices');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'brand',
                        'status',
                    ],
                ],
            ]);
    }
}
