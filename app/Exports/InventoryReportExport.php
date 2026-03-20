<?php

namespace App\Exports;

use App\Models\ProductStock;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(protected int $tenantId) {}

    public function query()
    {
        return ProductStock::with(['product', 'warehouse'])
            ->whereHas('product', fn($q) => $q->where('tenant_id', $this->tenantId)->where('is_active', true))
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->join('warehouses', 'product_stocks.warehouse_id', '=', 'warehouses.id')
            ->select('product_stocks.*')
            ->orderBy('products.name');
    }

    public function headings(): array
    {
        return ['Produk', 'SKU', 'Kategori', 'Satuan', 'Gudang', 'Stok', 'Stok Min', 'Status', 'Harga Beli', 'Harga Jual'];
    }

    public function map($row): array
    {
        $status = $row->quantity <= $row->product->stock_min ? 'MENIPIS' : 'AMAN';
        return [
            $row->product->name,
            $row->product->sku ?? '-',
            $row->product->category ?? '-',
            $row->product->unit,
            $row->warehouse->name,
            $row->quantity,
            $row->product->stock_min,
            $status,
            $row->product->price_buy,
            $row->product->price_sell,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FEF3C7']]],
        ];
    }

    public function title(): string { return 'Laporan Inventori'; }
}
