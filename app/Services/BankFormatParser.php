<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * BankFormatParser - Handle parsing CSV mutasi rekening dari berbagai bank Indonesia
 *
 * Supported banks:
 * - BCA (KlikBCA)
 * - Mandiri (Corporate Internet Banking)
 * - BNI (BNI Online Banking)
 * - BRI (BRI Internet Banking)
 * - Generic/Universal CSV format
 */
class BankFormatParser
{
    /**
     * Bank format configurations
     */
    private array $bankFormats = [
        'bca' => [
            'name' => 'BCA KlikBCA',
            'headers' => ['tanggal', 'keterangan', 'jumlah', 'saldo'],
            'header_patterns' => ['tanggal', 'mutasi', 'bcaklik'],
            'date_format' => 'd/m/Y',
            'columns' => [
                'transaction_date' => 0,
                'description' => 1,
                'amount' => 2,
                'balance' => 3,
            ],
            'amount_is_signed' => true, // Negative = debit, Positive = credit
            'encoding' => 'UTF-8',
            'delimiter' => ',',
            'skip_rows' => 0,
        ],
        'mandiri' => [
            'name' => 'Mandani Corporate Internet Banking',
            'headers' => ['tanggal', 'uraian', 'debit', 'kredit', 'saldo'],
            'header_patterns' => ['tanggal', 'uraian', 'mandiri'],
            'date_format' => 'd-m-Y',
            'columns' => [
                'transaction_date' => 0,
                'description' => 1,
                'debit' => 2,
                'credit' => 3,
                'balance' => 4,
            ],
            'amount_is_signed' => false,
            'encoding' => 'UTF-8',
            'delimiter' => ',',
            'skip_rows' => 0,
        ],
        'bni' => [
            'name' => 'BNI Online Banking',
            'headers' => ['tanggal', 'deskripsi', 'jumlah', 'tipe', 'saldo'],
            'header_patterns' => ['tanggal', 'deskripsi', 'bni'],
            'date_format' => 'Y-m-d',
            'columns' => [
                'transaction_date' => 0,
                'description' => 1,
                'amount' => 2,
                'type' => 3,
                'balance' => 4,
            ],
            'amount_is_signed' => false,
            'encoding' => 'UTF-8',
            'delimiter' => ',',
            'skip_rows' => 0,
        ],
        'bri' => [
            'name' => 'BRI Internet Banking',
            'headers' => ['tanggal', 'uraian', 'debit', 'kredit', 'saldo'],
            'header_patterns' => ['tanggal', 'uraian', 'bri'],
            'date_format' => 'd/m/Y',
            'columns' => [
                'transaction_date' => 0,
                'description' => 1,
                'debit' => 2,
                'credit' => 3,
                'balance' => 4,
            ],
            'amount_is_signed' => false,
            'encoding' => 'UTF-8',
            'delimiter' => ',',
            'skip_rows' => 0,
        ],
        'generic' => [
            'name' => 'Generic/Universal Format',
            'headers' => ['tanggal', 'deskripsi', 'tipe', 'jumlah'],
            'header_patterns' => [],
            'date_format' => 'auto', // Auto-detect
            'columns' => [
                'transaction_date' => 0,
                'description' => 1,
                'type' => 2,
                'amount' => 3,
            ],
            'amount_is_signed' => false,
            'encoding' => 'UTF-8',
            'delimiter' => 'auto', // Auto-detect
            'skip_rows' => 0,
        ],
    ];

    /**
     * Parse uploaded CSV file and return normalized bank statements
     *
     * @param  string|null  $forcedBank  Force specific bank format (bca, mandiri, bni, bri, generic)
     * @return array Normalized statements
     *
     * @throws \Exception
     */
    public function parse(UploadedFile $file, ?string $forcedBank = null): array
    {
        try {
            // Validate file
            $this->validateFile($file);

            // Detect or use forced bank format
            $bankKey = $forcedBank ?? $this->detectBankFormat($file);
            $format = $this->bankFormats[$bankKey] ?? $this->bankFormats['generic'];

            $this->logInfo('Parsing bank statement', [
                'bank' => $format['name'],
                'file' => $file->getClientOriginalName(),
            ]);

            // Read file content
            $content = $this->readFileContent($file, $format['encoding']);

            // Detect delimiter if auto
            $delimiter = $format['delimiter'] === 'auto'
                ? $this->detectDelimiter($content)
                : $format['delimiter'];

            // Parse CSV rows
            $rows = $this->parseCsvContent($content, $delimiter);

            if (empty($rows)) {
                throw new \Exception('File CSV kosong atau tidak dapat dibaca');
            }

            // Skip header/metadata rows if needed
            if ($format['skip_rows'] > 0) {
                $rows = array_slice($rows, $format['skip_rows']);
            }

            // Normalize rows to standard format
            $statements = $this->normalizeRows($rows, $format);

            $this->logInfo('Successfully parsed bank statements', [
                'bank' => $format['name'],
                'count' => count($statements),
            ]);

            return $statements;

        } catch (\Exception $e) {
            $this->logError('Failed to parse bank statement', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);
            throw $e;
        }
    }

    /**
     * Get list of supported bank formats
     */
    public function getSupportedBanks(): array
    {
        return collect($this->bankFormats)
            ->map(fn ($format, $key) => [
                'key' => $key,
                'name' => $format['name'],
            ])
            ->values()
            ->toArray();
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file): void
    {
        $allowedMimes = ['csv', 'txt'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $allowedMimes)) {
            throw new \Exception(
                'Format file tidak didukung. Gunakan: '.implode(', ', $allowedMimes)
            );
        }

        // Max 10MB
        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new \Exception('Ukuran file terlalu besar. Maksimal 10MB');
        }
    }

    /**
     * Detect bank format from file content
     */
    private function detectBankFormat(UploadedFile $file): string
    {
        $content = $this->readFileContent($file, 'UTF-8');
        $firstLines = implode(' ', array_slice(explode("\n", $content), 0, 10));
        $firstLinesLower = strtolower($firstLines);

        // Check each bank's header patterns
        foreach ($this->bankFormats as $key => $format) {
            if ($key === 'generic') {
                continue;
            }

            foreach ($format['header_patterns'] as $pattern) {
                if (str_contains($firstLinesLower, strtolower($pattern))) {
                    $this->logInfo('Detected bank format', ['bank' => $format['name']]);

                    return $key;
                }
            }
        }

        // Fallback to generic
        $this->logInfo('Using generic bank format (no specific pattern detected)');

        return 'generic';
    }

    /**
     * Read file content with proper encoding
     */
    private function readFileContent(UploadedFile $file, string $encoding): string
    {
        $content = file_get_contents($file->getRealPath());

        if ($content === false) {
            throw new \Exception('Gagal membaca file');
        }

        // Remove BOM if present
        $content = $this->removeBom($content);

        // Convert encoding if needed
        $currentEncoding = mb_detect_encoding($content, ['UTF-8', 'ASCII', 'Windows-1252', 'ISO-8859-1'], true);
        if ($currentEncoding && $currentEncoding !== $encoding) {
            $content = mb_convert_encoding($content, $encoding, $currentEncoding);
        }

        return $content;
    }

    /**
     * Remove UTF-8 BOM
     */
    private function removeBom(string $content): string
    {
        $bom = "\xEF\xBB\xBF";
        if (str_starts_with($content, $bom)) {
            return substr($content, 3);
        }

        return $content;
    }

    /**
     * Detect CSV delimiter
     */
    private function detectDelimiter(string $content): string
    {
        $firstLine = explode("\n", $content)[0] ?? '';

        $delimiters = [',', ';', "\t", '|'];
        $counts = [];

        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($firstLine, $delimiter);
        }

        // Return delimiter with highest count
        arsort($counts);
        $bestDelimiter = array_key_first($counts);

        $this->logInfo('Detected CSV delimiter', ['delimiter' => $bestDelimiter === "\t" ? 'tab' : $bestDelimiter]);

        return $bestDelimiter;
    }

    /**
     * Parse CSV content into rows
     */
    private function parseCsvContent(string $content, string $delimiter): array
    {
        $rows = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Parse CSV line (handles quoted fields)
            $row = str_getcsv($line, $delimiter, '"', '\\');
            if (! empty($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * Normalize rows to standard format
     *
     * Returns: [
     *   'transaction_date' => 'Y-m-d',
     *   'description' => '...',
     *   'type' => 'debit|credit',
     *   'amount' => 1000.00,
     *   'balance' => 5000.00,
     *   'reference' => '...' (optional)
     * ]
     */
    private function normalizeRows(array $rows, array $format): array
    {
        $statements = [];
        $columns = $format['columns'];

        // Check if first row is header
        $firstRow = $rows[0] ?? [];
        $hasHeader = $this->rowContainsHeaders($firstRow, $format);

        if ($hasHeader) {
            array_shift($rows);
        }

        foreach ($rows as $index => $row) {
            try {
                // Skip empty or incomplete rows
                if (count($row) < 3) {
                    continue;
                }

                // Extract fields based on format
                $date = $this->extractDate($row[$columns['transaction_date']] ?? '', $format);
                if (! $date) {
                    continue; // Skip invalid dates
                }

                $description = trim($row[$columns['description']] ?? '');
                if (empty($description)) {
                    continue; // Skip empty descriptions
                }

                // Determine type and amount
                if ($format['amount_is_signed']) {
                    // Amount column contains signed values (negative = debit, positive = credit)
                    $amountRaw = $this->parseAmount($row[$columns['amount']] ?? '0');
                    $type = $amountRaw < 0 ? 'debit' : 'credit';
                    $amount = abs($amountRaw);
                } else {
                    // Separate debit/credit columns or explicit type column
                    if (isset($columns['type'])) {
                        $typeRaw = strtolower(trim($row[$columns['type']] ?? ''));
                        $type = $this->normalizeType($typeRaw);
                    } elseif (isset($columns['debit']) && isset($columns['credit'])) {
                        $debit = $this->parseAmount($row[$columns['debit']] ?? '0');
                        $credit = $this->parseAmount($row[$columns['credit']] ?? '0');

                        if ($debit > 0) {
                            $type = 'debit';
                            $amount = $debit;
                        } else {
                            $type = 'credit';
                            $amount = $credit;
                        }
                    } else {
                        $type = 'debit'; // Default
                    }

                    $amount = isset($amount) ? $amount : $this->parseAmount($row[$columns['amount'] ?? 2] ?? '0');
                }

                // Skip zero amounts
                if ($amount <= 0) {
                    continue;
                }

                // Extract balance if available
                $balance = isset($columns['balance'])
                    ? $this->parseAmount($row[$columns['balance']] ?? '0')
                    : null;

                // Extract reference if available (from description or separate column)
                $reference = $this->extractReference($description);

                $statements[] = [
                    'transaction_date' => $date,
                    'description' => $description,
                    'type' => $type,
                    'amount' => round($amount, 2),
                    'balance' => $balance !== null ? round($balance, 2) : null,
                    'reference' => $reference,
                    'row_number' => $index + 1,
                ];

            } catch (\Exception $e) {
                $this->logWarning('Failed to parse row', [
                    'row' => $index + 1,
                    'error' => $e->getMessage(),
                    'data' => $row,
                ]);

                continue;
            }
        }

        return $statements;
    }

    /**
     * Check if row contains headers
     */
    private function rowContainsHeaders(array $row, array $format): bool
    {
        if (empty($row)) {
            return false;
        }

        $firstCell = strtolower(trim($row[0] ?? ''));

        // Check if first cell looks like a date
        if ($this->looksLikeDate($firstCell)) {
            return false; // First row is data, not header
        }

        // Check if first cell matches expected header names
        $headerKeywords = ['tanggal', 'date', 'tgl', 'waktu', 'no', 'urutan'];
        foreach ($headerKeywords as $keyword) {
            if (str_contains($firstCell, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse and normalize date
     */
    private function extractDate(string $dateStr, array $format): ?string
    {
        $dateStr = trim($dateStr);
        if (empty($dateStr)) {
            return null;
        }

        $dateFormat = $format['date_format'];

        try {
            if ($dateFormat === 'auto') {
                // Try multiple date formats
                $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'Y/m/d'];
                foreach ($formats as $fmt) {
                    $date = \DateTime::createFromFormat($fmt, $dateStr);
                    if ($date && $date->format($fmt) === $dateStr) {
                        return $date->format('Y-m-d');
                    }
                }

                // Fallback to strtotime
                $timestamp = strtotime($dateStr);
                if ($timestamp !== false) {
                    return date('Y-m-d', $timestamp);
                }
            } else {
                $date = \DateTime::createFromFormat($dateFormat, $dateStr);
                if ($date) {
                    return $date->format('Y-m-d');
                }
            }
        } catch (\Exception $e) {
            $this->logWarning('Failed to parse date', ['date' => $dateStr, 'error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Check if string looks like a date
     */
    private function looksLikeDate(string $str): bool
    {
        // Check common date patterns
        $patterns = [
            '/^\d{2}[\/\-]\d{2}[\/\-]\d{4}$/', // dd/mm/yyyy or dd-mm-yyyy
            '/^\d{4}[\/\-]\d{2}[\/\-]\d{2}$/', // yyyy/mm/dd or yyyy-mm-dd
            '/^\d{2}[\/\-]\d{2}[\/\-]\d{2}$/', // dd/mm/yy
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $str)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse amount string to float
     */
    private function parseAmount(string $amountStr): float
    {
        $amountStr = trim($amountStr);
        if (empty($amountStr)) {
            return 0.0;
        }

        // Remove currency symbols and spaces
        $amountStr = preg_replace('/[Rp$\s]/i', '', $amountStr);

        // Handle negative sign in parentheses: (1000) => -1000
        if (preg_match('/^\((.+)\)$/', $amountStr, $matches)) {
            $amountStr = '-'.$matches[1];
        }

        // Handle different thousand/decimal separators
        // Indonesian: 1.000.000,50 => 1000000.50
        // US: 1,000,000.50 => 1000000.50

        if (substr_count($amountStr, ',') > 1) {
            // Multiple commas = thousand separator (Indonesian style)
            $amountStr = str_replace(',', '', $amountStr);
            $amountStr = str_replace('.', '.', $amountStr);
        } elseif (substr_count($amountStr, '.') > 1) {
            // Multiple dots = thousand separator (European style)
            $amountStr = str_replace('.', '', $amountStr);
            $amountStr = str_replace(',', '.', $amountStr);
        } elseif (str_contains($amountStr, ',') && str_contains($amountStr, '.')) {
            // Both present - last one is decimal
            $lastComma = strrpos($amountStr, ',');
            $lastDot = strrpos($amountStr, '.');

            if ($lastComma > $lastDot) {
                // Comma is decimal separator (Indonesian/European)
                $amountStr = str_replace('.', '', $amountStr);
                $amountStr = str_replace(',', '.', $amountStr);
            } else {
                // Dot is decimal separator (US/UK)
                $amountStr = str_replace(',', '', $amountStr);
            }
        } elseif (str_contains($amountStr, ',')) {
            // Single comma - check if it's decimal or thousand
            if (preg_match('/,\d{2}$/', $amountStr)) {
                // Ends with ,XX - likely decimal
                $amountStr = str_replace(',', '.', $amountStr);
            } else {
                // Likely thousand separator
                $amountStr = str_replace(',', '', $amountStr);
            }
        }

        $amount = floatval($amountStr);

        return is_finite($amount) ? $amount : 0.0;
    }

    /**
     * Normalize transaction type
     */
    private function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));

        $debitKeywords = ['debit', 'db', 'debet', 'keluar', 'pengeluaran', 'dr'];
        $creditKeywords = ['credit', 'cr', 'kredit', 'masuk', 'penerimaan', 'setoran'];

        foreach ($debitKeywords as $keyword) {
            if (str_contains($type, $keyword)) {
                return 'debit';
            }
        }

        foreach ($creditKeywords as $keyword) {
            if (str_contains($type, $keyword)) {
                return 'credit';
            }
        }

        return 'debit'; // Default
    }

    /**
     * Extract reference number from description
     */
    private function extractReference(string $description): ?string
    {
        // Common reference patterns
        $patterns = [
            '/REF[.:]?\s*([A-Z0-9\-]+)/i',
            '/NO[.:]?\s*([A-Z0-9\-]+)/i',
            '/TRX[.:]?\s*([A-Z0-9\-]+)/i',
            '/([A-Z]{2,4}\d{8,})/', // e.g., TRX20240101001
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $description, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Safe logging helper - works with or without Laravel
     */
    private function logInfo(string $message, array $context = []): void
    {
        try {
            Log::info($message, $context);
        } catch (\Throwable $e) {
            error_log("INFO: {$message} - ".json_encode($context));
        }
    }

    /**
     * Safe error logging helper - works with or without Laravel
     */
    private function logError(string $message, array $context = []): void
    {
        try {
            Log::error($message, $context);
        } catch (\Throwable $e) {
            error_log("ERROR: {$message} - ".json_encode($context));
        }
    }

    /**
     * Safe warning logging helper - works with or without Laravel
     */
    private function logWarning(string $message, array $context = []): void
    {
        try {
            Log::warning($message, $context);
        } catch (\Throwable $e) {
            error_log("WARNING: {$message} - ".json_encode($context));
        }
    }
}
