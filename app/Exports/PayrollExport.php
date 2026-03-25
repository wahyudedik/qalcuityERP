<?php

namespace App\Exports;

use App\Models\PayrollItem;
use App\Models\PayrollRun;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollExport implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected int    $tenantId,
        protected string $period,
        protected string $tenantName = 'Qalcuity ERP',
    ) {}

    public function title(): string { return 'Payroll ' . $this->period; }

    public function columnWidths(): array
    {
        return ['A' => 25, 'B' => 18, 'C' => 8, 'D' => 8, 'E' => 15, 'F' => 15, 'G' => 12, 'H' => 12, 'I' => 18];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 11]],
            5 => ['font' => ['bold' => true]],
        ];
    }

    public function array(): array
    {
        $run = PayrollRun::where('tenant_id', $this->tenantId)
            ->where('period', $this->period)
            ->first();

        if (!$run) {
            return [[$this->tenantName], ["Tidak ada data payroll untuk periode {$this->period}"]];
        }

        $items = PayrollItem::where('payroll_run_id', $run->id)
            ->with('employee')
            ->orderBy('id')
            ->get();

        $fmt  = fn($n) => round((float) $n, 0);
        $rows = [];

        $rows[] = [$this->tenantName];
        $rows[] = ["LAPORAN PENGGAJIAN — PERIODE {$this->period}"];
        $rows[] = ["Status: " . ucfirst($run->status) . " | Diproses: " . ($run->processed_at?->format('d M Y H:i') ?? '-')];
        $rows[] = [];
        $rows[] = ['Nama Karyawan', 'Gaji Pokok', 'Hadir', 'Absen', 'Tunjangan', 'Lembur', 'BPJS', 'PPh 21', 'Gaji Bersih'];

        foreach ($items as $item) {
            $rows[] = [
                $item->employee?->name ?? '-',
                $fmt($item->base_salary),
                $item->present_days . 'h',
                $item->absent_days . 'h',
                $fmt($item->allowances),
                $fmt($item->overtime_pay),
                $fmt($item->bpjs_employee),
                $fmt($item->tax_pph21),
                $fmt($item->net_salary),
            ];
        }

        $rows[] = [];
        $rows[] = ['TOTAL', $fmt($run->total_gross), '', '', '', '', '', '', $fmt($run->total_net)];

        return $rows;
    }
}
