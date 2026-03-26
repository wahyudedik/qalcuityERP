<?php

namespace App\Http\Middleware;

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
 */
class EnforceTenantIsolation
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Super admin & guest tidak perlu isolasi
        if (!$user || $user->isSuperAdmin() || !$user->tenant_id) {
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
        ];

        // Cek semua route parameters yang merupakan Eloquent model
        foreach ($request->route()->parameters() as $param) {
            if (!is_object($param)) continue;

            $modelClass = get_class($param);
            if (!in_array($modelClass, $tenantModels)) continue;

            // Model punya tenant_id tapi tidak cocok → 403
            if (isset($param->tenant_id) && (int) $param->tenant_id !== (int) $tenantId) {
                abort(403, 'Akses ditolak: data bukan milik tenant Anda.');
            }
        }

        return $next($request);
    }
}
