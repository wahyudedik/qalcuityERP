<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.10 — Form Input Tidak Memiliki Dark Mode Styling
 *
 * Scan CSS/blade untuk dark mode styling pada input, select, textarea.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 */
class DarkModeFormTest extends TestCase
{
    private string $cssFile = 'resources/css/app.css';

    /**
     * @test
     * Bug 1.10: Verifikasi bahwa CSS memiliki dark mode styling untuk form elements
     *
     * AKAN GAGAL karena tidak ada dark mode styling untuk form elements di CSS
     */
    public function test_css_has_dark_mode_form_element_styles(): void
    {
        if (!file_exists($this->cssFile)) {
            $this->markTestSkipped("File CSS tidak ditemukan: {$this->cssFile}");
        }

        $content = file_get_contents($this->cssFile);

        // Cari dark mode styling untuk form elements
        // Seharusnya ada: dark:bg-slate-700 atau .dark input { background: ... }
        $hasDarkFormStyles = (
            str_contains($content, 'dark:bg-slate-700') ||
            str_contains($content, '.dark input') ||
            str_contains($content, '.dark select') ||
            str_contains($content, '.dark textarea') ||
            (str_contains($content, '@layer base') && str_contains($content, 'dark:'))
        );

        // Test ini AKAN GAGAL karena tidak ada dark mode styling untuk form elements
        $this->assertTrue(
            $hasDarkFormStyles,
            "Bug 1.10: Tidak ditemukan dark mode styling untuk form elements (input, select, textarea) " .
            "di file CSS. Form elements akan tetap berwarna putih di dark mode."
        );
    }

    /**
     * @test
     * Bug 1.10: Scan blade files untuk form elements tanpa dark: class
     *
     * AKAN GAGAL karena ada form elements tanpa dark: class
     */
    public function test_form_elements_have_dark_mode_class(): void
    {
        $violations = [];
        $viewDir = 'resources/views';

        if (!is_dir($viewDir)) {
            $this->markTestSkipped("Directory {$viewDir} tidak ditemukan");
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($viewDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        // Pattern untuk form elements dengan class Tailwind tapi tanpa dark:
        // Exclude bg-white/N (opacity modifier) — glassmorphism overlay, bukan bg putih solid
        // Exclude auth views — guest layout adalah intentionally light-themed
        $formElementPattern = '/<(input|select|textarea)[^>]*class=["\'][^"\']*bg-white(?!\/)[^"\']*["\'][^>]*>/i';

        foreach ($iterator as $file) {
            if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $relativePath = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $file->getPathname());

            // Skip auth views — guest layout adalah intentionally light-themed
            $normalized = str_replace('\\', '/', $relativePath);
            if (str_contains($normalized, '/views/auth/')) {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            if (preg_match($formElementPattern, $content, $matches)) {
                // Cek apakah ada dark: di match
                if (!str_contains($matches[0], 'dark:')) {
                    $relativePath = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $violations[] = $relativePath;

                    if (count($violations) >= 10) {
                        break;
                    }
                }
            }
        }

        // Test ini AKAN GAGAL karena ada form elements tanpa dark: class
        $this->assertEmpty(
            $violations,
            "Bug 1.10: Form elements berikut menggunakan 'bg-white' tanpa 'dark:' equivalent:\n" .
            implode("\n", $violations)
        );
    }

    /**
     * @test
     * Bug 1.10: Verifikasi bahwa ada @layer base dengan dark mode form styles di CSS
     *
     * AKAN GAGAL karena tidak ada @layer base dengan dark mode form styles
     */
    public function test_css_has_layer_base_with_dark_form_styles(): void
    {
        if (!file_exists($this->cssFile)) {
            $this->markTestSkipped("File CSS tidak ditemukan: {$this->cssFile}");
        }

        $content = file_get_contents($this->cssFile);

        // Cari @layer base dengan dark mode styling untuk form elements
        $hasLayerBaseWithDark = (
            str_contains($content, '@layer base') &&
            str_contains($content, 'dark:') &&
            (
                str_contains($content, 'input') ||
                str_contains($content, 'select') ||
                str_contains($content, 'textarea')
            )
        );

        // Test ini AKAN GAGAL karena tidak ada @layer base dengan dark mode form styles
        $this->assertTrue(
            $hasLayerBaseWithDark,
            "Bug 1.10: Tidak ditemukan '@layer base' dengan dark mode styling untuk form elements. " .
            "Tambahkan ke resources/css/app.css:\n" .
            "@layer base {\n" .
            "  input, select, textarea {\n" .
            "    @apply bg-white dark:bg-slate-700 text-gray-900 dark:text-gray-100;\n" .
            "  }\n" .
            "}"
        );
    }
}
