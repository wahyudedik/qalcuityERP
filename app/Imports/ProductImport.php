<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Str;
use Throwable;

class ProductImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    protected $tenantId;
    protected $imported = 0;
    protected $updated = 0;
    protected $errors = [];

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                // Skip empty rows
                if (empty($row['product_name']) && empty($row['sku'])) {
                    continue;
                }

                $data = [
                    'tenant_id' => $this->tenantId,
                    'name' => $row['product_name'] ?? $row['name'] ?? 'Unknown',
                    'sku' => $row['sku'] ?? Str::upper(Str::random(10)),
                    'category_id' => $this->resolveCategoryId($row['category'] ?? $row['category_name'] ?? null),
                    'description' => $row['description'] ?? null,
                    'unit' => $row['unit'] ?? 'pcs',
                    'purchase_price' => $this->parsePrice($row['purchase_price'] ?? $row['cost_price'] ?? 0),
                    'selling_price' => $this->parsePrice($row['selling_price'] ?? $row['sale_price'] ?? $row['price'] ?? 0),
                    'stock' => (int) ($row['stock'] ?? $row['quantity'] ?? 0),
                    'min_stock' => (int) ($row['min_stock'] ?? $row['reorder_point'] ?? 0),
                    'is_active' => $this->parseBoolean($row['is_active'] ?? $row['active'] ?? true),
                    'barcode' => $row['barcode'] ?? null,
                ];

                // Check if product exists by SKU
                $existingProduct = Product::where('tenant_id', $this->tenantId)
                    ->where('sku', $data['sku'])
                    ->first();

                if ($existingProduct) {
                    // Update existing product
                    $existingProduct->update($data);
                    $this->updated++;
                } else {
                    // Create new product
                    Product::create($data);
                    $this->imported++;
                }
            } catch (Throwable $e) {
                $this->errors[] = [
                    'row' => $index + 2, // +2 because header row is row 1
                    'error' => $e->getMessage(),
                    'data' => $row->toArray(),
                ];
            }
        }
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'product_name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'selling_price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'product_name.required' => 'Nama produk wajib diisi',
            'selling_price.required' => 'Harga jual wajib diisi',
            'selling_price.numeric' => 'Harga jual harus berupa angka',
        ];
    }

    /**
     * Resolve category ID from name
     */
    protected function resolveCategoryId(?string $categoryName): ?int
    {
        if (!$categoryName) {
            return null;
        }

        $category = Category::where('tenant_id', $this->tenantId)
            ->where('name', 'LIKE', "%{$categoryName}%")
            ->first();

        return $category?->id;
    }

    /**
     * Parse price value
     */
    protected function parsePrice($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Remove currency symbols and thousand separators
        $cleaned = preg_replace('/[^\d.,]/', '', (string) $value);
        $cleaned = str_replace(',', '', $cleaned);

        return (float) ($cleaned ?: 0);
    }

    /**
     * Parse boolean value
     */
    protected function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        $stringValue = strtolower((string) $value);
        return in_array($stringValue, ['true', 'yes', 'y', '1', 'aktif', 'active']);
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return [
            'imported' => $this->imported,
            'updated' => $this->updated,
            'errors_count' => count($this->errors),
            'errors' => array_slice($this->errors, 0, 10), // First 10 errors only
        ];
    }
}
