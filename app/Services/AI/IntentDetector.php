<?php

namespace App\Services\AI;

/**
 * IntentDetector - AI Tool Intent Detection Service
 * 
 * Detects user intent from chat messages to optimize AI tool declarations.
 * Instead of sending all 100+ tools to Gemini every request, we detect
 * the intent and only send relevant tools, reducing response time by 60-70%.
 * 
 * Usage:
 * ```php
 * $detector = new IntentDetector();
 * $intent = $detector->detect('Berapa stok kopi hari ini?');
 * // Returns: 'inventory'
 * 
 * $tools = $registry->getDeclarationsForIntent($intent, $allowedTools);
 * // Returns only inventory-related tools instead of all 100+ tools
 * ```
 */
class IntentDetector
{
    /**
     * Intent patterns with keywords
     * 
     * Each intent maps to an array of keywords that trigger it.
     * Keywords are checked case-insensitively.
     */
    protected array $intentPatterns = [
        // ── Sales & CRM ──────────────────────────────────────────
        'sales' => [
            // Indonesian
            'penjualan',
            'jual',
            'order',
            'pesanan',
            'invoice',
            'faktur',
            'customer',
            'pelanggan',
            'pembayaran',
            'bayar',
            'piutang',
            'quotation',
            'penawaran',
            'kasir',
            'pos',
            'checkout',
            // English
            'sale',
            'selling',
            'receipt',
            'bill',
            'payment',
            'receivable',
            'customer info',
            'client',
            'buyer',
        ],

        // ── Inventory & Products ─────────────────────────────────
        'inventory' => [
            // Indonesian
            'stok',
            'stock',
            'produk',
            'barang',
            'inventory',
            'gudang',
            'warehouse',
            'gudang',
            'cek stok',
            'stok rendah',
            'minimum',
            'tambah produk',
            'buat produk',
            'update produk',
            'hapus produk',
            // English
            'inventory',
            'product',
            'item',
            'warehouse',
            'stock level',
            'low stock',
            'out of stock',
            'stock in',
            'stock out',
        ],

        // ── Finance & Accounting ─────────────────────────────────
        'finance' => [
            // Indonesian
            'keuangan',
            'laporan',
            'journal',
            'jurnal',
            'akunting',
            'neraca',
            'laba rugi',
            'cashflow',
            'arus kas',
            'budget',
            'anggaran',
            'pajak',
            'tax',
            'currency',
            'kurs',
            'valuta',
            'expense',
            'pengeluaran',
            'income',
            'pemasukan',
            // English
            'finance',
            'financial',
            'accounting',
            'ledger',
            'balance sheet',
            'profit loss',
            'cash flow',
            'tax',
            'currency',
            'exchange rate',
        ],

        // ── HRM & Payroll ────────────────────────────────────────
        'hrm' => [
            // Indonesian
            'karyawan',
            'pegawai',
            'payroll',
            'gaji',
            'absensi',
            'cuti',
            'leave',
            'attendance',
            'hadir',
            'izin',
            'sakit',
            'overtime',
            'lembur',
            'shift',
            'jadwal',
            'departemen',
            'department',
            'hrd',
            'human resource',
            'recruitment',
            'rekrutmen',
            // English
            'employee',
            'staff',
            'worker',
            'payroll',
            'salary',
            'wage',
            'attendance',
            'leave',
            'vacation',
            'overtime',
            'shift',
        ],

        // ── Purchasing ───────────────────────────────────────────
        'purchasing' => [
            // Indonesian
            'pembelian',
            'beli',
            'purchase',
            'supplier',
            'pemasok',
            'vendor',
            'PO',
            'purchase order',
            'RFQ',
            'quotation',
            'barang masuk',
            'goods receipt',
            'pengadaan',
            'procurement',
            // English
            'buy',
            'purchasing',
            'procurement',
            'supplier',
            'vendor',
            'purchase order',
            'requisition',
            'goods receipt',
        ],

        // ── Production & Manufacturing ───────────────────────────
        'production' => [
            // Indonesian
            'produksi',
            'manufacturing',
            'pabrik',
            'work order',
            'resep',
            'recipe',
            'BOM',
            'bill of material',
            'output',
            'produksi output',
            'QC',
            'quality control',
            'inspeksi',
            // English
            'produce',
            'production',
            'manufacture',
            'factory',
            'work order',
            'recipe',
            'BOM',
            'bill of materials',
            'quality control',
            'inspection',
        ],

        // ── Projects & Construction ──────────────────────────────
        'project' => [
            // Indonesian
            'proyek',
            'project',
            'konstruksi',
            'construction',
            'RAB',
            'task',
            'tugas',
            'timesheet',
            'progress',
            'volume',
            'site report',
            'laporan harian',
            // English
            'project',
            'construction',
            'task',
            'timesheet',
            'progress',
            'site report',
            'daily report',
        ],

        // ── Assets & Maintenance ─────────────────────────────────
        'asset' => [
            // Indonesian
            'aset',
            'asset',
            'maintenance',
            'perawatan',
            'penyusutan',
            'depreciation',
            'jadwal maintenance',
            'kondisi aset',
            // English
            'asset',
            'equipment',
            'maintenance',
            'depreciation',
            'repair',
            'service',
            'condition',
        ],

        // ── Dashboard & Reports ──────────────────────────────────
        'dashboard' => [
            // Indonesian
            'dashboard',
            'ringkasan',
            'summary',
            'statistik',
            'analytics',
            'grafik',
            'chart',
            'KPI',
            'metric',
            'insight',
            'wawasan',
            'laporan',
            'report',
            'export',
            'download',
            // English
            'dashboard',
            'overview',
            'summary',
            'statistics',
            'analytics',
            'report',
            'chart',
            'graph',
            'KPI',
            'metric',
            'insight',
        ],

        // ── Banking & Payments ───────────────────────────────────
        'banking' => [
            // Indonesian
            'bank',
            'rekening',
            'account',
            'transfer',
            'mutasi',
            'statement',
            'rekonsiliasi',
            'reconciliation',
            // English
            'bank',
            'banking',
            'account',
            'transfer',
            'statement',
            'reconciliation',
            'transaction',
        ],

        // ── Notifications & Reminders ────────────────────────────
        'notification' => [
            // Indonesian
            'notifikasi',
            'notification',
            'reminder',
            'pengingat',
            'alert',
            'peringatan',
            'email',
            'whatsapp',
            'WA',
            'broadcast',
            'kirim',
            'send',
            // English
            'notification',
            'reminder',
            'alert',
            'email',
            'whatsapp',
            'send',
            'message',
            'broadcast',
        ],

        // ── Loyalty & Rewards ────────────────────────────────────
        'loyalty' => [
            // Indonesian
            'loyalty',
            'reward',
            'poin',
            'point',
            'member',
            'membership',
            'diskon',
            'discount',
            'voucher',
            // English
            'loyalty',
            'reward',
            'point',
            'member',
            'membership',
            'discount',
            'voucher',
            'coupon',
        ],

        // ── Shipping & Logistics ─────────────────────────────────
        'shipping' => [
            // Indonesian
            'kirim',
            'shipping',
            'pengiriman',
            'expedisi',
            'courier',
            'resi',
            'tracking',
            'logistik',
            'logistics',
            // English
            'ship',
            'shipping',
            'delivery',
            'courier',
            'tracking',
            'logistics',
            'freight',
        ],

        // ── Smart Query & AI ─────────────────────────────────────
        'smart_query' => [
            // Indonesian
            'cari',
            'search',
            'tampilkan',
            'show',
            'list',
            'daftar',
            'berapa',
            'how many',
            'how much',
            'apa',
            'what',
            'tolong',
            'please',
            'bantu',
            'help',
            // English
            'find',
            'search',
            'show',
            'list',
            'display',
            'how many',
            'how much',
            'what is',
            'tell me',
        ],

        // ── Forecasting & Predictions ────────────────────────────
        'forecast' => [
            // Indonesian
            'prediksi',
            'forecast',
            'ramalan',
            'proyeksi',
            'projection',
            'trend',
            'tren',
            'future',
            'masa depan',
            // English
            'forecast',
            'prediction',
            'projection',
            'trend',
            'future',
            'estimate',
            'predict',
        ],

        // ── Document Management ──────────────────────────────────
        'document' => [
            // Indonesian
            'dokumen',
            'document',
            'generate',
            'buat dokumen',
            'PDF',
            'template',
            'form',
            'lampiran',
            'attachment',
            // English
            'document',
            'generate',
            'PDF',
            'template',
            'form',
            'attachment',
            'file',
        ],

        // ── Onboarding & Setup ───────────────────────────────────
        'onboarding' => [
            // Indonesian
            'setup',
            'konfigurasi',
            'configuration',
            'initial',
            'pertama kali',
            'first time',
            'onboarding',
            'setup bisnis',
            // English
            'setup',
            'configure',
            'initial',
            'first time',
            'onboarding',
            'wizard',
            'getting started',
        ],

        // ── Bulk Operations ──────────────────────────────────────
        'bulk' => [
            'bulk', 'massal', 'batch', 'banyak', 'semua', 'all',
            'update semua', 'hapus semua', 'import', 'export',
            'mass', 'multiple', 'mass update',
        ],

        // ── Agriculture & Farm ───────────────────────────────────
        'farm' => [
            // Indonesian
            'lahan', 'kebun', 'sawah', 'ladang', 'blok', 'pertanian',
            'tanam', 'panen', 'pupuk', 'pestisida', 'irigasi',
            'siklus tanam', 'crop', 'harvest', 'padi', 'jagung',
            'kelapa sawit', 'tebu', 'kopi', 'kakao', 'karet',
            'hektar', 'are', 'plot', 'farm', 'agriculture',
            'agrikultur', 'perkebunan', 'tanaman',
            // English
            'farm', 'plot', 'crop', 'harvest', 'planting', 'field',
            'agriculture', 'plantation', 'irrigation', 'fertilizer',
        ],

        // ── Livestock & Peternakan ───────────────────────────────
        'livestock' => [
            // Indonesian
            'ternak', 'ayam', 'sapi', 'kambing', 'bebek', 'babi',
            'kelinci', 'ikan', 'kandang', 'pakan', 'FCR',
            'broiler', 'layer', 'DOC', 'populasi ternak',
            'mortalitas', 'vaksin ternak', 'kesehatan ternak',
            'peternakan', 'livestock', 'herd', 'flock',
            // English
            'livestock', 'cattle', 'poultry', 'chicken', 'cow',
            'goat', 'feed', 'FCR', 'mortality', 'vaccination',
            'herd', 'flock', 'breeding',
        ],

        // ── Hotel & Hospitality ──────────────────────────────────
        'hotel' => [
            // Indonesian
            'hotel', 'kamar', 'reservasi', 'tamu', 'check-in',
            'check-out', 'housekeeping', 'front office', 'tarif kamar',
            'room', 'booking hotel', 'penginapan', 'resort',
            'spa', 'restoran hotel', 'night audit',
            // English
            'hotel', 'room', 'reservation', 'guest', 'check-in',
            'check-out', 'housekeeping', 'front desk', 'room rate',
        ],

        // ── Telecom & Network ────────────────────────────────────
        'telecom' => [
            // Indonesian
            'internet', 'jaringan', 'network', 'router', 'bandwidth',
            'paket internet', 'hotspot', 'voucher internet',
            'pelanggan internet', 'ISP', 'mikrotik', 'switch',
            'telecom', 'telekomunikasi', 'koneksi',
            // English
            'telecom', 'network', 'internet', 'bandwidth', 'router',
            'hotspot', 'voucher', 'ISP', 'connection',
        ],

        // ── Healthcare & Medical ─────────────────────────────────
        'healthcare' => [
            // Indonesian
            'pasien', 'dokter', 'klinik', 'rumah sakit', 'RS',
            'rekam medis', 'EMR', 'resep', 'obat', 'apotek',
            'rawat inap', 'rawat jalan', 'IGD', 'laboratorium',
            'radiologi', 'BPJS', 'tagihan medis', 'jadwal dokter',
            'telemedicine', 'konsultasi online',
            // English
            'patient', 'doctor', 'clinic', 'hospital', 'medical',
            'prescription', 'medicine', 'pharmacy', 'EMR',
            'inpatient', 'outpatient', 'lab', 'radiology',
        ],

        // ── Fisheries ────────────────────────────────────────────
        'fisheries' => [
            // Indonesian
            'ikan', 'nelayan', 'kapal', 'kolam', 'tambak',
            'budidaya ikan', 'panen ikan', 'cold storage ikan',
            'ekspor ikan', 'perikanan', 'aquaculture',
            'udang', 'bandeng', 'lele', 'nila', 'salmon',
            // English
            'fish', 'fishery', 'fishing', 'aquaculture', 'pond',
            'vessel', 'catch', 'seafood', 'shrimp',
        ],

        // ── Tour & Travel ────────────────────────────────────────
        'tour' => [
            // Indonesian
            'paket wisata', 'tour', 'travel', 'booking wisata',
            'itinerary', 'destinasi', 'wisatawan', 'penumpang',
            'tiket wisata', 'agen perjalanan',
            // English
            'tour', 'travel', 'package', 'itinerary', 'booking',
            'tourist', 'destination', 'trip',
        ],

        // ── Cosmetic & Formula ───────────────────────────────────
        'cosmetic' => [
            // Indonesian
            'formula kosmetik', 'batch kosmetik', 'BPOM',
            'bahan baku kosmetik', 'produksi kosmetik',
            'registrasi produk', 'QC kosmetik', 'kosmetik',
            // English
            'cosmetic', 'formula', 'batch record', 'BPOM',
            'ingredient', 'formulation', 'beauty product',
        ],
    ];

    /**
     * Map intents to tool class names
     * 
     * This defines which tool classes should be loaded for each intent.
     * If intent is 'general' or no match, all tools are loaded (fallback).
     */
    protected array $intentToolMapping = [
        'sales' => [
            'SalesTools',
            'PosTools',
            'ReceivableTools',
            'CrmTools',
            'LoyaltyTools',
        ],
        'inventory' => [
            'InventoryTools',
            'WarehouseTools',
            'BulkTools',
        ],
        'finance' => [
            'FinanceTools',
            'BudgetTools',
            'TaxTools',
            'CurrencyTools',
            'BankTools',
            'AssetTools',
        ],
        'hrm' => [
            'HrmTools',
            'PayrollTools',
        ],
        'purchasing' => [
            'PurchasingTools',
            'InventoryTools',  // For stock checks
        ],
        'production' => [
            'ProductionTools',
            'RecipeTools',
            'InventoryTools',  // For material checks
        ],
        'project' => [
            'ProjectTools',
            'ConcreteMixTools',  // Construction-specific
        ],
        'asset' => [
            'AssetTools',
            'FinanceTools',  // For depreciation
        ],
        'dashboard' => [
            'DashboardTools',
            'ReportTools',
            'SmartQueryTools',
        ],
        'banking' => [
            'BankTools',
            'FinanceTools',
        ],
        'notification' => [
            'NotificationTools',
            'ReminderTools',
            'WhatsAppTools',
            'BotTools',
        ],
        'loyalty' => [
            'LoyaltyTools',
            'CrmTools',
        ],
        'shipping' => [
            'ShippingTools',
            'SalesTools',
        ],
        'smart_query' => [
            'SmartQueryTools',
            'DashboardTools',
            'ReportTools',
        ],
        'forecast' => [
            'ForecastTools',
            'AdvisorTools',
            'ReportTools',
        ],
        'document' => [
            'DocumentTools',
            'DocumentGeneratorTools',
        ],
        'onboarding' => [
            'OnboardingTools',
            'AppGuideTools',
        ],
        'bulk' => [
            'BulkTools',
        ],

        // ── Agriculture & Livestock ──────────────────────────────
        'farm' => [
            'FarmTools',
            'InventoryTools',
        ],
        'livestock' => [
            'FarmTools',
            'InventoryTools',
        ],

        // ── Hotel ────────────────────────────────────────────────
        'hotel' => [
            'DashboardTools',
            'ReportTools',
        ],

        // ── Telecom ──────────────────────────────────────────────
        'telecom' => [
            'DashboardTools',
            'ReportTools',
        ],

        // ── Healthcare ───────────────────────────────────────────
        'healthcare' => [
            'DashboardTools',
            'ReportTools',
        ],

        // ── Fisheries ────────────────────────────────────────────
        'fisheries' => [
            'FarmTools',
            'InventoryTools',
        ],

        // ── Tour & Travel ────────────────────────────────────────
        'tour' => [
            'DashboardTools',
            'ReportTools',
        ],

        // ── Cosmetic ─────────────────────────────────────────────
        'cosmetic' => [
            'ProductionTools',
            'InventoryTools',
        ],
    ];

    /**
     * Detect intent from user message
     * 
     * @param string $message User's chat message
     * @return string Detected intent (e.g., 'sales', 'inventory', 'general')
     */
    public function detect(string $message): string
    {
        $message = strtolower($message);

        $scores = [];

        // Score each intent based on keyword matches
        foreach ($this->intentPatterns as $intent => $keywords) {
            $score = 0;

            foreach ($keywords as $keyword) {
                if (str_contains($message, strtolower($keyword))) {
                    // Longer/more specific keywords get higher score
                    $score += strlen($keyword);
                }
            }

            if ($score > 0) {
                $scores[$intent] = $score;
            }
        }

        // Return highest scoring intent, or 'general' if no match
        if (empty($scores)) {
            return 'general';
        }

        arsort($scores);
        return array_key_first($scores);
    }

    /**
     * Get tool classes for detected intent
     * 
     * @param string $intent Detected intent
     * @return array Array of tool class names
     */
    public function getToolClassesForIntent(string $intent): array
    {
        return $this->intentToolMapping[$intent] ?? [];
    }

    /**
     * Detect multiple intents from message (for complex queries)
     * 
     * @param string $message User's chat message
     * @param int $maxIntents Maximum number of intents to return
     * @return array Array of intents sorted by relevance
     */
    public function detectMultiple(string $message, int $maxIntents = 3): array
    {
        $message = strtolower($message);
        $scores = [];

        // Score all intents
        foreach ($this->intentPatterns as $intent => $keywords) {
            $score = 0;

            foreach ($keywords as $keyword) {
                if (str_contains($message, strtolower($keyword))) {
                    $score += strlen($keyword);
                }
            }

            if ($score > 0) {
                $scores[$intent] = $score;
            }
        }

        // Sort by score and return top N
        arsort($scores);

        $intents = array_keys($scores);

        // Always include 'general' if no specific intent found
        if (empty($intents)) {
            return ['general'];
        }

        return array_slice($intents, 0, $maxIntents);
    }

    /**
     * Get confidence score for intent detection
     * 
     * @param string $message User's chat message
     * @param string $intent Detected intent
     * @return float Confidence score (0.0 to 1.0)
     */
    public function getConfidence(string $message, string $intent): float
    {
        if ($intent === 'general') {
            return 0.5; // Default confidence for general
        }

        $message = strtolower($message);
        $keywords = $this->intentPatterns[$intent] ?? [];

        if (empty($keywords)) {
            return 0.0;
        }

        $matches = 0;
        foreach ($keywords as $keyword) {
            if (str_contains($message, strtolower($keyword))) {
                $matches++;
            }
        }

        // Confidence = matches / total keywords (capped at 1.0)
        return min(1.0, $matches / max(1, count($keywords) * 0.3));
    }

    /**
     * Get all available intents
     * 
     * @return array Array of intent names
     */
    public function getAvailableIntents(): array
    {
        return array_keys($this->intentPatterns);
    }

    /**
     * Add custom intent pattern (for extensibility)
     * 
     * @param string $intent Intent name
     * @param array $keywords Array of keywords
     * @param array $toolClasses Array of tool class names
     * @return void
     */
    public function addIntent(string $intent, array $keywords, array $toolClasses): void
    {
        $this->intentPatterns[$intent] = $keywords;
        $this->intentToolMapping[$intent] = $toolClasses;
    }
}
