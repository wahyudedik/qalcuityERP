<?php

namespace App\Services;

/**
 * Validasi sebelum Gemini mengeksekusi operasi write ke database.
 * Mencegah halusinasi AI langsung memodifikasi data tanpa validasi.
 */
class GeminiWriteValidator
{
    protected array $errors = [];

    public function validate(string $toolName, array $args): bool
    {
        $this->errors = [];

        $method = 'validate'.str_replace('_', '', ucwords($toolName, '_'));

        if (method_exists($this, $method)) {
            return $this->$method($args);
        }

        // Default: lolos jika tidak ada validator spesifik
        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    // ─── Validators per tool ──────────────────────────────────────

    protected function validateAddStock(array $args): bool
    {
        if (empty($args['product_name'])) {
            $this->errors[] = 'Nama produk wajib diisi.';
        }
        if (empty($args['warehouse'])) {
            $this->errors[] = 'Nama gudang wajib diisi.';
        }
        if (! isset($args['quantity']) || $args['quantity'] <= 0) {
            $this->errors[] = 'Quantity harus lebih dari 0.';
        }
        if (isset($args['quantity']) && $args['quantity'] > 100000) {
            $this->errors[] = 'Quantity terlalu besar (maks 100.000). Mohon konfirmasi ulang.';
        }

        return empty($this->errors);
    }

    protected function validateCreatePurchaseOrder(array $args): bool
    {
        if (empty($args['supplier_name'])) {
            $this->errors[] = 'Nama supplier wajib diisi.';
        }
        if (empty($args['warehouse'])) {
            $this->errors[] = 'Nama gudang wajib diisi.';
        }
        if (empty($args['items']) || ! is_array($args['items'])) {
            $this->errors[] = 'Daftar item PO tidak boleh kosong.';
        }
        foreach ($args['items'] ?? [] as $i => $item) {
            if (empty($item['product_name'])) {
                $this->errors[] = 'Item ke-'.($i + 1).': nama produk wajib diisi.';
            }
            if (! isset($item['quantity']) || $item['quantity'] <= 0) {
                $this->errors[] = 'Item ke-'.($i + 1).': quantity harus lebih dari 0.';
            }
        }

        return empty($this->errors);
    }

    protected function validateAutoReorder(array $args): bool
    {
        if (empty($args['supplier_name'])) {
            $this->errors[] = 'Nama supplier wajib diisi untuk auto-reorder.';
        }
        if (empty($args['warehouse'])) {
            $this->errors[] = 'Nama gudang wajib diisi.';
        }

        return empty($this->errors);
    }

    protected function validateAddTransaction(array $args): bool
    {
        if (! in_array($args['type'] ?? '', ['income', 'expense'])) {
            $this->errors[] = 'Tipe transaksi harus income atau expense.';
        }
        if (! isset($args['amount']) || $args['amount'] <= 0) {
            $this->errors[] = 'Nominal transaksi harus lebih dari 0.';
        }
        if (isset($args['amount']) && $args['amount'] > 10_000_000_000) {
            $this->errors[] = 'Nominal terlalu besar. Mohon konfirmasi ulang.';
        }
        if (empty($args['description'])) {
            $this->errors[] = 'Keterangan transaksi wajib diisi.';
        }

        return empty($this->errors);
    }
}
