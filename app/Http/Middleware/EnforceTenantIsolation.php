<?php

namespace App\Http\Middleware;

use App\Models\AccountingPeriod;
use App\Models\AiMemory;
use App\Models\AiTourSession;
use App\Models\AnomalyAlert;
use App\Models\ApiToken;
use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\Asset;
use App\Models\Attendance;
use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\BinStock;
use App\Models\Bom;
use App\Models\Budget;
use App\Models\BulkPayment;
use App\Models\ChartOfAccount;
use App\Models\ChatSession;
use App\Models\CommissionCalculation;
use App\Models\CommissionRule;
use App\Models\ConsignmentPartner;
use App\Models\ConsignmentSalesReport;
use App\Models\ConsignmentShipment;
use App\Models\Contract;
use App\Models\ContractBilling;
use App\Models\ContractSlaLog;
use App\Models\ContractTemplate;
use App\Models\CostCenter;
use App\Models\CrmLead;
use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\CustomerSubscriptionPlan;
use App\Models\CustomField;
use App\Models\DeferredItem;
use App\Models\DeliveryOrder;
use App\Models\DisciplinaryLetter;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\DownPayment;
use App\Models\EcommerceChannel;
use App\Models\Employee;
use App\Models\ErpNotification;
use App\Models\FleetDriver;
use App\Models\FleetFuelLog;
use App\Models\FleetMaintenance;
use App\Models\FleetTrip;
use App\Models\FleetVehicle;
use App\Models\GoodsReceipt;
use App\Models\HelpdeskTicket;
use App\Models\Invoice;
use App\Models\InvoiceInstallment;
use App\Models\JournalEntry;
use App\Models\KbArticle;
use App\Models\KpiTarget;
use App\Models\LandedCost;
use App\Models\LeaveRequest;
use App\Models\LoyaltyPoint;
use App\Models\OvertimeRequest;
use App\Models\Payable;
use App\Models\PayrollRun;
use App\Models\PerformanceReview;
use App\Models\PickingList;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectBillingConfig;
use App\Models\ProjectInvoice;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseReturn;
use App\Models\Quotation;
use App\Models\RecurringJournal;
use App\Models\Reimbursement;
use App\Models\Reminder;
use App\Models\Rfq;
use App\Models\SalesOrder;
use App\Models\SalesReturn;
use App\Models\SalesTarget;
use App\Models\Shipment;
use App\Models\Simulation;
use App\Models\StockOpnameSession;
use App\Models\StockTransfer;
use App\Models\SubscriptionInvoice;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\Timesheet;
use App\Models\TrainingProgram;
use App\Models\TrainingSession;
use App\Models\UserPermission;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
use App\Models\WarehouseZone;
use App\Models\WebhookSubscription;
use App\Models\WorkCenter;
use App\Models\Workflow;
use App\Models\WorkOrder;
use App\Models\Writeoff;
use App\Models\ZeroInputLog;
use App\Services\Security\AuditLogService;
use Closure;
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
        if (! $user || ! $user->tenant_id) {
            return $next($request);
        }

        $tenantId = $user->tenant_id;

        // Daftar model yang perlu dicek tenant_id-nya dari route binding
        $tenantModels = [
            Product::class,
            Warehouse::class,
            SalesOrder::class,
            PurchaseOrder::class,
            Invoice::class,
            Employee::class,
            Customer::class,
            Supplier::class,
            Asset::class,
            Budget::class,
            Project::class,
            CrmLead::class,
            EcommerceChannel::class,
            ChatSession::class,
            ApprovalRequest::class,
            ApprovalWorkflow::class,
            BankAccount::class,
            BankStatement::class,
            Document::class,
            // Extended coverage
            Quotation::class,
            DeliveryOrder::class,
            SalesReturn::class,
            PurchaseReturn::class,
            DownPayment::class,
            BulkPayment::class,
            Payable::class,
            JournalEntry::class,
            ChartOfAccount::class,
            AccountingPeriod::class,
            Timesheet::class,
            KpiTarget::class,
            Simulation::class,
            ZeroInputLog::class,
            AnomalyAlert::class,
            DisciplinaryLetter::class,
            OvertimeRequest::class,
            TrainingSession::class,
            TrainingProgram::class,
            LoyaltyPoint::class,
            Reminder::class,
            Shipment::class,
            WorkOrder::class,
            Bom::class,
            WorkCenter::class,
            FleetVehicle::class,
            FleetDriver::class,
            FleetTrip::class,
            FleetFuelLog::class,
            FleetMaintenance::class,
            Contract::class,
            ContractTemplate::class,
            ContractBilling::class,
            ContractSlaLog::class,
            LandedCost::class,
            ConsignmentPartner::class,
            ConsignmentShipment::class,
            ConsignmentSalesReport::class,
            CommissionRule::class,
            SalesTarget::class,
            CommissionCalculation::class,
            HelpdeskTicket::class,
            KbArticle::class,
            ProjectBillingConfig::class,
            ProjectMilestone::class,
            ProjectInvoice::class,
            CustomerSubscriptionPlan::class,
            CustomerSubscription::class,
            SubscriptionInvoice::class,
            Reimbursement::class,
            WarehouseZone::class,
            WarehouseBin::class,
            BinStock::class,
            PickingList::class,
            StockOpnameSession::class,
            RecurringJournal::class,
            DeferredItem::class,
            Writeoff::class,
            CostCenter::class,
            ApiToken::class,
            WebhookSubscription::class,
            AiMemory::class,
            StockTransfer::class,
            GoodsReceipt::class,
            Rfq::class,
            PurchaseRequisition::class,
            PayrollRun::class,
            LeaveRequest::class,
            PerformanceReview::class,
            Attendance::class,
            InvoiceInstallment::class,
            ProjectTask::class,
            PriceList::class,
            // Bug 6 fix — model yang sebelumnya hilang dari daftar
            ErpNotification::class,
            UserPermission::class,
            CustomField::class,
            DocumentTemplate::class,
            Workflow::class,
            AiTourSession::class,
        ];

        // Cek semua route parameters yang merupakan Eloquent model
        foreach ($request->route()->parameters() as $param) {
            if (! is_object($param)) {
                continue;
            }

            $modelClass = get_class($param);
            if (! in_array($modelClass, $tenantModels)) {
                continue;
            }

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

        if (! $routeTenantId) {
            return; // Tidak ada tenant spesifik, tidak perlu log
        }

        // Get target tenant info
        $targetTenant = Tenant::find($routeTenantId);
        if (! $targetTenant) {
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
