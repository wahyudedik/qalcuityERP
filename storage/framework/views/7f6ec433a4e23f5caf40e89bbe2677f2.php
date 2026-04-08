<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> Price List <?php $__env->endSlot(); ?>

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500 dark:text-slate-400">Kelola harga khusus per customer berdasarkan tier, kontrak, atau promosi.</p>
        <a href="<?php echo e(route('price-lists.create')); ?>" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Price List Baru</a>
    </div>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php $__empty_1 = true; $__currentLoopData = $priceLists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $typeColor = match($pl->type) {
                'tier'     => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                'contract' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                'promo'    => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                default    => 'bg-gray-100 text-gray-500',
            };
            $isValid = $pl->isValid();
        ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white"><?php echo e($pl->name); ?></h3>
                    <?php if($pl->code): ?><p class="text-xs text-gray-400 dark:text-slate-500"><?php echo e($pl->code); ?></p><?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($typeColor); ?>"><?php echo e($pl->typeLabel()); ?></span>
                    <span class="w-2 h-2 rounded-full <?php echo e($isValid ? 'bg-green-500' : 'bg-gray-300 dark:bg-white/20'); ?>"></span>
                </div>
            </div>

            <?php if($pl->description): ?>
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-3"><?php echo e($pl->description); ?></p>
            <?php endif; ?>

            <div class="flex gap-4 text-xs text-gray-500 dark:text-slate-400 mb-3">
                <span>📦 <?php echo e($pl->items_count); ?> produk</span>
                <span>👥 <?php echo e($pl->customers->count()); ?> customer</span>
            </div>

            <?php if($pl->valid_from || $pl->valid_until): ?>
            <p class="text-xs text-gray-400 dark:text-slate-500 mb-3">
                Berlaku: <?php echo e($pl->valid_from?->format('d M Y') ?? '∞'); ?> – <?php echo e($pl->valid_until?->format('d M Y') ?? '∞'); ?>

            </p>
            <?php endif; ?>

            <div class="flex items-center gap-2 pt-3 border-t border-gray-100 dark:border-white/5">
                <a href="<?php echo e(route('price-lists.show', $pl)); ?>" class="flex-1 text-center px-3 py-1.5 text-xs border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5">Detail</a>
                <form method="POST" action="<?php echo e(route('price-lists.destroy', $pl)); ?>" onsubmit="return confirm('Hapus price list ini?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="px-3 py-1.5 text-xs text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg">Hapus</button>
                </form>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-span-3 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada price list. <a href="<?php echo e(route('price-lists.create')); ?>" class="text-blue-600 dark:text-blue-400 hover:underline">Buat sekarang</a>.</div>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/price-lists/index.blade.php ENDPATH**/ ?>