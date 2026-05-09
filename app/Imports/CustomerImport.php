<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;

class CustomerImport implements SkipsOnError, SkipsOnFailure, ToCollection, WithHeadingRow, WithValidation
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

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                // Skip empty rows
                if (empty($row['customer_name']) && empty($row['email']) && empty($row['phone'])) {
                    continue;
                }

                $data = [
                    'tenant_id' => $this->tenantId,
                    'name' => $row['customer_name'] ?? $row['name'] ?? 'Unknown',
                    'email' => $row['email'] ?? null,
                    'phone' => $row['phone'] ?? $row['mobile'] ?? $row['phone_number'] ?? null,
                    'address' => $row['address'] ?? null,
                    'city' => $row['city'] ?? null,
                    'state' => $row['state'] ?? $row['province'] ?? null,
                    'postal_code' => $row['postal_code'] ?? $row['zip'] ?? null,
                    'country' => $row['country'] ?? 'Indonesia',
                    'company' => $row['company'] ?? $row['company_name'] ?? null,
                    'tax_number' => $row['tax_number'] ?? $row['npwp'] ?? null,
                    'customer_type' => $row['customer_type'] ?? $row['type'] ?? 'retail',
                    'credit_limit' => $this->parsePrice($row['credit_limit'] ?? 0),
                    'notes' => $row['notes'] ?? $row['remarks'] ?? null,
                    'is_active' => $this->parseBoolean($row['is_active'] ?? $row['active'] ?? true),
                ];

                // Check if customer exists by email
                $existingCustomer = null;
                if ($data['email']) {
                    $existingCustomer = Customer::where('tenant_id', $this->tenantId)
                        ->where('email', $data['email'])
                        ->first();
                }

                if ($existingCustomer) {
                    // Update existing customer
                    $existingCustomer->update($data);
                    $this->updated++;
                } else {
                    // Create new customer
                    Customer::create($data);
                    $this->imported++;
                }
            } catch (Throwable $e) {
                $this->errors[] = [
                    'row' => $index + 2,
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
            'customer_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'credit_limit' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'customer_name.required' => 'Nama customer wajib diisi',
            'email.email' => 'Format email tidak valid',
        ];
    }

    /**
     * Parse price value
     */
    protected function parsePrice($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

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
            'errors' => array_slice($this->errors, 0, 10),
        ];
    }
}
