<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['disabled' => false, 'type' => 'text']));

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

foreach (array_filter((['disabled' => false, 'type' => 'text']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<input 
    type="<?php echo e($type); ?>"
    <?php if($disabled): echo 'disabled'; endif; ?> 
    <?php echo e($attributes->merge([
        'class' => 'w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm disabled:bg-gray-100 disabled:cursor-not-allowed transition-colors duration-200'
    ])); ?>

>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\text-input.blade.php ENDPATH**/ ?>