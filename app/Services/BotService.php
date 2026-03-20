<?php

namespace App\Services;

use App\Models\BotConfig;
use App\Models\BotMessage;
use Illuminate\Support\Facades\Http;

class BotService
{
    public function sendTelegram(BotConfig $config, string $chatId, string $message): bool
    {
        if (!$config->token) return false;

        $response = Http::post("https://api.telegram.org/bot{$config->token}/sendMessage", [
            'chat_id'    => $chatId,
            'text'       => $message,
            'parse_mode' => 'HTML',
        ]);

        if ($response->successful()) {
            BotMessage::create([
                'tenant_id'  => $config->tenant_id,
                'platform'   => 'telegram',
                'direction'  => 'outbound',
                'recipient'  => $chatId,
                'message'    => $message,
                'status'     => 'sent',
                'sent_at'    => now(),
            ]);
        }

        return $response->successful();
    }

    public function handleTelegram(BotConfig $config, string $chatId, string $text): void
    {
        $text = strtolower(trim($text));
        $reply = match(true) {
            str_starts_with($text, '/start')  => "Halo! Saya adalah bot ERP Qalcuity. Ketik /help untuk melihat perintah.",
            str_starts_with($text, '/help')   => "Perintah tersedia:\n/stok - Cek stok rendah\n/order - Order terbaru\n/laporan - Ringkasan hari ini",
            str_starts_with($text, '/stok')   => $this->getLowStockMessage($config->tenant_id),
            str_starts_with($text, '/order')  => $this->getRecentOrdersMessage($config->tenant_id),
            default => "Perintah tidak dikenali. Ketik /help untuk bantuan.",
        };

        $this->sendTelegram($config, $chatId, $reply);
    }

    public function notifyAll(int $tenantId, string $event, string $message): void
    {
        $configs = BotConfig::where('tenant_id', $tenantId)->where('is_active', true)->get();

        foreach ($configs as $config) {
            // Cek apakah event ini diaktifkan di notification_events
            $events = $config->notification_events ?? [];
            if (!empty($events) && !in_array($event, $events)) continue;

            if ($config->platform === 'telegram') {
                // Gunakan chat_id yang tersimpan di config, atau ambil dari pesan inbound terakhir
                $chatId = $config->chat_id;
                if (!$chatId) {
                    $lastMsg = BotMessage::where('tenant_id', $tenantId)
                        ->where('platform', 'telegram')
                        ->where('direction', 'inbound')
                        ->latest()
                        ->value('recipient');
                    $chatId = $lastMsg;
                }

                if ($chatId) {
                    $this->sendTelegram($config, $chatId, $message);
                }
            }
        }
    }

    private function getLowStockMessage(int $tenantId): string
    {
        $products = \App\Models\Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereHas('productStocks', fn($q) => $q->whereColumn('quantity', '<=', \Illuminate\Support\Facades\DB::raw('(SELECT stock_min FROM products WHERE id = product_stocks.product_id)')))
            ->take(5)
            ->get(['id', 'name', 'stock_min']);

        if ($products->isEmpty()) return "Tidak ada produk dengan stok rendah.";

        $lines = $products->map(fn($p) => "• {$p->name}: " . $p->totalStock() . " (min: {$p->stock_min})")->join("\n");
        return "⚠️ Stok Rendah:\n{$lines}";
    }

    private function getRecentOrdersMessage(int $tenantId): string
    {
        $orders = \App\Models\SalesOrder::where('tenant_id', $tenantId)
            ->latest()
            ->take(5)
            ->get(['number', 'total', 'status']);

        if ($orders->isEmpty()) return "Belum ada order.";

        $lines = $orders->map(fn($o) => "• #{$o->number} - Rp " . number_format($o->total) . " ({$o->status})")->join("\n");
        return "📦 Order Terbaru:\n{$lines}";
    }
}
