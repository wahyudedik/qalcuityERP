# 📐 Header & Navigation Standards

## Overview

Dokumen ini mendefinisikan standar konsisten untuk page headers, breadcrumbs, dan button placement di seluruh aplikasi QalcuityERP.

---

## 🎯 Design Principles

1. **NO buttons in nav header** - Header hanya untuk title dan breadcrumbs
2. **Consistent sizing** - Semua headers menggunakan ukuran yang sama
3. **Semantic HTML** - Gunakan heading tags (h1, h2, h3) dengan benar
4. **NO emoji icons** - Clean professional text only
5. **Buttons in content** - Action buttons di content area, bukan di header

---

## 📏 Size Standards

| Element | Tailwind Class | Pixel Size | Font Weight | Color |
|---------|---------------|------------|-------------|-------|
| **Page Title (h1)** | `text-base` | 16px | `font-semibold` (600) | `text-gray-900 dark:text-white` |
| **Breadcrumbs** | `text-xs` | 12px | `font-normal` (400) | `text-gray-500 dark:text-gray-400` |
| **Section Title (h2)** | `text-base` | 16px | `font-semibold` (600) | `text-gray-900 dark:text-white` |
| **Subsection (h3)** | `text-sm` | 14px | `font-medium` (500) | `text-gray-700 dark:text-gray-300` |
| **Sub-subsection (h4)** | `text-sm` | 14px | `font-medium` (500) | `text-gray-600 dark:text-gray-400` |

---

## ✅ CORRECT Pattern

### Simple Header (No Breadcrumbs)

```blade
<x-app-layout>
    <x-slot name="header">
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">Page Title</h1>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Action Buttons in Content Area --}}
            <div class="mb-4 flex justify-end">
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                    + Add New
                </button>
            </div>

            {{-- Page Content --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                ...
            </div>
        </div>
    </div>
</x-app-layout>
```

### Header with Breadcrumbs

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
            <h1 class="text-base font-semibold text-gray-900 dark:text-white">Page Title</h1>
            <x-breadcrumbs :items="[
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Parent Page', 'url' => route('parent.index')],
                ['label' => 'Current Page']
            ]" />
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Content with buttons --}}
        </div>
    </div>
</x-app-layout>
```

### Multiple Buttons

```blade
<div class="mb-4 flex flex-wrap gap-2 justify-end">
    <a href="{{ route('export') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm font-medium">
        Export
    </a>
    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
        + Add New
    </button>
</div>
```

---

## ❌ WRONG Patterns (DON'T USE)

### ❌ Button in Header

```blade
{{-- WRONG! --}}
<x-slot name="header">
    <div class="flex items-center justify-between">
        <span>Page Title</span>
        <button>Add New</button>  <!-- NO BUTTONS IN HEADER -->
    </div>
</x-slot>
```

### ❌ Emoji in Header

```blade
{{-- WRONG! --}}
<x-slot name="header">
    <span>📊 Dashboard Analytics</span>  <!-- NO EMOJI -->
</x-slot>
```

### ❌ Wrong Text Size

```blade
{{-- WRONG! --}}
<x-slot name="header">
    <h1 class="text-sm font-semibold">Title</h1>  <!-- TOO SMALL, USE text-base -->
</x-slot>

{{-- WRONG! --}}
<x-slot name="header">
    <h1 class="text-xl font-bold">Title</h1>  <!-- TOO BIG, USE text-base -->
</x-slot>
```

### ❌ Using <span> Instead of <h1>

```blade
{{-- WRONG! --}}
<x-slot name="header">
    <span>Page Title</span>  <!-- USE <h1> TAG -->
</x-slot>
```

---

## 🍞 Breadcrumbs Component

### Usage

```blade
<x-breadcrumbs :items="[
    ['label' => 'Dashboard', 'url' => route('dashboard')],
    ['label' => 'Products', 'url' => route('products.index')],
    ['label' => 'Create Product']  // Last item (current page) - no URL
]" />
```

### Output

```
Dashboard  ›  Products  ›  Create Product
```

### Features

- ✅ Automatic chevron separators
- ✅ Last item is bold (current page)
- ✅ All other items are clickable links
- ✅ Responsive and accessible
- ✅ Dark mode support

---

## 📋 Heading Hierarchy

### Proper Structure

```blade
<h1 class="text-base font-semibold text-gray-900 dark:text-white">Page Title</h1>

<div class="mt-6">
    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Section Title</h2>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Section description...</p>
    
    <div class="mt-4">
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Subsection</h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Subsection content...</p>
    </div>
</div>
```

### Rules

1. **ONE h1 per page** - Usually in `<x-slot name="header">`
2. **h2 for major sections** - Cards, tables, forms
3. **h3 for subsections** - Groups within sections
4. **h4 for minor headings** - Labels, small groups
5. **Never skip levels** - Don't go from h1 to h3

---

## 🎨 Button Standards

### Primary Button (Main Action)

```blade
<button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
    Primary Action
</button>
```

### Secondary Button (Alternative)

```blade
<button class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm font-medium">
    Secondary Action
</button>
```

### Danger Button (Delete)

```blade
<button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
    Delete
</button>
```

### Success Button (Confirm)

```blade
<button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
    Confirm
</button>
```

### Button Sizes

| Size | Classes | Usage |
|------|---------|-------|
| **Small** | `px-3 py-1.5 text-xs` | Table actions, inline buttons |
| **Medium** | `px-4 py-2 text-sm` | Default, page actions |
| **Large** | `px-6 py-3 text-base` | Hero actions, CTAs |

---

## 📐 Layout Structure

### Standard Page Layout

```blade
<x-app-layout>
    {{-- 1. Header (Title + Breadcrumbs Only) --}}
    <x-slot name="header">
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">Page Title</h1>
    </x-slot>

    {{-- 2. Content Wrapper --}}
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- 3. Action Buttons (If needed) --}}
            <div class="mb-4 flex justify-end">
                <button>+ Add New</button>
            </div>

            {{-- 4. Statistics/Summary (Optional) --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Stats cards -->
            </div>

            {{-- 5. Main Content --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
                <!-- Table, form, or other content -->
            </div>

            {{-- 6. Pagination (If needed) --}}
            <div class="mt-4">
                {{ $items->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
```

---

## 🔄 Migration Guide

### Before (Old Pattern)

```blade
<x-slot name="header">
    <div class="flex items-center justify-between">
        <span>📊 Products Management</span>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">
            + Add Product
        </button>
    </div>
</x-slot>

<div class="py-6">
    <!-- Content -->
</div>
```

### After (New Pattern)

```blade
<x-slot name="header">
    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">Products</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Products']
        ]" />
    </div>
</x-slot>

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex justify-end">
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                + Add Product
            </button>
        </div>
        <!-- Content -->
    </div>
</div>
```

---

## ✅ Checklist

Before deploying any page, verify:

- [ ] Header uses `text-base font-semibold`
- [ ] Header has NO buttons
- [ ] Header has NO emoji
- [ ] Header uses `<h1>` tag (not `<span>`)
- [ ] Buttons are in content area
- [ ] Breadcrumbs component used (if needed)
- [ ] Heading hierarchy is correct (h1 → h2 → h3)
- [ ] Dark mode colors applied
- [ ] Responsive layout (mobile-friendly)

---

## 📝 Examples

### Index Page (List)

```blade
<x-slot name="header">
    <h1 class="text-base font-semibold text-gray-900 dark:text-white">Customers</h1>
</x-slot>

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex justify-end">
            <a href="{{ route('customers.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                + New Customer
            </a>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm">
            <!-- Table -->
        </div>
    </div>
</div>
```

### Detail Page

```blade
<x-slot name="header">
    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">Customer Detail</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Customers', 'url' => route('customers.index')],
            ['label' => $customer->name]
        ]" />
    </div>
</x-slot>

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Detail content -->
    </div>
</div>
```

### Form Page

```blade
<x-slot name="header">
    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">Create Customer</h1>
        <x-breadcrumbs :items="[
            ['label' => 'Customers', 'url' => route('customers.index')],
            ['label' => 'Create']
        ]" />
    </div>
</x-slot>

<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <form>
            <!-- Form fields -->
        </form>
    </div>
</div>
```

---

## 🎯 Quick Reference

```
Header = Title + Breadcrumbs ONLY
Buttons = Content Area ONLY
Size = text-base (16px) for h1
Emoji = NEVER in headers
Structure = h1 → h2 → h3 → h4
```

---

**Last Updated:** April 8, 2026  
**Version:** 1.0  
**Status:** ✅ Active Standard
