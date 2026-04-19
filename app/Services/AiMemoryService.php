<?php

namespace App\Services;

use App\DTOs\Agent\AgentPlan;
use App\Models\AiLearnedPattern;
use App\Models\AiMemory;

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
        // New keys (Task 20)
        'frequent_suppliers',
        'typical_order_quantity',
        'preferred_discount',
        'preferred_payment_terms',
        'preferred_delivery_address',
        'tax_preference',
    ];

    /**
     * Catat aksi user untuk pembelajaran pola.
     */
    public function recordAction(int $tenantId, int $userId, string $action, array $context = []): void
    {
        $key   = $this->actionToKey($action);
        $value = $this->extractValue($action, $context);

        if (!$key || $value === null) return;

        $existing = AiMemory::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('key', $key)
            ->first();

        // Build contextual metadata
        $metadata = $this->buildMetadata($key, $context);

        if (!$existing) {
            // First time recording: set first_observed_at and initial confidence
            AiMemory::create([
                'tenant_id'        => $tenantId,
                'user_id'          => $userId,
                'key'              => $key,
                'value'            => $value,
                'last_seen_at'     => now(),
                'first_observed_at' => now(),
                'confidence_score' => 0.5,
                'frequency'        => 1,
                'metadata'         => $metadata ?: null,
            ]);
        } else {
            // Subsequent update: increment frequency, recalculate confidence
            $newFrequency = $existing->frequency + 1;
            $newConfidence = min(1.0, $newFrequency / 10);

            $existing->update([
                'value'            => $value,
                'last_seen_at'     => now(),
                'frequency'        => $newFrequency,
                'confidence_score' => $newConfidence,
                'metadata'         => $metadata ?: $existing->metadata,
            ]);
        }
    }

    /**
     * Ambil semua preferensi user.
     * Includes support for 6 new preference keys (Task 20).
     */
    public function getPreferences(int $tenantId, int $userId): array
    {
        $rows = AiMemory::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->orderByDesc('frequency')
            ->get(['key', 'value', 'frequency', 'last_seen_at', 'confidence_score']);

        $prefs = $rows->keyBy('key')->map(fn($m) => $m->value)->toArray();

        // Ensure all tracked keys have at least a null entry for callers that expect them
        $allKeys = self::KEYS;
        foreach ($allKeys as $key) {
            if (!array_key_exists($key, $prefs)) {
                $prefs[$key] = null;
            }
        }

        return $prefs;
    }

    /**
     * Buat konteks memori untuk diinjeksi ke system prompt Gemini.
     * Refactored to structured JSON context (Task 20).
     */
    public function buildMemoryContext(int $tenantId, int $userId): string
    {
        $memories = AiMemory::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('frequency', '>=', 2)
            ->orderByDesc('confidence_score')
            ->limit(15)
            ->get();

        $patterns = AiLearnedPattern::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('confidence', '>=', 0.6)
            ->orderByDesc('confidence')
            ->limit(10)
            ->get();

        if ($memories->isEmpty() && $patterns->isEmpty()) {
            return '';
        }

        $context = [];

        // Group memories into structured categories
        $context['preferensi_user'] = [];
        foreach ($memories as $m) {
            $context['preferensi_user'][] = [
                'kategori'        => $m->key,
                'nilai'           => $m->value,
                'frekuensi'       => $m->frequency,
                'kepercayaan'     => round($m->confidence_score, 2),
                'terakhir_terlihat' => $m->last_seen_at?->diffForHumans() ?? '-',
            ];
        }

        // Add learned patterns
        $context['pola_transaksi'] = [];
        foreach ($patterns as $p) {
            $context['pola_transaksi'][] = [
                'tipe'        => $p->pattern_type,
                'entitas'     => $p->entity_type,
                'data'        => $p->pattern_data,
                'kepercayaan' => round($p->confidence, 2),
            ];
        }

        // Generate contextual suggestions
        $context['saran_kontekstual'] = $this->generateStructuredSuggestions($memories, $patterns);

        return "## KONTEKS PERSONAL USER (JSON):\n" . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Buat saran kontekstual berdasarkan pola user.
     * Updated with 6 new preference keys (Task 20).
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

        // New keys (Task 20)
        if (!empty($prefs['frequent_suppliers'])) {
            $suppliers = is_array($prefs['frequent_suppliers'])
                ? array_slice($prefs['frequent_suppliers'], 0, 3)
                : [$prefs['frequent_suppliers']];
            $suggestions[] = "Supplier yang sering dipakai: " . implode(', ', $suppliers) . ". Pertimbangkan untuk pre-select supplier ini.";
        }

        if (!empty($prefs['typical_order_quantity'])) {
            $qty = is_array($prefs['typical_order_quantity'])
                ? ($prefs['typical_order_quantity'][0] ?? '')
                : $prefs['typical_order_quantity'];
            if ($qty) {
                $suggestions[] = "Rata-rata qty per transaksi user adalah '{$qty}'. Gunakan sebagai default quantity.";
            }
        }

        if (!empty($prefs['preferred_discount'])) {
            $disc = is_array($prefs['preferred_discount'])
                ? ($prefs['preferred_discount'][0] ?? '')
                : $prefs['preferred_discount'];
            if ($disc) {
                $suggestions[] = "Range diskon yang biasa dipakai: '{$disc}'. Sarankan diskon ini saat membuat penawaran.";
            }
        }

        if (!empty($prefs['preferred_payment_terms'])) {
            $terms = is_array($prefs['preferred_payment_terms'])
                ? ($prefs['preferred_payment_terms'][0] ?? '')
                : $prefs['preferred_payment_terms'];
            if ($terms) {
                $suggestions[] = "Terms pembayaran favorit user: '{$terms}'. Pre-fill terms ini pada invoice/PO baru.";
            }
        }

        if (!empty($prefs['preferred_delivery_address'])) {
            $addr = is_array($prefs['preferred_delivery_address'])
                ? ($prefs['preferred_delivery_address'][0] ?? '')
                : $prefs['preferred_delivery_address'];
            if ($addr) {
                $suggestions[] = "Alamat pengiriman default: '{$addr}'. Gunakan sebagai alamat kirim otomatis.";
            }
        }

        if (!empty($prefs['tax_preference'])) {
            $tax = is_array($prefs['tax_preference'])
                ? ($prefs['tax_preference'][0] ?? '')
                : $prefs['tax_preference'];
            if ($tax) {
                $suggestions[] = "Preferensi pajak user: '{$tax}'. Terapkan konfigurasi pajak ini secara default.";
            }
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

    /**
     * Hitung ulang confidence_score untuk semua memori user berdasarkan frekuensi + recency.
     */
    public static function recalculateConfidence(int $tenantId, int $userId): void
    {
        $memories = AiMemory::where('tenant_id', $tenantId)
            ->where('user_id', $userId)->get();

        foreach ($memories as $memory) {
            $frequencyScore = min(1.0, $memory->frequency / 10);
            $recencyDays    = $memory->last_seen_at ? now()->diffInDays($memory->last_seen_at) : 90;
            $recencyScore   = max(0.1, 1.0 - ($recencyDays / 90));
            $memory->confidence_score = round(($frequencyScore * 0.6) + ($recencyScore * 0.4), 2);
            $memory->save();
        }
    }

    /**
     * Hapus memori yang sudah stale (confidence rendah & lama tidak diperbarui).
     *
     * @deprecated Gunakan pruneStaleMemoriesForTenant() untuk logika decay sesuai spec.
     */
    public static function pruneStaleMemories(int $tenantId, ?int $userId = null): int
    {
        $query = AiMemory::where('tenant_id', $tenantId)
            ->where('confidence_score', '<', 0.3)
            ->where('updated_at', '<', now()->subDays(90));

        if ($userId) $query->where('user_id', $userId);

        return $query->delete();
    }

    /**
     * Turunkan confidence_score 50% untuk record dengan last_seen_at > 90 hari,
     * lalu hapus record dengan confidence_score hasil penurunan < 0.1.
     *
     * Diproses secara chunked untuk performa pada dataset besar.
     *
     * Requirements: 5.5
     */
    public function pruneStaleMemoriesForTenant(int $tenantId): void
    {
        $cutoff = now()->subDays(90);

        // Proses dalam chunk untuk menghindari memory exhaustion
        AiMemory::where('tenant_id', $tenantId)
            ->where('last_seen_at', '<', $cutoff)
            ->chunkById(200, function ($chunk) {
                $toDelete = [];

                foreach ($chunk as $memory) {
                    $newScore = $memory->confidence_score * 0.5;

                    if ($newScore < 0.1) {
                        $toDelete[] = $memory->id;
                    } else {
                        $memory->confidence_score = $newScore;
                        $memory->save();
                    }
                }

                if (!empty($toDelete)) {
                    AiMemory::whereIn('id', $toDelete)->delete();
                }
            });
    }

    /**
     * Simpan pola task yang berhasil sebagai template di AiLearnedPattern.
     *
     * Menggunakan updateOrCreate dengan hash key untuk menghindari duplikasi.
     * Scope ke kombinasi tenant_id + user_id.
     *
     * Requirements: 5.3
     */
    public function saveTaskPattern(int $tenantId, int $userId, AgentPlan $plan): void
    {
        // Kumpulkan nama tool yang digunakan dalam plan
        $toolNames = array_map(fn($step) => $step->toolName, $plan->steps);

        // Ringkasan langkah-langkah
        $stepsSummary = array_map(fn($step) => [
            'order'    => $step->order,
            'name'     => $step->name,
            'toolName' => $step->toolName,
            'isWrite'  => $step->isWriteOp,
        ], $plan->steps);

        $patternData = [
            'goal'          => $plan->goal,
            'summary'       => $plan->summary,
            'steps'         => $stepsSummary,
            'tools_used'    => array_values(array_unique($toolNames)),
            'language'      => $plan->language,
            'has_write_ops' => $plan->hasWriteOps,
            'step_count'    => count($plan->steps),
        ];

        // Hash unik berdasarkan goal + tools untuk dedup
        $hashKey = md5($tenantId . '|' . $userId . '|' . $plan->goal . '|' . implode(',', $toolNames));

        AiLearnedPattern::updateOrCreate(
            [
                'tenant_id'    => $tenantId,
                'user_id'      => $userId,
                'pattern_type' => 'task_template',
                'entity_type'  => 'agent_plan',
                'entity_id'    => null,
            ],
            [
                'pattern_data' => array_merge($patternData, ['hash' => $hashKey]),
                'confidence'   => min(1.0, ($patternData['step_count'] / 10) * 0.8 + 0.2),
                'analyzed_at'  => now(),
            ]
        );
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Generate suggestion strings dari memories + patterns (untuk buildMemoryContext).
     */
    private function generateStructuredSuggestions($memories, $patterns): array
    {
        $suggestions = [];

        // Build a quick lookup from memories
        $memMap = $memories->keyBy('key');

        $keyMap = [
            'skipped_steps'             => fn($v) => "User sering melewati langkah '{$v}'. Tawarkan jadikan default flow.",
            'preferred_payment_method'  => fn($v) => "Default metode pembayaran: '{$v}'.",
            'default_warehouse'         => fn($v) => "Pre-select gudang '{$v}' untuk transaksi stok.",
            'frequent_customers'        => fn($v) => "Customer yang sering digunakan: " . (is_array($v) ? implode(', ', array_slice($v, 0, 3)) : $v) . ".",
            'frequent_suppliers'        => fn($v) => "Supplier yang sering dipakai: " . (is_array($v) ? implode(', ', array_slice($v, 0, 3)) : $v) . ".",
            'typical_order_quantity'    => fn($v) => "Rata-rata qty per transaksi: '{$v}'. Gunakan sebagai default quantity.",
            'preferred_discount'        => fn($v) => "Range diskon yang biasa: '{$v}'.",
            'preferred_payment_terms'   => fn($v) => "Terms pembayaran favorit: '{$v}'. Pre-fill pada invoice/PO baru.",
            'preferred_delivery_address' => fn($v) => "Alamat pengiriman default: '{$v}'.",
            'tax_preference'            => fn($v) => "Preferensi pajak: '{$v}'. Terapkan secara default.",
            'frequent_products'         => fn($v) => "Produk yang sering digunakan: " . (is_array($v) ? implode(', ', array_slice($v, 0, 3)) : $v) . ".",
        ];

        foreach ($keyMap as $key => $formatter) {
            if (isset($memMap[$key])) {
                $val = $memMap[$key]->value;
                if ($val !== null && $val !== '' && $val !== []) {
                    $scalar = is_array($val) ? ($val[0] ?? null) : $val;
                    if ($scalar !== null) {
                        $suggestions[] = $formatter($val);
                    }
                }
            }
        }

        // Add pattern-based suggestions
        foreach ($patterns as $p) {
            if ($p->pattern_type === 'frequent_entity' && $p->entity_type) {
                $label   = ucfirst($p->entity_type);
                $name    = $p->pattern_data['name'] ?? $p->pattern_data['label'] ?? $p->entity_id ?? '?';
                $suggestions[] = "{$label} yang sering digunakan berdasarkan pola: '{$name}' (confidence: " . round($p->confidence, 2) . ").";
            }
        }

        return $suggestions;
    }

    /**
     * Build contextual metadata based on key and context.
     */
    private function buildMetadata(string $key, array $context): array
    {
        $metadata = [];

        // Store contextual information for specific keys
        if ($key === 'frequent_suppliers' && !empty($context['product_name'])) {
            $metadata['last_product'] = $context['product_name'];
        }

        if ($key === 'frequent_products' && !empty($context['supplier_name'])) {
            $metadata['last_supplier'] = $context['supplier_name'];
        }

        if ($key === 'preferred_delivery_address' && !empty($context['customer_name'])) {
            $metadata['for_customer'] = $context['customer_name'];
        }

        if ($key === 'preferred_discount' && !empty($context['customer_name'])) {
            $metadata['for_customer'] = $context['customer_name'];
        }

        if ($key === 'typical_order_quantity' && !empty($context['product_name'])) {
            $metadata['for_product'] = $context['product_name'];
        }

        return $metadata;
    }

    private function actionToKey(string $action): ?string
    {
        return match (true) {
            str_contains($action, 'payment_method')    => 'preferred_payment_method',
            str_contains($action, 'warehouse')          => 'default_warehouse',
            str_contains($action, 'customer')           => 'frequent_customers',
            str_contains($action, 'skip')               => 'skipped_steps',
            str_contains($action, 'product')            => 'frequent_products',
            str_contains($action, 'report_period')      => 'preferred_report_period',
            str_contains($action, 'cost_center')        => 'default_cost_center',
            str_contains($action, 'supplier')           => 'frequent_suppliers',
            str_contains($action, 'order_quantity')     => 'typical_order_quantity',
            str_contains($action, 'discount')           => 'preferred_discount',
            str_contains($action, 'payment_terms')      => 'preferred_payment_terms',
            str_contains($action, 'delivery_address')   => 'preferred_delivery_address',
            str_contains($action, 'tax')                => 'tax_preference',
            default                                     => null,
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
            'preferred_payment_method'   => 'Metode pembayaran favorit',
            'default_warehouse'          => 'Gudang default',
            'frequent_customers'         => 'Customer yang sering digunakan',
            'skipped_steps'              => 'Langkah yang sering dilewati',
            'frequent_products'          => 'Produk yang sering digunakan',
            'preferred_report_period'    => 'Periode laporan favorit',
            'default_cost_center'        => 'Cost center default',
            'frequent_suppliers'         => 'Supplier yang sering dipakai',
            'typical_order_quantity'     => 'Rata-rata qty per transaksi',
            'preferred_discount'         => 'Range diskon yang biasa',
            'preferred_payment_terms'    => 'Terms pembayaran favorit',
            'preferred_delivery_address' => 'Alamat pengiriman default',
            'tax_preference'             => 'Preferensi pajak',
            default                      => null,
        };
    }
}
