<?php

namespace App\Contracts;

interface WidgetInterface
{
    /**
     * Mengambil data yang akan ditampilkan oleh widget.
     *
     * @return array Data widget
     */
    public function getData(): array;

    /**
     * Mengambil tipe widget.
     *
     * @return string Tipe widget (contoh: 'summary', 'chart-trends')
     */
    public function getType(): string;

    /**
     * Mengambil nama halaman tempat widget berada.
     *
     * @return string Nama halaman (contoh: 'notifications', 'reports')
     */
    public function getPage(): string;

    /**
     * Mengambil konfigurasi widget.
     *
     * @return array Konfigurasi widget
     */
    public function getConfig(): array;

    /**
     * Mengonversi widget ke array untuk serialisasi atau response.
     *
     * @return array Representasi array dari widget
     */
    public function toArray(): array;
}
