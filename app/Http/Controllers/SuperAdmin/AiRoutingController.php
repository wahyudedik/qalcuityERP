<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AiUsageCostLog;
use App\Models\AiUseCaseRoute;
use App\Services\AI\ProviderSwitcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Controller untuk manajemen routing rules AI oleh SuperAdmin.
 *
 * Fitur:
 * - CRUD routing rules (index, edit, update, store)
 * - Reset ke default (seeder)
 * - Validasi provider dan model
 * - Cache invalidation
 * - Ringkasan penggunaan dan biaya per use case
 *
 * Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8, 11.5, 11.6
 */
class AiRoutingController extends Controller
{
    public function __construct(
        private readonly ProviderSwitcher $switcher,
    ) {}

    /**
     * Tampilkan semua routing rules dengan status provider real-time
     * dan ringkasan penggunaan 30 hari terakhir.
     *
     * Requirements: 4.1, 4.5, 4.8
     */
    public function index(): View
    {
        // Ambil semua global routing rules (tenant_id = NULL)
        $routes = AiUseCaseRoute::withoutTenantScope()
            ->whereNull('tenant_id')
            ->orderBy('use_case')
            ->get();

        // Status provider real-time
        $providerStatus = $this->getProviderAvailabilityStatus();

        // Ringkasan penggunaan 30 hari terakhir per use case
        $usageStats = $this->getUsageStats();

        // Daftar provider yang tersedia
        $availableProviders = ['gemini', 'anthropic'];

        // Daftar plan yang tersedia
        $availablePlans = ['trial', 'starter', 'business', 'professional', 'enterprise'];

        return view('super-admin.ai-routing.index', compact(
            'routes',
            'providerStatus',
            'usageStats',
            'availableProviders',
            'availablePlans'
        ));
    }

    /**
     * Tampilkan form edit routing rule.
     *
     * Requirements: 4.2
     */
    public function edit(AiUseCaseRoute $route): View
    {
        // Daftar provider yang tersedia
        $availableProviders = ['gemini', 'anthropic'];

        // Daftar plan yang tersedia
        $availablePlans = ['trial', 'starter', 'business', 'professional', 'enterprise'];

        // Status provider real-time
        $providerStatus = $this->getProviderAvailabilityStatus();

        return view('super-admin.ai-routing.edit', compact(
            'route',
            'availableProviders',
            'availablePlans',
            'providerStatus'
        ));
    }

    /**
     * Simpan perubahan routing rule.
     *
     * Requirements: 4.2, 4.3, 11.5, 11.6
     */
    public function update(Request $request, AiUseCaseRoute $route): RedirectResponse
    {
        $validated = $request->validate([
            'provider' => 'required|in:gemini,anthropic',
            'model' => 'nullable|string|max:100',
            'min_plan' => 'nullable|in:trial,starter,business,professional,enterprise',
            'fallback_chain' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        // Validasi provider terdaftar
        if (! in_array($validated['provider'], ['gemini', 'anthropic'])) {
            return back()->withErrors(['provider' => 'Provider tidak valid. Pilih gemini atau anthropic.']);
        }

        // Warning jika model tidak dikenal (bukan error — model baru dapat ditambahkan)
        if (! empty($validated['model'])) {
            $knownModels = $this->getKnownModels($validated['provider']);
            if (! in_array($validated['model'], $knownModels)) {
                session()->flash('warning', "Model '{$validated['model']}' tidak dikenal untuk provider {$validated['provider']}. Pastikan model ini valid.");
            }
        }

        // Parse fallback_chain dari JSON atau comma-separated
        $fallbackChain = null;
        if (! empty($validated['fallback_chain'])) {
            $decoded = json_decode($validated['fallback_chain'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $fallbackChain = array_values(array_filter(array_map('trim', $decoded)));
            } else {
                $fallbackChain = array_values(array_filter(array_map('trim', explode(',', $validated['fallback_chain']))));
            }
        }

        // Update routing rule
        $route->update([
            'provider' => $validated['provider'],
            'model' => $validated['model'] ?? null,
            'min_plan' => $validated['min_plan'] ?? null,
            'fallback_chain' => $fallbackChain,
            'is_active' => $validated['is_active'] ?? true,
            'description' => $validated['description'] ?? null,
        ]);

        // Invalidate cache
        $this->invalidateRoutingCache();

        return redirect()->route('super-admin.ai-routing.index')
            ->with('success', "Routing rule untuk use case '{$route->use_case}' berhasil diperbarui.");
    }

    /**
     * Tambah use case baru (custom routing rule).
     *
     * Requirements: 4.7
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'use_case' => 'required|string|max:100|unique:ai_use_case_routes,use_case,NULL,id,tenant_id,NULL',
            'provider' => 'required|in:gemini,anthropic',
            'model' => 'nullable|string|max:100',
            'min_plan' => 'nullable|in:trial,starter,business,professional,enterprise',
            'fallback_chain' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        // Validasi provider terdaftar
        if (! in_array($validated['provider'], ['gemini', 'anthropic'])) {
            return back()->withErrors(['provider' => 'Provider tidak valid. Pilih gemini atau anthropic.']);
        }

        // Warning jika model tidak dikenal
        if (! empty($validated['model'])) {
            $knownModels = $this->getKnownModels($validated['provider']);
            if (! in_array($validated['model'], $knownModels)) {
                session()->flash('warning', "Model '{$validated['model']}' tidak dikenal untuk provider {$validated['provider']}. Pastikan model ini valid.");
            }
        }

        // Parse fallback_chain
        $fallbackChain = null;
        if (! empty($validated['fallback_chain'])) {
            $decoded = json_decode($validated['fallback_chain'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $fallbackChain = array_values(array_filter(array_map('trim', $decoded)));
            } else {
                $fallbackChain = array_values(array_filter(array_map('trim', explode(',', $validated['fallback_chain']))));
            }
        }

        // Buat routing rule baru (global — tenant_id = NULL)
        AiUseCaseRoute::withoutTenantScope()->create([
            'tenant_id' => null,
            'use_case' => $validated['use_case'],
            'provider' => $validated['provider'],
            'model' => $validated['model'] ?? null,
            'min_plan' => $validated['min_plan'] ?? null,
            'fallback_chain' => $fallbackChain,
            'is_active' => $validated['is_active'] ?? true,
            'description' => $validated['description'] ?? null,
        ]);

        // Invalidate cache
        $this->invalidateRoutingCache();

        return redirect()->route('super-admin.ai-routing.index')
            ->with('success', "Routing rule baru untuk use case '{$validated['use_case']}' berhasil ditambahkan.");
    }

    /**
     * Reset semua routing rules ke default (seeder).
     *
     * Requirements: 4.4
     */
    public function resetToDefault(): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Hapus semua global routing rules
            AiUseCaseRoute::withoutTenantScope()
                ->whereNull('tenant_id')
                ->delete();

            // Re-seed routing rules default
            $this->seedDefaultRoutes();

            DB::commit();

            // Invalidate semua cache routing rules
            $this->invalidateAllRoutingCache();

            return redirect()->route('super-admin.ai-routing.index')
                ->with('success', 'Semua routing rules berhasil direset ke konfigurasi default.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('AiRoutingController: gagal reset routing rules ke default', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal reset routing rules: '.$e->getMessage());
        }
    }

    // ─── Private Helpers ──────────────────────────────────────────

    /**
     * Kembalikan status availability real-time untuk semua provider.
     *
     * Requirements: 4.5, 4.8
     */
    private function getProviderAvailabilityStatus(): array
    {
        $providers = ['gemini', 'anthropic'];
        $status = [];

        try {
            $availability = $this->switcher->getProviderAvailability($providers);

            foreach ($availability as $item) {
                $providerName = $item['provider'];
                $isConfigured = $this->isProviderConfigured($providerName);

                if (! $isConfigured) {
                    $statusLabel = 'Tidak Dikonfigurasi';
                    $statusColor = 'gray';
                } elseif ($item['available']) {
                    $statusLabel = 'Aktif';
                    $statusColor = 'green';
                } else {
                    $statusLabel = 'Cooldown';
                    $statusColor = 'amber';
                }

                $status[$providerName] = [
                    'provider' => $providerName,
                    'label' => ucfirst($providerName),
                    'configured' => $isConfigured,
                    'available' => $item['available'],
                    'status_label' => $statusLabel,
                    'status_color' => $statusColor,
                    'reason' => $item['reason'],
                    'recovers_at' => $item['recovers_at'] ? $item['recovers_at']->toDateTimeString() : null,
                ];
            }
        } catch (\Throwable $e) {
            Log::debug('AiRoutingController: ProviderSwitcher not available.', ['error' => $e->getMessage()]);

            foreach ($providers as $providerName) {
                $isConfigured = $this->isProviderConfigured($providerName);
                $status[$providerName] = [
                    'provider' => $providerName,
                    'label' => ucfirst($providerName),
                    'configured' => $isConfigured,
                    'available' => $isConfigured,
                    'status_label' => $isConfigured ? 'Aktif' : 'Tidak Dikonfigurasi',
                    'status_color' => $isConfigured ? 'green' : 'gray',
                    'reason' => null,
                    'recovers_at' => null,
                ];
            }
        }

        return $status;
    }

    /**
     * Cek apakah provider dikonfigurasi (API key tidak kosong).
     */
    private function isProviderConfigured(string $provider): bool
    {
        $apiKey = config("ai.providers.{$provider}.api_key");

        return ! empty($apiKey);
    }

    /**
     * Kembalikan ringkasan penggunaan 30 hari terakhir per use case.
     *
     * Requirements: 4.8
     */
    private function getUsageStats(): array
    {
        try {
            $from = now()->subDays(30);
            $to = now();

            $stats = AiUsageCostLog::withoutGlobalScope('tenant')
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('use_case, COUNT(*) as request_count, SUM(estimated_cost_idr) as total_cost')
                ->groupBy('use_case')
                ->get()
                ->keyBy('use_case');

            return $stats->toArray();
        } catch (\Throwable $e) {
            Log::debug('AiRoutingController: could not load usage stats.', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Kembalikan daftar model yang dikenal untuk provider tertentu.
     */
    private function getKnownModels(string $provider): array
    {
        return match ($provider) {
            'gemini' => [
                'gemini-2.5-flash',
                'gemini-2.5-flash-lite',
                'gemini-1.5-flash',
                'gemini-1.5-pro',
            ],
            'anthropic' => [
                'claude-3-5-sonnet-20241022',
                'claude-3-haiku-20240307',
                'claude-3-opus-20240229',
            ],
            default => [],
        };
    }

    /**
     * Invalidate cache routing rules global.
     */
    private function invalidateRoutingCache(): void
    {
        Cache::forget('ai_routing_rules:global');
    }

    /**
     * Invalidate semua cache routing rules (global + semua tenant).
     */
    private function invalidateAllRoutingCache(): void
    {
        // Invalidate global cache
        Cache::forget('ai_routing_rules:global');

        // Invalidate tenant-specific cache (flush pattern)
        // Karena tidak ada cara langsung untuk flush pattern di Laravel cache,
        // kita hanya invalidate global cache. Tenant cache akan expire dalam 5 menit.
        // Alternatif: gunakan cache tags jika driver mendukung (Redis, Memcached).
    }

    /**
     * Seed routing rules default (16 use cases).
     */
    private function seedDefaultRoutes(): void
    {
        $routes = [
            // Lightweight use cases — Gemini Flash
            ['use_case' => 'chatbot',             'provider' => 'gemini', 'model' => 'gemini-2.5-flash', 'min_plan' => null],
            ['use_case' => 'crud_ai',             'provider' => 'gemini', 'model' => 'gemini-2.5-flash', 'min_plan' => null],
            ['use_case' => 'auto_reply',          'provider' => 'gemini', 'model' => 'gemini-2.5-flash', 'min_plan' => null],
            ['use_case' => 'invoice_parsing',     'provider' => 'gemini', 'model' => 'gemini-2.5-flash', 'min_plan' => null],
            ['use_case' => 'document_parsing',    'provider' => 'gemini', 'model' => 'gemini-2.5-flash', 'min_plan' => null],
            ['use_case' => 'notification_ai',     'provider' => 'gemini', 'model' => 'gemini-2.5-flash', 'min_plan' => null],
            ['use_case' => 'product_description', 'provider' => 'gemini', 'model' => 'gemini-2.5-flash', 'min_plan' => null],
            ['use_case' => 'email_draft',         'provider' => 'gemini', 'model' => 'gemini-2.5-flash', 'min_plan' => null],

            // Heavyweight use cases — Claude Sonnet
            ['use_case' => 'financial_report',        'provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
            ['use_case' => 'forecasting',             'provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
            ['use_case' => 'decision_support',        'provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
            ['use_case' => 'audit_analysis',          'provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
            ['use_case' => 'business_recommendation', 'provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
            ['use_case' => 'bank_reconciliation_ai',  'provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
            ['use_case' => 'budget_analysis',         'provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
            ['use_case' => 'anomaly_detection',       'provider' => 'anthropic', 'model' => 'claude-3-5-sonnet-20241022', 'min_plan' => 'professional'],
        ];

        foreach ($routes as $route) {
            AiUseCaseRoute::withoutTenantScope()->updateOrCreate(
                [
                    'tenant_id' => null,
                    'use_case' => $route['use_case'],
                ],
                [
                    'provider' => $route['provider'],
                    'model' => $route['model'],
                    'min_plan' => $route['min_plan'],
                    'fallback_chain' => null,
                    'is_active' => true,
                    'description' => null,
                ]
            );
        }
    }
}
