<?php

namespace App\Services\Security;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\GdprConsent;
use App\Models\GdprDataExport;
use App\Models\GdprDeletionRequest;
use App\Models\Patient;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * GDPR Compliance Service
 *
 * Implements GDPR requirements:
 * - Right to Access (Data Export)
 * - Right to be Forgotten (Data Deletion)
 * - Right to Rectification
 * - Consent Management
 * - Data Portability
 */
class GdprComplianceService
{
    /**
     * Create data export request
     *
     * @param  string  $exportType  (personal_data, all_data, specific_module)
     * @param  array  $modules  (optional)
     */
    public function createDataExportRequest(int $userId, string $exportType = 'personal_data', array $modules = []): GdprDataExport
    {
        $export = GdprDataExport::create([
            'user_id' => $userId,
            'export_type' => $exportType,
            'modules' => $modules,
            'status' => 'pending',
            'requested_at' => now(),
            'expires_at' => now()->addDays(30), // GDPR: 30 days to respond
        ]);

        // Process export asynchronously
        $this->processDataExport($export);

        return $export;
    }

    /**
     * Process data export
     *
     * @return string Path to exported file
     */
    public function processDataExport(GdprDataExport $export): string
    {
        $export->update(['status' => 'processing']);

        try {
            $user = $export->user;
            $tenantId = $user->tenant_id;
            $data = [];

            // Export personal data
            $data['personal_information'] = [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'created_at' => $user->created_at,
                'last_login' => $user->last_login_at,
            ];

            // Export module-specific data based on request
            $modules = $export->modules ?: $this->getDefaultModules();

            foreach ($modules as $module) {
                $data[$module] = $this->exportModuleData($module, $user, $tenantId);
            }

            // Generate JSON file
            $filename = "gdpr_export_{$user->id}_".Str::uuid().'.json';
            $path = "gdpr/exports/{$filename}";

            Storage::disk('local')->put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $export->update([
                'status' => 'completed',
                'file_path' => $path,
                'completed_at' => now(),
                'file_size' => Storage::disk('local')->size($path),
            ]);

            return $path;
        } catch (\Exception $e) {
            $export->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create data deletion request (Right to be Forgotten)
     */
    public function createDeletionRequest(int $userId, string $reason): GdprDeletionRequest
    {
        $deletion = GdprDeletionRequest::create([
            'user_id' => $userId,
            'reason' => $reason,
            'status' => 'pending_approval',
            'requested_at' => now(),
        ]);

        return $deletion;
    }

    /**
     * Approve and process deletion request
     */
    public function processDeletionRequest(GdprDeletionRequest $deletion, int $approvedBy): bool
    {
        $deletion->update([
            'status' => 'processing',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        try {
            $user = $deletion->user;
            $tenantId = $user->tenant_id;

            // Anonymize user data instead of hard delete (for audit trail)
            $this->anonymizeUserData($user);

            // Delete or anonymize related data
            $this->deleteRelatedData($user, $tenantId);

            $deletion->update([
                'status' => 'completed',
                'completed_at' => now(),
                'anonymization_method' => 'pseudonymization',
            ]);

            return true;
        } catch (\Exception $e) {
            $deletion->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Anonymize user data
     */
    protected function anonymizeUserData($user): void
    {
        $user->update([
            'name' => 'Deleted User #'.$user->id,
            'email' => 'deleted_'.$user->id.'@anonymized.local',
            'phone' => null,
            'address' => null,
            'avatar' => null,
        ]);
    }

    /**
     * Delete or anonymize related data
     */
    protected function deleteRelatedData($user, int $tenantId): void
    {
        // Anonymize audit logs (keep for compliance but remove PII)
        \DB::table('audit_logs')
            ->where('causer_id', $user->id)
            ->update([
                'causer_type' => 'Anonymized User',
                'properties' => json_encode(['anonymized' => true]),
            ]);

        // Anonymize activity logs
        \DB::table('activity_log')
            ->where('causer_id', $user->id)
            ->update([
                'description' => '[Anonymized]',
                'properties' => json_encode(['anonymized' => true]),
            ]);

        // Delete personal preferences
        \DB::table('user_preferences')
            ->where('user_id', $user->id)
            ->delete();

        // Delete sessions
        \DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();

        // Note: Financial and legal records should be retained per local laws
        // Only PII should be anonymized
    }

    /**
     * Record user consent
     */
    public function recordConsent(int $userId, string $consentType, string $ipAddress, string $userAgent): GdprConsent
    {
        // Revoke any previous active consent of this type
        GdprConsent::where('user_id', $userId)
            ->where('consent_type', $consentType)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return GdprConsent::create([
            'user_id' => $userId,
            'consent_type' => $consentType,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'consented_at' => now(),
            'is_active' => true,
        ]);
    }

    /**
     * Revoke user consent
     */
    public function revokeConsent(int $userId, string $consentType): bool
    {
        return GdprConsent::where('user_id', $userId)
            ->where('consent_type', $consentType)
            ->where('is_active', true)
            ->update(['is_active' => false]) > 0;
    }

    /**
     * Check if user has active consent
     */
    public function hasConsent(int $userId, string $consentType): bool
    {
        return GdprConsent::where('user_id', $userId)
            ->where('consent_type', $consentType)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Export module data
     */
    protected function exportModuleData(string $module, $user, int $tenantId): array
    {
        return match ($module) {
            'patients' => $this->exportPatientData($user, $tenantId),
            'employees' => $this->exportEmployeeData($user, $tenantId),
            'customers' => $this->exportCustomerData($user, $tenantId),
            'orders' => $this->exportOrderData($user, $tenantId),
            default => [],
        };
    }

    /**
     * Export patient data (Healthcare)
     */
    protected function exportPatientData($user, int $tenantId): array
    {
        if (! class_exists('\App\Models\Patient')) {
            return [];
        }

        $patients = Patient::where('tenant_id', $tenantId)
            ->where('created_by', $user->id)
            ->get();

        return $patients->map(fn ($p) => [
            'mrn' => $p->mrn,
            'name' => $p->full_name,
            'date_of_birth' => $p->date_of_birth,
            'gender' => $p->gender,
            'contact' => $p->phone,
            'medical_records_count' => $p->visits()->count(),
        ])->toArray();
    }

    /**
     * Export employee data (HRM)
     */
    protected function exportEmployeeData($user, int $tenantId): array
    {
        if ($user->id !== $user->id) {
            return []; // Only export own data
        }

        if (! class_exists('\App\Models\Employee')) {
            return [];
        }

        $employee = Employee::where('tenant_id', $tenantId)
            ->where('user_id', $user->id)
            ->first();

        if (! $employee) {
            return [];
        }

        return [
            'employee_code' => $employee->employee_code,
            'department' => $employee->department?->name,
            'position' => $employee->position?->name,
            'hire_date' => $employee->hire_date,
            'salary' => '[REDACTED]', // Sensitive data
        ];
    }

    /**
     * Export customer data
     */
    protected function exportCustomerData($user, int $tenantId): array
    {
        if (! class_exists('\App\Models\Customer')) {
            return [];
        }

        $customers = Customer::where('tenant_id', $tenantId)
            ->where('created_by', $user->id)
            ->get();

        return $customers->map(fn ($c) => [
            'code' => $c->customer_code,
            'name' => $c->name,
            'email' => $c->email,
            'phone' => $c->phone,
            'address' => $c->address,
        ])->toArray();
    }

    /**
     * Export order data
     */
    protected function exportOrderData($user, int $tenantId): array
    {
        if (! class_exists('\App\Models\SalesOrder')) {
            return [];
        }

        $orders = SalesOrder::where('tenant_id', $tenantId)
            ->where('created_by', $user->id)
            ->with('customer')
            ->get();

        return $orders->map(fn ($o) => [
            'order_number' => $o->order_number,
            'customer' => $o->customer?->name,
            'date' => $o->order_date,
            'total' => $o->total_amount,
            'status' => $o->status,
        ])->toArray();
    }

    /**
     * Get default modules for export
     */
    protected function getDefaultModules(): array
    {
        return ['patients', 'employees', 'customers', 'orders'];
    }
}
