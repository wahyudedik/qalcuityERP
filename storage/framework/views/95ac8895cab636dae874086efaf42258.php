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
     <?php $__env->slot('header', null, []); ?> Dashboard Farmasi <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Farmasi Dashboard'],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Farmasi Dashboard'],
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $attributes = $__attributesOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__attributesOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $component = $__componentOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__componentOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Obat</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($statistics['total_items'])); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Stok Menipis</p>
            <p class="text-2xl font-bold text-amber-600 mt-1"><?php echo e($statistics['low_stock']); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Resep Pending</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e($statistics['pending_prescriptions']); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Selesai Hari Ini</p>
            <p class="text-2xl font-bold text-green-600 mt-1"><?php echo e($statistics['today_dispensed']); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Terverifikasi</p>
            <p class="text-2xl font-bold text-purple-600 mt-1"><?php echo e($statistics['verified_prescriptions']); ?></p>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Resep Menunggu</h3>
                <a href="<?php echo e(route('healthcare.pharmacy.prescriptions')); ?>"
                    class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
            </div>
            <div class="p-4 space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $pendingPrescriptionsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prescription): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p class="font-bold text-gray-900">
                                    <?php echo e($prescription->patient?->full_name ?? '-'); ?></p>
                                <p class="text-sm text-gray-600">
                                    <?php echo e($prescription->prescription_number ?? '-'); ?></p>
                            </div>
                            <span class="px-2 py-1 text-xs font-bold bg-blue-500 text-white rounded-lg">Pending</span>
                        </div>
                        <div class="text-xs text-gray-500 mb-2">
                            <p>Dokter: <?php echo e($prescription->doctor?->name ?? '-'); ?></p>
                            <p><?php echo e($prescription->created_at?->format('d M Y H:i') ?? '-'); ?></p>
                        </div>
                        <a href="<?php echo e(route('healthcare.pharmacy.prescriptions.show', $prescription)); ?>"
                            class="block w-full px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-center">
                            Proses Resep
                        </a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-center text-gray-500 py-4">Tidak ada resep pending</p>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Stok Menipis</h3>
                <a href="<?php echo e(route('healthcare.pharmacy.inventory')); ?>"
                    class="text-sm text-blue-600 hover:underline">Lihat Inventori</a>
            </div>
            <div class="p-4 space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $lowStockItemsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="font-bold text-gray-900"><?php echo e($item->name); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e($item->generic_name ?? '-'); ?> |
                                    <?php echo e($item->category ?? '-'); ?></p>
                            </div>
                            <span class="px-2 py-1 text-xs font-bold bg-amber-500 text-white rounded-lg">
                                <?php echo e($item->stock_quantity); ?> <?php echo e($item->unit ?? ''); ?>

                            </span>
                        </div>
                        <?php if($item->reorder_level > 0): ?>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-amber-500 h-2 rounded-full"
                                    style="width: <?php echo e(min(100, ($item->stock_quantity / $item->reorder_level) * 100)); ?>%">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Minimum: <?php echo e($item->reorder_level); ?>

                                <?php echo e($item->unit ?? ''); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-center text-gray-500 py-4">Semua stok aman</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Aktivitas Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3 text-left">Aktivitas</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Obat</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Petugas</th>
                        <th class="px-4 py-3 text-center">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $recentActivities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600">
                                <?php echo e($activity->created_at?->format('d M Y H:i') ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg
                                    <?php if(($activity->type ?? '') === 'dispensed'): ?> bg-green-100 text-green-700
                                    <?php elseif(($activity->type ?? '') === 'received'): ?> bg-blue-100 text-blue-700
                                    <?php elseif(($activity->type ?? '') === 'returned'): ?> bg-amber-100 text-amber-700
                                    <?php else: ?> bg-gray-100 text-gray-700 <?php endif; ?>">
                                    <?php echo e(ucfirst($activity->type ?? '-')); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-900 hidden md:table-cell">
                                <?php echo e($activity->item?->name ?? '-'); ?></td>
                            <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">
                                <?php echo e($activity->user?->name ?? '-'); ?></td>
                            <td class="px-4 py-3 text-center font-bold text-gray-900">
                                <?php echo e($activity->quantity ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                <p>Belum ada aktivitas</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\pharmacy\dashboard.blade.php ENDPATH**/ ?>