<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Base export class dengan chunking untuk preventing timeout
 *
 * BUG-REP-002 FIX: All large exports should extend this class
 * to enable chunked reading and prevent PHP timeout on 100K+ rows
 */
abstract class ChunkedExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /**
     * Chunk size for reading data
     * Default: 1000 rows per chunk (optimal for memory)
     */
    public function chunkSize(): int
    {
        return config('excel.exports.chunk_size', 1000);
    }

    /**
     * Default styles for all exports
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'DBEAFE'],
                ],
            ],
        ];
    }

    /**
     * Format currency value
     */
    protected function formatCurrency(float|int $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }

    /**
     * Format date
     */
    protected function formatDate($date, string $format = 'd/m/Y'): string
    {
        if (! $date) {
            return '-';
        }

        return is_string($date) ? date($format, strtotime($date)) : $date->format($format);
    }

    /**
     * Format status with uppercase
     */
    protected function formatStatus(string $status): string
    {
        return strtoupper($status);
    }

    /**
     * Safe null coalescing with default
     */
    protected function safe($value, string $default = '-'): string
    {
        return $value ?? $default;
    }
}
