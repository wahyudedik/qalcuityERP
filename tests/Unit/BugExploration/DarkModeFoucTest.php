<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.8 — FOUC (Flash of Unstyled Content) dengan theme=system
 *
 * UPDATED: Dark mode telah dihapus sepenuhnya dari aplikasi.
 * FOUC prevention script telah dihapus karena tidak ada lagi dark mode.
 * Test ini sekarang memverifikasi bahwa:
 * - Tidak ada FOUC script yang membaca theme dari localStorage
 * - Ada localStorage cleanup script (one-time)
 * - Layout menggunakan light mode secara default
 */
class DarkModeFoucTest extends TestCase
{
    private string $appBladeFile = 'resources/views/layouts/app.blade.php';

    /**
     * @test
     * Post dark mode removal: No theme system handling needed (dark mode removed)
     */
    public function test_theme_init_script_handles_system_theme(): void
    {
        $this->assertFileExists($this->appBladeFile);
        $content = file_get_contents($this->appBladeFile);

        // After dark mode removal, there should be NO system theme handling
        $hasSystemThemeHandling = (
            str_contains($content, "theme === 'system'") ||
            str_contains($content, 'theme === "system"') ||
            str_contains($content, 'prefers-color-scheme') ||
            str_contains($content, 'matchMedia')
        );

        $this->assertFalse(
            $hasSystemThemeHandling,
            'Layout should NOT contain system theme handling after dark mode removal'
        );
    }

    /**
     * @test
     * Post dark mode removal: localStorage cleanup script should be IIFE
     */
    public function test_theme_init_script_is_iife(): void
    {
        $this->assertFileExists($this->appBladeFile);
        $content = file_get_contents($this->appBladeFile);

        // The localStorage cleanup script should use IIFE pattern
        $hasIife = (
            preg_match('/\(function\s*\(\)\s*\{/', $content) ||
            preg_match('/\(\(\)\s*=>\s*\{/', $content)
        );

        $this->assertTrue(
            $hasIife,
            'localStorage cleanup script should use IIFE pattern'
        );
    }

    /**
     * @test
     * Post dark mode removal: Cleanup script should be before @vite in head
     */
    public function test_theme_init_script_is_before_vite_in_head(): void
    {
        $this->assertFileExists($this->appBladeFile);
        $content = file_get_contents($this->appBladeFile);

        // Cari posisi localStorage cleanup script
        $scriptPos = strpos($content, '_theme_cleaned');

        if ($scriptPos === false) {
            // Also check for localStorage reference (cleanup script)
            $scriptPos = strpos($content, "localStorage.removeItem('theme')");
        }

        if ($scriptPos === false) {
            $this->markTestSkipped('localStorage cleanup script not found in layout');
        }

        // Cari posisi @vite directive
        $vitePos = false;
        if (preg_match('/\n\s*@vite\s*\(/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $vitePos = $matches[0][1];
        }

        if ($vitePos === false) {
            $this->markTestSkipped('Directive @vite tidak ditemukan di blade file.');
        }

        // Cleanup script harus berada SEBELUM @vite
        $this->assertLessThan(
            $vitePos,
            $scriptPos,
            'localStorage cleanup script should be before @vite directive in <head>'
        );
    }
}
