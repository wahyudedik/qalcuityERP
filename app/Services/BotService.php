<?php

namespace App\Services;

use App\Models\BotConfig;
use App\Models\BotMessage;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * BotService — Handle incoming messages and send outbound notifications
 * via WhatsApp Business API (Cloud API) and Telegram Bot API.
 *
 * WhatsApp supports two providers:
 *   1. Meta Cloud API (official) — token = permanent access token
 *   2. Fonnte.com (Indonesian provider, simpler) — token = Fonnte device token
 *
 * Auto-detect provider by token format.
 */
class BotService
{
    // ─── Inbound Message Handlers ─────────────────────────────────

    /**
     * Handle incoming Telegram message — parse command and reply.
     */
    public function handleTelegram(BotConfig $config, string $chatId, string $text): void
    {
        $reply = $this->processCommand($config->tenant_id, $text);
        $this->sendTelegram($config->token, $chatId, $reply);

        BotMessage::create([
            'tenant_id' => $config->tenant_id,
            'platform' => 'telegram',
            'direction' => 'outbound',
            'recipient' => $chatId,
            'message' => $reply,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Handle incoming WhatsApp message — parse command and reply.
     */
    public function handleWhatsApp(BotConfig $config, string $from, string $text, string $senderName = ''): void
    {
        $reply = $this->processCommand($config->tenant_id, $text);
        $this->sendWhatsApp($config, $from, $reply);

        BotMessage::create([
            'tenant_id' => $config->tenant_id,
            'platform' => 'whatsapp',
            'direction' => 'outbound',
            'recipient' => $from,
            'message' => $reply,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    // ─── Command Parser ───────────────────────────────────────────

    /**
     * Parse text command and return reply string.
     * Simple keyword-based — no AI needed for basic queries.
     */
    private function processCommand(int $tenantId, string $text): string
    {
        $text = strtolower(trim($text));

        // /start or /help
        if (in_array($text, ['/start', '/help', 'help', 'menu', 'halo', 'hi'])) {
            return "🤖 *Qalcuity ERP Bot*\n\n"
                ."Perintah yang tersedia:\n"
                ."📊 *omzet* — Omzet hari ini\n"
                ."📦 *stok* — Produk stok rendah\n"
                ."🧾 *invoice* — Invoice jatuh tempo\n"
                ."👤 *customer* — Jumlah customer\n"
                ."📋 *order* — Order pending\n"
                ."🔍 *cari [nama]* — Cari produk\n\n"
                .'Ketik perintah di atas untuk mulai.';
        }

        // Omzet hari ini
        if (str_contains($text, 'omzet') || str_contains($text, 'revenue') || str_contains($text, 'penjualan')) {
            $today = SalesOrder::where('tenant_id', $tenantId)
                ->whereNotIn('status', ['cancelled'])
                ->whereDate('date', today())
                ->sum('total');
            $count = SalesOrder::where('tenant_id', $tenantId)
                ->whereNotIn('status', ['cancelled'])
                ->whereDate('date', today())
                ->count();

            return "📊 *Omzet Hari Ini*\n\n"
                .'💰 Rp '.number_format($today, 0, ',', '.')."\n"
                ."📋 {$count} order";
        }

        // Stok rendah
        if (str_contains($text, 'stok') || str_contains($text, 'stock')) {
            $low = Product::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->whereHas('productStocks', fn ($q) => $q->whereColumn('quantity', '<=', 'products.stock_min'))
                ->limit(10)
                ->get();

            if ($low->isEmpty()) {
                return '✅ Semua stok aman, tidak ada yang rendah.';
            }

            $lines = $low->map(fn ($p) => "• {$p->name}: {$p->totalStock()} {$p->unit} (min: {$p->stock_min})");

            return "📦 *Stok Rendah ({$low->count()})*\n\n".$lines->implode("\n");
        }

        // Invoice jatuh tempo
        if (str_contains($text, 'invoice') || str_contains($text, 'tagihan') || str_contains($text, 'piutang')) {
            $overdue = Invoice::where('tenant_id', $tenantId)
                ->whereIn('status', ['unpaid', 'partial'])
                ->where('due_date', '<', today())
                ->with('customer')
                ->limit(10)
                ->get();

            if ($overdue->isEmpty()) {
                return '✅ Tidak ada invoice jatuh tempo.';
            }

            $total = $overdue->sum('remaining_amount');
            $lines = $overdue->map(fn ($i) => "• {$i->number} — {$i->customer?->name} — Rp ".number_format($i->remaining_amount, 0, ',', '.'));

            return "🧾 *Invoice Jatuh Tempo ({$overdue->count()})*\n"
                .'Total: Rp '.number_format($total, 0, ',', '.')."\n\n"
                .$lines->implode("\n");
        }

        // Customer count
        if (str_contains($text, 'customer') || str_contains($text, 'pelanggan')) {
            $count = Customer::where('tenant_id', $tenantId)->where('is_active', true)->count();

            return "👤 Total customer aktif: *{$count}*";
        }

        // Pending orders
        if (str_contains($text, 'order') || str_contains($text, 'pesanan')) {
            $pending = SalesOrder::where('tenant_id', $tenantId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();

            return "📋 Order pending/confirmed: *{$pending}*";
        }

        // Search product
        if (str_starts_with($text, 'cari ') || str_starts_with($text, 'search ')) {
            $keyword = trim(substr($text, strpos($text, ' ') + 1));
            $products = Product::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->where('name', 'like', "%{$keyword}%")
                ->limit(5)
                ->get();

            if ($products->isEmpty()) {
                return "🔍 Tidak ditemukan produk dengan kata kunci \"{$keyword}\".";
            }

            $lines = $products->map(fn ($p) => "• {$p->name} — Rp ".number_format($p->price_sell, 0, ',', '.')." ({$p->totalStock()} {$p->unit})");

            return "🔍 *Hasil Pencarian: \"{$keyword}\"*\n\n".$lines->implode("\n");
        }

        return "🤔 Perintah tidak dikenali.\nKetik *help* untuk melihat daftar perintah.";
    }

    // ─── Outbound: Send Notification ──────────────────────────────

    /**
     * Send notification to all active bot channels for a tenant.
     * Called from ErpNotification or event listeners.
     */
    public function sendNotification(int $tenantId, string $eventType, string $message): void
    {
        $configs = BotConfig::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        foreach ($configs as $config) {
            // Check if this event type is enabled
            $events = $config->notification_events ?? [];
            if (! empty($events) && ! in_array($eventType, $events)) {
                continue;
            }

            try {
                if ($config->platform === 'telegram' && $config->chat_id) {
                    $this->sendTelegram($config->token, $config->chat_id, $message);
                } elseif ($config->platform === 'whatsapp' && $config->phone_number) {
                    $this->sendWhatsApp($config, $config->phone_number, $message);
                }

                BotMessage::create([
                    'tenant_id' => $tenantId,
                    'platform' => $config->platform,
                    'direction' => 'outbound',
                    'recipient' => $config->chat_id ?? $config->phone_number ?? '',
                    'message' => $message,
                    'event_type' => $eventType,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning("BotService notification failed ({$config->platform}): ".$e->getMessage());
            }
        }
    }

    // ─── Transport: Telegram ──────────────────────────────────────

    private function sendTelegram(string $token, string $chatId, string $text): void
    {
        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);
    }

    // ─── Transport: WhatsApp ──────────────────────────────────────

    /**
     * Send WhatsApp message — auto-detect provider:
     *   - Token starts with "EAA" → Meta Cloud API
     *   - Otherwise → Fonnte.com
     */
    private function sendWhatsApp(BotConfig $config, string $to, string $text): void
    {
        $token = $config->token;

        if (str_starts_with($token, 'EAA')) {
            $this->sendWhatsAppMeta($config, $to, $text);
        } else {
            $this->sendWhatsAppFonnte($token, $to, $text);
        }
    }

    /**
     * Meta Cloud API — official WhatsApp Business API.
     * Requires: phone_number_id in BotConfig, permanent access token.
     */
    private function sendWhatsAppMeta(BotConfig $config, string $to, string $text): void
    {
        $phoneNumberId = $config->phone_number; // stored as phone_number_id
        $token = $config->token;

        if (! $phoneNumberId) {
            Log::warning("WhatsApp Meta: phone_number_id not set for config #{$config->id}");

            return;
        }

        Http::withToken($token)->post(
            "https://graph.facebook.com/v18.0/{$phoneNumberId}/messages",
            [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => ['body' => strip_tags(str_replace(['*', '_'], '', $text))],
            ]
        );
    }

    /**
     * Fonnte.com — Indonesian WhatsApp gateway (simpler, no Meta approval needed).
     * Token = device token from fonnte.com dashboard.
     */
    private function sendWhatsAppFonnte(string $token, string $to, string $text): void
    {
        Http::withHeaders(['Authorization' => $token])
            ->post('https://api.fonnte.com/send', [
                'target' => $to,
                'message' => $text,
            ]);
    }
}
