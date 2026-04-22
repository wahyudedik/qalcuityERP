

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['actions' => []]));

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

foreach (array_filter((['actions' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div id="bulk-action-bar"
    class="hidden mb-4 px-4 py-3 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-lg">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-sm text-gray-700 dark:text-gray-300">
                <span id="selected-count" class="font-semibold text-blue-600 dark:text-blue-400">0</span>
                item dipilih
            </span>
        </div>
        <div class="flex items-center gap-2">
            <?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(isset($action['danger'])): ?>
                    <button type="button" onclick="<?php echo e($action['onclick'] ?? ''); ?>"
                        class="px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 border border-red-300 dark:border-red-500/30 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                        <?php echo e($action['label']); ?>

                    </button>
                <?php else: ?>
                    <button type="button" onclick="<?php echo e($action['onclick'] ?? ''); ?>"
                        class="px-3 py-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 border border-blue-300 dark:border-blue-500/30 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-500/10 transition">
                        <?php echo e($action['label']); ?>

                    </button>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <button type="button" onclick="clearSelection()"
                class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                Batal
            </button>
        </div>
    </div>
</div>

<script>
    function clearSelection() {
        const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
        const selectAll = document.getElementById('select-all');

        checkboxes.forEach(checkbox => checkbox.checked = false);
        if (selectAll) selectAll.checked = false;

        updateBulkBar();
    }
</script>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\bulk-action-bar.blade.php ENDPATH**/ ?>