<?php

namespace App\Http\Controllers\MultiCompany;

use App\Http\Controllers\Controller;
use App\Services\MultiCompany\CompanyGroupService;
use App\Services\MultiCompany\ConsolidationService;
use App\Services\MultiCompany\InterCompanyTransactionService;
use App\Services\MultiCompany\InventoryTransferService;
use App\Services\MultiCompany\SharedServiceService;
use Illuminate\Http\Request;

class MultiCompanyController extends Controller
{
    protected $companyGroupService;

    protected $interCompanyService;

    protected $consolidationService;

    protected $inventoryTransferService;

    protected $sharedServiceService;

    public function __construct(
        CompanyGroupService $companyGroupService,
        InterCompanyTransactionService $interCompanyService,
        ConsolidationService $consolidationService,
        InventoryTransferService $inventoryTransferService,
        SharedServiceService $sharedServiceService
    ) {
        $this->companyGroupService = $companyGroupService;
        $this->interCompanyService = $interCompanyService;
        $this->consolidationService = $consolidationService;
        $this->inventoryTransferService = $inventoryTransferService;
        $this->sharedServiceService = $sharedServiceService;
    }

    // ==================== COMPANY GROUPS ====================

    public function createGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:company_groups,code',
            'description' => 'sometimes|string',
        ]);

        $group = $this->companyGroupService->createGroup(
            auth()->user()->tenant_id,
            $request->name,
            $request->code,
            $request->description
        );

        return response()->json(['success' => true, 'group' => $group]);
    }

    public function addSubsidiary(Request $request, int $groupId)
    {
        $request->validate([
            'tenant_id' => 'required|integer',
            'ownership_percentage' => 'required|numeric|min:0|max:100',
            'role' => 'sometimes|in:subsidiary,associate,joint_venture',
        ]);

        $success = $this->companyGroupService->addSubsidiary(
            $groupId,
            $request->tenant_id,
            $request->ownership_percentage,
            $request->role ?? 'subsidiary'
        );

        return response()->json(['success' => $success]);
    }

    public function removeSubsidiary(int $groupId, int $tenantId)
    {
        $success = $this->companyGroupService->removeSubsidiary($groupId, $tenantId);

        return response()->json(['success' => $success]);
    }

    public function getMyGroups()
    {
        $groups = $this->companyGroupService->getTenantGroups(auth()->user()->tenant_id);

        return response()->json(['success' => true, 'groups' => $groups]);
    }

    public function getGroupStructure(int $groupId)
    {
        $structure = $this->companyGroupService->getGroupStructure($groupId);

        return response()->json(['success' => true, 'structure' => $structure]);
    }

    public function updateOwnership(Request $request, int $groupId, int $tenantId)
    {
        $request->validate([
            'ownership_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $success = $this->companyGroupService->updateOwnership(
            $groupId,
            $tenantId,
            $request->ownership_percentage
        );

        return response()->json(['success' => $success]);
    }

    // ==================== INTER-COMPANY TRANSACTIONS ====================

    public function createTransaction(Request $request)
    {
        $request->validate([
            'company_group_id' => 'required|integer',
            'from_tenant_id' => 'required|integer',
            'to_tenant_id' => 'required|integer',
            'transaction_type' => 'required|in:sale,purchase,loan,transfer,service_fee',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'due_date' => 'sometimes|date|after:transaction_date',
        ]);

        $transaction = $this->interCompanyService->createTransaction([
            'company_group_id' => $request->company_group_id,
            'from_tenant_id' => $request->from_tenant_id,
            'to_tenant_id' => $request->to_tenant_id,
            'transaction_type' => $request->transaction_type,
            'reference_type' => $request->reference_type ?? null,
            'reference_id' => $request->reference_id ?? null,
            'amount' => $request->amount,
            'currency' => $request->currency ?? 'IDR',
            'exchange_rate' => $request->exchange_rate ?? 1.0,
            'transaction_date' => $request->transaction_date,
            'due_date' => $request->due_date ?? null,
            'description' => $request->description ?? null,
            'line_items' => $request->line_items ?? null,
            'created_by_user_id' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'transaction' => $transaction]);
    }

    public function approveTransaction(int $transactionId)
    {
        $success = $this->interCompanyService->approveTransaction($transactionId, auth()->id());

        return response()->json(['success' => $success]);
    }

    public function rejectTransaction(Request $request, int $transactionId)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $success = $this->interCompanyService->rejectTransaction($transactionId, $request->reason);

        return response()->json(['success' => $success]);
    }

    public function completeTransaction(int $transactionId)
    {
        $success = $this->interCompanyService->completeTransaction($transactionId);

        return response()->json(['success' => $success]);
    }

    public function getPendingTransactions(int $groupId)
    {
        $transactions = $this->interCompanyService->getPendingTransactions($groupId);

        return response()->json(['success' => true, 'transactions' => $transactions]);
    }

    public function getTransactionHistory(Request $request, int $groupId)
    {
        $type = $request->query('type');
        $status = $request->query('status');

        $history = $this->interCompanyService->getTransactionHistory($groupId, $type, $status);

        return response()->json(['success' => true, 'history' => $history]);
    }

    public function reconcileAccounts(int $groupId, int $tenantId, int $counterpartyId)
    {
        $reconciliation = $this->interCompanyService->reconcileAccounts($groupId, $tenantId, $counterpartyId);

        return response()->json(['success' => true, 'reconciliation' => $reconciliation]);
    }

    // ==================== CONSOLIDATED REPORTS ====================

    public function generateBalanceSheet(Request $request, int $groupId)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
        ]);

        $report = $this->consolidationService->generateBalanceSheet(
            $groupId,
            $request->period_start,
            $request->period_end,
            auth()->id()
        );

        return response()->json(['success' => true, 'report' => $report]);
    }

    public function generateIncomeStatement(Request $request, int $groupId)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
        ]);

        $report = $this->consolidationService->generateIncomeStatement(
            $groupId,
            $request->period_start,
            $request->period_end,
            auth()->id()
        );

        return response()->json(['success' => true, 'report' => $report]);
    }

    public function finalizeReport(int $reportId)
    {
        $success = $this->consolidationService->finalizeReport($reportId);

        return response()->json(['success' => $success]);
    }

    public function approveReport(int $reportId)
    {
        $success = $this->consolidationService->approveReport($reportId, auth()->id());

        return response()->json(['success' => $success]);
    }

    public function getReportHistory(Request $request, int $groupId)
    {
        $reportType = $request->query('report_type');
        $history = $this->consolidationService->getReportHistory($groupId, $reportType);

        return response()->json(['success' => true, 'history' => $history]);
    }

    // ==================== INVENTORY TRANSFERS ====================

    public function createTransfer(Request $request)
    {
        $request->validate([
            'company_group_id' => 'required|integer',
            'from_tenant_id' => 'required|integer',
            'to_tenant_id' => 'required|integer',
            'transfer_date' => 'required|date',
            'items' => 'required|array|min:1',
        ]);

        $transfer = $this->inventoryTransferService->createTransfer([
            'company_group_id' => $request->company_group_id,
            'from_tenant_id' => $request->from_tenant_id,
            'to_tenant_id' => $request->to_tenant_id,
            'transfer_date' => $request->transfer_date,
            'expected_arrival_date' => $request->expected_arrival_date ?? null,
            'shipping_method' => $request->shipping_method ?? null,
            'shipping_cost' => $request->shipping_cost ?? 0.00,
            'notes' => $request->notes ?? null,
            'items' => $request->items,
            'created_by_user_id' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'transfer' => $transfer]);
    }

    public function sendTransfer(Request $request, int $transferId)
    {
        $trackingNumber = $request->input('tracking_number');
        $success = $this->inventoryTransferService->sendTransfer($transferId, $trackingNumber);

        return response()->json(['success' => $success]);
    }

    public function receiveTransfer(Request $request, int $transferId)
    {
        $receivedQuantities = $request->input('received_quantities', []);
        $success = $this->inventoryTransferService->receiveTransfer(
            $transferId,
            auth()->id(),
            $receivedQuantities
        );

        return response()->json(['success' => $success]);
    }

    public function cancelTransfer(int $transferId)
    {
        $success = $this->inventoryTransferService->cancelTransfer($transferId);

        return response()->json(['success' => $success]);
    }

    public function getTransferByNumber(string $transferNumber)
    {
        $transfer = $this->inventoryTransferService->getTransferByNumber($transferNumber);

        return response()->json(['success' => true, 'transfer' => $transfer]);
    }

    public function getPendingTransfers(int $groupId)
    {
        $transfers = $this->inventoryTransferService->getPendingTransfers($groupId);

        return response()->json(['success' => true, 'transfers' => $transfers]);
    }

    public function getTransferHistory(Request $request, int $groupId)
    {
        $status = $request->query('status');
        $history = $this->inventoryTransferService->getTransferHistory($groupId, $status);

        return response()->json(['success' => true, 'history' => $history]);
    }

    // ==================== SHARED SERVICES ====================

    public function createService(Request $request)
    {
        $request->validate([
            'company_group_id' => 'required|integer',
            'provider_tenant_id' => 'required|integer',
            'service_name' => 'required|string',
            'billing_method' => 'sometimes|in:allocation,fixed_fee,usage_based',
        ]);

        $service = $this->sharedServiceService->createService([
            'company_group_id' => $request->company_group_id,
            'provider_tenant_id' => $request->provider_tenant_id,
            'service_name' => $request->service_name,
            'description' => $request->description ?? null,
            'billing_method' => $request->billing_method ?? 'allocation',
            'fixed_fee' => $request->fixed_fee ?? null,
            'allocation_rules' => $request->allocation_rules ?? null,
        ]);

        return response()->json(['success' => true, 'service' => $service]);
    }

    public function subscribeToService(Request $request, int $serviceId)
    {
        $request->validate([
            'tenant_id' => 'required|integer',
            'allocation_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $success = $this->sharedServiceService->subscribeTenant(
            $serviceId,
            $request->tenant_id,
            $request->allocation_percentage
        );

        return response()->json(['success' => $success]);
    }

    public function generateBillings(Request $request, int $serviceId)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
        ]);

        $billings = $this->sharedServiceService->generateBillings(
            $serviceId,
            $request->period_start,
            $request->period_end
        );

        return response()->json(['success' => true, 'billings' => $billings]);
    }

    public function markBillingAsInvoiced(Request $request, int $billingId)
    {
        $request->validate([
            'invoice_id' => 'required|integer',
        ]);

        $success = $this->sharedServiceService->markAsInvoiced($billingId, $request->invoice_id);

        return response()->json(['success' => $success]);
    }

    public function markBillingAsPaid(int $billingId)
    {
        $success = $this->sharedServiceService->markAsPaid($billingId);

        return response()->json(['success' => $success]);
    }

    public function getServiceSubscribers(int $serviceId)
    {
        $subscribers = $this->sharedServiceService->getServiceSubscribers($serviceId);

        return response()->json(['success' => true, 'subscribers' => $subscribers]);
    }

    public function getPendingBillings(int $groupId)
    {
        $billings = $this->sharedServiceService->getPendingBillings($groupId);

        return response()->json(['success' => true, 'billings' => $billings]);
    }

    // ==================== DASHBOARD ====================

    public function dashboard()
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Multi-Company Dashboard',
            ]);
        }

        return view('multi-company.dashboard');
    }
}
