<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['value', 'required' => false]));

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

foreach (array_filter((['value', 'required' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<label <?php echo e($attributes->merge(['class' => 'block font-medium text-sm text-gray-700 dark:text-gray-300 mb-1'])); ?>>
    <?php echo e($value ?? $slot); ?>

    <?php if($required): ?>
        <span class="text-red-500 ml-0.5" title="Wajib diisi">*</span>
    <?php endif; ?>
</label>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\input-label.blade.php ENDPATH**/ ?>