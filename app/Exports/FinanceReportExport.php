<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinanceReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected int $tenantId,
        protected string $startDate,
        protected string $endDate,
    ) {}

    public function query()
    {
        return Transaction::with(['category', 'user'])
            ->where('tenant_id', $this->tenantId)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->orderBy('date');
    }

    public function headings(): array
    {
        return ['No. Transaksi', 'Tanggal', 'Tipe', 'Kategori', 'Keterangan', 'Metode', 'Nominal'];
    }

    public function map($row): array
    {
        return [
            $row->number,
            $row->date->format('d/m/Y'),
            strtoupper($row->type),
            $row->category?->name ?? '-',
            $row->description,
            $row->payment_method ?? '-',
            $row->amount,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D1FAE5']]],
        ];
    }

    public function title(): string
    {
        return 'Laporan Keuangan';
    }
}
