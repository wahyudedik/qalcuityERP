# Mobile Card View Component

## Overview
Komponen ini menampilkan data dalam format card untuk tampilan mobile (<768px), secara otomatis hide di desktop.

## Usage Example

### Basic Usage
```blade
{{-- Desktop Table --}}
<div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm">
        {{-- table content --}}
    </table>
</div>

{{-- Mobile Cards --}}
<x-mobile-card 
    :data="$customers" 
    :fields="[
        ['label' => 'Perusahaan', 'key' => 'company'],
        ['label' => 'Telepon', 'key' => 'phone', 'type' => 'tel'],
        ['label' => 'Email', 'key' => 'email', 'type' => 'email'],
        ['label' => 'Credit Limit', 'key' => 'credit_limit', 'type' => 'currency'],
    ]"
    titleField="name"
    subtitleField="company"
    statusField="is_active"
    :actions="function($item) {
        return '
            <button class=\"px-3 py-2 text-sm bg-blue-600 text-white rounded-lg\">Edit</button>
            <button class=\"px-3 py-2 text-sm bg-red-600 text-white rounded-lg\">Delete</button>
        ';
    }"
/>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `data` | array/collection | `[]` | Data yang akan ditampilkan |
| `fields` | array | `[]` | Konfigurasi field yang akan ditampilkan |
| `actions` | callable/string | `null` | Tombol aksi di bagian bawah card |
| `titleField` | string | `'name'` | Field untuk judul card |
| `subtitleField` | string | `null` | Field untuk subtitle (opsional) |
| `statusField` | string | `null` | Field untuk badge status |
| `emptyMessage` | string | `'Tidak ada data'` | Pesan saat data kosong |
| `clickUrl` | callable/string | `null` | URL saat card diklik |

## Field Configuration

Setiap field dalam array `fields` memiliki struktur:

```php
[
    'label' => 'Label Field',      // Label yang ditampilkan
    'key' => 'field_name',         // Key dari data
    'type' => 'text',              // Type: text, tel, email, currency, date, badge
    'class' => '',                 // Custom CSS class
    'badgeClass' => '',            // CSS class khusus untuk type badge
]
```

### Field Types

1. **text** (default): Teks biasa
2. **tel**: Link telepon (`tel:`)
3. **email**: Link email (`mailto:`)
4. **currency**: Format mata uang Rupiah
5. **date**: Format tanggal
6. **badge**: Badge dengan warna

## Status Auto-Styling

Status akan otomatis di-style berdasarkan value:

- **Active/Aktif/Published/Completed/Paid**: Green badge
- **Inactive/Nonaktif/Draft/Pending**: Amber badge
- **Cancelled/Rejected/Failed**: Red badge
- **Other**: Gray badge

## Touch Targets

Semua tombol dan link memiliki minimum touch target 44px sesuai standar mobile.

## Responsive Behavior

- **Mobile (<768px)**: Menampilkan card view
- **Desktop (≥768px)**: Component hidden (gunakan table untuk desktop)
