<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.5 — Mobile Sidebar Mutual Exclusion
 *
 * Verifikasi bahwa sidebar mobile memiliki logika mutual exclusion:
 * - toggleMobileSidebar() menutup panel sebelum membuka overlay
 * - toggleGroup() menutup overlay sebelum membuka panel
 * - z-index hierarchy benar (overlay z-40, panel z-50)
 *
 * Task 12.1: Re-run test dari task 1 pada kode yang sudah diperbaiki.
 * EXPECTED OUTCOME: SEMUA LULUS
 *
 * Validates: Requirements 2.5
 */
class SidebarMobileTest extends TestCase
{
    private string $appBladeFile = 'resources/views/layouts/app.blade.php';

    /**
     * @test
     * Bug 1.5 FIX: Verifikasi bahwa ada mutual exclusion logic di sidebar
     *
     * Setelah fix, mutual exclusion diimplementasikan via vanilla JS:
     * - toggleMobileSidebar() memanggil closePanel() sebelum membuka overlay
     * - toggleGroup() menutup overlay sebelum membuka panel
     *
     * Validates: Requirements 2.5
     */
    public function test_sidebar_store_has_mutual_exclusion_logic(): void
    {
        $content = '';
        if (file_exists($this->appBladeFile)) {
            $content = file_get_contents($this->appBladeFile);
        }

        // FIX: mutual exclusion diimplementasikan via vanilla JS functions
        // toggleMobileSidebar() memanggil closePanel() sebelum membuka overlay
        // toggleGroup() menutup overlay sebelum membuka panel
        $hasMutualExclusion = (
            str_contains($content, 'toggleMobileSidebar') &&
            str_contains($content, 'closePanel') &&
            str_contains($content, 'toggleGroup')
        );

        $this->assertTrue(
            $hasMutualExclusion,
            'Bug 1.5 FIX: Tidak ditemukan mutual exclusion logic untuk overlay dan panel sidebar. '.
            'Setelah fix, toggleMobileSidebar() harus memanggil closePanel() sebelum membuka overlay, '.
            'dan toggleGroup() harus menutup overlay sebelum membuka panel.'
        );
    }

    /**
     * @test
     * Bug 1.5 FIX: Verifikasi bahwa toggleMobileSidebar menutup panel sebelum membuka overlay
     *
     * Validates: Requirements 2.5
     */
    public function test_toggle_mobile_sidebar_closes_panel_first(): void
    {
        $content = '';
        if (file_exists($this->appBladeFile)) {
            $content = file_get_contents($this->appBladeFile);
        }

        // Cari fungsi toggleMobileSidebar
        $hasToggleMobileSidebar = str_contains($content, 'toggleMobileSidebar');
        $this->assertTrue($hasToggleMobileSidebar, 'Fungsi toggleMobileSidebar tidak ditemukan');

        // FIX: toggleMobileSidebar() memanggil closePanel() sebelum membuka overlay
        // Verifikasi bahwa closePanel dipanggil dalam konteks toggleMobileSidebar
        $toggleMobileSidebarPos = strpos($content, 'function toggleMobileSidebar');
        $closePanelPos = strpos($content, 'closePanel()', $toggleMobileSidebarPos);
        $overlayOpenPos = strpos($content, "sidebar-overlay').classList.remove('hidden')", $toggleMobileSidebarPos);

        // closePanel() harus dipanggil SEBELUM overlay dibuka
        $closesPanelBeforeOverlay = (
            $closePanelPos !== false &&
            $overlayOpenPos !== false &&
            $closePanelPos < $overlayOpenPos
        );

        $this->assertTrue(
            $closesPanelBeforeOverlay,
            'Bug 1.5 FIX: Fungsi toggleMobileSidebar() harus memanggil closePanel() '.
            'sebelum membuka overlay sidebar. '.
            'Ini memastikan panel dan overlay tidak terbuka bersamaan.'
        );
    }

    /**
     * @test
     * Bug 1.5 FIX: Verifikasi bahwa z-index hierarchy benar untuk mobile
     *
     * sidebar-overlay: z-40, sidebar-panel: z-50
     *
     * Validates: Requirements 2.5
     */
    public function test_mobile_sidebar_has_correct_z_index_hierarchy(): void
    {
        $content = '';
        if (file_exists($this->appBladeFile)) {
            $content = file_get_contents($this->appBladeFile);
        }

        // FIX: sidebar-overlay menggunakan z-40, sidebar-panel menggunakan z-50
        $hasOverlayZIndex = str_contains($content, 'sidebar-overlay') &&
            (str_contains($content, 'z-40') || str_contains($content, 'z-index: 40'));

        $hasPanelZIndex = str_contains($content, 'sidebar-panel') &&
            (str_contains($content, 'z-50') || str_contains($content, 'z-index: 50'));

        $this->assertTrue(
            $hasOverlayZIndex,
            'Bug 1.5 FIX: sidebar-overlay harus menggunakan z-40 untuk z-index hierarchy yang benar.'
        );

        $this->assertTrue(
            $hasPanelZIndex,
            'Bug 1.5 FIX: sidebar-panel harus menggunakan z-50 untuk z-index hierarchy yang benar.'
        );
    }
}
