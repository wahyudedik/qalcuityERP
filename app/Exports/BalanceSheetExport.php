<?php

namespace App\Exports;

use App\Services\FinancialStatementService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BalanceSheetExport implements WithMultipleSheets
{
    public function __construct(
        protected int    $tenantId,
        protected string $asOf,
        protected string $tenantName = 'Qalcuity ERP',
    ) {}

    public function sheets(): array
    {
        $data = app(FinancialStatementService::class)->balanceSheet($this->tenantId, $this->asOf);
        return [new BalanceSheetSheet($data, $this->asOf, $this->tenantName)];
    }
}

class BalanceSheetSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected array  $data,
        protected string $asOf,
        protected string $tenantName,
    ) {}

    public function array(): array
    {
        $fmt = fn($n) => round(abs((float) $n), 2);
        $rows = [];

        // Header
        $rows[] = [$this->tenantName];
        $rows[] = ['NERACA (BALANCE SHEET)'];
        $rows[] = ['Per Tanggal: ' . \Carbon\Carbon::parse($this->asOf)->translatedFormat('d F Y')];
        $rows[] = ['Status: ' . ($this->data['is_balanced'] ? 'BALANCE ✓' : 'TIDAK BALANCE ✗')];
        $rows[] = [];
        $rows[] = ['KODE', 'NAMA AKUN', 'JUMLAH (Rp)'];

        // ASET
        $rows[] = ['', '=== ASET ===', ''];
        $rows[] = ['', 'Aset Lancar', ''];
        foreach ($this->data['assets']['current'] as $acc) {
            $rows[] = [$acc['code'], $acc['name'], $fmt($acc['balance'])];
        }
        $rows[] = ['', 'Total Aset Lancar', $fmt($this->data['assets']['current']->sum('balance'))];
        $rows[] = [];
        $rows[] = ['', 'Aset Tidak Lancar', ''];
        foreach ($this->data['assets']['non_current'] as $acc) {
            $rows[] = [$acc['code'], $acc['name'], $fmt($acc['balance'])];
        }
        $rows[] = ['', 'Total Aset Tidak Lancar', $fmt($this->data['assets']['non_current']->sum('balance'))];
        $rows[] = ['', 'TOTAL ASET', $fmt($this->data['total_assets'])];
        $rows[] = [];

        // KEWAJIBAN
        $rows[] = ['', '=== KEWAJIBAN ===', ''];
        $rows[] = ['', 'Kewajiban Lancar', ''];
        foreach ($this->data['liabilities']['current'] as $acc) {
            $rows[] = [$acc['code'], $acc['name'], $fmt($acc['balance'])];
        }
        $rows[] = ['', 'Total Kewajiban Lancar', $fmt($this->data['liabilities']['current']->sum('balance'))];
        if ($this->data['liabilities']['long_term']->isNotEmpty()) {
            $rows[] = [];
            $rows[] = ['', 'Kewajiban Jangka Panjang', ''];
            foreach ($this->data['liabilities']['long_term'] as $acc) {
                $rows[] = [$acc['code'], $acc['name'], $fmt($acc['balance'])];
            }
            $rows[] = ['', 'Total Kewajiban Jangka Panjang', $fmt($this->data['liabilities']['long_term']->sum('balance'))];
        }
        $rows[] = ['', 'Total Kewajiban', $fmt($this->data['liabilities']['total'])];
        $rows[] = [];

        // EKUITAS
        $rows[] = ['', '=== EKUITAS ===', ''];
        foreach ($this->data['equity']['items'] as $acc) {
            $rows[] = [$acc['code'], $acc['name'], $fmt($acc['balance'])];
        }
        $rows[] = ['', 'Laba/Rugi Tahun Berjalan', $fmt($this->data['net_income'])];
        $rows[] = ['', 'Total Ekuitas', $fmt($this->data['equity']['total'] + $this->data['net_income'])];
        $rows[] = [];
        $rows[] = ['', 'TOTAL KEWAJIBAN & EKUITAS', $fmt($this->data['total_l_e'])];

        return $rows;
    }

    public function title(): string { return 'Neraca'; }

    public function columnWidths(): array
    {
        return ['A' => 12, 'B' => 40, 'C' => 20];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:C1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:C2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A6:C6')->getFont()->setBold(true);
        $sheet->getStyle('A6:C6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E40AF');
        $sheet->getStyle('A6:C6')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('C6:C200')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        return [];
    }
}
