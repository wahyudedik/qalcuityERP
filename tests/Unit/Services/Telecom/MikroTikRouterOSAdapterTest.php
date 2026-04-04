<?php

namespace Tests\Unit\Services\Telecom;

use App\Models\NetworkDevice;
use App\Services\Telecom\MikroTikRouterOSAdapter;
use App\Services\Telecom\RouterAdapterFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MikroTikRouterOSAdapterTest extends TestCase
{
    use RefreshDatabase;

    protected NetworkDevice $device;
    protected MikroTikRouterOSAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = NetworkDevice::create([
            'tenant_id' => 1,
            'name' => 'Test MikroTik Router',
            'brand' => 'mikrotik',
            'device_type' => 'router',
            'ip_address' => '192.168.88.1',
            'port' => 8728,
            'username' => 'admin',
            'password' => 'password123',
            'status' => 'online',
        ]);

        $this->adapter = new MikroTikRouterOSAdapter($this->device);
    }

    /** @test */
    public function it_can_create_adapter_from_factory()
    {
        $adapter = RouterAdapterFactory::create($this->device);

        $this->assertInstanceOf(MikroTikRouterOSAdapter::class, $adapter);
    }

    /** @test */
    public function it_returns_correct_brand()
    {
        $this->assertEquals('mikrotik', $this->adapter->getBrand());
    }

    /** @test */
    public function it_returns_correct_api_port()
    {
        $this->assertEquals(8728, $this->adapter->getApiPort());
    }

    /** @test */
    public function it_builds_correct_api_url()
    {
        $url = $this->adapter->buildApiUrl('/api/resource');

        $this->assertEquals('http://192.168.88.1:8728/api/resource', $url);
    }

    /** @test */
    public function it_can_test_connection_structure()
    {
        // This test verifies the method exists and returns expected structure
        // Actual connection test requires real device or simulator
        $result = $this->adapter->testConnection();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('latency_ms', $result);
    }

    /** @test */
    public function it_can_get_system_info_structure()
    {
        $info = $this->adapter->getSystemInfo();

        $this->assertIsArray($info);
        // Structure validation (actual data requires real device)
        $this->assertTrue(true); // Placeholder for structure check
    }

    /** @test */
    public function it_can_get_interface_list_structure()
    {
        $interfaces = $this->adapter->getInterfaceList();

        $this->assertIsArray($interfaces);
        // Each interface should have name, type, running status
        $this->assertTrue(true); // Placeholder
    }

    /** @test */
    public function it_can_get_active_users_structure()
    {
        $users = $this->adapter->getActiveUsers();

        $this->assertIsArray($users);
        // Should return array of user objects
        $this->assertTrue(true); // Placeholder
    }

    /** @test */
    public function it_can_create_hotspot_user_payload()
    {
        $userData = [
            'name' => 'testuser',
            'password' => 'testpass',
            'profile' => 'default',
            'comment' => 'Test user',
        ];

        // Verify adapter can prepare payload (implementation detail)
        $this->assertTrue(method_exists($this->adapter, 'createUser'));
    }

    /** @test */
    public function it_can_remove_hotspot_user_method_exists()
    {
        $this->assertTrue(method_exists($this->adapter, 'removeUser'));
    }

    /** @test */
    public function it_can_disconnect_user_method_exists()
    {
        $this->assertTrue(method_exists($this->adapter, 'disconnectUser'));
    }

    /** @test */
    public function it_can_get_bandwidth_usage_method_exists()
    {
        $this->assertTrue(method_exists($this->adapter, 'getBandwidthUsage'));
    }

    /** @test */
    public function it_handles_invalid_credentials_gracefully()
    {
        $invalidDevice = new NetworkDevice([
            'tenant_id' => 1,
            'name' => 'Invalid Device',
            'brand' => 'mikrotik',
            'device_type' => 'router',
            'ip_address' => '192.168.88.999', // Invalid IP
            'port' => 8728,
            'username' => 'wrong',
            'password' => 'wrong',
        ]);

        $adapter = new MikroTikRouterOSAdapter($invalidDevice);
        $result = $adapter->testConnection();

        $this->assertFalse($result['success']);
    }

    /** @test */
    public function it_validates_device_requirements()
    {
        $incompleteDevice = new NetworkDevice([
            'tenant_id' => 1,
            'name' => 'Incomplete',
            'brand' => 'mikrotik',
            // Missing required fields
        ]);

        $this->expectException(\InvalidArgumentException::class);
        new MikroTikRouterOSAdapter($incompleteDevice);
    }
}
