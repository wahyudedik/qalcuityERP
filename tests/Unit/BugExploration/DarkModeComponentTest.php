<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.6 — Komponen Modal/Card Tidak Memiliki Dark Mode Class
 *
 * UPDATED: Dark mode telah dihapus sepenuhnya dari aplikasi.
 * Test ini sekarang memverifikasi bahwa TIDAK ADA dark: class tersisa
 * di komponen inti, sesuai dengan keputusan remove dark mode.
 */
class DarkModeComponentTest extends TestCase
{
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
     * Post dark mode removal: Verifikasi tidak ada bg-white dengan dark: equivalent
     * (dark: classes sudah dihapus, jadi bg-white tanpa dark: adalah expected)
     */
    public function test_no_bg_white_without_dark_equivalent(): void
    {
        // After dark mode removal, bg-white without dark: equivalent is the correct state.
        // This test now verifies that no dark: classes remain.
        foreach ($this->coreViewDirs as $dir) {
            if (! is_dir($dir)) {
                continue;
            }

            $files = $this->getBladeFiles($dir);

            foreach ($files as $file) {
                $content = file_get_contents($file);
                $this->assertStringNotContainsString(
                    'dark:bg-',
                    $content,
                    "File {$file} should not contain dark:bg- classes after dark mode removal"
                );
            }
        }

        $this->assertTrue(true, 'No dark: classes found in core view directories');
    }

    /**
     * @test
     * Post dark mode removal: Modal components should NOT have dark mode class
     */
    public function test_modal_components_have_dark_mode_class(): void
    {
        $modalFiles = $this->findFilesContaining('resources/views', 'modal');

        foreach ($modalFiles as $file) {
            $content = file_get_contents($file);
            $relativePath = str_replace(getcwd().DIRECTORY_SEPARATOR, '', $file);

            $this->assertStringNotContainsString(
                'dark:bg-',
                $content,
                "Modal file {$relativePath} should not contain dark:bg- classes after dark mode removal"
            );
        }

        $this->assertTrue(true, 'No dark: classes found in modal components');
    }

    /**
     * @test
     * Post dark mode removal: Base components should NOT have dark mode classes
     */
    public function test_base_components_exist_with_dark_mode(): void
    {
        $baseComponents = [
            'resources/views/components/card.blade.php',
            'resources/views/components/modal.blade.php',
        ];

        foreach ($baseComponents as $component) {
            if (! file_exists($component)) {
                // Component not existing is acceptable
                continue;
            }

            $content = file_get_contents($component);
            $this->assertStringNotContainsString(
                'dark:',
                $content,
                "Component {$component} should not contain dark: classes after dark mode removal"
            );
        }

        $this->assertTrue(true, 'No dark: classes found in base components');
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
        if (! is_dir($dir)) {
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
