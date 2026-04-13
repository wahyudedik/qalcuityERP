# Mobile Responsive Components - Implementation Guide

## Overview

Panduan lengkap untuk menggunakan mobile responsive components di QalcuityERP.

---

## Available Components

### 1. mobile-table.blade.php

Component card view untuk menampilkan data list di mobile.

**Props:**
- `data` (array) - Data collection
- `fields` (array) - Field definitions dengan format:
  - `label` (string) - Label field
  - `key` (string) - Data key (support dot notation)
  - `type` (string) - Type: text, currency, date, datetime, number, percentage, tel, email, link, badge, boolean, progress
  - `class` (string) - Custom CSS class
  - `badgeClass` (string) - Badge styling (untuk type=badge)
- `titleField` (string) - Field untuk title card
- `subtitleField` (string) - Field untuk subtitle
- `statusField` (string) - Field untuk status badge (auto-coloring)
- `imageField` (string) - Field untuk image URL
- `actions` (callable|string) - Action buttons
- `emptyMessage` (string) - Pesan empty state
- `clickUrl` (string|callable) - URL untuk click-through

**Example:**
```blade
<x-mobile-table 
    :data="$invoices"
    :fields="[
        ['label' => 'No', 'key' => 'number'],
        ['label' => 'Customer', 'key' => 'customer.name'],
        ['label' => 'Total', 'key' => 'total_amount', 'type' => 'currency'],
        ['label' => 'Jatuh Tempo', 'key' => 'due_date', 'type' => 'date'],
    ]"
    titleField="number"
    subtitleField="customer.name"
    statusField="status"
    :actions="fn($item) => '<a href=\'/invoices/'.$item->id.'\' class=\'btn\'>Detail</a>'"
/>
```

---

### 2. mobile-stats.blade.php

Responsive stats cards dengan icon dan trend indicators.

**Props:**
- `stats` (array) - Array of stat objects
- `columns` (int) - Number of columns: 2, 3, or 4 (default: 2)
- `showIcons` (bool) - Show/hide icons (default: true)
- `showTrend` (bool) - Show/hide trend indicators (default: false)
- `size` (string) - Size: sm, md, lg (default: md)

**Stat Object:**
```php
[
    'label' => 'Total Penjualan',
    'value' => 'Rp 15.5M',
    'icon' => 'chart', // chart, document, users, currency, shopping, package, warning, check, clock
    'color' => 'blue', // blue, green, amber, red, purple, gray
    'trend' => '+12.5%',
    'trendUp' => true,
]
```

**Example:**
```blade
<x-mobile-stats :stats="[
    [
        'label' => 'Total Invoice',
        'value' => $stats['total'],
        'icon' => 'document',
        'color' => 'blue',
    ],
    [
        'label' => 'Belum Bayar',
        'value' => $stats['unpaid'],
        'icon' => 'warning',
        'color' => 'red',
    ],
]" :columns="2" />
```

---

### 3. mobile-empty-state.blade.php

Enhanced empty state dengan icon dan call-to-action.

**Props:**
- `title` (string) - Title text
- `description` (string) - Description text
- `icon` (string) - Icon: empty, document, search, filter, chart, users, package, calendar, warning
- `actionUrl` (string) - CTA button URL
- `actionText` (string) - CTA button text
- `actionIcon` (string) - CTA icon: plus, refresh, upload

**Example:**
```blade
<x-mobile-empty-state 
    title="Belum ada invoice"
    description="Buat invoice pertama Anda untuk mulai tracking"
    icon="document"
    action-url="{{ route('invoices.create') }}"
    action-text="Buat Invoice"
/>
```

---

### 4. mobile-pagination.blade.php

Touch-friendly pagination untuk mobile.

**Props:**
- `paginator` (Paginator) - Laravel paginator instance

**Example:**
```blade
<x-mobile-pagination :paginator="$products" />
```

---

### 5. mobile-toolbar.blade.php

Sticky toolbar dengan search, filter, sort, dan create actions.

**Props:**
- `searchToggle` (bool) - Show search toggle button
- `filterUrl` (string) - Filter page URL
- `sortUrl` (string) - Sort page URL
- `createUrl` (string) - Create page URL
- `createText` (string) - Create button text
- `bulkActions` (callable|string) - Bulk action buttons
- `selectedCount` (int) - Number of selected items

**Example:**
```blade
<x-mobile-toolbar 
    search-toggle
    create-url="{{ route('products.create') }}"
    create-text="Produk"
/>
```

---

## Implementation Pattern

### Adding Mobile View to Any Page

**Step 1:** Wrap desktop table with `hidden md:block`
```blade
{{-- Desktop view --}}
<div class="hidden md:block">
    <table>...</table>
</div>
```

**Step 2:** Add mobile card view with `md:hidden`
```blade
{{-- Mobile view --}}
<div class="md:hidden">
    <x-mobile-table 
        :data="$items"
        :fields="[...]"
        statusField="status"
        :actions="fn($item) => view('...')"
    />
    
    @if($items->hasPages())
        <x-mobile-pagination :paginator="$items" />
    @endif
</div>
```

**Step 3:** (Optional) Add mobile toolbar
```blade
<x-mobile-toolbar 
    search-toggle
    create-url="{{ route('items.create') }}"
    create-text="Item"
/>
```

---

## Complete Example: Products Page

```blade
<x-app-layout>
    <x-slot name="header">Data Produk</x-slot>

    {{-- Stats --}}
    <x-mobile-stats :stats="[
        ['label' => 'Total', 'value' => $total, 'icon' => 'package', 'color' => 'blue'],
        ['label' => 'Aktif', 'value' => $active, 'icon' => 'check', 'color' => 'green'],
        ['label' => 'Stok', 'value' => $stock, 'icon' => 'chart', 'color' => 'purple'],
        ['label' => 'Menipis', 'value' => $low, 'icon' => 'warning', 'color' => 'red'],
    ]" class="mb-6" />

    {{-- Toolbar --}}
    <x-mobile-toolbar 
        search-toggle
        create-url="{{ route('products.create') }}"
        create-text="Produk"
    />

    {{-- Desktop Table --}}
    <div class="hidden md:block">
        <table class="w-full text-sm">
            {{-- ... existing table ... --}}
        </table>
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden">
        <x-mobile-table 
            :data="$products"
            :fields="[
                ['label' => 'SKU', 'key' => 'sku'],
                ['label' => 'Harga Jual', 'key' => 'price_sell', 'type' => 'currency'],
                ['label' => 'Harga Beli', 'key' => 'price_buy', 'type' => 'currency'],
                ['label' => 'Stok', 'key' => 'stock'],
            ]"
            titleField="name"
            subtitleField="category"
            statusField="is_active"
            imageField="image"
            :actions="fn($product) => view('products._mobile-actions', ['product' => $product])"
        />
        
        @if($products->hasPages())
            <x-mobile-pagination :paginator="$products" />
        @endif
    </div>
</x-app-layout>
```

---

## Touch Target Guidelines

Semua interactive elements harus memiliki minimum size **44x44px** (WCAG 2.1 AA).

**Button sizes:**
- Small: `min-h-[40px] min-w-[40px]`
- Medium: `min-h-[44px] min-w-[44px]` (default)
- Large: `min-h-[48px] min-w-[48px]`
- Extra Large: `min-h-[52px] min-w-[52px]`

**Padding:**
- Buttons: `px-4 py-2.5` (minimum)
- Icon buttons: `p-2.5` (minimum)

---

## Status Badge Auto-Coloring

Component otomatis memberikan warna berdasarkan status:

| Status | Color |
|--------|-------|
| active, aktif, published, completed, paid, lunas, success | Green |
| inactive, nonaktif, draft, pending, menunggu | Amber |
| cancelled, rejected, failed, gagal, expired, kadaluarsa | Red |
| processing, diproses, in_progress | Blue |
| sent, terkirim, shipped | Purple |
| other | Gray |

---

## Field Types

| Type | Description | Example |
|------|-------------|---------|
| `text` | Plain text (default) | `John Doe` |
| `currency` | Format as Rupiah | `Rp 1.500.000` |
| `date` | Format date | `25/12/2024` |
| `datetime` | Format date & time | `25/12/2024 14:30` |
| `number` | Format number | `1,500,000` |
| `percentage` | Format as percentage | `12.5%` |
| `tel` | Clickable phone link | `<a href="tel:...">` |
| `email` | Clickable email link | `<a href="mailto:...">` |
| `link` | Custom URL link | `<a href="...">` |
| `badge` | Colored badge | `<span class="badge">` |
| `boolean` | Yes/No display | `Ya` / `Tidak` |
| `progress` | Progress bar | `[████░░░░] 50%` |

---

## Dark Mode Support

Semua component sudah support dark mode dengan class:
- `dark:bg-[#1e293b]` - Card background
- `dark:border-white/10` - Borders
- `dark:text-white` - Text
- `dark:text-slate-400` - Muted text

---

## Accessibility

- `role="list"` pada container
- `role="listitem"` pada cards
- `aria-label` pada interactive elements
- `aria-disabled="true"` pada disabled buttons
- Keyboard navigable
- Focus states visible

---

## Browser Support

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- iOS Safari 13+
- Android Chrome 80+

---

## Performance Tips

1. **Lazy load images** dengan `loading="lazy"`
2. **Use `md:hidden`** untuk hide mobile view di desktop
3. **Use `hidden md:block`** untuk hide desktop view di mobile
4. **Minimize re-renders** dengan Alpine.js `x-data` untuk state
5. **Use pagination** untuk large datasets

---

## Troubleshooting

**Issue:** Mobile view tidak muncul
**Solution:** Pastikan class `md:hidden` ada di container

**Issue:** Touch target terlalu kecil
**Solution:** Tambahkan `min-h-[44px] min-w-[44px]` ke button

**Issue:** Status badge tidak berwarna
**Solution:** Pastikan status value match dengan predefined statuses

**Issue:** Currency tidak terformat
**Solution:** Set `type='currency'` di field definition

---

## Next Steps

Untuk halaman lain yang belum ada mobile view:
1. Copy pattern dari `resources/views/products/index.blade.php`
2. Wrap desktop table dengan `hidden md:block`
3. Add mobile card view dengan `md:hidden`
4. Use `mobile-table` atau custom card layout
5. Add `mobile-pagination` jika ada pagination
6. Test di browser DevTools (iPhone SE, iPad)

---

## Component Files

- `resources/views/components/mobile-table.blade.php`
- `resources/views/components/mobile-stats.blade.php`
- `resources/views/components/mobile-empty-state.blade.php`
- `resources/views/components/mobile-pagination.blade.php`
- `resources/views/components/mobile-toolbar.blade.php`
- `resources/views/components/mobile-card.blade.php` (existing, enhanced)
- `resources/views/components/touch-button.blade.php` (existing)

---

**Last Updated:** April 12, 2026  
**Version:** 1.0.0
