<?php

namespace Tests\Unit\DTOs\Layout;

use App\DTOs\Layout\PageLayout;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests untuk PageLayout DTO
 *
 * Memverifikasi:
 * - 1.2.1: Properti columns, widgets, dan breakpoints
 * - 1.2.2: Aturan validasi struktur layout
 * - 1.2.3: Metode serialisasi untuk penyimpanan
 */
class PageLayoutTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Sub-task 1.2.1: Properties
    // -------------------------------------------------------------------------

    #[Test]
    public function it_stores_all_required_properties(): void
    {
        $layout = new PageLayout(
            page: 'notifications',
            columns: [60, 40],
            widgets: [
                'main' => [['type' => 'notification-list', 'position' => 0]],
                'sidebar' => [['type' => 'summary', 'position' => 0]],
            ],
            breakpoints: [
                'mobile' => ['grid_columns' => 1, 'sidebar_collapsed' => true],
                'tablet' => ['grid_columns' => 2, 'sidebar_collapsed' => false],
                'desktop' => ['grid_columns' => 4, 'sidebar_collapsed' => false],
            ]
        );

        $this->assertSame('notifications', $layout->page);
        $this->assertSame([60, 40], $layout->columns);
        $this->assertArrayHasKey('main', $layout->widgets);
        $this->assertArrayHasKey('sidebar', $layout->widgets);
        $this->assertArrayHasKey('mobile', $layout->breakpoints);
        $this->assertArrayHasKey('tablet', $layout->breakpoints);
        $this->assertArrayHasKey('desktop', $layout->breakpoints);
    }

    #[Test]
    public function it_has_readonly_properties(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [100],
            widgets: [],
            breakpoints: []
        );

        $reflection = new \ReflectionClass($layout);

        $this->assertTrue($reflection->getProperty('page')->isReadOnly());
        $this->assertTrue($reflection->getProperty('columns')->isReadOnly());
        $this->assertTrue($reflection->getProperty('widgets')->isReadOnly());
        $this->assertTrue($reflection->getProperty('breakpoints')->isReadOnly());
    }

    #[Test]
    public function it_exposes_valid_breakpoints_constant(): void
    {
        $this->assertSame(['mobile', 'tablet', 'desktop'], PageLayout::VALID_BREAKPOINTS);
    }

    #[Test]
    public function it_exposes_required_widget_properties_constant(): void
    {
        $this->assertSame(['type', 'position'], PageLayout::REQUIRED_WIDGET_PROPERTIES);
    }

    // -------------------------------------------------------------------------
    // Sub-task 1.2.2: Validation rules
    // -------------------------------------------------------------------------

    #[Test]
    public function valid_layout_passes_validation(): void
    {
        $layout = new PageLayout(
            page: 'notifications',
            columns: [60, 40],
            widgets: [
                'main' => [['type' => 'list', 'position' => 0]],
            ],
            breakpoints: [
                'mobile' => ['grid_columns' => 1],
                'desktop' => ['grid_columns' => 4],
            ]
        );

        $this->assertTrue($layout->isValid());
        $this->assertEmpty($layout->getValidationErrors());
    }

    #[Test]
    public function empty_page_name_fails_validation(): void
    {
        $layout = new PageLayout(
            page: '',
            columns: [100],
            widgets: [],
            breakpoints: []
        );

        $this->assertFalse($layout->isValid());
        $this->assertContains('Nama halaman (page) tidak boleh kosong', $layout->getValidationErrors());
    }

    #[Test]
    public function whitespace_only_page_name_fails_validation(): void
    {
        $layout = new PageLayout(
            page: '   ',
            columns: [],
            widgets: [],
            breakpoints: []
        );

        $this->assertFalse($layout->isValid());
        $errors = $layout->getValidationErrors();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('page', $errors[0]);
    }

    #[Test]
    public function columns_must_be_numeric_indexed_array(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: ['left' => 60, 'right' => 40], // associative — invalid
            widgets: [],
            breakpoints: []
        );

        $this->assertFalse($layout->isValid());
        $errors = $layout->getValidationErrors();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('indexed array', $errors[0]);
    }

    #[Test]
    public function columns_values_must_be_numeric(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: ['sixty', 40], // non-numeric value
            widgets: [],
            breakpoints: []
        );

        $this->assertFalse($layout->isValid());
        $errors = $layout->getValidationErrors();
        $this->assertTrue(
            collect($errors)->contains(fn($e) => str_contains($e, 'numerik'))
        );
    }

    #[Test]
    public function columns_values_must_be_positive(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [0, 100], // zero is invalid
            widgets: [],
            breakpoints: []
        );

        $this->assertFalse($layout->isValid());
        $errors = $layout->getValidationErrors();
        $this->assertTrue(
            collect($errors)->contains(fn($e) => str_contains($e, 'lebih dari 0'))
        );
    }

    #[Test]
    public function columns_total_must_not_exceed_100_percent(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [60, 50], // total = 110 — invalid
            widgets: [],
            breakpoints: []
        );

        $this->assertFalse($layout->isValid());
        $errors = $layout->getValidationErrors();
        $this->assertTrue(
            collect($errors)->contains(fn($e) => str_contains($e, '100%'))
        );
    }

    #[Test]
    public function columns_total_exactly_100_is_valid(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [60, 40], // total = 100 — valid
            widgets: [],
            breakpoints: []
        );

        $this->assertTrue($layout->isValid());
    }

    #[Test]
    public function columns_total_less_than_100_is_valid(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [60, 30], // total = 90 — valid (allows gutters)
            widgets: [],
            breakpoints: []
        );

        $this->assertTrue($layout->isValid());
    }

    #[Test]
    public function empty_columns_is_valid(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [],
            widgets: [],
            breakpoints: []
        );

        $this->assertTrue($layout->isValid());
    }

    #[Test]
    public function widgets_must_have_type_property(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [],
            widgets: [
                ['position' => 0], // missing 'type'
            ],
            breakpoints: []
        );

        $this->assertFalse($layout->isValid());
        $errors = $layout->getValidationErrors();
        $this->assertTrue(
            collect($errors)->contains(fn($e) => str_contains($e, "'type'"))
        );
    }

    #[Test]
    public function widgets_must_have_position_property(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [],
            widgets: [
                ['type' => 'chart'], // missing 'position'
            ],
            breakpoints: []
        );

        $this->assertFalse($layout->isValid());
        $errors = $layout->getValidationErrors();
        $this->assertTrue(
            collect($errors)->contains(fn($e) => str_contains($e, "'position'"))
        );
    }

    #[Test]
    public function widgets_type_must_not_be_empty(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [],
            widgets: [
                ['type' => '', 'position' => 0], // empty type
            ],
            breakpoints: []
        );

        $this->assertFalse($layout->isValid());
        $errors = $layout->getValidationErrors();
        $this->assertTrue(
            collect($errors)->contains(fn($e) => str_contains($e, "'type'") && str_contains($e, 'kosong'))
        );
    }

    #[Test]
    public function widgets_in_area_format_are_validated(): void
    {
        $layout = new PageLayout(
            page: 'notifications',
            columns: [],
            widgets: [
                'main' => [
                    ['type' => 'list', 'position' => 0],
                    ['position' => 1], // missing 'type'
                ],
            ],
            breakpoints: []
        );

        $this->assertFalse($layout->isValid());
        $errors = $layout->getValidationErrors();
        $this->assertTrue(
            collect($errors)->contains(fn($e) => str_contains($e, 'main[1]') && str_contains($e, "'type'"))
        );
    }

    #[Test]
    public function valid_widgets_with_type_and_position_pass(): void
    {
        $layout = new PageLayout(
            page: 'notifications',
            columns: [],
            widgets: [
                'main' => [
                    ['type' => 'notification-list', 'position' => 0],
                    ['type' => 'summary', 'position' => 1],
                ],
                'sidebar' => [
                    ['type' => 'quick-actions', 'position' => 0],
                ],
            ],
            breakpoints: []
        );

        $this->assertTrue($layout->isValid());
    }

    #[Test]
    public function empty_widgets_is_valid(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [],
            widgets: [],
            breakpoints: []
        );

        $this->assertTrue($layout->isValid());
    }

    #[Test]
    public function breakpoints_must_use_valid_keys(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [],
            widgets: [],
            breakpoints: [
                'mobile' => ['grid_columns' => 1],
                'xl' => ['grid_columns' => 6], // invalid key
            ]
        );

        $this->assertFalse($layout->isValid());
        $errors = $layout->getValidationErrors();
        $this->assertTrue(
            collect($errors)->contains(fn($e) => str_contains($e, 'xl'))
        );
    }

    #[Test]
    public function breakpoints_with_all_valid_keys_passes(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [],
            widgets: [],
            breakpoints: [
                'mobile' => ['grid_columns' => 1, 'sidebar_collapsed' => true],
                'tablet' => ['grid_columns' => 2, 'sidebar_collapsed' => false],
                'desktop' => ['grid_columns' => 4, 'sidebar_collapsed' => false],
            ]
        );

        $this->assertTrue($layout->isValid());
    }

    #[Test]
    public function breakpoints_with_subset_of_valid_keys_passes(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [],
            widgets: [],
            breakpoints: [
                'desktop' => ['grid_columns' => 4],
            ]
        );

        $this->assertTrue($layout->isValid());
    }

    #[Test]
    public function empty_breakpoints_is_valid(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [],
            widgets: [],
            breakpoints: []
        );

        $this->assertTrue($layout->isValid());
    }

    #[Test]
    public function multiple_invalid_breakpoint_keys_are_all_reported(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [],
            widgets: [],
            breakpoints: [
                'sm' => [],
                'md' => [],
                'lg' => [],
            ]
        );

        $this->assertFalse($layout->isValid());
        $errors = $layout->getValidationErrors();
        $errorText = implode(' ', $errors);
        $this->assertStringContainsString('sm', $errorText);
        $this->assertStringContainsString('md', $errorText);
        $this->assertStringContainsString('lg', $errorText);
    }

    // -------------------------------------------------------------------------
    // Sub-task 1.2.3: Serialization methods
    // -------------------------------------------------------------------------

    #[Test]
    public function to_array_returns_all_properties(): void
    {
        $layout = new PageLayout(
            page: 'notifications',
            columns: [60, 40],
            widgets: ['main' => [['type' => 'list', 'position' => 0]]],
            breakpoints: ['desktop' => ['grid_columns' => 4]]
        );

        $array = $layout->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('page', $array);
        $this->assertArrayHasKey('columns', $array);
        $this->assertArrayHasKey('widgets', $array);
        $this->assertArrayHasKey('breakpoints', $array);
        $this->assertSame('notifications', $array['page']);
        $this->assertSame([60, 40], $array['columns']);
    }

    #[Test]
    public function from_array_creates_instance_with_correct_properties(): void
    {
        $data = [
            'page' => 'anomalies',
            'columns' => [65, 35],
            'widgets' => ['sidebar' => [['type' => 'stats', 'position' => 0]]],
            'breakpoints' => ['mobile' => ['grid_columns' => 1]],
        ];

        $layout = PageLayout::fromArray($data);

        $this->assertInstanceOf(PageLayout::class, $layout);
        $this->assertSame('anomalies', $layout->page);
        $this->assertSame([65, 35], $layout->columns);
        $this->assertSame($data['widgets'], $layout->widgets);
        $this->assertSame($data['breakpoints'], $layout->breakpoints);
    }

    #[Test]
    public function from_array_uses_defaults_for_missing_keys(): void
    {
        $layout = PageLayout::fromArray([]);

        $this->assertSame('', $layout->page);
        $this->assertSame([], $layout->columns);
        $this->assertSame([], $layout->widgets);
        $this->assertSame([], $layout->breakpoints);
    }

    #[Test]
    public function to_array_and_from_array_are_inverse_operations(): void
    {
        $original = new PageLayout(
            page: 'simulations',
            columns: [60, 40],
            widgets: [
                'main' => [['type' => 'simulation-card', 'position' => 0]],
                'sidebar' => [['type' => 'history', 'position' => 0]],
            ],
            breakpoints: [
                'mobile' => ['grid_columns' => 1, 'sidebar_collapsed' => true],
                'desktop' => ['grid_columns' => 4, 'sidebar_collapsed' => false],
            ]
        );

        $restored = PageLayout::fromArray($original->toArray());

        $this->assertSame($original->page, $restored->page);
        $this->assertSame($original->columns, $restored->columns);
        $this->assertSame($original->widgets, $restored->widgets);
        $this->assertSame($original->breakpoints, $restored->breakpoints);
    }

    #[Test]
    public function to_json_returns_valid_json_string(): void
    {
        $layout = new PageLayout(
            page: 'reports',
            columns: [100],
            widgets: [],
            breakpoints: []
        );

        $json = $layout->toJson();

        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertNotNull($decoded);
        $this->assertSame('reports', $decoded['page']);
    }

    #[Test]
    public function to_json_preserves_unicode_characters(): void
    {
        $layout = new PageLayout(
            page: 'laporan-keuangan',
            columns: [100],
            widgets: [],
            breakpoints: []
        );

        $json = $layout->toJson();

        // JSON_UNESCAPED_UNICODE should preserve non-ASCII characters without escaping
        $this->assertStringNotContainsString('\\u', $json);
    }

    #[Test]
    public function from_json_creates_instance_from_valid_json(): void
    {
        $json = '{"page":"notifications","columns":[60,40],"widgets":{},"breakpoints":{"desktop":{"grid_columns":4}}}';

        $layout = PageLayout::fromJson($json);

        $this->assertInstanceOf(PageLayout::class, $layout);
        $this->assertSame('notifications', $layout->page);
        $this->assertSame([60, 40], $layout->columns);
    }

    #[Test]
    public function from_json_throws_on_invalid_json(): void
    {
        $this->expectException(\JsonException::class);

        PageLayout::fromJson('not-valid-json{{{');
    }

    #[Test]
    public function to_json_and_from_json_are_inverse_operations(): void
    {
        $original = new PageLayout(
            page: 'room-availability',
            columns: [100],
            widgets: [
                'main' => [['type' => 'room-grid', 'position' => 0]],
            ],
            breakpoints: [
                'mobile' => ['grid_columns' => 1],
                'tablet' => ['grid_columns' => 3],
                'desktop' => ['grid_columns' => 6],
            ]
        );

        $restored = PageLayout::fromJson($original->toJson());

        $this->assertSame($original->page, $restored->page);
        $this->assertSame($original->columns, $restored->columns);
        $this->assertSame($original->widgets, $restored->widgets);
        $this->assertSame($original->breakpoints, $restored->breakpoints);
    }

    // -------------------------------------------------------------------------
    // Helper method tests
    // -------------------------------------------------------------------------

    #[Test]
    public function get_columns_for_breakpoint_returns_breakpoint_specific_columns(): void
    {
        $layout = new PageLayout(
            page: 'notifications',
            columns: [60, 40],
            breakpoints: [
                'mobile' => ['columns' => [100]],
                'desktop' => ['columns' => [60, 40]],
            ],
            widgets: []
        );

        $this->assertSame([100], $layout->getColumnsForBreakpoint('mobile'));
        $this->assertSame([60, 40], $layout->getColumnsForBreakpoint('desktop'));
    }

    #[Test]
    public function get_columns_for_breakpoint_falls_back_to_default_columns(): void
    {
        $layout = new PageLayout(
            page: 'notifications',
            columns: [60, 40],
            breakpoints: [],
            widgets: []
        );

        $this->assertSame([60, 40], $layout->getColumnsForBreakpoint('tablet'));
    }

    #[Test]
    public function is_sidebar_collapsed_returns_correct_value(): void
    {
        $layout = new PageLayout(
            page: 'notifications',
            columns: [],
            widgets: [],
            breakpoints: [
                'mobile' => ['sidebar_collapsed' => true],
                'desktop' => ['sidebar_collapsed' => false],
            ]
        );

        $this->assertTrue($layout->isSidebarCollapsed('mobile'));
        $this->assertFalse($layout->isSidebarCollapsed('desktop'));
        $this->assertFalse($layout->isSidebarCollapsed('tablet')); // default
    }

    #[Test]
    public function get_grid_columns_returns_correct_value(): void
    {
        $layout = new PageLayout(
            page: 'room-availability',
            columns: [],
            widgets: [],
            breakpoints: [
                'mobile' => ['grid_columns' => 1],
                'tablet' => ['grid_columns' => 3],
                'desktop' => ['grid_columns' => 6],
            ]
        );

        $this->assertSame(1, $layout->getGridColumns('mobile'));
        $this->assertSame(3, $layout->getGridColumns('tablet'));
        $this->assertSame(6, $layout->getGridColumns('desktop'));
        $this->assertSame(4, $layout->getGridColumns('unknown')); // default
    }

    #[Test]
    public function get_widgets_for_area_returns_correct_widgets(): void
    {
        $mainWidgets = [['type' => 'list', 'position' => 0]];
        $sidebarWidgets = [['type' => 'summary', 'position' => 0]];

        $layout = new PageLayout(
            page: 'notifications',
            columns: [],
            widgets: [
                'main' => $mainWidgets,
                'sidebar' => $sidebarWidgets,
            ],
            breakpoints: []
        );

        $this->assertSame($mainWidgets, $layout->getWidgetsForArea('main'));
        $this->assertSame($sidebarWidgets, $layout->getWidgetsForArea('sidebar'));
        $this->assertSame([], $layout->getWidgetsForArea('nonexistent'));
    }

    #[Test]
    public function has_widgets_in_area_returns_correct_boolean(): void
    {
        $layout = new PageLayout(
            page: 'notifications',
            columns: [],
            widgets: [
                'main' => [['type' => 'list', 'position' => 0]],
                'empty' => [],
            ],
            breakpoints: []
        );

        $this->assertTrue($layout->hasWidgetsInArea('main'));
        $this->assertFalse($layout->hasWidgetsInArea('empty'));
        $this->assertFalse($layout->hasWidgetsInArea('nonexistent'));
    }

    #[Test]
    public function get_active_areas_returns_only_areas_with_widgets(): void
    {
        $layout = new PageLayout(
            page: 'notifications',
            columns: [],
            widgets: [
                'main' => [['type' => 'list', 'position' => 0]],
                'sidebar' => [['type' => 'summary', 'position' => 0]],
                'footer' => [],
            ],
            breakpoints: []
        );

        $activeAreas = $layout->getActiveAreas();

        $this->assertContains('main', $activeAreas);
        $this->assertContains('sidebar', $activeAreas);
        $this->assertNotContains('footer', $activeAreas);
    }

    #[Test]
    public function get_grid_class_returns_correct_tailwind_class(): void
    {
        $layout = new PageLayout(
            page: 'room-availability',
            columns: [],
            widgets: [],
            breakpoints: [
                'mobile' => ['grid_columns' => 1],
                'tablet' => ['grid_columns' => 3],
                'desktop' => ['grid_columns' => 4],
            ]
        );

        $this->assertSame('grid-cols-1', $layout->getGridClass('mobile'));
        $this->assertSame('md:grid-cols-3', $layout->getGridClass('tablet'));
        $this->assertSame('lg:grid-cols-4', $layout->getGridClass('desktop'));
    }
}



