<?php

namespace Tests\Unit\Models;

use App\Models\Tenant;
use App\Models\User;
use App\Models\WidgetDataCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WidgetDataCacheTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat tenant dan user untuk testing
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_can_create_widget_data_cache(): void
    {
        $cache = WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'test_cache_key',
            'data' => ['count' => 10, 'status' => 'active'],
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertDatabaseHas('widget_data_cache', [
            'widget_type' => 'summary',
            'cache_key' => 'test_cache_key',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals(['count' => 10, 'status' => 'active'], $cache->data);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $cache->expires_at);
    }

    public function test_is_expired_returns_true_for_past_expiration(): void
    {
        $cache = WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'expired_cache',
            'data' => ['test' => 'data'],
            'expires_at' => now()->subMinutes(5),
        ]);

        $this->assertTrue($cache->isExpired());
        $this->assertFalse($cache->isValid());
    }

    public function test_is_expired_returns_false_for_future_expiration(): void
    {
        $cache = WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'valid_cache',
            'data' => ['test' => 'data'],
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertFalse($cache->isExpired());
        $this->assertTrue($cache->isValid());
    }

    public function test_is_expired_returns_false_for_null_expiration(): void
    {
        $cache = WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'permanent_cache',
            'data' => ['test' => 'data'],
            'expires_at' => null,
        ]);

        $this->assertFalse($cache->isExpired());
        $this->assertTrue($cache->isValid());
    }

    public function test_valid_scope_filters_expired_cache(): void
    {
        // Buat cache yang valid
        WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'valid_1',
            'data' => ['test' => 'data'],
            'expires_at' => now()->addMinutes(5),
        ]);

        // Buat cache yang expired
        WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'expired_1',
            'data' => ['test' => 'data'],
            'expires_at' => now()->subMinutes(5),
        ]);

        // Buat cache tanpa expiration
        WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'permanent_1',
            'data' => ['test' => 'data'],
            'expires_at' => null,
        ]);

        $validCaches = WidgetDataCache::valid()->get();

        $this->assertCount(2, $validCaches);
        $this->assertTrue($validCaches->pluck('cache_key')->contains('valid_1'));
        $this->assertTrue($validCaches->pluck('cache_key')->contains('permanent_1'));
        $this->assertFalse($validCaches->pluck('cache_key')->contains('expired_1'));
    }

    public function test_expired_scope_filters_valid_cache(): void
    {
        // Buat cache yang valid
        WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'valid_1',
            'data' => ['test' => 'data'],
            'expires_at' => now()->addMinutes(5),
        ]);

        // Buat cache yang expired
        WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'expired_1',
            'data' => ['test' => 'data'],
            'expires_at' => now()->subMinutes(5),
        ]);

        $expiredCaches = WidgetDataCache::expired()->get();

        $this->assertCount(1, $expiredCaches);
        $this->assertEquals('expired_1', $expiredCaches->first()->cache_key);
    }

    public function test_get_valid_cache_returns_data_for_valid_cache(): void
    {
        WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'test_key',
            'data' => ['count' => 42, 'status' => 'success'],
            'expires_at' => now()->addMinutes(5),
        ]);

        $data = WidgetDataCache::getValidCache('test_key');

        $this->assertNotNull($data);
        $this->assertEquals(['count' => 42, 'status' => 'success'], $data);
    }

    public function test_get_valid_cache_returns_null_for_expired_cache(): void
    {
        WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'expired_key',
            'data' => ['count' => 42],
            'expires_at' => now()->subMinutes(5),
        ]);

        $data = WidgetDataCache::getValidCache('expired_key');

        $this->assertNull($data);
    }

    public function test_put_cache_creates_new_cache(): void
    {
        $cache = WidgetDataCache::putCache(
            'summary',
            'new_cache_key',
            ['count' => 100],
            now()->addMinutes(10)
        );

        $this->assertDatabaseHas('widget_data_cache', [
            'widget_type' => 'summary',
            'cache_key' => 'new_cache_key',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertEquals(['count' => 100], $cache->data);
    }

    public function test_put_cache_updates_existing_cache(): void
    {
        // Buat cache awal
        WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'update_key',
            'data' => ['count' => 10],
            'expires_at' => now()->addMinutes(5),
        ]);

        // Update cache
        $cache = WidgetDataCache::putCache(
            'chart',
            'update_key',
            ['count' => 20],
            now()->addMinutes(15)
        );

        $this->assertEquals(['count' => 20], $cache->data);
        $this->assertEquals('chart', $cache->widget_type);

        // Pastikan hanya ada satu record
        $this->assertEquals(1, WidgetDataCache::where('cache_key', 'update_key')->count());
    }

    public function test_clear_expired_cache_removes_only_expired(): void
    {
        // Buat cache yang valid
        WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'valid_1',
            'data' => ['test' => 'data'],
            'expires_at' => now()->addMinutes(5),
        ]);

        // Buat cache yang expired
        WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'expired_1',
            'data' => ['test' => 'data'],
            'expires_at' => now()->subMinutes(5),
        ]);

        WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'expired_2',
            'data' => ['test' => 'data'],
            'expires_at' => now()->subMinutes(10),
        ]);

        $deletedCount = WidgetDataCache::clearExpiredCache();

        $this->assertEquals(2, $deletedCount);
        $this->assertEquals(1, WidgetDataCache::count());
        $this->assertDatabaseHas('widget_data_cache', ['cache_key' => 'valid_1']);
    }

    public function test_clear_widget_type_cache_removes_specific_type(): void
    {
        WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'summary_1',
            'data' => ['test' => 'data'],
        ]);

        WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'summary_2',
            'data' => ['test' => 'data'],
        ]);

        WidgetDataCache::create([
            'widget_type' => 'chart',
            'cache_key' => 'chart_1',
            'data' => ['test' => 'data'],
        ]);

        $deletedCount = WidgetDataCache::clearWidgetTypeCache('summary');

        $this->assertEquals(2, $deletedCount);
        $this->assertEquals(1, WidgetDataCache::count());
        $this->assertDatabaseHas('widget_data_cache', ['widget_type' => 'chart']);
    }

    public function test_belongs_to_tenant_trait_auto_sets_tenant_id(): void
    {
        $cache = WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'auto_tenant',
            'data' => ['test' => 'data'],
        ]);

        $this->assertEquals($this->tenant->id, $cache->tenant_id);
    }

    public function test_belongs_to_tenant_trait_filters_by_tenant(): void
    {
        // Buat tenant lain
        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);

        // Buat cache untuk tenant saat ini
        WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'tenant_1_cache',
            'data' => ['test' => 'data'],
        ]);

        // Login sebagai user dari tenant lain dan buat cache
        $this->actingAs($otherUser);
        WidgetDataCache::create([
            'widget_type' => 'summary',
            'cache_key' => 'tenant_2_cache',
            'data' => ['test' => 'data'],
        ]);

        // Verifikasi hanya melihat cache dari tenant sendiri
        $caches = WidgetDataCache::all();
        $this->assertCount(1, $caches);
        $this->assertEquals('tenant_2_cache', $caches->first()->cache_key);
    }
}
