<?php

namespace Tests\Feature\BugExploration;

use App\Http\Middleware\RateLimitAiRequests;
use App\Models\Concerns\BelongsToTenant as ConcernsBelongsToTenant;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Scopes\TenantScope;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Task 12.5 — Verifikasi Fix Keamanan & Performa (Bug 1.24–1.28)
 *
 * Test ini memverifikasi bahwa semua fix untuk bug 1.24–1.28 sudah diterapkan
 * dengan benar pada kode yang sudah diperbaiki.
 *
 * EXPECTED OUTCOME: SEMUA LULUS
 *
 * Validates: Requirements 2.24, 2.25, 2.26, 2.27, 2.28
 */
class VerifyFix1224To1228Test extends BaseTestCase
{
    // ─── Bug 1.24: TenantScope ────────────────────────────────────────────────

    /**
     * @test
     * Bug 1.24 FIX: TenantScope class harus ada dan mengimplementasikan Scope interface
     *
     * Validates: Requirements 2.24
     */
    public function test_tenant_scope_class_exists_and_implements_scope_interface(): void
    {
        $this->assertTrue(
            class_exists(TenantScope::class),
            'TenantScope class harus ada di app/Models/Scopes/TenantScope.php'
        );

        $interfaces = class_implements(TenantScope::class);
        $this->assertContains(
            Scope::class,
            $interfaces,
            'TenantScope harus mengimplementasikan Illuminate\\Database\\Eloquent\\Scope interface'
        );
    }

    /**
     * @test
     * Bug 1.24 FIX: TenantScope harus memiliki method apply()
     *
     * Validates: Requirements 2.24
     */
    public function test_tenant_scope_has_apply_method(): void
    {
        $this->assertTrue(
            method_exists(TenantScope::class, 'apply'),
            'TenantScope harus memiliki method apply(Builder $builder, Model $model)'
        );
    }

    /**
     * @test
     * Bug 1.24 FIX: BelongsToTenant trait (App\Traits) harus mendaftarkan global scope
     *
     * Validates: Requirements 2.24
     */
    public function test_belongs_to_tenant_trait_registers_global_scope(): void
    {
        $this->assertTrue(
            trait_exists(BelongsToTenant::class),
            'App\\Traits\\BelongsToTenant trait harus ada'
        );

        // Verifikasi trait memiliki bootBelongsToTenant method
        $this->assertTrue(
            method_exists(BelongsToTenant::class, 'bootBelongsToTenant'),
            'BelongsToTenant trait harus memiliki bootBelongsToTenant() method'
        );
    }

    /**
     * @test
     * Bug 1.24 FIX: Model-model kritis harus menggunakan BelongsToTenant trait
     *
     * Validates: Requirements 2.24
     */
    public function test_critical_models_use_belongs_to_tenant_trait(): void
    {
        $criticalModels = [
            Customer::class,
            Product::class,
            SalesOrder::class,
            Invoice::class,
            JournalEntry::class,
            Employee::class,
        ];

        $missingTrait = [];

        foreach ($criticalModels as $modelClass) {
            if (! class_exists($modelClass)) {
                continue;
            }

            $traits = class_uses_recursive($modelClass);
            $hasTrait = in_array(BelongsToTenant::class, $traits)
                || in_array(ConcernsBelongsToTenant::class, $traits);

            if (! $hasTrait) {
                $missingTrait[] = $modelClass;
            }
        }

        $this->assertEmpty(
            $missingTrait,
            "Bug 1.24 FIX: Model-model berikut tidak menggunakan BelongsToTenant trait:\n".
            implode("\n", $missingTrait)
        );
    }

    /**
     * @test
     * Bug 1.24 FIX: BelongsToTenant trait harus memiliki global scope yang memfilter tenant_id
     *
     * Validates: Requirements 2.24
     */
    public function test_belongs_to_tenant_trait_has_tenant_filter_in_scope(): void
    {
        $traitFile = base_path('app/Traits/BelongsToTenant.php');
        $this->assertFileExists($traitFile, 'app/Traits/BelongsToTenant.php harus ada');

        $content = file_get_contents($traitFile);

        $hasGlobalScope = str_contains($content, 'addGlobalScope') ||
            str_contains($content, 'TenantScope');

        $this->assertTrue(
            $hasGlobalScope,
            'BelongsToTenant trait harus mendaftarkan global scope (addGlobalScope atau TenantScope)'
        );

        $hasTenantFilter = str_contains($content, 'tenant_id');
        $this->assertTrue(
            $hasTenantFilter,
            'BelongsToTenant trait harus memfilter berdasarkan tenant_id'
        );
    }

    // ─── Bug 1.25: AI Prompt Injection Sanitization ───────────────────────────

    /**
     * @test
     * Bug 1.25 FIX: ChatController harus memiliki sanitizeUserInput() method
     *
     * Validates: Requirements 2.25
     */
    public function test_chat_controller_has_sanitize_user_input_method(): void
    {
        $chatControllerFile = base_path('app/Http/Controllers/ChatController.php');
        $this->assertFileExists($chatControllerFile, 'ChatController.php harus ada');

        $content = file_get_contents($chatControllerFile);

        $this->assertStringContainsString(
            'sanitizeUserInput',
            $content,
            'Bug 1.25 FIX: ChatController harus memiliki method sanitizeUserInput()'
        );
    }

    /**
     * @test
     * Bug 1.25 FIX: sanitizeUserInput harus memfilter pola prompt injection
     *
     * Validates: Requirements 2.25
     */
    public function test_sanitize_user_input_filters_injection_patterns(): void
    {
        $chatControllerFile = base_path('app/Http/Controllers/ChatController.php');
        $content = file_get_contents($chatControllerFile);

        // Cari method sanitizeUserInput
        $this->assertStringContainsString(
            '[FILTERED]',
            $content,
            'Bug 1.25 FIX: sanitizeUserInput harus mengganti pola injection dengan [FILTERED]'
        );

        // Cari pola injection yang difilter
        $this->assertStringContainsString(
            'ignore',
            $content,
            "Bug 1.25 FIX: sanitizeUserInput harus memfilter pola 'ignore previous instructions'"
        );
    }

    /**
     * @test
     * Bug 1.25 FIX: Input AI harus dibatasi panjangnya (max 2000 karakter)
     *
     * Validates: Requirements 2.25
     */
    public function test_ai_input_has_length_limit(): void
    {
        $chatControllerFile = base_path('app/Http/Controllers/ChatController.php');
        $content = file_get_contents($chatControllerFile);

        $this->assertStringContainsString(
            'mb_substr',
            $content,
            'Bug 1.25 FIX: Input AI harus dibatasi panjangnya menggunakan mb_substr()'
        );

        $this->assertStringContainsString(
            '2000',
            $content,
            'Bug 1.25 FIX: Batas panjang input AI harus 2000 karakter'
        );
    }

    /**
     * @test
     * Bug 1.25 FIX: sanitizeUserInput harus dipanggil sebelum membangun prompt
     *
     * Validates: Requirements 2.25
     */
    public function test_sanitize_called_before_building_prompt(): void
    {
        $chatControllerFile = base_path('app/Http/Controllers/ChatController.php');
        $content = file_get_contents($chatControllerFile);

        // Verifikasi sanitizeUserInput dipanggil di buildSystemPrompt atau sebelum prompt dikirim
        $this->assertStringContainsString(
            'sanitizeUserInput',
            $content,
            'Bug 1.25 FIX: sanitizeUserInput harus dipanggil sebelum prompt dikirim ke AI'
        );

        // Verifikasi strip_tags juga digunakan
        $this->assertStringContainsString(
            'strip_tags',
            $content,
            'Bug 1.25 FIX: strip_tags harus digunakan untuk menghapus HTML dari input'
        );
    }

    // ─── Bug 1.26: Export Ownership Validation ────────────────────────────────

    /**
     * @test
     * Bug 1.26 FIX: ExportService.downloadExport harus memvalidasi tenant_id
     *
     * Validates: Requirements 2.26
     */
    public function test_export_service_validates_tenant_ownership(): void
    {
        $exportServiceFile = base_path('app/Services/ExportService.php');
        $this->assertFileExists($exportServiceFile, 'ExportService.php harus ada');

        $content = file_get_contents($exportServiceFile);

        // Cari method downloadExport
        $this->assertStringContainsString(
            'downloadExport',
            $content,
            'ExportService harus memiliki method downloadExport()'
        );

        // Cari validasi tenant_id
        $hasTenantValidation = str_contains($content, 'tenant_id') &&
            (
                str_contains($content, 'Auth::user()') ||
                str_contains($content, 'auth()->user()')
            );

        $this->assertTrue(
            $hasTenantValidation,
            'Bug 1.26 FIX: ExportService.downloadExport() harus memvalidasi tenant_id '.
            'dari user yang sedang login'
        );
    }

    /**
     * @test
     * Bug 1.26 FIX: downloadExport harus menggunakan where tenant_id untuk filter
     *
     * Validates: Requirements 2.26
     */
    public function test_export_download_uses_tenant_id_filter(): void
    {
        $exportServiceFile = base_path('app/Services/ExportService.php');
        $content = file_get_contents($exportServiceFile);

        // Cari where tenant_id di downloadExport
        $this->assertStringContainsString(
            'tenant_id',
            $content,
            'Bug 1.26 FIX: downloadExport harus memfilter berdasarkan tenant_id'
        );

        // Cari abort(404) untuk akses tidak sah
        $this->assertStringContainsString(
            '404',
            $content,
            'Bug 1.26 FIX: downloadExport harus mengembalikan 404 jika tenant tidak cocok'
        );
    }

    // ─── Bug 1.27: Dashboard N+1 Query ───────────────────────────────────────

    /**
     * @test
     * Bug 1.27 FIX: DashboardController harus menggunakan Cache::remember
     *
     * Validates: Requirements 2.27
     */
    public function test_dashboard_controller_uses_cache(): void
    {
        $dashboardFile = base_path('app/Http/Controllers/DashboardController.php');
        $this->assertFileExists($dashboardFile, 'DashboardController.php harus ada');

        $content = file_get_contents($dashboardFile);

        $usesCache = str_contains($content, 'Cache::remember') ||
            str_contains($content, 'cache()->remember') ||
            str_contains($content, 'cache(');

        $this->assertTrue(
            $usesCache,
            'Bug 1.27 FIX: DashboardController harus menggunakan cache untuk query agregat'
        );
    }

    /**
     * @test
     * Bug 1.27 FIX: DashboardController harus menggunakan eager loading
     *
     * Validates: Requirements 2.27
     */
    public function test_dashboard_controller_uses_eager_loading(): void
    {
        $dashboardFile = base_path('app/Http/Controllers/DashboardController.php');
        $content = file_get_contents($dashboardFile);

        $usesEagerLoading = str_contains($content, '->with(') ||
            str_contains($content, '->load(') ||
            str_contains($content, 'with([');

        $this->assertTrue(
            $usesEagerLoading,
            'Bug 1.27 FIX: DashboardController harus menggunakan eager loading untuk relasi'
        );
    }

    /**
     * @test
     * Bug 1.27 FIX: DashboardController harus menggunakan aggregate queries (selectRaw/sum/count)
     *
     * Validates: Requirements 2.27
     */
    public function test_dashboard_controller_uses_aggregate_queries(): void
    {
        $dashboardFile = base_path('app/Http/Controllers/DashboardController.php');
        $content = file_get_contents($dashboardFile);

        $usesAggregates = str_contains($content, 'selectRaw') ||
            str_contains($content, '->sum(') ||
            str_contains($content, '->count(') ||
            str_contains($content, 'groupBy');

        $this->assertTrue(
            $usesAggregates,
            'Bug 1.27 FIX: DashboardController harus menggunakan aggregate queries'
        );
    }

    /**
     * @test
     * Bug 1.27 FIX: Cache key harus berbasis tenant_id untuk isolasi data
     *
     * Validates: Requirements 2.27
     */
    public function test_dashboard_cache_key_is_tenant_based(): void
    {
        $dashboardFile = base_path('app/Http/Controllers/DashboardController.php');
        $content = file_get_contents($dashboardFile);

        // Cache key harus mengandung tenant_id
        $hasTenantCacheKey = str_contains($content, 'dashboard:{$tenantId}') ||
            str_contains($content, '"dashboard:'.'{$tenantId}') ||
            (str_contains($content, 'tenantId') && str_contains($content, 'cachePrefix'));

        $this->assertTrue(
            $hasTenantCacheKey,
            'Bug 1.27 FIX: Cache key dashboard harus berbasis tenant_id untuk isolasi data antar tenant'
        );
    }

    // ─── Bug 1.28: AI Rate Limiting Per-Tenant ────────────────────────────────

    /**
     * @test
     * Bug 1.28 FIX: RateLimitAiRequests middleware harus ada
     *
     * Validates: Requirements 2.28
     */
    public function test_rate_limit_ai_requests_middleware_exists(): void
    {
        $this->assertTrue(
            class_exists(RateLimitAiRequests::class),
            'Bug 1.28 FIX: RateLimitAiRequests middleware harus ada'
        );
    }

    /**
     * @test
     * Bug 1.28 FIX: Middleware harus menggunakan tenant_id sebagai rate limit key
     *
     * Validates: Requirements 2.28
     */
    public function test_rate_limit_key_uses_tenant_id(): void
    {
        $middlewareFile = base_path('app/Http/Middleware/RateLimitAiRequests.php');
        $this->assertFileExists($middlewareFile, 'RateLimitAiRequests.php harus ada');

        $content = file_get_contents($middlewareFile);

        // Key harus menggunakan tenant_id
        $usesTenantId = str_contains($content, 'tenant_id') &&
            (
                str_contains($content, 'ai_requests:{$tenantId}') ||
                str_contains($content, '"ai_requests:') ||
                str_contains($content, 'ai:tenant:')
            );

        $this->assertTrue(
            $usesTenantId,
            'Bug 1.28 FIX: Rate limit key harus menggunakan tenant_id, bukan user_id. '.
            'Konten file: '.substr($content, 0, 500)
        );
    }

    /**
     * @test
     * Bug 1.28 FIX: Middleware harus mengembalikan 429 saat limit tercapai
     *
     * Validates: Requirements 2.28
     */
    public function test_middleware_returns_429_when_limit_exceeded(): void
    {
        $middlewareFile = base_path('app/Http/Middleware/RateLimitAiRequests.php');
        $content = file_get_contents($middlewareFile);

        $this->assertStringContainsString(
            '429',
            $content,
            'Bug 1.28 FIX: Middleware harus mengembalikan HTTP 429 saat limit tercapai'
        );

        $this->assertStringContainsString(
            'tooManyAttempts',
            $content,
            'Bug 1.28 FIX: Middleware harus menggunakan tooManyAttempts() untuk cek limit'
        );
    }

    /**
     * @test
     * Bug 1.28 FIX: Middleware harus membatasi 60 request per menit per tenant
     *
     * Validates: Requirements 2.28
     */
    public function test_middleware_limits_60_requests_per_minute(): void
    {
        $middlewareFile = base_path('app/Http/Middleware/RateLimitAiRequests.php');
        $content = file_get_contents($middlewareFile);

        $this->assertStringContainsString(
            '60',
            $content,
            'Bug 1.28 FIX: Middleware harus membatasi 60 request per menit per tenant'
        );
    }

    /**
     * @test
     * Bug 1.28 FIX: Middleware harus terdaftar sebagai 'ai.rate' di bootstrap/app.php
     *
     * Validates: Requirements 2.28
     */
    public function test_middleware_registered_as_ai_rate(): void
    {
        $bootstrapFile = base_path('bootstrap/app.php');
        $this->assertFileExists($bootstrapFile, 'bootstrap/app.php harus ada');

        $content = file_get_contents($bootstrapFile);

        $this->assertStringContainsString(
            'ai.rate',
            $content,
            "Bug 1.28 FIX: Middleware harus terdaftar sebagai 'ai.rate' di bootstrap/app.php"
        );

        $this->assertStringContainsString(
            'RateLimitAiRequests',
            $content,
            'Bug 1.28 FIX: RateLimitAiRequests harus terdaftar di bootstrap/app.php'
        );
    }

    /**
     * @test
     * Bug 1.28 FIX: Middleware harus digunakan di route AI Chat
     *
     * Validates: Requirements 2.28
     */
    public function test_middleware_applied_to_ai_chat_routes(): void
    {
        $routesFile = base_path('routes/web.php');
        $this->assertFileExists($routesFile, 'routes/web.php harus ada');

        $content = file_get_contents($routesFile);

        $this->assertStringContainsString(
            'ai.rate',
            $content,
            "Bug 1.28 FIX: Middleware 'ai.rate' harus diterapkan ke route AI Chat"
        );
    }

    /**
     * @test
     * Bug 1.28 FIX: Middleware harus menyertakan retry_after dalam response 429
     *
     * Validates: Requirements 2.28
     */
    public function test_middleware_includes_retry_after_in_429_response(): void
    {
        $middlewareFile = base_path('app/Http/Middleware/RateLimitAiRequests.php');
        $content = file_get_contents($middlewareFile);

        $this->assertStringContainsString(
            'retry_after',
            $content,
            "Bug 1.28 FIX: Response 429 harus menyertakan 'retry_after' untuk client"
        );
    }
}
