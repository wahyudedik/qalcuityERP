<?php

namespace App\Exports;

use App\Models\SalesOrder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected int    $tenantId,
        protected string $startDate,
        protected string $endDate,
    ) {}

    public function query()
    {
        return SalesOrder::with(['customer', 'user'])
            ->where('tenant_id', $this->tenantId)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->orderBy('date');
    }

    public function headings(): array
    {
        return ['No. Order', 'Tanggal', 'Pelanggan', 'Sales', 'Status', 'Subtotal', 'Diskon', 'Pajak', 'Total'];
    }

    public function map($row): array
    {
        return [
            $row->number,
            $row->date->format('d/m/Y'),
            $row->customer?->name ?? '(Walk-in)',
            $row->user->name,
            strtoupper($row->status),
            $row->subtotal,
            $row->discount,
            $row->tax,
            $row->total,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DBEAFE']]],
        ];
    }

    public function title(): string { return 'Laporan Penjualan'; }
}
