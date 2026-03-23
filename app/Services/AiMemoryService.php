<?php

namespace App\Services;

use App\Models\AiMemory;
use Illuminate\Support\Facades\DB;

/**
 * AiMemoryService — Task 52
 * Simpan dan ambil preferensi/kebiasaan user per tenant untuk konteks AI.
 */
class AiMemoryService
{
    // Key-key yang dilacak
    const KEYS = [
        'preferred_payment_method',
        'default_warehouse',
        'frequent_customers',
        'skipped_steps',
        'preferred_currency',
        'default_cost_center',
        'frequent_products',
        'preferred_report_period',
    ];

    /**
     * Catat aksi user untuk pembelajaran pola.
     */
    public function recordAction(int $tenantId, int $userId, string $action, array $context = []): void
    {
        $key   = $this->actionToKey($action);
        $value = $this->extractValue($action, $context);

        if (!$key || $value === null) return;

        AiMemory::updateOrCreate(
            ['tenant_id' => $tenantId, 'user_id' => $userId, 'key' => $key],
            ['value' => $value, 'last_seen_at' => now()]
        );

        // Increment frequency
        AiMemory::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('key', $key)
            ->increment('frequency');
    }

    /**
     * Ambil semua preferensi user.
     */
    public function getPreferences(int $tenantId, int $userId): array
    {
        return AiMemory::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->orderByDesc('frequency')
            ->get(['key', 'value', 'frequency', 'last_seen_at'])
            ->keyBy('key')
            ->map(fn($m) => $m->value)
            ->toArray();
    }

    /**
     * Buat konteks memori untuk diinjeksi ke system prompt Gemini.
     */
    public function buildMemoryContext(int $tenantId, int $userId): string
    {
        $memories = AiMemory::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('frequency', '>=', 2) // hanya pola yang sudah muncul minimal 2x
            ->orderByDesc('frequency')
            ->limit(10)
            ->get();

        if ($memories->isEmpty()) return '';

        $lines = ['## PREFERENSI & KEBIASAAN USER:'];

        foreach ($memories as $memory) {
            $label = $this->keyToLabel($memory->key);
            $val   = is_array($memory->value) ? implode(', ', array_slice($memory->value, 0, 3)) : $memory->value;
            if ($label && $val) {
                $lines[] = "- {$label}: {$val}";
            }
        }

        // Tambahkan saran berdasarkan pola
        $suggestions = $this->getSuggestions($tenantId, $userId);
        if (!empty($suggestions)) {
            $lines[] = '';
            $lines[] = '## SARAN BERDASARKAN KEBIASAAN:';
            foreach ($suggestions as $s) {
                $lines[] = "- {$s}";
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Buat saran kontekstual berdasarkan pola user.
     */
    public function getSuggestions(int $tenantId, int $userId, array $context = []): array
    {
        $prefs = $this->getPreferences($tenantId, $userId);
        $suggestions = [];

        if (!empty($prefs['skipped_steps'])) {
            $skipped = is_array($prefs['skipped_steps']) ? $prefs['skipped_steps'] : [$prefs['skipped_steps']];
            foreach ($skipped as $step) {
                $suggestions[] = "User sering melewati langkah '{$step}'. Pertimbangkan untuk menawarkan jadikan default flow.";
            }
        }

        if (!empty($prefs['preferred_payment_method'])) {
            $method = is_array($prefs['preferred_payment_method'])
                ? ($prefs['preferred_payment_method'][0] ?? '')
                : $prefs['preferred_payment_method'];
            if ($method) {
                $suggestions[] = "Gunakan metode pembayaran '{$method}' sebagai default saat membuat transaksi baru.";
            }
        }

        if (!empty($prefs['default_warehouse'])) {
            $wh = is_array($prefs['default_warehouse'])
                ? ($prefs['default_warehouse'][0] ?? '')
                : $prefs['default_warehouse'];
            if ($wh) {
                $suggestions[] = "Pre-select gudang '{$wh}' sebagai default untuk transaksi stok.";
            }
        }

        if (!empty($prefs['frequent_customers'])) {
            $customers = is_array($prefs['frequent_customers'])
                ? array_slice($prefs['frequent_customers'], 0, 3)
                : [$prefs['frequent_customers']];
            $suggestions[] = "Customer yang sering digunakan: " . implode(', ', $customers) . ".";
        }

        return $suggestions;
    }

    /**
     * Reset semua memori user.
     */
    public function resetMemory(int $tenantId, int $userId): int
    {
        return AiMemory::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->delete();
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function actionToKey(string $action): ?string
    {
        return match (true) {
            str_contains($action, 'payment_method') => 'preferred_payment_method',
            str_contains($action, 'warehouse')       => 'default_warehouse',
            str_contains($action, 'customer')        => 'frequent_customers',
            str_contains($action, 'skip')            => 'skipped_steps',
            str_contains($action, 'product')         => 'frequent_products',
            str_contains($action, 'report_period')   => 'preferred_report_period',
            str_contains($action, 'cost_center')     => 'default_cost_center',
            default                                  => null,
        };
    }

    private function extractValue(string $action, array $context): mixed
    {
        // Ambil nilai dari context jika ada
        if (!empty($context['value'])) return $context['value'];
        if (!empty($context['name']))  return $context['name'];

        // Fallback: gunakan action sebagai value
        return $action;
    }

    private function keyToLabel(string $key): ?string
    {
        return match ($key) {
            'preferred_payment_method' => 'Metode pembayaran favorit',
            'default_warehouse'        => 'Gudang default',
            'frequent_customers'       => 'Customer yang sering digunakan',
            'skipped_steps'            => 'Langkah yang sering dilewati',
            'frequent_products'        => 'Produk yang sering digunakan',
            'preferred_report_period'  => 'Periode laporan favorit',
            'default_cost_center'      => 'Cost center default',
            default                    => null,
        };
    }
}
