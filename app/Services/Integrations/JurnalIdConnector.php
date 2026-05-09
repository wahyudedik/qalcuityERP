<?php

namespace App\Services\Integrations;

use App\Models\AccountingIntegration;
use App\Models\AccountingSyncLog;
use App\Models\Invoice;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * JurnalIdConnector — Integrasi dengan Jurnal.id
 *
 * Menyediakan sinkronisasi jurnal dan laporan keuangan dengan Jurnal.id.
 *
 * Requirement 16: Integrasi akuntansi (Jurnal.id, Accurate Online) berfungsi:
 * sinkronisasi data jurnal dan laporan keuangan berjalan dengan benar
 */
class JurnalIdConnector extends BaseConnector
{
    protected const API_BASE_URL = 'https://api.jurnal.id/v1';

    protected const TIMEOUT = 30;

    public function __construct(AccountingIntegration $integration)
    {
        parent::__construct($integration);
    }

    /**
     * Test koneksi ke Jurnal.id
     */
    public function testConnection(): array
    {
        try {
            $response = Http::withToken($this->integration->access_token)
                ->timeout(self::TIMEOUT)
                ->get(self::API_BASE_URL.'/companies');

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Koneksi ke Jurnal.id berhasil'];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Koneksi gagal',
            ];
        } catch (\Throwable $e) {
            Log::error('JurnalIdConnector: test connection failed', [
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
     * Sinkronisasi jurnal dari Qalcuity ke Jurnal.id
     */
    public function syncJournals(array $journalIds): AccountingSyncLog
    {
        $syncLog = AccountingSyncLog::create([
            'tenant_id' => $this->integration->tenant_id,
            'integration_id' => $this->integration->id,
            'sync_type' => 'journal',
            'status' => 'pending',
            'started_at' => now(),
        ]);

        try {
            $synced = 0;
            $failed = 0;
            $errors = [];

            foreach ($journalIds as $journalId) {
                try {
                    $journal = JournalEntry::find($journalId);
                    if (! $journal) {
                        $failed++;
                        $errors[] = "Jurnal ID {$journalId} tidak ditemukan";

                        continue;
                    }

                    $payload = $this->formatJournalPayload($journal);
                    $response = Http::withToken($this->integration->access_token)
                        ->timeout(self::TIMEOUT)
                        ->post(self::API_BASE_URL.'/journals', $payload);

                    if ($response->successful()) {
                        $synced++;
                        Log::info('JurnalIdConnector: journal synced', [
                            'journal_id' => $journalId,
                            'jurnal_id' => $response->json('id'),
                        ]);
                    } else {
                        $failed++;
                        $errors[] = "Jurnal {$journal->number}: ".($response->json('message') ?? 'Sync gagal');
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = "Jurnal ID {$journalId}: ".$e->getMessage();
                    Log::error('JurnalIdConnector: sync journal error', [
                        'journal_id' => $journalId,
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
            Log::error('JurnalIdConnector: sync journals failed', [
                'integration_id' => $this->integration->id,
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
     * Sinkronisasi invoice dari Qalcuity ke Jurnal.id
     */
    public function syncInvoices(array $invoiceIds): AccountingSyncLog
    {
        $syncLog = AccountingSyncLog::create([
            'tenant_id' => $this->integration->tenant_id,
            'integration_id' => $this->integration->id,
            'sync_type' => 'invoice',
            'status' => 'pending',
            'started_at' => now(),
        ]);

        try {
            $synced = 0;
            $failed = 0;
            $errors = [];

            foreach ($invoiceIds as $invoiceId) {
                try {
                    $invoice = Invoice::find($invoiceId);
                    if (! $invoice) {
                        $failed++;
                        $errors[] = "Invoice ID {$invoiceId} tidak ditemukan";

                        continue;
                    }

                    $payload = $this->formatInvoicePayload($invoice);
                    $response = Http::withToken($this->integration->access_token)
                        ->timeout(self::TIMEOUT)
                        ->post(self::API_BASE_URL.'/sales-invoices', $payload);

                    if ($response->successful()) {
                        $synced++;
                        Log::info('JurnalIdConnector: invoice synced', [
                            'invoice_id' => $invoiceId,
                            'jurnal_id' => $response->json('id'),
                        ]);
                    } else {
                        $failed++;
                        $errors[] = "Invoice {$invoice->number}: ".($response->json('message') ?? 'Sync gagal');
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = "Invoice ID {$invoiceId}: ".$e->getMessage();
                    Log::error('JurnalIdConnector: sync invoice error', [
                        'invoice_id' => $invoiceId,
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
            Log::error('JurnalIdConnector: sync invoices failed', [
                'integration_id' => $this->integration->id,
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
     * Ambil laporan keuangan dari Jurnal.id
     */
    public function fetchFinancialReports(string $reportType, string $period): array
    {
        try {
            $response = Http::withToken($this->integration->access_token)
                ->timeout(self::TIMEOUT)
                ->get(self::API_BASE_URL.'/reports/'.$reportType, [
                    'period' => $period,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Gagal mengambil laporan',
            ];
        } catch (\Throwable $e) {
            Log::error('JurnalIdConnector: fetch reports failed', [
                'integration_id' => $this->integration->id,
                'report_type' => $reportType,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Gagal mengambil laporan: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Format jurnal untuk Jurnal.id API
     */
    protected function formatJournalPayload(JournalEntry $journal): array
    {
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
     * Format invoice untuk Jurnal.id API
     */
    protected function formatInvoicePayload(Invoice $invoice): array
    {
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
     * Verifikasi signature webhook dari Jurnal.id
     */
    public static function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
