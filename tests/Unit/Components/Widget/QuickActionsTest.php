<?php

namespace Tests\Unit\Components\Widget;

use App\View\Components\Widget\QuickActions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Unit tests for QuickActionsWidget Blade component
 *
 * @see Task 4.3: Create QuickActionsWidget component
 */
class QuickActionsTest extends TestCase
{

    // ─── 4.3.1 Button grid layout with icons and labels ───────────

    public function test_grid_classes_for_single_action(): void
    {
        $actions = [
            ['label' => 'Action 1', 'url' => '/test'],
        ];

        $component = new QuickActions(actions: $actions);

        $this->assertEquals('grid-cols-1', $component->gridClasses);
    }

    public function test_grid_classes_for_two_actions(): void
    {
        $actions = [
            ['label' => 'Action 1', 'url' => '/test1'],
            ['label' => 'Action 2', 'url' => '/test2'],
        ];

        $component = new QuickActions(actions: $actions);

        $this->assertEquals('grid-cols-1 sm:grid-cols-2', $component->gridClasses);
    }

    public function test_grid_classes_for_three_actions(): void
    {
        $actions = [
            ['label' => 'Action 1', 'url' => '/test1'],
            ['label' => 'Action 2', 'url' => '/test2'],
            ['label' => 'Action 3', 'url' => '/test3'],
        ];

        $component = new QuickActions(actions: $actions);

        $this->assertEquals('grid-cols-2 sm:grid-cols-3', $component->gridClasses);
    }

    public function test_grid_classes_for_four_or_more_actions(): void
    {
        $actions = [
            ['label' => 'Action 1', 'url' => '/test1'],
            ['label' => 'Action 2', 'url' => '/test2'],
            ['label' => 'Action 3', 'url' => '/test3'],
            ['label' => 'Action 4', 'url' => '/test4'],
        ];

        $component = new QuickActions(actions: $actions);

        $this->assertEquals('grid-cols-2 sm:grid-cols-3 lg:grid-cols-4', $component->gridClasses);
    }

    public function test_explicit_columns_override_auto_detection(): void
    {
        $actions = [
            ['label' => 'Action 1', 'url' => '/test1'],
            ['label' => 'Action 2', 'url' => '/test2'],
        ];

        $component = new QuickActions(actions: $actions, columns: 3);

        $this->assertEquals('grid-cols-2 sm:grid-cols-3', $component->gridClasses);
    }

    public function test_normalize_action_provides_defaults(): void
    {
        $action = ['label' => 'Test'];

        $normalized = QuickActions::normalizeAction($action);

        $this->assertEquals('Test', $normalized['label']);
        $this->assertNull($normalized['icon']);
        $this->assertEquals('#', $normalized['url']);
        $this->assertEquals('GET', $normalized['method']);
        $this->assertNull($normalized['permission']);
        $this->assertNull($normalized['shortcut']);
        $this->assertFalse($normalized['confirm']);
    }

    public function test_normalize_action_preserves_values(): void
    {
        $action = [
            'label' => 'Tandai Dibaca',
            'icon' => 'check-circle',
            'url' => '/notifications/mark-all-read',
            'method' => 'POST',
            'permission' => 'notifications.edit',
            'shortcut' => 'Ctrl+M',
            'confirm' => true,
        ];

        $normalized = QuickActions::normalizeAction($action);

        $this->assertEquals('Tandai Dibaca', $normalized['label']);
        $this->assertEquals('check-circle', $normalized['icon']);
        $this->assertEquals('/notifications/mark-all-read', $normalized['url']);
        $this->assertEquals('POST', $normalized['method']);
        $this->assertEquals('notifications.edit', $normalized['permission']);
        $this->assertEquals('Ctrl+M', $normalized['shortcut']);
        $this->assertTrue($normalized['confirm']);
    }

    public function test_normalize_action_uppercases_method(): void
    {
        $action = ['label' => 'Test', 'method' => 'post'];

        $normalized = QuickActions::normalizeAction($action);

        $this->assertEquals('POST', $normalized['method']);
    }

    public function test_normalize_action_defaults_invalid_method_to_get(): void
    {
        $action = ['label' => 'Test', 'method' => 'INVALID'];

        $normalized = QuickActions::normalizeAction($action);

        $this->assertEquals('GET', $normalized['method']);
    }

    public function test_normalize_action_provides_default_confirm_message(): void
    {
        $action = ['label' => 'Test', 'confirm' => true];

        $normalized = QuickActions::normalizeAction($action);

        $this->assertEquals('Apakah Anda yakin ingin melakukan aksi ini?', $normalized['confirmMessage']);
    }

    public function test_normalize_action_preserves_custom_confirm_message(): void
    {
        $action = ['label' => 'Test', 'confirm' => true, 'confirmMessage' => 'Yakin hapus?'];

        $normalized = QuickActions::normalizeAction($action);

        $this->assertEquals('Yakin hapus?', $normalized['confirmMessage']);
    }

    public function test_get_normalized_actions_returns_all_filtered_actions(): void
    {
        $actions = [
            ['label' => 'Action 1', 'url' => '/test1'],
            ['label' => 'Action 2', 'url' => '/test2'],
        ];

        $component = new QuickActions(actions: $actions);
        $normalized = $component->getNormalizedActions();

        $this->assertCount(2, $normalized);
        $this->assertEquals('Action 1', $normalized[0]['label']);
        $this->assertEquals('Action 2', $normalized[1]['label']);
    }

    public function test_empty_actions_renders_empty_state(): void
    {
        $component = new QuickActions(actions: []);

        $this->assertCount(0, $component->filteredActions);
    }

    // ─── 4.3.2 Permission-based action filtering ──────────────────

    public function test_actions_without_permission_are_always_shown(): void
    {
        $actions = [
            ['label' => 'Public Action', 'url' => '/test', 'permission' => null],
            ['label' => 'Another Public', 'url' => '/test2'],
        ];

        $component = new QuickActions(actions: $actions);

        $this->assertCount(2, $component->filteredActions);
    }

    public function test_actions_with_empty_permission_are_always_shown(): void
    {
        $actions = [
            ['label' => 'Public Action', 'url' => '/test', 'permission' => ''],
        ];

        $component = new QuickActions(actions: $actions);

        $this->assertCount(1, $component->filteredActions);
    }

    public function test_actions_with_permission_hidden_when_no_user(): void
    {
        Auth::shouldReceive('user')->andReturn(null);

        $actions = [
            ['label' => 'Protected', 'url' => '/test', 'permission' => 'notifications.edit'],
            ['label' => 'Public', 'url' => '/test2', 'permission' => null],
        ];

        $component = new QuickActions(actions: $actions);

        $this->assertCount(1, $component->filteredActions);
        $this->assertEquals('Public', $component->filteredActions[0]['label']);
    }

    public function test_actions_with_permission_shown_when_user_has_permission(): void
    {
        $user = $this->createMockUserWithPermission('notifications.edit');
        Auth::shouldReceive('user')->andReturn($user);

        $actions = [
            ['label' => 'Protected', 'url' => '/test', 'permission' => 'notifications.edit'],
            ['label' => 'Public', 'url' => '/test2', 'permission' => null],
        ];

        $component = new QuickActions(actions: $actions);

        $this->assertCount(2, $component->filteredActions);
    }

    public function test_actions_with_permission_hidden_when_user_lacks_permission(): void
    {
        $user = $this->createMockUserWithoutPermission('notifications.delete');
        Auth::shouldReceive('user')->andReturn($user);

        $actions = [
            ['label' => 'Delete', 'url' => '/test', 'permission' => 'notifications.delete'],
            ['label' => 'Public', 'url' => '/test2', 'permission' => null],
        ];

        $component = new QuickActions(actions: $actions);

        $this->assertCount(1, $component->filteredActions);
        $this->assertEquals('Public', $component->filteredActions[0]['label']);
    }

    public function test_filtered_actions_are_reindexed(): void
    {
        Auth::shouldReceive('user')->andReturn(null);

        $actions = [
            ['label' => 'Protected 1', 'url' => '/test1', 'permission' => 'admin'],
            ['label' => 'Public', 'url' => '/test2', 'permission' => null],
            ['label' => 'Protected 2', 'url' => '/test3', 'permission' => 'admin'],
        ];

        $component = new QuickActions(actions: $actions);

        $this->assertCount(1, $component->filteredActions);
        $this->assertArrayHasKey(0, $component->filteredActions);
    }

    // ─── 4.3.3 Keyboard shortcuts for common actions ──────────────

    public function test_parse_shortcut_key_extracts_last_part(): void
    {
        $this->assertEquals('m', QuickActions::parseShortcutKey('Ctrl+M'));
        $this->assertEquals('s', QuickActions::parseShortcutKey('Ctrl+Shift+S'));
        $this->assertEquals('a', QuickActions::parseShortcutKey('Alt+A'));
    }

    public function test_shortcut_requires_ctrl(): void
    {
        $this->assertTrue(QuickActions::shortcutRequiresCtrl('Ctrl+M'));
        $this->assertTrue(QuickActions::shortcutRequiresCtrl('ctrl+m'));
        $this->assertFalse(QuickActions::shortcutRequiresCtrl('Alt+M'));
    }

    public function test_shortcut_requires_alt(): void
    {
        $this->assertTrue(QuickActions::shortcutRequiresAlt('Alt+A'));
        $this->assertTrue(QuickActions::shortcutRequiresAlt('Ctrl+Alt+A'));
        $this->assertFalse(QuickActions::shortcutRequiresAlt('Ctrl+M'));
    }

    public function test_shortcut_requires_shift(): void
    {
        $this->assertTrue(QuickActions::shortcutRequiresShift('Ctrl+Shift+S'));
        $this->assertTrue(QuickActions::shortcutRequiresShift('Shift+A'));
        $this->assertFalse(QuickActions::shortcutRequiresShift('Ctrl+M'));
    }

    public function test_get_shortcuts_json_returns_valid_json(): void
    {
        $actions = [
            ['label' => 'Action 1', 'url' => '/test1', 'shortcut' => 'Ctrl+M'],
            ['label' => 'Action 2', 'url' => '/test2'],
            ['label' => 'Action 3', 'url' => '/test3', 'shortcut' => 'Ctrl+Shift+A'],
        ];

        $component = new QuickActions(actions: $actions);
        $json = $component->getShortcutsJson();
        $decoded = json_decode($json, true);

        $this->assertNotNull($decoded);
        $this->assertCount(2, $decoded);
    }

    public function test_shortcuts_json_contains_correct_data(): void
    {
        $actions = [
            ['label' => 'Mark Read', 'url' => '/test', 'shortcut' => 'Ctrl+M'],
        ];

        $component = new QuickActions(actions: $actions);
        $decoded = json_decode($component->getShortcutsJson(), true);

        $this->assertEquals('m', $decoded[0]['key']);
        $this->assertTrue($decoded[0]['ctrl']);
        $this->assertFalse($decoded[0]['alt']);
        $this->assertFalse($decoded[0]['shift']);
        $this->assertEquals(0, $decoded[0]['index']);
        $this->assertEquals('Mark Read', $decoded[0]['label']);
    }

    public function test_shortcuts_json_empty_when_no_shortcuts(): void
    {
        $actions = [
            ['label' => 'Action 1', 'url' => '/test1'],
            ['label' => 'Action 2', 'url' => '/test2'],
        ];

        $component = new QuickActions(actions: $actions);
        $decoded = json_decode($component->getShortcutsJson(), true);

        $this->assertCount(0, $decoded);
    }

    // ─── 4.3.4 Loading states for async actions ───────────────────

    public function test_is_async_action_for_post(): void
    {
        $this->assertTrue(QuickActions::isAsyncAction(['method' => 'POST']));
    }

    public function test_is_async_action_for_put(): void
    {
        $this->assertTrue(QuickActions::isAsyncAction(['method' => 'PUT']));
    }

    public function test_is_async_action_for_delete(): void
    {
        $this->assertTrue(QuickActions::isAsyncAction(['method' => 'DELETE']));
    }

    public function test_is_not_async_action_for_get(): void
    {
        $this->assertFalse(QuickActions::isAsyncAction(['method' => 'GET']));
    }

    public function test_is_not_async_action_for_default(): void
    {
        $this->assertFalse(QuickActions::isAsyncAction([]));
    }

    // ─── Error and loading states ─────────────────────────────────

    public function test_default_loading_state_is_false(): void
    {
        $component = new QuickActions();

        $this->assertFalse($component->loading);
    }

    public function test_loading_state_can_be_set(): void
    {
        $component = new QuickActions(loading: true);

        $this->assertTrue($component->loading);
    }

    public function test_default_error_state_is_false(): void
    {
        $component = new QuickActions();

        $this->assertFalse($component->error);
    }

    public function test_error_state_can_be_set(): void
    {
        $component = new QuickActions(error: true);

        $this->assertTrue($component->error);
    }

    public function test_default_error_message(): void
    {
        $component = new QuickActions();

        $this->assertEquals('Gagal memuat aksi cepat', $component->getErrorMessage());
    }

    public function test_custom_error_message(): void
    {
        $component = new QuickActions(errorMessage: 'Koneksi terputus');

        $this->assertEquals('Koneksi terputus', $component->getErrorMessage());
    }

    // ─── ARIA and accessibility ───────────────────────────────────

    public function test_aria_label_with_title(): void
    {
        $component = new QuickActions(title: 'Aksi Cepat');

        $this->assertEquals('Aksi cepat: Aksi Cepat', $component->getAriaLabel());
    }

    public function test_aria_label_without_title(): void
    {
        $component = new QuickActions();

        $this->assertEquals('Widget Aksi Cepat', $component->getAriaLabel());
    }

    // ─── Render test ─────────────────────────────────────────────

    public function test_component_renders_without_error(): void
    {
        $actions = [
            ['label' => 'Test Action', 'icon' => 'check-circle', 'url' => '/test'],
        ];

        $component = new QuickActions(actions: $actions, title: 'Aksi Cepat');
        $view = $component->render();

        $this->assertEquals('components.widget.quick-actions', $view->name());
    }

    public function test_component_renders_with_minimal_config(): void
    {
        $component = new QuickActions();
        $view = $component->render();

        $this->assertEquals('components.widget.quick-actions', $view->name());
    }

    // ─── Blade rendering integration ─────────────────────────────

    public function test_blade_renders_title(): void
    {
        $view = $this->blade(
            '<x-widget.quick-actions :actions="$actions" title="Aksi Cepat" />',
            ['actions' => [['label' => 'Test', 'url' => '/test']]]
        );

        $view->assertSee('Aksi Cepat');
    }

    public function test_blade_renders_action_labels(): void
    {
        $view = $this->blade(
            '<x-widget.quick-actions :actions="$actions" />',
            ['actions' => [
                ['label' => 'Tandai Dibaca', 'url' => '/test', 'icon' => 'check-circle'],
                ['label' => 'Filter Prioritas', 'url' => '/test2', 'icon' => 'exclamation'],
            ]]
        );

        $view->assertSee('Tandai Dibaca');
        $view->assertSee('Filter Prioritas');
    }

    public function test_blade_renders_loading_skeleton(): void
    {
        $view = $this->blade(
            '<x-widget.quick-actions :actions="$actions" :loading="true" />',
            ['actions' => [['label' => 'Test', 'url' => '/test']]]
        );

        $view->assertSee('animate-pulse');
    }

    public function test_blade_renders_error_state(): void
    {
        $view = $this->blade(
            '<x-widget.quick-actions :actions="$actions" :error="true" />',
            ['actions' => [['label' => 'Test', 'url' => '/test']]]
        );

        $view->assertSee('Gagal memuat aksi cepat');
        $view->assertSee('Coba Lagi');
    }

    public function test_blade_renders_custom_error_message(): void
    {
        $view = $this->blade(
            '<x-widget.quick-actions :actions="$actions" :error="true" error-message="Koneksi gagal" />',
            ['actions' => [['label' => 'Test', 'url' => '/test']]]
        );

        $view->assertSee('Koneksi gagal');
    }

    public function test_blade_renders_empty_state_when_no_actions(): void
    {
        $view = $this->blade(
            '<x-widget.quick-actions :actions="$actions" />',
            ['actions' => []]
        );

        $view->assertSee('Tidak ada aksi tersedia');
    }

    public function test_blade_renders_aria_attributes(): void
    {
        $view = $this->blade(
            '<x-widget.quick-actions :actions="$actions" title="Aksi" />',
            ['actions' => [['label' => 'Test', 'url' => '/test']]]
        );

        $view->assertSee('role="region"', false);
        $view->assertSee('Aksi cepat: Aksi', false);
    }

    public function test_blade_renders_toolbar_role(): void
    {
        $view = $this->blade(
            '<x-widget.quick-actions :actions="$actions" />',
            ['actions' => [['label' => 'Test', 'url' => '/test']]]
        );

        $view->assertSee('role="toolbar"', false);
    }

    public function test_blade_renders_alpine_data_attribute(): void
    {
        $view = $this->blade(
            '<x-widget.quick-actions :actions="$actions" />',
            ['actions' => [['label' => 'Test', 'url' => '/test']]]
        );

        $view->assertSee('quickActionsWidget', false);
    }

    public function test_blade_renders_shortcut_badge(): void
    {
        $view = $this->blade(
            '<x-widget.quick-actions :actions="$actions" />',
            ['actions' => [['label' => 'Mark Read', 'url' => '/test', 'shortcut' => 'Ctrl+M']]]
        );

        $view->assertSee('Ctrl+M');
    }

    public function test_blade_renders_without_title(): void
    {
        $view = $this->blade(
            '<x-widget.quick-actions :actions="$actions" />',
            ['actions' => [['label' => 'Test', 'url' => '/test']]]
        );

        $view->assertDontSee('border-b border-gray-100');
    }

    // ─── Helper methods ───────────────────────────────────────────

    private function createMockUserWithPermission(string $permission): object
    {
        $user = new class($permission) {
            private string $allowedPermission;

            public function __construct(string $permission)
            {
                $this->allowedPermission = $permission;
            }

            public function can(string $ability, $arguments = []): bool
            {
                return $ability === $this->allowedPermission;
            }
        };

        return $user;
    }

    private function createMockUserWithoutPermission(string $permission): object
    {
        $user = new class($permission) {
            private string $deniedPermission;

            public function __construct(string $permission)
            {
                $this->deniedPermission = $permission;
            }

            public function can(string $ability, $arguments = []): bool
            {
                return $ability !== $this->deniedPermission;
            }
        };

        return $user;
    }
}
