# Number Formatting Migration Example

This document shows a real example of migrating from direct `number_format()` calls to the new Blade directives.

---

## File: `resources/views/affiliate/dashboard.blade.php`

### ❌ Before (Old Way)

```blade
{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Referral</p>
        <p class="text-2xl font-bold text-blue-500">{{ $referrals->count() }}</p>
    </div>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Earned</p>
        <p class="text-lg font-bold text-green-500">Rp {{ number_format($affiliate->total_earned, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Saldo Tersedia</p>
        <p class="text-lg font-bold text-amber-500">Rp {{ number_format(max(0, $affiliate->balance - $pendingWithdraw), 0, ',', '.') }}</p>
        @if($pendingWithdraw > 0)<p class="text-xs text-red-400">Pending: Rp {{ number_format($pendingWithdraw, 0, ',', '.') }}</p>@endif
    </div>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Dicairkan</p>
        <p class="text-lg font-bold text-gray-900 dark:text-white">Rp {{ number_format($affiliate->total_paid, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Komisi Rate</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $affiliate->commission_rate }}%</p>
    </div>
</div>
```

**Problems**:
1. Repetitive `number_format($value, 0, ',', '.')` calls
2. Manual `Rp` prefix (inconsistent spacing)
3. Hard to read and maintain
4. Easy to make mistakes with parameters

---

### ✅ After (New Way)

```blade
{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Referral</p>
        <p class="text-2xl font-bold text-blue-500">@number($referrals->count())</p>
    </div>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Earned</p>
        <p class="text-lg font-bold text-green-500">@idr($affiliate->total_earned)</p>
    </div>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Saldo Tersedia</p>
        <p class="text-lg font-bold text-amber-500">@idr(max(0, $affiliate->balance - $pendingWithdraw))</p>
        @if($pendingWithdraw > 0)<p class="text-xs text-red-400">Pending: @idr($pendingWithdraw)</p>@endif
    </div>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Dicairkan</p>
        <p class="text-lg font-bold text-gray-900 dark:text-white">@idr($affiliate->total_paid)</p>
    </div>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Komisi Rate</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">@percent($affiliate->commission_rate)</p>
    </div>
</div>
```

**Benefits**:
1. ✅ Cleaner, more readable code
2. ✅ Consistent formatting across the app
3. ✅ Less prone to errors
4. ✅ Easier to maintain
5. ✅ Semantic meaning (currency vs number vs percentage)

---

## More Examples

### Table with Amounts

#### ❌ Before
```blade
<table>
    <tr>
        <td>{{ $commission->tenant->name ?? '-' }}</td>
        <td class="text-right">Rp {{ number_format($commission->payment_amount, 0, ',', '.') }}</td>
        <td class="text-right">Rp {{ number_format($commission->commission_amount, 0, ',', '.') }}</td>
    </tr>
</table>
```

#### ✅ After
```blade
<table>
    <tr>
        <td>{{ $commission->tenant->name ?? '-' }}</td>
        <td class="text-right">@idr($commission->payment_amount)</td>
        <td class="text-right">@idr($commission->commission_amount)</td>
    </tr>
</table>
```

---

### Dashboard Stats with Abbreviations

#### ❌ Before
```blade
<div class="stat-card">
    <p class="text-sm">Total Revenue</p>
    <p class="text-3xl">Rp {{ number_format($totalRevenue / 1000000, 1, ',', '.') }} Jt</p>
</div>
```

#### ✅ After
```blade
<div class="stat-card">
    <p class="text-sm">Total Revenue</p>
    <p class="text-3xl">@abbr($totalRevenue)</p>
</div>
```

---

### Mixed Number Types

#### ❌ Before
```blade
<div class="product-info">
    <p>Price: Rp {{ number_format($product->price, 0, ',', '.') }}</p>
    <p>Stock: {{ number_format($product->stock, 0, ',', '.') }} unit</p>
    <p>Discount: {{ number_format($product->discount, 2, ',', '.') }}%</p>
    <p>Weight: {{ number_format($product->weight, 3, ',', '.') }} kg</p>
</div>
```

#### ✅ After
```blade
<div class="product-info">
    <p>Price: @idr($product->price)</p>
    <p>Stock: @number($product->stock) unit</p>
    <p>Discount: @percent($product->discount)</p>
    <p>Weight: @decimal($product->weight, 3) kg</p>
</div>
```

---

## Migration Checklist

When migrating a view file:

- [ ] Replace `Rp {{ number_format($amount, 0, ',', '.') }}` with `@idr($amount)`
- [ ] Replace `{{ number_format($quantity, 0, ',', '.') }}` with `@number($quantity)`
- [ ] Replace `{{ number_format($value, 2, ',', '.') }}` with `@decimal($value, 2)`
- [ ] Replace `{{ number_format($percent, 2, ',', '.') }}%` with `@percent($percent)`
- [ ] Replace large number displays with `@abbr($value)` where appropriate
- [ ] Test the page in both light and dark modes
- [ ] Verify all numbers display correctly
- [ ] Check table alignment (use `text-right` class)

---

## Common Patterns

### Pattern 1: Currency in Cards

```blade
{{-- Before --}}
<p>Rp {{ number_format($amount, 0, ',', '.') }}</p>

{{-- After --}}
<p>@idr($amount)</p>
```

### Pattern 2: Quantities

```blade
{{-- Before --}}
<p>{{ number_format($stock, 0, ',', '.') }} unit</p>

{{-- After --}}
<p>@number($stock) unit</p>
```

### Pattern 3: Percentages

```blade
{{-- Before --}}
<p>{{ number_format($rate, 2, ',', '.') }}%</p>

{{-- After --}}
<p>@percent($rate)</p>
```

### Pattern 4: Conditional Display

```blade
{{-- Before --}}
@if($pendingWithdraw > 0)
    <p>Pending: Rp {{ number_format($pendingWithdraw, 0, ',', '.') }}</p>
@endif

{{-- After --}}
@if($pendingWithdraw > 0)
    <p>Pending: @idr($pendingWithdraw)</p>
@endif
```

### Pattern 5: Calculations

```blade
{{-- Before --}}
<p>Rp {{ number_format(max(0, $balance - $pending), 0, ',', '.') }}</p>

{{-- After --}}
<p>@idr(max(0, $balance - $pending))</p>
```

---

## Testing After Migration

1. **Visual Check**: Open the page and verify all numbers display correctly
2. **Dark Mode**: Toggle dark mode and verify readability
3. **Responsive**: Check on mobile (320px), tablet (768px), desktop (1280px+)
4. **Edge Cases**: Test with:
   - Zero values: `@idr(0)` → `Rp 0`
   - Null values: `@idr(null)` → `Rp 0`
   - Large numbers: `@idr(1234567890)` → `Rp 1.234.567.890`
   - Negative numbers: `@idr(-1234)` → `Rp -1.234`

---

## Rollout Strategy

### Phase 1: High-Traffic Pages (Week 1)
- Dashboard
- Invoice list/detail
- Sales order list/detail
- Product list/detail

### Phase 2: Financial Pages (Week 2)
- Accounting reports
- Payment pages
- Commission pages
- Affiliate dashboard

### Phase 3: Remaining Pages (Week 3-4)
- All other module pages
- Admin pages
- Settings pages

### Phase 4: Verification (Week 5)
- Full application audit
- User acceptance testing
- Performance testing

---

## Need Help?

- **Documentation**: See [NUMBER-FORMATTING-GUIDE.md](./NUMBER-FORMATTING-GUIDE.md)
- **Helper Class**: `app/Helpers/NumberHelper.php`
- **Blade Directives**: `app/Providers/AppServiceProvider.php` (registerBladeDirectives method)
- **Task Summary**: `.kiro/specs/erp-comprehensive-audit-fix/TASK-6-UI-UX-AUDIT-SUMMARY.md`

---

**Last Updated**: 2025-01-XX  
**Migration Status**: In Progress (Phase 1)
