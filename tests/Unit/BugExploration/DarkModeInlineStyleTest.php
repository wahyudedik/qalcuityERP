<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.7 — Inline Style Hardcoded Override Dark Mode
 *
 * Scan file blade untuk inline style yang bisa override tema dark mode.
 * PDF templates dan thermal receipt dikecualikan karena output cetak
 * memang harus putih terlepas dari dark mode.
 */
class DarkModeInlineStyleTest extends TestCase
{
    private function shouldSkipFile(string $relativePath): bool
    {
        // PDF templates — print output must be white regardless of dark mode
        if (str_contains($relativePath, '/pdf/') || str_contains($relativePath, '\\pdf\\')) {
            return true;
        }
        return false;
    }

    private function shouldSkipLine(string $line): bool
    {
        // Thermal receipt and print-specific elements are intentionally white
        // Also skip JavaScript template literals (used for dynamic HTML generation)
        return str_contains($line, 'thermal') ||
               str_contains($line, 'receipt-content') ||
               str_contains($line, 'print-receipt') ||
               str_contains(trim($line), 'return `') ||
               (str_contains($line, '`') && str_contains($line, 'style='));
    }

    /**
     * @test
     * Bug 1.7: Scan blade files untuk inline style background putih
     */
    public function test_no_hardcoded_white_background_inline_style(): void
    {
        $violations = [];
        $viewDir = 'resources/views';

        if (!is_dir($viewDir)) {
            $this->markTestSkipped("Directory {$viewDir} tidak ditemukan");
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($viewDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $patterns = [
            '/style=["\'][^"\']*background:\s*#fff["\'\s;]/',
            '/style=["\'][^"\']*background:\s*white["\'\s;]/',
            '/style=["\'][^"\']*background-color:\s*#fff["\'\s;]/',
            '/style=["\'][^"\']*background-color:\s*white["\'\s;]/',
            '/style=["\'][^"\']*background:\s*#ffffff["\'\s;]/',
        ];

        foreach ($iterator as $file) {
            if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $relativePath = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $file->getPathname());

            if ($this->shouldSkipFile($relativePath)) {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            $lines = explode("\n", $content);

            foreach ($lines as $lineNum => $line) {
                if ($this->shouldSkipLine($line)) {
                    continue;
                }
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $line)) {
                        $violations[] = "{$relativePath}:{$lineNum}: " . trim($line);
                        if (count($violations) >= 15) {
                            break 3;
                        }
                        break;
                    }
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Bug 1.7: Ditemukan " . count($violations) . " inline style dengan background putih hardcoded " .
            "yang akan override dark mode:\n" .
            implode("\n", array_slice($violations, 0, 10))
        );
    }

    /**
     * @test
     * Bug 1.7: Scan blade files untuk inline style color hitam
     */
    public function test_no_hardcoded_black_color_inline_style(): void
    {
        $violations = [];
        $viewDir = 'resources/views';

        if (!is_dir($viewDir)) {
            $this->markTestSkipped("Directory {$viewDir} tidak ditemukan");
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($viewDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $patterns = [
            '/style=["\'][^"\']*(?<!background-)color:\s*#000["\'\s;]/',
            '/style=["\'][^"\']*(?<!background-)color:\s*black["\'\s;]/',
            '/style=["\'][^"\']*(?<!background-)color:\s*#333["\'\s;]/',
            '/style=["\'][^"\']*(?<!background-)color:\s*#222["\'\s;]/',
        ];

        foreach ($iterator as $file) {
            if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $relativePath = str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $file->getPathname());

            if ($this->shouldSkipFile($relativePath)) {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            $lines = explode("\n", $content);

            foreach ($lines as $lineNum => $line) {
                if ($this->shouldSkipLine($line)) {
                    continue;
                }
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $line)) {
                        $violations[] = "{$relativePath}:{$lineNum}: " . trim($line);
                        if (count($violations) >= 15) {
                            break 3;
                        }
                        break;
                    }
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Bug 1.7: Ditemukan " . count($violations) . " inline style dengan color hitam hardcoded " .
            "yang akan override dark mode:\n" .
            implode("\n", array_slice($violations, 0, 10))
        );
    }
}
