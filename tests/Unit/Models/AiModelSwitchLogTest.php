<?php

namespace Tests\Unit\Models;

use App\Models\AiModelSwitchLog;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests for AiModelSwitchLog model scopes.
 *
 * Requirements: 5.1, 5.2, 5.4, 5.5
 */
class AiModelSwitchLogTest extends TestCase
{
    private function makeLog(array $attrs = []): AiModelSwitchLog
    {
        return AiModelSwitchLog::create(array_merge([
            'from_model' => 'gemini-1.5-pro',
            'to_model' => 'gemini-1.5-flash',
            'reason' => 'rate_limit',
            'switched_at' => now(),
        ], $attrs));
    }

    // ── scopeRecent ───────────────────────────────────────────────

    #[Test]
    public function recent_scope_returns_records_within_default_7_days(): void
    {
        $within = $this->makeLog(['switched_at' => now()->subDays(3)]);
        $outside = $this->makeLog(['switched_at' => now()->subDays(10)]);

        $results = AiModelSwitchLog::recent()->pluck('id');

        $this->assertContains($within->id, $results);
        $this->assertNotContains($outside->id, $results);
    }

    #[Test]
    public function recent_scope_respects_custom_days_parameter(): void
    {
        $within = $this->makeLog(['switched_at' => now()->subDays(25)]);
        $outside = $this->makeLog(['switched_at' => now()->subDays(35)]);

        $results = AiModelSwitchLog::recent(30)->pluck('id');

        $this->assertContains($within->id, $results);
        $this->assertNotContains($outside->id, $results);
    }

    #[Test]
    public function recent_scope_includes_record_exactly_at_boundary(): void
    {
        // Record exactly at the boundary (7 days ago) should be included
        $boundary = $this->makeLog(['switched_at' => now()->subDays(7)->startOfSecond()]);

        $results = AiModelSwitchLog::recent(7)->pluck('id');

        $this->assertContains($boundary->id, $results);
    }

    #[Test]
    public function recent_scope_returns_empty_when_no_records_in_range(): void
    {
        $this->makeLog(['switched_at' => now()->subDays(20)]);

        $results = AiModelSwitchLog::recent(7)->get();

        $this->assertEmpty($results);
    }

    // ── scopeByReason ─────────────────────────────────────────────

    #[Test]
    public function by_reason_scope_filters_by_rate_limit(): void
    {
        $rateLimit = $this->makeLog(['reason' => 'rate_limit']);
        $quota = $this->makeLog(['reason' => 'quota_exceeded']);

        $results = AiModelSwitchLog::byReason('rate_limit')->pluck('id');

        $this->assertContains($rateLimit->id, $results);
        $this->assertNotContains($quota->id, $results);
    }

    #[Test]
    public function by_reason_scope_filters_by_quota_exceeded(): void
    {
        $quota = $this->makeLog(['reason' => 'quota_exceeded']);
        $recovery = $this->makeLog(['reason' => 'recovery']);

        $results = AiModelSwitchLog::byReason('quota_exceeded')->pluck('id');

        $this->assertContains($quota->id, $results);
        $this->assertNotContains($recovery->id, $results);
    }

    #[Test]
    public function by_reason_scope_filters_by_service_unavailable(): void
    {
        $unavailable = $this->makeLog(['reason' => 'service_unavailable']);
        $rateLimit = $this->makeLog(['reason' => 'rate_limit']);

        $results = AiModelSwitchLog::byReason('service_unavailable')->pluck('id');

        $this->assertContains($unavailable->id, $results);
        $this->assertNotContains($rateLimit->id, $results);
    }

    #[Test]
    public function by_reason_scope_filters_by_recovery(): void
    {
        $recovery = $this->makeLog(['reason' => 'recovery']);
        $rateLimit = $this->makeLog(['reason' => 'rate_limit']);

        $results = AiModelSwitchLog::byReason('recovery')->pluck('id');

        $this->assertContains($recovery->id, $results);
        $this->assertNotContains($rateLimit->id, $results);
    }

    #[Test]
    public function by_reason_scope_returns_empty_when_no_match(): void
    {
        $this->makeLog(['reason' => 'rate_limit']);

        $results = AiModelSwitchLog::byReason('recovery')->get();

        $this->assertEmpty($results);
    }

    // ── Scope chaining ────────────────────────────────────────────

    #[Test]
    public function scopes_can_be_chained_together(): void
    {
        $match = $this->makeLog(['reason' => 'rate_limit', 'switched_at' => now()->subDays(2)]);
        $oldMatch = $this->makeLog(['reason' => 'rate_limit', 'switched_at' => now()->subDays(10)]);
        $recent = $this->makeLog(['reason' => 'quota_exceeded', 'switched_at' => now()->subDays(2)]);

        $results = AiModelSwitchLog::recent(7)->byReason('rate_limit')->pluck('id');

        $this->assertContains($match->id, $results);
        $this->assertNotContains($oldMatch->id, $results);
        $this->assertNotContains($recent->id, $results);
    }

    // ── Cast verification ─────────────────────────────────────────

    #[Test]
    public function switched_at_is_cast_to_carbon_instance(): void
    {
        $log = $this->makeLog(['switched_at' => '2026-01-15 10:00:00']);

        $this->assertInstanceOf(Carbon::class, $log->switched_at);
        $this->assertEquals('2026-01-15', $log->switched_at->toDateString());
    }
}
