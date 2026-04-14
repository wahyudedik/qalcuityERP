<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.8 — FOUC (Flash of Unstyled Content) dengan theme=system
 *
 * Membuktikan bahwa script inisialisasi tema di <head> tidak menangani
 * theme === 'system' dengan window.matchMedia sebelum render pertama.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 */
class DarkModeFoucTest extends TestCase
{
    private string $appBladeFile = 'resources/views/layouts/app.blade.php';

    /**
     * @test
     * Bug 1.8: Verifikasi bahwa script inisialisasi tema menangani theme=system
     *
     * Script di <head> seharusnya menangani:
     * - theme === 'dark' → tambah class 'dark'
     * - theme === 'light' → hapus class 'dark'
     * - theme === 'system' → cek window.matchMedia('(prefers-color-scheme: dark)')
     *
     * AKAN GAGAL karena script hanya menangani theme === 'light'
     */
    public function test_theme_init_script_handles_system_theme(): void
    {
        $this->assertFileExists($this->appBladeFile);
        $content = file_get_contents($this->appBladeFile);

        // Cari script inisialisasi tema di <head>
        // Berdasarkan kode aktual: hanya ada pengecekan theme === 'light'
        $hasSystemThemeHandling = (
            str_contains($content, "theme === 'system'") ||
            str_contains($content, 'theme === "system"') ||
            str_contains($content, "prefers-color-scheme") ||
            str_contains($content, 'matchMedia')
        );

        // Test ini AKAN GAGAL karena script hanya menangani theme === 'light'
        // Kode aktual: if (localStorage.getItem('theme') === 'light') { remove dark }
        $this->assertTrue(
            $hasSystemThemeHandling,
            "Bug 1.8: Script inisialisasi tema di <head> tidak menangani theme === 'system'. " .
            "Pengguna dengan preferensi OS dark mode akan mengalami FOUC (flash putih) " .
            "saat halaman pertama kali dimuat karena tema tidak diterapkan sebelum render. " .
            "Kode aktual hanya menangani theme === 'light'."
        );
    }

    /**
     * @test
     * Bug 1.8: Verifikasi bahwa script inisialisasi tema adalah IIFE (Immediately Invoked)
     *
     * Script harus dijalankan segera (IIFE) sebelum render pertama untuk mencegah FOUC.
     * AKAN GAGAL jika script tidak menggunakan IIFE pattern
     */
    public function test_theme_init_script_is_iife(): void
    {
        $this->assertFileExists($this->appBladeFile);
        $content = file_get_contents($this->appBladeFile);

        // Cari IIFE pattern: (function() { ... })() atau (() => { ... })()
        $hasIife = (
            preg_match('/\(function\s*\(\)\s*\{/', $content) ||
            preg_match('/\(\(\)\s*=>\s*\{/', $content) ||
            preg_match('/\(function\s*\(\)\s*\{[^}]*localStorage[^}]*\}\)\(\)/', $content)
        );

        // Test ini AKAN GAGAL karena script tidak menggunakan IIFE
        $this->assertTrue(
            $hasIife,
            "Bug 1.8: Script inisialisasi tema tidak menggunakan IIFE pattern. " .
            "Script harus dijalankan segera (immediately invoked) sebelum render pertama " .
            "untuk mencegah FOUC. Gunakan: (function() { ... })() atau (() => { ... })()"
        );
    }

    /**
     * @test
     * Bug 1.8: Verifikasi bahwa script inisialisasi tema berada di <head> sebelum @vite
     *
     * Script harus berada di <head> SEBELUM stylesheet dimuat untuk mencegah FOUC.
     * AKAN GAGAL jika script berada setelah @vite
     */
    public function test_theme_init_script_is_before_vite_in_head(): void
    {
        $this->assertFileExists($this->appBladeFile);
        $content = file_get_contents($this->appBladeFile);

        // Cari posisi script tema
        $scriptPos = strpos($content, "localStorage.getItem('theme')");

        if ($scriptPos === false) {
            $this->fail(
                "Bug 1.8: Script inisialisasi tema tidak ditemukan di blade file. " .
                "Tidak ada script yang membaca localStorage untuk tema."
            );
        }

        // Cari posisi @vite directive yang sebenarnya (bukan dalam komentar)
        // Gunakan regex untuk menemukan @vite yang diikuti tanda kurung (directive aktual)
        $vitePos = false;
        if (preg_match('/\n\s*@vite\s*\(/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $vitePos = $matches[0][1];
        }

        if ($vitePos === false) {
            $this->markTestSkipped("Directive @vite tidak ditemukan di blade file.");
        }

        // Script tema harus berada SEBELUM @vite untuk mencegah FOUC
        $this->assertLessThan(
            $vitePos,
            $scriptPos,
            "Bug 1.8: Script inisialisasi tema berada SETELAH @vite directive. " .
            "Script harus berada di <head> SEBELUM stylesheet dimuat untuk mencegah FOUC."
        );
    }
}
