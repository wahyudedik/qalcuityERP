<?php

namespace App\Console\Commands;

use App\Models\ApiToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredApiTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:cleanup-tokens 
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--older-than=30 : Delete tokens expired more than N days ago (default: 30)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired and inactive API tokens for security';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $olderThanDays = (int) $this->option('older-than');
        $cutoffDate = now()->subDays($olderThanDays);

        $this->info("🔍 Scanning for expired API tokens...");
        $this->info("📅 Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')} ({$olderThanDays} days ago)");
        $this->info("🔒 Dry run: " . ($dryRun ? 'YES' : 'NO'));
        $this->newLine();

        // Find expired tokens
        $expiredTokens = ApiToken::where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        // Find inactive tokens
        $inactiveTokens = ApiToken::where('is_active', false)
            ->where('updated_at', '<', $cutoffDate)
            ->get();

        $this->info("📊 Results:");
        $this->info("  • Expired tokens: {$expiredTokens->count()}");
        $this->info("  • Inactive tokens (older than {$olderThanDays} days): {$inactiveTokens->count()}");
        $this->newLine();

        if ($expiredTokens->isEmpty() && $inactiveTokens->isEmpty()) {
            $this->info("✅ No tokens to clean up.");
            return Command::SUCCESS;
        }

        // Show details
        if ($expiredTokens->isNotEmpty()) {
            $this->warn("⚠️  Expired Tokens:");
            $this->table(
                ['ID', 'Tenant', 'Name', 'Expired At', 'Last Used'],
                $expiredTokens->map(function ($token) {
                    return [
                        $token->id,
                        $token->tenant_id,
                        $token->name,
                        $token->expires_at->format('Y-m-d'),
                        $token->last_used_at?->diffForHumans() ?? 'Never',
                    ];
                })->toArray()
            );
        }

        if ($inactiveTokens->isNotEmpty()) {
            $this->warn("⚠️  Inactive Tokens:");
            $this->table(
                ['ID', 'Tenant', 'Name', 'Updated At'],
                $inactiveTokens->map(function ($token) {
                    return [
                        $token->id,
                        $token->tenant_id,
                        $token->name,
                        $token->updated_at->format('Y-m-d'),
                    ];
                })->toArray()
            );
        }

        // Delete if not dry run
        if (!$dryRun) {
            $expiredCount = $expiredTokens->count();
            $inactiveCount = $inactiveTokens->count();

            $expiredTokens->each->delete();
            $inactiveTokens->each->delete();

            $this->newLine();
            $this->info("✅ Cleanup completed:");
            $this->info("  • Deleted {$expiredCount} expired tokens");
            $this->info("  • Deleted {$inactiveCount} inactive tokens");
            $this->info("  • Total: " . ($expiredCount + $inactiveCount) . " tokens removed");

            Log::info('API tokens cleanup completed', [
                'expired_deleted' => $expiredCount,
                'inactive_deleted' => $inactiveCount,
                'total_deleted' => $expiredCount + $inactiveCount,
                'cutoff_date' => $cutoffDate->toISOString(),
            ]);
        } else {
            $this->newLine();
            $this->info("ℹ️  Dry run mode - no tokens were deleted.");
            $this->info("   Remove --dry-run flag to actually delete tokens.");
        }

        return Command::SUCCESS;
    }
}
