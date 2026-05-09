<?php

namespace App\DTOs\Layout;

/**
 * PageLayout DTO - Data Transfer Object untuk konfigurasi layout halaman
 *
 * Menyimpan konfigurasi kolom, widget, dan breakpoints responsif untuk
 * setiap halaman yang dioptimasi dalam sistem UI/UX Qalcuity ERP.
 *
 * @see Requirements 6 (Responsive Layout Universal)
 * @see Requirements 7 (Widget Management System)
 */
class PageLayout
{
    /**
     * Breakpoint yang valid sesuai Requirement 6:
     * mobile (<768px), tablet (768-1024px), desktop (>1024px)
     */
    public const VALID_BREAKPOINTS = ['mobile', 'tablet', 'desktop'];

    /**
     * Properti widget yang wajib ada sesuai Requirement 7
     */
    public const REQUIRED_WIDGET_PROPERTIES = ['type', 'position'];

    /**
     * Konstruktor untuk PageLayout DTO
     *
     * @param  string  $page        Nama halaman (e.g. 'notifications', 'reports')
     * @param  array   $columns     Konfigurasi kolom (array numerik berisi persentase lebar, total <= 100)
     * @param  array   $widgets     Konfigurasi widget per area; setiap widget harus memiliki 'type' dan 'position'
     * @param  array   $breakpoints Konfigurasi responsif; kunci harus dari: mobile, tablet, desktop
     */
    public function __construct(
        public readonly string $page,
        public readonly array $columns,
        public readonly array $widgets,
        public readonly array $breakpoints
    ) {}

    // -------------------------------------------------------------------------
    // Sub-task 1.2.2: Validation rules for layout structure
    // -------------------------------------------------------------------------

    /**
     * Validasi struktur PageLayout secara keseluruhan
     *
     * @return bool True jika semua aturan validasi terpenuhi
     */
    public function isValid(): bool
    {
        return empty($this->getValidationErrors());
    }

    /**
     * Mengambil daftar error validasi secara detail
     *
     * Aturan validasi:
     * - page: tidak boleh kosong
     * - columns: harus array numerik (indexed), setiap nilai harus numerik dan > 0, total <= 100
     * - widgets: setiap widget harus memiliki properti 'type' dan 'position'
     * - breakpoints: kunci hanya boleh dari: mobile, tablet, desktop
     *
     * @return array Daftar pesan error; kosong jika valid
     */
    public function getValidationErrors(): array
    {
        $errors = [];

        // Validasi page name
        if (empty(trim($this->page))) {
            $errors[] = 'Nama halaman (page) tidak boleh kosong';
        }

        // Validasi columns: harus array numerik (indexed array)
        $errors = array_merge($errors, $this->validateColumns());

        // Validasi widgets: setiap widget harus memiliki 'type' dan 'position'
        $errors = array_merge($errors, $this->validateWidgets());

        // Validasi breakpoints: kunci hanya boleh mobile, tablet, desktop
        $errors = array_merge($errors, $this->validateBreakpoints());

        return $errors;
    }

    /**
     * Validasi konfigurasi kolom
     *
     * Kolom harus berupa array numerik (indexed), setiap nilai harus
     * berupa angka positif, dan total tidak boleh melebihi 100%.
     *
     * @return array Daftar error validasi kolom
     */
    private function validateColumns(): array
    {
        $errors = [];

        if (empty($this->columns)) {
            return $errors; // Kolom kosong diperbolehkan (layout default)
        }

        // Harus array numerik (indexed, bukan associative)
        if (array_keys($this->columns) !== range(0, count($this->columns) - 1)) {
            $errors[] = 'Columns harus berupa array numerik (indexed array), bukan associative array';

            return $errors; // Hentikan validasi lebih lanjut jika bukan indexed array
        }

        // Setiap nilai harus numerik dan positif
        foreach ($this->columns as $index => $width) {
            if (! is_numeric($width)) {
                $errors[] = "Column #{$index}: nilai lebar harus numerik, diberikan: " . gettype($width);
            } elseif ($width <= 0) {
                $errors[] = "Column #{$index}: nilai lebar harus lebih dari 0, diberikan: {$width}";
            } elseif ($width > 100) {
                $errors[] = "Column #{$index}: nilai lebar tidak boleh melebihi 100%, diberikan: {$width}";
            }
        }

        // Total lebar tidak boleh melebihi 100% (hanya jika semua nilai valid/numerik)
        if (empty($errors)) {
            $totalWidth = array_sum($this->columns);
            if ($totalWidth > 100) {
                $errors[] = "Total lebar kolom tidak boleh melebihi 100%, total saat ini: {$totalWidth}%";
            }
        }

        return $errors;
    }

    /**
     * Validasi konfigurasi widget
     *
     * Setiap widget harus memiliki properti 'type' dan 'position'.
     * Widget dapat diorganisir per area (associative) atau sebagai flat list.
     *
     * @return array Daftar error validasi widget
     */
    private function validateWidgets(): array
    {
        $errors = [];

        if (empty($this->widgets)) {
            return $errors; // Widget kosong diperbolehkan
        }

        foreach ($this->widgets as $areaOrIndex => $widgetOrList) {
            // Format per-area dengan list widget: ['main' => [['type' => ..., 'position' => ...], ...]]
            if (is_array($widgetOrList) && isset($widgetOrList[0]) && is_array($widgetOrList[0])) {
                foreach ($widgetOrList as $widgetIndex => $widget) {
                    $errors = array_merge(
                        $errors,
                        $this->validateSingleWidget($widget, "{$areaOrIndex}[{$widgetIndex}]")
                    );
                }
            } elseif (is_array($widgetOrList) && (array_key_exists('type', $widgetOrList) || array_key_exists('position', $widgetOrList))) {
                // Format flat list: [['type' => ..., 'position' => ...], ...]
                $errors = array_merge(
                    $errors,
                    $this->validateSingleWidget($widgetOrList, (string) $areaOrIndex)
                );
            } elseif (is_array($widgetOrList) && ! empty($widgetOrList)) {
                // Format per-area dengan widget individual (associative tanpa numeric index)
                foreach ($widgetOrList as $widgetIndex => $widget) {
                    if (is_array($widget)) {
                        $errors = array_merge(
                            $errors,
                            $this->validateSingleWidget($widget, "{$areaOrIndex}[{$widgetIndex}]")
                        );
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Validasi satu widget individual
     *
     * @param  array   $widget  Data widget
     * @param  string  $label   Label untuk pesan error
     * @return array Daftar error untuk widget ini
     */
    private function validateSingleWidget(array $widget, string $label): array
    {
        $errors = [];

        foreach (self::REQUIRED_WIDGET_PROPERTIES as $property) {
            if (! array_key_exists($property, $widget)) {
                $errors[] = "Widget '{$label}': properti '{$property}' wajib ada";
            } elseif ($widget[$property] === '' || $widget[$property] === null) {
                $errors[] = "Widget '{$label}': properti '{$property}' tidak boleh kosong";
            }
        }

        return $errors;
    }

    /**
     * Validasi konfigurasi breakpoints
     *
     * Kunci breakpoints hanya boleh: mobile, tablet, desktop
     * sesuai Requirement 6: mobile (<768px), tablet (768-1024px), desktop (>1024px)
     *
     * @return array Daftar error validasi breakpoints
     */
    private function validateBreakpoints(): array
    {
        $errors = [];

        if (empty($this->breakpoints)) {
            return $errors; // Breakpoints kosong diperbolehkan (gunakan default)
        }

        $invalidKeys = array_diff(array_keys($this->breakpoints), self::VALID_BREAKPOINTS);

        if (! empty($invalidKeys)) {
            $invalidList = implode(', ', $invalidKeys);
            $validList = implode(', ', self::VALID_BREAKPOINTS);
            $errors[] = "Breakpoints mengandung kunci tidak valid: [{$invalidList}]. Kunci yang valid: [{$validList}]";
        }

        return $errors;
    }

    // -------------------------------------------------------------------------
    // Helper methods untuk akses data
    // -------------------------------------------------------------------------

    /**
     * Mengambil konfigurasi kolom untuk breakpoint tertentu
     *
     * @param  string  $breakpoint  Nama breakpoint (mobile, tablet, desktop)
     * @return array Konfigurasi kolom untuk breakpoint
     */
    public function getColumnsForBreakpoint(string $breakpoint): array
    {
        return $this->breakpoints[$breakpoint]['columns'] ?? $this->columns;
    }

    /**
     * Mengecek apakah sidebar collapsed untuk breakpoint tertentu
     *
     * @param  string  $breakpoint  Nama breakpoint
     * @return bool True jika sidebar collapsed
     */
    public function isSidebarCollapsed(string $breakpoint): bool
    {
        return $this->breakpoints[$breakpoint]['sidebar_collapsed'] ?? false;
    }

    /**
     * Mengambil jumlah kolom grid untuk breakpoint tertentu
     *
     * @param  string  $breakpoint  Nama breakpoint
     * @return int Jumlah kolom grid
     */
    public function getGridColumns(string $breakpoint): int
    {
        return $this->breakpoints[$breakpoint]['grid_columns'] ?? 4;
    }

    /**
     * Mengambil widget untuk area tertentu
     *
     * @param  string  $area  Nama area (main, sidebar, additional, dll)
     * @return array Daftar widget untuk area
     */
    public function getWidgetsForArea(string $area): array
    {
        return $this->widgets[$area] ?? [];
    }

    /**
     * Mengecek apakah area memiliki widget
     *
     * @param  string  $area  Nama area
     * @return bool True jika area memiliki widget
     */
    public function hasWidgetsInArea(string $area): bool
    {
        return ! empty($this->widgets[$area]);
    }

    /**
     * Mengambil semua area yang memiliki widget
     *
     * @return array Daftar nama area yang memiliki widget
     */
    public function getActiveAreas(): array
    {
        return array_keys(array_filter($this->widgets, fn($widgets) => ! empty($widgets)));
    }

    /**
     * Mengambil CSS classes untuk kolom berdasarkan breakpoint
     *
     * @param  string  $breakpoint  Nama breakpoint
     * @return array CSS classes untuk setiap kolom
     */
    public function getColumnClasses(string $breakpoint): array
    {
        $columns = $this->getColumnsForBreakpoint($breakpoint);
        $classes = [];

        foreach ($columns as $width) {
            $classes[] = match ($breakpoint) {
                'mobile' => 'w-full',
                'tablet' => "md:w-{$width}/100",
                'desktop' => "lg:w-{$width}/100",
                default => "w-{$width}/100"
            };
        }

        return $classes;
    }

    /**
     * Mengambil grid CSS classes berdasarkan breakpoint
     *
     * @param  string  $breakpoint  Nama breakpoint
     * @return string CSS class untuk grid
     */
    public function getGridClass(string $breakpoint): string
    {
        $columns = $this->getGridColumns($breakpoint);

        return match ($breakpoint) {
            'mobile' => 'grid-cols-1',
            'tablet' => "md:grid-cols-{$columns}",
            'desktop' => "lg:grid-cols-{$columns}",
            default => "grid-cols-{$columns}"
        };
    }

    // -------------------------------------------------------------------------
    // Sub-task 1.2.3: Serialization methods for storage
    // -------------------------------------------------------------------------

    /**
     * Mengkonversi ke array untuk serialization/storage
     *
     * @return array Representasi array dari PageLayout
     */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'columns' => $this->columns,
            'widgets' => $this->widgets,
            'breakpoints' => $this->breakpoints,
        ];
    }

    /**
     * Membuat instance dari array (factory method untuk deserialization)
     *
     * @param  array  $data  Data array (dari database atau cache)
     * @return static Instance PageLayout
     */
    public static function fromArray(array $data): static
    {
        return new static(
            page: $data['page'] ?? '',
            columns: $data['columns'] ?? [],
            widgets: $data['widgets'] ?? [],
            breakpoints: $data['breakpoints'] ?? []
        );
    }

    /**
     * Mengkonversi ke JSON string untuk penyimpanan di database
     *
     * @return string JSON representation
     *
     * @throws \JsonException Jika serialization gagal
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Membuat instance dari JSON string (untuk membaca dari database/cache)
     *
     * @param  string  $json  JSON string
     * @return static Instance PageLayout
     *
     * @throws \JsonException Jika JSON tidak valid
     */
    public static function fromJson(string $json): static
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return static::fromArray($data);
    }
}
