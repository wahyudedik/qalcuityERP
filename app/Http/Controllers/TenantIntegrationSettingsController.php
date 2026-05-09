<?php

namespace App\Http\Controllers;

use App\Events\SettingsUpdated;
use App\Exceptions\RateLimitException;
use App\Models\SystemSetting;
use App\Models\TenantApiSetting;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\SettingsCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TenantIntegrationSettingsController extends Controller
{
    protected SettingsCacheService $cacheService;

    public function __construct(SettingsCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Settings map: key => [group, label, encrypted, description]
     */
    private const SETTINGS_MAP = [
        // Communication
        'fonnte_token' => ['communication', 'Fonnte API Token', true, 'Token API Fonnte untuk kirim pesan WhatsApp Bot'],
        'telegram_bot_token' => ['communication', 'Telegram Bot Token', true, 'Token dari @BotFather untuk bot Telegram Anda'],
        'telegram_chat_id' => ['communication', 'Telegram Chat ID', false, 'Chat ID grup/channel untuk notifikasi Telegram'],

        // Agriculture / Weather
        'weather_api_key' => ['weather', 'OpenWeatherMap API Key', true, 'API Key dari openweathermap.org untuk data cuaca pertanian'],

        // CCTV / Security
        'cctv_nvr_url' => ['cctv', 'NVR / DVR URL', false, 'URL akses NVR/DVR CCTV Anda, contoh: http://192.168.1.100:8000'],
        'cctv_api_key' => ['cctv', 'CCTV API Key', true, 'API Key autentikasi ke sistem NVR/DVR'],

        // Face Recognition
        'face_recognition_url' => ['face', 'Face Recognition Service URL', false, 'URL service face recognition, contoh: http://localhost:5000'],
        'face_recognition_api_key' => ['face', 'Face Recognition API Key', true, 'API Key autentikasi ke service face recognition'],
    ];

    private const GROUP_META = [
        'communication' => [
            'label' => 'Komunikasi (WA Bot & Telegram)',
            'icon' => '<path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
            'color' => 'green',
        ],
        'weather' => [
            'label' => 'Cuaca & Pertanian',
            'icon' => '<path d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>',
            'color' => 'blue',
        ],
        'cctv' => [
            'label' => 'CCTV & Keamanan',
            'icon' => '<path d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>',
            'color' => 'orange',
        ],
        'face' => [
            'label' => 'Face Recognition & Absensi',
            'icon' => '<path d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'color' => 'purple',
        ],
    ];

    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(! $tenantId, 403);

        // Load current saved settings, decrypted for display (masked)
        $saved = [];
        foreach (self::SETTINGS_MAP as $key => [$group, $label, $encrypted]) {
            $value = TenantApiSetting::get($tenantId, $key);
            $saved[$key] = [
                'value' => $value,
                'has_value' => ! empty($value),
                'masked' => $encrypted && ! empty($value) ? $this->mask($value) : $value,
                'encrypted' => $encrypted,
                'label' => $label,
                'group' => $group,
                'description' => self::SETTINGS_MAP[$key][3] ?? '',
            ];
        }

        // Group for view
        $groups = [];
        foreach ($saved as $key => $data) {
            $groups[$data['group']][$key] = $data;
        }

        $groupMeta = self::GROUP_META;

        // AI Provider settings
        $aiProviderOverride = TenantApiSetting::get($tenantId, 'ai_provider');
        $hasAiOverride = ! empty($aiProviderOverride);
        $globalDefault = SystemSetting::get('ai_default_provider') ?? config('ai.default_provider', 'gemini');
        $activeProvider = $hasAiOverride ? $aiProviderOverride : $globalDefault;
        $hasAnthropicKey = TenantApiSetting::has($tenantId, 'anthropic_api_key');
        $hasGeminiKey = TenantApiSetting::has($tenantId, 'gemini_api_key');

        $aiProviderData = [
            'has_override' => $hasAiOverride,
            'selected_provider' => $aiProviderOverride ?? $globalDefault,
            'active_provider' => $activeProvider,
            'global_default' => $globalDefault,
            'has_anthropic_key' => $hasAnthropicKey,
            'has_gemini_key' => $hasGeminiKey,
        ];

        return view('settings.integrations', compact('saved', 'groups', 'groupMeta', 'tenantId', 'aiProviderData'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(! $tenantId, 403);

        foreach (self::SETTINGS_MAP as $key => [$group, $label, $encrypted]) {
            $value = $request->input($key);

            // If encrypted and empty → keep existing value (don't overwrite with blank)
            if ($encrypted && empty($value)) {
                continue;
            }

            // If non-encrypted and null → store as empty
            TenantApiSetting::set(
                tenantId: $tenantId,
                key: $key,
                value: $value ?? '',
                encrypt: $encrypted,
                group: $group,
                label: $label,
            );
        }

        // BUG-SET-001 FIX: Dispatch event to clear API settings cache
        event(new SettingsUpdated(
            type: 'api',
            tenantId: $tenantId,
            metadata: [
                'settings_updated' => array_keys(self::SETTINGS_MAP),
            ]
        ));

        // Also clear specific cache
        $this->cacheService->clearTenantCache($tenantId);

        return back()->with('success', 'Pengaturan integrasi berhasil disimpan.');
    }

    public function testFonnte(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(! $tenantId, 403);

        $token = TenantApiSetting::get($tenantId, 'fonnte_token')
            ?? config('services.fonnte.token');

        if (! $token) {
            return response()->json(['success' => false, 'message' => 'Fonnte token belum dikonfigurasi.']);
        }

        $validated = $request->validate(['phone' => 'required|string|max:20']);
        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        try {
            $response = Http::withHeaders(['Authorization' => $token])
                ->post('https://api.fonnte.com/send', [
                    'target' => $phone,
                    'message' => 'Ini adalah pesan uji coba dari Qalcuity ERP. Fonnte berhasil dikonfigurasi! ✅',
                ]);

            $result = $response->json();

            return response()->json([
                'success' => $response->successful() && ($result['status'] ?? false),
                'message' => $result['status'] ?? false
                    ? "Pesan uji coba berhasil dikirim ke {$phone}."
                    : ('Gagal: '.($result['reason'] ?? 'Unknown error')),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Koneksi gagal: '.$e->getMessage()]);
        }
    }

    /**
     * Test AI provider connection for the tenant (AJAX).
     * POST /settings/ai-provider/test-connection
     * Uses tenant's configured API key, falls back to global.
     * Requirements: 4.6, 4.7, 5.7
     */
    public function testAiProviderConnection(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(! $tenantId, 403);

        // Determine which provider to test:
        // Use tenant's override if set, otherwise use global default
        $tenantProvider = TenantApiSetting::get($tenantId, 'ai_provider');
        $globalDefault = SystemSetting::get('ai_default_provider') ?? config('ai.default_provider', 'gemini');
        $provider = $tenantProvider ?? $globalDefault;

        try {
            if ($provider === 'gemini') {
                return $this->testTenantGeminiConnection($tenantId);
            }

            if ($provider === 'anthropic') {
                return $this->testTenantAnthropicConnection($tenantId);
            }

            return response()->json([
                'success' => false,
                'message' => "Provider '{$provider}' tidak dikenali.",
                'details' => null,
            ], 400);
        } catch (\Throwable $e) {
            Log::error("TenantIntegrationSettingsController: testAiProviderConnection failed for tenant [{$tenantId}], provider [{$provider}]", [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal test koneksi: '.$e->getMessage(),
                'details' => null,
            ], 500);
        }
    }

    /**
     * Test Gemini connection using tenant API key (falls back to global).
     */
    private function testTenantGeminiConnection(int $tenantId): JsonResponse
    {
        // Apply tenant API key override into config if available
        $tenantApiKey = TenantApiSetting::get($tenantId, 'gemini_api_key');
        if (! empty($tenantApiKey)) {
            config(['gemini.api_key' => $tenantApiKey]);
            config(['ai.providers.gemini.api_key' => $tenantApiKey]);
        } else {
            // Fall back to global SystemSetting
            SystemSetting::loadIntoConfig(['gemini_api_key' => 'gemini.api_key']);
            SystemSetting::loadIntoConfig(['gemini_api_key' => 'ai.providers.gemini.api_key']);
        }

        $effectiveKey = config('gemini.api_key') ?? config('ai.providers.gemini.api_key');

        if (empty($effectiveKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API key Gemini belum dikonfigurasi. Silakan isi API key di pengaturan atau hubungi administrator.',
                'details' => null,
            ], 400);
        }

        try {
            $provider = new GeminiProvider;
            $result = $provider->generate('Test connection - respond with: OK');

            return response()->json([
                'success' => true,
                'message' => 'Koneksi Gemini berhasil! Provider aktif dan siap digunakan.',
                'details' => [
                    'model' => $result['model'] ?? config('gemini.model', 'gemini-2.5-flash'),
                    'response' => substr($result['text'] ?? '', 0, 100),
                ],
            ]);
        } catch (\RuntimeException $e) {
            $statusCode = in_array($e->getCode(), [400, 401, 403, 429, 500, 503]) ? $e->getCode() : 400;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'details' => ['error_code' => $e->getCode()],
            ], $statusCode);
        }
    }

    /**
     * Test Anthropic connection using tenant API key (falls back to global).
     */
    private function testTenantAnthropicConnection(int $tenantId): JsonResponse
    {
        // Apply tenant API key override into config if available
        $tenantApiKey = TenantApiSetting::get($tenantId, 'anthropic_api_key');
        if (! empty($tenantApiKey)) {
            config(['ai.providers.anthropic.api_key' => $tenantApiKey]);
        } else {
            // Fall back to global SystemSetting
            SystemSetting::loadIntoConfig(['anthropic_api_key' => 'ai.providers.anthropic.api_key']);
        }

        $effectiveKey = config('ai.providers.anthropic.api_key');

        if (empty($effectiveKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API key Anthropic belum dikonfigurasi. Silakan isi API key di pengaturan atau hubungi administrator.',
                'details' => null,
            ], 400);
        }

        try {
            $provider = new AnthropicProvider;
            $result = $provider->generate('Test connection - respond with: OK');

            return response()->json([
                'success' => true,
                'message' => 'Koneksi Anthropic berhasil! Provider aktif dan siap digunakan.',
                'details' => [
                    'model' => $result['model'] ?? config('ai.providers.anthropic.model', 'claude-3-5-sonnet-20241022'),
                    'response' => substr($result['text'] ?? '', 0, 100),
                ],
            ]);
        } catch (RateLimitException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Anthropic API rate limit tercapai. Silakan coba beberapa saat lagi.',
                'details' => ['error_code' => $e->getCode()],
            ], 429);
        } catch (\RuntimeException $e) {
            $statusCode = in_array($e->getCode(), [400, 401, 403, 429, 500, 503]) ? $e->getCode() : 400;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'details' => ['error_code' => $e->getCode()],
            ], $statusCode);
        }
    }

    /**
     * Save AI Provider settings for the tenant.
     * Requirements: 5.1–5.6
     */
    public function saveAiProviderSettings(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(! $tenantId, 403);

        $validated = $request->validate([
            'ai_provider_override' => 'nullable|boolean',
            'ai_provider' => 'nullable|string|in:gemini,anthropic',
            'anthropic_api_key' => 'nullable|string|max:500',
            'gemini_api_key' => 'nullable|string|max:500',
        ]);

        $override = (bool) ($validated['ai_provider_override'] ?? false);

        if ($override) {
            // Simpan pilihan provider
            TenantApiSetting::set(
                tenantId: $tenantId,
                key: 'ai_provider',
                value: $validated['ai_provider'] ?? 'gemini',
                encrypt: false,
                group: 'ai',
                label: 'AI Provider',
            );

            // Simpan Anthropic API key jika diisi (jangan overwrite jika kosong)
            if (! empty($validated['anthropic_api_key'])) {
                TenantApiSetting::set(
                    tenantId: $tenantId,
                    key: 'anthropic_api_key',
                    value: $validated['anthropic_api_key'],
                    encrypt: true,
                    group: 'ai',
                    label: 'Anthropic API Key',
                );
            }

            // Simpan Gemini API key jika diisi (jangan overwrite jika kosong)
            if (! empty($validated['gemini_api_key'])) {
                TenantApiSetting::set(
                    tenantId: $tenantId,
                    key: 'gemini_api_key',
                    value: $validated['gemini_api_key'],
                    encrypt: true,
                    group: 'ai',
                    label: 'Gemini API Key',
                );
            }
        } else {
            // Override dinonaktifkan — hapus konfigurasi tenant
            TenantApiSetting::remove($tenantId, 'ai_provider');
            TenantApiSetting::remove($tenantId, 'anthropic_api_key');
            TenantApiSetting::remove($tenantId, 'gemini_api_key');
        }

        event(new SettingsUpdated(
            type: 'api',
            tenantId: $tenantId,
            metadata: ['settings_updated' => ['ai_provider', 'anthropic_api_key', 'gemini_api_key']],
        ));

        $this->cacheService->clearTenantCache($tenantId);

        return back()->with('success', 'Pengaturan AI Provider berhasil disimpan.');
    }

    /**
     * Get real-time AI provider status for the tenant (AJAX).
     * Requirements: 5.8
     */
    public function getAiProviderStatus(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(! $tenantId, 403);

        $tenantProvider = TenantApiSetting::get($tenantId, 'ai_provider');
        $hasOverride = ! empty($tenantProvider);

        $globalDefault = SystemSetting::get('ai_default_provider')
            ?? config('ai.default_provider', 'gemini');

        $activeProvider = $hasOverride ? $tenantProvider : $globalDefault;

        $providerLabels = [
            'gemini' => 'Gemini',
            'anthropic' => 'Anthropic',
        ];

        return response()->json([
            'active_provider' => $activeProvider,
            'active_provider_label' => $providerLabels[$activeProvider] ?? ucfirst($activeProvider),
            'is_override' => $hasOverride,
            'global_default' => $globalDefault,
            'global_default_label' => $providerLabels[$globalDefault] ?? ucfirst($globalDefault),
            'has_anthropic_key' => TenantApiSetting::has($tenantId, 'anthropic_api_key'),
            'has_gemini_key' => TenantApiSetting::has($tenantId, 'gemini_api_key'),
        ]);
    }

    /**
     * Mask a sensitive value for display: show first 4 + last 4 chars.
     */
    private function mask(string $value): string
    {
        $len = strlen($value);
        if ($len <= 8) {
            return str_repeat('*', $len);
        }

        return substr($value, 0, 4).str_repeat('*', $len - 8).substr($value, -4);
    }
}
