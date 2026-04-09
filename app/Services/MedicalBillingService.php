<?php

namespace App\Services;

use App\Models\MedicalBill;
use App\Models\BillItem;
use App\Models\InsuranceClaim;
use App\Models\InsuranceAdjudication;
use App\Models\Copayment;
use App\Models\PaymentPlan;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MedicalBillingService
{
    /**
     * Generate medical bill from visit/admission
     */
    public function generateBill(array $billData): MedicalBill
    {
        return DB::transaction(function () use ($billData) {
            $bill = MedicalBill::create([
                'patient_id' => $billData['patient_id'],
                'visit_id' => $billData['visit_id'] ?? null,
                'admission_id' => $billData['admission_id'] ?? null,
                'bill_number' => $this->generateBillNumber(),
                'bill_date' => $billData['bill_date'] ?? today(),
                'due_date' => $billData['due_date'] ?? now()->addDays(30),
                'discount_percentage' => $billData['discount_percentage'] ?? 0,
                'has_insurance' => $billData['has_insurance'] ?? false,
                'insurance_provider_id' => $billData['insurance_provider_id'] ?? null,
                'policy_number' => $billData['policy_number'] ?? null,
                'group_number' => $billData['group_number'] ?? null,
                'financial_class' => $billData['financial_class'] ?? 'self_pay',
                'billing_status' => 'draft',
                'payment_status' => 'unpaid',
            ]);

            // Add bill items
            if (!empty($billData['items'])) {
                foreach ($billData['items'] as $item) {
                    $this->addBillItem($bill->id, $item);
                }
            }

            // Calculate totals
            $bill->load('items');
            $bill->calculateTotals();

            Log::info("Medical bill generated", [
                'bill_number' => $bill->bill_number,
                'total_amount' => $bill->total_amount,
                'patient_payable' => $bill->patient_payable,
            ]);

            return $bill;
        });
    }

    /**
     * Add bill item
     */
    public function addBillItem(int $billId, array $itemData): BillItem
    {
        $total = $itemData['quantity'] * $itemData['unit_price'];
        $discount = $total * ($itemData['discount_percentage'] / 100);
        $afterDiscount = $total - $discount;

        return BillItem::create([
            'bill_id' => $billId,
            'item_type' => $itemData['item_type'],
            'item_id' => $itemData['item_id'] ?? null,
            'item_code' => $itemData['item_code'] ?? null,
            'item_name' => $itemData['item_name'],
            'description' => $itemData['description'] ?? null,
            'quantity' => $itemData['quantity'],
            'unit_price' => $itemData['unit_price'],
            'discount_percentage' => $itemData['discount_percentage'] ?? 0,
            'discount_amount' => $discount,
            'total' => $afterDiscount,
            'is_covered_by_insurance' => $itemData['is_covered_by_insurance'] ?? false,
            'category' => $itemData['category'] ?? null,
        ]);
    }

    /**
     * Finalize bill
     */
    public function finalizeBill(int $billId): MedicalBill
    {
        return DB::transaction(function () use ($billId) {
            $bill = MedicalBill::findOrFail($billId);

            $bill->update([
                'billing_status' => 'finalized',
                'finalized_at' => now(),
            ]);

            // If has insurance, create claim
            if ($bill->has_insurance) {
                $this->createInsuranceClaim($bill);
            }

            return $bill;
        });
    }

    /**
     * Create insurance claim
     */
    public function createInsuranceClaim(MedicalBill $bill): InsuranceClaim
    {
        return DB::transaction(function () use ($bill) {
            $claim = InsuranceClaim::create([
                'bill_id' => $bill->id,
                'patient_id' => $bill->patient_id,
                'insurance_provider_id' => $bill->insurance_provider_id,
                'claim_number' => $this->generateClaimNumber(),
                'claim_date' => today(),
                'billed_amount' => $bill->total_amount,
                'claim_amount' => $bill->insurance_coverage,
                'status' => 'draft',
                'policy_number' => $bill->policy_number,
                'group_number' => $bill->group_number,
                'service_date_from' => $bill->bill_date,
                'service_date_to' => $bill->bill_date,
            ]);

            return $claim;
        });
    }

    /**
     * Submit insurance claim (BPJS/Private)
     */
    public function submitClaim(int $claimId, string $method = 'electronic'): InsuranceClaim
    {
        return DB::transaction(function () use ($claimId, $method) {
            $claim = InsuranceClaim::findOrFail($claimId);

            // Validate claim before submission
            $this->validateClaim($claim);

            // Prepare claim data
            $claimData = $this->prepareClaimSubmission($claim);

            // Submit to insurance/clearinghouse
            $response = $this->submitToInsurance($claim, $claimData, $method);

            $claim->update([
                'status' => 'submitted',
                'submission_method' => $method,
                'submitted_date' => now(),
                'submission_response' => json_encode($response),
            ]);

            // Update bill status
            $claim->bill->update([
                'billing_status' => 'submitted',
            ]);

            Log::info("Insurance claim submitted", [
                'claim_number' => $claim->claim_number,
                'method' => $method,
                'amount' => $claim->claim_amount,
            ]);

            return $claim;
        });
    }

    /**
     * Process insurance adjudication
     */
    public function processAdjudication(int $claimId, array $adjudicationData): InsuranceAdjudication
    {
        return DB::transaction(function () use ($claimId, $adjudicationData) {
            $claim = InsuranceClaim::findOrFail($claimId);

            $adjudication = InsuranceAdjudication::create([
                'claim_id' => $claimId,
                'adjudication_date' => now(),
                'billed_amount' => $adjudicationData['billed_amount'],
                'allowed_amount' => $adjudicationData['allowed_amount'],
                'deductible_amount' => $adjudicationData['deductible_amount'] ?? 0,
                'copay_amount' => $adjudicationData['copay_amount'] ?? 0,
                'coinsurance_amount' => $adjudicationData['coinsurance_amount'] ?? 0,
                'approved_amount' => $adjudicationData['approved_amount'],
                'rejected_amount' => $adjudicationData['rejected_amount'] ?? 0,
                'paid_amount' => $adjudicationData['paid_amount'] ?? 0,
                'line_items' => $adjudicationData['line_items'] ?? null,
                'has_rejection' => ($adjudicationData['rejected_amount'] ?? 0) > 0,
                'rejection_reason' => $adjudicationData['rejection_reason'] ?? null,
                'rejection_codes' => $adjudicationData['rejection_codes'] ?? null,
                'remittance_number' => $adjudicationData['remittance_number'] ?? null,
            ]);

            // Update claim status
            $status = $adjudication->has_rejection ? 'partially_approved' : 'approved';
            $claim->update([
                'status' => $status,
                'approved_amount' => $adjudication->approved_amount,
                'rejected_amount' => $adjudication->rejected_amount,
                'processed_date' => now(),
            ]);

            // Update bill with insurance coverage
            $claim->bill->update([
                'insurance_coverage' => $adjudication->approved_amount,
                'patient_payable' => $claim->bill->total_amount - $adjudication->approved_amount,
                'balance_due' => $claim->bill->total_amount - $adjudication->approved_amount - $claim->bill->amount_paid,
                'billing_status' => 'approved',
            ]);

            Log::info("Insurance claim adjudicated", [
                'claim_number' => $claim->claim_number,
                'approved' => $adjudication->approved_amount,
                'rejected' => $adjudication->rejected_amount,
            ]);

            return $adjudication;
        });
    }

    /**
     * Collect copayment
     */
    public function collectCopayment(int $billId, array $copayData): Copayment
    {
        return DB::transaction(function () use ($billId, $copayData) {
            $bill = MedicalBill::findOrFail($billId);

            $copayment = Copayment::create([
                'bill_id' => $billId,
                'patient_id' => $bill->patient_id,
                'collected_by' => $copayData['collected_by'],
                'copay_number' => $this->generateCopayNumber(),
                'copay_date' => $copayData['copay_date'] ?? today(),
                'copay_amount' => $copayData['copay_amount'],
                'collected_amount' => $copayData['collected_amount'],
                'payment_method' => $copayData['payment_method'] ?? 'cash',
                'transaction_reference' => $copayData['transaction_reference'] ?? null,
                'status' => 'collected',
            ]);

            // Update bill payment
            $bill->increment('amount_paid', $copayData['collected_amount']);
            $bill->decrement('balance_due', $copayData['collected_amount']);

            // Update payment status
            if ($bill->isFullyPaid()) {
                $bill->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);
            } else {
                $bill->update([
                    'payment_status' => 'partial',
                ]);
            }

            return $copayment;
        });
    }

    /**
     * Create payment plan
     */
    public function createPaymentPlan(int $billId, array $planData): PaymentPlan
    {
        return DB::transaction(function () use ($billId, $planData) {
            $bill = MedicalBill::findOrFail($billId);

            $installmentAmount = $bill->balance_due / $planData['installment_count'];

            // Generate payment schedule
            $schedule = $this->generatePaymentSchedule(
                $planData['first_payment_date'],
                $planData['installment_count'],
                $planData['frequency'] ?? 'monthly',
                $installmentAmount
            );

            $paymentPlan = PaymentPlan::create([
                'bill_id' => $billId,
                'patient_id' => $bill->patient_id,
                'plan_number' => $this->generatePlanNumber(),
                'plan_date' => today(),
                'total_amount' => $bill->balance_due,
                'down_payment' => $planData['down_payment'] ?? 0,
                'remaining_balance' => $bill->balance_due - ($planData['down_payment'] ?? 0),
                'installment_count' => $planData['installment_count'],
                'installment_amount' => $installmentAmount,
                'first_payment_date' => $planData['first_payment_date'],
                'frequency' => $planData['frequency'] ?? 'monthly',
                'payment_schedule' => $schedule,
                'status' => 'active',
                'next_payment_date' => $schedule[0]['date'] ?? null,
            ]);

            // Process down payment if any
            if ($planData['down_payment'] > 0) {
                $bill->increment('amount_paid', $planData['down_payment']);
                $bill->decrement('balance_due', $planData['down_payment']);
            }

            Log::info("Payment plan created", [
                'plan_number' => $paymentPlan->plan_number,
                'installments' => $planData['installment_count'],
                'amount' => $installmentAmount,
            ]);

            return $paymentPlan;
        });
    }

    /**
     * Get aging report
     */
    public function getAgingReport(): array
    {
        $bills = MedicalBill::where('payment_status', '!=', 'paid')
            ->where('balance_due', '>', 0)
            ->get();

        $aging = [
            'current' => ['count' => 0, 'amount' => 0],
            '31-60' => ['count' => 0, 'amount' => 0],
            '61-90' => ['count' => 0, 'amount' => 0],
            '91-120' => ['count' => 0, 'amount' => 0],
            '120+' => ['count' => 0, 'amount' => 0],
        ];

        foreach ($bills as $bill) {
            $bucket = $bill->aging_bucket;
            if (isset($aging[$bucket])) {
                $aging[$bucket]['count']++;
                $aging[$bucket]['amount'] += $bill->balance_due;
            }
        }

        return $aging;
    }

    /**
     * Get billing dashboard
     */
    public function getDashboardData(): array
    {
        return [
            'bills_today' => MedicalBill::whereDate('bill_date', today())->count(),
            'total_revenue_today' => MedicalBill::whereDate('bill_date', today())
                ->sum('total_amount'),
            'pending_claims' => InsuranceClaim::whereIn('status', ['draft', 'submitted', 'under_review'])->count(),
            'overdue_bills' => MedicalBill::overdue()->count(),
            'total_outstanding' => MedicalBill::where('balance_due', '>', 0)->sum('balance_due'),
            'payment_collection_rate' => $this->getCollectionRate(),
            'aging_report' => $this->getAgingReport(),
            'active_payment_plans' => PaymentPlan::where('status', 'active')->count(),
        ];
    }

    /**
     * Generate bill number
     */
    protected function generateBillNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'BILL-' . $date;

        $lastBill = MedicalBill::where('bill_number', 'like', $prefix . '%')
            ->orderBy('bill_number', 'desc')
            ->first();

        if ($lastBill) {
            $lastNumber = (int) substr($lastBill->bill_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $newNumber;
    }

    /**
     * Generate claim number
     */
    protected function generateClaimNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'CLAIM-' . $date;

        $lastClaim = InsuranceClaim::where('claim_number', 'like', $prefix . '%')
            ->orderBy('claim_number', 'desc')
            ->first();

        if ($lastClaim) {
            $lastNumber = (int) substr($lastClaim->claim_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $newNumber;
    }

    /**
     * Generate copay number
     */
    protected function generateCopayNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'COPAY-' . $date;

        $lastCopay = Copayment::where('copay_number', 'like', $prefix . '%')
            ->orderBy('copay_number', 'desc')
            ->first();

        return $prefix . '-' . str_pad(
            $lastCopay ? (int) substr($lastCopay->copay_number, -4) + 1 : 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Generate plan number
     */
    protected function generatePlanNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'PLAN-' . $date;

        $lastPlan = PaymentPlan::where('plan_number', 'like', $prefix . '%')
            ->orderBy('plan_number', 'desc')
            ->first();

        return $prefix . '-' . str_pad(
            $lastPlan ? (int) substr($lastPlan->plan_number, -4) + 1 : 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Validate claim before submission
     */
    protected function validateClaim(InsuranceClaim $claim): void
    {
        if (empty($claim->policy_number)) {
            throw new Exception('Policy number is required for insurance claim.');
        }

        if ($claim->billed_amount <= 0) {
            throw new Exception('Billed amount must be greater than zero.');
        }
    }

    /**
     * Prepare claim submission data
     */
    protected function prepareClaimSubmission(InsuranceClaim $claim): array
    {
        return [
            'claim_number' => $claim->claim_number,
            'patient_id' => $claim->patient_id,
            'policy_number' => $claim->policy_number,
            'group_number' => $claim->group_number,
            'service_dates' => [
                'from' => $claim->service_date_from,
                'to' => $claim->service_date_to,
            ],
            'diagnosis_codes' => $claim->diagnosis_codes,
            'procedure_codes' => $claim->procedure_codes,
            'billed_amount' => $claim->billed_amount,
            'claim_amount' => $claim->claim_amount,
            'bill_items' => $claim->bill->items->map(function ($item) {
                return [
                    'code' => $item->item_code,
                    'description' => $item->item_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                ];
            })->toArray(),
        ];
    }

    /**
     * Submit to insurance (placeholder for API integration)
     */
    protected function submitToInsurance(InsuranceClaim $claim, array $claimData, string $method): array
    {
        // TODO: Implement actual API integration with:
        // - BPJS Kesehatan (Indonesia)
        // - Private insurance providers
        // - Clearinghouse services

        return [
            'status' => 'submitted',
            'method' => $method,
            'submitted_at' => now(),
            'reference_number' => 'REF-' . time(),
        ];
    }

    /**
     * Generate payment schedule
     */
    protected function generatePaymentSchedule($startDate, $count, $frequency, $amount): array
    {
        $schedule = [];
        $date = \Carbon\Carbon::parse($startDate);

        for ($i = 0; $i < $count; $i++) {
            $schedule[] = [
                'installment_number' => $i + 1,
                'date' => $date->format('Y-m-d'),
                'amount' => $amount,
                'status' => 'pending',
            ];

            // Advance date based on frequency
            switch ($frequency) {
                case 'weekly':
                    $date->addWeek();
                    break;
                case 'biweekly':
                    $date->addWeeks(2);
                    break;
                case 'monthly':
                    $date->addMonth();
                    break;
            }
        }

        return $schedule;
    }

    /**
     * Get collection rate
     */
    protected function getCollectionRate(): float
    {
        $totalBilled = MedicalBill::whereMonth('bill_date', now()->month)
            ->whereYear('bill_date', now()->year)
            ->sum('total_amount');

        $totalCollected = MedicalBill::whereMonth('bill_date', now()->month)
            ->whereYear('bill_date', now()->year)
            ->sum('amount_paid');

        if ($totalBilled > 0) {
            return round(($totalCollected / $totalBilled) * 100, 2);
        }

        return 0;
    }
}
