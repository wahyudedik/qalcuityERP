<?php

namespace App\View\Components\Widget;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * StatisticsWidget - Komponen untuk menampilkan statistik numerik
 *
 * Menyediakan widget statistik dengan fitur:
 * - Number formatting dengan suffix K, M, B
 * - Trend indicators (up/down arrows dengan persentase)
 * - Color coding untuk positive/negative values
 * - Loading skeleton dan error states
 * - Grid layout untuk multiple stat items
 *
 * Usage:
 * <x-widget.statistics :stats="$stats" title="Ringkasan" />
 *
 * @see Task 4.1: Create StatisticsWidget component
 * @see Requirements 7 (Sistem Widget Management)
 */
class Statistics extends Component
{
    /**
     * Formatted statistics data ready for rendering.
     */
    public array $formattedStats;

    /**
     * Grid column classes based on stat count.
     */
    public string $gridClasses;

    /**
     * Konstruktor StatisticsWidget component
     *
     * @param  array       $stats       Array of stat items [{label, value, trend, icon}]
     * @param  string      $title       Widget title
     * @param  bool        $loading     Initial loading state
     * @param  bool        $error       Initial error state
     * @param  string|null $errorMessage Custom error message
     * @param  int         $columns     Number of grid columns (auto-detected if 0)
     */
    public function __construct(
        public array $stats = [],
        public string $title = '',
        public bool $loading = false,
        public bool $error = false,
        public ?string $errorMessage = null,
        public int $columns = 0,
    ) {
        $this->formattedStats = $this->formatStats($this->stats);
        $this->gridClasses = $this->buildGridClasses();
    }

    /**
     * Format all stat items with number formatting and trend data.
     */
    private function formatStats(array $stats): array
    {
        return array_map(function (array $stat) {
            $value = $stat['value'] ?? 0;
            $trend = $stat['trend'] ?? 0;

            return [
                'label' => $stat['label'] ?? '',
                'value' => $value,
                'formattedValue' => $this->formatNumber($value),
                'trend' => $trend,
                'formattedTrend' => $this->formatTrend($trend),
                'trendDirection' => $this->getTrendDirection($trend),
                'trendColorClass' => $this->getTrendColorClass($trend),
                'trendBgClass' => $this->getTrendBgClass($trend),
                'icon' => $stat['icon'] ?? null,
                'prefix' => $stat['prefix'] ?? null,
                'suffix' => $stat['suffix'] ?? null,
                'inverse' => $stat['inverse'] ?? false,
            ];
        }, $stats);
    }

    /**
     * Format a number with K, M, B suffixes.
     *
     * @param  float|int|string|null  $number
     */
    public static function formatNumber($number): string
    {
        if ($number === null || $number === '') {
            return '0';
        }

        $number = is_string($number) ? (float) $number : $number;
        $absNumber = abs($number);
        $sign = $number < 0 ? '-' : '';

        if ($absNumber >= 1000000000) {
            $formatted = rtrim(rtrim(number_format($absNumber / 1000000000, 1, '.', ''), '0'), '.');

            return $sign . $formatted . 'B';
        }

        if ($absNumber >= 1000000) {
            $formatted = rtrim(rtrim(number_format($absNumber / 1000000, 1, '.', ''), '0'), '.');

            return $sign . $formatted . 'M';
        }

        if ($absNumber >= 1000) {
            $formatted = rtrim(rtrim(number_format($absNumber / 1000, 1, '.', ''), '0'), '.');

            return $sign . $formatted . 'K';
        }

        return $sign . number_format($absNumber, 0, '.', '');
    }

    /**
     * Format trend percentage with sign.
     */
    public static function formatTrend(float $trend): string
    {
        if ($trend == 0) {
            return '0%';
        }

        $sign = $trend > 0 ? '+' : '';

        return $sign . number_format($trend, 1, '.', '') . '%';
    }

    /**
     * Get trend direction: 'up', 'down', or 'neutral'.
     */
    public static function getTrendDirection(float $trend): string
    {
        if ($trend > 0) {
            return 'up';
        }

        if ($trend < 0) {
            return 'down';
        }

        return 'neutral';
    }

    /**
     * Get Tailwind text color class based on trend direction.
     */
    public static function getTrendColorClass(float $trend, bool $inverse = false): string
    {
        if ($trend > 0) {
            return $inverse ? 'text-red-600' : 'text-green-600';
        }

        if ($trend < 0) {
            return $inverse ? 'text-green-600' : 'text-red-600';
        }

        return 'text-gray-500';
    }

    /**
     * Get Tailwind background color class for trend badge.
     */
    public static function getTrendBgClass(float $trend, bool $inverse = false): string
    {
        if ($trend > 0) {
            return $inverse ? 'bg-red-50' : 'bg-green-50';
        }

        if ($trend < 0) {
            return $inverse ? 'bg-green-50' : 'bg-red-50';
        }

        return 'bg-gray-50';
    }

    /**
     * Build responsive grid classes based on stat count or explicit columns.
     */
    private function buildGridClasses(): string
    {
        $count = $this->columns > 0 ? $this->columns : count($this->stats);

        return match (true) {
            $count <= 1 => 'grid-cols-1',
            $count === 2 => 'grid-cols-1 sm:grid-cols-2',
            $count === 3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
            default => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
        };
    }

    /**
     * Get the default error message.
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage ?? 'Gagal memuat statistik';
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('components.widget.statistics');
    }
}
