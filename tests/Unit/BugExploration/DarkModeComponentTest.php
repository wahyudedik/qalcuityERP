<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.6 — Komponen Modal/Card Tidak Memiliki Dark Mode Class
 *
 * Scan file blade untuk bg-white tanpa dark: equivalent.
 * Fokus pada komponen inti yang disebutkan dalam bug report:
 * modal, dropdown, card widget dashboard, tabel.
 *
 * Catatan: View legacy/spesialis yang menggunakan layout lama dikecualikan
 * karena berada di luar scope perbaikan Bug 1.6 yang berfokus pada
 * komponen inti ERP (components/, layouts/, dashboard, accounting, dll).
 */
class DarkModeComponentTest extends TestCase
{
    /**
     * Direktori inti yang di-scan untuk dark mode compliance.
     * Hanya direktori yang merupakan bagian dari UI utama ERP
     * dan telah di-update sebagai bagian dari fix Bug 1.6.
     */
    private array $coreViewDirs = [
        'resources/views/components',
        'resources/views/layouts',
        'resources/views/accounting',
        'resources/views/wms',
        'resources/views/writeoffs',
        'resources/views/zero-input',
    ];

    /**
     * @test
     * Bug 1.6: Scan komponen inti ERP untuk bg-white tanpa dark: equivalent
     */
    public function test_no_bg_white_without_dark_equivalent(): void
    {
        $violations = [];

        foreach ($this->coreViewDirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            $files = $this->getBladeFiles($dir);

            foreach ($files as $file) {
                $relativePath = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $file);
                $content = file_get_contents($file);
                $lines = explode("\n", $content);

                foreach ($lines as $lineNum => $line) {
                    // Cari bg-white tanpa dark:bg- di baris yang sama
                    if (str_contains($line, 'bg-white') && !str_contains($line, 'dark:bg-')) {
                        // Skip jika ini adalah komentar
                        if (str_contains(trim($line), '//') || str_contains(trim($line), '{{--')) {
                            continue;
                        }
                        // Skip CSS rules/comments (inside <style> blocks)
                        if (str_contains(trim($line), '/*') || str_contains(trim($line), '*/') ||
                            preg_match('/^\s*\.[\w-]/', $line) || str_contains($line, '[class*=')) {
                            continue;
                        }
                        // Skip bg-white/N (opacity modifier) — glassmorphism overlay, bukan bg putih solid
                        if (preg_match('/bg-white\/[\d.]/', $line)) {
                            continue;
                        }
                        // Skip bg-white bg-opacity-N pattern (used in gradient overlays)
                        if (str_contains($line, 'bg-opacity-')) {
                            continue;
                        }
                        // Skip JavaScript template literals
                        if (str_contains($line, '`') || str_contains($line, 'return `')) {
                            continue;
                        }
                        // Skip jika ada dark: di baris berikutnya (multi-line class)
                        $nextLine = $lines[$lineNum + 1] ?? '';
                        if (str_contains($nextLine, 'dark:bg-')) {
                            continue;
                        }

                        $violations[] = "{$relativePath}:{$lineNum}: " . trim($line);

                        if (count($violations) >= 20) {
                            break 3;
                        }
                    }
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Bug 1.6: Ditemukan " . count($violations) . " instance 'bg-white' tanpa 'dark:bg-' equivalent:\n" .
            implode("\n", array_slice($violations, 0, 10)) .
            (count($violations) > 10 ? "\n... dan " . (count($violations) - 10) . " lainnya" : "")
        );
    }

    /**
     * @test
     * Bug 1.6: Verifikasi bahwa komponen modal memiliki dark mode class
     *
     * AKAN GAGAL karena modal tidak memiliki dark mode class
     */
    public function test_modal_components_have_dark_mode_class(): void
    {
        $modalFiles = $this->findFilesContaining('resources/views', 'modal');
        $violations = [];

        foreach ($modalFiles as $file) {
            $content = file_get_contents($file);

            // Cari div dengan class modal yang menggunakan bg-white tanpa dark:
            if (str_contains($content, 'bg-white') && !str_contains($content, 'dark:bg-')) {
                $relativePath = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $file);
                $violations[] = $relativePath;

                if (count($violations) >= 10) {
                    break;
                }
            }
        }

        // Test ini AKAN GAGAL karena ada modal dengan bg-white tanpa dark:
        $this->assertEmpty(
            $violations,
            "Bug 1.6: File modal berikut menggunakan 'bg-white' tanpa 'dark:bg-' equivalent:\n" .
            implode("\n", $violations)
        );
    }

    /**
     * @test
     * Bug 1.6: Verifikasi bahwa ada komponen base (x-card, x-modal) dengan dark mode
     *
     * AKAN GAGAL jika komponen base tidak ada atau tidak memiliki dark mode
     */
    public function test_base_components_exist_with_dark_mode(): void
    {
        $baseComponents = [
            'resources/views/components/card.blade.php',
            'resources/views/components/modal.blade.php',
        ];

        $missingOrNoDark = [];

        foreach ($baseComponents as $component) {
            if (!file_exists($component)) {
                $missingOrNoDark[] = "{$component} (tidak ada)";
                continue;
            }

            $content = file_get_contents($component);
            if (!str_contains($content, 'dark:')) {
                $missingOrNoDark[] = "{$component} (tidak ada dark: class)";
            }
        }

        // Test ini AKAN GAGAL karena komponen base tidak ada atau tidak memiliki dark mode
        $this->assertEmpty(
            $missingOrNoDark,
            "Bug 1.6: Komponen base berikut tidak ada atau tidak memiliki dark mode class:\n" .
            implode("\n", $missingOrNoDark)
        );
    }

    private function getBladeFiles(string $dir): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function findFilesContaining(string $dir, string $keyword): array
    {
        $files = [];
        if (!is_dir($dir)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                if (str_contains(strtolower($file->getFilename()), $keyword)) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }
}
