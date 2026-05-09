<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifikasi signature webhook dari payment gateway.
 * Digunakan di route webhook Midtrans dan Xendit.
 */
class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next, string $gateway): Response
    {
        $verified = match ($gateway) {
            'midtrans' => $this->verifyMidtrans($request),
            'xendit' => $this->verifyXendit($request),
            default => false,
        };

        if (! $verified) {
            Log::warning("VerifyWebhookSignature: invalid signature for [{$gateway}]", [
                'ip' => $request->ip(),
                'headers' => $request->headers->all(),
            ]);

            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }

    private function verifyMidtrans(Request $request): bool
    {
        $serverKey = config('services.midtrans.server_key');
        $orderId = $request->input('order_id', '');
        $statusCode = $request->input('status_code', '');
        $grossAmount = $request->input('gross_amount', '');
        $incoming = $request->input('signature_key', '');

        if (empty($serverKey) || empty($incoming)) {
            return false;
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expected, $incoming);
    }

    private function verifyXendit(Request $request): bool
    {
        $callbackToken = config('services.xendit.webhook_token');
        $incoming = $request->header('x-callback-token', '');

        if (empty($callbackToken) || empty($incoming)) {
            return false;
        }

        return hash_equals($callbackToken, $incoming);
    }
}
