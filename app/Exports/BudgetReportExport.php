<?php

namespace App\Exports;

use App\Models\Budget;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class BudgetReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected int $rowCount = 0;

    public function __construct(
        protected int    $tenantId,
        protected string $period,
    ) {}

    public function query()
    {
        return Budget::where('tenant_id', $this->tenantId)
            ->where('period', $this->period)
            ->where('status', 'active')
            ->orderBy('department')
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Nama Anggaran',
            'Departemen',
            'Kategori',
            'Anggaran (Rp)',
            'Realisasi (Rp)',
            'Selisih (Rp)',
            'Penggunaan (%)',
            'Status',
        ];
    }

    public function map($row): array
    {
        $this->rowCount++;
        $variance = $row->amount - $row->realized;
        $pct      = $row->amount > 0 ? round($row->realized / $row->amount * 100, 1) : 0;
        $status   = match(true) {
            $row->realized > $row->amount => 'OVER BUDGET',
            $pct >= 90                   => 'HAMPIR HABIS',
            default                      => 'NORMAL',
        };

        return [
            $row->name,
            $row->department ?? '-',
            $row->category ?? '-',
            round($row->amount, 2),
            round($row->realized, 2),
            round($variance, 2),
            $pct . '%',
            $status,
        ];
    }

    public function title(): string { return 'Budget vs Aktual'; }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 18, 'C' => 18, 'D' => 18, 'E' => 18, 'F' => 18, 'G' => 14, 'H' => 16];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E40AF');
        $sheet->getStyle('A1:H1')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('D1:F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Color-code status column
                for ($i = 2; $i <= $this->rowCount + 1; $i++) {
                    $status = $sheet->getCell("H{$i}")->getValue();
                    $color  = match($status) {
                        'OVER BUDGET'  => 'FEE2E2', // red
                        'HAMPIR HABIS' => 'FEF3C7', // yellow
                        default        => 'D1FAE5', // green
                    };
                    $sheet->getStyle("H{$i}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($color);
                }
                // Right-align numeric columns
                $sheet->getStyle("D2:F{$this->rowCount}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
