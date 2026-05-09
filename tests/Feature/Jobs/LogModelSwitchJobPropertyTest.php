<?php

namespace Tests\Feature\Jobs;

use App\Jobs\LogModelSwitchJob;
use App\Models\AiModelSwitchLog;
use Eris\Attributes\ErisRepeat;
use Eris\Generators;
use Eris\TestTrait;
use Tests\TestCase;

/**
 * Property-Based Tests for LogModelSwitchJob.
 *
 * Feature: gemini-model-auto-switching
 * Property 5: Switch log completeness
 *
 * Validates: Requirements 5.1, 5.3
 */
class LogModelSwitchJobPropertyTest extends TestCase
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
    // Property 5: Switch log completeness
    //
    // For any Switch_Event that is triggered, after the queued LogModelSwitchJob
    // is processed, the ai_model_switch_logs table must contain exactly one new
    // record with non-null from_model, to_model, reason, and switched_at fields
    // matching the event.
    //
    // Feature: gemini-model-auto-switching, Property 5: Switch log completeness
    // Validates: Requirements 5.1, 5.3
    // =========================================================================

    #[ErisRepeat(repeat: 100)]
    public function test_switch_log_completeness(): void
    {
        $this
            ->forAll(
                Generators::elements($this->modelNames),
                Generators::elements($this->modelNames),
                Generators::elements($this->validReasons)
            )
            ->when(
                // Ensure from_model and to_model are different (a real switch)
                fn (string $from, string $to, string $reason) => $from !== $to
            )
            ->then(function (string $fromModel, string $toModel, string $reason) {
                $countBefore = AiModelSwitchLog::count();

                // Dispatch the job — QUEUE_CONNECTION=sync in .env.testing
                // so the job runs immediately (synchronously)
                LogModelSwitchJob::dispatch(
                    fromModel: $fromModel,
                    toModel: $toModel,
                    reason: $reason,
                    errorMessage: null,
                    requestContext: null,
                    triggeredByTenantId: null,
                );

                $countAfter = AiModelSwitchLog::count();

                // ── Assert: exactly one new record was inserted ──
                $this->assertSame(
                    $countBefore + 1,
                    $countAfter,
                    'Dispatching LogModelSwitchJob must insert exactly one new record in ai_model_switch_logs. '.
                    "from={$fromModel}, to={$toModel}, reason={$reason}"
                );

                // ── Assert: the record has the correct field values ──
                $this->assertDatabaseHas('ai_model_switch_logs', [
                    'from_model' => $fromModel,
                    'to_model' => $toModel,
                    'reason' => $reason,
                ]);

                // ── Assert: switched_at is non-null ──
                $log = AiModelSwitchLog::latest('id')->first();

                $this->assertNotNull(
                    $log->switched_at,
                    'switched_at must be non-null after LogModelSwitchJob is processed.'
                );

                $this->assertSame(
                    $fromModel,
                    $log->from_model,
                    'from_model in DB must match the dispatched value.'
                );

                $this->assertSame(
                    $toModel,
                    $log->to_model,
                    'to_model in DB must match the dispatched value.'
                );

                $this->assertSame(
                    $reason,
                    $log->reason,
                    'reason in DB must match the dispatched value.'
                );
            });
    }

    // =========================================================================
    // Property 5 (with optional fields): Switch log completeness with error message
    //
    // When errorMessage and requestContext are provided, they must also be
    // persisted correctly alongside the required fields.
    //
    // Feature: gemini-model-auto-switching, Property 5: Switch log completeness
    // Validates: Requirements 5.1, 5.3
    // =========================================================================

    #[ErisRepeat(repeat: 100)]
    public function test_switch_log_completeness_with_optional_fields(): void
    {
        $this
            ->forAll(
                Generators::elements($this->modelNames),
                Generators::elements($this->modelNames),
                Generators::elements($this->validReasons),
                // errorMessage: short string or null
                Generators::oneOf(
                    Generators::constant(null),
                    Generators::map(
                        fn (int $len) => 'Error: '.str_repeat('x', $len),
                        Generators::choose(1, 30)
                    )
                ),
                // requestContext: module name or null
                Generators::oneOf(
                    Generators::constant(null),
                    Generators::elements(['chat', 'ai-advisor', 'ocr', 'insights'])
                )
            )
            ->when(
                fn (string $from, string $to) => $from !== $to
            )
            ->then(function (
                string $fromModel,
                string $toModel,
                string $reason,
                ?string $errorMessage,
                ?string $requestContext
            ) {
                $countBefore = AiModelSwitchLog::count();

                LogModelSwitchJob::dispatch(
                    fromModel: $fromModel,
                    toModel: $toModel,
                    reason: $reason,
                    errorMessage: $errorMessage,
                    requestContext: $requestContext,
                    triggeredByTenantId: null,
                );

                $countAfter = AiModelSwitchLog::count();

                // ── Exactly one new record ──
                $this->assertSame(
                    $countBefore + 1,
                    $countAfter,
                    'LogModelSwitchJob must insert exactly one record regardless of optional fields.'
                );

                $log = AiModelSwitchLog::latest('id')->first();

                // ── Required fields are non-null and correct ──
                $this->assertNotNull($log->from_model);
                $this->assertNotNull($log->to_model);
                $this->assertNotNull($log->reason);
                $this->assertNotNull($log->switched_at);

                $this->assertSame($fromModel, $log->from_model);
                $this->assertSame($toModel, $log->to_model);
                $this->assertSame($reason, $log->reason);

                // ── Optional fields stored as-is ──
                $this->assertSame(
                    $errorMessage,
                    $log->error_message,
                    'error_message must be stored exactly as dispatched (including null).'
                );

                $this->assertSame(
                    $requestContext,
                    $log->request_context,
                    'request_context must be stored exactly as dispatched (including null).'
                );
            });
    }
}
