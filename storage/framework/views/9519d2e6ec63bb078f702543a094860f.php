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
     <?php $__env->slot('header', null, []); ?> 
        <?php echo e(__('Detail Paket Internet')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($package->name); ?></h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><?php echo e(__('Detail paket internet')); ?></p>
                </div>
                <div class="flex gap-2">
                    <a href="<?php echo e(route('telecom.packages.edit', $package)); ?>"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-700">
                        <i class="fas fa-edit mr-2"></i><?php echo e(__('Edit')); ?>

                    </a>
                    <a href="<?php echo e(route('telecom.packages.index')); ?>"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i><?php echo e(__('Kembali')); ?>

                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Package Info Card -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 p-6 text-white">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-xl font-bold"><?php echo e($package->name); ?></h3>
                                    <p class="text-blue-100 dark:text-blue-200 text-sm mt-1"><?php echo e(ucfirst($package->billing_cycle ?? 'monthly')); ?></p>
                                </div>
                                <?php if($package->is_active): ?>
                                    <span class="px-2 py-1 text-xs bg-green-500 dark:bg-green-600 rounded-full"><?php echo e(__('Aktif')); ?></span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs bg-gray-500 dark:bg-gray-600 rounded-full"><?php echo e(__('Nonaktif')); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-4">
                                <p class="text-3xl font-bold">Rp <?php echo e(number_format($package->price, 0, ',', '.')); ?></p>
                                <p class="text-blue-100 text-sm">/<?php echo e(__('bulan')); ?></p>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400 text-sm"><?php echo e(__('Download')); ?></span>
                                <span class="font-bold text-gray-900 dark:text-white"><?php echo e($package->download_speed_mbps); ?> Mbps</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400 text-sm"><?php echo e(__('Upload')); ?></span>
                                <span class="font-bold text-gray-900 dark:text-white"><?php echo e($package->upload_speed_mbps); ?> Mbps</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400 text-sm"><?php echo e(__('Kuota')); ?></span>
                                <span class="font-bold text-gray-900 dark:text-white">
                                    <?php if($package->isUnlimited()): ?>
                                        <span class="text-blue-600 dark:text-blue-400">∞ <?php echo e(__('Unlimited')); ?></span>
                                    <?php else: ?>
                                        <?php echo e(number_format($package->quota_bytes / 1073741824, 0)); ?> GB
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php if($package->installation_fee > 0): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400 text-sm"><?php echo e(__('Biaya Pasang')); ?></span>
                                    <span class="font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($package->installation_fee, 0, ',', '.')); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if($package->description): ?>
                                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e($package->description); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Subscriptions List -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                <?php echo e(__('Subscription Aktif')); ?>

                                <span class="ml-2 px-2 py-0.5 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 rounded-full">
                                    <?php echo e($package->subscriptions->count()); ?>

                                </span>
                            </h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"><?php echo e(__('Pelanggan')); ?></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"><?php echo e(__('Status')); ?></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"><?php echo e(__('Mulai')); ?></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"><?php echo e(__('Aksi')); ?></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php $__empty_1 = true; $__currentLoopData = $package->subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($sub->customer?->name ?? '-'); ?></div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($sub->customer?->email ?? ''); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    <?php echo e($sub->status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : ($sub->status === 'suspended' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400')); ?>">
                                                    <?php echo e(ucfirst($sub->status)); ?>

                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo e($sub->started_at?->format('d M Y') ?? $sub->activated_at?->format('d M Y') ?? '-'); ?>

                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <a href="<?php echo e(route('telecom.subscriptions.show', $sub)); ?>"
                                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                                    <?php echo e(__('Lihat')); ?>

                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                                <?php echo e(__('Belum ada subscription untuk paket ini')); ?>

                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\packages\show.blade.php ENDPATH**/ ?>