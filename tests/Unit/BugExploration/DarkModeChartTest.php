<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.9 — Chart.js Tidak Merespons Event theme-changed
 *
 * Membuktikan bahwa tidak ada listener theme-changed di Chart.js initialization.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 */
class DarkModeChartTest extends TestCase
{
    private array $jsFiles = [
        'resources/js/app.js',
        'resources/js/theme-manager.js',
        'resources/js/charts.js',
        'resources/js/dashboard.js',
    ];

    /**
     * @test
     * Bug 1.9: Verifikasi bahwa ada listener theme-changed untuk Chart.js
     *
     * AKAN GAGAL karena tidak ada listener theme-changed di Chart.js initialization
     */
    public function test_chartjs_has_theme_changed_listener(): void
    {
        $allJsContent = '';

        foreach ($this->jsFiles as $jsFile) {
            if (file_exists($jsFile)) {
                $allJsContent .= file_get_contents($jsFile);
            }
        }

        // Juga scan semua JS files di resources/js
        if (is_dir('resources/js')) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator('resources/js', \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.js')) {
                    $allJsContent .= file_get_contents($file->getPathname());
                }
            }
        }

        // Cari listener theme-changed yang mengupdate Chart.js
        $hasChartThemeListener = (
            str_contains($allJsContent, 'theme-changed') &&
            (
                str_contains($allJsContent, 'chart') ||
                str_contains($allJsContent, 'Chart')
            ) &&
            str_contains($allJsContent, 'addEventListener')
        );

        // Test ini AKAN GAGAL karena tidak ada listener theme-changed untuk Chart.js
        $this->assertTrue(
            $hasChartThemeListener,
            "Bug 1.9: Tidak ditemukan listener 'theme-changed' event yang mengupdate Chart.js. " .
            "Chart.js tidak akan merespons perubahan tema dan akan tetap menggunakan " .
            "warna background putih setelah tema berubah ke dark mode."
        );
    }

    /**
     * @test
     * Bug 1.9: Verifikasi bahwa theme-manager.js mendispatch event theme-changed
     *
     * AKAN GAGAL jika theme-manager tidak mendispatch event
     */
    public function test_theme_manager_dispatches_theme_changed_event(): void
    {
        $themeManagerFile = 'resources/js/theme-manager.js';

        // Cari di semua JS files jika theme-manager.js tidak ada
        $allJsContent = '';
        if (file_exists($themeManagerFile)) {
            $allJsContent = file_get_contents($themeManagerFile);
        } else {
            // Scan semua JS files
            if (is_dir('resources/js')) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator('resources/js', \RecursiveDirectoryIterator::SKIP_DOTS)
                );
                foreach ($iterator as $file) {
                    if ($file->isFile() && str_ends_with($file->getFilename(), '.js')) {
                        $allJsContent .= file_get_contents($file->getPathname());
                    }
                }
            }
        }

        // Cari dispatchEvent dengan CustomEvent 'theme-changed'
        $dispatchesThemeChanged = (
            str_contains($allJsContent, "dispatchEvent") &&
            str_contains($allJsContent, "theme-changed")
        );

        // Test ini AKAN GAGAL karena theme-manager tidak mendispatch event theme-changed
        $this->assertTrue(
            $dispatchesThemeChanged,
            "Bug 1.9: Tidak ditemukan kode yang mendispatch CustomEvent 'theme-changed'. " .
            "Komponen pihak ketiga (Chart.js, Flatpickr, dll) tidak akan mendapat notifikasi " .
            "saat tema berubah."
        );
    }

    /**
     * @test
     * Bug 1.9: Verifikasi bahwa ada listener theme-changed untuk Flatpickr
     *
     * Flatpickr tidak digunakan di project ini — test ini di-skip jika flatpickr
     * tidak ditemukan di codebase.
     */
    public function test_flatpickr_has_theme_changed_listener(): void
    {
        $allJsContent = '';

        if (is_dir('resources/js')) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator('resources/js', \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.js')) {
                    $allJsContent .= file_get_contents($file->getPathname());
                }
            }
        }

        // Jika flatpickr tidak digunakan di project ini, skip test
        $flatpickrUsed = str_contains($allJsContent, 'flatpickr') ||
                         file_exists('node_modules/flatpickr');

        if (!$flatpickrUsed) {
            $this->markTestSkipped(
                "Flatpickr tidak digunakan di project ini — test dikecualikan."
            );
        }

        // Cari listener theme-changed yang mengupdate Flatpickr
        $hasFlatpickrThemeListener = (
            str_contains($allJsContent, 'theme-changed') &&
            str_contains($allJsContent, 'flatpickr')
        );

        $this->assertTrue(
            $hasFlatpickrThemeListener,
            "Bug 1.9: Tidak ditemukan listener 'theme-changed' event untuk Flatpickr. " .
            "Flatpickr tidak akan merespons perubahan tema."
        );
    }
}
