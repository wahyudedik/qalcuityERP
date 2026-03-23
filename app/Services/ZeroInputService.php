<?php

namespace App\Services;

use App\Models\ZeroInputLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * ZeroInputService — Task 56
 * Proses input via foto nota (OCR), voice, atau teks WhatsApp
 * dan mapping otomatis ke modul ERP yang tepat menggunakan Gemini AI.
 */
class ZeroInputService
{
    public function __construct(protected GeminiService $gemini) {}

    // ─── Entry Points ─────────────────────────────────────────────

    /**
     * Proses foto nota/struk via OCR + AI.
     */
    public function processPhoto(int $tenantId, int $userId, UploadedFile $file): ZeroInputLog
    {
        $path = $file->store('zero-input', 'public');

        $log = ZeroInputLog::create([
            'tenant_id' => $tenantId,
            'user_id'   => $userId,
            'channel'   => 'photo',
            'status'    => 'processing',
            'file_path' => $path,
        ]);

        try {
            $mimeType = $file->getMimeType();
            $base64   = base64_encode(file_get_contents($file->getRealPath()));

            $prompt = $this->buildOcrPrompt();
            $response = $this->gemini->chatWithMedia(
                message: $prompt,
                files: [['mime_type' => $mimeType, 'data' => $base64]],
                history: [],
                toolDeclarations: [],
            );

            $extracted = $this->parseAiResponse($response['text'] ?? '');
            $module    = $this->detectModule($extracted);

            $log->update([
                'status'         => 'mapped',
                'mapped_module'  => $module,
                'extracted_data' => $extracted,
            ]);
        } catch (\Throwable $e) {
            Log::error("ZeroInput photo error: " . $e->getMessage());
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }

        return $log->fresh();
    }

    /**
     * Proses input teks (voice transcript atau pesan WhatsApp).
     */
    public function processText(int $tenantId, int $userId, string $text, string $channel = 'whatsapp'): ZeroInputLog
    {
        $log = ZeroInputLog::create([
            'tenant_id' => $tenantId,
            'user_id'   => $userId,
            'channel'   => $channel,
            'status'    => 'processing',
            'raw_input' => $text,
        ]);

        try {
            $prompt   = $this->buildTextPrompt($text);
            $response = $this->gemini->chat($prompt, []);
            $extracted = $this->parseAiResponse($response['text'] ?? '');
            $module    = $this->detectModule($extracted);

            $log->update([
                'status'         => 'mapped',
                'mapped_module'  => $module,
                'extracted_data' => $extracted,
            ]);
        } catch (\Throwable $e) {
            Log::error("ZeroInput text error: " . $e->getMessage());
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
        }

        return $log->fresh();
    }

    /**
     * Konfirmasi dan buat record ERP dari data yang sudah diekstrak.
     */
    public function createRecord(ZeroInputLog $log): array
    {
        if ($log->status !== 'mapped' || empty($log->extracted_data)) {
            return ['success' => false, 'message' => 'Data belum siap untuk diproses.'];
        }

        $data   = $log->extracted_data;
        $module = $log->mapped_module;

        try {
            $result = match ($module) {
                'expense'  => $this->createExpense($log->tenant_id, $log->user_id, $data),
                'product'  => $this->createProduct($log->tenant_id, $data),
                'customer' => $this->createCustomer($log->tenant_id, $data),
                default    => ['success' => false, 'message' => "Modul '{$module}' belum didukung untuk auto-create."],
            };

            if ($result['success']) {
                $log->update([
                    'status'          => 'created',
                    'created_records' => $result['records'] ?? [],
                ]);
            }

            return $result;
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ─── Module Creators ──────────────────────────────────────────

    private function createExpense(int $tenantId, int $userId, array $data): array
    {
        $expense = \App\Models\Transaction::create([
            'tenant_id'   => $tenantId,
            'user_id'     => $userId,
            'type'        => 'expense',
            'amount'      => $data['total'] ?? $data['amount'] ?? 0,
            'description' => $data['description'] ?? $data['merchant'] ?? 'Dari Zero Input',
            'date'        => $data['date'] ?? today()->toDateString(),
            'reference'   => 'ZI-' . now()->format('YmdHis'),
        ]);

        return ['success' => true, 'records' => [['type' => 'expense', 'id' => $expense->id]]];
    }

    private function createProduct(int $tenantId, array $data): array
    {
        if (empty($data['name'])) {
            return ['success' => false, 'message' => 'Nama produk tidak ditemukan.'];
        }

        $product = \App\Models\Product::create([
            'tenant_id'  => $tenantId,
            'name'       => $data['name'],
            'sku'        => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $data['name']), 0, 6)) . '-' . rand(100, 999),
            'price_sell' => $data['price'] ?? 0,
            'price_buy'  => $data['cost_price'] ?? 0,
            'unit'       => $data['unit'] ?? 'pcs',
            'is_active'  => true,
        ]);

        return ['success' => true, 'records' => [['type' => 'product', 'id' => $product->id]]];
    }

    private function createCustomer(int $tenantId, array $data): array
    {
        if (empty($data['name'])) {
            return ['success' => false, 'message' => 'Nama customer tidak ditemukan.'];
        }

        $customer = \App\Models\Customer::create([
            'tenant_id' => $tenantId,
            'name'      => $data['name'],
            'phone'     => $data['phone'] ?? null,
            'email'     => $data['email'] ?? null,
            'address'   => $data['address'] ?? null,
            'is_active' => true,
        ]);

        return ['success' => true, 'records' => [['type' => 'customer', 'id' => $customer->id]]];
    }

    // ─── AI Prompts ───────────────────────────────────────────────

    private function buildOcrPrompt(): string
    {
        return <<<PROMPT
Kamu adalah sistem OCR ERP. Ekstrak semua informasi dari gambar nota/struk/dokumen ini.
Kembalikan HANYA JSON valid dengan format berikut (tanpa markdown, tanpa penjelasan):
{
  "module": "expense|product|customer|invoice|purchase",
  "merchant": "nama toko/vendor",
  "date": "YYYY-MM-DD",
  "items": [{"name": "...", "qty": 1, "price": 0, "total": 0}],
  "subtotal": 0,
  "tax": 0,
  "total": 0,
  "payment_method": "cash|transfer|qris",
  "notes": "..."
}
Jika field tidak ada, isi null. Untuk tanggal, gunakan format YYYY-MM-DD.
PROMPT;
    }

    private function buildTextPrompt(string $text): string
    {
        return <<<PROMPT
Kamu adalah sistem ERP. Ekstrak informasi transaksi dari teks berikut dan tentukan modul yang tepat.
Teks: "{$text}"

Kembalikan HANYA JSON valid (tanpa markdown):
{
  "module": "expense|product|customer|sales_order|purchase",
  "description": "...",
  "amount": 0,
  "date": "YYYY-MM-DD",
  "items": [{"name": "...", "qty": 1, "price": 0}],
  "customer_name": null,
  "supplier_name": null,
  "payment_method": null,
  "notes": "..."
}
PROMPT;
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function parseAiResponse(string $text): array
    {
        // Bersihkan markdown code block jika ada
        $text = preg_replace('/```(?:json)?\s*([\s\S]*?)\s*```/', '$1', trim($text));
        $text = trim($text);

        $data = json_decode($text, true);
        return is_array($data) ? $data : ['raw' => $text];
    }

    private function detectModule(array $data): string
    {
        if (!empty($data['module'])) return $data['module'];

        // Heuristik sederhana
        if (!empty($data['items']) && count($data['items']) > 0) {
            return 'expense';
        }
        if (!empty($data['customer_name'])) return 'sales_order';
        if (!empty($data['supplier_name'])) return 'purchase';

        return 'expense'; // default
    }
}
