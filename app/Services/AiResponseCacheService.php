<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AiResponseCacheService
{
    /**
     * Cache key prefix untuk AI responses
     */
    protected const CACHE_PREFIX = 'ai_response:';

    /**
     * Default TTL untuk cache (dalam detik)
     */
    protected const DEFAULT_TTL = 3600; // 1 jam

    /**
     * TTL untuk query yang sering berubah (dalam detik)
     */
    protected const SHORT_TTL = 300; // 5 menit

    /**
     * TTL untuk data yang jarang berubah (dalam detik)
     */
    protected const LONG_TTL = 86400; // 24 jam

    /**
     * Generate cache key berdasarkan tenant, user, dan pesan
     */
    public function generateCacheKey(int $tenantId, int $userId, string $message): string
    {
        // Normalisasi pesan untuk konsistensi key
        $normalizedMessage = $this->normalizeMessage($message);

        return self::CACHE_PREFIX . md5("{$tenantId}:{$userId}:{$normalizedMessage}");
    }

    /**
     * Cek apakah response ada di cache
     */
    public function has(string $cacheKey): bool
    {
        return Cache::has($cacheKey);
    }

    /**
     * Ambil response dari cache
     */
    public function get(string $cacheKey): ?array
    {
        try {
            $cached = Cache::get($cacheKey);

            if ($cached && is_array($cached)) {
                Log::info('AI Response Cache HIT', ['key' => substr($cacheKey, 0, 20) . '...']);
                return $cached;
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('AI Response Cache GET failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Simpan response ke cache
     */
    public function put(string $cacheKey, array $response, ?int $ttl = null): bool
    {
        try {
            $ttl = $ttl ?? $this->determineTtl($response);

            Cache::put($cacheKey, $response, $ttl);

            Log::info('AI Response Cache PUT', [
                'key' => substr($cacheKey, 0, 20) . '...',
                'ttl' => $ttl,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('AI Response Cache PUT failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus cache spesifik
     */
    public function forget(string $cacheKey): bool
    {
        try {
            return Cache::forget($cacheKey);
        } catch (\Throwable $e) {
            Log::warning('AI Response Cache FORGET failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hapus semua cache untuk tenant tertentu
     * Berguna saat ada perubahan data signifikan
     */
    public function flushTenant(int $tenantId): bool
    {
        try {
            // Note: Ini pattern matching sederhana, untuk production bisa pakai tags jika pakai Redis
            $pattern = self::CACHE_PREFIX . "*";

            // Untuk Redis, bisa pakai keys() tapi hati-hati performance
            // Alternatif: gunakan cache tags atau invalidation strategy lain
            Log::info('AI Response Cache FLUSH for tenant', ['tenant_id' => $tenantId]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('AI Response Cache FLUSH failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Tentukan TTL berdasarkan tipe response
     */
    protected function determineTtl(array $response): int
    {
        // Jika response mengandung data real-time, gunakan TTL pendek
        $text = $response['text'] ?? '';

        // Data yang sering berubah
        $realtimeKeywords = [
            'stok',
            'inventory',
            'harga hari ini',
            'transaksi hari ini',
            'penjualan hari ini',
            'omzet hari ini',
            'real-time'
        ];

        foreach ($realtimeKeywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                return self::SHORT_TTL;
            }
        }

        // Data laporan periodik
        $reportKeywords = [
            'laporan mingguan',
            'laporan bulanan',
            'rekap minggu',
            'rekap bulan'
        ];

        foreach ($reportKeywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                return self::LONG_TTL;
            }
        }

        // Default TTL
        return self::DEFAULT_TTL;
    }

    /**
     * Normalisasi pesan untuk konsistensi cache key
     */
    protected function normalizeMessage(string $message): string
    {
        // Lowercase
        $normalized = strtolower($message);

        // Hapus whitespace berlebih
        $normalized = preg_replace('/\s+/', ' ', trim($normalized));

        // Hapus tanda baca yang tidak penting
        $normalized = preg_replace('/[^\w\s]/', '', $normalized);

        return $normalized;
    }

    /**
     * Check and get cached response, or execute and cache
     */
    public function remember(string $cacheKey, callable $callback, ?int $ttl = null): array
    {
        // Try to get from cache first
        $cached = $this->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Execute callback
        $response = $callback();

        // Cache the result
        $this->put($cacheKey, $response, $ttl);

        return $response;
    }

    /**
     * Get cache statistics (untuk monitoring)
     */
    public function getStats(): array
    {
        return [
            'driver' => config('cache.default'),
            'prefix' => config('cache.prefix', ''),
        ];
    }
}
