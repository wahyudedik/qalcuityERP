<?php

namespace App\Exports;

use App\Services\FinancialStatementService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomeStatementExport implements FromArray, WithColumnWidths, WithStyles, WithTitle
{
    public function __construct(
        protected int $tenantId,
        protected string $from,
        protected string $to,
        protected string $tenantName = 'Qalcuity ERP',
    ) {}

    public function title(): string
    {
        return 'Laba Rugi';
    }

    public function columnWidths(): array
    {
        return ['A' => 12, 'B' => 40, 'C' => 20];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }

    public function array(): array
    {
        $data = app(FinancialStatementService::class)->incomeStatement($this->tenantId, $this->from, $this->to);
        $fmt = fn ($n) => round((float) $n, 2);
        $rows = [];

        $rows[] = [$this->tenantName];
        $rows[] = ['LAPORAN LABA RUGI (INCOME STATEMENT)'];
        $rows[] = ['Periode: '.Carbon::parse($this->from)->format('d M Y').' s/d '.Carbon::parse($this->to)->format('d M Y')];
        $rows[] = [];
        $rows[] = ['KODE', 'KETERANGAN', 'JUMLAH (Rp)'];

        // Revenue
        $rows[] = ['', '=== PENDAPATAN ===', ''];
        foreach ($data['revenue']['items'] as $acc) {
            $rows[] = [$acc['code'], $acc['name'], $fmt($acc['balance'])];
        }
        $rows[] = ['', 'Total Pendapatan', $fmt($data['revenue']['total'])];
        $rows[] = [];

        // COGS
        $rows[] = ['', '=== HARGA POKOK PENJUALAN ===', ''];
        foreach ($data['cogs']['items'] as $acc) {
            $rows[] = [$acc['code'], $acc['name'], $fmt($acc['balance'])];
        }
        $rows[] = ['', 'Total HPP', $fmt($data['cogs']['total'])];
        $rows[] = ['', 'LABA KOTOR', $fmt($data['gross_profit'])];
        $rows[] = [];

        // Operating Expenses
        $rows[] = ['', '=== BEBAN OPERASIONAL ===', ''];
        foreach ($data['opex']['items'] as $acc) {
            $rows[] = [$acc['code'], $acc['name'], $fmt($acc['balance'])];
        }
        $rows[] = ['', 'Total Beban Operasional', $fmt($data['opex']['total'])];
        $rows[] = ['', 'LABA OPERASIONAL', $fmt($data['operating_income'])];
        $rows[] = [];

        // Other
        if (! empty($data['other_expense']['items']) && count($data['other_expense']['items']) > 0) {
            $rows[] = ['', '=== BEBAN LAIN-LAIN ===', ''];
            foreach ($data['other_expense']['items'] as $acc) {
                $rows[] = [$acc['code'], $acc['name'], $fmt($acc['balance'])];
            }
            $rows[] = ['', 'Total Beban Lain-lain', $fmt($data['other_expense']['total'])];
            $rows[] = [];
        }

        $rows[] = ['', 'LABA / RUGI BERSIH', $fmt($data['net_income'])];

        return $rows;
    }
}
