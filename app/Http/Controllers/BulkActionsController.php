<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkActionsController extends Controller
{
    /**
     * Execute bulk action on multiple records
     */
    public function execute(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:delete,update_status,export,assign',
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:'.$this->getModelTable($request->model).',id',
            'model' => 'required|string|in:products,customers,invoices,sales_orders,journal_entries',
            'options' => 'nullable|array',
        ]);

        $action = $request->action;
        $ids = $request->ids;
        $model = $request->model;
        $options = $request->options ?? [];
        $tenantId = $request->user()->current_tenant_id ?? $request->user()->tenant_id;

        try {
            DB::beginTransaction();

            $result = match ($action) {
                'delete' => $this->bulkDelete($model, $ids, $tenantId),
                'update_status' => $this->bulkUpdateStatus($model, $ids, $tenantId, $options),
                'export' => $this->bulkExport($model, $ids, $tenantId),
                'assign' => $this->bulkAssign($model, $ids, $tenantId, $options),
            };

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Bulk {$action} completed successfully",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Bulk action failed: {$e->getMessage()}", [
                'action' => $action,
                'model' => $model,
                'ids' => $ids,
            ]);

            return response()->json([
                'success' => false,
                'message' => "Bulk action failed: {$e->getMessage()}",
            ], 500);
        }
    }

    /**
     * Bulk delete records (soft delete)
     */
    private function bulkDelete($model, $ids, $tenantId)
    {
        $modelClass = $this->getModelClass($model);
        $deleted = $modelClass::where('tenant_id', $tenantId)
            ->whereIn('id', $ids)
            ->delete();

        return [
            'action' => 'delete',
            'affected' => $deleted,
        ];
    }

    /**
     * Bulk update status
     */
    private function bulkUpdateStatus($model, $ids, $tenantId, $options)
    {
        if (! isset($options['status'])) {
            throw new \Exception('Status is required for update_status action');
        }

        $modelClass = $this->getModelClass($model);
        $updated = $modelClass::where('tenant_id', $tenantId)
            ->whereIn('id', $ids)
            ->update(['status' => $options['status']]);

        return [
            'action' => 'update_status',
            'affected' => $updated,
            'new_status' => $options['status'],
        ];
    }

    /**
     * Bulk export - return data for export
     */
    private function bulkExport($model, $ids, $tenantId)
    {
        $modelClass = $this->getModelClass($model);
        $records = $modelClass::where('tenant_id', $tenantId)
            ->whereIn('id', $ids)
            ->get();

        return [
            'action' => 'export',
            'count' => $records->count(),
            'download_url' => route('bulk-actions.export-download', [
                'model' => $model,
                'ids' => implode(',', $ids),
            ]),
        ];
    }

    /**
     * Bulk assign to user/category/etc
     */
    private function bulkAssign($model, $ids, $tenantId, $options)
    {
        if (! isset($options['field']) || ! isset($options['value'])) {
            throw new \Exception('Field and value are required for assign action');
        }

        $modelClass = $this->getModelClass($model);
        $updated = $modelClass::where('tenant_id', $tenantId)
            ->whereIn('id', $ids)
            ->update([$options['field'] => $options['value']]);

        return [
            'action' => 'assign',
            'affected' => $updated,
            'field' => $options['field'],
            'value' => $options['value'],
        ];
    }

    /**
     * Get model class from string
     */
    private function getModelClass($model)
    {
        $models = [
            'products' => Product::class,
            'customers' => Customer::class,
            'invoices' => Invoice::class,
            'sales_orders' => SalesOrder::class,
            'journal_entries' => JournalEntry::class,
        ];

        if (! isset($models[$model])) {
            throw new \Exception("Invalid model: {$model}");
        }

        return $models[$model];
    }

    /**
     * Get table name from model string
     */
    private function getModelTable($model)
    {
        return $model; // Assuming table names match model strings
    }

    /**
     * Export download endpoint
     */
    public function exportDownload(Request $request)
    {
        $request->validate([
            'model' => 'required|string',
            'ids' => 'required|string',
        ]);

        $modelClass = $this->getModelClass($request->model);
        $ids = explode(',', $request->ids);
        $tenantId = $request->user()->current_tenant_id ?? $request->user()->tenant_id;

        $records = $modelClass::where('tenant_id', $tenantId)
            ->whereIn('id', $ids)
            ->get();

        // Simple CSV export
        $csv = $this->generateCsv($records);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$request->model}_export.csv\"",
        ]);
    }

    /**
     * Generate CSV from records
     */
    private function generateCsv($records)
    {
        if ($records->isEmpty()) {
            return '';
        }

        $output = fopen('php://temp', 'r+');

        // Headers
        $headers = array_keys($records->first()->toArray());
        fputcsv($output, $headers);

        // Data
        foreach ($records as $record) {
            fputcsv($output, $record->toArray());
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
