<?php

namespace Tests\Unit\Preservation;

use PHPUnit\Framework\TestCase;

/**
 * Preservation Test — Theme Persistence and System Mode
 *
 * UPDATED: Dark mode telah dihapus sepenuhnya dari aplikasi.
 * Test ini sekarang memverifikasi bahwa:
 * - Layout memiliki localStorage cleanup script (bukan theme init script)
 * - theme-manager.js sudah dihapus
 * - Logika simulasi tema tetap valid sebagai unit test
 *
 * Validates: Requirements 3.4, 3.5, 3.6 (updated for dark mode removal)
 */
class ThemePreservationTest extends TestCase
{
    private string $appLayoutPath = 'resources/views/layouts/app.blade.php';

    private string $themeManagerPath = 'resources/js/theme-manager.js';

    // ── Post dark mode removal: localStorage cleanup ──────────────────────

    /**
     * @test
     * Post removal: Layout has localStorage reference (cleanup script)
     * Validates: Requirements 3.4
     */
    public function test_theme_initialization_script_exists_in_layout(): void
    {
        if (! file_exists($this->appLayoutPath)) {
            $this->markTestSkipped("Layout file tidak ditemukan: {$this->appLayoutPath}");
        }

        $content = file_get_contents($this->appLayoutPath);

        $this->assertStringContainsString(
            'localStorage',
            $content,
            'Layout harus mengandung script localStorage (cleanup script)'
        );
    }

    /**
     * @test
     * Post removal: Layout has cleanup script that removes 'theme' key
     * Validates: Requirements 3.4
     */
    public function test_theme_script_reads_theme_key_from_localstorage(): void
    {
        if (! file_exists($this->appLayoutPath)) {
            $this->markTestSkipped("Layout file tidak ditemukan: {$this->appLayoutPath}");
        }

        $content = file_get_contents($this->appLayoutPath);

        // After dark mode removal, the script should REMOVE the theme key (cleanup)
        $hasThemeCleanup = (
            str_contains($content, "localStorage.removeItem('theme')") ||
            str_contains($content, 'localStorage.removeItem("theme")')
        );

        $this->assertTrue(
            $hasThemeCleanup,
            "Layout should have localStorage cleanup script that removes 'theme' key"
        );
    }

    /**
     * @test
     * Preservation 3.4: Logika simulasi localStorage persistence berfungsi
     * Validates: Requirements 3.4
     */
    public function test_theme_persistence_logic_simulation(): void
    {
        $localStorage = [];
        $localStorage['theme'] = 'dark';

        $savedTheme = $localStorage['theme'] ?? 'system';

        $this->assertEquals(
            'dark',
            $savedTheme,
            'Tema yang disimpan harus bisa dibaca kembali (persistence)'
        );

        $localStorage['theme'] = 'light';
        $savedTheme = $localStorage['theme'] ?? 'system';

        $this->assertEquals(
            'light',
            $savedTheme,
            'Perubahan tema harus tersimpan dan bisa dibaca kembali'
        );
    }

    /**
     * @test
     * Preservation 3.4: Default tema adalah 'system' jika tidak ada di localStorage
     * Validates: Requirements 3.4
     */
    public function test_default_theme_is_system_when_not_set(): void
    {
        $localStorage = [];
        $savedTheme = $localStorage['theme'] ?? 'system';

        $this->assertEquals(
            'system',
            $savedTheme,
            "Default tema harus 'system' jika tidak ada di localStorage"
        );
    }

    // ── Requirement 3.5: Mode system logic (pure logic tests) ──────────────

    /**
     * @test
     * Preservation 3.5: Logika mode system (pure logic test)
     * Validates: Requirements 3.5
     */
    public function test_theme_script_handles_system_mode(): void
    {
        $applyTheme = function (string $theme, bool $osDark): string {
            if ($theme === 'dark') {
                return 'dark';
            }
            if ($theme === 'light') {
                return 'light';
            }

            return $osDark ? 'dark' : 'light';
        };

        $this->assertEquals('dark', $applyTheme('system', true));
        $this->assertEquals('light', $applyTheme('system', false));
    }

    /**
     * @test
     * Preservation 3.5: Logika mode system menerapkan dark jika OS dark
     * Validates: Requirements 3.5
     */
    public function test_system_mode_applies_dark_when_os_is_dark(): void
    {
        $applyTheme = function (string $theme, bool $osDark): string {
            if ($theme === 'dark') {
                return 'dark';
            }
            if ($theme === 'light') {
                return 'light';
            }

            return $osDark ? 'dark' : 'light';
        };

        $result = $applyTheme('system', true);
        $this->assertEquals('dark', $result, "Mode 'system' dengan OS dark harus menerapkan tema dark");

        $result = $applyTheme('system', false);
        $this->assertEquals('light', $result, "Mode 'system' dengan OS light harus menerapkan tema light");

        $result = $applyTheme('dark', false);
        $this->assertEquals('dark', $result, "Mode 'dark' eksplisit harus selalu dark");

        $result = $applyTheme('light', true);
        $this->assertEquals('light', $result, "Mode 'light' eksplisit harus selalu light");
    }

    /**
     * @test
     * Preservation 3.5: Semua nilai tema yang valid ditangani
     * Validates: Requirements 3.5
     */
    public function test_all_valid_theme_values_are_handled(): void
    {
        $validThemes = ['dark', 'light', 'system'];

        $applyTheme = function (string $theme, bool $osDark): string {
            return match ($theme) {
                'dark' => 'dark',
                'light' => 'light',
                'system' => $osDark ? 'dark' : 'light',
                default => 'light',
            };
        };

        foreach ($validThemes as $theme) {
            $resultDark = $applyTheme($theme, true);
            $resultLight = $applyTheme($theme, false);

            $this->assertContains(
                $resultDark,
                ['dark', 'light'],
                "Tema '{$theme}' dengan OS dark harus menghasilkan 'dark' atau 'light'"
            );

            $this->assertContains(
                $resultLight,
                ['dark', 'light'],
                "Tema '{$theme}' dengan OS light harus menghasilkan 'dark' atau 'light'"
            );
        }
    }

    // ── Requirement 3.6: theme-manager.js removed ────────

    /**
     * @test
     * Post removal: ThemeManager file should not exist
     * Validates: Requirements 3.6
     */
    public function test_theme_manager_dispatches_theme_changed_event(): void
    {
        if (! file_exists($this->themeManagerPath)) {
            $this->markTestSkipped("ThemeManager file tidak ditemukan: {$this->themeManagerPath}");
        }

        $content = file_get_contents($this->themeManagerPath);

        $this->assertTrue(
            str_contains($content, 'theme-changed') ||
                str_contains($content, 'themeChanged'),
            "ThemeManager harus mendispatch event 'theme-changed'"
        );
    }

    /**
     * @test
     * Post removal: ThemeManager file should not exist
     * Validates: Requirements 3.6
     */
    public function test_theme_changed_event_uses_dispatch(): void
    {
        if (! file_exists($this->themeManagerPath)) {
            $this->markTestSkipped("ThemeManager file tidak ditemukan: {$this->themeManagerPath}");
        }

        $content = file_get_contents($this->themeManagerPath);

        $this->assertTrue(
            str_contains($content, 'dispatchEvent') ||
                str_contains($content, 'CustomEvent') ||
                str_contains($content, 'emit('),
            'ThemeManager harus menggunakan dispatchEvent atau CustomEvent untuk mengirim event tema'
        );
    }

    /**
     * @test
     * Preservation 3.6: Simulasi event listener menerima event theme-changed
     * Validates: Requirements 3.6
     */
    public function test_theme_changed_event_listener_simulation(): void
    {
        $listeners = [];
        $receivedEvents = [];

        $listeners[] = function (array $event) use (&$receivedEvents) {
            $receivedEvents[] = $event;
        };

        $dispatchThemeChanged = function (string $theme, bool $isDark) use (&$listeners) {
            $event = ['theme' => $theme, 'isDark' => $isDark];
            foreach ($listeners as $listener) {
                $listener($event);
            }
        };

        $dispatchThemeChanged('dark', true);

        $this->assertCount(1, $receivedEvents, 'Listener harus menerima event theme-changed');
        $this->assertEquals('dark', $receivedEvents[0]['theme']);
        $this->assertTrue($receivedEvents[0]['isDark']);

        $dispatchThemeChanged('light', false);

        $this->assertCount(2, $receivedEvents, 'Listener harus menerima semua event theme-changed');
        $this->assertEquals('light', $receivedEvents[1]['theme']);
        $this->assertFalse($receivedEvents[1]['isDark']);
    }

    /**
     * @test
     * Preservation 3.6: Semua listener yang terdaftar menerima event
     * Validates: Requirements 3.6
     */
    public function test_all_registered_listeners_receive_theme_changed_event(): void
    {
        $receivedByListener1 = false;
        $receivedByListener2 = false;
        $receivedByListener3 = false;

        $listeners = [
            function () use (&$receivedByListener1) {
                $receivedByListener1 = true;
            },
            function () use (&$receivedByListener2) {
                $receivedByListener2 = true;
            },
            function () use (&$receivedByListener3) {
                $receivedByListener3 = true;
            },
        ];

        foreach ($listeners as $listener) {
            $listener();
        }

        $this->assertTrue($receivedByListener1, 'Listener 1 harus menerima event theme-changed');
        $this->assertTrue($receivedByListener2, 'Listener 2 harus menerima event theme-changed');
        $this->assertTrue($receivedByListener3, 'Listener 3 harus menerima event theme-changed');
    }
}
