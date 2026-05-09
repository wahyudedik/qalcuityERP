<?php

namespace Tests\Unit\Components\Widget;

use App\View\Components\Widget\Chart;
use Tests\TestCase;

/**
 * Unit tests for ChartWidget Blade component
 *
 * @see Task 4.2: Create ChartWidget component using Chart.js
 */
class ChartTest extends TestCase
{
    // ─── 4.2.1 Line chart for trends and time series ──────────────

    public function test_default_type_is_line(): void
    {
        $component = new Chart();

        $this->assertEquals('line', $component->validatedType);
    }

    public function test_line_type_is_accepted(): void
    {
        $component = new Chart(type: 'line');

        $this->assertEquals('line', $component->validatedType);
    }

    public function test_line_chart_has_tension_and_point_options(): void
    {
        $component = new Chart(type: 'line');
        $options = $component->buildDefaultOptions();

        $this->assertEquals(0.3, $options['elements']['line']['tension']);
        $this->assertEquals(2, $options['elements']['line']['borderWidth']);
        $this->assertEquals(3, $options['elements']['point']['radius']);
        $this->assertEquals(5, $options['elements']['point']['hoverRadius']);
    }

    public function test_line_chart_has_scales(): void
    {
        $component = new Chart(type: 'line');
        $options = $component->buildDefaultOptions();

        $this->assertArrayHasKey('scales', $options);
        $this->assertArrayHasKey('x', $options['scales']);
        $this->assertArrayHasKey('y', $options['scales']);
        $this->assertTrue($options['scales']['y']['beginAtZero']);
    }

    // ─── 4.2.2 Bar chart for comparisons and categories ───────────

    public function test_bar_type_is_accepted(): void
    {
        $component = new Chart(type: 'bar');

        $this->assertEquals('bar', $component->validatedType);
    }

    public function test_bar_chart_has_border_radius(): void
    {
        $component = new Chart(type: 'bar');
        $options = $component->buildDefaultOptions();

        $this->assertEquals(4, $options['elements']['bar']['borderRadius']);
        $this->assertEquals(0, $options['elements']['bar']['borderWidth']);
    }

    public function test_bar_chart_has_scales(): void
    {
        $component = new Chart(type: 'bar');
        $options = $component->buildDefaultOptions();

        $this->assertArrayHasKey('scales', $options);
        $this->assertFalse($options['scales']['x']['grid']['display']);
    }

    // ─── 4.2.3 Pie/doughnut chart for distributions ───────────────

    public function test_pie_type_is_accepted(): void
    {
        $component = new Chart(type: 'pie');

        $this->assertEquals('pie', $component->validatedType);
    }

    public function test_doughnut_type_is_accepted(): void
    {
        $component = new Chart(type: 'doughnut');

        $this->assertEquals('doughnut', $component->validatedType);
    }

    public function test_circular_charts_have_no_scales(): void
    {
        $pie = new Chart(type: 'pie');
        $doughnut = new Chart(type: 'doughnut');

        $pieOptions = $pie->buildDefaultOptions();
        $doughnutOptions = $doughnut->buildDefaultOptions();

        $this->assertArrayNotHasKey('scales', $pieOptions);
        $this->assertArrayNotHasKey('scales', $doughnutOptions);
    }

    public function test_circular_charts_have_legend_at_bottom(): void
    {
        $pie = new Chart(type: 'pie');
        $options = $pie->buildDefaultOptions();

        $this->assertEquals('bottom', $options['plugins']['legend']['position']);
    }

    public function test_non_circular_charts_have_legend_at_top(): void
    {
        $line = new Chart(type: 'line');
        $options = $line->buildDefaultOptions();

        $this->assertEquals('top', $options['plugins']['legend']['position']);
    }

    public function test_is_circular_type_returns_true_for_pie(): void
    {
        $this->assertTrue(Chart::isCircularType('pie'));
    }

    public function test_is_circular_type_returns_true_for_doughnut(): void
    {
        $this->assertTrue(Chart::isCircularType('doughnut'));
    }

    public function test_is_circular_type_returns_false_for_line(): void
    {
        $this->assertFalse(Chart::isCircularType('line'));
    }

    public function test_is_circular_type_returns_false_for_bar(): void
    {
        $this->assertFalse(Chart::isCircularType('bar'));
    }

    // ─── 4.2.4 Responsive chart sizing and mobile optimization ────

    public function test_default_height_is_200(): void
    {
        $component = new Chart();

        $this->assertEquals('200px', $component->getHeightStyle());
    }

    public function test_custom_height_is_applied(): void
    {
        $component = new Chart(height: 300);

        $this->assertEquals('300px', $component->getHeightStyle());
    }

    public function test_string_height_is_converted(): void
    {
        $component = new Chart(height: '250');

        $this->assertEquals('250px', $component->getHeightStyle());
    }

    public function test_responsive_option_is_enabled(): void
    {
        $component = new Chart();
        $options = $component->buildDefaultOptions();

        $this->assertTrue($options['responsive']);
        $this->assertFalse($options['maintainAspectRatio']);
    }

    public function test_mobile_options_have_smaller_fonts(): void
    {
        $mobileOptions = Chart::getMobileOptions();

        $this->assertEquals(9, $mobileOptions['plugins']['legend']['labels']['font']['size']);
        $this->assertEquals(9, $mobileOptions['plugins']['tooltip']['bodyFont']['size']);
        $this->assertEquals(10, $mobileOptions['plugins']['tooltip']['titleFont']['size']);
    }

    public function test_mobile_options_have_reduced_ticks(): void
    {
        $mobileOptions = Chart::getMobileOptions();

        $this->assertEquals(5, $mobileOptions['scales']['x']['ticks']['maxTicksLimit']);
        $this->assertEquals(5, $mobileOptions['scales']['y']['ticks']['maxTicksLimit']);
    }

    public function test_mobile_options_have_smaller_legend_box(): void
    {
        $mobileOptions = Chart::getMobileOptions();

        $this->assertEquals(8, $mobileOptions['plugins']['legend']['labels']['boxWidth']);
        $this->assertEquals(8, $mobileOptions['plugins']['legend']['labels']['padding']);
    }

    // ─── 4.2.5 Chart data caching and lazy loading ────────────────

    public function test_lazy_load_is_enabled_by_default(): void
    {
        $component = new Chart();

        $this->assertTrue($component->lazyLoad);
    }

    public function test_lazy_load_can_be_disabled(): void
    {
        $component = new Chart(lazyLoad: false);

        $this->assertFalse($component->lazyLoad);
    }

    public function test_cache_key_is_null_by_default(): void
    {
        $component = new Chart();

        $this->assertNull($component->cacheKey);
    }

    public function test_cache_key_can_be_set(): void
    {
        $component = new Chart(cacheKey: 'notification-trends');

        $this->assertEquals('notification-trends', $component->cacheKey);
    }

    public function test_default_cache_ttl_is_300_seconds(): void
    {
        $component = new Chart();

        $this->assertEquals(300, $component->cacheTtl);
    }

    public function test_custom_cache_ttl(): void
    {
        $component = new Chart(cacheTtl: 600);

        $this->assertEquals(600, $component->cacheTtl);
    }

    // ─── Type validation ──────────────────────────────────────────

    public function test_invalid_type_defaults_to_line(): void
    {
        $this->assertEquals('line', Chart::validateType('invalid'));
        $this->assertEquals('line', Chart::validateType(''));
        $this->assertEquals('line', Chart::validateType('scatter'));
    }

    public function test_type_validation_is_case_insensitive(): void
    {
        $this->assertEquals('line', Chart::validateType('LINE'));
        $this->assertEquals('bar', Chart::validateType('Bar'));
        $this->assertEquals('pie', Chart::validateType('PIE'));
        $this->assertEquals('doughnut', Chart::validateType('Doughnut'));
    }

    public function test_type_validation_trims_whitespace(): void
    {
        $this->assertEquals('line', Chart::validateType(' line '));
        $this->assertEquals('bar', Chart::validateType('  bar'));
    }

    // ─── Data normalization ───────────────────────────────────────

    public function test_normalize_empty_data(): void
    {
        $result = Chart::normalizeData([]);

        $this->assertEquals(['labels' => [], 'datasets' => []], $result);
    }

    public function test_normalize_data_preserves_labels(): void
    {
        $data = [
            'labels' => ['Sen', 'Sel', 'Rab'],
            'datasets' => [],
        ];

        $result = Chart::normalizeData($data);

        $this->assertEquals(['Sen', 'Sel', 'Rab'], $result['labels']);
    }

    public function test_normalize_data_applies_default_colors(): void
    {
        $data = [
            'labels' => ['A', 'B'],
            'datasets' => [
                ['label' => 'Test', 'data' => [1, 2]],
            ],
        ];

        $result = Chart::normalizeData($data);

        $this->assertEquals('#3B82F6', $result['datasets'][0]['borderColor']);
        $this->assertStringContainsString('rgba(59, 130, 246', $result['datasets'][0]['backgroundColor']);
    }

    public function test_normalize_data_preserves_custom_colors(): void
    {
        $data = [
            'labels' => ['A', 'B'],
            'datasets' => [
                [
                    'label' => 'Test',
                    'data' => [1, 2],
                    'borderColor' => '#FF0000',
                    'backgroundColor' => 'rgba(255, 0, 0, 0.5)',
                ],
            ],
        ];

        $result = Chart::normalizeData($data);

        $this->assertEquals('#FF0000', $result['datasets'][0]['borderColor']);
        $this->assertEquals('rgba(255, 0, 0, 0.5)', $result['datasets'][0]['backgroundColor']);
    }

    public function test_normalize_data_assigns_different_colors_to_multiple_datasets(): void
    {
        $data = [
            'labels' => ['A'],
            'datasets' => [
                ['label' => 'First', 'data' => [1]],
                ['label' => 'Second', 'data' => [2]],
                ['label' => 'Third', 'data' => [3]],
            ],
        ];

        $result = Chart::normalizeData($data);

        $this->assertEquals('#3B82F6', $result['datasets'][0]['borderColor']);
        $this->assertEquals('#10B981', $result['datasets'][1]['borderColor']);
        $this->assertEquals('#F59E0B', $result['datasets'][2]['borderColor']);
    }

    public function test_normalize_data_cycles_colors_for_many_datasets(): void
    {
        $datasets = [];
        for ($i = 0; $i < 10; $i++) {
            $datasets[] = ['label' => "Set {$i}", 'data' => [$i]];
        }

        $data = ['labels' => ['A'], 'datasets' => $datasets];
        $result = Chart::normalizeData($data);

        // Color at index 8 should cycle back to index 0
        $this->assertEquals('#3B82F6', $result['datasets'][8]['borderColor']);
    }

    public function test_normalize_data_provides_default_label(): void
    {
        $data = [
            'labels' => ['A'],
            'datasets' => [
                ['data' => [1]],
            ],
        ];

        $result = Chart::normalizeData($data);

        $this->assertEquals('Dataset 1', $result['datasets'][0]['label']);
    }

    public function test_normalize_data_handles_missing_labels_key(): void
    {
        $data = [
            'datasets' => [
                ['label' => 'Test', 'data' => [1, 2, 3]],
            ],
        ];

        $result = Chart::normalizeData($data);

        $this->assertEquals([], $result['labels']);
    }

    // ─── Color utilities ──────────────────────────────────────────

    public function test_color_with_alpha_converts_hex_to_rgba(): void
    {
        $result = Chart::colorWithAlpha('#3B82F6', 0.1);

        $this->assertEquals('rgba(59, 130, 246, 0.1)', $result);
    }

    public function test_color_with_alpha_handles_short_hex(): void
    {
        $result = Chart::colorWithAlpha('#F00', 0.5);

        $this->assertEquals('rgba(255, 0, 0, 0.5)', $result);
    }

    public function test_color_with_alpha_handles_hex_without_hash(): void
    {
        $result = Chart::colorWithAlpha('3B82F6', 0.2);

        $this->assertEquals('rgba(59, 130, 246, 0.2)', $result);
    }

    public function test_color_with_alpha_returns_fallback_for_invalid_hex(): void
    {
        $result = Chart::colorWithAlpha('invalid', 0.1);

        $this->assertEquals('rgba(59, 130, 246, 0.1)', $result);
    }

    // ─── ARIA and accessibility ───────────────────────────────────

    public function test_aria_label_with_title(): void
    {
        $component = new Chart(type: 'line', title: 'Tren Notifikasi');

        $this->assertEquals('Grafik garis: Tren Notifikasi', $component->getAriaLabel());
    }

    public function test_aria_label_without_title(): void
    {
        $component = new Chart(type: 'bar');

        $this->assertEquals('Grafik batang', $component->getAriaLabel());
    }

    public function test_aria_label_for_pie(): void
    {
        $component = new Chart(type: 'pie', title: 'Distribusi');

        $this->assertEquals('Grafik lingkaran: Distribusi', $component->getAriaLabel());
    }

    public function test_aria_label_for_doughnut(): void
    {
        $component = new Chart(type: 'doughnut', title: 'Kategori');

        $this->assertEquals('Grafik donat: Kategori', $component->getAriaLabel());
    }

    // ─── Error states ─────────────────────────────────────────────

    public function test_default_error_state_is_false(): void
    {
        $component = new Chart();

        $this->assertFalse($component->error);
    }

    public function test_error_state_can_be_set(): void
    {
        $component = new Chart(error: true);

        $this->assertTrue($component->error);
    }

    public function test_default_error_message(): void
    {
        $component = new Chart();

        $this->assertEquals('Gagal memuat grafik', $component->getErrorMessage());
    }

    public function test_custom_error_message(): void
    {
        $component = new Chart(errorMessage: 'Data tidak tersedia');

        $this->assertEquals('Data tidak tersedia', $component->getErrorMessage());
    }

    public function test_default_loading_state_is_false(): void
    {
        $component = new Chart();

        $this->assertFalse($component->loading);
    }

    public function test_loading_state_can_be_set(): void
    {
        $component = new Chart(loading: true);

        $this->assertTrue($component->loading);
    }

    // ─── Chart options merging ────────────────────────────────────

    public function test_custom_options_are_merged(): void
    {
        $customOptions = [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];

        $component = new Chart(options: $customOptions);
        $decoded = json_decode($component->chartOptionsJson, true);

        $this->assertFalse($decoded['plugins']['legend']['display']);
        // Other default options should still be present
        $this->assertTrue($decoded['responsive']);
    }

    // ─── JSON output ──────────────────────────────────────────────

    public function test_chart_data_json_is_valid(): void
    {
        $data = [
            'labels' => ['Sen', 'Sel', 'Rab'],
            'datasets' => [
                ['label' => 'Test', 'data' => [1, 2, 3], 'borderColor' => '#3B82F6'],
            ],
        ];

        $component = new Chart(data: $data);
        $decoded = json_decode($component->chartDataJson, true);

        $this->assertNotNull($decoded);
        $this->assertEquals(['Sen', 'Sel', 'Rab'], $decoded['labels']);
        $this->assertCount(1, $decoded['datasets']);
    }

    public function test_chart_options_json_is_valid(): void
    {
        $component = new Chart(type: 'line');
        $decoded = json_decode($component->chartOptionsJson, true);

        $this->assertNotNull($decoded);
        $this->assertTrue($decoded['responsive']);
    }

    // ─── Chart ID generation ──────────────────────────────────────

    public function test_chart_id_is_unique(): void
    {
        $component1 = new Chart();
        $component2 = new Chart();

        $this->assertNotEquals($component1->chartId, $component2->chartId);
    }

    public function test_chart_id_has_prefix(): void
    {
        $component = new Chart();

        $this->assertStringStartsWith('chart-', $component->chartId);
    }

    // ─── Render test ─────────────────────────────────────────────

    public function test_component_renders_without_error(): void
    {
        $data = [
            'labels' => ['Sen', 'Sel', 'Rab'],
            'datasets' => [
                ['label' => 'Test', 'data' => [1, 2, 3]],
            ],
        ];

        $component = new Chart(type: 'line', data: $data, title: 'Test Chart');
        $view = $component->render();

        $this->assertEquals('components.widget.chart', $view->name());
    }

    public function test_component_renders_with_minimal_config(): void
    {
        $component = new Chart();
        $view = $component->render();

        $this->assertEquals('components.widget.chart', $view->name());
    }

    // ─── Blade rendering integration ─────────────────────────────

    public function test_blade_renders_title(): void
    {
        $view = $this->blade(
            '<x-widget.chart type="line" :data="$data" title="Tren Notifikasi 7 Hari" />',
            ['data' => ['labels' => ['A'], 'datasets' => [['label' => 'T', 'data' => [1]]]]]
        );

        $view->assertSee('Tren Notifikasi 7 Hari');
    }

    public function test_blade_renders_chart_canvas(): void
    {
        $view = $this->blade(
            '<x-widget.chart type="bar" :data="$data" />',
            ['data' => ['labels' => ['A'], 'datasets' => [['label' => 'T', 'data' => [1]]]]]
        );

        $view->assertSee('<canvas', false);
    }

    public function test_blade_renders_loading_skeleton(): void
    {
        $view = $this->blade(
            '<x-widget.chart type="line" :data="$data" :loading="true" />',
            ['data' => ['labels' => [], 'datasets' => []]]
        );

        $view->assertSee('animate-pulse');
    }

    public function test_blade_renders_error_state(): void
    {
        $view = $this->blade(
            '<x-widget.chart type="line" :data="$data" :error="true" />',
            ['data' => ['labels' => [], 'datasets' => []]]
        );

        $view->assertSee('Gagal memuat grafik');
        $view->assertSee('Coba Lagi');
    }

    public function test_blade_renders_custom_error_message(): void
    {
        $view = $this->blade(
            '<x-widget.chart type="line" :data="$data" :error="true" error-message="Koneksi terputus" />',
            ['data' => ['labels' => [], 'datasets' => []]]
        );

        $view->assertSee('Koneksi terputus');
    }

    public function test_blade_renders_aria_attributes(): void
    {
        $view = $this->blade(
            '<x-widget.chart type="line" :data="$data" title="Tren" />',
            ['data' => ['labels' => ['A'], 'datasets' => [['label' => 'T', 'data' => [1]]]]]
        );

        $view->assertSee('role="region"', false);
        $view->assertSee('Grafik garis: Tren', false);
    }

    public function test_blade_renders_alpine_data_attribute(): void
    {
        $view = $this->blade(
            '<x-widget.chart type="line" :data="$data" />',
            ['data' => ['labels' => ['A'], 'datasets' => [['label' => 'T', 'data' => [1]]]]]
        );

        $view->assertSee('chartWidget', false);
    }

    public function test_blade_renders_circular_skeleton_for_pie(): void
    {
        $view = $this->blade(
            '<x-widget.chart type="pie" :data="$data" :loading="true" />',
            ['data' => ['labels' => [], 'datasets' => []]]
        );

        $view->assertSee('rounded-full', false);
    }

    public function test_blade_renders_bar_skeleton_for_line(): void
    {
        $view = $this->blade(
            '<x-widget.chart type="line" :data="$data" :loading="true" />',
            ['data' => ['labels' => [], 'datasets' => []]]
        );

        // Line/bar charts show bar-style skeleton
        $view->assertSee('items-end', false);
    }

    public function test_blade_renders_with_custom_height(): void
    {
        $view = $this->blade(
            '<x-widget.chart type="line" :data="$data" height="300" />',
            ['data' => ['labels' => ['A'], 'datasets' => [['label' => 'T', 'data' => [1]]]]]
        );

        $view->assertSee('300', false);
    }

    public function test_blade_renders_without_title(): void
    {
        $view = $this->blade(
            '<x-widget.chart type="line" :data="$data" />',
            ['data' => ['labels' => ['A'], 'datasets' => [['label' => 'T', 'data' => [1]]]]]
        );

        // Should not render the header border when no title
        $view->assertDontSee('border-b border-gray-100');
    }
}
