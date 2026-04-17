<?php

namespace Tests\Feature\Jobs;

use App\Jobs\LogModelSwitchJob;
use App\Models\AiModelSwitchLog;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Tests\TestCase;

/**
 * Property-Based Tests for LogModelSwitchJob — Tenant ID recording.
 *
 * Feature: gemini-model-auto-switching
 * Property 10: Tenant ID recorded in switch log
 *
 * Validates: Requirements 9.3
 */
class LogModelSwitchJobTenantPropertyTest extends TestCase
{
    use TestTrait;

    /** @var array<string> */
    private array $validReasons = [
        'rate_limit',
        'quota_exceeded',
        'service_unavailable',
        'recovery',
    ];

    /** @var array<string> */
    private array $modelNames = [
        'gemini-2.5-flash',
        'gemini-2.5-flash-lite',
        'gemini-1.5-flash',
        'gemini-2.5-pro',
        'gemini-1.5-pro',
    ];

    // =========================================================================
    // Property 10: Tenant ID recorded in switch log
    //
    // For any Switch_Event triggered by a request that carries a tenant context,
    // the corresponding ai_model_switch_logs record must contain the correct
    // triggered_by_tenant_id. For requests without tenant context (e.g.,
    // system-level calls), the field may be null.
    //
    // Feature: gemini-model-auto-switching, Property 10: Tenant ID recorded in switch log
    // Validates: Requirements 9.3
    // =========================================================================

    /**
     * P10 — positive integer tenant IDs are stored exactly as dispatched.
     *
     * **Validates: Requirements 9.3**
     */
    #[ErisRepeat(repeat: 100)]
    public function testTenantIdStoredCorrectlyForTenantRequests(): void
    {
        $this
            ->forAll(
                Generators::elements($this->modelNames),
                Generators::elements($this->modelNames),
                Generators::elements($this->validReasons),
                // triggeredByTenantId: positive integer (1 – 999999)
                Generators::choose(1, 999999)
            )
            ->when(
                fn(string $from, string $to) => $from !== $to
            )
            ->then(function (
                string $fromModel,
                string $toModel,
                string $reason,
                int $tenantId
            ) {
                $countBefore = AiModelSwitchLog::count();

                // Dispatch the job — QUEUE_CONNECTION=sync in .env.testing
                // so the job runs immediately (synchronously)
                LogModelSwitchJob::dispatch(
                    fromModel: $fromModel,
                    toModel: $toModel,
                    reason: $reason,
                    errorMessage: null,
                    requestContext: null,
                    triggeredByTenantId: $tenantId,
                );

                $countAfter = AiModelSwitchLog::count();

                // ── Exactly one new record inserted ──
                $this->assertSame(
                    $countBefore + 1,
                    $countAfter,
                    "LogModelSwitchJob must insert exactly one record. " .
                    "from={$fromModel}, to={$toModel}, tenantId={$tenantId}"
                );

                // ── triggered_by_tenant_id matches exactly ──
                $log = AiModelSwitchLog::latest('id')->first();

                $this->assertSame(
                    $tenantId,
                    $log->triggered_by_tenant_id,
                    "triggered_by_tenant_id in DB must match the dispatched tenant ID. " .
                    "Expected {$tenantId}, got {$log->triggered_by_tenant_id}."
                );

                $this->assertDatabaseHas('ai_model_switch_logs', [
                    'from_model'             => $fromModel,
                    'to_model'               => $toModel,
                    'reason'                 => $reason,
                    'triggered_by_tenant_id' => $tenantId,
                ]);
            });
    }

    /**
     * P10 — null tenant ID (system-level calls) is stored as null.
     *
     * **Validates: Requirements 9.3**
     */
    #[ErisRepeat(repeat: 100)]
    public function testNullTenantIdStoredCorrectlyForSystemRequests(): void
    {
        $this
            ->forAll(
                Generators::elements($this->modelNames),
                Generators::elements($this->modelNames),
                Generators::elements($this->validReasons)
            )
            ->when(
                fn(string $from, string $to) => $from !== $to
            )
            ->then(function (
                string $fromModel,
                string $toModel,
                string $reason
            ) {
                $countBefore = AiModelSwitchLog::count();

                LogModelSwitchJob::dispatch(
                    fromModel: $fromModel,
                    toModel: $toModel,
                    reason: $reason,
                    errorMessage: null,
                    requestContext: null,
                    triggeredByTenantId: null,
                );

                $countAfter = AiModelSwitchLog::count();

                // ── Exactly one new record inserted ──
                $this->assertSame(
                    $countBefore + 1,
                    $countAfter,
                    "LogModelSwitchJob must insert exactly one record for system-level calls."
                );

                // ── triggered_by_tenant_id is null ──
                $log = AiModelSwitchLog::latest('id')->first();

                $this->assertNull(
                    $log->triggered_by_tenant_id,
                    "triggered_by_tenant_id must be null for system-level calls (no tenant context)."
                );
            });
    }

    /**
     * P10 — mixed tenant IDs (int and null) are each stored exactly as dispatched.
     *
     * Generates a mix of positive integers and null values to verify the field
     * is persisted correctly regardless of whether a tenant context is present.
     *
     * **Validates: Requirements 9.3**
     */
    #[ErisRepeat(repeat: 100)]
    public function testMixedTenantIdStoredCorrectly(): void
    {
        $this
            ->forAll(
                Generators::elements($this->modelNames),
                Generators::elements($this->modelNames),
                Generators::elements($this->validReasons),
                // triggeredByTenantId: positive integer or null
                Generators::oneOf(
                    Generators::constant(null),
                    Generators::choose(1, 999999)
                )
            )
            ->when(
                fn(string $from, string $to) => $from !== $to
            )
            ->then(function (
                string $fromModel,
                string $toModel,
                string $reason,
                ?int $tenantId
            ) {
                $countBefore = AiModelSwitchLog::count();

                LogModelSwitchJob::dispatch(
                    fromModel: $fromModel,
                    toModel: $toModel,
                    reason: $reason,
                    errorMessage: null,
                    requestContext: null,
                    triggeredByTenantId: $tenantId,
                );

                $countAfter = AiModelSwitchLog::count();

                // ── Exactly one new record inserted ──
                $this->assertSame(
                    $countBefore + 1,
                    $countAfter,
                    "LogModelSwitchJob must insert exactly one record. tenantId=" . var_export($tenantId, true)
                );

                // ── triggered_by_tenant_id stored exactly as dispatched ──
                $log = AiModelSwitchLog::latest('id')->first();

                $this->assertSame(
                    $tenantId,
                    $log->triggered_by_tenant_id,
                    "triggered_by_tenant_id must be stored exactly as dispatched. " .
                    "Expected " . var_export($tenantId, true) . ", got " . var_export($log->triggered_by_tenant_id, true) . "."
                );
            });
    }
}
