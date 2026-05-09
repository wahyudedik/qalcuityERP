<?php

namespace Tests\Unit\Components\Layout;

use App\View\Components\Layout\WidgetContainer;
use Tests\TestCase;

/**
 * Unit tests for WidgetContainer Blade component
 *
 * @see Task 3.2: Create widget-container Blade component
 */
class WidgetContainerTest extends TestCase
{
    // ─── 3.2.1 Widget wrapper with loading states ─────────────────────

    public function test_default_loading_state_is_false(): void
    {
        $component = new WidgetContainer(widgetId: 'test-widget');

        $this->assertFalse($component->loading);
    }

    public function test_loading_state_can_be_set_to_true(): void
    {
        $component = new WidgetContainer(widgetId: 'test-widget', loading: true);

        $this->assertTrue($component->loading);
    }

    public function test_widget_id_is_set(): void
    {
        $component = new WidgetContainer(widgetId: 'stats-panel-1');

        $this->assertEquals('stats-panel-1', $component->widgetId);
    }

    public function test_title_is_set(): void
    {
        $component = new WidgetContainer(widgetId: 'w1', title: 'Statistik Penjualan');

        $this->assertEquals('Statistik Penjualan', $component->title);
    }

    public function test_size_sm_produces_correct_classes(): void
    {
        $component = new WidgetContainer(widgetId: 'w1', size: 'sm');

        $this->assertEquals('min-h-[8rem]', $component->sizeClasses);
    }

    public function test_size_md_is_default(): void
    {
        $component = new WidgetContainer(widgetId: 'w1');

        $this->assertEquals('min-h-[12rem]', $component->sizeClasses);
    }

    public function test_size_lg_produces_correct_classes(): void
    {
        $component = new WidgetContainer(widgetId: 'w1', size: 'lg');

        $this->assertEquals('min-h-[16rem]', $component->sizeClasses);
    }

    public function test_size_full_produces_correct_classes(): void
    {
        $component = new WidgetContainer(widgetId: 'w1', size: 'full');

        $this->assertEquals('min-h-[20rem]', $component->sizeClasses);
    }

    public function test_invalid_size_defaults_to_md(): void
    {
        $component = new WidgetContainer(widgetId: 'w1', size: 'invalid');

        $this->assertEquals('min-h-[12rem]', $component->sizeClasses);
    }

    // ─── 3.2.2 Edit mode for widget configuration ────────────────────

    public function test_editable_defaults_to_false(): void
    {
        $component = new WidgetContainer(widgetId: 'w1');

        $this->assertFalse($component->editable);
    }

    public function test_editable_can_be_enabled(): void
    {
        $component = new WidgetContainer(widgetId: 'w1', editable: true);

        $this->assertTrue($component->editable);
    }

    // ─── 3.2.3 Error boundary for failed widgets ─────────────────────

    public function test_default_error_message(): void
    {
        $component = new WidgetContainer(widgetId: 'w1');

        $this->assertEquals('Widget gagal dimuat', $component->getErrorMessage());
    }

    public function test_custom_error_message(): void
    {
        $component = new WidgetContainer(
            widgetId: 'w1',
            errorMessage: 'Data tidak tersedia'
        );

        $this->assertEquals('Data tidak tersedia', $component->getErrorMessage());
    }

    // ─── 3.2.4 Drag-and-drop support ─────────────────────────────────

    public function test_draggable_defaults_to_false(): void
    {
        $component = new WidgetContainer(widgetId: 'w1');

        $this->assertFalse($component->draggable);
    }

    public function test_draggable_can_be_enabled(): void
    {
        $component = new WidgetContainer(widgetId: 'w1', draggable: true);

        $this->assertTrue($component->draggable);
    }

    // ─── Accessibility ───────────────────────────────────────────────

    public function test_aria_label_uses_title_when_set(): void
    {
        $component = new WidgetContainer(widgetId: 'w1', title: 'Grafik Penjualan');

        $this->assertEquals('Widget: Grafik Penjualan', $component->getAriaLabel());
    }

    public function test_aria_label_defaults_to_widget_without_title(): void
    {
        $component = new WidgetContainer(widgetId: 'w1');

        $this->assertEquals('Widget', $component->getAriaLabel());
    }

    public function test_custom_aria_label_overrides_default(): void
    {
        $component = new WidgetContainer(
            widgetId: 'w1',
            title: 'Grafik',
            ariaLabel: 'Panel grafik penjualan bulanan'
        );

        $this->assertEquals('Panel grafik penjualan bulanan', $component->getAriaLabel());
    }

    // ─── Render test ─────────────────────────────────────────────────

    public function test_component_renders_without_error(): void
    {
        $component = new WidgetContainer(
            widgetId: 'test-1',
            title: 'Test Widget',
            editable: true,
            draggable: true,
            size: 'lg'
        );

        $view = $component->render();

        $this->assertEquals('components.layout.widget-container', $view->name());
    }

    public function test_component_renders_with_minimal_config(): void
    {
        $component = new WidgetContainer();

        $view = $component->render();

        $this->assertEquals('components.layout.widget-container', $view->name());
    }

    // ─── Blade rendering integration ─────────────────────────────────

    public function test_blade_renders_loading_skeleton(): void
    {
        $view = $this->blade(
            '<x-layout.widget-container widget-id="test-1" title="Test" :loading="true">Content</x-layout.widget-container>'
        );

        $view->assertSee('animate-pulse');
        $view->assertSee('widget-title-test-1');
    }

    public function test_blade_renders_widget_title(): void
    {
        $view = $this->blade(
            '<x-layout.widget-container widget-id="w1" title="Statistik Harian">Content</x-layout.widget-container>'
        );

        $view->assertSee('Statistik Harian');
        $view->assertSee('widget-title-w1');
    }

    public function test_blade_renders_error_message(): void
    {
        $view = $this->blade(
            '<x-layout.widget-container widget-id="w1" title="Test">Content</x-layout.widget-container>'
        );

        $view->assertSee('Widget gagal dimuat');
        $view->assertSee('Coba Lagi');
    }

    public function test_blade_renders_custom_error_message(): void
    {
        $view = $this->blade(
            '<x-layout.widget-container widget-id="w1" error-message="Koneksi terputus">Content</x-layout.widget-container>'
        );

        $view->assertSee('Koneksi terputus');
    }

    public function test_blade_renders_drag_handle_when_draggable(): void
    {
        $view = $this->blade(
            '<x-layout.widget-container widget-id="w1" title="Test" :draggable="true">Content</x-layout.widget-container>'
        );

        $view->assertSee('Seret untuk mengatur ulang widget');
        $view->assertSee('draggable="true"', false);
    }

    public function test_blade_does_not_render_drag_handle_when_not_draggable(): void
    {
        $view = $this->blade(
            '<x-layout.widget-container widget-id="w1" title="Test" :draggable="false">Content</x-layout.widget-container>'
        );

        $view->assertDontSee('Seret untuk mengatur ulang widget');
    }

    public function test_blade_renders_settings_button_when_editable(): void
    {
        $view = $this->blade(
            '<x-layout.widget-container widget-id="w1" title="Test" :editable="true">Content</x-layout.widget-container>'
        );

        $view->assertSee('Pengaturan widget');
        $view->assertSee('widget-settings-w1');
    }

    public function test_blade_does_not_render_settings_when_not_editable(): void
    {
        $view = $this->blade(
            '<x-layout.widget-container widget-id="w1" title="Test" :editable="false">Content</x-layout.widget-container>'
        );

        $view->assertDontSee('widget-settings-w1');
    }

    public function test_blade_renders_aria_attributes(): void
    {
        $view = $this->blade(
            '<x-layout.widget-container widget-id="w1" title="Grafik" aria-label="Panel grafik utama">Content</x-layout.widget-container>'
        );

        $view->assertSee('aria-label="Panel grafik utama"', false);
        $view->assertSee('role="region"', false);
    }

    public function test_blade_renders_slot_content(): void
    {
        $view = $this->blade(
            '<x-layout.widget-container widget-id="w1" title="Test"><p>Hello Widget World</p></x-layout.widget-container>'
        );

        $view->assertSee('Hello Widget World');
    }

    public function test_blade_renders_alpine_data_attribute(): void
    {
        $view = $this->blade(
            '<x-layout.widget-container widget-id="my-widget" title="Test">Content</x-layout.widget-container>'
        );

        $view->assertSee("widgetId: 'my-widget'", false);
    }

    public function test_blade_renders_data_widget_id_attribute(): void
    {
        $view = $this->blade(
            '<x-layout.widget-container widget-id="chart-1" title="Test">Content</x-layout.widget-container>'
        );

        $view->assertSee('data-widget-id="chart-1"', false);
    }
}
