<?php

namespace App\View\Components\Layout;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * WidgetContainer - Komponen wrapper untuk widget dashboard
 *
 * Menyediakan container widget dengan fitur:
 * - Loading states dengan skeleton animation
 * - Error boundary dengan retry functionality
 * - Edit mode untuk konfigurasi widget
 * - Drag-and-drop support untuk reordering
 * - Accessibility: ARIA attributes, keyboard navigation
 *
 * @see Requirements 7 (Sistem Widget Management)
 * @see Requirements 8 (Performance dan Loading Optimization)
 * @see Design Document: Layout Engine - Widget Container
 */
class WidgetContainer extends Component
{
    /**
     * CSS classes for the container size
     */
    public string $sizeClasses;

    /**
     * Konstruktor WidgetContainer component
     *
     * @param  string      $widgetId    Unique identifier for the widget
     * @param  string      $title       Widget title displayed in header
     * @param  bool        $editable    Whether the widget can be configured
     * @param  bool        $draggable   Whether the widget supports drag-and-drop
     * @param  bool        $loading     Initial loading state
     * @param  string      $size        Widget size: 'sm', 'md', 'lg', 'full'
     * @param  string|null $errorMessage Custom error message
     * @param  string|null $ariaLabel   Custom ARIA label for accessibility
     */
    public function __construct(
        public string $widgetId = '',
        public string $title = '',
        public bool $editable = false,
        public bool $draggable = false,
        public bool $loading = false,
        public string $size = 'md',
        public ?string $errorMessage = null,
        public ?string $ariaLabel = null,
    ) {
        $this->sizeClasses = $this->buildSizeClasses();
    }

    /**
     * Build CSS classes based on widget size
     */
    private function buildSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'min-h-[8rem]',
            'md' => 'min-h-[12rem]',
            'lg' => 'min-h-[16rem]',
            'full' => 'min-h-[20rem]',
            default => 'min-h-[12rem]',
        };
    }

    /**
     * Get the ARIA label for the widget container
     */
    public function getAriaLabel(): string
    {
        return $this->ariaLabel ?? ($this->title ? "Widget: {$this->title}" : 'Widget');
    }

    /**
     * Get the default error message
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage ?? 'Widget gagal dimuat';
    }

    /**
     * Render the component
     */
    public function render(): View
    {
        return view('components.layout.widget-container');
    }
}
