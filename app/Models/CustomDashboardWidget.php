<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomDashboardWidget extends Model
{
    protected $fillable = [
        'tenant_id',
        'created_by',
        'title',
        'subtitle',
        'icon_bg',
        'icon_color',
        'cols',
        'metric_type',
        'model_class',
        'metric_column',
        'where_conditions',
        'date_scope',
        'date_column',
        'value_prefix',
        'value_suffix',
        'value_format',
        'visible_to_roles',
    ];

    protected function casts(): array
    {
        return [
            'where_conditions' => 'array',
            'visible_to_roles' => 'array',
            'cols' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The registry key used to reference this custom widget.
     * Format: custom_{id}
     */
    public function registryKey(): string
    {
        return 'custom_' . $this->id;
    }

    /**
     * Evaluate the metric value for a given tenant.
     */
    public function evaluate(int $tenantId): float|int|string
    {
        return match ($this->metric_type) {
            'count' => $this->runCount($tenantId),
            'sum' => $this->runSum($tenantId),
            'avg' => $this->runAvg($tenantId),
            'static' => (float) ($this->metric_column ?? 0),
            default => 0,
        };
    }

    // ─── Allowed models (whitelist to prevent arbitrary code execution) ──────
    private const ALLOWED_MODELS = [
        'SalesOrder' => \App\Models\SalesOrder::class,
        'PurchaseOrder' => \App\Models\PurchaseOrder::class,
        'Transaction' => \App\Models\Transaction::class,
        'Invoice' => \App\Models\Invoice::class,
        'ProductStock' => \App\Models\ProductStock::class,
        'Customer' => \App\Models\Customer::class,
        'Employee' => \App\Models\Employee::class,
        'EcommerceOrder' => \App\Models\EcommerceOrder::class,
        'Attendance' => \App\Models\Attendance::class,
    ];

    private function buildQuery(int $tenantId)
    {
        $modelClass = self::ALLOWED_MODELS[$this->model_class] ?? null;
        if (!$modelClass)
            return null;

        $q = $modelClass::where('tenant_id', $tenantId);

        // Date scope
        $dateCol = $this->date_column ?: 'created_at';
        $q = match ($this->date_scope) {
            'today' => $q->whereDate($dateCol, today()),
            'this_month' => $q->whereMonth($dateCol, now()->month)->whereYear($dateCol, now()->year),
            'this_year' => $q->whereYear($dateCol, now()->year),
            default => $q, // all_time
        };

        // Additional where conditions: [[column, operator, value], ...]
        foreach ($this->where_conditions ?? [] as $cond) {
            if (is_array($cond) && count($cond) === 3) {
                [$col, $op, $val] = $cond;
                // Sanitize: only allow simple column names (no expressions)
                if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $col)) {
                    $q->where($col, $op, $val);
                }
            }
        }

        return $q;
    }

    private function runCount(int $tenantId): int
    {
        return (int) ($this->buildQuery($tenantId)?->count() ?? 0);
    }

    private function runSum(int $tenantId): float
    {
        $col = $this->metric_column;
        if (!$col || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $col))
            return 0;
        return (float) ($this->buildQuery($tenantId)?->sum($col) ?? 0);
    }

    private function runAvg(int $tenantId): float
    {
        $col = $this->metric_column;
        if (!$col || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $col))
            return 0;
        return (float) ($this->buildQuery($tenantId)?->avg($col) ?? 0);
    }

    /**
     * Format the evaluated value for display.
     */
    public function formatValue(float|int|string $value): string
    {
        $formatted = match ($this->value_format) {
            'currency' => 'Rp ' . number_format((float) $value, 0, ',', '.'),
            'percent' => number_format((float) $value, 1) . '%',
            default => number_format((float) $value, 0, ',', '.'),
        };

        $prefix = $this->value_prefix ? ($this->value_prefix . ' ') : '';
        $suffix = $this->value_suffix ? (' ' . $this->value_suffix) : '';

        return $prefix . $formatted . $suffix;
    }
}
