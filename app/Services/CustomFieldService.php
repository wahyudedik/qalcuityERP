<?php

namespace App\Services;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * CustomFieldService — Task 54
 * Kelola definisi dan nilai custom field per modul per tenant.
 */
class CustomFieldService
{
    /**
     * Ambil semua field aktif untuk modul tertentu.
     */
    public function getFields(int $tenantId, string $module): \Illuminate\Support\Collection
    {
        return Cache::remember("cf_{$tenantId}_{$module}", 60, fn() =>
            CustomField::where('tenant_id', $tenantId)
                ->where('module', $module)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
        );
    }

    /**
     * Ambil nilai custom field untuk satu record.
     * Return: ['field_key' => 'value', ...]
     */
    public function getValues(int $tenantId, string $modelClass, int $modelId): array
    {
        return CustomFieldValue::where('tenant_id', $tenantId)
            ->where('model_type', $modelClass)
            ->where('model_id', $modelId)
            ->with('customField')
            ->get()
            ->keyBy(fn($v) => $v->customField->key)
            ->map(fn($v) => $v->value)
            ->toArray();
    }

    /**
     * Simpan nilai custom field dari request.
     * $data: ['custom_fields' => ['key' => 'value', ...]]
     */
    public function saveValues(int $tenantId, string $modelClass, int $modelId, array $customData): void
    {
        if (empty($customData)) return;

        $module = $this->modelClassToModule($modelClass);
        $fields = $this->getFields($tenantId, $module)->keyBy('key');

        foreach ($customData as $key => $value) {
            $field = $fields->get($key);
            if (!$field) continue;

            CustomFieldValue::updateOrCreate(
                [
                    'custom_field_id' => $field->id,
                    'model_type'      => $modelClass,
                    'model_id'        => $modelId,
                ],
                [
                    'tenant_id' => $tenantId,
                    'value'     => is_array($value) ? implode(',', $value) : (string) $value,
                ]
            );
        }
    }

    /**
     * Validasi nilai custom field (required check).
     * Return array error messages.
     */
    public function validate(int $tenantId, string $module, array $customData): array
    {
        $errors = [];
        $fields = $this->getFields($tenantId, $module)->where('required', true);

        foreach ($fields as $field) {
            $value = $customData[$field->key] ?? null;
            if ($value === null || $value === '') {
                $errors[$field->key] = "{$field->label} wajib diisi.";
            }
        }

        return $errors;
    }

    /**
     * Invalidate cache setelah field diubah.
     */
    public function invalidateCache(int $tenantId, string $module): void
    {
        Cache::forget("cf_{$tenantId}_{$module}");
    }

    private function modelClassToModule(string $modelClass): string
    {
        return match ($modelClass) {
            'App\Models\Invoice'       => 'invoice',
            'App\Models\Product'       => 'product',
            'App\Models\Customer'      => 'customer',
            'App\Models\Supplier'      => 'supplier',
            'App\Models\Employee'      => 'employee',
            'App\Models\SalesOrder'    => 'sales_order',
            'App\Models\PurchaseOrder' => 'purchase_order',
            'App\Models\Expense'       => 'expense',
            default                    => strtolower(class_basename($modelClass)),
        };
    }
}
