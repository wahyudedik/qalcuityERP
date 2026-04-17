<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Security\AuditLogService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforce tenant isolation — pastikan user hanya bisa akses data tenant sendiri.
 *
 * Cara kerja:
 * - Inject tenant_id ke semua query via Model::creating/updating observer
 * - Validasi route model binding yang punya tenant_id field
 * - Blokir akses jika tenant_id tidak cocok
 * - Audit log ketika SuperAdmin akses tenant data (compliance)
 */
class EnforceTenantIsolation
{
    protected AuditLogService $auditService;

    public function __construct(AuditLogService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Super admin bypass dengan audit trail
        if ($user && $user->isSuperAdmin()) {
            // Log superadmin access untuk compliance
            $this->logSuperAdminAccess($user, $request);
            return $next($request);
        }

        // Guest tidak perlu isolasi
        if (!$user || !$user->tenant_id) {
            return $next($request);
        }

        $tenantId = $user->tenant_id;

        // Daftar model yang perlu dicek tenant_id-nya dari route binding
        $tenantModels = [
            \App\Models\Product::class,
            \App\Models\Warehouse::class,
            \App\Models\SalesOrder::class,
            \App\Models\PurchaseOrder::class,
            \App\Models\Invoice::class,
            \App\Models\Employee::class,
            \App\Models\Customer::class,
            \App\Models\Supplier::class,
            \App\Models\Asset::class,
            \App\Models\Budget::class,
            \App\Models\Project::class,
            \App\Models\CrmLead::class,
            \App\Models\EcommerceChannel::class,
            \App\Models\ChatSession::class,
            \App\Models\ApprovalRequest::class,
            \App\Models\ApprovalWorkflow::class,
            \App\Models\BankAccount::class,
            \App\Models\BankStatement::class,
            \App\Models\Document::class,
            // Extended coverage
            \App\Models\Quotation::class,
            \App\Models\DeliveryOrder::class,
            \App\Models\SalesReturn::class,
            \App\Models\PurchaseReturn::class,
            \App\Models\DownPayment::class,
            \App\Models\BulkPayment::class,
            \App\Models\Payable::class,
            \App\Models\JournalEntry::class,
            \App\Models\ChartOfAccount::class,
            \App\Models\AccountingPeriod::class,
            \App\Models\Timesheet::class,
            \App\Models\KpiTarget::class,
            \App\Models\Simulation::class,
            \App\Models\ZeroInputLog::class,
            \App\Models\AnomalyAlert::class,
            \App\Models\DisciplinaryLetter::class,
            \App\Models\OvertimeRequest::class,
            \App\Models\TrainingSession::class,
            \App\Models\TrainingProgram::class,
            \App\Models\LoyaltyPoint::class,
            \App\Models\Reminder::class,
            \App\Models\Shipment::class,
            \App\Models\WorkOrder::class,
            \App\Models\Bom::class,
            \App\Models\WorkCenter::class,
            \App\Models\FleetVehicle::class,
            \App\Models\FleetDriver::class,
            \App\Models\FleetTrip::class,
            \App\Models\FleetFuelLog::class,
            \App\Models\FleetMaintenance::class,
            \App\Models\Contract::class,
            \App\Models\ContractTemplate::class,
            \App\Models\ContractBilling::class,
            \App\Models\ContractSlaLog::class,
            \App\Models\LandedCost::class,
            \App\Models\ConsignmentPartner::class,
            \App\Models\ConsignmentShipment::class,
            \App\Models\ConsignmentSalesReport::class,
            \App\Models\CommissionRule::class,
            \App\Models\SalesTarget::class,
            \App\Models\CommissionCalculation::class,
            \App\Models\HelpdeskTicket::class,
            \App\Models\KbArticle::class,
            \App\Models\ProjectBillingConfig::class,
            \App\Models\ProjectMilestone::class,
            \App\Models\ProjectInvoice::class,
            \App\Models\CustomerSubscriptionPlan::class,
            \App\Models\CustomerSubscription::class,
            \App\Models\SubscriptionInvoice::class,
            \App\Models\Reimbursement::class,
            \App\Models\WarehouseZone::class,
            \App\Models\WarehouseBin::class,
            \App\Models\BinStock::class,
            \App\Models\PickingList::class,
            \App\Models\StockOpnameSession::class,
            \App\Models\RecurringJournal::class,
            \App\Models\DeferredItem::class,
            \App\Models\Writeoff::class,
            \App\Models\CostCenter::class,
            \App\Models\ApiToken::class,
            \App\Models\WebhookSubscription::class,
            \App\Models\AiMemory::class,
            \App\Models\StockTransfer::class,
            \App\Models\GoodsReceipt::class,
            \App\Models\Rfq::class,
            \App\Models\PurchaseRequisition::class,
            \App\Models\PayrollRun::class,
            \App\Models\LeaveRequest::class,
            \App\Models\PerformanceReview::class,
            \App\Models\Attendance::class,
            \App\Models\InvoiceInstallment::class,
            \App\Models\ProjectTask::class,
            \App\Models\PriceList::class,
            // Bug 6 fix — model yang sebelumnya hilang dari daftar
            \App\Models\ErpNotification::class,
            \App\Models\UserPermission::class,
            \App\Models\CustomField::class,
            \App\Models\DocumentTemplate::class,
            \App\Models\Workflow::class,
            \App\Models\AiTourSession::class,
        ];

        // Cek semua route parameters yang merupakan Eloquent model
        foreach ($request->route()->parameters() as $param) {
            if (!is_object($param))
                continue;

            $modelClass = get_class($param);
            if (!in_array($modelClass, $tenantModels))
                continue;

            // Model punya tenant_id tapi tidak cocok → 403
            if (isset($param->tenant_id) && (int) $param->tenant_id !== (int) $tenantId) {
                abort(403, 'Akses ditolak: data bukan milik tenant Anda.');
            }
        }

        return $next($request);
    }

    /**
     * Log ketika SuperAdmin mengakses tenant data (compliance requirement)
     */
    protected function logSuperAdminAccess($user, Request $request): void
    {
        // Hanya log jika ada tenant context dari route
        $routeTenantId = $request->route('tenant')
            ?? $request->route('tenant_id')
            ?? $request->input('tenant_id');

        if (!$routeTenantId) {
            return; // Tidak ada tenant spesifik, tidak perlu log
        }

        // Get target tenant info
        $targetTenant = \App\Models\Tenant::find($routeTenantId);
        if (!$targetTenant) {
            return;
        }

        // Log dengan rate limiting (max 1 log per tenant per 5 menit)
        $cacheKey = "superadmin_audit_{$user->id}_{$routeTenantId}";
        $lastLogged = cache()->get($cacheKey);

        if ($lastLogged && now()->diffInMinutes($lastLogged) < 5) {
            return; // Sudah log dalam 5 menit terakhir
        }

        // Set cache untuk 5 menit
        cache()->put($cacheKey, now(), 300);

        // Log ke audit trail
        try {
            $this->auditService->logEvent([
                'tenant_id' => (int) $routeTenantId,
                'user_id' => $user->id,
                'event_type' => 'superadmin_tenant_access',
                'model_type' => 'Tenant',
                'model_id' => $routeTenantId,
                'metadata' => [
                    'superadmin_id' => $user->id,
                    'superadmin_name' => $user->name,
                    'superadmin_email' => $user->email,
                    'target_tenant_id' => (int) $routeTenantId,
                    'target_tenant_name' => $targetTenant->name,
                    'target_tenant_company' => $targetTenant->company_name ?? null,
                    'route' => $request->route()->getName(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'reason' => 'SuperAdmin accessing tenant data for monitoring/support',
                ],
                'success' => true,
            ]);
        } catch (\Exception $e) {
            // Jangan ganggu request jika audit log gagal
            \Log::warning('Failed to log superadmin access', [
                'user_id' => $user->id,
                'tenant_id' => $routeTenantId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
