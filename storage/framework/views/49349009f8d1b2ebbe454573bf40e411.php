<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'primary', // primary, secondary, danger, success, warning, info, ghost
    'size' => 'md', // sm, md, lg
    'loading' => false,
    'disabled' => false,
    'iconOnly' => false,
    'type' => 'button',
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
    'variant' => 'primary', // primary, secondary, danger, success, warning, info, ghost
    'size' => 'md', // sm, md, lg
    'loading' => false,
    'disabled' => false,
    'iconOnly' => false,
    'type' => 'button',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // TASK 6.2: Button dengan state hover, disabled, loading, dan touch target 44x44px
    
    $isDisabled = $disabled || $loading;
    
    // Size classes — minimum 44x44px untuk mobile touch target
    $sizeClasses = [
        'sm' => 'min-h-[40px] min-w-[40px] px-3 py-2 text-xs',
        'md' => 'min-h-[44px] min-w-[44px] px-4 py-2.5 text-sm',
        'lg' => 'min-h-[48px] min-w-[48px] px-5 py-3 text-base',
    ];
    
    // Variant classes dengan state hover, active, disabled
    $variantClasses = [
        'primary' => 'bg-blue-600 text-white border-transparent hover:bg-blue-700 active:bg-blue-800 focus:ring-blue-500 disabled:bg-blue-400 disabled:cursor-not-allowed',
        'secondary' => 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 active:bg-gray-100 focus:ring-gray-500 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed',
        'danger' => 'bg-red-600 text-white border-transparent hover:bg-red-700 active:bg-red-800 focus:ring-red-500 disabled:bg-red-400 disabled:cursor-not-allowed',
        'success' => 'bg-green-600 text-white border-transparent hover:bg-green-700 active:bg-green-800 focus:ring-green-500 disabled:bg-green-400 disabled:cursor-not-allowed',
        'warning' => 'bg-amber-500 text-white border-transparent hover:bg-amber-600 active:bg-amber-700 focus:ring-amber-500 disabled:bg-amber-300 disabled:cursor-not-allowed',
        'info' => 'bg-cyan-600 text-white border-transparent hover:bg-cyan-700 active:bg-cyan-800 focus:ring-cyan-500 disabled:bg-cyan-400 disabled:cursor-not-allowed',
        'ghost' => 'bg-transparent text-gray-700 border-transparent hover:bg-gray-100 active:bg-gray-200 focus:ring-gray-500 disabled:text-gray-400 disabled:cursor-not-allowed',
    ];
    
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-medium rounded-lg border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';
    
    $classes = $baseClasses . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']) . ' ' . ($variantClasses[$variant] ?? $variantClasses['primary']);
?>

<button
    type="<?php echo e($type); ?>"
    <?php echo e($attributes->merge(['class' => $classes])); ?>

    <?php if($isDisabled): ?> disabled <?php endif; ?>
>
    <?php if($loading): ?>
        
        <svg class="animate-spin <?php echo e($size === 'sm' ? 'h-3 w-3' : ($size === 'lg' ? 'h-5 w-5' : 'h-4 w-4')); ?>" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    <?php endif; ?>
    
    <?php if($iconOnly): ?>
        <span class="<?php echo e($size === 'sm' ? 'w-4 h-4' : ($size === 'lg' ? 'w-6 h-6' : 'w-5 h-5')); ?>">
            <?php echo e($slot); ?>

        </span>
    <?php else: ?>
        <?php echo e($slot); ?>

    <?php endif; ?>
</button>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\button.blade.php ENDPATH**/ ?>