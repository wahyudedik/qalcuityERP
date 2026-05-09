<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\UserWidgetPreference;
use App\Services\Widget\WidgetManagerService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Unit Tests for WidgetManagerService — widget lifecycle management.
 *
 * Feature: ui-ux-optimization
 * Validates: Requirements 7 (Widget Management System), 8 (Performance & Caching)
 */
class WidgetManagerServiceTest extends TestCase
{
    private WidgetManagerService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(WidgetManagerService::class);

        $tenant = $this->createTenant();
        $this->user = $this->createAdminUser($tenant);

        // Authenticate as the user so BelongsToTenant trait can auto-set tenant_id
        $this->actingAs($this->user);
    }

    // ── 2.1.1 getAvailableWidgets ─────────────────────────────────

    /**
     * Test 2.1.1: getAvailableWidgets returns correct widgets for each supported page.
     * Validates: Requirement 7.3 (widget library per page)
     */
    public function test_get_available_widgets_returns_correct_widgets_for_notifications(): void
    {
        $widgets = $this->service->getAvailableWidgets('notifications');

        $this->assertCount(4, $widgets);
        $types = $widgets->pluck('type')->toArray();
        $this->assertContains('summary', $types);
        $this->assertContains('quick-actions', $types);
        $this->assertContains('chart-trends', $types);
        $this->assertContains('recent-items', $types);
    }

    /**
     * Test 2.1.1: getAvailableWidgets filters correctly for room-availability page.
     * Validates: Requirement 7.5 (widget compatibility with page)
     */
    public function test_get_available_widgets_returns_correct_widgets_for_room_availability(): void
    {
        $widgets = $this->service->getAvailableWidgets('room-availability');

        $types = $widgets->pluck('type')->toArray();
        $this->assertContains('room-summary', $types);
        $this->assertContains('chart-occupancy', $types);
        $this->assertContains('maintenance-schedule', $types);
        $this->assertNotContains('summary', $types, 'notifications-only widget must not appear here');
    }

    /**
     * Test 2.1.1: getAvailableWidgets returns empty collection for unknown page.
     */
    public function test_get_available_widgets_returns_empty_for_unknown_page(): void
    {
        $widgets = $this->service->getAvailableWidgets('unknown-page');

        $this->assertTrue($widgets->isEmpty());
    }

    /**
     * Test 2.1.1: Each widget entry contains required keys.
     */
    public function test_get_available_widgets_entries_have_required_keys(): void
    {
        $widgets = $this->service->getAvailableWidgets('reports');

        foreach ($widgets as $widget) {
            $this->assertArrayHasKey('type', $widget);
            $this->assertArrayHasKey('page', $widget);
            $this->assertArrayHasKey('default_config', $widget);
            $this->assertArrayHasKey('label', $widget);
            $this->assertEquals('reports', $widget['page']);
        }
    }

    // ── 2.1.2 getUserWidgets ──────────────────────────────────────

    /**
     * Test 2.1.2: getUserWidgets falls back to defaults when user has no preferences.
     * Validates: Requirement 7.2 (save widget preferences per user)
     */
    public function test_get_user_widgets_creates_defaults_when_no_preferences_exist(): void
    {
        $widgets = $this->service->getUserWidgets($this->user, 'notifications');

        $this->assertNotEmpty($widgets);
        $this->assertCount(4, $widgets); // 4 default widgets for notifications
    }

    /**
     * Test 2.1.2: getUserWidgets returns saved preferences when they exist.
     */
    public function test_get_user_widgets_returns_saved_preferences(): void
    {
        // Create a specific widget preference
        UserWidgetPreference::create([
            'user_id' => $this->user->id,
            'page' => 'reports',
            'widget_type' => 'report-stats',
            'widget_config' => ['show_frequency' => true],
            'position' => 0,
            'is_active' => true,
        ]);

        $widgets = $this->service->getUserWidgets($this->user, 'reports');

        $this->assertCount(1, $widgets);
        $this->assertEquals('report-stats', $widgets->first()->widget_type);
    }

    /**
     * Test 2.1.2: getUserWidgets only returns active widgets.
     */
    public function test_get_user_widgets_only_returns_active_widgets(): void
    {
        UserWidgetPreference::create([
            'user_id' => $this->user->id,
            'page' => 'anomalies',
            'widget_type' => 'anomaly-stats',
            'widget_config' => [],
            'position' => 0,
            'is_active' => true,
        ]);

        UserWidgetPreference::create([
            'user_id' => $this->user->id,
            'page' => 'anomalies',
            'widget_type' => 'quick-actions',
            'widget_config' => [],
            'position' => 1,
            'is_active' => false, // inactive
        ]);

        $widgets = $this->service->getUserWidgets($this->user, 'anomalies');

        $this->assertCount(1, $widgets);
        $this->assertEquals('anomaly-stats', $widgets->first()->widget_type);
    }

    /**
     * Test 2.1.2: getUserWidgets returns widgets ordered by position.
     */
    public function test_get_user_widgets_returns_widgets_ordered_by_position(): void
    {
        UserWidgetPreference::create([
            'user_id' => $this->user->id,
            'page' => 'simulations',
            'widget_type' => 'templates',
            'widget_config' => [],
            'position' => 2,
            'is_active' => true,
        ]);

        UserWidgetPreference::create([
            'user_id' => $this->user->id,
            'page' => 'simulations',
            'widget_type' => 'simulation-history',
            'widget_config' => [],
            'position' => 0,
            'is_active' => true,
        ]);

        $widgets = $this->service->getUserWidgets($this->user, 'simulations');

        $this->assertEquals('simulation-history', $widgets->first()->widget_type);
        $this->assertEquals('templates', $widgets->last()->widget_type);
    }

    // ── 2.1.3 addWidget ──────────────────────────────────────────

    /**
     * Test 2.1.3: addWidget creates a new widget preference.
     * Validates: Requirement 7.1 (add widget interface)
     */
    public function test_add_widget_creates_new_widget_preference(): void
    {
        $widget = $this->service->addWidget($this->user, 'notifications', 'summary');

        $this->assertInstanceOf(UserWidgetPreference::class, $widget);
        $this->assertEquals('summary', $widget->widget_type);
        $this->assertEquals('notifications', $widget->page);
        $this->assertEquals($this->user->id, $widget->user_id);
        $this->assertTrue($widget->is_active);
    }

    /**
     * Test 2.1.3: addWidget merges provided config with defaults.
     */
    public function test_add_widget_merges_config_with_defaults(): void
    {
        $widget = $this->service->addWidget(
            $this->user,
            'notifications',
            'chart-trends',
            ['period_days' => 14]
        );

        $this->assertEquals(14, $widget->widget_config['period_days']);
        $this->assertEquals('line', $widget->widget_config['chart_type']); // from default
    }

    /**
     * Test 2.1.3: addWidget throws InvalidArgumentException for unknown page.
     * Validates: Requirement 7.5 (validate widget compatibility)
     */
    public function test_add_widget_throws_for_unknown_page(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/tidak didukung/');

        $this->service->addWidget($this->user, 'unknown-page', 'summary');
    }

    /**
     * Test 2.1.3: addWidget throws InvalidArgumentException for incompatible widget type.
     * Validates: Requirement 7.5 (validate widget compatibility)
     */
    public function test_add_widget_throws_for_incompatible_widget_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/tidak tersedia/');

        // 'room-summary' is only for room-availability, not notifications
        $this->service->addWidget($this->user, 'notifications', 'room-summary');
    }

    /**
     * Test 2.1.3: addWidget reactivates an existing inactive widget instead of duplicating.
     */
    public function test_add_widget_reactivates_inactive_widget(): void
    {
        $existing = UserWidgetPreference::create([
            'user_id' => $this->user->id,
            'page' => 'reports',
            'widget_type' => 'favorites',
            'widget_config' => [],
            'position' => 0,
            'is_active' => false,
        ]);

        $widget = $this->service->addWidget($this->user, 'reports', 'favorites');

        $this->assertEquals($existing->id, $widget->id);
        $this->assertTrue($widget->fresh()->is_active);

        // Ensure no duplicate was created
        $count = UserWidgetPreference::where('user_id', $this->user->id)
            ->where('page', 'reports')
            ->where('widget_type', 'favorites')
            ->count();
        $this->assertEquals(1, $count);
    }

    /**
     * Test 2.1.3: addWidget assigns sequential positions.
     */
    public function test_add_widget_assigns_sequential_positions(): void
    {
        $first = $this->service->addWidget($this->user, 'anomalies', 'anomaly-stats');
        $second = $this->service->addWidget($this->user, 'anomalies', 'quick-actions');

        $this->assertEquals(0, $first->position);
        $this->assertEquals(1, $second->position);
    }

    // ── 2.1.4 removeWidget ────────────────────────────────────────

    /**
     * Test 2.1.4: removeWidget deletes the widget and returns true.
     * Validates: Requirement 7.1 (remove widget interface)
     */
    public function test_remove_widget_deletes_widget_and_returns_true(): void
    {
        $widget = $this->service->addWidget($this->user, 'notifications', 'summary');

        $result = $this->service->removeWidget($this->user, $widget->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('user_widget_preferences', ['id' => $widget->id]);
    }

    /**
     * Test 2.1.4: removeWidget returns false when widget not found.
     */
    public function test_remove_widget_returns_false_when_not_found(): void
    {
        $result = $this->service->removeWidget($this->user, 999999);

        $this->assertFalse($result);
    }

    /**
     * Test 2.1.4: removeWidget clears related cache.
     * Validates: Requirement 8.5 (caching for widget data)
     */
    public function test_remove_widget_clears_related_cache(): void
    {
        $widget = $this->service->addWidget($this->user, 'notifications', 'summary');
        $cacheKey = "widget_data.{$this->user->tenant_id}.{$widget->id}.summary";

        // Seed the cache
        Cache::put($cacheKey, ['cached' => true], 300);
        $this->assertTrue(Cache::has($cacheKey));

        $this->service->removeWidget($this->user, $widget->id);

        $this->assertFalse(Cache::has($cacheKey), 'Cache must be cleared after widget removal');
    }

    /**
     * Test 2.1.4: removeWidget cannot remove another user's widget.
     */
    public function test_remove_widget_cannot_remove_another_users_widget(): void
    {
        $otherTenant = $this->createTenant();
        $otherUser = $this->createAdminUser($otherTenant);

        $widget = $this->service->addWidget($otherUser, 'notifications', 'summary');

        $result = $this->service->removeWidget($this->user, $widget->id);

        $this->assertFalse($result);
        $this->assertDatabaseHas('user_widget_preferences', ['id' => $widget->id]);
    }

    // ── 2.1.5 updateWidgetConfig ──────────────────────────────────

    /**
     * Test 2.1.5: updateWidgetConfig merges new config with existing.
     * Validates: Requirement 7.1 (configure widget interface)
     */
    public function test_update_widget_config_merges_with_existing_config(): void
    {
        $widget = $this->service->addWidget(
            $this->user,
            'notifications',
            'chart-trends',
            ['period_days' => 7, 'chart_type' => 'line']
        );

        $result = $this->service->updateWidgetConfig($widget->id, ['period_days' => 30]);

        $this->assertTrue($result);
        $updated = $widget->fresh();
        $this->assertEquals(30, $updated->widget_config['period_days']);
        $this->assertEquals('line', $updated->widget_config['chart_type']); // preserved
    }

    /**
     * Test 2.1.5: updateWidgetConfig throws RuntimeException for non-existent widget.
     */
    public function test_update_widget_config_throws_for_non_existent_widget(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->service->updateWidgetConfig(999999, ['period_days' => 7]);
    }

    /**
     * Test 2.1.5: updateWidgetConfig throws InvalidArgumentException for empty config.
     */
    public function test_update_widget_config_throws_for_empty_config(): void
    {
        $widget = $this->service->addWidget($this->user, 'reports', 'report-stats');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/tidak boleh kosong/');

        $this->service->updateWidgetConfig($widget->id, []);
    }

    /**
     * Test 2.1.5: updateWidgetConfig invalidates cache after update.
     * Validates: Requirement 8.5 (cache invalidation)
     */
    public function test_update_widget_config_invalidates_cache(): void
    {
        $widget = $this->service->addWidget($this->user, 'reports', 'report-stats');
        $cacheKey = "widget_data.{$this->user->tenant_id}.{$widget->id}.report-stats";

        Cache::put($cacheKey, ['stale' => true], 300);
        $this->assertTrue(Cache::has($cacheKey));

        $this->service->updateWidgetConfig($widget->id, ['show_frequency' => false]);

        $this->assertFalse(Cache::has($cacheKey), 'Cache must be invalidated after config update');
    }

    // ── 2.1.6 getWidgetData ───────────────────────────────────────

    /**
     * Test 2.1.6: getWidgetData returns array with required keys.
     * Validates: Requirement 8.5 (caching for widget data)
     */
    public function test_get_widget_data_returns_array_with_required_keys(): void
    {
        $widget = $this->service->addWidget($this->user, 'notifications', 'summary');

        $data = $this->service->getWidgetData($widget);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('widget_type', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('fetched_at', $data);
        $this->assertEquals('summary', $data['widget_type']);
        $this->assertEquals('notifications', $data['page']);
    }

    /**
     * Test 2.1.6: getWidgetData caches result with 5-minute TTL.
     * Validates: Requirement 8.5 (5-minute cache TTL)
     */
    public function test_get_widget_data_caches_result(): void
    {
        $widget = $this->service->addWidget($this->user, 'notifications', 'summary');
        $cacheKey = "widget_data.{$this->user->tenant_id}.{$widget->id}.summary";

        $this->assertFalse(Cache::has($cacheKey), 'Cache should be empty before first call');

        $this->service->getWidgetData($widget);

        $this->assertTrue(Cache::has($cacheKey), 'Data must be cached after first call');
    }

    /**
     * Test 2.1.6: getWidgetData returns cached data on second call.
     */
    public function test_get_widget_data_returns_cached_data_on_second_call(): void
    {
        $widget = $this->service->addWidget($this->user, 'notifications', 'summary');
        $cacheKey = "widget_data.{$this->user->tenant_id}.{$widget->id}.summary";

        // Seed cache with known data
        $cachedData = [
            'widget_type' => 'summary',
            'page' => 'notifications',
            'cached' => true,
            'fetched_at' => now()->toIso8601String(),
        ];
        Cache::put($cacheKey, $cachedData, 300);

        $result = $this->service->getWidgetData($widget);

        $this->assertTrue($result['cached'] ?? false, 'Should return cached data');
    }

    /**
     * Test 2.1.6: getWidgetData returns chart structure for chart widgets.
     */
    public function test_get_widget_data_returns_chart_structure_for_chart_widgets(): void
    {
        $widget = $this->service->addWidget($this->user, 'notifications', 'chart-trends');

        $data = $this->service->getWidgetData($widget);

        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('datasets', $data);
    }

    /**
     * Test 2.1.6: getWidgetData returns items structure for list widgets.
     */
    public function test_get_widget_data_returns_items_structure_for_list_widgets(): void
    {
        $widget = $this->service->addWidget($this->user, 'notifications', 'recent-items');

        $data = $this->service->getWidgetData($widget);

        $this->assertArrayHasKey('items', $data);
    }

    // ── getSupportedPages ─────────────────────────────────────────

    /**
     * Test: getSupportedPages returns all 5 target pages.
     */
    public function test_get_supported_pages_returns_all_target_pages(): void
    {
        $pages = $this->service->getSupportedPages();

        $this->assertCount(5, $pages);
        $this->assertContains('notifications', $pages->toArray());
        $this->assertContains('room-availability', $pages->toArray());
        $this->assertContains('reports', $pages->toArray());
        $this->assertContains('anomalies', $pages->toArray());
        $this->assertContains('simulations', $pages->toArray());
    }
}
