# Telecom Views Standardization - Progress & Guide

## 📊 Progress Status

**Completed: 2/13 files (15%)**
- ✅ `devices/create.blade.php` - Fully standardized
- ✅ `devices/edit.blade.php` - Fully standardized

**Remaining: 11/13 files (85%)**
- ⏳ `devices/index.blade.php` (331 lines)
- ⏳ `devices/show.blade.php`
- ⏳ `dashboard/index.blade.php`
- ⏳ `maps/index.blade.php`
- ⏳ `packages/index.blade.php`
- ⏳ `packages/create.blade.php`
- ⏳ `subscriptions/index.blade.php`
- ⏳ `customers/usage.blade.php`
- ⏳ `vouchers/index.blade.php`
- ⏳ `vouchers/create.blade.php`
- ⏳ `reports/index.blade.php`

---

## 🎯 Standardization Checklist

For each file, apply these transformations:

### 1. Layout Migration
```blade
# BEFORE
@extends('layouts.app')
@section('title', 'Page Title')
@section('content')
    <div class="container mx-auto px-4 py-6">
        ...
    </div>
@endsection

# AFTER
<x-app-layout>
    <x-slot name="header">
        {{ __('Page Title') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            ...
        </div>
    </div>
</x-app-layout>
```

### 2. Container Widths
- **Full width pages**: `max-w-7xl mx-auto sm:px-6 lg:px-8`
- **Form pages**: `max-w-4xl mx-auto sm:px-6 lg:px-8`
- **Single column**: `max-w-3xl mx-auto sm:px-6 lg:px-8`

### 3. Card/Box Styling
```blade
# BEFORE
<div class="bg-white rounded-lg shadow">

# AFTER
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
```

### 4. Dark Mode Colors

#### Text Colors
| Element | Light Mode | Dark Mode |
|---------|-----------|-----------|
| Headings | `text-gray-900` | `dark:text-white` |
| Labels | `text-gray-700` | `dark:text-gray-300` |
| Body text | `text-gray-600` | `dark:text-gray-400` |
| Muted text | `text-gray-500` | `dark:text-gray-400` |

#### Backgrounds
| Element | Light Mode | Dark Mode |
|---------|-----------|-----------|
| Cards | `bg-white` | `dark:bg-gray-800` |
| Inputs | - | `dark:bg-gray-700 dark:text-white` |
| Secondary | `bg-gray-50` | `dark:bg-gray-700` |
| Headers | `bg-gray-50` | `dark:bg-gray-700` |

#### Borders
| Element | Light Mode | Dark Mode |
|---------|-----------|-----------|
| Default | `border-gray-300` | `dark:border-gray-600` |
| Light | `border-gray-200` | `dark:border-gray-700` |
| Table dividers | `divide-gray-200` | `dark:divide-gray-700` |

### 5. Input Fields Template
```html
<input class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 
    rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent 
    dark:bg-gray-700 dark:text-white">

<select class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 
    rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">

<textarea class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 
    rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent 
    dark:bg-gray-700 dark:text-white">
```

### 6. Icon Replacement (SVG → Font Awesome)

| Old SVG Icon | New Font Awesome |
|--------------|------------------|
| Back arrow | `<i class="fas fa-arrow-left"></i>` |
| Plus/Add | `<i class="fas fa-plus"></i>` |
| Eye/View | `<i class="fas fa-eye"></i>` |
| Pencil/Edit | `<i class="fas fa-edit"></i>` |
| Trash/Delete | `<i class="fas fa-trash"></i>` |
| Map marker | `<i class="fas fa-map-marker-alt"></i>` |
| Map | `<i class="fas fa-map"></i>` |
| Spinner/Loading | `<i class="fas fa-spinner fa-spin"></i>` |
| Check | `<i class="fas fa-check"></i>` |
| Warning | `<i class="fas fa-exclamation-triangle"></i>` |
| Error | `<i class="fas fa-times-circle"></i>` |
| Info | `<i class="fas fa-info-circle"></i>` |
| Server/Device | `<i class="fas fa-server"></i>` |
| Network | `<i class="fas fa-network-wired"></i>` |
| Users | `<i class="fas fa-users"></i>` |
| Chart | `<i class="fas fa-chart-bar"></i>` |

### 7. Button Styling
```html
# Primary Button
<button class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 
    dark:bg-blue-700 dark:hover:bg-blue-600">

# Secondary/Cancel Button
<a class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
    text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">

# Green/Success Button
<button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 
    dark:bg-green-700 dark:hover:bg-green-600">

# Red/Danger Button
<button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 
    dark:bg-red-700 dark:hover:bg-red-600">
```

### 8. Alert Boxes
```html
# Success
<div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-800 
    text-green-700 dark:text-green-400 px-4 py-3 rounded-lg">

# Error
<div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-800 
    text-red-700 dark:text-red-400 px-4 py-3 rounded-lg">

# Info
<div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 
    p-4">
    <div class="flex">
        <i class="fas fa-info-circle h-5 w-5 text-blue-400"></i>
        <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300 ml-3">
            {{ __('Info Title') }}
        </h3>
    </div>
</div>
```

### 9. Status Badges
```html
# Online/Active/Approved
<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
    bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">

# Offline/Error/Rejected
<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
    bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">

# Maintenance/Warning/Pending
<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
    bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400">

# Neutral/Unknown
<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
    bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-400">
```

### 10. Stats Cards
```html
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-600 dark:text-gray-400">Label</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">Value</p>
        </div>
        <div class="bg-blue-100 dark:bg-blue-900/30 p-3 rounded-full">
            <i class="fas fa-icon text-blue-600 dark:text-blue-400 text-xl"></i>
        </div>
    </div>
</div>
```

### 11. Tables
```html
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium 
                    text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Header
                </th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                    Data
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

### 12. Empty State
```html
<tr>
    <td colspan="8" class="px-6 py-12 text-center">
        <div class="text-gray-400 dark:text-gray-500">
            <i class="fas fa-inbox text-6xl"></i>
            <p class="mt-2 text-sm">{{ __('Belum ada data') }}</p>
            <a href="#" class="mt-2 inline-block text-blue-600 dark:text-blue-400 
                hover:text-blue-800 dark:hover:text-blue-300">
                {{ __('Tambah data pertama') }} →
            </a>
        </div>
    </td>
</tr>
```

### 13. Text Localization
Wrap ALL user-facing text:
```blade
# Headers
<h1>{{ __('Network Devices') }}</h1>

# Labels
<label>{{ __('Nama Device') }} *</label>

# Buttons
<button>{{ __('Simpan') }}</button>

# Placeholders
<input placeholder="{{ __('Cari device...') }}">

# Messages
<p>{{ __('Belum ada device yang terdaftar') }}</p>

# Alerts
{{ __('Device berhasil ditambahkan') }}
```

---

## 📝 File-by-File Notes

### devices/index.blade.php (331 lines)
- Stats cards (4 cards) → Add dark mode
- Filter form → Add dark mode to inputs
- Table → Full dark mode support
- Empty state → Replace SVG with Font Awesome
- Action buttons → Replace SVG with Font Awesome
- All text → Add `{{ __('...') }}`

### devices/show.blade.php
- Device details → Dark mode cards
- Status badge → Dark mode variant
- Related lists (subscriptions, users) → Dark mode tables
- Action buttons → Font Awesome icons

### dashboard/index.blade.php
- Stats grid (multiple cards) → Dark mode
- Charts section → Dark mode backgrounds
- Recent alerts list → Dark mode
- Network topology → Dark mode
- Revenue summary → Dark mode cards

### maps/index.blade.php
- Full-screen Leaflet map → Keep as-is (map library)
- Sidebar filters → Dark mode
- Device list → Dark mode
- Controls → Dark mode buttons
- Stats overlay → Dark mode

### packages/index.blade.php
- Packages table → Dark mode
- Status badges → Dark mode variants
- Action buttons → Font Awesome
- Empty state → Font Awesome icon

### packages/create.blade.php
- Form layout → Same as devices/create
- All inputs → Dark mode
- Buttons → Dark mode variants
- Info boxes → Dark mode

### subscriptions/index.blade.php
- Subscriptions table → Dark mode
- Customer info → Dark mode
- Status badges → Dark mode
- Filters → Dark mode inputs

### customers/usage.blade.php
- Usage charts → Dark mode backgrounds
- Usage table → Dark mode
- Stats cards → Dark mode
- Date filters → Dark mode inputs

### vouchers/index.blade.php
- Vouchers table → Dark mode
- Status badges → Dark mode
- Action buttons → Font Awesome
- Stats cards → Dark mode

### vouchers/create.blade.php
- Form → Same pattern as devices/create
- All inputs → Dark mode
- Buttons → Dark mode

### reports/index.blade.php
- Report cards/grid → Dark mode
- Filter form → Dark mode
- Download buttons → Dark mode
- Stats → Dark mode cards

---

## ✅ Quality Checklist (Per File)

For each file, verify:
- [ ] `@extends('layouts.app')` → `<x-app-layout>`
- [ ] `@section('title')` → `<x-slot name="header">`
- [ ] Container uses `max-w-* mx-auto sm:px-6 lg:px-8`
- [ ] All cards: `dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg`
- [ ] All text: `dark:text-*` variants added
- [ ] All inputs: `dark:bg-gray-700 dark:text-white dark:border-gray-600`
- [ ] All borders: `dark:border-gray-*` variants
- [ ] SVG icons → Font Awesome `<i class="fas fa-*">`
- [ ] All user text: `{{ __('...') }}`
- [ ] Alert boxes: dark mode variants
- [ ] Buttons: `dark:bg-*` and `dark:hover:bg-*`
- [ ] Status badges: dark mode colors
- [ ] Tables: `dark:divide-gray-700` and `dark:hover:bg-gray-700`
- [ ] Empty states: Font Awesome icons
- [ ] JavaScript functionality preserved (Leaflet, geolocation, etc.)

---

## 🚀 How to Continue

### Option 1: Manual (Recommended for accuracy)
1. Open each file
2. Apply transformations from this guide
3. Use the checklist to verify
4. Test in both light and dark mode

### Option 2: Semi-Automated
1. Use search/replace for common patterns:
   - `@extends('layouts.app')` → `<x-app-layout>`
   - `bg-white rounded-lg shadow` → `bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg`
   - `text-gray-700` → `text-gray-700 dark:text-gray-300`
   - `border-gray-300` → `border-gray-300 dark:border-gray-600`
2. Manually fix complex sections (tables, forms, icons)

### Option 3: AI-Assisted
1. Open each file in Qoder
2. Ask: "Standardize this file using the pattern in TELECOM_VIEWS_STANDARDIZATION_GUIDE.md"
3. Review changes
4. Test in browser

---

## 📚 References

- **Memory**: "Laravel Blade UI Standardization with x-app-layout"
- **Completed Examples**: 
  - `telecom/devices/create.blade.php`
  - `telecom/devices/edit.blade.php`
  - `cosmetic/formulas/index.blade.php`
  - `production/gantt.blade.php`
  - `production/dashboard.blade.php`

---

## ⚠️ Important Notes

1. **Always test both light and dark mode** after transformation
2. **Keep JavaScript intact** (Leaflet maps, geolocation, charts)
3. **Font Awesome assumed loaded** - don't add CDN links
4. **Preserve all functionality** - only change styling
5. **Use create_file for large changes** (>50 lines) for efficiency
6. **Use search_replace for small changes** (<50 lines) for precision

---

**Last Updated**: {{ current_date }}
**Status**: In Progress (2/13 files completed)
**Next Step**: Continue with devices/index.blade.php
