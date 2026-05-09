<?php

namespace App\Services\Integrations;

use App\Models\AccountingIntegration;
use App\Models\AccountingSyncLog;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AccurateOnlineConnector — Integrasi dengan Accurate Online
 *
 * Menyediakan sinkronisasi data akuntansi dengan Accurate Online.
 *
 * Requirement 16: Integrasi akuntansi (Jurnal.id, Accurate Online) berfungsi:
 * sinkronisasi data akuntansi berjalan dengan benar
 */
class AccurateOnlineConnector extends BaseConnector
{
    protected const API_BASE_URL = 'https://api.accurate.id/v1';

    protected const TIMEOUT = 30;

    public function __construct(AccountingIntegration $integration)
    {
        parent::__construct($integration);
    }

    /**
     * Test koneksi ke Accurate Online
     */
    public function testConnection(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->integration->access_token,
            ])
                ->timeout(self::TIMEOUT)
                ->get(self::API_BASE_URL.'/companies');

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Koneksi ke Accurate Online berhasil'];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Koneksi gagal',
            ];
        } catch (\Throwable $e) {
            Log::error('AccurateOnlineConnector: test connection failed', [
                'integration_id' => $this->integration->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Koneksi gagal: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Sinkronisasi data akuntansi dari Qalcuity ke Accurate Online
     */
    public function syncAccountingData(string $dataType, array $ids): AccountingSyncLog
    {
        $syncLog = AccountingSyncLog::create([
            'tenant_id' => $this->integration->tenant_id,
            'integration_id' => $this->integration->id,
            'sync_type' => $dataType,
            'status' => 'pending',
            'started_at' => now(),
        ]);

        try {
            $synced = 0;
            $failed = 0;
            $errors = [];

            foreach ($ids as $id) {
                try {
                    $payload = match ($dataType) {
                        'journal' => $this->getJournalData($id),
                        'invoice' => $this->getInvoiceData($id),
                        'payment' => $this->getPaymentData($id),
                        'expense' => $this->getExpenseData($id),
                        default => null,
                    };

                    if (! $payload) {
                        $failed++;
                        $errors[] = "Tipe data {$dataType} tidak didukung";

                        continue;
                    }

                    $endpoint = match ($dataType) {
                        'journal' => '/journals',
                        'invoice' => '/sales-invoices',
                        'payment' => '/payments',
                        'expense' => '/expenses',
                    };

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer '.$this->integration->access_token,
                    ])
                        ->timeout(self::TIMEOUT)
                        ->post(self::API_BASE_URL.$endpoint, $payload);

                    if ($response->successful()) {
                        $synced++;
                        Log::info('AccurateOnlineConnector: data synced', [
                            'data_type' => $dataType,
                            'id' => $id,
                            'accurate_id' => $response->json('id'),
                        ]);
                    } else {
                        $failed++;
                        $errors[] = "{$dataType} ID {$id}: ".($response->json('message') ?? 'Sync gagal');
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = "{$dataType} ID {$id}: ".$e->getMessage();
                    Log::error('AccurateOnlineConnector: sync data error', [
                        'data_type' => $dataType,
                        'id' => $id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $syncLog->update([
                'status' => $failed === 0 ? 'success' : ($synced > 0 ? 'partial' : 'failed'),
                'records_synced' => $synced,
                'records_failed' => $failed,
                'errors' => $errors,
                'completed_at' => now(),
            ]);

            $this->integration->update(['last_sync_at' => now()]);

            return $syncLog;
        } catch (\Throwable $e) {
            Log::error('AccurateOnlineConnector: sync failed', [
                'integration_id' => $this->integration->id,
                'data_type' => $dataType,
                'error' => $e->getMessage(),
            ]);

            $syncLog->update([
                'status' => 'failed',
                'errors' => [$e->getMessage()],
                'completed_at' => now(),
            ]);

            return $syncLog;
        }
    }

    /**
     * Ambil data jurnal untuk sinkronisasi
     */
    protected function getJournalData(int $journalId): ?array
    {
        $journal = JournalEntry::find($journalId);
        if (! $journal) {
            return null;
        }

        return [
            'date' => $journal->date->format('Y-m-d'),
            'number' => $journal->number,
            'description' => $journal->description,
            'lines' => $journal->lines->map(function ($line) {
                return [
                    'account_code' => $line->account->code,
                    'account_name' => $line->account->name,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'description' => $line->description,
                ];
            })->toArray(),
        ];
    }

    /**
     * Ambil data invoice untuk sinkronisasi
     */
    protected function getInvoiceData(int $invoiceId): ?array
    {
        $invoice = Invoice::find($invoiceId);
        if (! $invoice) {
            return null;
        }

        return [
            'date' => $invoice->invoice_date->format('Y-m-d'),
            'number' => $invoice->number,
            'customer_name' => $invoice->customer->name,
            'customer_email' => $invoice->customer->email,
            'customer_phone' => $invoice->customer->phone,
            'total_amount' => $invoice->total,
            'tax_amount' => $invoice->tax_amount,
            'items' => $invoice->items->map(function ($item) {
                return [
                    'description' => $item->product->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'amount' => $item->amount,
                ];
            })->toArray(),
        ];
    }

    /**
     * Ambil data pembayaran untuk sinkronisasi
     */
    protected function getPaymentData(int $paymentId): ?array
    {
        $payment = Payment::find($paymentId);
        if (! $payment) {
            return null;
        }

        return [
            'date' => $payment->payment_date->format('Y-m-d'),
            'number' => $payment->number,
            'invoice_number' => $payment->invoice->number,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'reference' => $payment->reference,
        ];
    }

    /**
     * Ambil data pengeluaran untuk sinkronisasi
     */
    protected function getExpenseData(int $expenseId): ?array
    {
        $expense = Expense::find($expenseId);
        if (! $expense) {
            return null;
        }

        return [
            'date' => $expense->expense_date->format('Y-m-d'),
            'number' => $expense->number,
            'description' => $expense->description,
            'amount' => $expense->amount,
            'category' => $expense->category,
            'reference' => $expense->reference,
        ];
    }

    /**
     * Verifikasi signature webhook dari Accurate Online
     */
    public static function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
