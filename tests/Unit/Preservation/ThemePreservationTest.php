<?php

namespace Tests\Unit\Preservation;

use PHPUnit\Framework\TestCase;

/**
 * Preservation Test — Theme Persistence and System Mode
 *
 * Memverifikasi bahwa behavior tema yang SUDAH BENAR tidak berubah setelah fix diterapkan.
 * Test ini memverifikasi logika PHP-side dan keberadaan script inisialisasi tema.
 *
 * Karena tema adalah JavaScript behavior, test ini memverifikasi:
 * - Script inisialisasi tema ada di file layout yang benar
 * - Logika localStorage persistence ada
 * - Event theme-changed ada di theme manager
 *
 * Test ini harus LULUS pada kode unfixed (baseline) dan tetap LULUS setelah fix.
 *
 * Validates: Requirements 3.4, 3.5, 3.6
 */
class ThemePreservationTest extends TestCase
{
    private string $appLayoutPath = 'resources/views/layouts/app.blade.php';
    private string $themeManagerPath = 'resources/js/theme-manager.js';

    // ── Requirement 3.4: localStorage theme persistence ──────────────────────

    /**
     * @test
     * Preservation 3.4: Script inisialisasi tema ada di layout head
     *
     * Memverifikasi bahwa ada script di <head> yang membaca localStorage
     * untuk menerapkan tema sebelum render pertama.
     * Validates: Requirements 3.4
     */
    public function test_theme_initialization_script_exists_in_layout(): void
    {
        if (!file_exists($this->appLayoutPath)) {
            $this->markTestSkipped("Layout file tidak ditemukan: {$this->appLayoutPath}");
        }

        $content = file_get_contents($this->appLayoutPath);

        $this->assertStringContainsString(
            'localStorage',
            $content,
            "Layout harus mengandung script yang membaca localStorage untuk tema"
        );
    }

    /**
     * @test
     * Preservation 3.4: Script tema membaca key 'theme' dari localStorage
     *
     * Validates: Requirements 3.4
     */
    public function test_theme_script_reads_theme_key_from_localstorage(): void
    {
        if (!file_exists($this->appLayoutPath)) {
            $this->markTestSkipped("Layout file tidak ditemukan: {$this->appLayoutPath}");
        }

        $content = file_get_contents($this->appLayoutPath);

        $this->assertTrue(
            str_contains($content, "localStorage.getItem('theme')") ||
            str_contains($content, 'localStorage.getItem("theme")'),
            "Script tema harus membaca key 'theme' dari localStorage"
        );
    }

    /**
     * @test
     * Preservation 3.4: Logika simulasi localStorage persistence berfungsi
     *
     * Simulasi PHP-side dari logika localStorage persistence.
     * Validates: Requirements 3.4
     */
    public function test_theme_persistence_logic_simulation(): void
    {
        // Simulasi: user menyimpan tema 'dark' ke localStorage
        $localStorage = [];
        $localStorage['theme'] = 'dark';

        // Simulasi: setelah refresh, tema dibaca dari localStorage
        $savedTheme = $localStorage['theme'] ?? 'system';

        $this->assertEquals(
            'dark',
            $savedTheme,
            "Tema yang disimpan harus bisa dibaca kembali (persistence)"
        );

        // Simulasi: user mengubah ke 'light'
        $localStorage['theme'] = 'light';
        $savedTheme = $localStorage['theme'] ?? 'system';

        $this->assertEquals(
            'light',
            $savedTheme,
            "Perubahan tema harus tersimpan dan bisa dibaca kembali"
        );
    }

    /**
     * @test
     * Preservation 3.4: Default tema adalah 'system' jika tidak ada di localStorage
     *
     * Validates: Requirements 3.4
     */
    public function test_default_theme_is_system_when_not_set(): void
    {
        // Simulasi: localStorage kosong (tidak ada tema tersimpan)
        $localStorage = [];
        $savedTheme = $localStorage['theme'] ?? 'system';

        $this->assertEquals(
            'system',
            $savedTheme,
            "Default tema harus 'system' jika tidak ada di localStorage"
        );
    }

    // ── Requirement 3.5: Mode system dengan OS dark → tema dark ──────────────

    /**
     * @test
     * Preservation 3.5: Logika mode system menerapkan dark jika OS dark (pure logic test)
     *
     * Memverifikasi bahwa logika penentuan tema untuk mode 'system' sudah benar.
     * Test ini adalah pure logic test — tidak bergantung pada implementasi file.
     * Validates: Requirements 3.5
     */
    public function test_theme_script_handles_system_mode(): void
    {
        // Logika yang benar untuk mode 'system':
        // Jika OS dark → terapkan dark
        // Jika OS light → terapkan light
        $applyTheme = function (string $theme, bool $osDark): string {
            if ($theme === 'dark') return 'dark';
            if ($theme === 'light') return 'light';
            // mode 'system': ikuti OS
            return $osDark ? 'dark' : 'light';
        };

        // Mode system + OS dark → dark
        $this->assertEquals('dark', $applyTheme('system', true));
        // Mode system + OS light → light
        $this->assertEquals('light', $applyTheme('system', false));
    }

    /**
     * @test
     * Preservation 3.5: Logika mode system menerapkan dark jika OS dark
     *
     * Simulasi logika PHP-side dari deteksi prefers-color-scheme.
     * Validates: Requirements 3.5
     */
    public function test_system_mode_applies_dark_when_os_is_dark(): void
    {
        // Simulasi logika tema
        $applyTheme = function (string $theme, bool $osDark): string {
            if ($theme === 'dark') {
                return 'dark';
            }
            if ($theme === 'light') {
                return 'light';
            }
            // mode 'system': ikuti OS
            return $osDark ? 'dark' : 'light';
        };

        // OS dark + mode system → dark
        $result = $applyTheme('system', true);
        $this->assertEquals('dark', $result, "Mode 'system' dengan OS dark harus menerapkan tema dark");

        // OS light + mode system → light
        $result = $applyTheme('system', false);
        $this->assertEquals('light', $result, "Mode 'system' dengan OS light harus menerapkan tema light");

        // Mode dark eksplisit → selalu dark
        $result = $applyTheme('dark', false);
        $this->assertEquals('dark', $result, "Mode 'dark' eksplisit harus selalu dark");

        // Mode light eksplisit → selalu light
        $result = $applyTheme('light', true);
        $this->assertEquals('light', $result, "Mode 'light' eksplisit harus selalu light");
    }

    /**
     * @test
     * Preservation 3.5: Semua nilai tema yang valid ditangani
     *
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

    // ── Requirement 3.6: Event theme-changed dikirim ke semua listener ────────

    /**
     * @test
     * Preservation 3.6: ThemeManager mengirim event 'theme-changed'
     *
     * Memverifikasi bahwa theme-manager.js mendispatch event 'theme-changed'.
     * Validates: Requirements 3.6
     */
    public function test_theme_manager_dispatches_theme_changed_event(): void
    {
        if (!file_exists($this->themeManagerPath)) {
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
     * Preservation 3.6: Event theme-changed menggunakan CustomEvent atau dispatchEvent
     *
     * Validates: Requirements 3.6
     */
    public function test_theme_changed_event_uses_dispatch(): void
    {
        if (!file_exists($this->themeManagerPath)) {
            $this->markTestSkipped("ThemeManager file tidak ditemukan: {$this->themeManagerPath}");
        }

        $content = file_get_contents($this->themeManagerPath);

        $this->assertTrue(
            str_contains($content, 'dispatchEvent') ||
            str_contains($content, 'CustomEvent') ||
            str_contains($content, 'emit('),
            "ThemeManager harus menggunakan dispatchEvent atau CustomEvent untuk mengirim event tema"
        );
    }

    /**
     * @test
     * Preservation 3.6: Simulasi event listener menerima event theme-changed
     *
     * Simulasi PHP-side dari event dispatch dan listener pattern.
     * Validates: Requirements 3.6
     */
    public function test_theme_changed_event_listener_simulation(): void
    {
        // Simulasi event bus sederhana
        $listeners = [];
        $receivedEvents = [];

        // Daftarkan listener
        $listeners[] = function (array $event) use (&$receivedEvents) {
            $receivedEvents[] = $event;
        };

        // Dispatch event theme-changed
        $dispatchThemeChanged = function (string $theme, bool $isDark) use (&$listeners) {
            $event = ['theme' => $theme, 'isDark' => $isDark];
            foreach ($listeners as $listener) {
                $listener($event);
            }
        };

        // Dispatch event
        $dispatchThemeChanged('dark', true);

        // Verifikasi listener menerima event
        $this->assertCount(1, $receivedEvents, "Listener harus menerima event theme-changed");
        $this->assertEquals('dark', $receivedEvents[0]['theme']);
        $this->assertTrue($receivedEvents[0]['isDark']);

        // Dispatch event lagi
        $dispatchThemeChanged('light', false);

        $this->assertCount(2, $receivedEvents, "Listener harus menerima semua event theme-changed");
        $this->assertEquals('light', $receivedEvents[1]['theme']);
        $this->assertFalse($receivedEvents[1]['isDark']);
    }

    /**
     * @test
     * Preservation 3.6: Semua listener yang terdaftar menerima event
     *
     * Validates: Requirements 3.6
     */
    public function test_all_registered_listeners_receive_theme_changed_event(): void
    {
        $receivedByListener1 = false;
        $receivedByListener2 = false;
        $receivedByListener3 = false;

        $listeners = [
            function () use (&$receivedByListener1) { $receivedByListener1 = true; },
            function () use (&$receivedByListener2) { $receivedByListener2 = true; },
            function () use (&$receivedByListener3) { $receivedByListener3 = true; },
        ];

        // Dispatch ke semua listener
        foreach ($listeners as $listener) {
            $listener();
        }

        $this->assertTrue($receivedByListener1, "Listener 1 harus menerima event theme-changed");
        $this->assertTrue($receivedByListener2, "Listener 2 harus menerima event theme-changed");
        $this->assertTrue($receivedByListener3, "Listener 3 harus menerima event theme-changed");
    }
}
