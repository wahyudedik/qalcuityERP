<?php

/**
 * Bank Format Configuration
 *
 * Konfigurasi format CSV untuk berbagai bank di Indonesia
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Supported Bank Formats
    |--------------------------------------------------------------------------
    |
    | Konfigurasi format CSV untuk masing-masing bank.
    | Setiap bank memiliki struktur CSV yang berbeda-beda.
    |
    */

    'banks' => [

        'bca' => [
            'name' => 'BCA KlikBCA',
            'description' => 'Format CSV dari BCA KlikBCA',
            'headers' => ['Tanggal', 'Keterangan', 'Jumlah', 'Saldo'],
            'date_format' => 'd/m/Y',
            'amount_is_signed' => true, // Negative = Debit, Positive = Credit
            'delimiter' => ',',
            'encoding' => 'UTF-8',
            'sample_file' => 'samples/bca_sample.csv',
            'notes' => 'Jumlah negatif = uang keluar (debit), positif = uang masuk (credit)',
        ],

        'mandiri' => [
            'name' => 'Mandiri Corporate Internet Banking',
            'description' => 'Format CSV dari Mandiri CIB',
            'headers' => ['Tanggal', 'Uraian', 'Debit', 'Kredit', 'Saldo'],
            'date_format' => 'd-m-Y',
            'amount_is_signed' => false,
            'delimiter' => ',',
            'encoding' => 'UTF-8',
            'sample_file' => 'samples/mandiri_sample.csv',
            'notes' => 'Kolom Debit dan Kredit terpisah',
        ],

        'bni' => [
            'name' => 'BNI Online Banking',
            'description' => 'Format CSV dari BNI',
            'headers' => ['Tanggal', 'Deskripsi', 'Jumlah', 'Tipe', 'Saldo'],
            'date_format' => 'Y-m-d',
            'amount_is_signed' => false,
            'delimiter' => ',',
            'encoding' => 'UTF-8',
            'sample_file' => 'samples/bni_sample.csv',
            'notes' => 'Tipe: Debit/Credit',
        ],

        'bri' => [
            'name' => 'BRI Internet Banking',
            'description' => 'Format CSV dari BRI',
            'headers' => ['Tanggal', 'Uraian', 'Debit', 'Kredit', 'Saldo'],
            'date_format' => 'd/m/Y',
            'amount_is_signed' => false,
            'delimiter' => ',',
            'encoding' => 'UTF-8',
            'sample_file' => 'samples/bri_sample.csv',
            'notes' => 'Kolom Debit dan Kredit terpisah',
        ],

        'generic' => [
            'name' => 'Generic/Universal',
            'description' => 'Format universal untuk bank lainnya',
            'headers' => ['Tanggal', 'Deskripsi', 'Tipe', 'Jumlah'],
            'date_format' => 'auto',
            'amount_is_signed' => false,
            'delimiter' => 'auto',
            'encoding' => 'UTF-8',
            'sample_file' => 'samples/generic_sample.csv',
            'notes' => 'Auto-detect format, support berbagai delimiter',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Parsing Options
    |--------------------------------------------------------------------------
    */

    'options' => [

        // Max file size (bytes) - default 10MB
        'max_file_size' => 10 * 1024 * 1024,

        // Allowed file extensions
        'allowed_extensions' => ['csv', 'txt'],

        // Skip empty rows
        'skip_empty_rows' => true,

        // Trim whitespace from values
        'trim_values' => true,

        // Minimum columns required
        'min_columns' => 3,

        // Date formats to try for auto-detection
        'auto_date_formats' => [
            'Y-m-d',
            'd/m/Y',
            'd-m-Y',
            'm/d/Y',
            'Y/m/d',
            'd/m/y',
            'd-m-y',
        ],

        // Amount parsing options
        'amount' => [
            // Remove these characters
            'remove_chars' => ['Rp', 'rp', 'RP', '$', ' ', "\t"],

            // Handle parentheses for negative: (1000) => -1000
            'handle_parentheses' => true,

            // Decimal precision
            'precision' => 2,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Type Detection Keywords
    |--------------------------------------------------------------------------
    |
    | Keywords untuk mendeteksi tipe transaksi (Debit/Credit)
    |
    */

    'type_keywords' => [
        'debit' => [
            'debit',
            'db',
            'debet',
            'keluar',
            'pengeluaran',
            'dr',
            'withdrawal',
            'transfer keluar',
            'pembayaran',
        ],
        'credit' => [
            'credit',
            'cr',
            'kredit',
            'masuk',
            'penerimaan',
            'setoran',
            'cr',
            'deposit',
            'transfer masuk',
            'pembayaran dari',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Common Bank Description Patterns
    |--------------------------------------------------------------------------
    |
    | Pattern umum dari deskripsi transaksi bank untuk auto-categorization
    |
    */

    'description_patterns' => [

        // Transfer
        'transfer' => [
            'pattern' => '/transfer\s*(ke|dari|antar)/i',
            'category' => 'transfer',
        ],

        // Salary
        'salary' => [
            'pattern' => '/(gaji|salary|payroll|upah)/i',
            'category' => 'salary',
        ],

        // Rent
        'rent' => [
            'pattern' => '/(sewa|rent|rental)/i',
            'category' => 'rent',
        ],

        // Utilities
        'utilities' => [
            'pattern' => '/(listrik|air|telepon|internet|utilities|pln|pdam)/i',
            'category' => 'utilities',
        ],

        // Bank fees
        'bank_fee' => [
            'pattern' => '/(biaya admin|admin fee|provisi|materai)/i',
            'category' => 'bank_fee',
        ],

        // Sales
        'sales' => [
            'pattern' => '/(penjualan|sales|invoice|pembayaran dari)/i',
            'category' => 'sales',
        ],

        // Interest
        'interest' => [
            'pattern' => '/(bunga|interest|jasa giro)/i',
            'category' => 'interest',
        ],

    ],

];
