<?php

namespace App\Widgets;

use App\Contracts\WidgetInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Base class untuk semua widget dashboard.
 *
 * Menyediakan fungsionalitas umum seperti:
 * - Manajemen konfigurasi widget
 * - Validasi permissions
 * - Serialisasi data
 * - Error handling
 */
abstract class AbstractWidget implements WidgetInterface
{
    /**
     * Konfigurasi widget.
     */
    protected array $config;

    /**
     * Nama halaman tempat widget berada.
     */
    protected string $page;

    /**
     * User yang mengakses widget.
     *
     * @var User|null
     */
    protected $user;

    /**
     * Constructor.
     *
     * @param  string  $page  Nama halaman
     * @param  array  $config  Konfigurasi widget
     */
    public function __construct(string $page, array $config = [])
    {
        $this->page = $page;
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->user = Auth::user();
    }

    /**
     * Mengambil data yang akan ditampilkan oleh widget.
     * Method ini harus diimplementasikan oleh child class.
     *
     * @return array Data widget
     */
    abstract public function getData(): array;

    /**
     * Mengambil tipe widget.
     * Method ini harus diimplementasikan oleh child class.
     *
     * @return string Tipe widget
     */
    abstract public function getType(): string;

    /**
     * Mengambil konfigurasi default untuk widget.
     * Child class dapat override method ini untuk menyediakan default config.
     *
     * @return array Konfigurasi default
     */
    protected function getDefaultConfig(): array
    {
        return [
            'title' => '',
            'description' => '',
            'icon' => null,
            'color' => 'blue',
            'refreshInterval' => null, // dalam detik, null = tidak auto-refresh
            'collapsible' => false,
            'defaultCollapsed' => false,
        ];
    }

    /**
     * Mengambil nama halaman tempat widget berada.
     *
     * @return string Nama halaman
     */
    public function getPage(): string
    {
        return $this->page;
    }

    /**
     * Mengambil konfigurasi widget.
     *
     * @return array Konfigurasi widget
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Mengambil nilai konfigurasi tertentu.
     *
     * @param  string  $key  Key konfigurasi
     * @param  mixed  $default  Nilai default jika key tidak ada
     * @return mixed Nilai konfigurasi
     */
    public function getConfigValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Set nilai konfigurasi.
     *
     * @param  string  $key  Key konfigurasi
     * @param  mixed  $value  Nilai konfigurasi
     */
    public function setConfigValue(string $key, $value): self
    {
        $this->config[$key] = $value;

        return $this;
    }

    /**
     * Validasi apakah user memiliki permission untuk mengakses widget.
     * Child class dapat override method ini untuk custom permission logic.
     *
     * @return bool True jika user memiliki permission
     */
    public function hasPermission(): bool
    {
        // Default: semua user yang authenticated dapat akses
        return $this->user !== null;
    }

    /**
     * Mengambil data widget dengan error handling.
     * Jika terjadi error, return empty state.
     *
     * @return array Data widget atau empty state
     */
    public function getDataSafely(): array
    {
        try {
            if (! $this->hasPermission()) {
                return $this->getPermissionDeniedState();
            }

            return $this->getData();
        } catch (\Exception $e) {
            \Log::error("Widget error: {$this->getType()}", [
                'page' => $this->page,
                'error' => $e->getMessage(),
                'user_id' => $this->user?->id,
                'tenant_id' => $this->user?->tenant_id,
            ]);

            return $this->getErrorState($e);
        }
    }

    /**
     * Mengambil state untuk permission denied.
     *
     * @return array Permission denied state
     */
    protected function getPermissionDeniedState(): array
    {
        return [
            'error' => true,
            'message' => 'Anda tidak memiliki akses ke widget ini.',
            'type' => 'permission_denied',
        ];
    }

    /**
     * Mengambil state untuk error.
     *
     * @param  \Exception  $e  Exception yang terjadi
     * @return array Error state
     */
    protected function getErrorState(\Exception $e): array
    {
        return [
            'error' => true,
            'message' => 'Widget gagal dimuat. Silakan coba lagi.',
            'type' => 'error',
            'debug' => config('app.debug') ? $e->getMessage() : null,
        ];
    }

    /**
     * Mengambil state untuk empty data.
     *
     * @param  string|null  $message  Custom message
     * @return array Empty state
     */
    protected function getEmptyState(?string $message = null): array
    {
        return [
            'empty' => true,
            'message' => $message ?? 'Tidak ada data untuk ditampilkan.',
        ];
    }

    /**
     * Format angka dengan suffix (K, M, B).
     *
     * @param  float|int  $number  Angka yang akan diformat
     * @param  int  $decimals  Jumlah desimal
     * @return string Angka yang sudah diformat
     */
    protected function formatNumber($number, int $decimals = 1): string
    {
        if ($number >= 1000000000) {
            return number_format($number / 1000000000, $decimals).'B';
        } elseif ($number >= 1000000) {
            return number_format($number / 1000000, $decimals).'M';
        } elseif ($number >= 1000) {
            return number_format($number / 1000, $decimals).'K';
        }

        return number_format($number, $decimals);
    }

    /**
     * Format persentase dengan tanda + atau -.
     *
     * @param  float  $percentage  Persentase
     * @param  int  $decimals  Jumlah desimal
     * @return string Persentase yang sudah diformat
     */
    protected function formatPercentage(float $percentage, int $decimals = 1): string
    {
        $sign = $percentage >= 0 ? '+' : '';

        return $sign.number_format($percentage, $decimals).'%';
    }

    /**
     * Mengambil trend indicator (up/down/neutral).
     *
     * @param  float  $value  Nilai untuk menentukan trend
     * @return string Trend indicator ('up', 'down', 'neutral')
     */
    protected function getTrendIndicator(float $value): string
    {
        if ($value > 0) {
            return 'up';
        } elseif ($value < 0) {
            return 'down';
        }

        return 'neutral';
    }

    /**
     * Mengambil color class berdasarkan trend.
     *
     * @param  float  $value  Nilai untuk menentukan trend
     * @param  bool  $inverse  Inverse color (merah untuk positif, hijau untuk negatif)
     * @return string Color class
     */
    protected function getTrendColor(float $value, bool $inverse = false): string
    {
        if ($value > 0) {
            return $inverse ? 'red' : 'green';
        } elseif ($value < 0) {
            return $inverse ? 'green' : 'red';
        }

        return 'gray';
    }

    /**
     * Mengonversi widget ke array untuk serialisasi atau response.
     *
     * @return array Representasi array dari widget
     */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'page' => $this->getPage(),
            'config' => $this->getConfig(),
            'data' => $this->getDataSafely(),
        ];
    }

    /**
     * Mengonversi widget ke JSON.
     *
     * @param  int  $options  JSON encode options
     * @return string JSON representation
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Magic method untuk JSON serialization.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
