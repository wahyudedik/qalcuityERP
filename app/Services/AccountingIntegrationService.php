<?php

namespace App\Services;

use App\Models\AccountingIntegration;
use App\Models\AccountingSyncLog;
use App\Services\Integrations\AccurateOnlineConnector;
use App\Services\Integrations\JurnalIdConnector;
use Illuminate\Support\Facades\Log;

/**
 * AccountingIntegrationService — Manajemen integrasi akuntansi eksternal
 *
 * Requirement 16: Integrasi akuntansi (Jurnal.id, Accurate Online) berfungsi:
 * sinkronisasi data jurnal dan laporan keuangan berjalan dengan benar
 *
 * IF sebuah layanan eksternal tidak tersedia atau mengembalikan error,
 * THEN THE System SHALL mencatat error ke log, menampilkan pesan yang informatif
 * kepada pengguna, dan tidak mengakibatkan crash aplikasi
 */
class AccountingIntegrationService
{
    /**
     * Dapatkan connector untuk provider tertentu
     */
    public function getConnector(AccountingIntegration $integration)
    {
        return match ($integration->provider) {
            'jurnal_id' => new JurnalIdConnector($integration),
            'accurate_online' => new AccurateOnlineConnector($integration),
            default => throw new \InvalidArgumentException("Provider {$integration->provider} tidak didukung"),
        };
    }

    /**
     * Test koneksi ke layanan eksternal
     */
    public function testConnection(AccountingIntegration $integration): array
    {
        try {
            $connector = $this->getConnector($integration);

            return $connector->testConnection();
        } catch (\Throwable $e) {
            Log::error('AccountingIntegrationService: test connection failed', [
                'integration_id' => $integration->id,
                'provider' => $integration->provider,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Gagal menguji koneksi: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Sinkronisasi jurnal ke layanan eksternal
     */
    public function syncJournals(AccountingIntegration $integration, array $journalIds): AccountingSyncLog
    {
        try {
            if (! $integration->is_active) {
                throw new \RuntimeException('Integrasi tidak aktif');
            }

            $connector = $this->getConnector($integration);

            return $connector->syncJournals($journalIds);
        } catch (\Throwable $e) {
            Log::error('AccountingIntegrationService: sync journals failed', [
                'integration_id' => $integration->id,
                'provider' => $integration->provider,
                'error' => $e->getMessage(),
            ]);

            // Buat sync log untuk mencatat error
            return AccountingSyncLog::create([
                'tenant_id' => $integration->tenant_id,
                'integration_id' => $integration->id,
                'sync_type' => 'journal',
                'status' => 'failed',
                'records_synced' => 0,
                'records_failed' => count($journalIds),
                'errors' => [$e->getMessage()],
                'started_at' => now(),
                'completed_at' => now(),
            ]);
        }
    }

    /**
     * Sinkronisasi invoice ke layanan eksternal
     */
    public function syncInvoices(AccountingIntegration $integration, array $invoiceIds): AccountingSyncLog
    {
        try {
            if (! $integration->is_active) {
                throw new \RuntimeException('Integrasi tidak aktif');
            }

            $connector = $this->getConnector($integration);

            if ($integration->provider === 'jurnal_id') {
                return $connector->syncInvoices($invoiceIds);
            } else {
                return $connector->syncAccountingData('invoice', $invoiceIds);
            }
        } catch (\Throwable $e) {
            Log::error('AccountingIntegrationService: sync invoices failed', [
                'integration_id' => $integration->id,
                'provider' => $integration->provider,
                'error' => $e->getMessage(),
            ]);

            return AccountingSyncLog::create([
                'tenant_id' => $integration->tenant_id,
                'integration_id' => $integration->id,
                'sync_type' => 'invoice',
                'status' => 'failed',
                'records_synced' => 0,
                'records_failed' => count($invoiceIds),
                'errors' => [$e->getMessage()],
                'started_at' => now(),
                'completed_at' => now(),
            ]);
        }
    }

    /**
     * Sinkronisasi pembayaran ke layanan eksternal
     */
    public function syncPayments(AccountingIntegration $integration, array $paymentIds): AccountingSyncLog
    {
        try {
            if (! $integration->is_active) {
                throw new \RuntimeException('Integrasi tidak aktif');
            }

            $connector = $this->getConnector($integration);

            return $connector->syncAccountingData('payment', $paymentIds);
        } catch (\Throwable $e) {
            Log::error('AccountingIntegrationService: sync payments failed', [
                'integration_id' => $integration->id,
                'provider' => $integration->provider,
                'error' => $e->getMessage(),
            ]);

            return AccountingSyncLog::create([
                'tenant_id' => $integration->tenant_id,
                'integration_id' => $integration->id,
                'sync_type' => 'payment',
                'status' => 'failed',
                'records_synced' => 0,
                'records_failed' => count($paymentIds),
                'errors' => [$e->getMessage()],
                'started_at' => now(),
                'completed_at' => now(),
            ]);
        }
    }

    /**
     * Sinkronisasi pengeluaran ke layanan eksternal
     */
    public function syncExpenses(AccountingIntegration $integration, array $expenseIds): AccountingSyncLog
    {
        try {
            if (! $integration->is_active) {
                throw new \RuntimeException('Integrasi tidak aktif');
            }

            $connector = $this->getConnector($integration);

            return $connector->syncAccountingData('expense', $expenseIds);
        } catch (\Throwable $e) {
            Log::error('AccountingIntegrationService: sync expenses failed', [
                'integration_id' => $integration->id,
                'provider' => $integration->provider,
                'error' => $e->getMessage(),
            ]);

            return AccountingSyncLog::create([
                'tenant_id' => $integration->tenant_id,
                'integration_id' => $integration->id,
                'sync_type' => 'expense',
                'status' => 'failed',
                'records_synced' => 0,
                'records_failed' => count($expenseIds),
                'errors' => [$e->getMessage()],
                'started_at' => now(),
                'completed_at' => now(),
            ]);
        }
    }

    /**
     * Ambil laporan keuangan dari layanan eksternal
     */
    public function fetchFinancialReports(AccountingIntegration $integration, string $reportType, string $period): array
    {
        try {
            if (! $integration->is_active) {
                throw new \RuntimeException('Integrasi tidak aktif');
            }

            $connector = $this->getConnector($integration);

            if ($integration->provider === 'jurnal_id') {
                return $connector->fetchFinancialReports($reportType, $period);
            }

            return [
                'success' => false,
                'error' => 'Provider '.$integration->provider.' tidak mendukung fetch laporan',
            ];
        } catch (\Throwable $e) {
            Log::error('AccountingIntegrationService: fetch reports failed', [
                'integration_id' => $integration->id,
                'provider' => $integration->provider,
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
     * Verifikasi webhook signature dari layanan eksternal
     */
    public function verifyWebhookSignature(AccountingIntegration $integration, string $payload, string $signature): bool
    {
        try {
            $secret = $integration->api_secret;

            return match ($integration->provider) {
                'jurnal_id' => JurnalIdConnector::verifyWebhookSignature($payload, $signature, $secret),
                'accurate_online' => AccurateOnlineConnector::verifyWebhookSignature($payload, $signature, $secret),
                default => false,
            };
        } catch (\Throwable $e) {
            Log::error('AccountingIntegrationService: webhook verification failed', [
                'integration_id' => $integration->id,
                'provider' => $integration->provider,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Aktifkan integrasi
     */
    public function activate(AccountingIntegration $integration): bool
    {
        try {
            $result = $this->testConnection($integration);

            if ($result['success']) {
                $integration->update(['is_active' => true]);
                Log::info('AccountingIntegrationService: integration activated', [
                    'integration_id' => $integration->id,
                    'provider' => $integration->provider,
                ]);

                return true;
            }

            Log::warning('AccountingIntegrationService: activation failed', [
                'integration_id' => $integration->id,
                'provider' => $integration->provider,
                'error' => $result['error'] ?? 'Unknown error',
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('AccountingIntegrationService: activation error', [
                'integration_id' => $integration->id,
                'provider' => $integration->provider,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Nonaktifkan integrasi
     */
    public function deactivate(AccountingIntegration $integration): bool
    {
        try {
            $integration->update(['is_active' => false]);
            Log::info('AccountingIntegrationService: integration deactivated', [
                'integration_id' => $integration->id,
                'provider' => $integration->provider,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('AccountingIntegrationService: deactivation error', [
                'integration_id' => $integration->id,
                'provider' => $integration->provider,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
