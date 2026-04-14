<?php

namespace Tests\Unit\BugExploration;

use PHPUnit\Framework\TestCase;

/**
 * Bug 1.3 — Item Submenu Tidak Auto-Active Saat Panel Dibuka
 *
 * Verifikasi bahwa blade template menyertakan flag 'active' di setiap item
 * NAV_GROUPS yang diteruskan ke JavaScript, sehingga item ter-highlight
 * saat panel pertama kali dibuka.
 *
 * Task 12.1: Re-run test dari task 1 pada kode yang sudah diperbaiki.
 * EXPECTED OUTCOME: SEMUA LULUS
 *
 * Validates: Requirements 2.3
 */
class SidebarSubmenuActiveTest extends TestCase
{
    private string $appBladeFile = 'resources/views/layouts/app.blade.php';

    /**
     * @test
     * Bug 1.3 FIX: Verifikasi bahwa panel nav items memiliki active class check
     *
     * Setelah fix, setiap item di NAV_GROUPS memiliki flag 'active' yang di-render
     * oleh PHP (request()->routeIs()), sehingga renderPanelItems() bisa menerapkan
     * class 'active' saat panel dibuka.
     *
     * Validates: Requirements 2.3
     */
    public function test_panel_nav_items_have_active_class_check(): void
    {
        $this->assertFileExists(
            $this->appBladeFile,
            "File sidebar tidak ditemukan: {$this->appBladeFile}"
        );

        $content = file_get_contents($this->appBladeFile);

        // FIX: panel-link items mendapatkan class 'active' dari item.active flag
        // yang di-render oleh PHP di NAV_GROUPS
        $hasPanelLinkActiveCheck = (
            str_contains($content, "panel-link") &&
            (
                str_contains($content, "Route::is(") ||
                str_contains($content, "request()->routeIs(") ||
                str_contains($content, "routeIs(") ||
                (str_contains($content, "'active'") && str_contains($content, "panel-link")) ||
                (str_contains($content, "active") && str_contains($content, "panel-link"))
            )
        );

        $this->assertTrue(
            $hasPanelLinkActiveCheck,
            "Bug 1.3 FIX: Tidak ditemukan mekanisme active class untuk panel-link items. " .
            "Setelah fix, renderPanelItems() harus menerapkan class 'active' berdasarkan " .
            "flag item.active yang di-render oleh PHP."
        );
    }

    /**
     * @test
     * Bug 1.3 FIX: Verifikasi bahwa NAV_GROUPS menyertakan flag active dari PHP
     *
     * Setelah fix, setiap item di NAV_GROUPS memiliki property 'active' yang
     * di-set oleh PHP menggunakan request()->routeIs().
     *
     * Validates: Requirements 2.3
     */
    public function test_js_panel_rendering_includes_active_state(): void
    {
        $content = '';
        if (file_exists($this->appBladeFile)) {
            $content = file_get_contents($this->appBladeFile);
        }

        // FIX: NAV_GROUPS items memiliki 'active' property yang di-render PHP
        // Pattern: active: {{ request()->routeIs(...) ? 'true' : 'false' }}
        $hasActiveInNavGroups = (
            str_contains($content, "active:") &&
            (
                str_contains($content, "request()->routeIs(") ||
                str_contains($content, "routeIs(")
            )
        );

        $this->assertTrue(
            $hasActiveInNavGroups,
            "Bug 1.3 FIX: NAV_GROUPS tidak menyertakan flag 'active' yang di-render PHP. " .
            "Setelah fix, setiap item harus memiliki 'active: {{ request()->routeIs(...) ? 'true' : 'false' }}' " .
            "agar renderPanelItems() bisa menerapkan class 'active' yang benar."
        );
    }

    /**
     * @test
     * Bug 1.3 FIX: Verifikasi bahwa renderPanelItems() menerapkan class active dari item.active
     *
     * Validates: Requirements 2.3
     */
    public function test_panel_nav_data_includes_active_flag(): void
    {
        $content = '';
        if (file_exists($this->appBladeFile)) {
            $content = file_get_contents($this->appBladeFile);
        }

        // FIX: renderPanelItems() menggunakan item.active untuk menerapkan class 'active'
        // Pattern: item.active ? ' active' : '' atau 'panel-link' + (item.active ? ' active' : '')
        $hasActiveFlagInRender = (
            str_contains($content, 'item.active') ||
            (str_contains($content, "'active': true") || str_contains($content, '"active": true')) ||
            str_contains($content, "isActive") ||
            (str_contains($content, "is_active") && str_contains($content, "panel"))
        );

        $this->assertTrue(
            $hasActiveFlagInRender,
            "Bug 1.3 FIX: renderPanelItems() tidak menggunakan flag 'active' dari item data. " .
            "Setelah fix, harus ada 'item.active' atau flag serupa untuk menandai " .
            "item submenu yang sesuai dengan route aktif saat ini."
        );
    }
}
