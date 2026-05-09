<?php

namespace Tests\Property\AI;

use App\Services\AI\Providers\AnthropicProvider;
use Eris\Attributes\ErisRepeat;
use Eris\Generator;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Support\Facades\Config;
use ReflectionClass;
use Tests\TestCase;

/**
 * Property-Based Tests for Chat History Conversion (Partial Bijection).
 *
 * Feature: multi-ai-provider
 *
 * **Validates: Requirements 10.5**
 *
 * Property 2: Konversi history chat adalah bijeksi parsial.
 *
 * Untuk SEMBARANG array history dalam format Gemini (role 'user'/'model', key 'text'),
 * konversi ke format Anthropic harus:
 *   1. Mempertahankan urutan pesan (order preserved)
 *   2. Mempertahankan konten teks (text preserved)
 *   3. Memetakan role dengan benar: 'user' → 'user', 'model' → 'assistant'
 *   4. Memfilter entri dengan teks kosong (empty text filtered out)
 */
class HistoryConversionTest extends TestCase
{
    use TestTrait;

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Akses protected method convertHistory() via reflection.
     */
    private function callConvertHistory(AnthropicProvider $provider, array $history): array
    {
        $reflection = new ReflectionClass($provider);
        $method = $reflection->getMethod('convertHistory');
        $method->setAccessible(true);

        return $method->invoke($provider, $history);
    }

    /**
     * Buat instance AnthropicProvider tanpa memerlukan API key nyata.
     */
    private function makeProvider(): AnthropicProvider
    {
        Config::set('ai.providers.anthropic.api_key', 'fake-key');
        Config::set('ai.providers.anthropic.model', 'claude-3-5-sonnet-20241022');
        Config::set('ai.providers.anthropic.fallback_models', [
            'claude-3-5-sonnet-20241022',
            'claude-3-haiku-20240307',
        ]);
        Config::set('ai.providers.anthropic.max_tokens', 8192);
        Config::set('ai.providers.anthropic.timeout', 60);

        return new AnthropicProvider;
    }

    /**
     * Generator untuk satu entri history Gemini dengan teks non-kosong.
     *
     * Menghasilkan array ['role' => 'user'|'model', 'text' => <non-empty string>].
     *
     * Menggunakan Generators::associative() untuk membangun array asosiatif
     * dengan generator terpisah untuk setiap key.
     * Kemudian Generators::suchThat() memastikan teks tidak kosong setelah trim.
     */
    private function nonEmptyHistoryEntryGenerator(): Generator
    {
        return Generators::suchThat(
            function (array $entry) {
                return trim($entry['text']) !== '';
            },
            Generators::associative([
                'role' => Generators::elements(['user', 'model']),
                'text' => Generators::string(),
            ])
        );
    }

    /**
     * Generator untuk satu entri history Gemini yang mungkin kosong atau tidak.
     *
     * Menghasilkan array ['role' => 'user'|'model', 'text' => <string>].
     */
    private function anyHistoryEntryGenerator(): Generator
    {
        return Generators::associative([
            'role' => Generators::elements(['user', 'model']),
            'text' => Generators::string(),
        ]);
    }

    // ─── Property Tests ───────────────────────────────────────────

    /**
     * Property 2a: Urutan pesan dipertahankan setelah konversi.
     *
     * Untuk SEMBARANG array history dengan entri non-kosong, urutan pesan
     * dalam output Anthropic harus sama dengan urutan dalam input Gemini.
     *
     * **Validates: Requirements 10.5**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_history_conversion_preserves_order(): void
    {
        $provider = $this->makeProvider();

        $this
            ->forAll(
                // Sequence of non-empty history entries (variable length)
                Generators::seq($this->nonEmptyHistoryEntryGenerator())
            )
            ->then(function (array $history) use ($provider) {
                $result = $this->callConvertHistory($provider, $history);

                // Jumlah output harus sama dengan jumlah input (semua non-kosong)
                $this->assertCount(
                    count($history),
                    $result,
                    'Jumlah pesan output harus sama dengan jumlah pesan input non-kosong'
                );

                // Urutan teks harus sama
                foreach ($history as $i => $entry) {
                    $this->assertSame(
                        trim($entry['text']),
                        $result[$i]['content'],
                        "Teks pesan ke-{$i} harus sama antara input dan output"
                    );
                }
            });
    }

    /**
     * Property 2b: Konten teks dipertahankan setelah konversi.
     *
     * Untuk SEMBARANG array history, teks dari setiap entri non-kosong
     * harus muncul di output dengan nilai yang sama (setelah trim).
     *
     * **Validates: Requirements 10.5**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_history_conversion_preserves_text_content(): void
    {
        $provider = $this->makeProvider();

        $this
            ->forAll(
                Generators::seq($this->nonEmptyHistoryEntryGenerator())
            )
            ->then(function (array $history) use ($provider) {
                $result = $this->callConvertHistory($provider, $history);

                foreach ($history as $i => $entry) {
                    $this->assertArrayHasKey(
                        'content',
                        $result[$i],
                        "Setiap entri output harus memiliki key 'content'"
                    );

                    $this->assertSame(
                        trim($entry['text']),
                        $result[$i]['content'],
                        'Konten teks harus dipertahankan setelah konversi'
                    );
                }
            });
    }

    /**
     * Property 2c: Role dipetakan dengan benar.
     *
     * Untuk SEMBARANG array history:
     *   - role 'user'  → role 'user'
     *   - role 'model' → role 'assistant'
     *
     * **Validates: Requirements 10.5**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_history_conversion_maps_roles_correctly(): void
    {
        $provider = $this->makeProvider();

        $this
            ->forAll(
                Generators::seq($this->nonEmptyHistoryEntryGenerator())
            )
            ->then(function (array $history) use ($provider) {
                $result = $this->callConvertHistory($provider, $history);

                foreach ($history as $i => $entry) {
                    $expectedRole = $entry['role'] === 'model' ? 'assistant' : 'user';

                    $this->assertArrayHasKey(
                        'role',
                        $result[$i],
                        "Setiap entri output harus memiliki key 'role'"
                    );

                    $this->assertSame(
                        $expectedRole,
                        $result[$i]['role'],
                        "Role '{$entry['role']}' harus dipetakan ke '{$expectedRole}', bukan '{$result[$i]['role']}'"
                    );
                }
            });
    }

    /**
     * Property 2d: Entri dengan teks kosong difilter.
     *
     * Untuk SEMBARANG array history yang mungkin mengandung entri dengan
     * teks kosong ('' atau hanya whitespace), entri tersebut harus
     * dihilangkan dari output.
     *
     * **Validates: Requirements 10.5**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_history_conversion_filters_empty_text_entries(): void
    {
        $provider = $this->makeProvider();

        $this
            ->forAll(
                // Mix of entries that may have empty text
                Generators::seq($this->anyHistoryEntryGenerator())
            )
            ->then(function (array $history) use ($provider) {
                $result = $this->callConvertHistory($provider, $history);

                // Hitung entri non-kosong dalam input
                $nonEmptyInputCount = count(array_filter(
                    $history,
                    fn ($entry) => trim($entry['text'] ?? '') !== ''
                ));

                // Output harus memiliki jumlah yang sama dengan entri non-kosong
                $this->assertCount(
                    $nonEmptyInputCount,
                    $result,
                    'Output harus hanya berisi entri dengan teks non-kosong. '.
                        "Input memiliki {$nonEmptyInputCount} entri non-kosong, ".
                        'output memiliki '.count($result).' entri'
                );

                // Semua entri dalam output harus memiliki content non-kosong (bukan string kosong)
                foreach ($result as $i => $entry) {
                    $this->assertNotSame(
                        '',
                        $entry['content'],
                        "Entri output ke-{$i} tidak boleh memiliki content string kosong"
                    );
                }
            });
    }

    /**
     * Property 2e: Konversi history kosong menghasilkan array kosong.
     *
     * Untuk input history kosong ([]), output harus berupa array kosong.
     *
     * **Validates: Requirements 10.5**
     */
    public function test_empty_history_converts_to_empty_array(): void
    {
        $provider = $this->makeProvider();
        $result = $this->callConvertHistory($provider, []);

        $this->assertIsArray($result, 'Output harus berupa array');
        $this->assertEmpty($result, 'Konversi history kosong harus menghasilkan array kosong');
    }

    /**
     * Property 2f: Bijeksi parsial — setiap entri non-kosong dalam input
     * memiliki tepat satu padanan dalam output (injektif).
     *
     * Untuk SEMBARANG array history dengan entri non-kosong, setiap entri
     * input harus memiliki tepat satu padanan dalam output dengan teks dan
     * role yang sesuai.
     *
     * **Validates: Requirements 10.5**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_history_conversion_is_injective_for_nonempty_entries(): void
    {
        $provider = $this->makeProvider();

        $this
            ->forAll(
                Generators::seq($this->nonEmptyHistoryEntryGenerator())
            )
            ->then(function (array $history) use ($provider) {
                $result = $this->callConvertHistory($provider, $history);

                // Jumlah output harus tepat sama dengan jumlah input
                $this->assertCount(
                    count($history),
                    $result,
                    'Setiap entri non-kosong dalam input harus memiliki tepat satu padanan dalam output'
                );

                // Setiap pasangan (input[i], output[i]) harus konsisten
                foreach ($history as $i => $inputEntry) {
                    $outputEntry = $result[$i];

                    // Teks harus sama
                    $this->assertSame(
                        trim($inputEntry['text']),
                        $outputEntry['content'],
                        "Entri ke-{$i}: teks harus dipertahankan"
                    );

                    // Role harus dipetakan dengan benar
                    $expectedRole = $inputEntry['role'] === 'model' ? 'assistant' : 'user';
                    $this->assertSame(
                        $expectedRole,
                        $outputEntry['role'],
                        "Entri ke-{$i}: role harus dipetakan dengan benar"
                    );
                }
            });
    }

    /**
     * Property 2g: Output hanya mengandung key 'role' dan 'content'.
     *
     * Setiap entri dalam output harus memiliki key 'role' dan 'content',
     * dan role harus salah satu dari 'user' atau 'assistant'.
     *
     * **Validates: Requirements 10.5**
     */
    #[ErisRepeat(repeat: 100)]
    public function test_history_conversion_output_has_correct_keys(): void
    {
        $provider = $this->makeProvider();

        $this
            ->forAll(
                Generators::seq($this->nonEmptyHistoryEntryGenerator())
            )
            ->then(function (array $history) use ($provider) {
                $result = $this->callConvertHistory($provider, $history);

                foreach ($result as $i => $entry) {
                    $this->assertArrayHasKey(
                        'role',
                        $entry,
                        "Entri output ke-{$i} harus memiliki key 'role'"
                    );

                    $this->assertArrayHasKey(
                        'content',
                        $entry,
                        "Entri output ke-{$i} harus memiliki key 'content'"
                    );

                    // Role harus salah satu dari 'user' atau 'assistant'
                    $this->assertContains(
                        $entry['role'],
                        ['user', 'assistant'],
                        "Role output ke-{$i} harus 'user' atau 'assistant', bukan '{$entry['role']}'"
                    );

                    // Content harus berupa string
                    $this->assertIsString(
                        $entry['content'],
                        "Content output ke-{$i} harus berupa string"
                    );
                }
            });
    }
}
