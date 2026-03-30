<?php

namespace App\Services\ERP;

use App\Services\AiFinancialAdvisorService;

class AdvisorTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'get_ai_advisor',
                'description' => 'Dapatkan rekomendasi strategis AI Financial Advisor berdasarkan seluruh data bisnis. '
                    . 'Gunakan untuk: "rekomendasi bisnis", "saran keuangan", "apa yang harus saya lakukan?", '
                    . '"analisis bisnis saya", "advisor", "saran AI", "tips bisnis minggu ini".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [],
                ],
            ],
        ];
    }

    public function getAiAdvisor(array $args): array
    {
        $service = app(AiFinancialAdvisorService::class);
        $recommendations = $service->generateRecommendations($this->tenantId, 'on_demand');

        if (empty($recommendations)) {
            return [
                'status'  => 'empty',
                'message' => 'Belum cukup data untuk menghasilkan rekomendasi. Pastikan sudah ada data penjualan, pengeluaran, atau aktivitas bisnis lainnya.',
            ];
        }

        $icons = ['critical' => '🚨', 'warning' => '⚠️', 'info' => '💡'];
        $lines = ["## 🧠 Rekomendasi AI Financial Advisor\n"];

        foreach ($recommendations as $i => $rec) {
            $icon = $icons[$rec['severity']] ?? '💡';
            $num = $i + 1;
            $lines[] = "### {$num}. {$icon} {$rec['title']}";
            $lines[] = $rec['body'];
            $lines[] = '';
        }

        $lines[] = '---';
        $lines[] = '_Rekomendasi ini dihasilkan berdasarkan analisis seluruh data bisnis Anda oleh AI._';

        return [
            'status'  => 'success',
            'message' => implode("\n", $lines),
            'data'    => ['count' => count($recommendations), 'recommendations' => $recommendations],
        ];
    }
}
