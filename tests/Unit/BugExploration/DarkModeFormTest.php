<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.10 — Form Input Tidak Memiliki Dark Mode Styling
 *
 * UPDATED: Dark mode telah dihapus sepenuhnya dari aplikasi.
 * Test ini sekarang memverifikasi bahwa TIDAK ADA dark: class tersisa
 * di CSS dan form elements, sesuai dengan keputusan remove dark mode.
 */
class DarkModeFormTest extends TestCase
{
    private string $cssFile = 'resources/css/app.css';

    /**
     * @test
     * Post dark mode removal: CSS should NOT have dark mode form element styles
     */
    public function test_css_has_dark_mode_form_element_styles(): void
    {
        if (! file_exists($this->cssFile)) {
            $this->markTestSkipped("File CSS tidak ditemukan: {$this->cssFile}");
        }

        $content = file_get_contents($this->cssFile);

        // After dark mode removal, there should be NO dark mode styling
        $hasDarkFormStyles = (
            str_contains($content, 'dark:bg-slate-700') ||
            str_contains($content, '.dark input') ||
            str_contains($content, '.dark select') ||
            str_contains($content, '.dark textarea') ||
            (str_contains($content, '@layer base') && str_contains($content, 'dark:'))
        );

        $this->assertFalse(
            $hasDarkFormStyles,
            'CSS should NOT contain dark mode styling for form elements after dark mode removal'
        );
    }

    /**
     * @test
     * Post dark mode removal: Form elements should NOT have dark: class
     */
    public function test_form_elements_have_dark_mode_class(): void
    {
        $viewDir = 'resources/views';

        if (! is_dir($viewDir)) {
            $this->markTestSkipped("Directory {$viewDir} tidak ditemukan");
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($viewDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $violations = [];

        foreach ($iterator as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            if (str_contains($content, 'dark:bg-slate-700') || str_contains($content, 'dark:bg-gray-')) {
                $relativePath = str_replace(getcwd().DIRECTORY_SEPARATOR, '', $file->getPathname());
                $violations[] = $relativePath;

                if (count($violations) >= 10) {
                    break;
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Form elements should NOT have dark: classes after dark mode removal:\n".
                implode("\n", $violations)
        );
    }

    /**
     * @test
     * Post dark mode removal: CSS @layer base should NOT have dark form styles
     */
    public function test_css_has_layer_base_with_dark_form_styles(): void
    {
        if (! file_exists($this->cssFile)) {
            $this->markTestSkipped("File CSS tidak ditemukan: {$this->cssFile}");
        }

        $content = file_get_contents($this->cssFile);

        // After dark mode removal, @layer base should NOT contain dark: classes
        $hasLayerBaseWithDark = (
            str_contains($content, '@layer base') &&
            str_contains($content, 'dark:')
        );

        $this->assertFalse(
            $hasLayerBaseWithDark,
            'CSS @layer base should NOT contain dark: classes after dark mode removal'
        );
    }
}
