<?php

namespace App\Services\Widget;

use App\Contracts\CacheableWidgetInterface;
use App\Contracts\WidgetInterface;

/**
 * AbstractWidget — Base class untuk semua widget di Qalcuity ERP.
 *
 * Menyediakan implementasi default untuk fungsionalitas umum widget,
 * termasuk cache key generation, konfigurasi, dan serialisasi.
 * Concrete widget harus mengimplementasikan method `getData()`.
 */
abstract class AbstractWidget implements WidgetInterface, CacheableWidgetInterface
{
    /**
     * TTL cache default: 5 menit (300 detik).
     */
    protected const DEFAULT_CACHE_TTL = 300;

    /**
     * @param  string  $widgetType  Tipe widget (contoh: 'summary', 'chart-trends')
     * @param  string  $page        Nama halaman (contoh: 'notifications', 'reports')
     * @param  array   $config      Konfigurasi widget
     */
    public function __construct(
        protected string $widgetType,
        protected string $page,
        protected array $config = [],
    ) {}

    /**
     * Mengambil data widget. Harus diimplementasikan oleh concrete widget.
     *
     * @return array Data widget
     */
    abstract public function getData(): array;

    /**
     * Mengambil tipe widget.
     */
    public function getType(): string
    {
        return $this->widgetType;
    }

    /**
     * Mengambil nama halaman tempat widget berada.
     */
    public function getPage(): string
    {
        return $this->page;
    }

    /**
     * Mengambil konfigurasi widget.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Membangun cache key unik berdasarkan tenant, halaman, dan tipe widget.
     *
     * Format: "widget.{tenantId}.{page}.{widgetType}"
     *
     * @param  int  $tenantId  ID tenant
     * @return string Cache key
     */
    public function getCacheKey(int $tenantId): string
    {
        return "widget.{$tenantId}.{$this->page}.{$this->widgetType}";
    }

    /**
     * Mengambil TTL cache dalam detik.
     * Override method ini di concrete widget untuk TTL kustom.
     *
     * @return int TTL dalam detik (default: 300 = 5 menit)
     */
    public function getCacheTtl(): int
    {
        return self::DEFAULT_CACHE_TTL;
    }

    /**
     * Menentukan apakah widget ini harus di-cache.
     * Default: true. Override untuk menonaktifkan caching pada widget tertentu.
     *
     * @return bool True jika widget harus di-cache
     */
    public function shouldCache(): bool
    {
        return true;
    }

    /**
     * Mengonversi widget ke array untuk serialisasi atau API response.
     *
     * @return array Representasi array dari widget beserta datanya
     */
    public function toArray(): array
    {
        return [
            'type'   => $this->getType(),
            'page'   => $this->getPage(),
            'config' => $this->getConfig(),
            'data'   => $this->getData(),
        ];
    }
}
