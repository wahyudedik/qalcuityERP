

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['type' => 'card', 'count' => 1]));

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

foreach (array_filter((['type' => 'card', 'count' => 1]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $skeletonClass = 'animate-pulse bg-gray-200 rounded';
?>


<?php if($type === 'stats'): ?>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <?php for($i = 0; $i < $count; $i++): ?>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <div class="<?php echo e($skeletonClass); ?> h-3 w-20 mb-2"></div>
                <div class="<?php echo e($skeletonClass); ?> h-8 w-16 mb-1"></div>
                <div class="<?php echo e($skeletonClass); ?> h-2 w-24"></div>
            </div>
        <?php endfor; ?>
    </div>

    
<?php elseif($type === 'table'): ?>
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <div class="flex gap-4">
                <div class="<?php echo e($skeletonClass); ?> h-4 flex-1"></div>
                <div class="<?php echo e($skeletonClass); ?> h-4 flex-1"></div>
                <div class="<?php echo e($skeletonClass); ?> h-4 w-20"></div>
                <div class="<?php echo e($skeletonClass); ?> h-4 w-20"></div>
            </div>
        </div>

        
        <?php for($i = 0; $i < $count; $i++): ?>
            <div class="px-4 py-3 border-b border-gray-100 last:border-0">
                <div class="flex items-center gap-4">
                    <div class="<?php echo e($skeletonClass); ?> h-9 w-9 rounded-xl"></div>
                    <div class="flex-1 space-y-2">
                        <div class="<?php echo e($skeletonClass); ?> h-4 w-32"></div>
                        <div class="<?php echo e($skeletonClass); ?> h-3 w-24"></div>
                    </div>
                    <div class="<?php echo e($skeletonClass); ?> h-6 w-16 rounded-lg"></div>
                    <div class="<?php echo e($skeletonClass); ?> h-8 w-8 rounded-lg"></div>
                </div>
            </div>
        <?php endfor; ?>
    </div>

    
<?php elseif($type === 'card'): ?>
    <?php for($i = 0; $i < $count; $i++): ?>
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex items-start gap-4 mb-4">
                <div class="<?php echo e($skeletonClass); ?> h-12 w-12 rounded-xl"></div>
                <div class="flex-1 space-y-2">
                    <div class="<?php echo e($skeletonClass); ?> h-5 w-3/4"></div>
                    <div class="<?php echo e($skeletonClass); ?> h-4 w-1/2"></div>
                </div>
            </div>
            <div class="space-y-3">
                <div class="<?php echo e($skeletonClass); ?> h-4 w-full"></div>
                <div class="<?php echo e($skeletonClass); ?> h-4 w-5/6"></div>
                <div class="<?php echo e($skeletonClass); ?> h-4 w-4/6"></div>
            </div>
            <div class="flex gap-2 mt-4 pt-4 border-t border-gray-100">
                <div class="<?php echo e($skeletonClass); ?> h-9 flex-1 rounded-xl"></div>
                <div class="<?php echo e($skeletonClass); ?> h-9 flex-1 rounded-xl"></div>
            </div>
        </div>
    <?php endfor; ?>

    
<?php elseif($type === 'list'): ?>
    <div class="space-y-3">
        <?php for($i = 0; $i < $count; $i++): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="<?php echo e($skeletonClass); ?> h-10 w-10 rounded-xl"></div>
                    <div class="flex-1 space-y-2">
                        <div class="<?php echo e($skeletonClass); ?> h-4 w-2/3"></div>
                        <div class="<?php echo e($skeletonClass); ?> h-3 w-1/2"></div>
                    </div>
                    <div class="<?php echo e($skeletonClass); ?> h-6 w-16 rounded-lg"></div>
                </div>
            </div>
        <?php endfor; ?>
    </div>

    
<?php elseif($type === 'form'): ?>
    <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
        <?php for($i = 0; $i < $count; $i++): ?>
            <div class="space-y-2">
                <div class="<?php echo e($skeletonClass); ?> h-4 w-24"></div>
                <div class="<?php echo e($skeletonClass); ?> h-10 w-full rounded-xl"></div>
            </div>
        <?php endfor; ?>
        <div class="flex gap-3 pt-4">
            <div class="<?php echo e($skeletonClass); ?> h-10 w-24 rounded-xl"></div>
            <div class="<?php echo e($skeletonClass); ?> h-10 w-32 rounded-xl"></div>
        </div>
    </div>

    
<?php elseif($type === 'text'): ?>
    <div class="space-y-2">
        <?php for($i = 0; $i < $count; $i++): ?>
            <div class="<?php echo e($skeletonClass); ?> h-4 w-full"></div>
        <?php endfor; ?>
        <div class="<?php echo e($skeletonClass); ?> h-4 w-3/4"></div>
    </div>

    
<?php elseif($type === 'custom'): ?>
    <?php echo e($slot); ?>


<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\skeleton.blade.php ENDPATH**/ ?>