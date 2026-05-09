<?php

namespace Tests\Unit\Components\Widget;

use App\View\Components\Widget\Statistics;
use Tests\TestCase;

/**
 * Unit tests for StatisticsWidget Blade component
 *
 * @see Task 4.1: Create StatisticsWidget component
 */
class StatisticsTest extends TestCase
{
    // ─── 4.1.1 Number display with formatting (K, M, B suffixes) ──

    public function test_format_number_returns_zero_for_null(): void
    {
        $this->assertEquals('0', Statistics::formatNumber(null));
    }

    public function test_format_number_returns_zero_for_empty_string(): void
    {
        $this->assertEquals('0', Statistics::formatNumber(''));
    }

    public function test_format_number_below_thousand(): void
    {
        $this->assertEquals('500', Statistics::formatNumber(500));
        $this->assertEquals('0', Statistics::formatNumber(0));
        $this->assertEquals('999', Statistics::formatNumber(999));
    }

    public function test_format_number_thousands_with_k_suffix(): void
    {
        $this->assertEquals('1K', Statistics::formatNumber(1000));
        $this->assertEquals('1.5K', Statistics::formatNumber(1500));
        $this->assertEquals('10K', Statistics::formatNumber(10000));
        $this->assertEquals('999.9K', Statistics::formatNumber(999900));
    }

    public function test_format_number_millions_with_m_suffix(): void
    {
        $this->assertEquals('1M', Statistics::formatNumber(1000000));
        $this->assertEquals('2.5M', Statistics::formatNumber(2500000));
        $this->assertEquals('999.9M', Statistics::formatNumber(999900000));
    }

    public function test_format_number_billions_with_b_suffix(): void
    {
        $this->assertEquals('1B', Statistics::formatNumber(1000000000));
        $this->assertEquals('3.5B', Statistics::formatNumber(3500000000));
    }

    public function test_format_number_handles_string_input(): void
    {
        $this->assertEquals('1.5K', Statistics::formatNumber('1500'));
        $this->assertEquals('2M', Statistics::formatNumber('2000000'));
    }

    public function test_format_number_handles_negative_values(): void
    {
        $this->assertEquals('-1.5K', Statistics::formatNumber(-1500));
        $this->assertEquals('-2M', Statistics::formatNumber(-2000000));
    }

    public function test_format_number_removes_trailing_zeros(): void
    {
        $this->assertEquals('1K', Statistics::formatNumber(1000));
        $this->assertEquals('2M', Statistics::formatNumber(2000000));
        $this->assertEquals('3B', Statistics::formatNumber(3000000000));
    }

    // ─── 4.1.2 Trend indicators (up/down arrows with percentages) ─

    public function test_trend_direction_up_for_positive(): void
    {
        $this->assertEquals('up', Statistics::getTrendDirection(12.5));
        $this->assertEquals('up', Statistics::getTrendDirection(0.1));
    }

    public function test_trend_direction_down_for_negative(): void
    {
        $this->assertEquals('down', Statistics::getTrendDirection(-5.2));
        $this->assertEquals('down', Statistics::getTrendDirection(-0.1));
    }

    public function test_trend_direction_neutral_for_zero(): void
    {
        $this->assertEquals('neutral', Statistics::getTrendDirection(0));
        $this->assertEquals('neutral', Statistics::getTrendDirection(0.0));
    }

    public function test_format_trend_positive(): void
    {
        $this->assertEquals('+12.5%', Statistics::formatTrend(12.5));
        $this->assertEquals('+0.1%', Statistics::formatTrend(0.1));
    }

    public function test_format_trend_negative(): void
    {
        $this->assertEquals('-5.2%', Statistics::formatTrend(-5.2));
        $this->assertEquals('-100.0%', Statistics::formatTrend(-100.0));
    }

    public function test_format_trend_zero(): void
    {
        $this->assertEquals('0%', Statistics::formatTrend(0));
    }

    // ─── 4.1.3 Color coding for positive/negative values ──────────

    public function test_trend_color_green_for_positive(): void
    {
        $this->assertEquals('text-green-600', Statistics::getTrendColorClass(10));
    }

    public function test_trend_color_red_for_negative(): void
    {
        $this->assertEquals('text-red-600', Statistics::getTrendColorClass(-10));
    }

    public function test_trend_color_gray_for_neutral(): void
    {
        $this->assertEquals('text-gray-500', Statistics::getTrendColorClass(0));
    }

    public function test_trend_color_inverse_red_for_positive(): void
    {
        $this->assertEquals('text-red-600', Statistics::getTrendColorClass(10, true));
    }

    public function test_trend_color_inverse_green_for_negative(): void
    {
        $this->assertEquals('text-green-600', Statistics::getTrendColorClass(-10, true));
    }

    public function test_trend_bg_green_for_positive(): void
    {
        $this->assertEquals('bg-green-50', Statistics::getTrendBgClass(10));
    }

    public function test_trend_bg_red_for_negative(): void
    {
        $this->assertEquals('bg-red-50', Statistics::getTrendBgClass(-10));
    }

    public function test_trend_bg_gray_for_neutral(): void
    {
        $this->assertEquals('bg-gray-50', Statistics::getTrendBgClass(0));
    }

    // ─── 4.1.4 Loading skeleton and error states ──────────────────

    public function test_default_loading_state_is_false(): void
    {
        $component = new Statistics();

        $this->assertFalse($component->loading);
    }

    public function test_loading_state_can_be_set_to_true(): void
    {
        $component = new Statistics(loading: true);

        $this->assertTrue($component->loading);
    }

    public function test_default_error_state_is_false(): void
    {
        $component = new Statistics();

        $this->assertFalse($component->error);
    }

    public function test_error_state_can_be_set_to_true(): void
    {
        $component = new Statistics(error: true);

        $this->assertTrue($component->error);
    }

    public function test_default_error_message(): void
    {
        $component = new Statistics();

        $this->assertEquals('Gagal memuat statistik', $component->getErrorMessage());
    }

    public function test_custom_error_message(): void
    {
        $component = new Statistics(errorMessage: 'Koneksi terputus');

        $this->assertEquals('Koneksi terputus', $component->getErrorMessage());
    }

    // ─── Component construction and data formatting ───────────────

    public function test_component_formats_stats_correctly(): void
    {
        $stats = [
            ['label' => 'Total', 'value' => 1250, 'trend' => 12.5, 'icon' => 'bell'],
        ];

        $component = new Statistics(stats: $stats);

        $this->assertCount(1, $component->formattedStats);
        $this->assertEquals('Total', $component->formattedStats[0]['label']);
        $this->assertEquals('1.3K', $component->formattedStats[0]['formattedValue']);
        $this->assertEquals('+12.5%', $component->formattedStats[0]['formattedTrend']);
        $this->assertEquals('up', $component->formattedStats[0]['trendDirection']);
        $this->assertEquals('text-green-600', $component->formattedStats[0]['trendColorClass']);
        $this->assertEquals('bg-green-50', $component->formattedStats[0]['trendBgClass']);
        $this->assertEquals('bell', $component->formattedStats[0]['icon']);
    }

    public function test_component_handles_multiple_stats(): void
    {
        $stats = [
            ['label' => 'Total Notifikasi', 'value' => 1250, 'trend' => 12.5, 'icon' => 'bell'],
            ['label' => 'Belum Dibaca', 'value' => 45, 'trend' => -5.2, 'icon' => 'envelope'],
            ['label' => 'Prioritas Tinggi', 'value' => 8, 'trend' => 0, 'icon' => 'exclamation'],
        ];

        $component = new Statistics(stats: $stats);

        $this->assertCount(3, $component->formattedStats);
        $this->assertEquals('down', $component->formattedStats[1]['trendDirection']);
        $this->assertEquals('text-red-600', $component->formattedStats[1]['trendColorClass']);
        $this->assertEquals('neutral', $component->formattedStats[2]['trendDirection']);
    }

    public function test_component_handles_empty_stats(): void
    {
        $component = new Statistics(stats: []);

        $this->assertCount(0, $component->formattedStats);
    }

    public function test_component_handles_stats_with_missing_fields(): void
    {
        $stats = [
            ['label' => 'Test'],
        ];

        $component = new Statistics(stats: $stats);

        $this->assertEquals('Test', $component->formattedStats[0]['label']);
        $this->assertEquals('0', $component->formattedStats[0]['formattedValue']);
        $this->assertEquals('0%', $component->formattedStats[0]['formattedTrend']);
        $this->assertEquals('neutral', $component->formattedStats[0]['trendDirection']);
        $this->assertNull($component->formattedStats[0]['icon']);
    }

    public function test_component_supports_prefix_and_suffix(): void
    {
        $stats = [
            ['label' => 'Revenue', 'value' => 5000000, 'trend' => 8.3, 'prefix' => 'Rp', 'suffix' => '/bln'],
        ];

        $component = new Statistics(stats: $stats);

        $this->assertEquals('Rp', $component->formattedStats[0]['prefix']);
        $this->assertEquals('/bln', $component->formattedStats[0]['suffix']);
    }

    // ─── Grid layout classes ──────────────────────────────────────

    public function test_grid_classes_single_stat(): void
    {
        $stats = [['label' => 'A', 'value' => 1]];
        $component = new Statistics(stats: $stats);

        $this->assertEquals('grid-cols-1', $component->gridClasses);
    }

    public function test_grid_classes_two_stats(): void
    {
        $stats = [
            ['label' => 'A', 'value' => 1],
            ['label' => 'B', 'value' => 2],
        ];
        $component = new Statistics(stats: $stats);

        $this->assertEquals('grid-cols-1 sm:grid-cols-2', $component->gridClasses);
    }

    public function test_grid_classes_three_stats(): void
    {
        $stats = [
            ['label' => 'A', 'value' => 1],
            ['label' => 'B', 'value' => 2],
            ['label' => 'C', 'value' => 3],
        ];
        $component = new Statistics(stats: $stats);

        $this->assertEquals('grid-cols-1 sm:grid-cols-2 lg:grid-cols-3', $component->gridClasses);
    }

    public function test_grid_classes_four_or_more_stats(): void
    {
        $stats = [
            ['label' => 'A', 'value' => 1],
            ['label' => 'B', 'value' => 2],
            ['label' => 'C', 'value' => 3],
            ['label' => 'D', 'value' => 4],
        ];
        $component = new Statistics(stats: $stats);

        $this->assertEquals('grid-cols-1 sm:grid-cols-2 lg:grid-cols-4', $component->gridClasses);
    }

    public function test_explicit_columns_override_auto_detection(): void
    {
        $stats = [
            ['label' => 'A', 'value' => 1],
            ['label' => 'B', 'value' => 2],
        ];
        $component = new Statistics(stats: $stats, columns: 3);

        $this->assertEquals('grid-cols-1 sm:grid-cols-2 lg:grid-cols-3', $component->gridClasses);
    }

    // ─── Render test ─────────────────────────────────────────────

    public function test_component_renders_without_error(): void
    {
        $stats = [
            ['label' => 'Total', 'value' => 1250, 'trend' => 12.5, 'icon' => 'bell'],
        ];

        $component = new Statistics(stats: $stats, title: 'Ringkasan');
        $view = $component->render();

        $this->assertEquals('components.widget.statistics', $view->name());
    }

    public function test_component_renders_with_minimal_config(): void
    {
        $component = new Statistics();
        $view = $component->render();

        $this->assertEquals('components.widget.statistics', $view->name());
    }

    // ─── Blade rendering integration ─────────────────────────────

    public function test_blade_renders_title(): void
    {
        $view = $this->blade(
            '<x-widget.statistics :stats="$stats" title="Ringkasan Notifikasi" />',
            ['stats' => [['label' => 'Total', 'value' => 100, 'trend' => 5.0]]]
        );

        $view->assertSee('Ringkasan Notifikasi');
    }

    public function test_blade_renders_formatted_value(): void
    {
        $view = $this->blade(
            '<x-widget.statistics :stats="$stats" />',
            ['stats' => [['label' => 'Users', 'value' => 1500, 'trend' => 0]]]
        );

        $view->assertSee('1.5K');
    }

    public function test_blade_renders_stat_label(): void
    {
        $view = $this->blade(
            '<x-widget.statistics :stats="$stats" />',
            ['stats' => [['label' => 'Total Notifikasi', 'value' => 50, 'trend' => 0]]]
        );

        $view->assertSee('Total Notifikasi');
    }

    public function test_blade_renders_positive_trend(): void
    {
        $view = $this->blade(
            '<x-widget.statistics :stats="$stats" />',
            ['stats' => [['label' => 'Test', 'value' => 100, 'trend' => 12.5]]]
        );

        $view->assertSee('+12.5%');
        $view->assertSee('text-green-600', false);
    }

    public function test_blade_renders_negative_trend(): void
    {
        $view = $this->blade(
            '<x-widget.statistics :stats="$stats" />',
            ['stats' => [['label' => 'Test', 'value' => 100, 'trend' => -5.2]]]
        );

        $view->assertSee('-5.2%');
        $view->assertSee('text-red-600', false);
    }

    public function test_blade_renders_neutral_trend(): void
    {
        $view = $this->blade(
            '<x-widget.statistics :stats="$stats" />',
            ['stats' => [['label' => 'Test', 'value' => 100, 'trend' => 0]]]
        );

        $view->assertSee('0%');
        $view->assertSee('text-gray-500', false);
    }

    public function test_blade_renders_loading_skeleton(): void
    {
        $view = $this->blade(
            '<x-widget.statistics :stats="$stats" :loading="true" />',
            ['stats' => [['label' => 'Test', 'value' => 100, 'trend' => 0]]]
        );

        $view->assertSee('animate-pulse');
    }

    public function test_blade_renders_error_state(): void
    {
        $view = $this->blade(
            '<x-widget.statistics :stats="$stats" :error="true" />',
            ['stats' => []]
        );

        $view->assertSee('Gagal memuat statistik');
        $view->assertSee('Coba Lagi');
    }

    public function test_blade_renders_custom_error_message(): void
    {
        $view = $this->blade(
            '<x-widget.statistics :stats="$stats" :error="true" error-message="Server tidak merespons" />',
            ['stats' => []]
        );

        $view->assertSee('Server tidak merespons');
    }

    public function test_blade_renders_empty_state_message(): void
    {
        $view = $this->blade(
            '<x-widget.statistics :stats="$stats" />',
            ['stats' => []]
        );

        $view->assertSee('Tidak ada data statistik');
    }

    public function test_blade_renders_aria_attributes(): void
    {
        $view = $this->blade(
            '<x-widget.statistics :stats="$stats" title="Ringkasan" />',
            ['stats' => [['label' => 'Test', 'value' => 100, 'trend' => 5.0]]]
        );

        $view->assertSee('role="region"', false);
        $view->assertSee('Statistik: Ringkasan', false);
    }

    public function test_blade_renders_icon(): void
    {
        $view = $this->blade(
            '<x-widget.statistics :stats="$stats" />',
            ['stats' => [['label' => 'Notif', 'value' => 10, 'trend' => 0, 'icon' => 'bell']]]
        );

        $view->assertSee('bg-blue-50', false);
    }

    public function test_blade_renders_multiple_stats_in_grid(): void
    {
        $view = $this->blade(
            '<x-widget.statistics :stats="$stats" />',
            ['stats' => [
                ['label' => 'Stat A', 'value' => 100, 'trend' => 5.0],
                ['label' => 'Stat B', 'value' => 200, 'trend' => -3.0],
            ]]
        );

        $view->assertSee('Stat A');
        $view->assertSee('Stat B');
        $view->assertSee('sm:grid-cols-2', false);
    }

    public function test_blade_renders_alpine_data_attribute(): void
    {
        $view = $this->blade(
            '<x-widget.statistics :stats="$stats" />',
            ['stats' => [['label' => 'Test', 'value' => 100, 'trend' => 0]]]
        );

        $view->assertSee('statisticsWidget', false);
    }
}
