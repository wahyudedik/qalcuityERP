{{-- 
    Bulk Table Row Checkbox Component
    Usage: Di dalam <tbody><tr>, taruh di kolom pertama
--}}

@props(['id'])

<td class="px-6 py-4 whitespace-nowrap w-12">
    <input type="checkbox" name="selected_ids[]" value="{{ $id }}"
        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500" onchange="updateBulkBar()">
</td>
