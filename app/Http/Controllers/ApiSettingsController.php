<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use App\Models\WebhookSubscription;
use App\Models\WebhookDelivery;
use App\Services\WebhookService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiSettingsController extends Controller
{
    // ─── API Tokens ───────────────────────────────────────────────

    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(!$tenantId, 403);

        $tokens   = ApiToken::where('tenant_id', $tenantId)->latest()->get();
        $webhooks = WebhookSubscription::where('tenant_id', $tenantId)->latest()->get();

        $availableEvents = \App\Services\WebhookService::EVENTS;

        return view('settings.api', compact('tokens', 'webhooks', 'availableEvents'));
    }

    public function storeToken(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(!$tenantId, 403);

        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'abilities'  => 'required|array',
            'abilities.*'=> 'in:read,write,delete,*',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $token = ApiToken::generate(
            $tenantId,
            $validated['name'],
            $validated['abilities'],
            isset($validated['expires_at']) ? Carbon::parse($validated['expires_at']) : null
        );

        return back()->with('new_token', $token->token)->with('success', 'Token API dibuat. Salin sekarang — tidak akan ditampilkan lagi.');
    }

    public function revokeToken(Request $request, ApiToken $apiToken)
    {
        abort_if($apiToken->tenant_id !== $request->user()->tenant_id, 403);
        $apiToken->update(['is_active' => false]);
        return back()->with('success', 'Token dicabut.');
    }

    public function destroyToken(Request $request, ApiToken $apiToken)
    {
        abort_if($apiToken->tenant_id !== $request->user()->tenant_id, 403);
        $apiToken->delete();
        return back()->with('success', 'Token dihapus.');
    }

    // ─── Webhooks ─────────────────────────────────────────────────

    public function storeWebhook(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(!$tenantId, 403);

        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'url'      => 'required|url|max:500',
            'events'   => 'required|array|min:1',
            'events.*' => 'string',
        ]);

        WebhookSubscription::create([
            'tenant_id' => $tenantId,
            'name'      => $validated['name'],
            'url'       => $validated['url'],
            'secret'    => Str::random(32),
            'events'    => $validated['events'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Webhook subscription dibuat.');
    }

    public function toggleWebhook(Request $request, WebhookSubscription $webhookSubscription)
    {
        abort_if($webhookSubscription->tenant_id !== $request->user()->tenant_id, 403);
        $webhookSubscription->update(['is_active' => !$webhookSubscription->is_active]);
        return back()->with('success', 'Status webhook diperbarui.');
    }

    public function destroyWebhook(Request $request, WebhookSubscription $webhookSubscription)
    {
        abort_if($webhookSubscription->tenant_id !== $request->user()->tenant_id, 403);
        $webhookSubscription->delete();
        return back()->with('success', 'Webhook dihapus.');
    }

    public function testWebhook(Request $request, WebhookSubscription $webhookSubscription)
    {
        abort_if($webhookSubscription->tenant_id !== $request->user()->tenant_id, 403);

        app(WebhookService::class)->deliver($webhookSubscription, 'test.ping', [
            'message' => 'Test webhook dari Qalcuity ERP',
            'time'    => now()->toIso8601String(),
        ]);

        return back()->with('success', 'Test webhook dikirim.');
    }

    public function webhookDeliveries(Request $request, WebhookSubscription $webhookSubscription)
    {
        abort_if($webhookSubscription->tenant_id !== $request->user()->tenant_id, 403);

        $deliveries = $webhookSubscription->deliveries()->latest()->limit(50)->get();
        return response()->json($deliveries);
    }

    public function retryDelivery(Request $request, WebhookDelivery $webhookDelivery)
    {
        $subscription = $webhookDelivery->subscription;
        abort_if($subscription->tenant_id !== $request->user()->tenant_id, 403);

        \App\Jobs\DispatchWebhookJob::dispatch(
            $subscription,
            $webhookDelivery->event,
            $webhookDelivery->payload,
            $webhookDelivery->attempt + 1,
        );

        return back()->with('success', 'Webhook sedang dikirim ulang.');
    }

    public function deliveryLog(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        abort_if(!$tenantId, 403);

        $deliveries = WebhookDelivery::whereHas('subscription', fn ($q) => $q->where('tenant_id', $tenantId))
            ->with('subscription:id,name,url')
            ->latest()
            ->paginate(50);

        return view('settings.webhook-log', compact('deliveries'));
    }
}
