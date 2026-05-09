<?php

namespace Tests\Unit\Widgets;

use App\Models\User;
use App\Widgets\AbstractWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbstractWidgetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test widget dapat dibuat dengan konfigurasi default.
     */
    public function test_widget_can_be_created_with_default_config(): void
    {
        $widget = new TestWidget('test-page');

        $this->assertEquals('test-page', $widget->getPage());
        $this->assertEquals('test-widget', $widget->getType());
        $this->assertIsArray($widget->getConfig());
    }

    /**
     * Test widget dapat dibuat dengan konfigurasi custom.
     */
    public function test_widget_can_be_created_with_custom_config(): void
    {
        $config = [
            'title' => 'Test Widget',
            'color' => 'red',
            'refreshInterval' => 60,
        ];

        $widget = new TestWidget('test-page', $config);

        $this->assertEquals('Test Widget', $widget->getConfigValue('title'));
        $this->assertEquals('red', $widget->getConfigValue('color'));
        $this->assertEquals(60, $widget->getConfigValue('refreshInterval'));
    }

    /**
     * Test widget dapat mengambil dan set nilai konfigurasi.
     */
    public function test_widget_can_get_and_set_config_values(): void
    {
        $widget = new TestWidget('test-page');

        $widget->setConfigValue('customKey', 'customValue');

        $this->assertEquals('customValue', $widget->getConfigValue('customKey'));
        $this->assertNull($widget->getConfigValue('nonExistentKey'));
        $this->assertEquals('default', $widget->getConfigValue('nonExistentKey', 'default'));
    }

    /**
     * Test widget dapat dikonversi ke array.
     */
    public function test_widget_can_be_converted_to_array(): void
    {
        $widget = new TestWidget('test-page', ['title' => 'Test']);

        $array = $widget->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('test-widget', $array['type']);
        $this->assertEquals('test-page', $array['page']);
        $this->assertArrayHasKey('config', $array);
        $this->assertArrayHasKey('data', $array);
    }

    /**
     * Test widget dapat dikonversi ke JSON.
     */
    public function test_widget_can_be_converted_to_json(): void
    {
        $widget = new TestWidget('test-page');

        $json = $widget->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals('test-widget', $decoded['type']);
    }

    /**
     * Test format number dengan suffix.
     */
    public function test_format_number_with_suffix(): void
    {
        $widget = new TestWidget('test-page');

        // Menggunakan reflection untuk mengakses protected method
        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('formatNumber');
        $method->setAccessible(true);

        $this->assertEquals('1.5K', $method->invoke($widget, 1500));
        $this->assertEquals('2.5M', $method->invoke($widget, 2500000));
        $this->assertEquals('3.5B', $method->invoke($widget, 3500000000));
        $this->assertEquals('500.0', $method->invoke($widget, 500));
    }

    /**
     * Test format percentage.
     */
    public function test_format_percentage(): void
    {
        $widget = new TestWidget('test-page');

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('formatPercentage');
        $method->setAccessible(true);

        $this->assertEquals('+15.5%', $method->invoke($widget, 15.5));
        $this->assertEquals('-10.2%', $method->invoke($widget, -10.2));
        $this->assertEquals('+0.0%', $method->invoke($widget, 0));
    }

    /**
     * Test trend indicator.
     */
    public function test_trend_indicator(): void
    {
        $widget = new TestWidget('test-page');

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getTrendIndicator');
        $method->setAccessible(true);

        $this->assertEquals('up', $method->invoke($widget, 10));
        $this->assertEquals('down', $method->invoke($widget, -10));
        $this->assertEquals('neutral', $method->invoke($widget, 0));
    }

    /**
     * Test trend color.
     */
    public function test_trend_color(): void
    {
        $widget = new TestWidget('test-page');

        $reflection = new \ReflectionClass($widget);
        $method = $reflection->getMethod('getTrendColor');
        $method->setAccessible(true);

        $this->assertEquals('green', $method->invoke($widget, 10, false));
        $this->assertEquals('red', $method->invoke($widget, -10, false));
        $this->assertEquals('gray', $method->invoke($widget, 0, false));

        // Test inverse
        $this->assertEquals('red', $method->invoke($widget, 10, true));
        $this->assertEquals('green', $method->invoke($widget, -10, true));
    }

    /**
     * Test widget menangani error dengan graceful.
     */
    public function test_widget_handles_errors_gracefully(): void
    {
        // Login sebagai user untuk bypass permission check
        $user = User::factory()->create();
        $this->actingAs($user);

        $widget = new ErrorWidget('test-page');

        $data = $widget->getDataSafely();

        $this->assertTrue($data['error']);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('error', $data['type']);
    }

    /**
     * Test widget mengembalikan empty state.
     */
    public function test_widget_returns_empty_state(): void
    {
        $widget = new EmptyWidget('test-page');

        $data = $widget->getData();

        $this->assertTrue($data['empty']);
        $this->assertArrayHasKey('message', $data);
    }
}

/**
 * Test widget implementation untuk testing.
 */
class TestWidget extends AbstractWidget
{
    public function getData(): array
    {
        return [
            'value' => 100,
            'label' => 'Test Data',
        ];
    }

    public function getType(): string
    {
        return 'test-widget';
    }
}

/**
 * Widget yang selalu throw error untuk testing error handling.
 */
class ErrorWidget extends AbstractWidget
{
    public function getData(): array
    {
        throw new \Exception('Test error');
    }

    public function getType(): string
    {
        return 'error-widget';
    }
}

/**
 * Widget yang mengembalikan empty state.
 */
class EmptyWidget extends AbstractWidget
{
    public function getData(): array
    {
        return $this->getEmptyState('No data available');
    }

    public function getType(): string
    {
        return 'empty-widget';
    }
}
