<?php

namespace App\Http\Controllers;

use App\Models\TenantApiSetting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantIntegrationSettingsController extends Controller
{
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
        abort_if(!$tenantId, 403);

        // Load current saved settings, decrypted for display (masked)
        $saved = [];
        foreach (self::SETTINGS_MAP as $key => [$group, $label, $encrypted]) {
            $value = TenantApiSetting::get($tenantId, $key);
            $saved[$key] = [
                'value' => $value,
                'has_value' => !empty($value),
                'masked' => $encrypted && !empty($value) ? $this->mask($value) : $value,
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

        return view('settings.integrations', compact('saved', 'groups', 'groupMeta', 'tenantId'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(!$tenantId, 403);

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

        return back()->with('success', 'Pengaturan integrasi berhasil disimpan.');
    }

    public function testFonnte(Request $request): \Illuminate\Http\JsonResponse
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(!$tenantId, 403);

        $token = TenantApiSetting::get($tenantId, 'fonnte_token')
            ?? config('services.fonnte.token');

        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Fonnte token belum dikonfigurasi.']);
        }

        $validated = $request->validate(['phone' => 'required|string|max:20']);
        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders(['Authorization' => $token])
                ->post('https://api.fonnte.com/send', [
                    'target' => $phone,
                    'message' => 'Ini adalah pesan uji coba dari Qalcuity ERP. Fonnte berhasil dikonfigurasi! ✅',
                ]);

            $result = $response->json();

            return response()->json([
                'success' => $response->successful() && ($result['status'] ?? false),
                'message' => $result['status'] ?? false
                    ? "Pesan uji coba berhasil dikirim ke {$phone}."
                    : ('Gagal: ' . ($result['reason'] ?? 'Unknown error')),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Koneksi gagal: ' . $e->getMessage()]);
        }
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
        return substr($value, 0, 4) . str_repeat('*', $len - 8) . substr($value, -4);
    }
}
