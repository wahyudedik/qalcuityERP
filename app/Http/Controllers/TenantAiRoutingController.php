<?php

namespace App\Http\Controllers;

use App\Enums\AiUseCase;
use App\Models\AiUsageCostLog;
use App\Models\AiUseCaseRoute;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Controller untuk tenant mengelola override routing rules AI.
 *
 * Tenant dapat meng-override routing rule global untuk use case tertentu,
 * memilih provider dan model yang berbeda sesuai kebutuhan bisnis mereka.
 *
 * Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8
 */
class TenantAiRoutingController extends Controller
{
    /**
     * Tampilkan halaman AI Routing untuk tenant.
     *
     * Menampilkan:
     * - Routing rules yang berlaku (global + override tenant)
     * - Label "Override Aktif" untuk rule tenant-specific
     * - Estimasi biaya per use case bulan berjalan
     * - Form untuk membuat override
     *
     * Requirements: 5.1, 5.4, 5.6, 5.8
     */
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(! $tenantId, 403, 'Tenant ID tidak ditemukan.');

        $tenant = Tenant::findOrFail($tenantId);
        $tenantPlan = $tenant->subscription_plan ?? 'trial';

        // Ambil semua use case yang tersedia
        $useCases = AiUseCase::cases();

        // Ambil global rules dan tenant-specific rules
        $globalRules = AiUseCaseRoute::globalRules()->active()->get()->keyBy('use_case');
        $tenantRules = AiUseCaseRoute::tenantRules($tenantId)->active()->get()->keyBy('use_case');

        // Gabungkan: prioritas tenant rules > global rules > config default
        $routingRules = [];
        foreach ($useCases as $useCase) {
            $useCaseValue = $useCase->value;

            // Cek apakah ada tenant-specific rule
            $tenantRule = $tenantRules->get($useCaseValue);
            $globalRule = $globalRules->get($useCaseValue);

            // Tentukan rule yang berlaku
            $activeRule = $tenantRule ?? $globalRule;

            // Jika tidak ada rule di database, gunakan config default
            if (! $activeRule) {
                $configRule = config("ai.use_case_routing.{$useCaseValue}");
                if ($configRule) {
                    $activeRule = (object) [
                        'use_case' => $useCaseValue,
                        'provider' => $configRule['provider'],
                        'model' => $configRule['model'],
                        'min_plan' => $configRule['min_plan'],
                        'is_active' => true,
                        'is_override' => false,
                        'is_config_default' => true,
                    ];
                }
            } else {
                $activeRule->is_override = $tenantRule !== null;
                $activeRule->is_config_default = false;
            }

            $routingRules[] = $activeRule;
        }

        // Hitung estimasi biaya per use case bulan berjalan
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $costByUseCase = AiUsageCostLog::where('tenant_id', $tenantId)
            ->inDateRange($startOfMonth, $endOfMonth)
            ->selectRaw('use_case, SUM(estimated_cost_idr) as total_cost, COUNT(*) as request_count')
            ->groupBy('use_case')
            ->get()
            ->keyBy('use_case');

        // Provider yang tersedia berdasarkan plan tenant
        $availableProviders = $this->getAvailableProviders($tenantPlan);

        // Plan hierarchy untuk validasi
        $planHierarchy = config('ai.plan_hierarchy', ['trial', 'starter', 'business', 'professional', 'enterprise']);

        return view('settings.ai-routing', compact(
            'routingRules',
            'costByUseCase',
            'availableProviders',
            'tenantPlan',
            'planHierarchy',
            'useCases'
        ));
    }

    /**
     * Buat override routing rule tenant-specific.
     *
     * Membuat record AiUseCaseRoute baru dengan tenant_id diisi.
     * Validasi:
     * - Provider yang dipilih tersedia
     * - Plan tenant memenuhi syarat minimum
     *
     * Requirements: 5.2, 5.3, 5.7
     */
    public function store(Request $request): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(! $tenantId, 403, 'Tenant ID tidak ditemukan.');

        $validated = $request->validate([
            'use_case' => 'required|string|max:100',
            'provider' => 'required|string|in:gemini,anthropic',
            'model' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $tenant = Tenant::findOrFail($tenantId);
        $tenantPlan = $tenant->subscription_plan ?? 'trial';

        // Validasi: cek apakah provider tersedia untuk plan tenant
        $availableProviders = $this->getAvailableProviders($tenantPlan);
        if (! in_array($validated['provider'], $availableProviders)) {
            return back()->withErrors([
                'provider' => "Provider {$validated['provider']} tidak tersedia untuk plan {$tenantPlan}. Upgrade plan Anda untuk mengakses provider ini.",
            ])->withInput();
        }

        // Cek apakah use case memerlukan plan minimum
        $globalRule = AiUseCaseRoute::globalRules()
            ->where('use_case', $validated['use_case'])
            ->first();

        $configRule = config("ai.use_case_routing.{$validated['use_case']}");
        $minPlan = $globalRule->min_plan ?? $configRule['min_plan'] ?? null;

        if ($minPlan && ! $this->planMeetsRequirement($tenantPlan, $minPlan)) {
            return back()->withErrors([
                'use_case' => "Use case {$validated['use_case']} memerlukan plan minimum {$minPlan}. Plan Anda saat ini: {$tenantPlan}.",
            ])->withInput();
        }

        // Buat atau update override tenant-specific
        $route = AiUseCaseRoute::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'use_case' => $validated['use_case'],
            ],
            [
                'provider' => $validated['provider'],
                'model' => $validated['model'] ?? null,
                'min_plan' => $minPlan, // Inherit dari global rule
                'is_active' => true,
                'description' => $validated['description'] ?? null,
            ]
        );

        // Invalidate cache routing rules untuk tenant ini
        Cache::forget("ai_routing_rules:{$tenantId}");

        Log::info("TenantAiRoutingController: Override created for tenant [{$tenantId}], use_case [{$validated['use_case']}], provider [{$validated['provider']}]");

        return back()->with('success', "Override routing rule untuk {$validated['use_case']} berhasil disimpan.");
    }

    /**
     * Hapus override tenant-specific.
     *
     * Menghapus record AiUseCaseRoute tenant-specific,
     * kembali menggunakan routing rule global.
     *
     * Requirements: 5.5
     */
    public function destroy(Request $request, AiUseCaseRoute $route): RedirectResponse
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(! $tenantId, 403, 'Tenant ID tidak ditemukan.');

        // Validasi: pastikan route ini milik tenant yang sedang login
        if ($route->tenant_id !== $tenantId) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus routing rule ini.');
        }

        $useCase = $route->use_case;
        $route->delete();

        // Invalidate cache routing rules untuk tenant ini
        Cache::forget("ai_routing_rules:{$tenantId}");

        Log::info("TenantAiRoutingController: Override deleted for tenant [{$tenantId}], use_case [{$useCase}]");

        return back()->with('success', "Override routing rule untuk {$useCase} berhasil dihapus. Kembali menggunakan routing rule global.");
    }

    /**
     * Dapatkan daftar provider yang tersedia berdasarkan plan tenant.
     *
     * Logika:
     * - Trial, Starter, Business: hanya Gemini
     * - Professional, Enterprise: Gemini + Anthropic
     *
     * Requirements: 5.6
     */
    private function getAvailableProviders(string $plan): array
    {
        $planHierarchy = config('ai.plan_hierarchy', ['trial', 'starter', 'business', 'professional', 'enterprise']);
        $planIndex = array_search($plan, $planHierarchy);

        // Professional dan Enterprise dapat akses semua provider
        if ($planIndex !== false && $planIndex >= 3) {
            return ['gemini', 'anthropic'];
        }

        // Plan di bawah Professional hanya dapat akses Gemini
        return ['gemini'];
    }

    /**
     * Cek apakah plan tenant memenuhi syarat minimum.
     *
     * Requirements: 3.3, 5.7
     */
    private function planMeetsRequirement(string $currentPlan, string $requiredPlan): bool
    {
        $planHierarchy = config('ai.plan_hierarchy', ['trial', 'starter', 'business', 'professional', 'enterprise']);

        $currentIndex = array_search($currentPlan, $planHierarchy);
        $requiredIndex = array_search($requiredPlan, $planHierarchy);

        // Jika salah satu plan tidak ditemukan, anggap tidak memenuhi syarat
        if ($currentIndex === false || $requiredIndex === false) {
            return false;
        }

        return $currentIndex >= $requiredIndex;
    }
}
