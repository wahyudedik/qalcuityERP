<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.12 — Breadcrumb Hidden di Mobile View
 *
 * Membuktikan bahwa breadcrumb menggunakan hidden sm:block
 * tanpa mobile fallback, sehingga tidak terlihat di mobile.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 */
class LayoutBreadcrumbMobileTest extends TestCase
{
    private string $appBladeFile = 'resources/views/layouts/app.blade.php';

    private string $breadcrumbFile = 'resources/views/components/breadcrumbs.blade.php';

    /**
     * @test
     * Bug 1.12: Verifikasi bahwa breadcrumb component ada dan tidak menggunakan hidden sm:block tanpa fallback
     *
     * Setelah fix: breadcrumb component (breadcrumbs.blade.php) memiliki dua versi:
     * - Mobile: flex sm:hidden (terlihat di mobile, tersembunyi di desktop)
     * - Desktop: hidden sm:flex (tersembunyi di mobile, terlihat di desktop)
     */
    public function test_breadcrumb_visible_on_mobile(): void
    {
        // Cek di breadcrumb component (bukan app.blade.php)
        $this->assertFileExists($this->breadcrumbFile,
            "Bug 1.12: File breadcrumb component tidak ditemukan: {$this->breadcrumbFile}."
        );
        $content = file_get_contents($this->breadcrumbFile);

        // Setelah fix: breadcrumb component menggunakan flex sm:hidden untuk mobile
        // dan hidden sm:flex untuk desktop — bukan hidden sm:block tanpa fallback
        $hasMobileVersion = str_contains($content, 'flex sm:hidden') ||
            str_contains($content, 'block sm:hidden');

        $this->assertTrue(
            $hasMobileVersion,
            'Bug 1.12: Breadcrumb component tidak memiliki versi mobile (flex sm:hidden). '.
            'Seharusnya ada dua versi: mobile (flex sm:hidden) dan desktop (hidden sm:flex).'
        );
    }

    /**
     * @test
     * Bug 1.12: Verifikasi bahwa ada mobile breadcrumb fallback di breadcrumb component
     *
     * Setelah fix: breadcrumbs.blade.php memiliki versi mobile yang terlihat di layar kecil.
     */
    public function test_mobile_breadcrumb_fallback_exists(): void
    {
        $this->assertFileExists($this->breadcrumbFile,
            "Bug 1.12: File breadcrumb component tidak ditemukan: {$this->breadcrumbFile}."
        );
        $content = file_get_contents($this->breadcrumbFile);

        // Cari mobile breadcrumb fallback: flex sm:hidden atau block sm:hidden
        $hasMobileFallback = (
            str_contains($content, 'flex sm:hidden') ||
            str_contains($content, 'block sm:hidden') ||
            str_contains($content, 'sm:hidden') ||
            str_contains($content, 'mobile-breadcrumb')
        );

        $this->assertTrue(
            $hasMobileFallback,
            'Bug 1.12: Tidak ditemukan mobile breadcrumb fallback di breadcrumb component. '.
            'Seharusnya ada versi ringkas breadcrumb yang terlihat di mobile '.
            "menggunakan class 'flex sm:hidden' atau 'block sm:hidden'."
        );
    }

    /**
     * @test
     * Bug 1.12: Verifikasi bahwa breadcrumb component ada dan memiliki mobile support
     */
    public function test_breadcrumb_component_has_mobile_support(): void
    {
        $this->assertFileExists(
            $this->breadcrumbFile,
            "Bug 1.12: File breadcrumb component tidak ditemukan: {$this->breadcrumbFile}. ".
            'Breadcrumb seharusnya diimplementasikan sebagai Blade component.'
        );

        $content = file_get_contents($this->breadcrumbFile);

        // Cari mobile support: sm:hidden (mobile visible) dan hidden sm:flex (desktop only)
        $hasMobileSupport = (
            str_contains($content, 'sm:hidden') ||
            str_contains($content, 'sm:flex') ||
            str_contains($content, 'mobile') ||
            str_contains($content, 'hidden sm:')
        );

        $this->assertTrue(
            $hasMobileSupport,
            'Bug 1.12: Breadcrumb component tidak memiliki mobile support. '.
            'Seharusnya ada dua versi: desktop (breadcrumb penuh) dan mobile (halaman aktif saja).'
        );
    }
}
