<?php

namespace Tests\Property\AI;

use App\Services\AI\ProviderSwitcher;
use Carbon\Carbon;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Property-Based Tests untuk Cooldown Expiry.
 *
 * Feature: multi-ai-provider
 *
 * **Validates: Requirements 3.5, 3.6**
 *
 * Property 4: Cooldown habis mengembalikan provider ke ketersediaan.
 *
 * Untuk SEMBARANG durasi cooldown d (1–3600 detik):
 *   - Segera setelah di-mark unavailable → isProviderAvailable() mengembalikan false
 *   - Setelah (d - 1) detik berlalu → isProviderAvailable() masih mengembalikan false
 *   - Setelah d detik berlalu (tepat di batas) → isProviderAvailable() mengembalikan true
 *
 * Teknik: Carbon time travel (Carbon::setTestNow()) untuk mensimulasikan
 * perjalanan waktu tanpa harus menunggu detik nyata.
 *
 * Catatan: ArrayStore tidak menghormati TTL secara otomatis, tetapi
 * ProviderSwitcher::getUnavailableEntry() melakukan double-check terhadap
 * field `expires_at` menggunakan Carbon::now() — sehingga Carbon time travel
 * bekerja dengan benar untuk test ini.
 */
class CooldownExpiryTest extends TestCase
{
    use TestTrait;

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Buat instance ProviderSwitcher dengan ArrayStore (in-memory cache)
     * yang terisolasi — tidak ada state yang bocor antar iterasi.
     */
    private function makeSwitcher(): ProviderSwitcher
    {
        $cache = new Repository(new ArrayStore());

        return new ProviderSwitcher($cache);
    }

    /**
     * Reset Carbon time travel setelah setiap test untuk menghindari
     * state yang bocor ke test lain.
     */
    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    // ─── Property Tests ───────────────────────────────────────────

    /**
     * Property 4: Cooldown habis mengembalikan provider ke ketersediaan.
     *
     * Untuk SEMBARANG durasi cooldown d (1–3600 detik):
     *   1. Segera setelah di-mark unavailable → provider tidak tersedia
     *   2. Setelah (d - 1) detik → provider masih tidak tersedia
     *   3. Setelah d detik (tepat di batas expiry) → provider tersedia kembali
     *
     * **Validates: Requirements 3.5, 3.6**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_provider_becomes_available_after_cooldown_expires(): void
    {
        $this
            ->forAll(
                Generators::choose(1, 3600)
            )
            ->then(function (int $cooldownSeconds) {
                // Buat switcher baru per iterasi agar state cache terisolasi
                $switcher = $this->makeSwitcher();

                // Set waktu awal yang tetap (time travel ke titik awal)
                $startTime = Carbon::now();
                Carbon::setTestNow($startTime);

                // Konfigurasi cooldown sesuai durasi yang di-generate
                Config::set('ai.providers.gemini.rate_limit_cooldown', $cooldownSeconds);

                // Mark provider sebagai unavailable
                $switcher->markProviderUnavailable('gemini', 'rate_limit');

                // ── Langkah 1: Segera setelah di-mark → harus tidak tersedia ──
                $this->assertFalse(
                    $switcher->isProviderAvailable('gemini'),
                    sprintf(
                        "Provider 'gemini' harus tidak tersedia segera setelah di-mark unavailable " .
                            "dengan cooldown %d detik. Property 4 dilanggar.",
                        $cooldownSeconds
                    )
                );

                // ── Langkah 2: Setelah (d - 1) detik → masih tidak tersedia ──
                // (hanya berlaku jika cooldown > 1 detik)
                if ($cooldownSeconds > 1) {
                    Carbon::setTestNow($startTime->copy()->addSeconds($cooldownSeconds - 1));

                    $this->assertFalse(
                        $switcher->isProviderAvailable('gemini'),
                        sprintf(
                            "Provider 'gemini' harus masih tidak tersedia setelah %d detik " .
                                "(cooldown %d detik belum habis). Property 4 dilanggar.",
                            $cooldownSeconds - 1,
                            $cooldownSeconds
                        )
                    );
                }

                // ── Langkah 3: Setelah d detik (tepat di batas) → harus tersedia ──
                // Maju 1 detik melewati batas expiry untuk memastikan isAfter() bernilai true
                Carbon::setTestNow($startTime->copy()->addSeconds($cooldownSeconds + 1));

                $this->assertTrue(
                    $switcher->isProviderAvailable('gemini'),
                    sprintf(
                        "Provider 'gemini' harus tersedia kembali setelah cooldown %d detik habis " .
                            "(waktu saat ini: %d detik setelah di-mark). Property 4 dilanggar.",
                        $cooldownSeconds,
                        $cooldownSeconds + 1
                    )
                );

                // Reset Carbon time travel untuk iterasi berikutnya
                Carbon::setTestNow(null);
            });
    }

    /**
     * Property 4 (variant): Cooldown quota_exceeded (3600 detik default) juga
     * mengembalikan provider ke ketersediaan setelah habis.
     *
     * Memverifikasi bahwa mekanisme expiry bekerja untuk semua jenis reason,
     * bukan hanya rate_limit.
     *
     * **Validates: Requirements 3.5, 3.6**
     */
    #[ErisRepeat(repeat: 50)]
    public function test_quota_exceeded_cooldown_also_expires(): void
    {
        $this
            ->forAll(
                Generators::choose(1, 3600)
            )
            ->then(function (int $cooldownSeconds) {
                $switcher  = $this->makeSwitcher();
                $startTime = Carbon::now();
                Carbon::setTestNow($startTime);

                // Konfigurasi cooldown quota_exceeded
                Config::set('ai.providers.gemini.quota_cooldown', $cooldownSeconds);

                // Mark provider sebagai unavailable dengan reason quota_exceeded
                $switcher->markProviderUnavailable('gemini', 'quota_exceeded');

                // Segera setelah di-mark → tidak tersedia
                $this->assertFalse(
                    $switcher->isProviderAvailable('gemini'),
                    sprintf(
                        "Provider 'gemini' harus tidak tersedia segera setelah di-mark unavailable " .
                            "dengan quota_exceeded cooldown %d detik. Property 4 dilanggar.",
                        $cooldownSeconds
                    )
                );

                // Setelah cooldown habis → tersedia kembali
                Carbon::setTestNow($startTime->copy()->addSeconds($cooldownSeconds + 1));

                $this->assertTrue(
                    $switcher->isProviderAvailable('gemini'),
                    sprintf(
                        "Provider 'gemini' harus tersedia kembali setelah quota_exceeded cooldown " .
                            "%d detik habis. Property 4 dilanggar.",
                        $cooldownSeconds
                    )
                );

                Carbon::setTestNow(null);
            });
    }

    // ─── Edge Case Tests ──────────────────────────────────────────

    /**
     * Edge case: Segera setelah di-mark unavailable → provider tidak tersedia.
     *
     * **Validates: Requirements 3.5**
     */
    public function test_provider_immediately_unavailable_after_marking(): void
    {
        $switcher = $this->makeSwitcher();

        Carbon::setTestNow(Carbon::now());
        Config::set('ai.providers.gemini.rate_limit_cooldown', 60);

        $switcher->markProviderUnavailable('gemini', 'rate_limit');

        $this->assertFalse(
            $switcher->isProviderAvailable('gemini'),
            "Provider harus tidak tersedia segera setelah di-mark unavailable."
        );
    }

    /**
     * Edge case: Tepat setelah batas expiry → provider tersedia kembali.
     *
     * Memverifikasi bahwa 1 detik setelah cooldown habis, provider sudah tersedia.
     *
     * **Validates: Requirements 3.5, 3.6**
     */
    public function test_provider_available_just_after_expiry(): void
    {
        $switcher        = $this->makeSwitcher();
        $cooldownSeconds = 60;
        $startTime       = Carbon::now();

        Carbon::setTestNow($startTime);
        Config::set('ai.providers.gemini.rate_limit_cooldown', $cooldownSeconds);

        $switcher->markProviderUnavailable('gemini', 'rate_limit');

        // Maju tepat 1 detik setelah expiry
        Carbon::setTestNow($startTime->copy()->addSeconds($cooldownSeconds + 1));

        $this->assertTrue(
            $switcher->isProviderAvailable('gemini'),
            "Provider harus tersedia kembali 1 detik setelah cooldown 60 detik habis."
        );
    }

    /**
     * Edge case: Cooldown minimum (1 detik) — provider tersedia setelah 2 detik.
     *
     * **Validates: Requirements 3.5, 3.6**
     */
    public function test_minimum_cooldown_one_second(): void
    {
        $switcher  = $this->makeSwitcher();
        $startTime = Carbon::now();

        Carbon::setTestNow($startTime);
        Config::set('ai.providers.gemini.rate_limit_cooldown', 1);

        $switcher->markProviderUnavailable('gemini', 'rate_limit');

        // Segera → tidak tersedia
        $this->assertFalse(
            $switcher->isProviderAvailable('gemini'),
            "Provider harus tidak tersedia segera setelah di-mark dengan cooldown 1 detik."
        );

        // Setelah 2 detik → tersedia
        Carbon::setTestNow($startTime->copy()->addSeconds(2));

        $this->assertTrue(
            $switcher->isProviderAvailable('gemini'),
            "Provider harus tersedia kembali setelah cooldown 1 detik habis (2 detik kemudian)."
        );
    }

    /**
     * Edge case: Cooldown maksimum (3600 detik / 1 jam) — provider tersedia setelah 3601 detik.
     *
     * **Validates: Requirements 3.5, 3.6**
     */
    public function test_maximum_cooldown_one_hour(): void
    {
        $switcher  = $this->makeSwitcher();
        $startTime = Carbon::now();

        Carbon::setTestNow($startTime);
        Config::set('ai.providers.gemini.rate_limit_cooldown', 3600);

        $switcher->markProviderUnavailable('gemini', 'rate_limit');

        // Setelah 3599 detik → masih tidak tersedia
        Carbon::setTestNow($startTime->copy()->addSeconds(3599));

        $this->assertFalse(
            $switcher->isProviderAvailable('gemini'),
            "Provider harus masih tidak tersedia setelah 3599 detik (cooldown 3600 detik)."
        );

        // Setelah 3601 detik → tersedia
        Carbon::setTestNow($startTime->copy()->addSeconds(3601));

        $this->assertTrue(
            $switcher->isProviderAvailable('gemini'),
            "Provider harus tersedia kembali setelah cooldown 3600 detik habis (3601 detik kemudian)."
        );
    }

    /**
     * Edge case: Provider yang berbeda memiliki cooldown independen.
     *
     * Memverifikasi bahwa cooldown satu provider tidak mempengaruhi provider lain.
     *
     * **Validates: Requirements 3.5**
     */
    public function test_different_providers_have_independent_cooldowns(): void
    {
        $switcher  = $this->makeSwitcher();
        $startTime = Carbon::now();

        Carbon::setTestNow($startTime);
        Config::set('ai.providers.gemini.rate_limit_cooldown', 60);
        Config::set('ai.providers.anthropic.rate_limit_cooldown', 120);

        // Mark gemini unavailable dengan cooldown 60 detik
        $switcher->markProviderUnavailable('gemini', 'rate_limit');

        // Mark anthropic unavailable dengan cooldown 120 detik
        $switcher->markProviderUnavailable('anthropic', 'rate_limit');

        // Setelah 61 detik: gemini tersedia, anthropic masih tidak tersedia
        Carbon::setTestNow($startTime->copy()->addSeconds(61));

        $this->assertTrue(
            $switcher->isProviderAvailable('gemini'),
            "Provider 'gemini' harus tersedia setelah cooldown 60 detik habis (61 detik kemudian)."
        );

        $this->assertFalse(
            $switcher->isProviderAvailable('anthropic'),
            "Provider 'anthropic' harus masih tidak tersedia setelah 61 detik (cooldown 120 detik)."
        );

        // Setelah 121 detik: kedua provider tersedia
        Carbon::setTestNow($startTime->copy()->addSeconds(121));

        $this->assertTrue(
            $switcher->isProviderAvailable('anthropic'),
            "Provider 'anthropic' harus tersedia setelah cooldown 120 detik habis (121 detik kemudian)."
        );
    }
}
