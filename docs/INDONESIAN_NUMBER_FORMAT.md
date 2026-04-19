# Format Angka Indonesia — Panduan Penggunaan

## Overview

TASK 6.9: Semua angka di aplikasi Qalcuity ERP harus menggunakan format Indonesia:
- **Titik (.)** sebagai pemisah ribuan
- **Koma (,)** sebagai pemisah desimal

## Helper Functions

### 1. `format_number_id($number, $decimals = 0, $showZero = true)`

Format angka dengan format Indonesia.

```php
// Contoh penggunaan
format_number_id(1234567)        // "1.234.567"
format_number_id(1234.56, 2)     // "1.234,56"
format_number_id(0, 0, false)    // ""
```

### 2. `format_currency_id($amount, $showSymbol = true)`

Format mata uang Rupiah.

```php
// Contoh penggunaan
format_currency_id(1234567)      // "Rp 1.234.567"
format_currency_id(1234567, false) // "1.234.567"
```

### 3. `format_percentage_id($number, $decimals = 2)`

Format persentase.

```php
// Contoh penggunaan
format_percentage_id(12.5)       // "12,50%"
format_percentage_id(100, 0)     // "100%"
```

### 4. `abbreviate_number_id($number, $decimals = 1)`

Format angka dengan suffix (Rb, Jt, M).

```php
// Contoh penggunaan
abbreviate_number_id(1500)       // "1,5 Rb"
abbreviate_number_id(2500000)    // "2,5 Jt"
abbreviate_number_id(3500000000) // "3,5 M"
```

## Penggunaan di Blade Templates

### Card Statistik Dashboard

```blade
<div class="stat-card">
    <h3>Total Penjualan</h3>
    <p class="text-2xl font-bold">
        {{ format_currency_id($totalSales) }}
    </p>
</div>

<div class="stat-card">
    <h3>Jumlah Transaksi</h3>
    <p class="text-2xl font-bold">
        {{ format_number_id($transactionCount) }}
    </p>
</div>
```

### Tabel Transaksi

```blade
<table>
    <thead>
        <tr>
            <th>No. Invoice</th>
            <th>Pelanggan</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoices as $invoice)
        <tr>
            <td>{{ $invoice->number }}</td>
            <td>{{ $invoice->customer->name }}</td>
            <td class="text-right">
                {{ format_currency_id($invoice->total) }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

### Laporan Keuangan

```blade
<div class="report-row">
    <span>Pendapatan</span>
    <span class="font-semibold">
        {{ format_currency_id($revenue) }}
    </span>
</div>

<div class="report-row">
    <span>Beban</span>
    <span class="font-semibold">
        {{ format_currency_id($expenses) }}
    </span>
</div>

<div class="report-row border-t-2 font-bold">
    <span>Laba Bersih</span>
    <span>
        {{ format_currency_id($netIncome) }}
    </span>
</div>
```

### Chart.js Data

```blade
<script>
const chartData = {
    labels: @json($labels),
    datasets: [{
        label: 'Penjualan',
        data: @json($salesData),
    }]
};

const chartOptions = {
    plugins: {
        tooltip: {
            callbacks: {
                label: function(context) {
                    // Format tooltip dengan format Indonesia
                    return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                }
            }
        }
    },
    scales: {
        y: {
            ticks: {
                callback: function(value) {
                    // Format axis dengan format Indonesia
                    return 'Rp ' + value.toLocaleString('id-ID');
                }
            }
        }
    }
};
</script>
```

## Penggunaan di Controller

```php
use App\Helpers\NumberHelper;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_sales' => NumberHelper::currency(1234567),
            'total_orders' => NumberHelper::format(456),
            'growth_rate' => NumberHelper::percentage(12.5),
        ];
        
        return view('dashboard', compact('stats'));
    }
}
```

## Parsing Input dari User

Jika user memasukkan angka dengan format Indonesia, gunakan `NumberHelper::parse()`:

```php
$input = "1.234.567,89"; // Format Indonesia dari user
$number = NumberHelper::parse($input); // 1234567.89 (float)
```

## Checklist Implementasi

- [x] Helper functions dibuat di `app/Helpers/NumberHelper.php`
- [x] Global helper functions di `app/helpers.php`
- [x] Autoload helpers di `composer.json`
- [ ] Update semua card statistik dashboard
- [ ] Update semua tabel transaksi
- [ ] Update semua laporan keuangan
- [ ] Update semua chart tooltips
- [ ] Update semua form display (read-only)

## Area yang Perlu Diaudit

1. **Dashboard** — semua card statistik
2. **Accounting** — laporan Neraca, Laba Rugi, Arus Kas
3. **Sales** — tabel invoice, quotation, sales order
4. **Purchasing** — tabel PO, supplier invoice
5. **Inventory** — nilai stok, HPP
6. **Payroll** — slip gaji, komponen gaji
7. **POS** — total transaksi, kembalian
8. **Reports** — semua laporan dengan angka

## Testing

```php
// Test format_number_id
$this->assertEquals('1.234', format_number_id(1234));
$this->assertEquals('1.234,56', format_number_id(1234.56, 2));

// Test format_currency_id
$this->assertEquals('Rp 1.234.567', format_currency_id(1234567));

// Test format_percentage_id
$this->assertEquals('12,50%', format_percentage_id(12.5));

// Test abbreviate_number_id
$this->assertEquals('1,5 Rb', abbreviate_number_id(1500));
$this->assertEquals('2,5 Jt', abbreviate_number_id(2500000));

// Test parse
$this->assertEquals(1234567.89, NumberHelper::parse('1.234.567,89'));
```
