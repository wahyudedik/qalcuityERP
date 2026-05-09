<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AgingReportExport implements FromArray, WithColumnWidths, WithStyles, WithTitle
{
    public function __construct(
        protected int $tenantId,
        protected string $tenantName = 'Qalcuity ERP',
    ) {}

    public function title(): string
    {
        return 'AR Aging';
    }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 16, 'C' => 16, 'D' => 16, 'E' => 16, 'F' => 16, 'G' => 18];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 11]],
            4 => ['font' => ['bold' => true]],
        ];
    }

    public function array(): array
    {
        $invoices = Invoice::with('customer')
            ->where('tenant_id', $this->tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->get();

        // Build aging buckets per customer
        $aging = [];
        foreach ($invoices as $inv) {
            $cid = $inv->customer_id;
            $name = $inv->customer?->name ?? 'Unknown';
            $days = max(0, (int) now()->startOfDay()->diffInDays($inv->due_date->startOfDay(), false) * -1);

            $bucket = match (true) {
                $days <= 0 => 'current',
                $days <= 30 => '1-30',
                $days <= 60 => '31-60',
                $days <= 90 => '61-90',
                default => '90+',
            };

            if (! isset($aging[$cid])) {
                $aging[$cid] = ['customer' => $name, 'current' => 0, '1-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0, 'total' => 0];
            }

            $aging[$cid][$bucket] += (float) $inv->remaining_amount;
            $aging[$cid]['total'] += (float) $inv->remaining_amount;
        }

        usort($aging, fn ($a, $b) => $b['total'] <=> $a['total']);

        $fmt = fn ($n) => $n > 0 ? round($n, 0) : '';
        $rows = [];

        $rows[] = [$this->tenantName];
        $rows[] = ['LAPORAN AGING PIUTANG (AR AGING)'];
        $rows[] = ['Per Tanggal: '.now()->format('d M Y')];
        $rows[] = ['Customer', 'Belum Jatuh Tempo', '1-30 Hari', '31-60 Hari', '61-90 Hari', '> 90 Hari', 'Total'];

        $totals = ['current' => 0, '1-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0, 'total' => 0];

        foreach ($aging as $row) {
            $rows[] = [
                $row['customer'],
                $fmt($row['current']),
                $fmt($row['1-30']),
                $fmt($row['31-60']),
                $fmt($row['61-90']),
                $fmt($row['90+']),
                round($row['total'], 0),
            ];
            foreach (['current', '1-30', '31-60', '61-90', '90+', 'total'] as $k) {
                $totals[$k] += $row[$k];
            }
        }

        $rows[] = [];
        $rows[] = ['TOTAL', round($totals['current']), round($totals['1-30']), round($totals['31-60']), round($totals['61-90']), round($totals['90+']), round($totals['total'])];

        return $rows;
    }
}
