<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\AI\ModelSwitcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Gemini\Client as GeminiClient;

class SystemSettingsController extends Controller
{
    /**
     * Map of setting key => [config path, encrypt, group, label]
     */
    private const SETTINGS_MAP = [
        // AI / Gemini
        'gemini_api_key' => ['gemini.api_key', true, 'ai', 'Gemini API Key'],
        'gemini_model' => ['gemini.model', false, 'ai', 'Gemini Model'],
        'gemini_timeout' => ['gemini.timeout', false, 'ai', 'Timeout (detik)'],
        'ai_response_cache_enabled' => ['gemini.optimization.cache_enabled', false, 'ai', 'Cache AI Response'],
        'ai_cache_short_ttl' => ['gemini.optimization.cache_ttl.short', false, 'ai', 'Cache TTL Pendek (detik)'],
        'ai_cache_default_ttl' => ['gemini.optimization.cache_ttl.default', false, 'ai', 'Cache TTL Default (detik)'],
        'ai_cache_long_ttl' => ['gemini.optimization.cache_ttl.long', false, 'ai', 'Cache TTL Panjang (detik)'],
        'ai_rule_based_enabled' => ['gemini.optimization.rule_based_enabled', false, 'ai', 'Rule-Based Response'],
        'ai_streaming_enabled' => ['gemini.optimization.streaming_enabled', false, 'ai', 'Streaming AI'],
        // AI Model Auto-Switching
        'gemini_fallback_models' => ['gemini.fallback_models', false, 'ai', 'Fallback Models (JSON atau comma-separated)'],
        'gemini_rate_limit_cooldown' => ['gemini.rate_limit_cooldown', false, 'ai', 'Rate Limit Cooldown (detik)'],
        'gemini_quota_cooldown' => ['gemini.quota_cooldown', false, 'ai', 'Quota Cooldown (detik)'],
        'gemini_log_retention_days' => ['gemini.log_retention_days', false, 'ai', 'Retensi Log Switch (hari)'],

        // Email / SMTP
        'mail_host' => ['mail.mailers.smtp.host', false, 'mail', 'SMTP Host'],
        'mail_port' => ['mail.mailers.smtp.port', false, 'mail', 'SMTP Port'],
        'mail_username' => ['mail.mailers.smtp.username', false, 'mail', 'SMTP Username'],
        'mail_password' => ['mail.mailers.smtp.password', true, 'mail', 'SMTP Password'],
        'mail_encryption' => ['mail.mailers.smtp.encryption', false, 'mail', 'Enkripsi (tls/ssl/null)'],
        'mail_from_address' => ['mail.from.address', false, 'mail', 'From Address'],
        'mail_from_name' => ['mail.from.name', false, 'mail', 'From Name'],

        // Google OAuth
        'google_client_id' => ['services.google.client_id', false, 'oauth', 'Google Client ID'],
        'google_client_secret' => ['services.google.client_secret', true, 'oauth', 'Google Client Secret'],

        // Push Notifications (VAPID)
        'vapid_public_key' => ['services.vapid.public_key', false, 'push', 'VAPID Public Key'],
        'vapid_private_key' => ['services.vapid.private_key', true, 'push', 'VAPID Private Key'],

        // Error Alerts
        'slack_error_webhook_url' => ['services.slack.error_webhook', false, 'alert', 'Slack Webhook URL'],
        'error_alert_email' => ['services.error_alert_email.recipients', false, 'alert', 'Email Penerima Error'],

        // App settings
        'app_name' => ['app.name', false, 'app', 'Nama Aplikasi'],
        'app_url' => ['app.url', false, 'app', 'URL Aplikasi'],
        'app_timezone' => ['app.timezone', false, 'app', 'Timezone'],
    ];

    public function index()
    {
        $raw = SystemSetting::all()->keyBy('key');

        // Build current values for each setting (mask encrypted ones)
        $settings = [];
        foreach (self::SETTINGS_MAP as $key => [$configPath, $encrypt, $group, $label]) {
            $record = $raw->get($key);
            $settings[$key] = [
                'label' => $label,
                'group' => $group,
                'encrypt' => $encrypt,
                'config_path' => $configPath,
                'has_value' => $record && !empty($record->value),
                'value' => ($record && !$encrypt && !empty($record->value)) ? $record->value : null,
                // For encrypted fields — show placeholder if set
                'is_set' => $record && !empty($record->value),
            ];
        }

        // Group settings
        $grouped = [];
        foreach ($settings as $key => $s) {
            $grouped[$s['group']][$key] = $s;
        }

        // Current config fallback values (from .env)
        $envFallbacks = $this->getEnvFallbacks();

        return view('super-admin.settings.index', compact('grouped', 'envFallbacks'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'gemini_model' => 'nullable|string|max:100',
            'gemini_timeout' => 'nullable|integer|min:10|max:300',
            'gemini_rate_limit_cooldown' => 'nullable|integer|min:1|max:86400',
            'gemini_quota_cooldown' => 'nullable|integer|min:1|max:86400',
            'gemini_log_retention_days' => 'nullable|integer|min:1|max:365',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl,starttls,null,',
            'google_client_id' => 'nullable|string|max:500',
            'slack_error_webhook_url' => 'nullable|url|max:500',
            'error_alert_email' => 'nullable|string|max:500',
            'app_name' => 'nullable|string|max:100',
            'app_url' => 'nullable|url|max:255',
            'app_timezone' => 'nullable|timezone',
        ]);

        // Track whether any Gemini auto-switching config was changed
        $geminiSwitcherChanged = false;
        $geminiSwitcherKeys = ['gemini_fallback_models', 'gemini_rate_limit_cooldown', 'gemini_quota_cooldown', 'gemini_log_retention_days'];

        foreach (self::SETTINGS_MAP as $key => [$configPath, $encrypt, $group, $label]) {
            // Skip if not submitted
            if (!$request->has($key)) {
                continue;
            }

            $value = $request->input($key);

            // For encrypted fields: if value is empty string, don't overwrite existing
            if ($encrypt && empty($value)) {
                if (SystemSetting::has($key)) {
                    continue;
                }
            }

            // Special handling: gemini_fallback_models — accept JSON string or comma-separated, store as JSON array
            if ($key === 'gemini_fallback_models' && !empty($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    // Already valid JSON array — re-encode to normalise
                    $value = json_encode(array_values(array_filter(array_map('trim', $decoded))));
                } else {
                    // Treat as comma-separated list
                    $models = array_values(array_filter(array_map('trim', explode(',', $value))));
                    $value = json_encode($models);
                }
            }

            SystemSetting::set($key, $value, $encrypt, $group, $label);

            if (in_array($key, $geminiSwitcherKeys)) {
                $geminiSwitcherChanged = true;
            }
        }

        SystemSetting::clearCache();

        // Invalidate ModelSwitcher cache when Gemini auto-switching config changes
        if ($geminiSwitcherChanged) {
            try {
                app(ModelSwitcher::class)->resetAll();
            } catch (\Throwable $e) {
                \Log::warning('SystemSettingsController: failed to reset ModelSwitcher cache.', ['error' => $e->getMessage()]);
            }
        }

        return back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }

    public function testMail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            // Apply current DB settings before sending
            $this->applyMailConfig();

            Mail::raw('Test email dari Qalcuity ERP SuperAdmin Settings. Jika Anda menerima ini, konfigurasi SMTP sudah benar.', function ($msg) use ($request) {
                $msg->to($request->test_email)
                    ->subject('Test Email — Qalcuity ERP');
            });

            return back()->with('success', "Test email berhasil dikirim ke {$request->test_email}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal kirim email: ' . $e->getMessage());
        }
    }

    /**
     * BUG-AI-003 FIX: Test Gemini API Key connection
     */
    public function testGeminiApiKey(Request $request)
    {
        try {
            // Get API key from request or database
            $apiKey = $request->input('gemini_api_key');

            // If not in request, get from database
            if (empty($apiKey)) {
                $apiKey = SystemSetting::get('gemini_api_key');
            }

            // Validate API key exists
            if (empty($apiKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI Service configuration tidak ditemukan. Silakan simpan konfigurasi terlebih dahulu di pengaturan.',
                    'details' => null,
                ], 400);
            }

            // Test API key by making a simple request
            $client = \Gemini::factory()->withApiKey($apiKey)->make();
            $model = config('gemini.model', 'gemini-2.0-flash');

            // Make a simple test request
            $response = $client->geminiPro()->generateContent('Test connection - respond with: OK');
            $text = $response->text();

            return response()->json([
                'success' => true,
                'message' => 'AI Service configuration berhasil divalidasi! Koneksi aktif.',
                'details' => [
                    'model' => $model,
                    'response' => substr($text, 0, 100),
                    'api_key_prefix' => substr($apiKey, 0, 10) . '...',
                ],
            ]);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // HTTP error (401, 403, 429, etc.)
            $statusCode = $e->getResponse()->getStatusCode();
            $errorBody = $e->getResponse()->getBody()->getContents();

            $errorMessage = match ($statusCode) {
                401 => 'Konfigurasi tidak valid (Unauthorized). Periksa kembali pengaturan AI Service Anda.',
                403 => 'Konfigurasi tidak memiliki akses (Forbidden). Pastikan layanan AI sudah diaktifkan.',
                429 => 'Layanan AI sedang mengalami keterbatasan (Rate Limited). Silakan coba beberapa saat lagi.',
                default => 'Error HTTP ' . $statusCode . ': ' . $errorBody,
            };

            \Log::error('AI Service Configuration Test Failed', [
                'status' => $statusCode,
                'error' => $errorBody,
            ]);

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'details' => [
                    'status_code' => $statusCode,
                    'error' => $errorBody,
                ],
            ], $statusCode);

        } catch (\Throwable $e) {
            \Log::error('AI Service Configuration Test Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal test API key: ' . $e->getMessage(),
                'details' => null,
            ], 500);
        }
    }

    public function regenerateVapid()
    {
        try {
            // Use artisan command to generate VAPID keys
            Artisan::call('vapid:generate', ['--force' => true]);
            $output = Artisan::output();

            // Parse output for keys
            $publicKey = null;
            $privateKey = null;

            if (preg_match('/VAPID_PUBLIC_KEY=(.+)/', $output, $m)) {
                $publicKey = trim($m[1]);
            }
            if (preg_match('/VAPID_PRIVATE_KEY=(.+)/', $output, $m)) {
                $privateKey = trim($m[1]);
            }

            if ($publicKey && $privateKey) {
                SystemSetting::set('vapid_public_key', $publicKey, false, 'push', 'VAPID Public Key');
                SystemSetting::set('vapid_private_key', $privateKey, true, 'push', 'VAPID Private Key');
                return back()->with('success', 'VAPID keys berhasil di-generate ulang dan disimpan.');
            }

            return back()->with('error', 'Gagal parse VAPID keys dari output artisan. Output: ' . $output);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal generate VAPID: ' . $e->getMessage());
        }
    }

    private function applyMailConfig(): void
    {
        $mailMap = [
            'mail_host' => 'mail.mailers.smtp.host',
            'mail_port' => 'mail.mailers.smtp.port',
            'mail_username' => 'mail.mailers.smtp.username',
            'mail_password' => 'mail.mailers.smtp.password',
            'mail_encryption' => 'mail.mailers.smtp.encryption',
            'mail_from_address' => 'mail.from.address',
            'mail_from_name' => 'mail.from.name',
        ];

        foreach ($mailMap as $key => $configPath) {
            $val = SystemSetting::get($key);
            if ($val !== null) {
                config([$configPath => $val]);
            }
        }
    }

    private function getEnvFallbacks(): array
    {
        return [
            'gemini_api_key' => !empty(config('gemini.api_key')),
            'mail_host' => config('mail.mailers.smtp.host', ''),
            'mail_from_address' => config('mail.from.address', ''),
            'google_client_id' => !empty(config('services.google.client_id')),
            'vapid_public_key' => !empty(config('services.vapid.public_key')),
            'app_name' => config('app.name', 'Qalcuity ERP'),
            'app_url' => config('app.url', ''),
            'app_timezone' => config('app.timezone', 'Asia/Jakarta'),
        ];
    }
}
