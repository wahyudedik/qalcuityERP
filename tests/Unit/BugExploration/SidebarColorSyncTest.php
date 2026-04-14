<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.2 — Warna Aksen Tidak Sinkron Setelah Klik Rail Button
 *
 * Verifikasi bahwa kode JS di app.blade.php melakukan sinkronisasi
 * CSS custom property --group-color saat rail button diklik (via buildPanel).
 *
 * Task 12.1: Re-run test dari task 1 pada kode yang sudah diperbaiki.
 * EXPECTED OUTCOME: SEMUA LULUS
 *
 * Validates: Requirements 2.2
 */
class SidebarColorSyncTest extends TestCase
{
    private string $appBladeFile = 'resources/views/layouts/app.blade.php';
    private array $jsFiles = [
        'resources/js/app.js',
        'resources/js/sidebar.js',
    ];

    /**
     * @test
     * Bug 1.2 FIX: Verifikasi bahwa ada handler yang meng-update --group-color saat klik rail button
     *
     * Setelah fix, buildPanel() dipanggil saat rail button diklik dan
     * meng-update --group-color di document.documentElement dan #sidebar-panel.
     *
     * Validates: Requirements 2.2
     */
    public function test_rail_button_click_handler_updates_group_color(): void
    {
        $appBladeContent = '';
        if (file_exists($this->appBladeFile)) {
            $appBladeContent = file_get_contents($this->appBladeFile);
        }

        $jsContent = '';
        foreach ($this->jsFiles as $jsFile) {
            if (file_exists($jsFile)) {
                $jsContent .= file_get_contents($jsFile);
            }
        }

        $allContent = $appBladeContent . $jsContent;

        // FIX: --group-color di-set di buildPanel() yang dipanggil saat rail button diklik
        $hasColorSyncOnClick = (
            str_contains($allContent, "setProperty('--group-color'") ||
            str_contains($allContent, 'setProperty("--group-color"')
        );

        $this->assertTrue(
            $hasColorSyncOnClick,
            "Bug 1.2 FIX: Tidak ditemukan setProperty('--group-color') di kode. " .
            "Setelah fix, buildPanel() harus meng-update CSS custom property '--group-color' " .
            "saat rail button diklik."
        );
    }

    /**
     * @test
     * Bug 1.2 FIX: Verifikasi bahwa buildPanel() meng-update --group-color
     *
     * Setelah fix, buildPanel() (bukan toggleGroup()) yang bertanggung jawab
     * meng-update --group-color di document.documentElement dan panel element.
     *
     * Validates: Requirements 2.2
     */
    public function test_toggle_group_function_syncs_color(): void
    {
        $appBladeContent = '';
        if (file_exists($this->appBladeFile)) {
            $appBladeContent = file_get_contents($this->appBladeFile);
        }

        // FIX: buildPanel() meng-update --group-color (bukan toggleGroup())
        // Cari fungsi buildPanel yang mengupdate --group-color
        $hasBuildPanel = str_contains($appBladeContent, 'function buildPanel');
        $this->assertTrue($hasBuildPanel, "Fungsi buildPanel tidak ditemukan di sidebar");

        // Verifikasi buildPanel mengupdate --group-color
        $buildPanelPattern = '/function buildPanel\s*\([^)]*\)\s*\{.*?(?=\nfunction |\z)/s';
        preg_match($buildPanelPattern, $appBladeContent, $matches);
        $buildPanelCode = $matches[0] ?? '';

        $updatesGroupColor = str_contains($buildPanelCode, '--group-color') ||
            str_contains($buildPanelCode, 'group-color') ||
            str_contains($buildPanelCode, 'groupColor');

        $this->assertTrue(
            $updatesGroupColor,
            "Bug 1.2 FIX: Fungsi buildPanel() tidak mengupdate CSS custom property '--group-color'. " .
            "Warna panel header tidak akan berubah saat rail button diklik. " .
            "Kode buildPanel yang ditemukan: " . substr($buildPanelCode, 0, 300)
        );
    }

    /**
     * @test
     * Bug 1.2 FIX: Verifikasi bahwa panel accent line tersinkronisasi dengan rail button color
     *
     * Validates: Requirements 2.2
     */
    public function test_panel_accent_syncs_with_rail_button_color(): void
    {
        $appBladeContent = '';
        if (file_exists($this->appBladeFile)) {
            $appBladeContent = file_get_contents($this->appBladeFile);
        }

        // FIX: buildPanel() mengupdate panel-accent via setProperty atau style.background
        $hasPanelAccentSync = (
            str_contains($appBladeContent, 'panel-accent') &&
            (
                str_contains($appBladeContent, 'setProperty') ||
                str_contains($appBladeContent, 'style.background') ||
                str_contains($appBladeContent, 'style.setProperty')
            )
        );

        $this->assertTrue(
            $hasPanelAccentSync,
            "Bug 1.2 FIX: Tidak ditemukan kode yang mensinkronisasi warna panel-accent " .
            "dengan warna rail button yang aktif. " .
            "buildPanel() harus mengupdate panel-accent saat rail button diklik."
        );
    }
}
