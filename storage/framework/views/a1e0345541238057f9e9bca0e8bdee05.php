<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'class' => '',
    'striped' => true,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'class' => '',
    'striped' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<div <?php echo e($attributes->merge(['class' => 'bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden shadow-sm ' . $class])); ?>>
    <div class="overflow-x-auto">
        <table class="w-full text-sm <?php echo e($striped ? 'table-striped' : ''); ?>">
            <?php echo e($slot); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\table.blade.php ENDPATH**/ ?>