<?php

namespace App\View\Components\Widget;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * ChartWidget - Komponen untuk menampilkan grafik menggunakan Chart.js
 *
 * Menyediakan widget chart dengan fitur:
 * - Line chart untuk trends dan time series
 * - Bar chart untuk comparisons dan categories
 * - Pie/Doughnut chart untuk distributions
 * - Responsive chart sizing dan mobile optimization
 * - Lazy loading dengan intersection observer
 * - Loading skeleton dan error states
 * - Data caching support
 *
 * Usage:
 * <x-widget.chart type="line" :data="$chartData" title="Tren Notifikasi 7 Hari" height="200" />
 *
 * Data format:
 * $chartData = [
 *     'labels' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
 *     'datasets' => [
 *         [
 *             'label' => 'Notifikasi',
 *             'data' => [12, 19, 3, 5, 2, 3, 7],
 *             'borderColor' => '#3B82F6',
 *             'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
 *         ]
 *     ]
 * ];
 *
 * @see Task 4.2: Create ChartWidget component using Chart.js
 * @see Requirements 7 (Sistem Widget Management)
 * @see Requirements 8 (Performance dan Loading Optimization)
 */
class Chart extends Component
{
    /**
     * Unique identifier for this chart instance.
     */
    public string $chartId;

    /**
     * JSON-encoded chart data for JavaScript consumption.
     */
    public string $chartDataJson;

    /**
     * JSON-encoded chart options for JavaScript consumption.
     */
    public string $chartOptionsJson;

    /**
     * Validated chart type.
     */
    public string $validatedType;

    /**
     * Supported chart types.
     */
    public const SUPPORTED_TYPES = ['line', 'bar', 'pie', 'doughnut'];

    /**
     * Default colors for datasets that don't specify colors.
     */
    public const DEFAULT_COLORS = [
        '#3B82F6', // blue-500
        '#10B981', // emerald-500
        '#F59E0B', // amber-500
        '#EF4444', // red-500
        '#8B5CF6', // violet-500
        '#06B6D4', // cyan-500
        '#F97316', // orange-500
        '#EC4899', // pink-500
    ];

    /**
     * Konstruktor ChartWidget component
     *
     * @param  string      $type         Chart type: line, bar, pie, doughnut
     * @param  array       $data         Chart data with labels and datasets
     * @param  string      $title        Widget title
     * @param  int|string  $height       Chart height in pixels
     * @param  bool        $loading      Initial loading state
     * @param  bool        $error        Initial error state
     * @param  string|null $errorMessage Custom error message
     * @param  bool        $lazyLoad     Enable lazy loading with intersection observer
     * @param  array       $options      Additional Chart.js options
     * @param  string|null $cacheKey     Cache key for data caching
     * @param  int         $cacheTtl     Cache TTL in seconds (default 5 minutes)
     */
    public function __construct(
        public string $type = 'line',
        public array $data = [],
        public string $title = '',
        public int|string $height = 200,
        public bool $loading = false,
        public bool $error = false,
        public ?string $errorMessage = null,
        public bool $lazyLoad = true,
        public array $options = [],
        public ?string $cacheKey = null,
        public int $cacheTtl = 300,
    ) {
        $this->validatedType = $this->validateType($type);
        $this->chartId = 'chart-' . uniqid();
        $this->chartDataJson = $this->buildChartDataJson();
        $this->chartOptionsJson = $this->buildChartOptionsJson();
    }

    /**
     * Validate and normalize chart type.
     */
    public static function validateType(string $type): string
    {
        $type = strtolower(trim($type));

        return in_array($type, self::SUPPORTED_TYPES) ? $type : 'line';
    }

    /**
     * Check if the chart type is circular (pie/doughnut).
     */
    public static function isCircularType(string $type): bool
    {
        return in_array($type, ['pie', 'doughnut']);
    }

    /**
     * Build the chart data JSON with default colors applied.
     */
    private function buildChartDataJson(): string
    {
        $data = $this->normalizeData($this->data);

        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * Normalize chart data, applying default colors where missing.
     */
    public static function normalizeData(array $data): array
    {
        if (empty($data)) {
            return ['labels' => [], 'datasets' => []];
        }

        $normalized = [
            'labels' => $data['labels'] ?? [],
            'datasets' => [],
        ];

        $datasets = $data['datasets'] ?? [];
        foreach ($datasets as $index => $dataset) {
            $colorIndex = $index % count(self::DEFAULT_COLORS);
            $defaultColor = self::DEFAULT_COLORS[$colorIndex];

            $normalized['datasets'][] = array_merge([
                'label' => $dataset['label'] ?? 'Dataset ' . ($index + 1),
                'data' => $dataset['data'] ?? [],
                'borderColor' => $defaultColor,
                'backgroundColor' => $dataset['backgroundColor'] ?? self::colorWithAlpha($defaultColor, 0.1),
            ], $dataset);
        }

        return $normalized;
    }

    /**
     * Convert a hex color to rgba with specified alpha.
     */
    public static function colorWithAlpha(string $hex, float $alpha): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) !== 6) {
            return "rgba(59, 130, 246, {$alpha})";
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }

    /**
     * Build Chart.js options JSON with responsive and mobile optimizations.
     */
    private function buildChartOptionsJson(): string
    {
        $options = $this->buildDefaultOptions();

        // Merge user-provided options
        if (! empty($this->options)) {
            $options = array_replace_recursive($options, $this->options);
        }

        return json_encode($options, JSON_THROW_ON_ERROR);
    }

    /**
     * Build default Chart.js options based on chart type.
     */
    public function buildDefaultOptions(): array
    {
        $isCircular = self::isCircularType($this->validatedType);

        $options = [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => $isCircular ? 'bottom' : 'top',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 12,
                        'font' => [
                            'size' => 11,
                        ],
                    ],
                ],
                'tooltip' => [
                    'enabled' => true,
                    'backgroundColor' => 'rgba(17, 24, 39, 0.9)',
                    'titleFont' => ['size' => 12],
                    'bodyFont' => ['size' => 11],
                    'padding' => 8,
                    'cornerRadius' => 6,
                ],
            ],
        ];

        // Add scales for non-circular charts
        if (! $isCircular) {
            $options['scales'] = [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'font' => ['size' => 11],
                        'maxRotation' => 0,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.05)',
                    ],
                    'ticks' => [
                        'font' => ['size' => 11],
                    ],
                ],
            ];
        }

        // Line chart specific options
        if ($this->validatedType === 'line') {
            $options['elements'] = [
                'line' => [
                    'tension' => 0.3,
                    'borderWidth' => 2,
                ],
                'point' => [
                    'radius' => 3,
                    'hoverRadius' => 5,
                ],
            ];
        }

        // Bar chart specific options
        if ($this->validatedType === 'bar') {
            $options['elements'] = [
                'bar' => [
                    'borderRadius' => 4,
                    'borderWidth' => 0,
                ],
            ];
        }

        return $options;
    }

    /**
     * Get mobile-optimized options (smaller fonts, simplified legends).
     */
    public static function getMobileOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'labels' => [
                        'font' => ['size' => 9],
                        'padding' => 8,
                        'boxWidth' => 8,
                    ],
                ],
                'tooltip' => [
                    'titleFont' => ['size' => 10],
                    'bodyFont' => ['size' => 9],
                    'padding' => 6,
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'font' => ['size' => 9],
                        'maxTicksLimit' => 5,
                    ],
                ],
                'y' => [
                    'ticks' => [
                        'font' => ['size' => 9],
                        'maxTicksLimit' => 5,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get the default error message.
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage ?? 'Gagal memuat grafik';
    }

    /**
     * Get the ARIA label for the chart.
     */
    public function getAriaLabel(): string
    {
        $typeLabels = [
            'line' => 'Grafik garis',
            'bar' => 'Grafik batang',
            'pie' => 'Grafik lingkaran',
            'doughnut' => 'Grafik donat',
        ];

        $typeLabel = $typeLabels[$this->validatedType] ?? 'Grafik';

        return $this->title
            ? "{$typeLabel}: {$this->title}"
            : $typeLabel;
    }

    /**
     * Get the height style value.
     */
    public function getHeightStyle(): string
    {
        $height = is_numeric($this->height) ? (int) $this->height : 200;

        return "{$height}px";
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('components.widget.chart');
    }
}
