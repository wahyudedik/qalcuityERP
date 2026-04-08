# 📋 Bulk Table Actions Standard

## Overview

Standard untuk tabel dengan bulk actions (multi-select) di QalcuityERP.

---

## ✅ CORRECT Structure

**Checkbox MUST be in FIRST column**, not in Actions column.

### Table Column Order:

```
✅ CORRECT:
[✓] | Name | Email | Status | Actions
 1  |  2   |   3   |   4    |   5

❌ WRONG:
Name | Email | Status | Actions | [✓]
  1  |   2   |   3    |    4    |  5
```

---

## 📐 Implementation

### 1. Bulk Action Bar (Above Table)

```blade
{{-- Bulk Action Bar --}}
<x-bulk-action-bar :actions="[
    ['label' => 'Export Selected', 'onclick' => 'bulkExport()'],
    ['label' => 'Delete Selected', 'onclick' => 'bulkDelete()', 'danger' => true]
]" />
```

### 2. Table Header (First Column)

```blade
<thead>
    <tr>
        <x-bulk-table-header select-all-id="select-all" />
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
    </tr>
</thead>
```

### 3. Table Rows (First Column)

```blade
<tbody>
    @foreach($items as $item)
        <tr>
            <x-bulk-table-row :id="$item->id" />
            <td class="px-6 py-4">{{ $item->name }}</td>
            <td class="px-6 py-4">{{ $item->email }}</td>
            <td class="px-6 py-4 text-center">
                <a href="{{ route('items.edit', $item) }}">Edit</a>
            </td>
        </tr>
    @endforeach
</tbody>
```

---

## 🎨 Complete Example

```blade
<x-app-layout>
    <x-slot name="header">
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">Customers</h1>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Action Button --}}
            <div class="mb-4 flex justify-end">
                <a href="{{ route('customers.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                    + New Customer
                </a>
            </div>

            {{-- Bulk Action Bar --}}
            <x-bulk-action-bar :actions="[
                ['label' => 'Export Selected', 'onclick' => 'bulkExport()'],
                ['label' => 'Delete Selected', 'onclick' => 'bulkDelete()', 'danger' => true]
            ]" />

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <form id="bulk-form" action="{{ route('customers.bulk-action') }}" method="POST">
                    @csrf
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    {{-- Checkbox column (FIRST) --}}
                                    <x-bulk-table-header select-all-id="select-all" />
                                    
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Phone
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($customers as $customer)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                        {{-- Checkbox column (FIRST) --}}
                                        <x-bulk-table-row :id="$customer->id" />
                                        
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $customer->name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $customer->email }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $customer->phone ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('customers.show', $customer) }}" 
                                                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm font-medium">
                                                    View
                                                </a>
                                                <a href="{{ route('customers.edit', $customer) }}" 
                                                   class="text-green-600 hover:text-green-800 dark:text-green-400 text-sm font-medium">
                                                    Edit
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>

<script>
function bulkExport() {
    const form = document.getElementById('bulk-form');
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'export';
    form.appendChild(actionInput);
    form.submit();
}

function bulkDelete() {
    if (confirm('Delete selected items?')) {
        const form = document.getElementById('bulk-form');
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        form.appendChild(actionInput);
        form.submit();
    }
}
</script>
```

---

## 🔧 Backend Controller

```php
public function bulkAction(Request $request)
{
    $request->validate([
        'selected_ids' => 'required|array',
        'selected_ids.*' => 'integer|exists:customers,id',
        'action' => 'required|in:export,delete,update_status',
    ]);

    $ids = $request->selected_ids;
    $action = $request->action;

    switch ($action) {
        case 'export':
            return (new CustomersExport())->download('customers.xlsx');
            
        case 'delete':
            Customer::whereIn('id', $ids)->delete();
            return redirect()->back()->with('success', count($ids) . ' customers deleted.');
            
        default:
            return redirect()->back()->with('error', 'Invalid action.');
    }
}
```

---

## 📊 Component Files

| Component | File | Purpose |
|-----------|------|---------|
| **Bulk Action Bar** | `components/bulk-action-bar.blade.php` | Shows selected count and action buttons |
| **Table Header Checkbox** | `components/bulk-table-header.blade.php` | Select all checkbox |
| **Table Row Checkbox** | `components/bulk-table-row.blade.php` | Individual row checkbox |

---

## ✅ Checklist

When implementing bulk actions:

- [ ] Checkbox is in FIRST column (not in Actions)
- [ ] Bulk action bar above table
- [ ] Select all checkbox in header
- [ ] Individual checkboxes in each row
- [ ] Bulk action bar shows selected count
- [ ] Cancel button to clear selection
- [ ] Form wraps entire table
- [ ] Backend validates `selected_ids[]` array
- [ ] Backend validates `action` parameter

---

**Last Updated:** April 8, 2026  
**Version:** 1.0  
**Status:** ✅ Active Standard
