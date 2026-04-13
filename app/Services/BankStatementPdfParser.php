<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Gemini\Client;
use Gemini\Data\Blob;
use Gemini\Data\Content;
use Gemini\Data\Part;
use Gemini\Enums\MimeType;
use Gemini\Enums\Role;

/**
 * BankStatementPdfParser
 * 
 * Parse bank statements dari PDF menggunakan Gemini AI Vision OCR
 * 
 * Features:
 * - PDF text extraction
 * - Image-based PDF OCR via Gemini AI
 * - Auto-detect bank format
 * - Convert to structured BankStatement data
 * - Support multi-page PDFs
 */
class BankStatementPdfParser
{
    /**
     * Supported file types
     */
    private array $allowedMimes = [
        'application/pdf',
        'image/jpeg',
        'image/jpg',
        'image/png',
    ];

    /**
     * Maximum file size (10MB)
     */
    private int $maxFileSize = 10 * 1024 * 1024;

    /**
     * Parse PDF file and extract bank statements
     * 
     * @param UploadedFile $file
     * @return array Extracted statements
     * @throws \Exception
     */
    public function parse(UploadedFile $file): array
    {
        $this->validateFile($file);

        try {
            // Check if PDF is text-based or image-based
            if ($this->isTextBasedPdf($file)) {
                $this->logInfo('PDF is text-based, using text extraction');
                return $this->parseTextBasedPdf($file);
            } else {
                $this->logInfo('PDF is image-based, using OCR');
                return $this->parseImageBasedPdf($file);
            }
        } catch (\Exception $e) {
            $this->logError('PDF parsing failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file): void
    {
        if (!in_array($file->getMimeType(), $this->allowedMimes)) {
            throw new \Exception('File type tidak didukung. Hanya PDF, JPG, PNG yang diperbolehkan.');
        }

        if ($file->getSize() > $this->maxFileSize) {
            throw new \Exception('Ukuran file terlalu besar. Maksimal 10MB.');
        }
    }

    /**
     * Check if PDF has extractable text
     */
    private function isTextBasedPdf(UploadedFile $file): bool
    {
        // Try to extract text using pdftotext if available
        if (function_exists('shell_exec')) {
            $tempPath = $file->getPathname();
            $output = shell_exec("pdftotext -layout {$tempPath} - 2>&1");

            if ($output && strlen(trim($output)) > 100) {
                return true;
            }
        }

        // Fallback: assume image-based (will use OCR)
        return false;
    }

    /**
     * Parse text-based PDF
     */
    private function parseTextBasedPdf(UploadedFile $file): array
    {
        $tempPath = $file->getPathname();

        // Extract text using pdftotext
        $output = shell_exec("pdftotext -layout {$tempPath} - 2>&1");

        if (!$output || strlen(trim($output)) < 100) {
            throw new \Exception('Tidak dapat extract text dari PDF');
        }

        // Parse text to statements
        return $this->parseExtractedText($output);
    }

    /**
     * Parse image-based PDF using Gemini AI OCR
     */
    private function parseImageBasedPdf(UploadedFile $file): array
    {
        try {
            // Convert PDF pages to images
            $images = $this->convertPdfToImages($file);

            $allStatements = [];

            foreach ($images as $index => $imagePath) {
                $this->logInfo("Processing page " . ($index + 1) . " with Gemini OCR");

                // Use Gemini AI Vision to extract text
                $extractedText = $this->extractTextWithGemini($imagePath);

                // Parse extracted text
                $statements = $this->parseExtractedText($extractedText);
                $allStatements = array_merge($allStatements, $statements);

                // Cleanup temp image
                @unlink($imagePath);
            }

            return $allStatements;

        } catch (\Exception $e) {
            $this->logError('Gemini OCR failed', ['error' => $e->getMessage()]);
            throw new \Exception('OCR processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Convert PDF to images (one per page)
     */
    private function convertPdfToImages(UploadedFile $file): array
    {
        $tempDir = storage_path('app/temp/pdf_pages');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $file->getPathname();
        $outputPattern = $tempDir . '/page_%d.jpg';

        // Use ImageMagick convert
        $command = "convert -density 300 {$tempPath} -quality 100 {$outputPattern} 2>&1";
        $output = shell_exec($command);

        if ($output && strpos($output, 'Error') !== false) {
            throw new \Exception('Failed to convert PDF to images: ' . $output);
        }

        // Get generated images
        $images = glob($tempDir . '/page_*.jpg');

        if (empty($images)) {
            throw new \Exception('No images generated from PDF');
        }

        sort($images);
        return $images;
    }

    /**
     * Extract text from image using Gemini AI Vision
     */
    private function extractTextWithGemini(string $imagePath): string
    {
        try {
            $gemini = app(Client::class);

            // Read image file
            $imageData = file_get_contents($imagePath);

            // Determine MIME type
            $mimeType = $this->resolveMimeType($imagePath);

            // Prepare prompt for bank statement extraction
            $prompt = <<<'PROMPT'
Extract ALL text from this bank statement image. Return ONLY the raw text in this exact format:

TANGGAL|DESKRIPSI|DEBIT|KREDIT|SALDO

One transaction per line. Use pipe (|) as separator.
Include ALL transactions visible in the image.
Do NOT add any explanations or notes.
PROMPT;

            // Build parts with image and text
            $parts = [];

            // Add image part
            $parts[] = new Part(
                inlineData: new Blob(
                    mimeType: $mimeType,
                    data: $imageData,
                )
            );

            // Add text prompt
            $parts[] = new Part(text: $prompt);

            // Create content
            $content = new Content(
                parts: $parts,
                role: Role::USER
            );

            // Call Gemini API with gemini-pro-vision or gemini-2.0-flash
            $model = config('gemini.model', 'gemini-2.0-flash');
            $result = $gemini->geminiPro()->generateContent($content);

            $extractedText = $result->text();

            if (!$extractedText) {
                throw new \Exception('Gemini returned empty response');
            }

            $this->logInfo('Gemini OCR successful', [
                'text_length' => strlen($extractedText)
            ]);

            return $extractedText;

        } catch (\Exception $e) {
            $this->logError('Gemini API call failed', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Gemini OCR failed: ' . $e->getMessage());
        }
    }

    /**
     * Resolve MIME type from file path
     */
    private function resolveMimeType(string $filePath): MimeType
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => MimeType::IMAGE_JPEG,
            'png' => MimeType::IMAGE_PNG,
            default => MimeType::IMAGE_JPEG,
        };
    }

    /**
     * Parse extracted text to structured statements
     */
    private function parseExtractedText(string $text): array
    {
        $lines = explode("\n", trim($text));
        $statements = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and headers
            if (empty($line) || $this->isHeaderLine($line)) {
                continue;
            }

            // Try to parse transaction line
            $statement = $this->parseTransactionLine($line);

            if ($statement) {
                $statements[] = $statement;
            }
        }

        $this->logInfo('Parsed statements from text', [
            'total_lines' => count($lines),
            'parsed_statements' => count($statements)
        ]);

        return $statements;
    }

    /**
     * Check if line is a header
     */
    private function isHeaderLine(string $line): bool
    {
        $headerKeywords = [
            'tanggal',
            'date',
            'description',
            'deskripsi',
            'uraian',
            'debit',
            'kredit',
            'credit',
            'amount',
            'jumlah',
            'saldo',
            'balance',
            'mutasi',
            'rekening'
        ];

        $lowerLine = strtolower($line);

        foreach ($headerKeywords as $keyword) {
            if (strpos($lowerLine, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse single transaction line
     */
    private function parseTransactionLine(string $line): ?array
    {
        // Try pipe-separated format first
        if (strpos($line, '|') !== false) {
            return $this->parsePipeSeparated($line);
        }

        // Try space-separated (multiple spaces)
        if (preg_match('/\s{2,}/', $line)) {
            return $this->parseSpaceSeparated($line);
        }

        // Try tab-separated
        if (strpos($line, "\t") !== false) {
            return $this->parseTabSeparated($line);
        }

        return null;
    }

    /**
     * Parse pipe-separated line
     * Format: TANGGAL|DESKRIPSI|DEBIT|KREDIT|SALDO
     */
    private function parsePipeSeparated(string $line): ?array
    {
        $parts = explode('|', $line);

        if (count($parts) < 3) {
            return null;
        }

        $date = trim($parts[0]);
        $description = trim($parts[1]);

        // Determine format (with/without separate debit/credit)
        if (count($parts) >= 5) {
            // Format: DATE|DESC|DEBIT|CREDIT|BALANCE
            $debit = $this->parseAmount($parts[2] ?? '');
            $credit = $this->parseAmount($parts[3] ?? '');
            $balance = $this->parseAmount($parts[4] ?? '');

            $amount = $credit > 0 ? $credit : $debit;
            $type = $credit > 0 ? 'credit' : 'debit';
        } else {
            // Format: DATE|DESC|AMOUNT|BALANCE
            $amount = $this->parseAmount($parts[2] ?? '');
            $balance = $this->parseAmount($parts[3] ?? '');
            $type = $amount >= 0 ? 'credit' : 'debit';
            $amount = abs($amount);
        }

        if (!$date || !$description || $amount <= 0) {
            return null;
        }

        return [
            'transaction_date' => $this->parseDate($date),
            'description' => $description,
            'amount' => $amount,
            'type' => $type,
            'balance' => $balance,
            'reference' => '',
        ];
    }

    /**
     * Parse space-separated line
     */
    private function parseSpaceSeparated(string $line): ?array
    {
        // Complex regex for Indonesian bank statements
        $pattern = '/^(\d{2}[\/\-]\d{2}[\/\-]\d{4})\s+(.+?)\s+([\d\.]+,?\d*)\s+([\d\.]+,?\d*)?\s*$/';

        if (preg_match($pattern, $line, $matches)) {
            $date = $matches[1];
            $description = $matches[2];
            $amount1 = $this->parseAmount($matches[3]);
            $amount2 = isset($matches[4]) ? $this->parseAmount($matches[4]) : 0;

            // If two amounts, first is debit, second is credit
            if ($amount2 > 0) {
                $amount = $amount2;
                $type = 'credit';
            } else {
                $amount = $amount1;
                $type = 'debit';
            }

            return [
                'transaction_date' => $this->parseDate($date),
                'description' => $description,
                'amount' => $amount,
                'type' => $type,
                'balance' => 0,
                'reference' => '',
            ];
        }

        return null;
    }

    /**
     * Parse tab-separated line
     */
    private function parseTabSeparated(string $line): ?array
    {
        $parts = explode("\t", $line);

        if (count($parts) < 3) {
            return null;
        }

        return $this->parsePipeSeparated(implode('|', $parts));
    }

    /**
     * Parse amount string to float
     */
    private function parseAmount(string $amount): float
    {
        // Remove currency symbols and spaces
        $amount = preg_replace('/[Rp\$€\s]/', '', $amount);

        // Handle Indonesian format (1.000.000,50)
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $amount)) {
            $amount = str_replace('.', '', $amount);
            $amount = str_replace(',', '.', $amount);
        }
        // Handle US format (1,000,000.50)
        elseif (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $amount)) {
            $amount = str_replace(',', '', $amount);
        }
        // Handle simple format (1000000.50 or 1000000,50)
        else {
            $amount = str_replace(',', '.', $amount);
        }

        return floatval($amount);
    }

    /**
     * Parse date string
     */
    private function parseDate(string $date): ?string
    {
        $date = trim($date);

        // Try various formats
        $formats = [
            'd/m/Y',
            'd-m-Y',
            'Y-m-d',
            'd/m/y',
            'd-m-y',
        ];

        foreach ($formats as $format) {
            $parsed = \DateTime::createFromFormat($format, $date);
            if ($parsed && $parsed->format($format) === $date) {
                return $parsed->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * Safe logging helper
     */
    private function logInfo(string $message, array $context = []): void
    {
        try {
            Log::info($message, $context);
        } catch (\Throwable $e) {
            error_log("INFO: {$message} - " . json_encode($context));
        }
    }

    /**
     * Safe logging helper
     */
    private function logError(string $message, array $context = []): void
    {
        try {
            Log::error($message, $context);
        } catch (\Throwable $e) {
            error_log("ERROR: {$message} - " . json_encode($context));
        }
    }
}
