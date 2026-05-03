<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['number', 'title' => '']));

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

foreach (array_filter((['number', 'title' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>



<div data-step="<?php echo e($number); ?>" data-step-title="<?php echo e($title); ?>" class="wizard-step"
    <?php if($number == 1): ?> style="display: block;" <?php else: ?> style="display: none;" <?php endif; ?>>
    <?php if($title): ?>
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900"><?php echo e($title); ?></h3>
            <?php if(isset($description)): ?>
                <p class="mt-1 text-sm text-gray-500"><?php echo e($description); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="space-y-5">
        <?php echo e($slot); ?>

    </div>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\wizard-step.blade.php ENDPATH**/ ?>