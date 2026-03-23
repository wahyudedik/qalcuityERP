<?php

namespace App\Services;

use App\Models\BankStatement;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BankReconciliationAiService
{
    // Tolerances
    private const AMOUNT_TOLERANCE_PCT  = 0.01;  // 1% amount difference allowed
    private const DATE_WINDOW_DAYS      = 5;      // ±5 days date window
    private const HIGH_CONFIDENCE       = 85;
    private const MEDIUM_CONFIDENCE     = 55;

    /**
     * Auto-match a single BankStatement against tenant transactions.
     * Returns best match with confidence score, or flag explanation if no match.
     */
    public function matchStatement(BankStatement $statement, int $tenantId): array
    {
        $candidates = $this->fetchCandidates($statement, $tenantId);

        if ($candidates->isEmpty()) {
            return $this->flagUnmatched($statement, 'no_candidates', $tenantId);
        }

        $scored = $candidates->map(fn($tx) => $this->scoreCandidate($statement, $tx))->sortByDesc('score');
        $best   = $scored->first();

        if ($best['score'] >= self::HIGH_CONFIDENCE) {
            return [
                'status'      => 'matched',
                'confidence'  => $best['score'],
                'tier'        => 'high',
                'transaction' => $best['transaction'],
                'reasons'     => $best['reasons'],
                'alternatives' => $scored->skip(1)->take(2)->values()->map(fn($s) => [
                    'id'         => $s['transaction']->id,
                    'number'     => $s['transaction']->number,
                    'score'      => $s['score'],
                    'date'       => $s['transaction']->date->format('d M Y'),
                    'amount'     => $s['transaction']->amount,
                    'description'=> $s['transaction']->description,
                ])->all(),
            ];
        }

        if ($best['score'] >= self::MEDIUM_CONFIDENCE) {
            return [
                'status'      => 'suggestion',
                'confidence'  => $best['score'],
                'tier'        => 'medium',
                'transaction' => $best['transaction'],
                'reasons'     => $best['reasons'],
                'alternatives' => $scored->skip(1)->take(2)->values()->map(fn($s) => [
                    'id'         => $s['transaction']->id,
                    'number'     => $s['transaction']->number,
                    'score'      => $s['score'],
                    'date'       => $s['transaction']->date->format('d M Y'),
                    'amount'     => $s['transaction']->amount,
                    'description'=> $s['transaction']->description,
                ])->all(),
            ];
        }

        return $this->flagUnmatched($statement, 'low_confidence', $tenantId, $best);
    }

    /**
     * Batch auto-match all unmatched statements for a tenant.
     */
    public function matchAll(int $tenantId): array
    {
        $statements = BankStatement::where('tenant_id', $tenantId)
            ->where('status', 'unmatched')
            ->get();

        $results = [];
        foreach ($statements as $stmt) {
            $results[$stmt->id] = $this->matchStatement($stmt, $tenantId);
        }
        return $results;
    }

    /**
     * Apply a confirmed match (update DB).
     */
    public function applyMatch(BankStatement $statement, int $transactionId): void
    {
        $statement->update([
            'status'                 => 'matched',
            'matched_transaction_id' => $transactionId,
        ]);
    }

    // ── Private helpers ──────────────────────────────────────────────

    private function fetchCandidates(BankStatement $stmt, int $tenantId): Collection
    {
        $date   = Carbon::parse($stmt->transaction_date);
        $amount = abs($stmt->amount);

        // Amount window: ±1%
        $amountMin = $amount * (1 - self::AMOUNT_TOLERANCE_PCT);
        $amountMax = $amount * (1 + self::AMOUNT_TOLERANCE_PCT);

        return Transaction::where('tenant_id', $tenantId)
            ->whereBetween('date', [
                $date->copy()->subDays(self::DATE_WINDOW_DAYS),
                $date->copy()->addDays(self::DATE_WINDOW_DAYS),
            ])
            ->whereBetween('amount', [$amountMin, $amountMax])
            ->whereNotIn('id', function ($q) use ($tenantId) {
                $q->select('matched_transaction_id')
                  ->from('bank_statements')
                  ->where('tenant_id', $tenantId)
                  ->where('status', 'matched')
                  ->whereNotNull('matched_transaction_id');
            })
            ->limit(20)
            ->get();
    }

    private function scoreCandidate(BankStatement $stmt, Transaction $tx): array
    {
        $score   = 0;
        $reasons = [];

        // 1. Amount match (max 40 pts)
        $stmtAmt = abs($stmt->amount);
        $txAmt   = abs((float) $tx->amount);
        $diffPct = $txAmt > 0 ? abs($stmtAmt - $txAmt) / $txAmt : 1;

        if ($diffPct === 0.0) {
            $score += 40; $reasons[] = 'Jumlah sama persis';
        } elseif ($diffPct <= 0.001) {
            $score += 35; $reasons[] = 'Jumlah hampir sama (< 0.1%)';
        } elseif ($diffPct <= self::AMOUNT_TOLERANCE_PCT) {
            $score += 25; $reasons[] = 'Jumlah mendekati (< 1%)';
        }

        // 2. Date proximity (max 30 pts)
        $daysDiff = abs(Carbon::parse($stmt->transaction_date)->diffInDays($tx->date));
        if ($daysDiff === 0) {
            $score += 30; $reasons[] = 'Tanggal sama';
        } elseif ($daysDiff <= 1) {
            $score += 25; $reasons[] = 'Selisih 1 hari';
        } elseif ($daysDiff <= 3) {
            $score += 15; $reasons[] = "Selisih {$daysDiff} hari";
        } elseif ($daysDiff <= self::DATE_WINDOW_DAYS) {
            $score += 8;  $reasons[] = "Selisih {$daysDiff} hari";
        }

        // 3. Description similarity (max 20 pts)
        $descScore = $this->descriptionSimilarity($stmt->description, $tx->description ?? '');
        if ($descScore >= 0.8) {
            $score += 20; $reasons[] = 'Deskripsi sangat mirip';
        } elseif ($descScore >= 0.5) {
            $score += 12; $reasons[] = 'Deskripsi cukup mirip';
        } elseif ($descScore >= 0.3) {
            $score += 6;  $reasons[] = 'Deskripsi ada kesamaan kata';
        }

        // 4. Reference match (max 10 pts)
        if ($stmt->reference && $tx->reference) {
            $stmtRef = strtolower(trim($stmt->reference));
            $txRef   = strtolower(trim($tx->reference));
            if ($stmtRef === $txRef) {
                $score += 10; $reasons[] = 'Nomor referensi sama';
            } elseif (str_contains($stmtRef, $txRef) || str_contains($txRef, $stmtRef)) {
                $score += 6;  $reasons[] = 'Referensi sebagian cocok';
            }
        }

        // 5. Type consistency (bonus/penalty)
        $txIsCredit = in_array($tx->type, ['income', 'receipt', 'credit']);
        $stmtCredit = $stmt->type === 'credit';
        if ($txIsCredit === $stmtCredit) {
            $score += 5; $reasons[] = 'Tipe transaksi sesuai';
        } else {
            $score -= 10; $reasons[] = 'Tipe transaksi berbeda';
        }

        return [
            'score'       => max(0, min(100, $score)),
            'reasons'     => $reasons,
            'transaction' => $tx,
        ];
    }

    private function descriptionSimilarity(string $a, string $b): float
    {
        if (!$a || !$b) return 0.0;

        $tokensA = $this->tokenize($a);
        $tokensB = $this->tokenize($b);

        if (empty($tokensA) || empty($tokensB)) return 0.0;

        $intersection = count(array_intersect($tokensA, $tokensB));
        $union        = count(array_unique(array_merge($tokensA, $tokensB)));

        return $union > 0 ? $intersection / $union : 0.0;
    }

    private function tokenize(string $text): array
    {
        $text = strtolower(preg_replace('/[^a-z0-9\s]/i', ' ', $text));
        $words = array_filter(explode(' ', $text), fn($w) => strlen($w) > 2);
        // Remove common stopwords
        $stop = ['the', 'dan', 'ke', 'dari', 'untuk', 'dengan', 'via', 'ref', 'no', 'trx'];
        return array_values(array_diff($words, $stop));
    }

    private function flagUnmatched(BankStatement $stmt, string $reason, int $tenantId, ?array $bestCandidate = null): array
    {
        $amount = abs($stmt->amount);
        $date   = Carbon::parse($stmt->transaction_date);

        // Diagnose why it can't be matched
        $flags       = [];
        $explanation = '';

        if ($reason === 'no_candidates') {
            // Check if amount exists but date is off
            $amountExists = Transaction::where('tenant_id', $tenantId)
                ->whereBetween('amount', [$amount * 0.99, $amount * 1.01])
                ->exists();

            $dateExists = Transaction::where('tenant_id', $tenantId)
                ->whereBetween('date', [$date->copy()->subDays(30), $date->copy()->addDays(30)])
                ->where('amount', '>', $amount * 0.5)
                ->exists();

            if (!$amountExists && !$dateExists) {
                $flags[]     = 'Tidak ada transaksi dengan jumlah atau tanggal yang mendekati';
                $explanation = 'Transaksi ini kemungkinan belum dicatat di sistem. Perlu input jurnal baru.';
            } elseif ($amountExists) {
                $flags[]     = 'Jumlah cocok ditemukan tapi di luar rentang tanggal ±' . self::DATE_WINDOW_DAYS . ' hari';
                $explanation = 'Kemungkinan ada perbedaan tanggal pencatatan. Cek transaksi dengan jumlah Rp ' . number_format($amount, 0, ',', '.') . '.';
            } else {
                $flags[]     = 'Tidak ada transaksi dengan jumlah yang mendekati dalam periode ini';
                $explanation = 'Jumlah Rp ' . number_format($amount, 0, ',', '.') . ' tidak ditemukan di sistem. Mungkin transaksi belum dicatat.';
            }
        } elseif ($reason === 'low_confidence') {
            $flags[]     = 'Kandidat terbaik hanya ' . $bestCandidate['score'] . '% cocok';
            $explanation = 'Ditemukan kandidat tapi kemiripan rendah: ' . implode(', ', $bestCandidate['reasons'] ?? []) . '.';
        }

        // Additional flags
        if ($stmt->type === 'debit' && $amount > 50_000_000) {
            $flags[] = 'Transaksi debit bernilai besar (> Rp 50jt) — perlu verifikasi manual';
        }
        if ($date->isWeekend()) {
            $flags[] = 'Transaksi terjadi di akhir pekan — mungkin ada perbedaan tanggal valuta';
        }

        return [
            'status'      => 'unmatched',
            'confidence'  => $bestCandidate['score'] ?? 0,
            'tier'        => 'none',
            'transaction' => $bestCandidate['transaction'] ?? null,
            'reasons'     => $bestCandidate['reasons'] ?? [],
            'flags'       => $flags,
            'explanation' => $explanation,
        ];
    }
}
