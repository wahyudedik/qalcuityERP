@props([
    'align' => 'right',
])

{{-- TASK 6.4: Kolom aksi konsisten dengan dropdown menu --}}
<td {{ $attributes->merge(['class' => 'px-4 py-3 border-b border-gray-100 dark:border-slate-700/50 whitespace-nowrap text-' . $align]) }}>
    <div class="flex items-center justify-end gap-2">
        {{ $slot }}
    </div>
</td>
