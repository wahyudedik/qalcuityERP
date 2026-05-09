<?php

namespace App\Services\ERP;

use App\Models\BotConfig;
use App\Services\BotService;

class BotTools
{
    public function __construct(
        private int $tenantId,
        private int $userId,
        private BotService $bot = new BotService
    ) {}

    public static function definitions(): array
    {
        return [
            [
                'name' => 'send_bot_notification',
                'description' => 'Kirim notifikasi ke Telegram atau WhatsApp',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'message' => ['type' => 'string', 'description' => 'Pesan yang akan dikirim'],
                        'event' => ['type' => 'string', 'description' => 'Jenis event: new_order, low_stock, payment, approval'],
                    ],
                    'required' => ['message'],
                ],
            ],
            [
                'name' => 'get_bot_status',
                'description' => 'Cek status konfigurasi bot Telegram/WhatsApp',
                'parameters' => ['type' => 'object', 'properties' => []],
            ],
        ];
    }

    public function sendBotNotification(array $args): array
    {
        $this->bot->notifyAll($this->tenantId, $args['event'] ?? 'general', $args['message']);

        return ['status' => 'success', 'message' => 'Notifikasi dikirim.'];
    }

    public function getBotStatus(array $args): array
    {
        $configs = BotConfig::where('tenant_id', $this->tenantId)->get(['platform', 'is_active', 'phone_number', 'chat_id']);

        return ['status' => 'success', 'bots' => $configs->toArray()];
    }
}
