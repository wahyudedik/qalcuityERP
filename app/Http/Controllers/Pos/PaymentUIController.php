<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\TenantPaymentGateway;

class PaymentUIController extends Controller
{
    /**
     * Display payment gateway settings page
     */
    public function gatewaySettings()
    {
        $gateways = TenantPaymentGateway::where('tenant_id', $this->tenantId())
            ->get()
            ->keyBy('provider');

        return view('settings.payment-gateways', compact('gateways'));
    }

    /**
     * Display QRIS payment page
     */
    public function showQrisPayment(string $transactionNumber)
    {
        $transaction = PaymentTransaction::where('tenant_id', $this->tenantId())
            ->where('transaction_number', $transactionNumber)
            ->with('salesOrder')
            ->firstOrFail();

        // Check if transaction is still valid
        if ($transaction->isExpired()) {
            return redirect()->route('pos.orders.index')
                ->with('error', 'QR code has expired. Please generate a new one.');
        }

        return view('pos.payment-qris', compact('transaction'));
    }

    /**
     * Display payment history page
     */
    public function paymentHistory()
    {
        return view('pos.payment-history');
    }
}
