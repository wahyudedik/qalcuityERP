<?php

namespace App\Services\Agent;

use App\DTOs\Agent\ErpContext;

/**
 * SkillRouter — Task 7
 *
 * Mendeteksi domain bisnis dari intent pesan user dan mengaktifkan Skill
 * yang relevan. Membangun system prompt tambahan per skill dengan terminologi
 * domain yang tepat.
 *
 * Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6
 */
class SkillRouter
{
    /**
     * Nama-nama skill yang tersedia.
     */
    public const SKILL_ACCOUNTING  = 'accounting';
    public const SKILL_INVENTORY   = 'inventory';
    public const SKILL_HRM         = 'hrm';
    public const SKILL_SALES       = 'sales';
    public const SKILL_PROJECT     = 'project';
    public const SKILL_HEALTHCARE  = 'healthcare';
    public const SKILL_MANUFACTURE = 'manufacture';
    public const SKILL_TELECOM     = 'telecom';

    /**
     * Modul industri khusus yang memerlukan skill tambahan.
     */
    private const INDUSTRY_MODULE_SKILL_MAP = [
        'healthcare'    => self::SKILL_HEALTHCARE,
        'manufacturing' => self::SKILL_MANUFACTURE,
        'manufacture'   => self::SKILL_MANUFACTURE,
        'telecom'       => self::SKILL_TELECOM,
    ];

    /**
     * Keyword per skill untuk deteksi intent (Bahasa Indonesia & Inggris).
     */
    private const SKILL_KEYWORDS = [
        self::SKILL_ACCOUNTING => [
            // Bahasa Indonesia
            'jurnal', 'akuntansi', 'neraca', 'laba rugi', 'arus kas', 'buku besar',
            'debit', 'kredit', 'piutang', 'hutang', 'utang', 'aset', 'ekuitas',
            'pendapatan', 'beban', 'biaya', 'pajak', 'ppn', 'pph', 'invoice',
            'faktur', 'keuangan', 'laporan keuangan', 'anggaran', 'budget',
            'rekonsiliasi', 'bank', 'kas', 'periode akuntansi', 'tutup buku',
            'posting', 'trial balance', 'neraca saldo', 'penyusutan', 'amortisasi',
            // English
            'journal', 'accounting', 'balance sheet', 'income statement', 'cash flow',
            'ledger', 'receivable', 'payable', 'asset', 'equity', 'revenue',
            'expense', 'tax', 'financial', 'financial report', 'reconciliation',
            'depreciation', 'amortization', 'fiscal', 'profit', 'loss',
        ],
        self::SKILL_INVENTORY => [
            // Bahasa Indonesia
            'stok', 'inventori', 'gudang', 'produk', 'barang', 'persediaan',
            'reorder', 'minimum stok', 'stok kritis', 'transfer stok', 'penyesuaian stok',
            'fifo', 'average', 'harga pokok', 'hpp', 'lot', 'batch', 'barcode',
            'qr code', 'warehouse', 'lokasi', 'rak', 'bin', 'penerimaan barang',
            'pengiriman', 'retur', 'opname', 'stock opname',
            // English
            'stock', 'inventory', 'warehouse', 'product', 'goods', 'item',
            'reorder point', 'critical stock', 'stock transfer', 'stock adjustment',
            'costing', 'cost of goods', 'cogs', 'receiving', 'shipment', 'return',
        ],
        self::SKILL_HRM => [
            // Bahasa Indonesia
            'karyawan', 'pegawai', 'sdm', 'hrm', 'hr', 'payroll', 'gaji',
            'absensi', 'kehadiran', 'cuti', 'lembur', 'shift', 'jadwal kerja',
            'rekrutmen', 'pelatihan', 'training', 'kontrak', 'bpjs', 'umr', 'umk',
            'pph 21', 'tunjangan', 'potongan', 'slip gaji', 'penggajian',
            'jabatan', 'departemen', 'divisi', 'struktur organisasi',
            // English
            'employee', 'staff', 'human resource', 'payroll', 'salary', 'wage',
            'attendance', 'leave', 'overtime', 'shift', 'schedule', 'recruitment',
            'training', 'contract', 'allowance', 'deduction', 'payslip',
            'position', 'department', 'division',
        ],
        self::SKILL_SALES => [
            // Bahasa Indonesia
            'penjualan', 'sales', 'pelanggan', 'customer', 'order', 'pesanan',
            'penawaran', 'quotation', 'invoice penjualan', 'faktur penjualan',
            'pengiriman', 'delivery', 'retur penjualan', 'diskon', 'harga jual',
            'daftar harga', 'price list', 'komisi', 'target penjualan', 'crm',
            'prospek', 'lead', 'pipeline', 'kontak', 'peluang',
            // English
            'sale', 'customer', 'order', 'quotation', 'sales invoice',
            'delivery', 'sales return', 'discount', 'selling price',
            'commission', 'sales target', 'prospect', 'lead', 'opportunity',
        ],
        self::SKILL_PROJECT => [
            // Bahasa Indonesia
            'proyek', 'project', 'task', 'tugas', 'milestone', 'deadline',
            'anggota tim', 'tim proyek', 'progress', 'kemajuan', 'gantt',
            'manajemen proyek', 'biaya proyek', 'anggaran proyek', 'wbs',
            // English
            'project', 'task', 'milestone', 'deadline', 'team member',
            'project team', 'progress', 'gantt chart', 'project management',
            'project cost', 'project budget', 'work breakdown',
        ],
    ];

    /**
     * Peta modul aktif ke skill yang relevan.
     */
    private const MODULE_SKILL_MAP = [
        'accounting' => self::SKILL_ACCOUNTING,
        'inventory'  => self::SKILL_INVENTORY,
        'hrm'        => self::SKILL_HRM,
        'payroll'    => self::SKILL_HRM,
        'sales'      => self::SKILL_SALES,
        'crm'        => self::SKILL_SALES,
        'project'    => self::SKILL_PROJECT,
    ];

    /**
     * Deteksi skill yang relevan berdasarkan intent pesan dan modul aktif.
     *
     * Mengembalikan array skill names yang aktif, misalnya:
     * ['accounting', 'inventory']
     *
     * @param  string $message       Pesan dari user
     * @param  array  $activeModules Daftar modul aktif tenant
     * @return array<string>         Skill names yang terdeteksi
     *
     * Requirements: 8.1, 8.2, 8.6
     */
    public function detectSkills(string $message, array $activeModules): array
    {
        $skills = [];
        $lower  = mb_strtolower($message);

        // Deteksi skill dari keyword dalam pesan
        foreach (self::SKILL_KEYWORDS as $skill => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($lower, $keyword)) {
                    if (!in_array($skill, $skills, true)) {
                        $skills[] = $skill;
                    }
                    break; // Satu keyword cukup untuk mengaktifkan skill ini
                }
            }
        }

        // Aktifkan skill industri khusus jika modul terkait aktif
        $normalizedModules = array_map('strtolower', $activeModules);
        foreach (self::INDUSTRY_MODULE_SKILL_MAP as $module => $industrySkill) {
            if (in_array($module, $normalizedModules, true)) {
                if (!in_array($industrySkill, $skills, true)) {
                    $skills[] = $industrySkill;
                }
            }
        }

        return $skills;
    }

    /**
     * Bangun system prompt tambahan untuk skill yang aktif.
     *
     * Menyusun terminologi domain yang tepat per skill:
     * - Akuntansi: terminologi akuntansi standar Indonesia
     * - HRM: regulasi ketenagakerjaan Indonesia (UMR, BPJS, PPh 21)
     * - Inventory: metode costing FIFO/Average dari konteks tenant
     *
     * @param  array      $skills  Daftar skill yang aktif
     * @param  ErpContext $context Konteks ERP tenant
     * @return string              System prompt tambahan
     *
     * Requirements: 8.2, 8.3, 8.4, 8.5
     */
    public function buildSkillPrompt(array $skills, ErpContext $context): string
    {
        if (empty($skills)) {
            return '';
        }

        $parts = [];

        foreach ($skills as $skill) {
            $prompt = $this->buildSingleSkillPrompt($skill, $context);
            if ($prompt !== '') {
                $parts[] = $prompt;
            }
        }

        return implode("\n\n", $parts);
    }

    // ─── Private: Per-Skill Prompt Builders ──────────────────────────────────

    private function buildSingleSkillPrompt(string $skill, ErpContext $context): string
    {
        return match ($skill) {
            self::SKILL_ACCOUNTING  => $this->buildAccountingPrompt($context),
            self::SKILL_INVENTORY   => $this->buildInventoryPrompt($context),
            self::SKILL_HRM         => $this->buildHrmPrompt($context),
            self::SKILL_SALES       => $this->buildSalesPrompt($context),
            self::SKILL_PROJECT     => $this->buildProjectPrompt($context),
            self::SKILL_HEALTHCARE  => $this->buildHealthcarePrompt($context),
            self::SKILL_MANUFACTURE => $this->buildManufacturePrompt($context),
            self::SKILL_TELECOM     => $this->buildTelecomPrompt($context),
            default                 => '',
        };
    }

    /**
     * Prompt untuk skill Akuntansi & Keuangan.
     * Menggunakan terminologi akuntansi standar Indonesia.
     *
     * Requirements: 8.3
     */
    private function buildAccountingPrompt(ErpContext $context): string
    {
        $period = $context->accountingPeriod ?? 'periode berjalan';

        return <<<PROMPT
        [SKILL: Akuntansi & Keuangan]
        Gunakan terminologi akuntansi standar Indonesia dalam setiap respons:
        - Debit/Kredit untuk pencatatan jurnal
        - Neraca (Balance Sheet), Laba Rugi (Income Statement), Arus Kas (Cash Flow)
        - Jurnal Umum, Buku Besar, Neraca Saldo (Trial Balance)
        - Piutang Usaha (Accounts Receivable), Hutang Usaha (Accounts Payable)
        - Harga Pokok Penjualan (HPP / COGS)
        - Pajak: PPN (Pajak Pertambahan Nilai), PPh (Pajak Penghasilan)
        - Periode akuntansi aktif: {$period}
        Pastikan setiap saran jurnal mengikuti prinsip double-entry bookkeeping.
        PROMPT;
    }

    /**
     * Prompt untuk skill Inventory & Gudang.
     * Menggunakan metode costing dari konteks tenant.
     *
     * Requirements: 8.5
     */
    private function buildInventoryPrompt(ErpContext $context): string
    {
        // Deteksi metode costing dari industrySkills atau kpiSummary
        $costingMethod = $this->detectCostingMethod($context);

        return <<<PROMPT
        [SKILL: Inventory & Gudang]
        Metode costing yang digunakan tenant: {$costingMethod}
        Gunakan metode {$costingMethod} sebagai dasar analisis nilai stok dan HPP.
        Terminologi yang relevan:
        - Stok Minimum (Reorder Point), Stok Kritis, Stok Opname
        - Transfer Antar Gudang, Penyesuaian Stok (Stock Adjustment)
        - Penerimaan Barang (Goods Receipt), Pengiriman Barang (Goods Issue)
        - Lot/Batch Tracking, Barcode/QR Code
        - Landed Cost (biaya pengiriman yang dibebankan ke nilai stok)
        Selalu pertimbangkan dampak perubahan stok terhadap nilai HPP dengan metode {$costingMethod}.
        PROMPT;
    }

    /**
     * Prompt untuk skill HRM & Payroll.
     * Menggunakan regulasi ketenagakerjaan Indonesia.
     *
     * Requirements: 8.4
     */
    private function buildHrmPrompt(ErpContext $context): string
    {
        return <<<PROMPT
        [SKILL: HRM & Payroll]
        Gunakan regulasi ketenagakerjaan Indonesia yang berlaku:
        - UMR/UMK: Upah Minimum Regional/Kota sebagai batas minimum gaji
        - BPJS Ketenagakerjaan: JHT (3.7% karyawan + 3.7% perusahaan), JP (1% karyawan + 2% perusahaan), JKK, JKM
        - BPJS Kesehatan: 1% karyawan + 4% perusahaan dari gaji (maks. batas atas)
        - PPh 21: Pajak penghasilan karyawan berdasarkan PTKP dan tarif progresif
        - PTKP: TK/0 = Rp 54.000.000/tahun, K/0 = Rp 58.500.000/tahun, dst.
        - Lembur: 1.5x upah per jam untuk jam pertama, 2x untuk jam berikutnya
        - Pesangon: sesuai UU Cipta Kerja (Perpu No. 2/2022)
        Selalu referensikan regulasi yang berlaku saat memberikan rekomendasi terkait penggajian.
        PROMPT;
    }

    /**
     * Prompt untuk skill Penjualan & CRM.
     */
    private function buildSalesPrompt(ErpContext $context): string
    {
        return <<<PROMPT
        [SKILL: Penjualan & CRM]
        Terminologi yang relevan:
        - Siklus penjualan: Prospek → Penawaran (Quotation) → Sales Order → Delivery Order → Invoice → Pembayaran
        - Piutang Usaha (AR): invoice yang belum dibayar
        - Retur Penjualan: pengembalian barang dari pelanggan
        - Komisi Sales: persentase dari nilai penjualan yang berhasil
        - Pipeline CRM: tahapan prospek hingga closing
        - Daftar Harga (Price List): harga per segmen pelanggan atau kuantitas
        Analisis penjualan harus mempertimbangkan tren, seasonality, dan perbandingan periode.
        PROMPT;
    }

    /**
     * Prompt untuk skill Project Management.
     */
    private function buildProjectPrompt(ErpContext $context): string
    {
        return <<<PROMPT
        [SKILL: Project Management]
        Terminologi yang relevan:
        - WBS (Work Breakdown Structure): dekomposisi pekerjaan proyek
        - Milestone: titik pencapaian penting dalam proyek
        - Gantt Chart: visualisasi jadwal proyek
        - Baseline: rencana awal proyek sebagai acuan perbandingan
        - Earned Value: nilai pekerjaan yang sudah diselesaikan
        - Critical Path: jalur terpanjang yang menentukan durasi proyek
        - RAB (Rencana Anggaran Biaya): estimasi biaya proyek
        Selalu pertimbangkan ketergantungan antar task dan dampak keterlambatan terhadap milestone.
        PROMPT;
    }

    /**
     * Prompt untuk skill Healthcare (modul industri khusus).
     *
     * Requirements: 8.6
     */
    private function buildHealthcarePrompt(ErpContext $context): string
    {
        return <<<PROMPT
        [SKILL: Healthcare]
        Terminologi dan regulasi yang relevan untuk industri kesehatan:
        - BPJS Kesehatan: klaim, kapitasi, INA-CBGs
        - Rekam Medis: SOAP (Subjective, Objective, Assessment, Plan)
        - Farmasi: resep, dispensing, stok obat, expired date, FIFO ketat
        - Rawat Inap / Rawat Jalan / IGD: alur pasien dan billing
        - Regulasi: Permenkes, akreditasi KARS/JCI
        - Kode ICD-10: diagnosis penyakit standar internasional
        Pastikan setiap rekomendasi mempertimbangkan aspek kepatuhan regulasi kesehatan.
        PROMPT;
    }

    /**
     * Prompt untuk skill Manufaktur (modul industri khusus).
     *
     * Requirements: 8.6
     */
    private function buildManufacturePrompt(ErpContext $context): string
    {
        return <<<PROMPT
        [SKILL: Manufaktur]
        Terminologi yang relevan untuk industri manufaktur:
        - BOM (Bill of Materials): daftar bahan baku untuk satu unit produk
        - Work Order / Production Order: perintah produksi
        - Routing: urutan proses produksi di setiap work center
        - WIP (Work In Progress): barang dalam proses produksi
        - Kapasitas Produksi: output maksimal per periode
        - OEE (Overall Equipment Effectiveness): efisiensi mesin
        - MRP (Material Requirements Planning): perencanaan kebutuhan material
        - Scrap / Waste: sisa produksi yang tidak terpakai
        Analisis produksi harus mempertimbangkan efisiensi, kapasitas, dan biaya per unit.
        PROMPT;
    }

    /**
     * Prompt untuk skill Telecom/ISP (modul industri khusus).
     *
     * Requirements: 8.6
     */
    private function buildTelecomPrompt(ErpContext $context): string
    {
        return <<<PROMPT
        [SKILL: Telecom/ISP]
        Terminologi yang relevan untuk industri telekomunikasi/ISP:
        - Paket Internet: bandwidth, kuota, FUP (Fair Usage Policy)
        - Pelanggan: aktivasi, suspend, terminasi layanan
        - Invoice Berulang (Recurring Invoice): tagihan bulanan otomatis
        - NAS (Network Access Server): perangkat autentikasi jaringan
        - Radius: protokol autentikasi pelanggan
        - SLA (Service Level Agreement): jaminan uptime layanan
        - Churn Rate: persentase pelanggan yang berhenti berlangganan
        Analisis harus mempertimbangkan ARPU (Average Revenue Per User) dan churn.
        PROMPT;
    }

    // ─── Private: Helpers ─────────────────────────────────────────────────────

    /**
     * Deteksi metode costing dari konteks tenant.
     * Default ke FIFO jika tidak dapat dideteksi.
     */
    private function detectCostingMethod(ErpContext $context): string
    {
        // Cek dari industrySkills jika ada informasi costing
        foreach ($context->industrySkills as $skill) {
            $lower = strtolower($skill);
            if (str_contains($lower, 'average')) {
                return 'Average';
            }
            if (str_contains($lower, 'fifo')) {
                return 'FIFO';
            }
        }

        // Cek dari kpiSummary jika ada field costing_method
        if (isset($context->kpiSummary['costing_method'])) {
            $method = strtoupper($context->kpiSummary['costing_method']);
            if (in_array($method, ['FIFO', 'AVERAGE'], true)) {
                return $method === 'AVERAGE' ? 'Average' : 'FIFO';
            }
        }

        // Default ke FIFO (metode yang paling umum digunakan)
        return 'FIFO';
    }
}
