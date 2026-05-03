

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null, // Optional toolbar title
    'collapsible' => false, // Whether filters can be collapsed
    'defaultCollapsed' => false, // Start collapsed or not
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
    'title' => null, // Optional toolbar title
    'collapsible' => false, // Whether filters can be collapsed
    'defaultCollapsed' => false, // Start collapsed or not
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="bg-white rounded-2xl border border-gray-200 mb-4">
    <?php if($title): ?>
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900"><?php echo e($title); ?></h3>
        </div>
    <?php endif; ?>

    <div class="p-4">
        
        <?php if(isset($filters)): ?>
            <div class="space-y-4">
                <?php echo e($filters); ?>

            </div>
        <?php endif; ?>

        
        <?php if(isset($actions)): ?>
            <div
                class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mt-4 pt-4 border-t border-gray-200">
                <?php echo e($actions); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\healthcare\toolbar.blade.php ENDPATH**/ ?>