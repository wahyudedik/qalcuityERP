<?php

namespace Tests\Unit\Components\Layout;

use App\View\Components\Layout\ResponsiveGrid;
use Tests\TestCase;

/**
 * Unit tests for ResponsiveGrid Blade component
 *
 * @see Task 3.1: Create responsive-grid Blade component
 */
class ResponsiveGridTest extends TestCase
{
    // ─── 3.1.1 Column configuration with Tailwind classes ─────────────

    public function test_default_columns_produces_full_width(): void
    {
        $component = new ResponsiveGrid();

        $this->assertFalse($component->isAutoGrid);
        $this->assertEquals(['lg:w-full'], $component->processedColumns);
    }

    public function test_two_column_60_40_split(): void
    {
        $component = new ResponsiveGrid(columns: [60, 40]);

        $this->assertFalse($component->isAutoGrid);
        $this->assertEquals(['lg:w-3/5', 'lg:w-2/5'], $component->processedColumns);
    }

    public function test_two_column_50_50_split(): void
    {
        $component = new ResponsiveGrid(columns: [50, 50]);

        $this->assertEquals(['lg:w-1/2', 'lg:w-1/2'], $component->processedColumns);
    }

    public function test_three_column_33_33_34_split(): void
    {
        $component = new ResponsiveGrid(columns: [33, 33, 34]);

        $this->assertEquals(['lg:w-1/3', 'lg:w-1/3', 'lg:w-1/3'], $component->processedColumns);
    }

    public function test_two_column_75_25_split(): void
    {
        $component = new ResponsiveGrid(columns: [75, 25]);

        $this->assertEquals(['lg:w-3/4', 'lg:w-1/4'], $component->processedColumns);
    }

    public function test_two_column_65_35_split(): void
    {
        $component = new ResponsiveGrid(columns: [65, 35]);

        // 65 maps to 2/3, 35 is arbitrary so uses percentage
        $this->assertEquals(['lg:w-2/3', 'lg:w-[35%]'], $component->processedColumns);
    }

    public function test_auto_grid_mode(): void
    {
        $component = new ResponsiveGrid(columns: ['auto']);

        $this->assertTrue($component->isAutoGrid);
        $this->assertEquals(['auto'], $component->processedColumns);
    }

    public function test_arbitrary_percentage_uses_bracket_notation(): void
    {
        $component = new ResponsiveGrid(columns: [70, 30]);

        // 70 and 30 don't map to standard Tailwind fractions
        $this->assertEquals(['lg:w-[70%]', 'lg:w-[30%]'], $component->processedColumns);
    }

    // ─── 3.1.2 Breakpoint-specific behavior ──────────────────────────

    public function test_auto_grid_uses_breakpoint_columns(): void
    {
        $component = new ResponsiveGrid(
            columns: ['auto'],
            breakpoints: [
                'mobile' => ['columns' => 1],
                'tablet' => ['columns' => 3],
                'desktop' => ['columns' => 6],
            ]
        );

        $this->assertStringContainsString('grid-cols-1', $component->gridClasses);
        $this->assertStringContainsString('md:grid-cols-3', $component->gridClasses);
        $this->assertStringContainsString('lg:grid-cols-6', $component->gridClasses);
    }

    public function test_auto_grid_defaults_without_breakpoints(): void
    {
        $component = new ResponsiveGrid(columns: ['auto']);

        $this->assertStringContainsString('grid-cols-1', $component->gridClasses);
        $this->assertStringContainsString('md:grid-cols-2', $component->gridClasses);
        $this->assertStringContainsString('lg:grid-cols-4', $component->gridClasses);
    }

    public function test_flex_grid_uses_column_stacking_on_mobile(): void
    {
        $component = new ResponsiveGrid(columns: [60, 40], mobileStack: 'stack');

        $this->assertStringContainsString('flex', $component->gridClasses);
        $this->assertStringContainsString('flex-col', $component->gridClasses);
        $this->assertStringContainsString('lg:flex-row', $component->gridClasses);
    }

    public function test_flex_grid_scroll_mode(): void
    {
        $component = new ResponsiveGrid(columns: [60, 40], mobileStack: 'scroll');

        $this->assertStringContainsString('flex', $component->gridClasses);
        $this->assertStringNotContainsString('flex-col', $component->gridClasses);
    }

    // ─── 3.1.3 Gap and spacing configuration ─────────────────────────

    public function test_gap_none(): void
    {
        $component = new ResponsiveGrid(gap: 'none');

        $this->assertEquals('', $component->gapClasses);
    }

    public function test_gap_xs(): void
    {
        $component = new ResponsiveGrid(gap: 'xs');

        $this->assertEquals('gap-1', $component->gapClasses);
    }

    public function test_gap_sm(): void
    {
        $component = new ResponsiveGrid(gap: 'sm');

        $this->assertEquals('gap-2 lg:gap-3', $component->gapClasses);
    }

    public function test_gap_md_default(): void
    {
        $component = new ResponsiveGrid();

        $this->assertEquals('gap-4 lg:gap-6', $component->gapClasses);
    }

    public function test_gap_lg(): void
    {
        $component = new ResponsiveGrid(gap: 'lg');

        $this->assertEquals('gap-6 lg:gap-8', $component->gapClasses);
    }

    public function test_gap_xl(): void
    {
        $component = new ResponsiveGrid(gap: 'xl');

        $this->assertEquals('gap-8 lg:gap-10', $component->gapClasses);
    }

    public function test_custom_gap_class(): void
    {
        $component = new ResponsiveGrid(gap: 'gap-3 md:gap-5');

        $this->assertEquals('gap-3 md:gap-5', $component->gapClasses);
    }

    // ─── 3.1.4 Accessibility attributes ──────────────────────────────

    public function test_auto_grid_has_grid_role(): void
    {
        $component = new ResponsiveGrid(columns: ['auto']);

        $this->assertEquals('grid', $component->getRole());
    }

    public function test_flex_grid_has_region_role(): void
    {
        $component = new ResponsiveGrid(columns: [60, 40]);

        $this->assertEquals('region', $component->getRole());
    }

    public function test_custom_role_overrides_default(): void
    {
        $component = new ResponsiveGrid(columns: [60, 40], role: 'navigation');

        $this->assertEquals('navigation', $component->getRole());
    }

    public function test_aria_label_is_set(): void
    {
        $component = new ResponsiveGrid(ariaLabel: 'Konten utama halaman');

        $this->assertEquals('Konten utama halaman', $component->getAriaLabel());
    }

    public function test_aria_label_null_by_default(): void
    {
        $component = new ResponsiveGrid();

        $this->assertNull($component->getAriaLabel());
    }

    // ─── Render test ─────────────────────────────────────────────────

    public function test_component_renders_without_error(): void
    {
        $component = new ResponsiveGrid(columns: [60, 40], gap: 'md');

        $view = $component->render();

        $this->assertEquals('components.layout.responsive-grid', $view->name());
    }
}
