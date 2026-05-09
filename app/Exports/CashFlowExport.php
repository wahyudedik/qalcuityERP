<?php

namespace App\Exports;

use App\Services\FinancialStatementService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashFlowExport implements WithMultipleSheets
{
    public function __construct(
        protected int $tenantId,
        protected string $from,
        protected string $to,
        protected string $tenantName = 'Qalcuity ERP',
    ) {}

    public function sheets(): array
    {
        $data = app(FinancialStatementService::class)->cashFlowStatement($this->tenantId, $this->from, $this->to);

        return [new CashFlowSheet($data, $this->from, $this->to, $this->tenantName)];
    }
}

class CashFlowSheet implements FromArray, WithColumnWidths, WithStyles, WithTitle
{
    public function __construct(
        protected array $data,
        protected string $from,
        protected string $to,
        protected string $tenantName,
    ) {}

    public function array(): array
    {
        $fmt = fn ($n) => round((float) $n, 2);
        $rows = [];

        $rows[] = [$this->tenantName];
        $rows[] = ['LAPORAN ARUS KAS (CASH FLOW STATEMENT)'];
        $rows[] = ['Metode Tidak Langsung (Indirect Method)'];
        $rows[] = ['Periode: '.Carbon::parse($this->from)->translatedFormat('d F Y').' s/d '.Carbon::parse($this->to)->translatedFormat('d F Y')];
        $rows[] = ['Rekonsiliasi: '.($this->data['reconciled'] ? 'OK ✓' : 'TIDAK OK ✗')];
        $rows[] = [];
        $rows[] = ['KETERANGAN', 'JUMLAH (Rp)'];

        // Saldo awal
        $rows[] = ['Saldo Kas Awal Periode', $fmt($this->data['opening_cash'])];
        $rows[] = [];

        // Operasi
        $rows[] = ['I. ARUS KAS DARI AKTIVITAS OPERASI', ''];
        $rows[] = ['  Laba/Rugi Bersih', $fmt($this->data['operating']['net_income'])];
        foreach ($this->data['operating']['wc_adjustments'] as $item) {
            $rows[] = ['    '.$item['label'], $fmt($item['amount'])];
        }
        $rows[] = ['  Arus Kas Bersih dari Operasi', $fmt($this->data['operating']['total'])];
        $rows[] = [];

        // Investasi
        $rows[] = ['II. ARUS KAS DARI AKTIVITAS INVESTASI', ''];
        if (empty($this->data['investing']['items'])) {
            $rows[] = ['  Tidak ada aktivitas investasi', 0];
        } else {
            foreach ($this->data['investing']['items'] as $item) {
                $rows[] = ['  '.$item['label'], $fmt($item['amount'])];
            }
        }
        $rows[] = ['  Arus Kas Bersih dari Investasi', $fmt($this->data['investing']['total'])];
        $rows[] = [];

        // Pendanaan
        $rows[] = ['III. ARUS KAS DARI AKTIVITAS PENDANAAN', ''];
        if (empty($this->data['financing']['items'])) {
            $rows[] = ['  Tidak ada aktivitas pendanaan', 0];
        } else {
            foreach ($this->data['financing']['items'] as $item) {
                $rows[] = ['  '.$item['label'], $fmt($item['amount'])];
            }
        }
        $rows[] = ['  Arus Kas Bersih dari Pendanaan', $fmt($this->data['financing']['total'])];
        $rows[] = [];

        // Ringkasan
        $rows[] = ['Kenaikan (Penurunan) Kas Bersih', $fmt($this->data['net_change'])];
        $rows[] = ['Saldo Kas Awal Periode', $fmt($this->data['opening_cash'])];
        $rows[] = ['SALDO KAS AKHIR PERIODE', $fmt($this->data['closing_cash'])];

        return $rows;
    }

    public function title(): string
    {
        return 'Arus Kas';
    }

    public function columnWidths(): array
    {
        return ['A' => 50, 'B' => 20];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:B1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:B2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A7:B7')->getFont()->setBold(true);
        $sheet->getStyle('A7:B7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E40AF');
        $sheet->getStyle('A7:B7')->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('B7:B200')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }
}
