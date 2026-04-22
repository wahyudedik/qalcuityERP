<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'size' => 'md', // sm, md, lg, xl
    'block' => false,
    'touch' => true,
    'iconOnly' => false,
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
    'size' => 'md', // sm, md, lg, xl
    'block' => false,
    'touch' => true,
    'iconOnly' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>



<?php
    $sizes = [
        'sm' => 'min-h-[40px] min-w-[40px] px-3 py-2 text-xs',
        'md' => 'min-h-[44px] px-4 py-2.5 text-sm',
        'lg' => 'min-h-[48px] px-5 py-3 text-base',
        'xl' => 'min-h-[52px] px-6 py-4 text-lg',
    ];

    $baseClasses =
        'inline-flex items-center justify-center gap-2 font-medium rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';
    $touchClasses = $touch ? ' touch-target btn-touch' : '';
    $blockClasses = $block ? ' w-full' : '';
?>

<button
    <?php echo e($attributes->merge(['class' => $baseClasses . $touchClasses . $blockClasses . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . $class])); ?>

    type="button">
    <?php if($iconOnly): ?>
        <span class="<?php echo e($size === 'sm' ? 'w-4 h-4' : ($size === 'lg' ? 'w-6 h-6' : 'w-5 h-5')); ?>">
            <?php echo e($slot); ?>

        </span>
    <?php else: ?>
        <?php echo e($slot); ?>

    <?php endif; ?>
</button>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\touch-button.blade.php ENDPATH**/ ?>