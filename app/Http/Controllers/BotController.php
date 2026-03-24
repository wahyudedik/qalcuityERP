<?php

namespace App\Http\Controllers;

use App\Models\BotConfig;
use App\Models\BotMessage;
use App\Services\BotService;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class BotController extends Controller
{
    public function __construct(private BotService $bot) {}

    public function settings()
    {
        $tenantId = auth()->user()->tenant_id;
        $telegram = BotConfig::where('tenant_id', $tenantId)->where('platform', 'telegram')->first();
        $whatsapp = BotConfig::where('tenant_id', $tenantId)->where('platform', 'whatsapp')->first();
        return view('settings.bot', compact('telegram', 'whatsapp'));
    }

    public function saveSettings(Request $request)
    {
        $request->validate(['platform' => 'required|in:telegram,whatsapp', 'token' => 'required|string']);
        $tenantId = auth()->user()->tenant_id;

        BotConfig::updateOrCreate(
            ['tenant_id' => $tenantId, 'platform' => $request->platform],
            [
                'token'               => $request->token,
                'is_active'           => $request->boolean('is_active'),
                'notification_events' => array_keys(array_filter([
                    'new_order'  => $request->boolean('notify_new_order'),
                    'low_stock'  => $request->boolean('notify_low_stock'),
                    'payment'    => $request->boolean('notify_payment'),
                    'approval'   => $request->boolean('notify_approval'),
                ])),
            ]
        );

        ActivityLog::record('bot_settings_saved', "Konfigurasi bot {$request->platform} disimpan");

        return back()->with('success', 'Konfigurasi bot disimpan.');
    }

    // Telegram webhook
    public function telegramWebhook(Request $request)
    {
        $data = $request->all();
        $chatId  = $data['message']['chat']['id'] ?? null;
        $text    = $data['message']['text'] ?? '';
        $sender  = $data['message']['from']['first_name'] ?? 'Unknown';

        if (!$chatId) return response()->json(['ok' => true]);

        // Match config by bot token from request header or find by chat_id mapping
        // Use the token from the URL path segment (Telegram sends to /webhook/{token})
        $token  = $request->route('token') ?? $request->header('X-Bot-Token');
        if ($token) {
            $config = BotConfig::where('platform', 'telegram')
                ->where('token', $token)
                ->where('is_active', true)
                ->first();
        } else {
            // Fallback: match by chat_id stored in config metadata
            $config = BotConfig::where('platform', 'telegram')
                ->where('is_active', true)
                ->whereJsonContains('notification_events', 'new_order') // at least configured
                ->whereRaw("JSON_SEARCH(payload, 'one', ?) IS NOT NULL", [(string) $chatId])
                ->first();
        }

        if (!$config) return response()->json(['ok' => true]);

        BotMessage::create([
            'tenant_id'  => $config->tenant_id,
            'platform'   => 'telegram',
            'direction'  => 'inbound',
            'recipient'  => (string) $chatId,
            'message'    => $text,
            'status'     => 'sent',
            'sent_at'    => now(),
            'payload'    => ['chat_id' => $chatId, 'sender' => $sender],
        ]);

        $this->bot->handleTelegram($config, $chatId, $text);

        return response()->json(['ok' => true]);
    }

    // WhatsApp webhook
    public function whatsappWebhook(Request $request)
    {
        // Verification challenge
        if ($request->has('hub_challenge')) {
            return response($request->hub_challenge, 200);
        }

        $data    = $request->all();
        $entry   = $data['entry'][0]['changes'][0]['value'] ?? null;
        $message = $entry['messages'][0] ?? null;

        if (!$message) return response()->json(['ok' => true]);

        // Match config by phone number ID from the webhook payload
        $phoneNumberId = $entry['metadata']['phone_number_id'] ?? null;
        if ($phoneNumberId) {
            $config = BotConfig::where('platform', 'whatsapp')
                ->where('is_active', true)
                ->whereJsonContains('payload->phone_number_id', $phoneNumberId)
                ->first();
        } else {
            // Fallback: match by token in request header set by WhatsApp
            $token  = $request->header('X-Hub-Signature-256') ?? $request->header('X-Bot-Token');
            $config = $token
                ? BotConfig::where('platform', 'whatsapp')->where('token', $token)->where('is_active', true)->first()
                : null;
        }

        if (!$config) return response()->json(['ok' => true]);

        BotMessage::create([
            'tenant_id'  => $config->tenant_id,
            'platform'   => 'whatsapp',
            'direction'  => 'inbound',
            'recipient'  => $message['from'],
            'message'    => $message['text']['body'] ?? '',
            'status'     => 'sent',
            'sent_at'    => now(),
            'payload'    => ['from' => $message['from'], 'sender' => $entry['contacts'][0]['profile']['name'] ?? 'Unknown'],
        ]);

        return response()->json(['ok' => true]);
    }
}
