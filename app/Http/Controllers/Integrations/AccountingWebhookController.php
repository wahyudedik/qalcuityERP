<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\AccountingIntegration;
use App\Services\AccountingIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * AccountingWebhookController — Handle webhook dari layanan akuntansi eksternal
 *
 * Requirement 16: THE System SHALL memastikan webhook dari layanan eksternal
 * diverifikasi signature-nya sebelum diproses untuk mencegah request palsu.
 */
class AccountingWebhookController extends Controller
{
    protected AccountingIntegrationService $integrationService;

    public function __construct(AccountingIntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * Handle webhook dari Jurnal.id
     */
    public function handleJurnalIdWebhook(Request $request)
    {
        try {
            $signature = $request->header('X-Jurnal-Signature');
            $payload = $request->getContent();

            if (! $signature || ! $payload) {
                Log::warning('AccountingWebhookController: missing signature or payload', [
                    'provider' => 'jurnal_id',
                ]);

                return response()->json(['error' => 'Invalid request'], 400);
            }

            // Cari integrasi berdasarkan webhook secret
            $integration = AccountingIntegration::where('provider', 'jurnal_id')
                ->where('is_active', true)
                ->first();

            if (! $integration) {
                Log::warning('AccountingWebhookController: integration not found', [
                    'provider' => 'jurnal_id',
                ]);

                return response()->json(['error' => 'Integration not found'], 404);
            }

            // Verifikasi signature
            if (! $this->integrationService->verifyWebhookSignature($integration, $payload, $signature)) {
                Log::warning('AccountingWebhookController: invalid signature', [
                    'provider' => 'jurnal_id',
                    'integration_id' => $integration->id,
                ]);

                return response()->json(['error' => 'Invalid signature'], 403);
            }

            $data = $request->json()->all();
            $eventType = $data['event_type'] ?? null;

            Log::info('AccountingWebhookController: webhook received', [
                'provider' => 'jurnal_id',
                'integration_id' => $integration->id,
                'event_type' => $eventType,
            ]);

            // Handle berbagai event type
            match ($eventType) {
                'journal.created' => $this->handleJournalCreated($integration, $data),
                'journal.updated' => $this->handleJournalUpdated($integration, $data),
                'invoice.created' => $this->handleInvoiceCreated($integration, $data),
                'invoice.updated' => $this->handleInvoiceUpdated($integration, $data),
                'payment.received' => $this->handlePaymentReceived($integration, $data),
                default => Log::info('AccountingWebhookController: unknown event type', [
                    'event_type' => $eventType,
                ]),
            };

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('AccountingWebhookController: webhook processing failed', [
                'provider' => 'jurnal_id',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle webhook dari Accurate Online
     */
    public function handleAccurateOnlineWebhook(Request $request)
    {
        try {
            $signature = $request->header('X-Accurate-Signature');
            $payload = $request->getContent();

            if (! $signature || ! $payload) {
                Log::warning('AccountingWebhookController: missing signature or payload', [
                    'provider' => 'accurate_online',
                ]);

                return response()->json(['error' => 'Invalid request'], 400);
            }

            // Cari integrasi berdasarkan webhook secret
            $integration = AccountingIntegration::where('provider', 'accurate_online')
                ->where('is_active', true)
                ->first();

            if (! $integration) {
                Log::warning('AccountingWebhookController: integration not found', [
                    'provider' => 'accurate_online',
                ]);

                return response()->json(['error' => 'Integration not found'], 404);
            }

            // Verifikasi signature
            if (! $this->integrationService->verifyWebhookSignature($integration, $payload, $signature)) {
                Log::warning('AccountingWebhookController: invalid signature', [
                    'provider' => 'accurate_online',
                    'integration_id' => $integration->id,
                ]);

                return response()->json(['error' => 'Invalid signature'], 403);
            }

            $data = $request->json()->all();
            $eventType = $data['event_type'] ?? null;

            Log::info('AccountingWebhookController: webhook received', [
                'provider' => 'accurate_online',
                'integration_id' => $integration->id,
                'event_type' => $eventType,
            ]);

            // Handle berbagai event type
            match ($eventType) {
                'transaction.created' => $this->handleTransactionCreated($integration, $data),
                'transaction.updated' => $this->handleTransactionUpdated($integration, $data),
                'report.generated' => $this->handleReportGenerated($integration, $data),
                default => Log::info('AccountingWebhookController: unknown event type', [
                    'event_type' => $eventType,
                ]),
            };

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('AccountingWebhookController: webhook processing failed', [
                'provider' => 'accurate_online',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle event: journal created di Jurnal.id
     */
    protected function handleJournalCreated(AccountingIntegration $integration, array $data): void
    {
        Log::info('AccountingWebhookController: journal created event', [
            'integration_id' => $integration->id,
            'journal_id' => $data['journal_id'] ?? null,
        ]);

        // Implementasi logika untuk handle journal created
        // Contoh: update sync status, trigger notifikasi, dll.
    }

    /**
     * Handle event: journal updated di Jurnal.id
     */
    protected function handleJournalUpdated(AccountingIntegration $integration, array $data): void
    {
        Log::info('AccountingWebhookController: journal updated event', [
            'integration_id' => $integration->id,
            'journal_id' => $data['journal_id'] ?? null,
        ]);
    }

    /**
     * Handle event: invoice created di Jurnal.id
     */
    protected function handleInvoiceCreated(AccountingIntegration $integration, array $data): void
    {
        Log::info('AccountingWebhookController: invoice created event', [
            'integration_id' => $integration->id,
            'invoice_id' => $data['invoice_id'] ?? null,
        ]);
    }

    /**
     * Handle event: invoice updated di Jurnal.id
     */
    protected function handleInvoiceUpdated(AccountingIntegration $integration, array $data): void
    {
        Log::info('AccountingWebhookController: invoice updated event', [
            'integration_id' => $integration->id,
            'invoice_id' => $data['invoice_id'] ?? null,
        ]);
    }

    /**
     * Handle event: payment received di Jurnal.id
     */
    protected function handlePaymentReceived(AccountingIntegration $integration, array $data): void
    {
        Log::info('AccountingWebhookController: payment received event', [
            'integration_id' => $integration->id,
            'payment_id' => $data['payment_id'] ?? null,
        ]);
    }

    /**
     * Handle event: transaction created di Accurate Online
     */
    protected function handleTransactionCreated(AccountingIntegration $integration, array $data): void
    {
        Log::info('AccountingWebhookController: transaction created event', [
            'integration_id' => $integration->id,
            'transaction_id' => $data['transaction_id'] ?? null,
        ]);
    }

    /**
     * Handle event: transaction updated di Accurate Online
     */
    protected function handleTransactionUpdated(AccountingIntegration $integration, array $data): void
    {
        Log::info('AccountingWebhookController: transaction updated event', [
            'integration_id' => $integration->id,
            'transaction_id' => $data['transaction_id'] ?? null,
        ]);
    }

    /**
     * Handle event: report generated di Accurate Online
     */
    protected function handleReportGenerated(AccountingIntegration $integration, array $data): void
    {
        Log::info('AccountingWebhookController: report generated event', [
            'integration_id' => $integration->id,
            'report_type' => $data['report_type'] ?? null,
        ]);
    }
}
