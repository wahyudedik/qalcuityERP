<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HrmReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected int $tenantId,
        protected string $startDate,
        protected string $endDate,
    ) {}

    public function query()
    {
        return Attendance::with(['employee'])
            ->whereHas('employee', fn ($q) => $q->where('tenant_id', $this->tenantId))
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->orderBy('date');
    }

    public function headings(): array
    {
        return ['Tanggal', 'Karyawan', 'Posisi', 'Status', 'Check In', 'Check Out', 'Catatan'];
    }

    public function map($row): array
    {
        return [
            $row->date->format('d/m/Y'),
            $row->employee->name,
            $row->employee->position ?? '-',
            strtoupper($row->status),
            $row->check_in ?? '-',
            $row->check_out ?? '-',
            $row->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EDE9FE']]],
        ];
    }

    public function title(): string
    {
        return 'Laporan Kehadiran';
    }
}
