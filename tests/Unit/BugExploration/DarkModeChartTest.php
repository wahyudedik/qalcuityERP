<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.9 — Chart.js Tidak Merespons Event theme-changed
 *
 * UPDATED: Dark mode telah dihapus sepenuhnya dari aplikasi.
 * chart-theme.js sekarang hanya menggunakan warna light mode.
 * Test ini memverifikasi bahwa:
 * - Tidak ada lagi listener theme-changed (tidak diperlukan)
 * - chart-theme.js menggunakan warna light mode secara default
 */
class DarkModeChartTest extends TestCase
{
    /**
     * @test
     * Post dark mode removal: chart-theme.js should NOT have theme-changed listener
     */
    public function test_chartjs_has_theme_changed_listener(): void
    {
        $chartThemeFile = 'resources/js/chart-theme.js';

        if (! file_exists($chartThemeFile)) {
            $this->markTestSkipped('chart-theme.js tidak ditemukan');
        }

        $content = file_get_contents($chartThemeFile);

        // After dark mode removal, there should be NO theme-changed listener
        $hasThemeChangedListener = (
            str_contains($content, 'theme-changed') &&
            str_contains($content, 'addEventListener')
        );

        $this->assertFalse(
            $hasThemeChangedListener,
            'chart-theme.js should NOT have theme-changed event listener after dark mode removal'
        );

        // Verify light mode colors are used
        $this->assertStringContainsString(
            '#1e293b',
            $content,
            'chart-theme.js should use light mode text color #1e293b'
        );

        $this->assertStringContainsString(
            '#64748b',
            $content,
            'chart-theme.js should use light mode muted color #64748b'
        );
    }

    /**
     * @test
     * Post dark mode removal: No theme-changed event dispatch needed
     */
    public function test_theme_manager_dispatches_theme_changed_event(): void
    {
        $themeManagerFile = 'resources/js/theme-manager.js';

        // theme-manager.js should have been deleted
        $this->assertFileDoesNotExist(
            $themeManagerFile,
            'theme-manager.js should have been deleted after dark mode removal'
        );
    }

    /**
     * @test
     * Post dark mode removal: Flatpickr theme-changed listener not needed
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

        $flatpickrUsed = str_contains($allJsContent, 'flatpickr') ||
            file_exists('node_modules/flatpickr');

        if (! $flatpickrUsed) {
            $this->markTestSkipped(
                'Flatpickr tidak digunakan di project ini — test dikecualikan.'
            );
        }

        // After dark mode removal, no theme-changed listener should exist
        $hasFlatpickrThemeListener = (
            str_contains($allJsContent, 'theme-changed') &&
            str_contains($allJsContent, 'flatpickr')
        );

        $this->assertFalse(
            $hasFlatpickrThemeListener,
            'No theme-changed listener for Flatpickr should exist after dark mode removal'
        );
    }
}
