<?php

namespace Tests\Unit\AI;

use App\Models\AiUseCaseRoute;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests untuk model AiUseCaseRoute.
 *
 * Requirements: 1.2, 1.7
 *
 * Catatan: Model menggunakan BelongsToTenant global scope, sehingga
 * test data dibuat via DB::table() untuk menghindari konflik dengan
 * global scope. Tenant dibuat via createTenant() helper agar FK
 * constraint terpenuhi.
 *
 * Menggunakan DatabaseTransactions (diwarisi dari TestCase) agar setiap
 * test berjalan dalam transaksi yang di-rollback setelah selesai.
 */
class AiUseCaseRouteTest extends TestCase
{
    // ── Helpers ───────────────────────────────────────────────────

    /**
     * Buat tenant baru dan kembalikan ID-nya.
     */
    private function newTenantId(): int
    {
        return $this->createTenant()->id;
    }

    /**
     * Buat routing rule langsung ke database, melewati global scope BelongsToTenant.
     * Gunakan tenant_id yang sudah ada (dari newTenantId()) untuk non-null tenant_id.
     */
    private function createRoute(array $attrs = []): AiUseCaseRoute
    {
        $defaults = [
            'tenant_id'      => null,
            'use_case'       => 'chatbot',
            'provider'       => 'gemini',
            'model'          => 'gemini-2.5-flash',
            'min_plan'       => null,
            'fallback_chain' => null,
            'is_active'      => true,
            'description'    => null,
        ];

        $data = array_merge($defaults, $attrs);

        DB::table('ai_use_case_routes')->insert(array_merge($data, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return AiUseCaseRoute::withoutGlobalScopes()
            ->where('use_case', $data['use_case'])
            ->where(function ($q) use ($data) {
                if ($data['tenant_id'] === null) {
                    $q->whereNull('tenant_id');
                } else {
                    $q->where('tenant_id', $data['tenant_id']);
                }
            })
            ->latest('id')
            ->first();
    }

    // ── scopeActive() ─────────────────────────────────────────────

    #[Test]
    public function active_scope_returns_only_active_records(): void
    {
        // Requirements: 1.2
        $active   = $this->createRoute(['use_case' => 'chatbot', 'is_active' => true]);
        $inactive = $this->createRoute(['use_case' => 'crud_ai', 'is_active' => false]);

        $results = AiUseCaseRoute::withoutGlobalScopes()->active()->pluck('id');

        $this->assertContains($active->id, $results);
        $this->assertNotContains($inactive->id, $results);
    }

    #[Test]
    public function active_scope_excludes_all_inactive_records(): void
    {
        // Requirements: 1.2
        $this->createRoute(['use_case' => 'chatbot', 'is_active' => false]);
        $this->createRoute(['use_case' => 'crud_ai', 'is_active' => false]);

        $results = AiUseCaseRoute::withoutGlobalScopes()->active()->get();

        $this->assertEmpty($results);
    }

    // ── scopeGlobalRules() ────────────────────────────────────────

    #[Test]
    public function global_rules_scope_returns_only_records_with_null_tenant_id(): void
    {
        // Requirements: 1.2, 1.7
        $tenantId = $this->newTenantId();

        $global = $this->createRoute(['use_case' => 'chatbot', 'tenant_id' => null]);
        $tenant = $this->createRoute(['use_case' => 'chatbot', 'tenant_id' => $tenantId]);

        $results = AiUseCaseRoute::globalRules()->pluck('id');

        $this->assertContains($global->id, $results);
        $this->assertNotContains($tenant->id, $results);
    }

    #[Test]
    public function global_rules_scope_returns_empty_when_no_global_rules_exist(): void
    {
        // Requirements: 1.7
        $tenantIdA = $this->newTenantId();
        $tenantIdB = $this->newTenantId();

        $this->createRoute(['use_case' => 'chatbot', 'tenant_id' => $tenantIdA]);
        $this->createRoute(['use_case' => 'crud_ai', 'tenant_id' => $tenantIdB]);

        $results = AiUseCaseRoute::globalRules()->get();

        $this->assertEmpty($results);
    }

    // ── scopeTenantRules() ────────────────────────────────────────

    #[Test]
    public function tenant_rules_scope_returns_only_records_for_given_tenant(): void
    {
        // Requirements: 1.2, 1.7
        $tenantIdA = $this->newTenantId();
        $tenantIdB = $this->newTenantId();

        $tenantA = $this->createRoute(['use_case' => 'chatbot', 'tenant_id' => $tenantIdA]);
        $tenantB = $this->createRoute(['use_case' => 'chatbot', 'tenant_id' => $tenantIdB]);
        $global  = $this->createRoute(['use_case' => 'crud_ai', 'tenant_id' => null]);

        $results = AiUseCaseRoute::tenantRules($tenantIdA)->pluck('id');

        $this->assertContains($tenantA->id, $results);
        $this->assertNotContains($tenantB->id, $results);
        $this->assertNotContains($global->id, $results);
    }

    #[Test]
    public function tenant_rules_scope_returns_empty_when_no_rules_for_tenant(): void
    {
        // Requirements: 1.7
        $existingTenantId = $this->newTenantId();
        $this->createRoute(['use_case' => 'chatbot', 'tenant_id' => $existingTenantId]);

        // Query untuk tenant yang tidak punya rule
        $results = AiUseCaseRoute::tenantRules($existingTenantId + 9999)->get();

        $this->assertEmpty($results);
    }

    #[Test]
    public function tenant_rules_scope_does_not_return_global_rules(): void
    {
        // Requirements: 1.7
        // Global rules (tenant_id = NULL) tidak boleh muncul di tenantRules()
        $this->createRoute(['use_case' => 'chatbot', 'tenant_id' => null]);

        $tenantId = $this->newTenantId();
        $results  = AiUseCaseRoute::tenantRules($tenantId)->get();

        $this->assertEmpty($results);
    }

    // ── scopeForUseCase() ─────────────────────────────────────────

    #[Test]
    public function for_use_case_scope_filters_by_use_case(): void
    {
        // Requirements: 1.2
        $chatbot  = $this->createRoute(['use_case' => 'chatbot',     'tenant_id' => null]);
        $crudAi   = $this->createRoute(['use_case' => 'crud_ai',     'tenant_id' => null]);
        $forecast = $this->createRoute(['use_case' => 'forecasting', 'tenant_id' => null]);

        $results = AiUseCaseRoute::withoutGlobalScopes()->forUseCase('chatbot')->pluck('id');

        $this->assertContains($chatbot->id, $results);
        $this->assertNotContains($crudAi->id, $results);
        $this->assertNotContains($forecast->id, $results);
    }

    #[Test]
    public function for_use_case_scope_returns_empty_when_no_match(): void
    {
        // Requirements: 1.2
        $this->createRoute(['use_case' => 'chatbot', 'tenant_id' => null]);

        $results = AiUseCaseRoute::withoutGlobalScopes()->forUseCase('financial_report')->get();

        $this->assertEmpty($results);
    }

    // ── tenant_id = NULL berarti global rule ──────────────────────

    #[Test]
    public function null_tenant_id_means_global_rule(): void
    {
        // Requirements: 1.7
        // Record dengan tenant_id = NULL adalah global rule yang berlaku untuk semua tenant
        $global = $this->createRoute(['use_case' => 'chatbot', 'tenant_id' => null]);

        $this->assertNull($global->tenant_id);

        // Harus muncul di globalRules()
        $globalResults = AiUseCaseRoute::globalRules()->pluck('id');
        $this->assertContains($global->id, $globalResults);

        // Tidak boleh muncul di tenantRules() untuk tenant manapun
        $tenantId      = $this->newTenantId();
        $tenantResults = AiUseCaseRoute::tenantRules($tenantId)->pluck('id');
        $this->assertNotContains($global->id, $tenantResults);
    }

    #[Test]
    public function non_null_tenant_id_means_tenant_specific_rule(): void
    {
        // Requirements: 1.7
        $tenantId   = $this->newTenantId();
        $tenantRule = $this->createRoute(['use_case' => 'chatbot', 'tenant_id' => $tenantId]);

        $this->assertSame($tenantId, $tenantRule->tenant_id);

        // Tidak boleh muncul di globalRules()
        $globalResults = AiUseCaseRoute::globalRules()->pluck('id');
        $this->assertNotContains($tenantRule->id, $globalResults);

        // Harus muncul di tenantRules($tenantId)
        $tenantResults = AiUseCaseRoute::tenantRules($tenantId)->pluck('id');
        $this->assertContains($tenantRule->id, $tenantResults);
    }

    // ── Cast: fallback_chain sebagai array ────────────────────────

    #[Test]
    public function fallback_chain_is_cast_as_array(): void
    {
        // Requirements: 1.2
        $route = $this->createRoute([
            'use_case'       => 'financial_report',
            'tenant_id'      => null,
            'fallback_chain' => json_encode(['gemini', 'anthropic']),
        ]);

        // Refresh dari database untuk memastikan cast berjalan
        $fresh = AiUseCaseRoute::withoutGlobalScopes()->find($route->id);

        $this->assertIsArray($fresh->fallback_chain);
        $this->assertSame(['gemini', 'anthropic'], $fresh->fallback_chain);
    }

    #[Test]
    public function fallback_chain_is_null_when_not_set(): void
    {
        // Requirements: 1.2
        $route = $this->createRoute([
            'use_case'       => 'chatbot',
            'tenant_id'      => null,
            'fallback_chain' => null,
        ]);

        $fresh = AiUseCaseRoute::withoutGlobalScopes()->find($route->id);

        $this->assertNull($fresh->fallback_chain);
    }

    #[Test]
    public function fallback_chain_preserves_order(): void
    {
        // Requirements: 1.2
        $chain = ['anthropic', 'gemini', 'openai'];
        $route = $this->createRoute([
            'use_case'       => 'forecasting',
            'tenant_id'      => null,
            'fallback_chain' => json_encode($chain),
        ]);

        $fresh = AiUseCaseRoute::withoutGlobalScopes()->find($route->id);

        $this->assertSame($chain, $fresh->fallback_chain);
    }

    // ── Cast: is_active sebagai boolean ───────────────────────────

    #[Test]
    public function is_active_is_cast_as_boolean_true(): void
    {
        // Requirements: 1.2
        $route = $this->createRoute(['use_case' => 'chatbot', 'is_active' => true]);

        $fresh = AiUseCaseRoute::withoutGlobalScopes()->find($route->id);

        $this->assertIsBool($fresh->is_active);
        $this->assertTrue($fresh->is_active);
    }

    #[Test]
    public function is_active_is_cast_as_boolean_false(): void
    {
        // Requirements: 1.2
        $route = $this->createRoute(['use_case' => 'crud_ai', 'is_active' => false]);

        $fresh = AiUseCaseRoute::withoutGlobalScopes()->find($route->id);

        $this->assertIsBool($fresh->is_active);
        $this->assertFalse($fresh->is_active);
    }

    // ── Scope chaining ────────────────────────────────────────────

    #[Test]
    public function scopes_can_be_chained(): void
    {
        // Requirements: 1.2
        // globalRules() + active() + forUseCase() dapat dirantai
        $tenantId = $this->newTenantId();

        $match    = $this->createRoute(['use_case' => 'chatbot', 'tenant_id' => null,      'is_active' => true]);
        $inactive = $this->createRoute(['use_case' => 'chatbot', 'tenant_id' => null,      'is_active' => false]);
        $tenant   = $this->createRoute(['use_case' => 'chatbot', 'tenant_id' => $tenantId, 'is_active' => true]);
        $other    = $this->createRoute(['use_case' => 'crud_ai', 'tenant_id' => null,      'is_active' => true]);

        $results = AiUseCaseRoute::globalRules()
            ->active()
            ->forUseCase('chatbot')
            ->pluck('id');

        $this->assertContains($match->id, $results);
        $this->assertNotContains($inactive->id, $results);
        $this->assertNotContains($tenant->id, $results);
        $this->assertNotContains($other->id, $results);
    }
}
