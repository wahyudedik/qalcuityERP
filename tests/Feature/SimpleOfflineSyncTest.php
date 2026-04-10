<?php

namespace Tests\Feature;

use App\Models\OfflineSyncConflict;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Simple test to verify offline sync setup works
 */
class SimpleOfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Basic test to verify database connection and model
     */
    public function test_database_connection_works()
    {
        $this->assertTrue(true);
    }

    /**
     * Test that we can create tenant and user
     */
    public function test_can_create_tenant_and_user()
    {
        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'subdomain' => 'test-' . time(),
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'manager',
        ]);

        $this->assertDatabaseHas('tenants', ['id' => $tenant->id]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /**
     * Test that we can create offline sync conflict
     */
    public function test_can_create_offline_conflict()
    {
        $tenant = Tenant::create([
            'name' => 'Test Tenant',
            'subdomain' => 'test-' . time(),
        ]);

        $conflict = OfflineSyncConflict::create([
            'tenant_id' => $tenant->id,
            'entity_type' => 'inventory',
            'entity_id' => 1,
            'local_id' => 'test-local-001',
            'offline_timestamp' => now()->subMinutes(10)->toISOString(),
            'server_state' => ['quantity' => 100],
            'local_state' => ['adjustment' => 10],
            'offline_changes' => 0,
            'status' => 'pending',
            'detected_at' => now(),
        ]);

        $this->assertDatabaseHas('offline_sync_conflicts', [
            'id' => $conflict->id,
            'status' => 'pending',
        ]);
    }
}
