<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'searchToggle' => false,
    'filterUrl' => null,
    'sortUrl' => null,
    'createUrl' => null,
    'createText' => 'Tambah',
    'bulkActions' => null,
    'selectedCount' => 0,
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
    'searchToggle' => false,
    'filterUrl' => null,
    'sortUrl' => null,
    'createUrl' => null,
    'createText' => 'Tambah',
    'bulkActions' => null,
    'selectedCount' => 0,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>



<div
    class="md:hidden sticky top-0 z-30 bg-white dark:bg-[#1e293b] border-b border-gray-200 dark:border-white/10 shadow-sm">
    
    <?php if($selectedCount > 0): ?>
        <div class="px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-blue-700 dark:text-blue-400">
                    <?php echo e($selectedCount); ?> dipilih
                </span>
                <?php if($bulkActions): ?>
                    <div class="flex items-center gap-2">
                        <?php if(is_callable($bulkActions)): ?>
                            <?php echo $bulkActions(); ?>

                        <?php else: ?>
                            <?php echo $bulkActions; ?>

                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="px-4 py-3">
        <div class="flex items-center justify-between gap-3">
            
            <div class="flex items-center gap-2">
                <?php if($searchToggle): ?>
                    <button type="button" onclick="document.getElementById('mobile-search').classList.toggle('hidden')"
                        class="p-2.5 rounded-xl border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-white/5 transition min-w-[44px] min-h-[44px] flex items-center justify-center"
                        aria-label="Toggle search">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                <?php endif; ?>

                <?php if($filterUrl): ?>
                    <a href="<?php echo e($filterUrl); ?>"
                        class="p-2.5 rounded-xl border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-white/5 transition min-w-[44px] min-h-[44px] flex items-center justify-center"
                        aria-label="Filter">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                    </a>
                <?php endif; ?>

                <?php if($sortUrl): ?>
                    <a href="<?php echo e($sortUrl); ?>"
                        class="p-2.5 rounded-xl border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-white/5 transition min-w-[44px] min-h-[44px] flex items-center justify-center"
                        aria-label="Sort">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                        </svg>
                    </a>
                <?php endif; ?>
            </div>

            
            <?php if($createUrl): ?>
                <a href="<?php echo e($createUrl); ?>"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition shadow-sm min-h-[44px]"
                    role="button">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span><?php echo e($createText); ?></span>
                </a>
            <?php endif; ?>
        </div>

        
        <?php if($searchToggle): ?>
            <div id="mobile-search" class="hidden mt-3">
                <form method="GET" class="flex gap-2">
                    <input type="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari..."
                        class="flex-1 px-4 py-3 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[44px]"
                        aria-label="Search input">
                    <button type="submit"
                        class="px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition min-h-[44px]">
                        Cari
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\mobile-toolbar.blade.php ENDPATH**/ ?>