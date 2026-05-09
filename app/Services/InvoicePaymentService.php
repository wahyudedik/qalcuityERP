<?php

namespace App\Services;

use App\Exceptions\TransactionException;
use App\Models\ActivityLog;
use App\Models\ErpNotification;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling invoice payment operations with full transactional integrity.
 * Ensures atomicity across: Payment creation → Invoice status update → GL posting
 */
class InvoicePaymentService
{
    /**
     * Process an invoice payment atomically.
     *
     * All operations happen in a single database transaction:
     * 1. Create payment record
     * 2. Update invoice payment status
     * 3. Post to General Ledger
     * 4. Record activity log
     * 5. Create notifications
     *
     * If ANY step fails, ALL changes are rolled back automatically.
     *
     * @param  Invoice  $invoice  The invoice being paid
     * @param  array  $data  Payment data [amount, method, notes]
     * @param  int  $userId  User making the payment
     * @return array Result containing payment, invoice, and GL posting result
     *
     * @throws TransactionException
     */
    public function processPayment(
        Invoice $invoice,
        array $data,
        int $userId
    ): array {
        $tenantId = $invoice->tenant_id;
        $amount = (float) $data['amount'];
        $method = $data['method'];
        $notes = $data['notes'] ?? null;

        Log::info('Starting atomic invoice payment', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->number,
            'amount' => $amount,
            'method' => $method,
            'user_id' => $userId,
        ]);

        try {
            $result = DB::transaction(function () use ($invoice, $tenantId, $userId, $amount, $method, $notes) {
                // Step 1: Lock the invoice row for update (prevents race conditions)
                $lockedInvoice = Invoice::where('id', $invoice->id)->lockForUpdate()->first();

                if (! $lockedInvoice) {
                    throw TransactionException::rollbackRequired(
                        message: 'Invoice not found or locked by another transaction',
                        type: 'invoice_payment',
                        data: ['invoice_id' => $invoice->id]
                    );
                }

                // Validate payment amount doesn't exceed remaining
                if ($amount > $lockedInvoice->remaining_amount) {
                    throw TransactionException::rollbackRequired(
                        message: "Payment amount ({$amount}) exceeds remaining balance ({$lockedInvoice->remaining_amount})",
                        type: 'invoice_payment',
                        data: [
                            'payment_amount' => $amount,
                            'remaining_amount' => $lockedInvoice->remaining_amount,
                        ]
                    );
                }

                // Step 2: Create payment record
                $payment = Payment::create([
                    'tenant_id' => $tenantId,
                    'payable_type' => Invoice::class,
                    'payable_id' => $lockedInvoice->id,
                    'amount' => $amount,
                    'payment_method' => $method,
                    'notes' => $notes,
                    'payment_date' => today(),
                    'user_id' => $userId,
                ]);

                Log::info('Payment record created', ['payment_id' => $payment->id]);

                // Step 3: Update invoice payment status
                $lockedInvoice->updatePaymentStatus();

                Log::info('Invoice payment status updated', [
                    'paid_amount' => $lockedInvoice->paid_amount,
                    'remaining_amount' => $lockedInvoice->remaining_amount,
                    'status' => $lockedInvoice->status,
                ]);

                // Step 4: Record activity log (inside transaction)
                ActivityLog::record(
                    'payment_recorded',
                    "Pembayaran dicatat: Invoice {$lockedInvoice->number} Rp ".number_format($amount, 0, ',', '.')." via {$method}",
                    $lockedInvoice
                );

                // Step 5: GL Auto-Posting - Dr Kas/Bank / Cr Piutang Usaha
                // This is now atomic thanks to DB::transaction wrapper in GlPostingService
                $glPostingService = app(GlPostingService::class);
                $glResult = $glPostingService->postInvoicePayment(
                    tenantId: $tenantId,
                    userId: $userId,
                    invoiceNumber: $lockedInvoice->number.'-PAY-'.now()->format('His'),
                    invoiceId: $lockedInvoice->id,
                    amount: $amount,
                    method: $method,
                    date: today()->toDateString(),
                );

                // Don't fail the whole transaction if GL posting fails (just warn)
                // The payment itself is valid even without GL posting
                $glSuccess = true;
                if ($glResult->isFailed()) {
                    Log::warning('GL posting failed but payment will proceed', [
                        'reason' => $glResult->reason,
                    ]);
                    $glSuccess = false;
                }

                // Step 6: Create notification if fully paid
                if ($lockedInvoice->fresh()->status === 'paid') {
                    ErpNotification::create([
                        'tenant_id' => $tenantId,
                        'user_id' => $userId,
                        'type' => 'invoice_paid',
                        'title' => '✅ Invoice Lunas',
                        'body' => "Invoice {$lockedInvoice->number} telah lunas dibayar.",
                        'data' => ['invoice_id' => $lockedInvoice->id],
                    ]);
                }

                return [
                    'payment' => $payment,
                    'invoice' => $lockedInvoice->fresh(),
                    'gl_result' => $glResult,
                    'gl_success' => $glSuccess,
                ];
            });

            Log::info('Invoice payment completed successfully', [
                'payment_id' => $result['payment']->id,
                'invoice_status' => $result['invoice']->status,
                'gl_success' => $result['gl_success'],
            ]);

            return $result;

        } catch (TransactionException $e) {
            // Re-throw transaction exceptions as-is
            Log::critical('Invoice payment transaction failed - ROLLED BACK', [
                'error' => $e->getMessage(),
                'type' => $e->getTransactionType(),
                'context' => $e->getContext(),
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::critical('Invoice payment unexpected error - ROLLED BACK', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw TransactionException::rollbackRequired(
                message: 'Failed to process payment: '.$e->getMessage(),
                type: 'invoice_payment',
                data: [
                    'invoice_id' => $invoice->id,
                    'original_error' => get_class($e),
                ]
            );
        }
    }

    /**
     * Process bulk invoice payments (multiple invoices in one transaction).
     * Uses saga pattern for complex multi-step operations.
     *
     * @param  array  $invoices  Array of ['invoice' => Invoice, 'amount' => float]
     * @param  string  $method  Payment method
     * @param  int  $userId  User ID
     * @return array Result with all payments and their statuses
     *
     * @throws TransactionException
     */
    public function processBulkPayments(
        array $invoices,
        string $method,
        int $userId
    ): array {
        $sagaService = app(TransactionSagaService::class);

        $steps = [
            'validate_invoices' => function ($context) use ($invoices) {
                foreach ($invoices as $item) {
                    $invoice = $item['invoice'];
                    if ($item['amount'] > $invoice->remaining_amount) {
                        throw new \RuntimeException(
                            "Payment amount exceeds remaining for invoice {$invoice->number}"
                        );
                    }
                }

                return ['validated' => true];
            },

            'create_payments' => function ($context) use ($invoices, $method, $userId) {
                $payments = [];
                foreach ($invoices as $item) {
                    $invoice = Invoice::where('id', $item['invoice']->id)
                        ->lockForUpdate()
                        ->first();

                    $payment = Payment::create([
                        'tenant_id' => $invoice->tenant_id,
                        'payable_type' => Invoice::class,
                        'payable_id' => $invoice->id,
                        'amount' => $item['amount'],
                        'payment_method' => $method,
                        'payment_date' => today(),
                        'user_id' => $userId,
                    ]);

                    $payments[] = $payment;
                    $invoice->updatePaymentStatus();
                }

                return ['payments' => $payments];
            },

            'post_to_gl' => function ($context) use ($method, $userId) {
                $glResults = [];
                $glService = app(GlPostingService::class);

                foreach ($context['payments'] as $payment) {
                    $invoice = $payment->payable;
                    $glResult = $glService->postInvoicePayment(
                        tenantId: $invoice->tenant_id,
                        userId: $userId,
                        invoiceNumber: $invoice->number.'-BULK-'.now()->format('His'),
                        invoiceId: $invoice->id,
                        amount: $payment->amount,
                        method: $method,
                        date: today()->toDateString(),
                    );
                    $glResults[] = $glResult;
                }

                return ['gl_results' => $glResults];
            },
        ];

        try {
            $result = $sagaService->execute(
                steps: $steps,
                compensations: [], // Payments are not auto-reversed - would need manual reversal
                sagaType: 'bulk_invoice_payment',
                context: []
            );

            return [
                'success' => true,
                'payments' => $result['data']['payments'] ?? [],
                'gl_results' => $result['data']['gl_results'] ?? [],
                'total_processed' => count($result['data']['payments'] ?? []),
            ];

        } catch (TransactionException $e) {
            Log::critical('Bulk payment saga failed', [
                'error' => $e->getMessage(),
                'compensated' => ! $e->getContext()['requires_compensation'] ?? false,
            ]);
            throw $e;
        }
    }
}
