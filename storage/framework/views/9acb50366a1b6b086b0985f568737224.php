<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['paginator', 'simple' => false]));

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

foreach (array_filter((['paginator', 'simple' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>



<?php if($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="Pagination Navigation" class="md:hidden">
        <div class="flex items-center justify-between gap-3 py-4">
            
            <?php if($paginator->onFirstPage()): ?>
                <span
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-400 bg-gray-50 cursor-not-allowed min-h-[44px]"
                    aria-disabled="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Sebelumnya</span>
                </span>
            <?php else: ?>
                <a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-700 bg-white hover:bg-gray-50 transition min-h-[44px]"
                    aria-label="Previous page">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Sebelumnya</span>
                </a>
            <?php endif; ?>

            
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600">
                    Halaman
                </span>
                <span
                    class="px-3 py-2 rounded-lg bg-blue-50 text-sm font-semibold text-blue-600 min-w-[44px] text-center">
                    <?php echo e($paginator->currentPage()); ?>

                </span>
                <span class="text-sm text-gray-600">
                    dari <?php echo e($paginator->lastPage()); ?>

                </span>
            </div>

            
            <?php if($paginator->hasMorePages()): ?>
                <a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-700 bg-white hover:bg-gray-50 transition min-h-[44px]"
                    aria-label="Next page">
                    <span>Selanjutnya</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            <?php else: ?>
                <span
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-400 bg-gray-50 cursor-not-allowed min-h-[44px]"
                    aria-disabled="true">
                    <span>Selanjutnya</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            <?php endif; ?>
        </div>

        
        <div class="text-center text-xs text-gray-500 pb-2">
            Menampilkan <?php echo e($paginator->firstItem() ?? 0); ?> - <?php echo e($paginator->lastItem() ?? 0); ?>

            dari <?php echo e($paginator->total()); ?> data
        </div>
    </nav>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\mobile-pagination.blade.php ENDPATH**/ ?>