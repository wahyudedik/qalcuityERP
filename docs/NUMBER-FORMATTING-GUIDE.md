# Indonesian Number Formatting Guide

## Overview

Qalcuity ERP uses Indonesian number formatting throughout the application:
- **Thousand separator**: Titik (`.`)
- **Decimal separator**: Koma (`,`)
- **Currency**: Rupiah (Rp)

Example: `Rp 1.234.567,89`

---

## Quick Reference

| Directive | Input | Output | Use Case |
|-----------|-------|--------|----------|
| `@idr($amount)` | `1234567` | `Rp 1.234.567` | Currency (Rupiah) |
| `@number($value)` | `1234567` | `1.234.567` | Quantities, counts |
| `@decimal($value, 2)` | `1234.56` | `1.234,56` | Prices, measurements |
| `@percent($value)` | `12.34` | `12,34%` | Percentages |
| `@abbr($value)` | `1234567` | `1,2 Jt` | Large numbers (dashboard) |

---

## Blade Directives

### @idr — Currency (Rupiah)

Format numbers as Indonesian Rupiah currency.

```blade
{{-- Basic usage --}}
<p>Total: @idr($invoice->total)</p>
{{-- Output: Total: Rp 1.234.567 --}}

{{-- In tables --}}
<td>@idr($payment->amount)</td>

{{-- In cards --}}
<div class="text-2xl font-bold">
    @idr($revenue)
</div>
```

**Parameters**:
- `$amount` (float|int|string): The amount to format

**Output**: `Rp 1.234.567` (no decimals for currency)

---

### @number — Integer/Quantity

Format numbers with thousand separators (no decimals).

```blade
{{-- Quantities --}}
<p>Stock: @number($product->stock) unit</p>
{{-- Output: Stock: 1.234 unit --}}

{{-- Counts --}}
<p>Total Customers: @number($customerCount)</p>
{{-- Output: Total Customers: 1.234 --}}

{{-- In badges --}}
<span class="badge">@number($unreadCount)</span>
```

**Parameters**:
- `$value` (float|int|string): The number to format

**Output**: `1.234.567` (no decimals)

---

### @decimal — Decimal Numbers

Format numbers with decimal places (comma as decimal separator).

```blade
{{-- Prices with decimals --}}
<p>Price per unit: @decimal($price, 2)</p>
{{-- Output: Price per unit: 1.234,56 --}}

{{-- Measurements --}}
<p>Weight: @decimal($weight, 3) kg</p>
{{-- Output: Weight: 12,345 kg --}}

{{-- Percentages with custom decimals --}}
<p>Rate: @decimal($rate, 4)%</p>
```

**Parameters**:
- `$value` (float|int|string): The number to format
- `$decimals` (int): Number of decimal places (default: 0)

**Output**: `1.234,56` (with comma as decimal separator)

---

### @percent — Percentage

Format numbers as percentages with Indonesian formatting.

```blade
{{-- Growth rate --}}
<p>Growth: @percent($growthRate)</p>
{{-- Output: Growth: 12,34% --}}

{{-- Discount --}}
<span class="text-red-500">Diskon @percent($discount)</span>
{{-- Output: Diskon 15,00% --}}

{{-- Achievement --}}
<div>Target Achievement: @percent($achievement)</div>
```

**Parameters**:
- `$value` (float|int|string): The percentage value (e.g., 12.34 for 12.34%)
- `$decimals` (int): Number of decimal places (default: 2)

**Output**: `12,34%`

---

### @abbr — Abbreviated Numbers

Format large numbers with Indonesian suffixes (Rb, Jt, M).

```blade
{{-- Dashboard stats --}}
<div class="stat-card">
    <p class="text-3xl">@abbr($totalRevenue)</p>
    <p class="text-sm">Total Revenue</p>
</div>
{{-- Output: 1,2 Jt --}}

{{-- Large quantities --}}
<p>Total Transactions: @abbr($transactionCount)</p>
{{-- Output: Total Transactions: 15,3 Rb --}}
```

**Parameters**:
- `$value` (float|int|string): The number to abbreviate
- `$decimals` (int): Number of decimal places (default: 1)

**Output**:
- `< 1.000`: `123`
- `≥ 1.000`: `1,2 Rb` (Ribu)
- `≥ 1.000.000`: `1,2 Jt` (Juta)
- `≥ 1.000.000.000`: `1,2 M` (Miliar)

---

## PHP Helper Methods

For use in controllers, services, and other PHP code:

```php
use App\Helpers\NumberHelper;

// Format as currency
$formatted = NumberHelper::currency(1234567);
// Output: "Rp 1.234.567"

// Format as currency without symbol
$formatted = NumberHelper::currency(1234567, false);
// Output: "1.234.567"

// Format with thousand separator
$formatted = NumberHelper::format(1234567);
// Output: "1.234.567"

// Format with decimals
$formatted = NumberHelper::format(1234.56, 2);
// Output: "1.234,56"

// Format as percentage
$formatted = NumberHelper::percentage(12.34);
// Output: "12,34%"

// Abbreviate large numbers
$formatted = NumberHelper::abbreviate(1234567);
// Output: "1,2 Jt"

// Parse Indonesian format back to float
$float = NumberHelper::parse("Rp 1.234.567,89");
// Output: 1234567.89
```

---

## Migration Guide

### Before (Inconsistent)

```blade
{{-- Different formats in different files --}}
Rp {{ number_format($amount, 0, ',', '.') }}
{{ number_format($quantity, 0, '.', ',') }}  {{-- Wrong! --}}
Rp. {{ number_format($price, 2, ',', '.') }}
{{ $count }}  {{-- No formatting --}}
```

### After (Consistent)

```blade
{{-- Consistent Indonesian formatting --}}
@idr($amount)
@number($quantity)
@decimal($price, 2)
@number($count)
```

---

## Common Use Cases

### Dashboard Statistics Cards

```blade
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    {{-- Revenue --}}
    <div class="stat-card">
        <p class="text-sm text-gray-500">Total Revenue</p>
        <p class="text-2xl font-bold">@idr($totalRevenue)</p>
        <p class="text-xs text-green-500">+@percent($revenueGrowth) vs last month</p>
    </div>
    
    {{-- Orders --}}
    <div class="stat-card">
        <p class="text-sm text-gray-500">Total Orders</p>
        <p class="text-2xl font-bold">@number($totalOrders)</p>
    </div>
    
    {{-- Average Order Value --}}
    <div class="stat-card">
        <p class="text-sm text-gray-500">Avg Order Value</p>
        <p class="text-2xl font-bold">@idr($avgOrderValue)</p>
    </div>
    
    {{-- Conversion Rate --}}
    <div class="stat-card">
        <p class="text-sm text-gray-500">Conversion Rate</p>
        <p class="text-2xl font-bold">@percent($conversionRate)</p>
    </div>
</div>
```

### Invoice Table

```blade
<table>
    <thead>
        <tr>
            <th>Invoice #</th>
            <th>Customer</th>
            <th class="text-right">Amount</th>
            <th class="text-right">Paid</th>
            <th class="text-right">Balance</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoices as $invoice)
        <tr>
            <td>{{ $invoice->number }}</td>
            <td>{{ $invoice->customer->name }}</td>
            <td class="text-right">@idr($invoice->total)</td>
            <td class="text-right">@idr($invoice->paid_amount)</td>
            <td class="text-right">@idr($invoice->balance)</td>
        </tr>
        @endforeach
    </tbody>
</table>
```

### Product Card

```blade
<div class="product-card">
    <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
    <h3>{{ $product->name }}</h3>
    <p class="text-2xl font-bold">@idr($product->price)</p>
    <p class="text-sm text-gray-500">Stock: @number($product->stock) unit</p>
    @if($product->discount > 0)
        <span class="badge badge-red">Diskon @percent($product->discount)</span>
    @endif
</div>
```

### Financial Report

```blade
<div class="report">
    <h2>Laporan Laba Rugi</h2>
    <table>
        <tr>
            <td>Pendapatan</td>
            <td class="text-right">@idr($revenue)</td>
        </tr>
        <tr>
            <td>Beban Pokok Penjualan</td>
            <td class="text-right">(@idr($cogs))</td>
        </tr>
        <tr class="font-bold">
            <td>Laba Kotor</td>
            <td class="text-right">@idr($grossProfit)</td>
        </tr>
        <tr>
            <td>Beban Operasional</td>
            <td class="text-right">(@idr($operatingExpenses))</td>
        </tr>
        <tr class="font-bold border-t-2">
            <td>Laba Bersih</td>
            <td class="text-right">@idr($netProfit)</td>
        </tr>
        <tr>
            <td>Margin Laba</td>
            <td class="text-right">@percent($profitMargin)</td>
        </tr>
    </table>
</div>
```

---

## Best Practices

### 1. Always Format User-Facing Numbers

❌ **Don't**:
```blade
<p>Total: {{ $amount }}</p>
{{-- Output: Total: 1234567 (hard to read) --}}
```

✅ **Do**:
```blade
<p>Total: @idr($amount)</p>
{{-- Output: Total: Rp 1.234.567 (easy to read) --}}
```

### 2. Use Appropriate Directive for Context

❌ **Don't**:
```blade
<p>Stock: @idr($stock)</p>  {{-- Wrong! Stock is not currency --}}
```

✅ **Do**:
```blade
<p>Stock: @number($stock) unit</p>
```

### 3. Align Numbers in Tables

```blade
<td class="text-right">@idr($amount)</td>  {{-- Right-align for better readability --}}
```

### 4. Use Abbreviations for Dashboard

```blade
{{-- Dashboard: Use abbreviated format for large numbers --}}
<div class="stat-card">
    <p class="text-3xl">@abbr($totalRevenue)</p>
    <p class="text-xs">Total Revenue</p>
</div>

{{-- Detail page: Use full format --}}
<div class="detail">
    <p>Total Revenue: @idr($totalRevenue)</p>
</div>
```

### 5. Consistent Decimal Places

```blade
{{-- Prices: Always 2 decimals --}}
@decimal($price, 2)

{{-- Percentages: Always 2 decimals --}}
@percent($rate, 2)

{{-- Quantities: No decimals --}}
@number($quantity)
```

---

## Testing

### Manual Testing Checklist

- [ ] Currency displays as `Rp 1.234.567`
- [ ] Quantities display as `1.234`
- [ ] Decimals display as `1.234,56`
- [ ] Percentages display as `12,34%`
- [ ] Abbreviations display as `1,2 Jt`
- [ ] Numbers align properly in tables
- [ ] Format is consistent across all pages
- [ ] Dark mode doesn't affect number readability

### Unit Test Example

```php
use App\Helpers\NumberHelper;
use Tests\TestCase;

class NumberHelperTest extends TestCase
{
    public function test_format_currency()
    {
        $this->assertEquals('Rp 1.234.567', NumberHelper::currency(1234567));
    }
    
    public function test_format_number()
    {
        $this->assertEquals('1.234.567', NumberHelper::format(1234567));
    }
    
    public function test_format_decimal()
    {
        $this->assertEquals('1.234,56', NumberHelper::format(1234.56, 2));
    }
    
    public function test_format_percentage()
    {
        $this->assertEquals('12,34%', NumberHelper::percentage(12.34));
    }
    
    public function test_abbreviate()
    {
        $this->assertEquals('1,2 Jt', NumberHelper::abbreviate(1234567));
    }
    
    public function test_parse()
    {
        $this->assertEquals(1234567.89, NumberHelper::parse('Rp 1.234.567,89'));
    }
}
```

---

## Troubleshooting

### Issue: Numbers not formatting

**Cause**: Blade directives not registered

**Solution**: Clear config cache
```bash
php artisan config:clear
php artisan view:clear
```

### Issue: Wrong decimal separator

**Cause**: Using wrong directive or parameters

**Solution**: Use `@decimal($value, 2)` for decimals, not `@number($value)`

### Issue: Currency symbol missing

**Cause**: Using `@number()` instead of `@idr()`

**Solution**: Use `@idr($amount)` for currency

---

## FAQ

**Q: Can I use these directives in JavaScript?**  
A: No, these are Blade directives (server-side). For JavaScript, use the `formatNumber()` function in `resources/js/utils/number-format.js`.

**Q: How do I format numbers in exports (Excel/PDF)?**  
A: Use `NumberHelper::format()` in your export classes.

**Q: Can I change the currency symbol?**  
A: Yes, modify `NumberHelper::currency()` method to accept a currency parameter.

**Q: What about other currencies (USD, EUR)?**  
A: Use `NumberHelper::format()` and prepend the currency symbol manually, or extend the helper.

**Q: How do I handle null values?**  
A: The helper handles null gracefully and returns `'0'` by default. You can also use `@idr($amount ?? 0)`.

---

## Related Documentation

- [NumberHelper Class](../app/Helpers/NumberHelper.php)
- [AppServiceProvider](../app/Providers/AppServiceProvider.php) (Blade directive registration)
- [Task 6 UI/UX Audit Summary](../.kiro/specs/erp-comprehensive-audit-fix/TASK-6-UI-UX-AUDIT-SUMMARY.md)

---

**Last Updated**: 2025-01-XX  
**Maintained by**: Qalcuity Development Team
