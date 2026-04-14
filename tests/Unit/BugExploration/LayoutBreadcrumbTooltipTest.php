<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.13 — Breadcrumb Panjang Tidak Memiliki Tooltip
 *
 * Membuktikan bahwa breadcrumb tidak memiliki tooltip untuk teks panjang.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 */
class LayoutBreadcrumbTooltipTest extends TestCase
{
    private string $breadcrumbFile = 'resources/views/components/breadcrumbs.blade.php';
    private string $appBladeFile = 'resources/views/layouts/app.blade.php';

    /**
     * @test
     * Bug 1.13: Verifikasi bahwa breadcrumb memiliki tooltip untuk teks panjang
     */
    public function test_breadcrumb_has_tooltip_for_long_text(): void
    {
        $this->assertFileExists($this->breadcrumbFile,
            "Bug 1.13: File breadcrumb component tidak ditemukan: {$this->breadcrumbFile}."
        );
        $content = file_get_contents($this->breadcrumbFile);

        // Cari tooltip implementation di breadcrumb
        $hasTooltip = (
            str_contains($content, 'title=') ||
            str_contains($content, 'tooltip') ||
            str_contains($content, 'x-tooltip') ||
            str_contains($content, '@mouseenter') ||
            (str_contains($content, 'x-show') && str_contains($content, 'show'))
        );

        $this->assertTrue(
            $hasTooltip,
            "Bug 1.13: Breadcrumb tidak memiliki tooltip implementation untuk teks panjang. " .
            "Teks breadcrumb yang terpotong (truncate) tidak bisa dilihat teks lengkapnya."
        );
    }

    /**
     * @test
     * Bug 1.13: Verifikasi bahwa breadcrumb memiliki Alpine.js tooltip dengan x-data
     */
    public function test_breadcrumb_has_alpine_tooltip(): void
    {
        $this->assertFileExists($this->breadcrumbFile,
            "Bug 1.13: File breadcrumb component tidak ditemukan: {$this->breadcrumbFile}."
        );
        $content = file_get_contents($this->breadcrumbFile);

        // Cari Alpine.js tooltip pattern
        $hasAlpineTooltip = (
            str_contains($content, 'x-data') &&
            str_contains($content, 'show') &&
            (
                str_contains($content, '@mouseenter') ||
                str_contains($content, 'x-on:mouseenter')
            )
        );

        $this->assertTrue(
            $hasAlpineTooltip,
            "Bug 1.13: Tidak ditemukan Alpine.js tooltip di breadcrumb. " .
            "Seharusnya ada x-data dengan show state dan @mouseenter/@mouseleave handler " .
            "untuk menampilkan tooltip saat hover pada breadcrumb yang terpotong."
        );
    }

    /**
     * @test
     * Bug 1.13: Verifikasi bahwa breadcrumb memiliki kondisi panjang teks untuk tooltip
     *
     * Tooltip seharusnya hanya muncul jika teks > 20 karakter.
     */
    public function test_breadcrumb_tooltip_has_length_condition(): void
    {
        $this->assertFileExists($this->breadcrumbFile,
            "Bug 1.13: File breadcrumb component tidak ditemukan: {$this->breadcrumbFile}."
        );
        $content = file_get_contents($this->breadcrumbFile);

        // Cari kondisi panjang teks untuk tooltip
        $hasLengthCondition = (
            str_contains($content, '.length > 20') ||
            str_contains($content, '.length > 40') ||
            str_contains($content, 'strlen(') ||
            str_contains($content, 'mb_strlen(')
        );

        $this->assertTrue(
            $hasLengthCondition,
            "Bug 1.13: Tidak ditemukan kondisi panjang teks untuk tooltip di breadcrumb. " .
            "Tooltip seharusnya hanya muncul jika teks breadcrumb > 20 karakter."
        );
    }
}
