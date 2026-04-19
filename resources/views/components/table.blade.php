@props([
    'class' => '',
    'striped' => true,
])

{{-- TASK 6.4: Tabel dengan header jelas, alternating rows, kolom aksi konsisten --}}
<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden shadow-sm ' . $class]) }}>
    <div class="overflow-x-auto">
        <table class="w-full text-sm {{ $striped ? 'table-striped' : '' }}">
            {{ $slot }}
        </table>
    </div>
</div>

<style>
    /* TASK 6.4: Alternating row colors untuk keterbacaan */
    .table-striped tbody tr:nth-child(even) {
        @apply bg-gray-50 dark:bg-slate-700/30;
    }
    
    .table-striped tbody tr:hover {
        @apply bg-blue-50 dark:bg-slate-700/50;
    }
    
    /* Header styling */
    .table-striped thead th {
        @apply bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-gray-200 font-semibold text-left px-4 py-3 border-b border-gray-200 dark:border-slate-600;
    }
    
    .table-striped tbody td {
        @apply px-4 py-3 border-b border-gray-100 dark:border-slate-700/50;
    }
    
    /* Kolom aksi konsisten di kanan */
    .table-striped .action-column {
        @apply text-right whitespace-nowrap;
    }
</style>
