<?php

namespace App\View\Components\Layout;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * ResponsiveGrid - Komponen layout grid responsif
 *
 * Menyediakan grid layout yang menyesuaikan dengan breakpoint:
 * - Mobile (<768px): single column
 * - Tablet (768-1024px): configurable columns
 * - Desktop (>1024px): full multi-column layout
 *
 * Mendukung konfigurasi kolom berbasis persentase (e.g. [60, 40])
 * atau auto-grid (e.g. ['auto']) untuk grid otomatis.
 *
 * @see Requirements 6 (Responsive Layout Universal)
 * @see Design Document: Layout Engine - Blade Components
 */
class ResponsiveGrid extends Component
{
    /**
     * Konfigurasi kolom yang sudah diproses
     */
    public array $processedColumns;

    /**
     * CSS classes untuk grid container
     */
    public string $gridClasses;

    /**
     * CSS classes untuk gap/spacing
     */
    public string $gapClasses;

    /**
     * Apakah menggunakan mode auto-grid
     */
    public bool $isAutoGrid;

    /**
     * Konstruktor ResponsiveGrid component
     *
     * @param  array       $columns      Konfigurasi kolom: [60, 40] untuk split, ['auto'] untuk auto-grid
     * @param  array       $breakpoints  Konfigurasi per-breakpoint (opsional)
     * @param  string      $gap          Gap size: 'sm', 'md', 'lg', 'xl' atau custom Tailwind class
     * @param  string      $mobileStack  Perilaku mobile: 'stack' (default) atau 'scroll'
     * @param  string|null $role         ARIA role untuk grid container
     * @param  string|null $ariaLabel    ARIA label untuk aksesibilitas
     */
    public function __construct(
        public array $columns = [100],
        public array $breakpoints = [],
        public string $gap = 'md',
        public string $mobileStack = 'stack',
        public ?string $role = null,
        public ?string $ariaLabel = null,
    ) {
        $this->isAutoGrid = $this->detectAutoGrid();
        $this->processedColumns = $this->processColumns();
        $this->gridClasses = $this->buildGridClasses();
        $this->gapClasses = $this->buildGapClasses();
    }

    /**
     * Deteksi apakah menggunakan mode auto-grid
     */
    private function detectAutoGrid(): bool
    {
        return count($this->columns) === 1
            && isset($this->columns[0])
            && $this->columns[0] === 'auto';
    }

    /**
     * Proses konfigurasi kolom menjadi Tailwind width classes
     *
     * Mapping persentase ke Tailwind fractional widths:
     * - 100 => w-full
     * - 75  => w-3/4
     * - 66/67 => w-2/3
     * - 60  => w-3/5
     * - 50  => w-1/2
     * - 40  => w-2/5
     * - 33/34 => w-1/3
     * - 25  => w-1/4
     * - 20  => w-1/5
     *
     * @return array Array of Tailwind width classes per column
     */
    private function processColumns(): array
    {
        if ($this->isAutoGrid) {
            return ['auto'];
        }

        $classes = [];
        foreach ($this->columns as $width) {
            $classes[] = $this->percentageToTailwindClass((int) $width);
        }

        return $classes;
    }

    /**
     * Konversi persentase ke Tailwind CSS class
     */
    private function percentageToTailwindClass(int $percentage): string
    {
        return match (true) {
            $percentage >= 100 => 'lg:w-full',
            $percentage >= 74 && $percentage <= 76 => 'lg:w-3/4',
            $percentage >= 65 && $percentage <= 67 => 'lg:w-2/3',
            $percentage >= 59 && $percentage <= 61 => 'lg:w-3/5',
            $percentage === 50 => 'lg:w-1/2',
            $percentage >= 39 && $percentage <= 41 => 'lg:w-2/5',
            $percentage >= 33 && $percentage <= 34 => 'lg:w-1/3',
            $percentage >= 24 && $percentage <= 26 => 'lg:w-1/4',
            $percentage >= 19 && $percentage <= 21 => 'lg:w-1/5',
            default => 'lg:w-[' . $percentage . '%]',
        };
    }

    /**
     * Build CSS classes untuk grid container
     */
    private function buildGridClasses(): string
    {
        if ($this->isAutoGrid) {
            return $this->buildAutoGridClasses();
        }

        return $this->buildFlexGridClasses();
    }

    /**
     * Build classes untuk auto-grid mode (CSS Grid)
     * Menggunakan responsive grid columns berdasarkan breakpoints
     */
    private function buildAutoGridClasses(): string
    {
        $mobileColumns = $this->breakpoints['mobile']['columns'] ?? 1;
        $tabletColumns = $this->breakpoints['tablet']['columns'] ?? 2;
        $desktopColumns = $this->breakpoints['desktop']['columns'] ?? 4;

        $classes = ['grid'];

        // Mobile columns
        $classes[] = 'grid-cols-' . $mobileColumns;

        // Tablet columns (md breakpoint)
        $classes[] = 'md:grid-cols-' . $tabletColumns;

        // Desktop columns (lg breakpoint)
        $classes[] = 'lg:grid-cols-' . $desktopColumns;

        return implode(' ', $classes);
    }

    /**
     * Build classes untuk flex-based grid (percentage columns)
     */
    private function buildFlexGridClasses(): string
    {
        $classes = ['flex', 'flex-wrap'];

        // Mobile: stack vertically
        if ($this->mobileStack === 'stack') {
            $classes[] = 'flex-col';
            $classes[] = 'lg:flex-row';
        }

        return implode(' ', $classes);
    }

    /**
     * Build gap/spacing classes berdasarkan konfigurasi
     */
    private function buildGapClasses(): string
    {
        return match ($this->gap) {
            'none' => '',
            'xs' => 'gap-1',
            'sm' => 'gap-2 lg:gap-3',
            'md' => 'gap-4 lg:gap-6',
            'lg' => 'gap-6 lg:gap-8',
            'xl' => 'gap-8 lg:gap-10',
            default => $this->gap, // Allow custom Tailwind class
        };
    }

    /**
     * Mendapatkan ARIA role yang sesuai
     */
    public function getRole(): string
    {
        return $this->role ?? ($this->isAutoGrid ? 'grid' : 'region');
    }

    /**
     * Mendapatkan ARIA label
     */
    public function getAriaLabel(): ?string
    {
        return $this->ariaLabel;
    }

    /**
     * Render the component
     */
    public function render(): View
    {
        return view('components.layout.responsive-grid');
    }
}
