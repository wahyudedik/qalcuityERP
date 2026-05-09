<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.11 — Topbar Actions Overflow
 *
 * Membuktikan bahwa $topbarActions slot ada di navbar/topbar
 * yang menyebabkan overflow di layar kecil.
 *
 * EXPECTED: Test ini HARUS GAGAL pada kode unfixed.
 */
class LayoutTopbarTest extends TestCase
{
    private string $appBladeFile = 'resources/views/layouts/app.blade.php';

    /**
     * @test
     * Bug 1.11: Verifikasi bahwa $topbarActions ada di dalam navbar (bukan page content)
     *
     * Berdasarkan kode aktual, $topbarActions ada di dalam <header> navbar.
     * Setelah fix, seharusnya dipindahkan ke page content area.
     *
     * AKAN GAGAL karena $topbarActions masih ada di navbar
     */
    public function test_topbar_actions_not_in_navbar(): void
    {
        $this->assertFileExists($this->appBladeFile);
        $content = file_get_contents($this->appBladeFile);

        // Cari apakah $topbarActions ada di dalam <header> atau <nav>
        // Berdasarkan kode aktual: ada di dalam <header class="sticky top-0 z-20 h-14...">
        $headerPattern = '/<header[^>]*>.*?\$topbarActions.*?<\/header>/s';
        $isInHeader = preg_match($headerPattern, $content);

        // Test ini AKAN GAGAL karena $topbarActions ada di dalam <header>
        $this->assertFalse(
            (bool) $isInHeader,
            'Bug 1.11: $topbarActions slot ditemukan di dalam <header> navbar. '.
            'Tombol aksi seharusnya berada di page content area (page header section), '.
            'bukan di topbar global yang bisa overflow di layar kecil.'
        );
    }

    /**
     * @test
     * Bug 1.11: Verifikasi bahwa ada $pageHeader slot di page content area
     *
     * Setelah fix, seharusnya ada $pageHeader slot di dalam area konten.
     * AKAN GAGAL karena $pageHeader slot belum ada
     */
    public function test_page_header_slot_exists_in_content_area(): void
    {
        $this->assertFileExists($this->appBladeFile);
        $content = file_get_contents($this->appBladeFile);

        // Cari $pageHeader slot di luar <header> navbar
        $hasPageHeaderSlot = str_contains($content, '$pageHeader') ||
            str_contains($content, 'pageHeader');

        // Test ini AKAN GAGAL karena $pageHeader slot belum ada
        $this->assertTrue(
            $hasPageHeaderSlot,
            'Bug 1.11: Tidak ditemukan $pageHeader slot di layout. '.
            "Seharusnya ada slot 'pageHeader' di dalam area konten halaman ".
            'untuk menempatkan tombol aksi halaman.'
        );
    }

    /**
     * @test
     * Bug 1.11: Verifikasi bahwa $topbarActions tidak ada di topbar sama sekali
     *
     * AKAN GAGAL karena $topbarActions masih ada di topbar
     */
    public function test_topbar_actions_slot_removed_from_topbar(): void
    {
        $this->assertFileExists($this->appBladeFile);
        $content = file_get_contents($this->appBladeFile);

        // Berdasarkan kode aktual, ada: @isset($topbarActions) di dalam <header>
        $topbarActionsInHeader = str_contains($content, '@isset($topbarActions)') ||
            str_contains($content, '{{ $topbarActions }}') ||
            str_contains($content, '{!! $topbarActions !!}');

        // Test ini AKAN GAGAL karena $topbarActions masih ada di topbar
        $this->assertFalse(
            $topbarActionsInHeader,
            'Bug 1.11: $topbarActions masih ada di topbar/navbar. '.
            'Ini menyebabkan topbar overflow di layar kecil ketika ada banyak tombol aksi. '.
            'Pindahkan ke page content area menggunakan $pageHeader slot.'
        );
    }
}
