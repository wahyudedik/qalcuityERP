<?php

namespace App\Contracts;

interface CacheableWidgetInterface
{
    /**
     * Membangun cache key unik untuk widget berdasarkan tenant.
     *
     * @param  int  $tenantId  ID tenant
     * @return string Cache key
     */
    public function getCacheKey(int $tenantId): string;

    /**
     * Mengambil TTL (Time To Live) cache dalam detik.
     *
     * @return int TTL dalam detik
     */
    public function getCacheTtl(): int;

    /**
     * Menentukan apakah widget ini harus di-cache.
     *
     * @return bool True jika widget harus di-cache
     */
    public function shouldCache(): bool;
}
