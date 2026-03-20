<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReceivablesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected int    $tenantId,
        protected string $startDate,
        protected string $endDate,
    ) {}

    public function collection()
    {
        return Invoice::with(['customer'])
            ->where('tenant_id', $this->tenantId)
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->orderBy('due_date')
            ->get();
    }

    public function headings(): array
    {
        return ['No. Invoice', 'Customer', 'Jumlah', 'Terbayar', 'Sisa', 'Jatuh Tempo', 'Status', 'Hari Terlambat'];
    }

    public function map($row): array
    {
        $overdue = $row->status !== 'paid' && $row->due_date < now() ? $row->due_date->diffInDays(now()) : 0;
        return [
            $row->number,
            $row->customer?->name ?? '-',
            $row->total_amount,
            $row->paid_amount,
            $row->remaining_amount,
            $row->due_date?->format('d/m/Y') ?? '-',
            strtoupper($row->status),
            $overdue,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FEE2E2']]],
        ];
    }

    public function title(): string { return 'Laporan Piutang'; }
}
