

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['items' => []]));

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

foreach (array_filter((['items' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if(count($items) > 0): ?>
    <?php $lastItem = $items[count($items) - 1]; ?>

    
    <nav class="flex sm:hidden items-center text-xs text-gray-500" aria-label="Breadcrumb">
        <span class="text-gray-700 font-medium truncate max-w-[200px]">
            <?php echo e($lastItem['label']); ?>

        </span>
    </nav>

    
    <nav class="hidden sm:flex items-center gap-1 text-xs text-gray-500" aria-label="Breadcrumb">
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($index > 0): ?>
                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
            <?php endif; ?>

            
            <span
                x-data="{ show: false }"
                class="relative"
                @mouseenter="show = true"
                @mouseleave="show = false"
                title="<?php echo e($item['label']); ?>"
            >
                <?php if(isset($item['url']) && $index < count($items) - 1): ?>
                    <a href="<?php echo e($item['url']); ?>" class="hover:text-gray-700 transition truncate max-w-[180px] inline-block">
                        <?php echo e($item['label']); ?>

                    </a>
                <?php else: ?>
                    <span class="text-gray-700 font-medium truncate max-w-[180px] inline-block">
                        <?php echo e($item['label']); ?>

                    </span>
                <?php endif; ?>

                
                <?php if(strlen($item['label']) > 20): ?>
                    <span
                        x-show="show"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute bottom-full left-0 mb-1 z-50 px-2 py-1 text-xs text-white bg-gray-800 rounded shadow-lg whitespace-nowrap pointer-events-none"
                        role="tooltip"
                    >
                        <?php echo e($item['label']); ?>

                    </span>
                <?php endif; ?>
            </span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\breadcrumbs.blade.php ENDPATH**/ ?>