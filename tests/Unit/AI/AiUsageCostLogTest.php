<?php

namespace Tests\Unit\AI;

use App\Models\AiUsageCostLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests untuk model AiUsageCostLog.
 *
 * Requirements: 6.2, 6.3
 *
 * Catatan: Model menggunakan BelongsToTenant global scope, sehingga
 * test data dibuat via DB::table() untuk menghindari konflik dengan
 * global scope. Tenant dibuat via createTenant() helper agar FK
 * constraint terpenuhi.
 *
 * Menggunakan DatabaseTransactions (diwarisi dari TestCase) agar setiap
 * test berjalan dalam transaksi yang di-rollback setelah selesai.
 */
class AiUsageCostLogTest extends TestCase
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
     * Buat cost log langsung ke database, melewati global scope BelongsToTenant.
     */
    private function createLog(array $attrs = []): AiUsageCostLog
    {
        $tenantId = $attrs['tenant_id'] ?? $this->newTenantId();

        $defaults = [
            'tenant_id'          => $tenantId,
            'user_id'            => null,
            'use_case'           => 'chatbot',
            'provider'           => 'gemini',
            'model'              => 'gemini-2.5-flash',
            'input_tokens'       => 100,
            'output_tokens'      => 50,
            'estimated_cost_idr' => 0.0225,
            'response_time_ms'   => null,
            'fallback_degraded'  => false,
            'created_at'         => now(),
        ];

        $data = array_merge($defaults, $attrs);

        DB::table('ai_usage_cost_logs')->insert($data);

        return AiUsageCostLog::withoutGlobalScopes()
            ->where('tenant_id', $data['tenant_id'])
            ->where('use_case', $data['use_case'])
            ->latest('id')
            ->first();
    }

    // ── record() — immutable (tidak ada updated_at) ───────────────

    #[Test]
    public function record_creates_immutable_log_without_updated_at(): void
    {
        // Requirements: 6.2
        // AiUsageCostLog adalah immutable log — tidak boleh ada kolom updated_at
        $tenantId = $this->newTenantId();

        $log = AiUsageCostLog::record([
            'tenant_id'          => $tenantId,
            'use_case'           => 'chatbot',
            'provider'           => 'gemini',
            'model'              => 'gemini-2.5-flash',
            'input_tokens'       => 100,
            'output_tokens'      => 50,
            'estimated_cost_idr' => 0.0225,
        ]);

        // Model tidak memiliki updated_at
        $this->assertFalse($log->timestamps);
        $this->assertNull($log->updated_at ?? null);

        // Pastikan record tersimpan di database tanpa kolom updated_at
        $raw = DB::table('ai_usage_cost_logs')->where('id', $log->id)->first();
        $this->assertObjectNotHasProperty('updated_at', $raw);
    }

    #[Test]
    public function record_stores_all_fields_correctly(): void
    {
        // Requirements: 6.2, 6.3
        $tenantId = $this->newTenantId();

        $log = AiUsageCostLog::record([
            'tenant_id'          => $tenantId,
            'user_id'            => null,
            'use_case'           => 'financial_report',
            'provider'           => 'anthropic',
            'model'              => 'claude-3-5-sonnet-20241022',
            'input_tokens'       => 500,
            'output_tokens'      => 300,
            'estimated_cost_idr' => 2.0000,
            'response_time_ms'   => 1500,
            'fallback_degraded'  => false,
        ]);

        $fresh = AiUsageCostLog::withoutGlobalScopes()->find($log->id);

        $this->assertSame($tenantId, $fresh->tenant_id);
        $this->assertNull($fresh->user_id);
        $this->assertSame('financial_report', $fresh->use_case);
        $this->assertSame('anthropic', $fresh->provider);
        $this->assertSame('claude-3-5-sonnet-20241022', $fresh->model);
        $this->assertSame(500, $fresh->input_tokens);
        $this->assertSame(300, $fresh->output_tokens);
        $this->assertSame(2.0, $fresh->estimated_cost_idr);
        $this->assertSame(1500, $fresh->response_time_ms);
        $this->assertFalse($fresh->fallback_degraded);
    }

    #[Test]
    public function record_stores_created_at_when_provided(): void
    {
        // Requirements: 6.2
        // Model menggunakan $timestamps = false — created_at harus diberikan secara eksplisit.
        // Ini adalah pola yang sama dengan AiProviderSwitchLog.
        $tenantId  = $this->newTenantId();
        $timestamp = now()->startOfSecond();

        $log = AiUsageCostLog::record([
            'tenant_id'  => $tenantId,
            'use_case'   => 'chatbot',
            'provider'   => 'gemini',
            'model'      => 'gemini-2.5-flash',
            'created_at' => $timestamp,
        ]);

        $fresh = AiUsageCostLog::withoutGlobalScopes()->find($log->id);

        $this->assertNotNull($fresh->created_at);
        $this->assertTrue($fresh->created_at->eq($timestamp));
    }

    // ── scopeInDateRange() ────────────────────────────────────────

    #[Test]
    public function in_date_range_scope_returns_records_within_range(): void
    {
        // Requirements: 6.3
        $tenantId = $this->newTenantId();

        $inside = $this->createLog([
            'tenant_id'  => $tenantId,
            'use_case'   => 'chatbot',
            'created_at' => '2025-06-15 12:00:00',
        ]);

        $results = AiUsageCostLog::withoutGlobalScopes()
            ->inDateRange(Carbon::parse('2025-06-01'), Carbon::parse('2025-06-30'))
            ->pluck('id');

        $this->assertContains($inside->id, $results);
    }

    #[Test]
    public function in_date_range_scope_excludes_records_outside_range(): void
    {
        // Requirements: 6.3
        $tenantId = $this->newTenantId();

        $before = $this->createLog([
            'tenant_id'  => $tenantId,
            'use_case'   => 'chatbot',
            'created_at' => '2025-05-31 23:59:59',
        ]);

        $after = $this->createLog([
            'tenant_id'  => $tenantId,
            'use_case'   => 'crud_ai',
            'created_at' => '2025-07-01 00:00:00',
        ]);

        $results = AiUsageCostLog::withoutGlobalScopes()
            ->inDateRange(Carbon::parse('2025-06-01'), Carbon::parse('2025-06-30'))
            ->pluck('id');

        $this->assertNotContains($before->id, $results);
        $this->assertNotContains($after->id, $results);
    }

    #[Test]
    public function in_date_range_scope_includes_records_on_boundary_dates(): void
    {
        // Requirements: 6.3
        // Batas awal (startOfDay) dan batas akhir (endOfDay) harus inklusif
        $tenantId = $this->newTenantId();

        $onStart = $this->createLog([
            'tenant_id'  => $tenantId,
            'use_case'   => 'chatbot',
            'created_at' => '2025-06-01 00:00:00',
        ]);

        $onEnd = $this->createLog([
            'tenant_id'  => $tenantId,
            'use_case'   => 'crud_ai',
            'created_at' => '2025-06-30 23:59:59',
        ]);

        $results = AiUsageCostLog::withoutGlobalScopes()
            ->inDateRange(Carbon::parse('2025-06-01'), Carbon::parse('2025-06-30'))
            ->pluck('id');

        $this->assertContains($onStart->id, $results);
        $this->assertContains($onEnd->id, $results);
    }

    // ── scopeForUseCase() ─────────────────────────────────────────

    #[Test]
    public function for_use_case_scope_filters_by_use_case(): void
    {
        // Requirements: 6.3
        $tenantId = $this->newTenantId();

        $chatbot  = $this->createLog(['tenant_id' => $tenantId, 'use_case' => 'chatbot']);
        $report   = $this->createLog(['tenant_id' => $tenantId, 'use_case' => 'financial_report']);
        $forecast = $this->createLog(['tenant_id' => $tenantId, 'use_case' => 'forecasting']);

        $results = AiUsageCostLog::withoutGlobalScopes()
            ->forUseCase('chatbot')
            ->pluck('id');

        $this->assertContains($chatbot->id, $results);
        $this->assertNotContains($report->id, $results);
        $this->assertNotContains($forecast->id, $results);
    }

    #[Test]
    public function for_use_case_scope_returns_empty_when_no_match(): void
    {
        // Requirements: 6.3
        $tenantId = $this->newTenantId();
        $this->createLog(['tenant_id' => $tenantId, 'use_case' => 'chatbot']);

        $results = AiUsageCostLog::withoutGlobalScopes()
            ->forUseCase('unknown_use_case')
            ->get();

        $this->assertEmpty($results);
    }

    // ── Cast: fallback_degraded sebagai boolean ───────────────────

    #[Test]
    public function fallback_degraded_is_cast_as_boolean_true(): void
    {
        // Requirements: 6.2
        $log = $this->createLog(['fallback_degraded' => true]);

        $fresh = AiUsageCostLog::withoutGlobalScopes()->find($log->id);

        $this->assertIsBool($fresh->fallback_degraded);
        $this->assertTrue($fresh->fallback_degraded);
    }

    #[Test]
    public function fallback_degraded_is_cast_as_boolean_false(): void
    {
        // Requirements: 6.2
        $log = $this->createLog(['fallback_degraded' => false]);

        $fresh = AiUsageCostLog::withoutGlobalScopes()->find($log->id);

        $this->assertIsBool($fresh->fallback_degraded);
        $this->assertFalse($fresh->fallback_degraded);
    }

    // ── Cast: estimated_cost_idr sebagai float ────────────────────

    #[Test]
    public function estimated_cost_idr_is_cast_as_float(): void
    {
        // Requirements: 6.2
        $log = $this->createLog(['estimated_cost_idr' => 1.2500]);

        $fresh = AiUsageCostLog::withoutGlobalScopes()->find($log->id);

        $this->assertIsFloat($fresh->estimated_cost_idr);
        $this->assertSame(1.25, $fresh->estimated_cost_idr);
    }

    #[Test]
    public function estimated_cost_idr_is_zero_by_default(): void
    {
        // Requirements: 6.2
        $tenantId = $this->newTenantId();

        $log = AiUsageCostLog::record([
            'tenant_id' => $tenantId,
            'use_case'  => 'chatbot',
            'provider'  => 'gemini',
            'model'     => 'gemini-2.5-flash',
        ]);

        $fresh = AiUsageCostLog::withoutGlobalScopes()->find($log->id);

        $this->assertIsFloat($fresh->estimated_cost_idr);
        $this->assertSame(0.0, $fresh->estimated_cost_idr);
    }

    // ── Scope chaining ────────────────────────────────────────────

    #[Test]
    public function scopes_can_be_chained(): void
    {
        // Requirements: 6.3
        // inDateRange() + forUseCase() dapat dirantai
        $tenantId = $this->newTenantId();

        $match = $this->createLog([
            'tenant_id'  => $tenantId,
            'use_case'   => 'chatbot',
            'created_at' => '2025-06-15 10:00:00',
        ]);

        $wrongUseCase = $this->createLog([
            'tenant_id'  => $tenantId,
            'use_case'   => 'financial_report',
            'created_at' => '2025-06-15 10:00:00',
        ]);

        $outOfRange = $this->createLog([
            'tenant_id'  => $tenantId,
            'use_case'   => 'chatbot',
            'created_at' => '2025-07-01 10:00:00',
        ]);

        $results = AiUsageCostLog::withoutGlobalScopes()
            ->inDateRange(Carbon::parse('2025-06-01'), Carbon::parse('2025-06-30'))
            ->forUseCase('chatbot')
            ->pluck('id');

        $this->assertContains($match->id, $results);
        $this->assertNotContains($wrongUseCase->id, $results);
        $this->assertNotContains($outOfRange->id, $results);
    }
}
