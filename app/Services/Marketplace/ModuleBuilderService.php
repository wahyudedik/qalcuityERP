<?php

namespace App\Services\Marketplace;

use App\Models\CustomModule;
use App\Models\CustomModuleRecord;
use Illuminate\Support\Str;

class ModuleBuilderService
{
    /**
     * Create custom module
     */
    public function createModule(int $tenantId, int $userId, array $data): CustomModule
    {
        $slug = Str::slug($data['name']);

        return CustomModule::create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'version' => $data['version'] ?? '1.0.0',
            'schema' => $data['schema'], // JSON schema definition
            'ui_config' => $data['ui_config'] ?? [],
            'permissions' => $data['permissions'] ?? [],
            'is_active' => true,
            'created_by_user_id' => $userId,
        ]);
    }

    /**
     * Update module schema
     */
    public function updateSchema(int $moduleId, array $schema): bool
    {
        try {
            $module = CustomModule::findOrFail($moduleId);
            $module->update([
                'schema' => $schema,
                'version' => $this->incrementVersion($module->version),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Update schema failed', [
                'module_id' => $moduleId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Add record to module
     */
    public function addRecord(int $moduleId, int $tenantId, int $userId, array $data): CustomModuleRecord
    {
        return CustomModuleRecord::create([
            'custom_module_id' => $moduleId,
            'tenant_id' => $tenantId,
            'data' => $data,
            'created_by_user_id' => $userId,
        ]);
    }

    /**
     * Update record
     */
    public function updateRecord(int $recordId, int $userId, array $data): bool
    {
        try {
            $record = CustomModuleRecord::findOrFail($recordId);
            $record->update([
                'data' => $data,
                'updated_by_user_id' => $userId,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Update record failed', [
                'record_id' => $recordId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete record
     */
    public function deleteRecord(int $recordId): bool
    {
        try {
            $record = CustomModuleRecord::findOrFail($recordId);
            $record->delete();

            return true;
        } catch (\Exception $e) {
            \Log::error('Delete record failed', [
                'record_id' => $recordId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get module records with filters
     */
    public function getRecords(int $moduleId, array $filters = []): array
    {
        $query = CustomModuleRecord::where('custom_module_id', $moduleId);

        // Apply JSON field filters
        if (! empty($filters)) {
            foreach ($filters as $field => $value) {
                $query->whereJsonContains('data', [$field => $value]);
            }
        }

        return $query->orderBy('created_at', 'desc')->get()->toArray();
    }

    /**
     * Export module as package
     */
    public function exportModule(int $moduleId): array
    {
        $module = CustomModule::with('records')->findOrFail($moduleId);

        return [
            'module' => [
                'name' => $module->name,
                'slug' => $module->slug,
                'description' => $module->description,
                'version' => $module->version,
                'schema' => $module->schema,
                'ui_config' => $module->ui_config,
            ],
            'records_count' => $module->records->count(),
            'exported_at' => now(),
        ];
    }

    /**
     * Get tenant's custom modules
     */
    public function getTenantModules(int $tenantId): array
    {
        return CustomModule::where('tenant_id', $tenantId)
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Increment semantic version
     */
    protected function incrementVersion(string $version): string
    {
        $parts = explode('.', $version);
        if (count($parts) === 3) {
            $parts[2] = intval($parts[2]) + 1;

            return implode('.', $parts);
        }

        return '1.0.1';
    }
}
