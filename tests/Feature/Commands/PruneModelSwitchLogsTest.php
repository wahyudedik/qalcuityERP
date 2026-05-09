<?php

namespace Tests\Feature\Commands;

use App\Models\AiModelSwitchLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Unit tests for the PruneModelSwitchLogs artisan command.
 *
 * Feature: gemini-model-auto-switching
 * Validates: Requirements 5.4
 */
class PruneModelSwitchLogsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear SystemSetting cache so each test starts fresh
        Cache::forget(SystemSetting::CACHE_KEY);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Create an AiModelSwitchLog record with a specific switched_at age.
     */
    private function createLog(int $daysAgo): AiModelSwitchLog
    {
        return AiModelSwitchLog::create([
            'from_model' => 'gemini-2.5-flash',
            'to_model' => 'gemini-2.5-flash-lite',
            'reason' => 'rate_limit',
            'switched_at' => now()->subDays($daysAgo),
        ]);
    }

    // ── Tests ─────────────────────────────────────────────────────────────────

    /**
     * With default retention (30 days), records older than 30 days are deleted
     * and records 30 days old or newer are kept.
     *
     * Validates: Requirements 5.4
     */
    public function test_default_retention_deletes_old_records(): void
    {
        // Seed records at various ages
        $recent = $this->createLog(10);  // 10 days old — keep
        $boundary = $this->createLog(29);  // 29 days old — keep (within 30-day window)
        $old = $this->createLog(31);  // 31 days old — delete
        $veryOld = $this->createLog(60);  // 60 days old — delete

        $this->artisan('ai:prune-switch-logs')
            ->assertSuccessful();

        // Recent records must still exist
        $this->assertDatabaseHas('ai_model_switch_logs', ['id' => $recent->id]);
        $this->assertDatabaseHas('ai_model_switch_logs', ['id' => $boundary->id]);

        // Old records must be gone
        $this->assertDatabaseMissing('ai_model_switch_logs', ['id' => $old->id]);
        $this->assertDatabaseMissing('ai_model_switch_logs', ['id' => $veryOld->id]);
    }

    /**
     * When gemini_log_retention_days is set to a custom value via SystemSetting,
     * the command uses that value instead of the default 30 days.
     *
     * Validates: Requirements 5.4
     */
    public function test_custom_retention_via_system_setting(): void
    {
        // Set retention to 7 days
        SystemSetting::set('gemini_log_retention_days', '7');

        $recent = $this->createLog(5);   // 5 days old — keep
        $edge = $this->createLog(6);   // 6 days old — keep
        $old = $this->createLog(8);   // 8 days old — delete
        $veryOld = $this->createLog(30);  // 30 days old — delete

        $this->artisan('ai:prune-switch-logs')
            ->assertSuccessful();

        $this->assertDatabaseHas('ai_model_switch_logs', ['id' => $recent->id]);
        $this->assertDatabaseHas('ai_model_switch_logs', ['id' => $edge->id]);
        $this->assertDatabaseMissing('ai_model_switch_logs', ['id' => $old->id]);
        $this->assertDatabaseMissing('ai_model_switch_logs', ['id' => $veryOld->id]);
    }

    /**
     * The command outputs the correct count of deleted records.
     *
     * Validates: Requirements 5.4
     */
    public function test_output_shows_correct_deleted_count(): void
    {
        // 2 records older than 30 days
        $this->createLog(31);
        $this->createLog(45);

        // 1 record within retention window
        $this->createLog(10);

        $this->artisan('ai:prune-switch-logs')
            ->expectsOutputToContain('2')
            ->assertSuccessful();
    }

    /**
     * When there are no records to prune, the command outputs 0 deleted records.
     *
     * Validates: Requirements 5.4
     */
    public function test_output_shows_zero_when_nothing_to_delete(): void
    {
        $this->createLog(5);
        $this->createLog(10);

        $this->artisan('ai:prune-switch-logs')
            ->expectsOutputToContain('0')
            ->assertSuccessful();
    }

    /**
     * When the table is empty, the command runs without error and reports 0 deletions.
     *
     * Validates: Requirements 5.4
     */
    public function test_runs_cleanly_on_empty_table(): void
    {
        $this->assertSame(0, AiModelSwitchLog::count());

        $this->artisan('ai:prune-switch-logs')
            ->expectsOutputToContain('0')
            ->assertSuccessful();
    }

    /**
     * With a very short retention (1 day), only today's records survive.
     *
     * Validates: Requirements 5.4
     */
    public function test_short_retention_keeps_only_very_recent_records(): void
    {
        SystemSetting::set('gemini_log_retention_days', '1');

        $today = $this->createLog(0);  // created now — keep
        $yesterday = $this->createLog(2);  // 2 days old — delete

        $this->artisan('ai:prune-switch-logs')
            ->assertSuccessful();

        $this->assertDatabaseHas('ai_model_switch_logs', ['id' => $today->id]);
        $this->assertDatabaseMissing('ai_model_switch_logs', ['id' => $yesterday->id]);
    }

    /**
     * With a very long retention (365 days), no records are deleted.
     *
     * Validates: Requirements 5.4
     */
    public function test_long_retention_keeps_all_records(): void
    {
        SystemSetting::set('gemini_log_retention_days', '365');

        $log1 = $this->createLog(60);
        $log2 = $this->createLog(180);
        $log3 = $this->createLog(364);

        $this->artisan('ai:prune-switch-logs')
            ->expectsOutputToContain('0')
            ->assertSuccessful();

        $this->assertDatabaseHas('ai_model_switch_logs', ['id' => $log1->id]);
        $this->assertDatabaseHas('ai_model_switch_logs', ['id' => $log2->id]);
        $this->assertDatabaseHas('ai_model_switch_logs', ['id' => $log3->id]);
    }
}
