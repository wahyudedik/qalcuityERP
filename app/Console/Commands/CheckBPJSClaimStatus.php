<?php

namespace App\Console\Commands;

use App\Models\InsuranceClaim;
use App\Models\User;
use App\Notifications\Healthcare\BPJSClaimUpdate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckBPJSClaimStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'healthcare:bpjs:check-claims
                            {--tenant= : Specific tenant ID}
                            {--limit=100 : Maximum claims to check}
                            {--dry-run : Test mode without updating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update BPJS insurance claim status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');

        $this->info('🏥 Checking BPJS claim status...');

        if ($dryRun) {
            $this->warn('⚠️ DRY RUN MODE - No claims will be updated');
        }

        // Get pending BPJS claims
        $query = InsuranceClaim::where('insurance_provider', 'BPJS')
            ->whereIn('status', ['submitted', 'pending']);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $claims = $query->limit($limit)->get();

        if ($claims->isEmpty()) {
            $this->info('✅ No pending BPJS claims to check');

            return Command::SUCCESS;
        }

        $this->info("Found {$claims->count()} pending BPJS claims");

        $updatedCount = 0;
        $failedCount = 0;

        foreach ($claims as $claim) {
            try {
                $this->info("\nChecking claim: {$claim->claim_number}");

                // Query BPJS V-Claim API
                $bpjsStatus = $this->queryBPJSApi($claim);

                if (! $bpjsStatus) {
                    $this->warn('  ⚠️ No status update from BPJS');

                    continue;
                }

                if ($dryRun) {
                    $this->line("  Would update status: {$claim->status} → {$bpjsStatus['status']}");
                    $updatedCount++;

                    continue;
                }

                // Update claim status
                $oldStatus = $claim->status;
                $claim->update([
                    'status' => $bpjsStatus['status'],
                    'bpjs_response' => json_encode($bpjsStatus),
                    'last_checked_at' => now(),
                ]);

                if ($bpjsStatus['status'] === 'approved') {
                    $claim->update([
                        'approved_amount' => $bpjsStatus['approved_amount'] ?? $claim->claim_amount,
                        'approval_date' => now(),
                    ]);
                }

                $this->info("  ✓ Updated: {$oldStatus} → {$claim->status}");
                $updatedCount++;

                // Send notification if approved or rejected
                if (in_array($claim->status, ['approved', 'rejected'])) {
                    $this->notifyClaimUpdate($claim);
                }

            } catch (\Exception $e) {
                $failedCount++;
                $this->error("❌ Failed to check claim {$claim->claim_number}: {$e->getMessage()}");

                Log::error('BPJS claim check failed', [
                    'claim_id' => $claim->id,
                    'claim_number' => $claim->claim_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("\n📊 BPJS Claim Check Summary:");
        $this->line("   ✓ Updated: {$updatedCount}");
        $this->line("   ✗ Failed: {$failedCount}");
        $this->line("   Total: {$claims->count()}");

        return Command::SUCCESS;
    }

    /**
     * Query BPJS V-Claim API
     */
    protected function queryBPJSApi($claim): ?array
    {
        // BPJS Kesehatan V-Claim API integration
        // Documentation: https://dvlp.bpjs-kesehatan.go.id:8888/pcare-rest-api/

        $baseUrl = config('services.bpjs.base_url', 'https://api.bpjs-kesehatan.go.id');
        $consId = config('services.bpjs.cons_id');
        $secretKey = config('services.bpjs.secret_key');

        if (! $consId || ! $secretKey) {
            Log::warning('BPJS credentials not configured');

            return null;
        }

        try {
            // Generate security token (simplified - implement proper HMAC)
            $timestamp = time();
            $signature = hash_hmac('sha256', $consId.'&'.$timestamp, $secretKey, true);
            $encodedSignature = base64_encode($signature);

            // Query claim status
            $response = Http::withHeaders([
                'X-cons-id' => $consId,
                'X-timestamp' => (string) $timestamp,
                'X-signature' => $encodedSignature,
                'Accept' => 'application/json',
            ])->get("{$baseUrl}/vclaim-monitoring/claim/{$claim->claim_number}");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'status' => $this->mapBPJSStatus($data['status'] ?? 'pending'),
                    'approved_amount' => $data['setuju'] ?? null,
                    'response_data' => $data,
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('BPJS API request failed', [
                'claim_number' => $claim->claim_number,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Map BPJS status to internal status
     */
    protected function mapBPJSStatus(string $bpjsStatus): string
    {
        return match (strtolower($bpjsStatus)) {
            'approved', 'setuju' => 'approved',
            'rejected', 'tidak setuju' => 'rejected',
            'pending', 'proses' => 'pending',
            default => 'pending',
        };
    }

    /**
     * Notify about claim status update
     */
    protected function notifyClaimUpdate($claim): void
    {
        try {
            // Notify billing staff and admin
            $recipients = User::where('tenant_id', $claim->tenant_id)
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['admin', 'billing_staff']);
                })
                ->get();

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new BPJSClaimUpdate($claim));
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send BPJS claim notification', [
                'claim_id' => $claim->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
