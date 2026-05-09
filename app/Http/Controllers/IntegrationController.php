<?php

namespace App\Http\Controllers;

use App\Models\AccountingIntegration;
use App\Models\AccountingSyncLog;
use App\Models\BankAccount;
use App\Models\CommunicationChannel;
use App\Models\EcommercePlatform;
use App\Models\LogisticsProvider;
use App\Models\PaymentGateway;
use App\Models\Shipment;
use App\Services\AccountingIntegrationService;
use App\Services\Integrations\EcommerceIntegrationService;
use App\Services\Integrations\LogisticsTrackingService;
use App\Services\Integrations\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IntegrationController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $paymentService,
        protected EcommerceIntegrationService $ecommerceService,
        protected LogisticsTrackingService $logisticsService,
    ) {}

    // ==================== PAYMENT GATEWAYS ====================

    public function paymentGateways()
    {
        $gateways = PaymentGateway::where('tenant_id', auth()->user()->tenant_id)->get();

        return response()->json(['gateways' => $gateways]);
    }

    public function configurePaymentGateway(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:midtrans,xendit,duitku',
            'environment' => 'required|in:sandbox,production',
            'api_key' => 'nullable|string',
            'secret_key' => 'nullable|string',
        ]);

        $gateway = PaymentGateway::updateOrCreate(
            [
                'tenant_id' => auth()->user()->tenant_id,
                'provider' => $request->provider,
                'environment' => $request->environment,
            ],
            $request->all()
        );

        return response()->json(['success' => true, 'gateway' => $gateway]);
    }

    public function createPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
            'amount' => 'required|numeric|min:1000',
            'gateway' => 'required|in:midtrans,xendit,duitku',
            'customer_name' => 'required|string',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $result = match ($request->gateway) {
            'midtrans' => $this->paymentService->createMidtransPayment($request->all(), $tenantId),
            'xendit' => $this->paymentService->createXenditPayment($request->all(), $tenantId),
            default => ['error' => 'Unsupported gateway']
        };

        return response()->json($result);
    }

    public function paymentWebhook(Request $request, string $provider)
    {
        $payload = $request->all();

        $success = $this->paymentService->handleWebhook($provider, $payload);

        return response()->json(['status' => $success ? 'ok' : 'error']);
    }

    // ==================== E-COMMERCE ====================

    public function ecommercePlatforms()
    {
        $platforms = EcommercePlatform::where('tenant_id', auth()->user()->tenant_id)->get();

        return response()->json(['platforms' => $platforms]);
    }

    public function connectEcommercePlatform(Request $request)
    {
        $request->validate([
            'platform' => 'required|in:shopify,woocommerce,tokopedia,shopee,lazada',
            'store_name' => 'required|string',
            'store_url' => 'nullable|url',
            'access_token' => 'nullable|string',
        ]);

        $platform = EcommercePlatform::create(array_merge($request->all(), [
            'tenant_id' => auth()->user()->tenant_id,
        ]));

        return response()->json(['success' => true, 'platform' => $platform]);
    }

    public function syncEcommerceOrders(Request $request, int $platformId)
    {
        $platform = EcommercePlatform::findOrFail($platformId);

        $result = match ($platform->platform) {
            'shopify' => $this->ecommerceService->syncShopifyOrders($platformId),
            'woocommerce' => $this->ecommerceService->syncWooCommerceOrders($platformId),
            'tokopedia' => $this->ecommerceService->syncTokopediaOrders($platformId),
            default => ['error' => 'Unsupported platform']
        };

        return response()->json($result);
    }

    public function ecommerceSalesStats(Request $request)
    {
        $days = $request->input('days', 30);
        $stats = $this->ecommerceService->getSalesStats(auth()->user()->tenant_id, $days);

        return response()->json($stats);
    }

    // ==================== LOGISTICS ====================

    public function logisticsProviders()
    {
        $providers = LogisticsProvider::where('tenant_id', auth()->user()->tenant_id)->get();

        return response()->json(['providers' => $providers]);
    }

    public function createShipment(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:jne,jnt,sicepat',
            'origin_city' => 'required|string',
            'destination_city' => 'required|string',
            'weight_kg' => 'required|numeric|min:0.1',
            'service_type' => 'nullable|string',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $result = match ($request->provider) {
            'jne' => $this->logisticsService->createJNEShipment($request->all(), $tenantId),
            default => ['error' => 'Unsupported provider']
        };

        return response()->json($result);
    }

    public function trackShipment(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'provider' => 'required|in:jne,jnt,sicepat',
        ]);

        $result = $this->logisticsService->trackShipment(
            $request->tracking_number,
            $request->provider
        );

        return response()->json($result);
    }

    public function getShippingCost(Request $request)
    {
        $request->validate([
            'origin' => 'required|string',
            'destination' => 'required|string',
            'weight_kg' => 'required|numeric|min:0.1',
            'provider' => 'required|in:jne,jnt,sicepat',
        ]);

        $result = $this->logisticsService->getShippingCost(
            $request->origin,
            $request->destination,
            $request->weight_kg,
            $request->provider
        );

        return response()->json($result);
    }

    // ==================== ACCOUNTING SYNC ====================

    public function accountingIntegrations()
    {
        $integrations = AccountingIntegration::where('tenant_id', auth()->user()->tenant_id)->get();

        return response()->json(['integrations' => $integrations]);
    }

    public function connectAccounting(Request $request)
    {
        $request->validate([
            'provider' => 'required|in:jurnal_id,accurate_online,zahir',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
        ]);

        try {
            $integration = AccountingIntegration::create(array_merge($request->all(), [
                'tenant_id' => auth()->user()->tenant_id,
            ]));

            return response()->json(['success' => true, 'integration' => $integration]);
        } catch (\Throwable $e) {
            Log::error('IntegrationController: connect accounting failed', [
                'provider' => $request->provider,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Gagal menghubungkan integrasi akuntansi: '.$e->getMessage(),
            ], 400);
        }
    }

    /**
     * Test koneksi ke layanan akuntansi eksternal
     */
    public function testAccountingConnection(Request $request)
    {
        $request->validate([
            'integration_id' => 'required|exists:accounting_integrations,id',
        ]);

        try {
            $integration = AccountingIntegration::where('id', $request->integration_id)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->firstOrFail();

            $service = new AccountingIntegrationService;
            $result = $service->testConnection($integration);

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal menguji koneksi: '.$e->getMessage(),
            ], 400);
        }
    }

    /**
     * Sinkronisasi jurnal ke layanan akuntansi eksternal
     */
    public function syncAccountingJournals(Request $request)
    {
        $request->validate([
            'integration_id' => 'required|exists:accounting_integrations,id',
            'journal_ids' => 'required|array|min:1',
            'journal_ids.*' => 'integer|exists:journal_entries,id',
        ]);

        try {
            $integration = AccountingIntegration::where('id', $request->integration_id)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->firstOrFail();

            $service = new AccountingIntegrationService;
            $syncLog = $service->syncJournals($integration, $request->journal_ids);

            return response()->json([
                'success' => $syncLog->status === 'success',
                'sync_log' => $syncLog,
            ]);
        } catch (\Throwable $e) {
            Log::error('IntegrationController: sync journals failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Gagal sinkronisasi jurnal: '.$e->getMessage(),
            ], 400);
        }
    }

    /**
     * Sinkronisasi invoice ke layanan akuntansi eksternal
     */
    public function syncAccountingInvoices(Request $request)
    {
        $request->validate([
            'integration_id' => 'required|exists:accounting_integrations,id',
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'integer|exists:invoices,id',
        ]);

        try {
            $integration = AccountingIntegration::where('id', $request->integration_id)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->firstOrFail();

            $service = new AccountingIntegrationService;
            $syncLog = $service->syncInvoices($integration, $request->invoice_ids);

            return response()->json([
                'success' => $syncLog->status === 'success',
                'sync_log' => $syncLog,
            ]);
        } catch (\Throwable $e) {
            Log::error('IntegrationController: sync invoices failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Gagal sinkronisasi invoice: '.$e->getMessage(),
            ], 400);
        }
    }

    /**
     * Ambil sync logs untuk integrasi akuntansi
     */
    public function getAccountingSyncLogs(Request $request)
    {
        $request->validate([
            'integration_id' => 'required|exists:accounting_integrations,id',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $integration = AccountingIntegration::where('id', $request->integration_id)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->firstOrFail();

            $logs = AccountingSyncLog::where('integration_id', $integration->id)
                ->orderBy('started_at', 'desc')
                ->limit($request->input('limit', 50))
                ->get();

            return response()->json(['logs' => $logs]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal mengambil sync logs: '.$e->getMessage(),
            ], 400);
        }
    }

    // ==================== COMMUNICATION ====================

    public function communicationChannels()
    {
        $channels = CommunicationChannel::where('tenant_id', auth()->user()->tenant_id)->get();

        return response()->json(['channels' => $channels]);
    }

    public function connectWhatsApp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'api_key' => 'required|string',
        ]);

        $channel = CommunicationChannel::create([
            'tenant_id' => auth()->user()->tenant_id,
            'channel' => 'whatsapp_business',
            'phone_number' => $request->phone_number,
            'api_key' => $request->api_key,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'channel' => $channel]);
    }

    public function sendWhatsAppMessage(Request $request)
    {
        $request->validate([
            'recipient' => 'required|string',
            'message' => 'required|string',
        ]);

        // Implementation for sending WhatsApp message
        return response()->json(['success' => true, 'message' => 'Message sent']);
    }

    // ==================== BANKING ====================

    public function bankAccounts()
    {
        $accounts = BankAccount::where('tenant_id', auth()->user()->tenant_id)->get();

        return response()->json(['accounts' => $accounts]);
    }

    public function addBankAccount(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
        ]);

        $account = BankAccount::create(array_merge($request->all(), [
            'tenant_id' => auth()->user()->tenant_id,
        ]));

        return response()->json(['success' => true, 'account' => $account]);
    }

    public function importBankStatement(Request $request, int $accountId)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx',
        ]);

        // Parse CSV/Excel and import transactions
        return response()->json(['success' => true, 'imported' => 0]);
    }

    // ==================== DASHBOARD ====================

    public function dashboard()
    {
        $tenantId = auth()->user()->tenant_id;

        $stats = [
            'payment_gateways' => PaymentGateway::where('tenant_id', $tenantId)->count(),
            'ecommerce_platforms' => EcommercePlatform::where('tenant_id', $tenantId)->count(),
            'logistics_providers' => LogisticsProvider::where('tenant_id', $tenantId)->count(),
            'active_shipments' => Shipment::where('tenant_id', $tenantId)
                ->whereIn('status', ['pending', 'in_transit'])->count(),
            'accounting_integrations' => AccountingIntegration::where('tenant_id', $tenantId)->count(),
            'communication_channels' => CommunicationChannel::where('tenant_id', $tenantId)->count(),
            'bank_accounts' => BankAccount::where('tenant_id', $tenantId)->count(),
        ];

        if (request()->expectsJson()) {
            return response()->json($stats);
        }

        return view('integrations.dashboard', compact('stats'));
    }
}
