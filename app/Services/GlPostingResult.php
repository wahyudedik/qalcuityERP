<?php

namespace App\Services;

use App\Models\JournalEntry;

/**
 * GlPostingResult — Structured result from GlPostingService.
 *
 * Replaces the ambiguous ?JournalEntry return type so callers
 * can distinguish between:
 *   - success: journal was created and posted
 *   - skipped: journal already exists (idempotent, not an error)
 *   - failed:  journal could not be created (missing COA, unbalanced, etc.)
 */
final class GlPostingResult
{
    private function __construct(
        public readonly string        $status,    // 'success' | 'skipped' | 'failed'
        public readonly ?JournalEntry $journal,
        public readonly ?string       $reason,    // human-readable reason for failure/skip
        public readonly array         $missingCoa, // list of COA codes that were not found
    ) {}

    public static function success(JournalEntry $journal): self
    {
        return new self('success', $journal, null, []);
    }

    public static function skipped(string $reason): self
    {
        return new self('skipped', null, $reason, []);
    }

    public static function failed(string $reason, array $missingCoa = []): self
    {
        return new self('failed', null, $reason, $missingCoa);
    }

    public function isSuccess(): bool { return $this->status === 'success'; }
    public function isSkipped(): bool { return $this->status === 'skipped'; }
    public function isFailed():  bool { return $this->status === 'failed'; }

    /**
     * User-facing warning message when GL posting failed.
     * Returns null if no warning needed (success or skipped).
     */
    public function warningMessage(): ?string
    {
        if (!$this->isFailed()) return null;

        if (!empty($this->missingCoa)) {
            $codes = implode(', ', $this->missingCoa);
            return "⚠️ Jurnal otomatis tidak terbuat karena akun COA tidak ditemukan: {$codes}. "
                . "Silakan load COA Default Indonesia di menu Pengaturan → Akuntansi, atau buat jurnal manual.";
        }

        return "⚠️ Jurnal otomatis tidak terbuat: {$this->reason}. Silakan buat jurnal manual di menu Jurnal.";
    }
}
