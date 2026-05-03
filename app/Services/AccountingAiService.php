<?php

namespace App\Services;

use App\Enums\AiUseCase;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

/**
 * AccountingAiService — AI contextual untuk fitur Accounting & Journal.
 *
 * 1. suggestAccounts()       — suggest akun debit/kredit dari deskripsi transaksi
 * 2. detectJournalAnomalies()— deteksi jurnal "aneh" sebelum di-post
 * 3. categorizeStatement()   — auto-categorize transaksi bank statement ke akun COA
 *
 * Use Cases:
 * - categorizeStatement() uses AiUseCase::BANK_RECONCILIATION_AI
 * - Methods generating financial reports use AiUseCase::FINANCIAL_REPORT
 */
class AccountingAiService
{
    // ─── 1. Account Suggestion ────────────────────────────────────

    /**
     * Suggest pasangan akun debit/kredit berdasarkan deskripsi transaksi.
     *
     * Strategi (tanpa API eksternal):
     * a) Cari pola dari histori jurnal tenant yang deskripsinya mirip
     * b) Fallback ke rule-based keyword matching
     *
     * Return: [
     *   ['debit_account_id', 'debit_account_code', 'debit_account_name',
     *    'credit_account_id', 'credit_account_code', 'credit_account_name',
     *    'confidence', 'basis']
     * ]
     */
    public function suggestAccounts(int $tenantId, string $description, float $amount = 0): array
    {
        $desc = strtolower(trim($description));
        if (empty($desc)) return [];

        // ── a) Histori: cari jurnal dengan deskripsi mirip ────────
        $historySuggestions = $this->suggestFromHistory($tenantId, $desc);
        if (!empty($historySuggestions)) {
            return $historySuggestions;
        }

        // ── b) Rule-based keyword matching ────────────────────────
        return $this->suggestFromKeywords($tenantId, $desc, $amount);
    }

    /**
     * Cari pola dari histori jurnal yang deskripsinya mengandung kata kunci serupa.
     */
    private function suggestFromHistory(int $tenantId, string $desc): array
    {
        // Ambil kata-kata penting (>= 4 karakter)
        $words = array_filter(explode(' ', $desc), fn($w) => strlen($w) >= 4);
        if (empty($words)) return [];

        // Cari jurnal posted dengan deskripsi mirip (max 6 bulan terakhir)
        $query = JournalEntry::where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->where('date', '>=', now()->subMonths(6)->toDateString());

        foreach (array_slice($words, 0, 3) as $word) {
            $query->where('description', 'like', "%{$word}%");
        }

        $similar = $query->with('lines.account')
            ->orderByDesc('date')
            ->limit(5)
            ->get();

        if ($similar->isEmpty()) return [];

        // Ambil pasangan akun yang paling sering muncul
        $pairs = [];
        foreach ($similar as $journal) {
            $debits  = $journal->lines->where('debit', '>', 0)->sortByDesc('debit');
            $credits = $journal->lines->where('credit', '>', 0)->sortByDesc('credit');

            $debitAcc  = $debits->first()?->account;
            $creditAcc = $credits->first()?->account;

            if (!$debitAcc || !$creditAcc) continue;

            $key = "{$debitAcc->id}_{$creditAcc->id}";
            $pairs[$key] = ($pairs[$key] ?? 0) + 1;
        }

        if (empty($pairs)) return [];

        arsort($pairs);
        $topKey = array_key_first($pairs);
        [$debitId, $creditId] = explode('_', $topKey);

        $debitAcc  = ChartOfAccount::find($debitId);
        $creditAcc = ChartOfAccount::find($creditId);

        if (!$debitAcc || !$creditAcc) return [];

        $count = $pairs[$topKey];
        return [[
            'debit_account_id'    => (int) $debitId,
            'debit_account_code'  => $debitAcc->code,
            'debit_account_name'  => $debitAcc->name,
            'credit_account_id'   => (int) $creditId,
            'credit_account_code' => $creditAcc->code,
            'credit_account_name' => $creditAcc->name,
            'confidence'          => $count >= 3 ? 'high' : 'medium',
            'basis'               => "Berdasarkan {$count} jurnal serupa sebelumnya",
        ]];
    }

    /**
     * Rule-based: mapping keyword → pasangan akun COA standar Indonesia.
     */
    private function suggestFromKeywords(int $tenantId, string $desc, float $amount): array
    {
        // Ambil semua akun aktif tenant
        $accounts = ChartOfAccount::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('is_header', false)
            ->get()
            ->keyBy(fn($a) => strtolower($a->name));

        $rules = $this->getKeywordRules();

        foreach ($rules as $rule) {
            $matched = false;
            foreach ($rule['keywords'] as $kw) {
                if (str_contains($desc, $kw)) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) continue;

            // Cari akun yang namanya mengandung kata kunci COA
            $debitAcc  = $this->findAccountByKeywords($accounts, $rule['debit_keywords']);
            $creditAcc = $this->findAccountByKeywords($accounts, $rule['credit_keywords']);

            if (!$debitAcc || !$creditAcc) continue;

            return [[
                'debit_account_id'    => $debitAcc->id,
                'debit_account_code'  => $debitAcc->code,
                'debit_account_name'  => $debitAcc->name,
                'credit_account_id'   => $creditAcc->id,
                'credit_account_code' => $creditAcc->code,
                'credit_account_name' => $creditAcc->name,
                'confidence'          => 'low',
                'basis'               => "Berdasarkan kata kunci: \"{$rule['label']}\"",
            ]];
        }

        return [];
    }

    /**
     * Daftar aturan keyword → pasangan akun (standar COA Indonesia).
     */
    private function getKeywordRules(): array
    {
        return [
            // Penjualan
            [
                'keywords' => ['penjualan', 'jual', 'sales', 'revenue', 'pendapatan'],
                'label' => 'Penjualan',
                'debit_keywords'  => ['kas', 'bank', 'piutang'],
                'credit_keywords' => ['penjualan', 'pendapatan', 'revenue']
            ],

            // Pembelian / Pengadaan
            [
                'keywords' => ['pembelian', 'beli', 'purchase', 'pengadaan', 'po'],
                'label' => 'Pembelian',
                'debit_keywords'  => ['persediaan', 'inventory', 'pembelian'],
                'credit_keywords' => ['kas', 'bank', 'hutang', 'utang']
            ],

            // Gaji / Payroll
            [
                'keywords' => ['gaji', 'salary', 'payroll', 'upah', 'tunjangan'],
                'label' => 'Gaji',
                'debit_keywords'  => ['beban gaji', 'gaji', 'biaya gaji'],
                'credit_keywords' => ['kas', 'bank', 'hutang gaji']
            ],

            // Sewa
            [
                'keywords' => ['sewa', 'rent', 'rental', 'kontrak'],
                'label' => 'Sewa',
                'debit_keywords'  => ['beban sewa', 'sewa', 'biaya sewa'],
                'credit_keywords' => ['kas', 'bank']
            ],

            // Listrik / Utilitas
            [
                'keywords' => ['listrik', 'pln', 'air', 'pdam', 'telepon', 'internet', 'utilitas'],
                'label' => 'Utilitas',
                'debit_keywords'  => ['beban listrik', 'beban utilitas', 'utilitas', 'listrik'],
                'credit_keywords' => ['kas', 'bank']
            ],

            // Depresiasi
            [
                'keywords' => ['depresiasi', 'depreciation', 'penyusutan'],
                'label' => 'Depresiasi',
                'debit_keywords'  => ['beban depresiasi', 'penyusutan'],
                'credit_keywords' => ['akumulasi depresiasi', 'akumulasi penyusutan']
            ],

            // Pajak
            [
                'keywords' => ['pajak', 'tax', 'ppn', 'pph', 'bphtb'],
                'label' => 'Pajak',
                'debit_keywords'  => ['pajak', 'ppn masukan', 'beban pajak'],
                'credit_keywords' => ['kas', 'bank', 'hutang pajak', 'ppn keluaran']
            ],

            // Kas masuk / transfer
            [
                'keywords' => ['transfer masuk', 'setoran', 'deposit', 'penerimaan'],
                'label' => 'Penerimaan Kas',
                'debit_keywords'  => ['kas', 'bank'],
                'credit_keywords' => ['piutang', 'pendapatan']
            ],

            // Kas keluar
            [
                'keywords' => ['transfer keluar', 'penarikan', 'pembayaran', 'bayar'],
                'label' => 'Pembayaran',
                'debit_keywords'  => ['hutang', 'utang', 'beban'],
                'credit_keywords' => ['kas', 'bank']
            ],

            // Biaya operasional
            [
                'keywords' => ['operasional', 'kantor', 'atk', 'alat tulis', 'supplies'],
                'label' => 'Biaya Operasional',
                'debit_keywords'  => ['beban operasional', 'biaya operasional', 'perlengkapan'],
                'credit_keywords' => ['kas', 'bank']
            ],

            // Asuransi
            [
                'keywords' => ['asuransi', 'insurance', 'premi'],
                'label' => 'Asuransi',
                'debit_keywords'  => ['beban asuransi', 'asuransi dibayar dimuka'],
                'credit_keywords' => ['kas', 'bank']
            ],
        ];
    }

    private function findAccountByKeywords($accounts, array $keywords): ?ChartOfAccount
    {
        foreach ($keywords as $kw) {
            foreach ($accounts as $name => $account) {
                if (str_contains($name, $kw)) {
                    return $account;
                }
            }
        }
        return null;
    }

    // ─── 2. Journal Anomaly Detection ────────────────────────────

    /**
     * Deteksi jurnal "aneh" sebelum di-post.
     *
     * Cek:
     * - Jumlah sangat besar (> 3x rata-rata jurnal tenant)
     * - Akun yang tidak lazim dipasangkan
     * - Jurnal di hari libur / weekend
     * - Deskripsi terlalu pendek / tidak informatif
     * - Akun yang sama di debit dan kredit
     * - Nilai bulat yang sangat besar (potensi estimasi)
     *
     * Return: ['warnings' => [...], 'errors' => [...], 'risk' => 'high'|'medium'|'low']
     */
    public function detectJournalAnomalies(int $tenantId, array $lines, string $date, string $description, float $totalAmount): array
    {
        $warnings = [];
        $errors   = [];

        // ── 1. Deskripsi tidak informatif ─────────────────────────
        if (strlen(trim($description)) < 5) {
            $warnings[] = 'Deskripsi jurnal terlalu pendek. Tambahkan keterangan yang lebih jelas.';
        }

        // ── 2. Akun sama di debit dan kredit ──────────────────────
        $debitAccounts  = collect($lines)->where('debit', '>', 0)->pluck('account_id')->filter()->toArray();
        $creditAccounts = collect($lines)->where('credit', '>', 0)->pluck('account_id')->filter()->toArray();
        $overlap = array_intersect($debitAccounts, $creditAccounts);
        if (!empty($overlap)) {
            $errors[] = 'Akun yang sama muncul di debit dan kredit sekaligus. Periksa kembali baris jurnal.';
        }

        // ── 3. Jumlah sangat besar vs rata-rata ───────────────────
        $avgAmount = JournalEntry::where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->where('date', '>=', now()->subMonths(3)->toDateString())
            ->join('journal_entry_lines', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->avg('journal_entry_lines.debit') ?? 0;

        if ($avgAmount > 0 && $totalAmount > ($avgAmount * 5)) {
            $fmt = 'Rp ' . number_format($totalAmount, 0, ',', '.');
            $fmtAvg = 'Rp ' . number_format($avgAmount, 0, ',', '.');
            $warnings[] = "Jumlah jurnal ({$fmt}) jauh di atas rata-rata ({$fmtAvg}). Pastikan angka sudah benar.";
        }

        // ── 4. Nilai bulat sangat besar (potensi estimasi) ────────
        if ($totalAmount >= 100_000_000 && fmod($totalAmount, 1_000_000) === 0.0) {
            $warnings[] = 'Nilai jurnal adalah angka bulat yang sangat besar. Pastikan ini bukan estimasi.';
        }

        // ── 5. Tanggal weekend ────────────────────────────────────
        $dayOfWeek = date('N', strtotime($date)); // 6=Sabtu, 7=Minggu
        if (in_array($dayOfWeek, [6, 7])) {
            $dayName = $dayOfWeek === 6 ? 'Sabtu' : 'Minggu';
            $warnings[] = "Jurnal dibuat pada hari {$dayName}. Pastikan ini disengaja.";
        }

        // ── 6. Tanggal di masa depan ──────────────────────────────
        if (strtotime($date) > strtotime(today()->toDateString())) {
            $warnings[] = 'Tanggal jurnal adalah tanggal masa depan.';
        }

        // ── 7. Pasangan akun tidak lazim ──────────────────────────
        $unusualPairs = $this->detectUnusualAccountPairs($tenantId, $debitAccounts, $creditAccounts);
        if (!empty($unusualPairs)) {
            $warnings = array_merge($warnings, $unusualPairs);
        }

        // ── 8. Banyak baris (> 10) ────────────────────────────────
        if (count($lines) > 10) {
            $warnings[] = 'Jurnal memiliki lebih dari 10 baris. Pertimbangkan memecah menjadi beberapa jurnal.';
        }

        // Risk level
        $risk = match (true) {
            !empty($errors)          => 'high',
            count($warnings) >= 2    => 'medium',
            count($warnings) === 1   => 'low',
            default                  => 'none',
        };

        return compact('warnings', 'errors', 'risk');
    }

    /**
     * Deteksi pasangan akun yang tidak lazim berdasarkan histori tenant.
     */
    private function detectUnusualAccountPairs(int $tenantId, array $debitIds, array $creditIds): array
    {
        if (empty($debitIds) || empty($creditIds)) return [];

        $warnings = [];

        // Ambil akun-akun yang terlibat
        $accounts = ChartOfAccount::whereIn('id', array_merge($debitIds, $creditIds))->get()->keyBy('id');

        foreach ($debitIds as $dId) {
            foreach ($creditIds as $cId) {
                $debitAcc  = $accounts[$dId] ?? null;
                $creditAcc = $accounts[$cId] ?? null;
                if (!$debitAcc || !$creditAcc) continue;

                // Cek pasangan yang tidak pernah muncul di histori (6 bulan)
                $existsInHistory = JournalEntryLine::where('account_id', $dId)
                    ->where('debit', '>', 0)
                    ->whereHas(
                        'journalEntry',
                        fn($q) => $q
                            ->where('tenant_id', $tenantId)
                            ->where('status', 'posted')
                            ->where('date', '>=', now()->subMonths(6)->toDateString())
                            ->whereHas('lines', fn($q2) => $q2->where('account_id', $cId)->where('credit', '>', 0))
                    )->exists();

                if (!$existsInHistory) {
                    // Cek apakah tipe akun tidak lazim dipasangkan
                    $unusual = $this->isUnusualTypePair($debitAcc->type, $creditAcc->type);
                    if ($unusual) {
                        $warnings[] = "Pasangan akun {$debitAcc->code} ({$debitAcc->name}) debit ↔ {$creditAcc->code} ({$creditAcc->name}) kredit tidak lazim. Periksa kembali.";
                    }
                }
            }
        }

        return array_slice($warnings, 0, 2); // max 2 warning pasangan
    }

    /**
     * Pasangan tipe akun yang tidak lazim dalam double-entry.
     */
    private function isUnusualTypePair(string $debitType, string $creditType): bool
    {
        // Pasangan yang sangat tidak lazim
        $unusual = [
            ['revenue', 'revenue'],   // revenue debit ↔ revenue kredit
            ['expense', 'expense'],   // expense debit ↔ expense kredit
            ['equity', 'asset'],      // equity debit ↔ asset kredit (jarang)
        ];

        foreach ($unusual as [$d, $c]) {
            if ($debitType === $d && $creditType === $c) return true;
        }

        return false;
    }

    // ─── 3. Bank Statement Auto-Categorize ───────────────────────

    /**
     * Auto-categorize transaksi bank statement ke akun COA.
     *
     * Use Case: AiUseCase::BANK_RECONCILIATION_AI
     * When AI provider is integrated, pass: AiUseCase::BANK_RECONCILIATION_AI->value
     *
     * Return: [
     *   'account_id'   => int,
     *   'account_code' => string,
     *   'account_name' => string,
     *   'confidence'   => 'high'|'medium'|'low',
     *   'basis'        => string,
     *   'journal_type' => 'debit'|'credit',  // posisi di jurnal (bukan tipe bank)
     * ]
     *
     * Requirements: 8.3
     */
    public function categorizeStatement(int $tenantId, string $description, string $type, float $amount): array
    {
        $desc = strtolower(trim($description));

        // ── a) Cari dari histori matched statements ───────────────
        $historyResult = $this->categorizeFromHistory($tenantId, $desc, $type);
        if ($historyResult) return $historyResult;

        // ── b) Rule-based ─────────────────────────────────────────
        return $this->categorizeFromRules($tenantId, $desc, $type, $amount);
    }

    /**
     * Cari kategori dari histori bank statement yang sudah pernah di-match.
     */
    private function categorizeFromHistory(int $tenantId, string $desc, string $type): ?array
    {
        // Cari statement yang sudah matched dengan deskripsi mirip
        $words = array_filter(explode(' ', $desc), fn($w) => strlen($w) >= 4);
        if (empty($words)) return null;

        $query = DB::table('bank_statements')
            ->where('tenant_id', $tenantId)
            ->where('status', 'matched')
            ->where('type', $type)
            ->whereNotNull('matched_transaction_id');

        foreach (array_slice($words, 0, 2) as $word) {
            $query->where('description', 'like', "%{$word}%");
        }

        $matched = $query->orderByDesc('transaction_date')->limit(5)->get();

        if ($matched->isEmpty()) return null;

        // Ambil akun dari jurnal yang terkait dengan transaksi tersebut
        $transactionIds = $matched->pluck('matched_transaction_id')->filter()->toArray();
        if (empty($transactionIds)) return null;

        // Cari journal lines yang terkait
        $accountId = JournalEntryLine::whereHas(
            'journalEntry',
            fn($q) => $q
                ->where('tenant_id', $tenantId)
                ->whereIn('reference_id', $transactionIds)
        )
            ->where($type === 'credit' ? 'credit' : 'debit', '>', 0)
            ->groupBy('account_id')
            ->orderByRaw('COUNT(*) DESC')
            ->value('account_id');

        if (!$accountId) return null;

        $account = ChartOfAccount::find($accountId);
        if (!$account) return null;

        return [
            'account_id'   => $account->id,
            'account_code' => $account->code,
            'account_name' => $account->name,
            'confidence'   => 'high',
            'basis'        => 'Berdasarkan histori transaksi serupa yang sudah dikategorikan',
            'journal_type' => $type === 'credit' ? 'credit' : 'debit',
        ];
    }

    /**
     * Rule-based categorization untuk bank statement.
     * type = 'credit' (uang masuk ke bank) atau 'debit' (uang keluar dari bank)
     */
    private function categorizeFromRules(int $tenantId, string $desc, string $type, float $amount): array
    {
        $accounts = ChartOfAccount::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('is_header', false)
            ->get();

        $rules = $this->getStatementRules();

        foreach ($rules as $rule) {
            // Filter berdasarkan tipe transaksi bank
            if (isset($rule['bank_type']) && $rule['bank_type'] !== $type) continue;

            $matched = false;
            foreach ($rule['keywords'] as $kw) {
                if (str_contains($desc, $kw)) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) continue;

            $account = $accounts->first(
                fn($a) => collect($rule['account_keywords'])
                    ->contains(fn($kw) => str_contains(strtolower($a->name), $kw))
            );

            if (!$account) continue;

            return [
                'account_id'   => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'confidence'   => 'medium',
                'basis'        => "Kata kunci: \"{$rule['label']}\"",
                'journal_type' => $rule['journal_side'],
            ];
        }

        // Fallback: kas/bank
        $bankAccount = $accounts->first(
            fn($a) =>
            str_contains(strtolower($a->name), 'kas') ||
                str_contains(strtolower($a->name), 'bank')
        );

        return [
            'account_id'   => $bankAccount?->id,
            'account_code' => $bankAccount?->code ?? '-',
            'account_name' => $bankAccount?->name ?? 'Tidak ditemukan',
            'confidence'   => 'low',
            'basis'        => 'Tidak ada pola yang cocok, gunakan akun kas/bank sebagai default',
            'journal_type' => $type === 'credit' ? 'debit' : 'credit',
        ];
    }

    private function getStatementRules(): array
    {
        return [
            // Kredit bank (uang masuk) → Piutang / Pendapatan
            [
                'keywords' => ['transfer masuk', 'trfin', 'cr', 'setoran', 'penerimaan', 'pembayaran dari'],
                'bank_type' => 'credit',
                'label' => 'Penerimaan',
                'account_keywords' => ['piutang', 'pendapatan', 'penjualan'],
                'journal_side' => 'credit'
            ],

            [
                'keywords' => ['penjualan', 'sales', 'invoice'],
                'bank_type' => 'credit',
                'label' => 'Penjualan',
                'account_keywords' => ['penjualan', 'pendapatan'],
                'journal_side' => 'credit'
            ],

            // Debit bank (uang keluar) → Hutang / Beban
            [
                'keywords' => ['gaji', 'salary', 'payroll', 'upah'],
                'bank_type' => 'debit',
                'label' => 'Gaji',
                'account_keywords' => ['beban gaji', 'gaji', 'hutang gaji'],
                'journal_side' => 'debit'
            ],

            [
                'keywords' => ['sewa', 'rent', 'rental'],
                'bank_type' => 'debit',
                'label' => 'Sewa',
                'account_keywords' => ['beban sewa', 'sewa'],
                'journal_side' => 'debit'
            ],

            [
                'keywords' => ['listrik', 'pln', 'air', 'pdam', 'telepon', 'internet'],
                'bank_type' => 'debit',
                'label' => 'Utilitas',
                'account_keywords' => ['beban listrik', 'utilitas', 'listrik'],
                'journal_side' => 'debit'
            ],

            [
                'keywords' => ['pajak', 'tax', 'pph', 'ppn', 'bpjs'],
                'bank_type' => 'debit',
                'label' => 'Pajak',
                'account_keywords' => ['hutang pajak', 'pajak', 'beban pajak'],
                'journal_side' => 'debit'
            ],

            [
                'keywords' => ['pembelian', 'purchase', 'supplier', 'vendor', 'po'],
                'bank_type' => 'debit',
                'label' => 'Pembelian',
                'account_keywords' => ['hutang', 'utang usaha', 'persediaan'],
                'journal_side' => 'debit'
            ],

            [
                'keywords' => ['operasional', 'atk', 'kantor', 'supplies'],
                'bank_type' => 'debit',
                'label' => 'Operasional',
                'account_keywords' => ['beban operasional', 'biaya operasional'],
                'journal_side' => 'debit'
            ],

            [
                'keywords' => ['asuransi', 'insurance', 'premi'],
                'bank_type' => 'debit',
                'label' => 'Asuransi',
                'account_keywords' => ['beban asuransi', 'asuransi'],
                'journal_side' => 'debit'
            ],
        ];
    }
}
