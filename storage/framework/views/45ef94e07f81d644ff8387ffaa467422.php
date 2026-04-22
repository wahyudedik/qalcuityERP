<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'maxHeight' => '90vh',
    'stickyHeader' => true,
    'stickyFooter' => true,
    'fullScreenMobile' => false,
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
    'maxHeight' => '90vh',
    'stickyHeader' => true,
    'stickyFooter' => true,
    'fullScreenMobile' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>



<div <?php echo e($attributes->merge(['class' => 'modal-content-mobile ' . ($fullScreenMobile ? 'modal-mobile-full' : '')])); ?>

    style="max-height: <?php echo e($maxHeight); ?>;">
    <?php if($stickyHeader): ?>
        <div class="modal-header-sticky px-4 py-3 sm:px-6">
            <?php echo e($header ?? ''); ?>

        </div>
    <?php else: ?>
        <?php echo e($header ?? ''); ?>

    <?php endif; ?>

    <div class="modal-body px-4 py-3 sm:px-6 <?php echo e($stickyHeader ? 'pt-2' : ''); ?> <?php echo e($stickyFooter ? 'pb-2' : ''); ?>">
        <?php echo e($slot); ?>

    </div>

    <?php if($stickyFooter && isset($footer)): ?>
        <div class="modal-footer-sticky px-4 py-3 sm:px-6">
            <?php echo e($footer); ?>

        </div>
    <?php elseif(isset($footer)): ?>
        <?php echo e($footer); ?>

    <?php endif; ?>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\mobile-modal.blade.php ENDPATH**/ ?>