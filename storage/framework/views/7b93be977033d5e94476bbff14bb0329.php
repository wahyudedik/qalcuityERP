<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'sortable' => false,
    'sortKey' => '',
    'currentSort' => '',
    'currentDirection' => 'asc',
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
    'sortable' => false,
    'sortKey' => '',
    'currentSort' => '',
    'currentDirection' => 'asc',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // TASK 6.4: Header tabel yang jelas dengan sort indicator
    $isSorted = $sortable && $sortKey === $currentSort;
    $nextDirection = $isSorted && $currentDirection === 'asc' ? 'desc' : 'asc';
?>

<th <?php echo e($attributes->merge(['class' => 'px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider bg-gray-100 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600'])); ?>>
    <?php if($sortable): ?>
        <button 
            type="button"
            onclick="window.location.href='<?php echo e(request()->fullUrlWithQuery(['sort' => $sortKey, 'direction' => $nextDirection])); ?>'"
            class="flex items-center gap-1 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
        >
            <?php echo e($slot); ?>

            <?php if($isSorted): ?>
                <?php if($currentDirection === 'asc'): ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                <?php else: ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                <?php endif; ?>
            <?php else: ?>
                <svg class="w-4 h-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
            <?php endif; ?>
        </button>
    <?php else: ?>
        <?php echo e($slot); ?>

    <?php endif; ?>
</th>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\table-header.blade.php ENDPATH**/ ?>