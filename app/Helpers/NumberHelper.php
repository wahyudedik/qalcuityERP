<?php

namespace App\Helpers;

/**
 * NumberHelper — Format angka Indonesia (titik ribuan, koma desimal)
 * 
 * Digunakan di seluruh aplikasi untuk konsistensi format angka:
 * - Card statistik dashboard
 * - Tabel transaksi
 * - Laporan keuangan
 * - Form input (display only)
 */
class NumberHelper
{
    /**
     * Format angka dengan format Indonesia
     * 
     * @param float|int|string|null $number
     * @param int $decimals Jumlah desimal (default 0)
     * @param bool $showZero Tampilkan 0 atau string kosong
     * @return string
     */
    public static function format($number, int $decimals = 0, bool $showZero = true): string
    {
        if ($number === null || $number === '') {
            return $showZero ? '0' : '';
        }

        $number = is_string($number) ? (float) $number : $number;

        // Format: titik sebagai pemisah ribuan, koma sebagai desimal
        return number_format($number, $decimals, ',', '.');
    }

    /**
     * Format mata uang Rupiah
     * 
     * @param float|int|string|null $amount
     * @param bool $showSymbol Tampilkan simbol Rp
     * @return string
     */
    public static function currency($amount, bool $showSymbol = true): string
    {
        $formatted = self::format($amount, 0);
        
        return $showSymbol ? "Rp {$formatted}" : $formatted;
    }

    /**
     * Format persentase
     * 
     * @param float|int|string|null $number
     * @param int $decimals
     * @return string
     */
    public static function percentage($number, int $decimals = 2): string
    {
        return self::format($number, $decimals) . '%';
    }

    /**
     * Format angka dengan suffix (K, M, B)
     * 
     * @param float|int|string|null $number
     * @param int $decimals
     * @return string
     */
    public static function abbreviate($number, int $decimals = 1): string
    {
        if ($number === null || $number === '') {
            return '0';
        }

        $number = is_string($number) ? (float) $number : $number;

        if ($number >= 1000000000) {
            return self::format($number / 1000000000, $decimals) . ' M'; // Miliar
        }

        if ($number >= 1000000) {
            return self::format($number / 1000000, $decimals) . ' Jt'; // Juta
        }

        if ($number >= 1000) {
            return self::format($number / 1000, $decimals) . ' Rb'; // Ribu
        }

        return self::format($number, 0);
    }

    /**
     * Parse angka format Indonesia ke float
     * 
     * @param string $formatted
     * @return float
     */
    public static function parse(string $formatted): float
    {
        // Hapus Rp, spasi, dan titik ribuan
        $cleaned = str_replace(['Rp', ' ', '.'], '', $formatted);
        
        // Ganti koma desimal dengan titik
        $cleaned = str_replace(',', '.', $cleaned);
        
        return (float) $cleaned;
    }
}
