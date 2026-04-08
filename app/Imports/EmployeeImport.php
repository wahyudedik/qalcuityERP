<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Carbon\Carbon;
use Throwable;

class EmployeeImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
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
                if (empty($row['employee_name']) && empty($row['email']) && empty($row['nik'])) {
                    continue;
                }

                $data = [
                    'tenant_id' => $this->tenantId,
                    'name' => $row['employee_name'] ?? $row['name'] ?? $row['full_name'] ?? 'Unknown',
                    'email' => $row['email'] ?? null,
                    'phone' => $row['phone'] ?? $row['mobile'] ?? $row['phone_number'] ?? null,
                    'nik' => $row['nik'] ?? $row['employee_code'] ?? $row['employee_id'] ?? $this->generateNIK(),
                    'position' => $row['position'] ?? $row['job_title'] ?? $row['title'] ?? null,
                    'department' => $row['department'] ?? $row['division'] ?? null,
                    'join_date' => $this->parseDate($row['join_date'] ?? $row['start_date'] ?? now()),
                    'salary' => $this->parsePrice($row['salary'] ?? $row['base_salary'] ?? 0),
                    'status' => $row['status'] ?? $row['employment_status'] ?? 'active',
                    'address' => $row['address'] ?? null,
                    'emergency_contact' => $row['emergency_contact'] ?? null,
                    'emergency_phone' => $row['emergency_phone'] ?? null,
                    'bank_account' => $row['bank_account'] ?? $row['bank_number'] ?? null,
                    'bank_name' => $row['bank_name'] ?? null,
                    'is_active' => $this->parseBoolean($row['is_active'] ?? $row['active'] ?? true),
                ];

                // Check if employee exists by NIK
                $existingEmployee = Employee::where('tenant_id', $this->tenantId)
                    ->where('nik', $data['nik'])
                    ->first();

                if ($existingEmployee) {
                    // Update existing employee
                    $existingEmployee->update($data);
                    $this->updated++;
                } else {
                    // Create new employee
                    Employee::create($data);
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
            'employee_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'nik' => 'nullable|string|max:100',
            'join_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'employee_name.required' => 'Nama karyawan wajib diisi',
            'email.email' => 'Format email tidak valid',
            'join_date.date' => 'Format tanggal tidak valid',
        ];
    }

    /**
     * Generate unique NIK
     */
    protected function generateNIK(): string
    {
        return 'EMP' . now()->format('YmdHis') . rand(100, 999);
    }

    /**
     * Parse date value
     */
    protected function parseDate($value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_numeric($value)) {
            // Excel date serial number
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return now();
        }
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
