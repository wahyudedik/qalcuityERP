

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['selectAllId' => 'select-all']));

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

foreach (array_filter((['selectAllId' => 'select-all']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<th class="px-6 py-3 text-left w-12">
    <input type="checkbox" id="<?php echo e($selectAllId); ?>"
        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
        onclick="toggleAllCheckboxes(this)">
</th>

<script>
    function toggleAllCheckboxes(source) {
        const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = source.checked);
        updateBulkBar();
    }

    function updateBulkBar() {
        const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]:checked');
        const bulkBar = document.getElementById('bulk-action-bar');
        const countSpan = document.getElementById('selected-count');

        if (bulkBar) {
            if (checkboxes.length > 0) {
                bulkBar.classList.remove('hidden');
                countSpan.textContent = checkboxes.length;
            } else {
                bulkBar.classList.add('hidden');
            }
        }
    }
</script>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\bulk-table-header.blade.php ENDPATH**/ ?>