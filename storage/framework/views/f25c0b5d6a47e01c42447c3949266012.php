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
     <?php $__env->slot('header', null, []); ?> Manajemen Gudang <?php $__env->endSlot(); ?>

    <div class="flex flex-col sm:flex-row gap-6">
        
        <div class="w-full sm:w-80 shrink-0">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Tambah Gudang</h3>
                <form method="POST" action="<?php echo e(route('inventory.warehouses.store')); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Gudang *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kode (opsional)</label>
                        <input type="text" name="code" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Alamat</label>
                        <textarea name="address" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tambah Gudang</button>
                </form>
            </div>
        </div>

        
        <div class="flex-1">
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Daftar Gudang</h3>
                    <a href="<?php echo e(route('inventory.index')); ?>" class="text-sm text-blue-600 hover:underline">← Kembali ke Produk</a>
                </div>
                <div class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $stockCount = $wh->productStocks->sum('quantity'); ?>
                    <div class="px-5 py-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900"><?php echo e($wh->name); ?></p>
                            <p class="text-xs text-gray-500">Kode: <?php echo e($wh->code); ?> <?php if($wh->address): ?> · <?php echo e($wh->address); ?><?php endif; ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900"><?php echo e(number_format($wh->productStocks->sum('quantity'))); ?></p>
                            <p class="text-xs text-gray-500">total stok</p>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-5 py-10 text-center text-gray-400 text-sm">Belum ada gudang.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\inventory\warehouses.blade.php ENDPATH**/ ?>