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
        <?php echo e(__('Laporan Pendapatan per Paket')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e(__('Pendapatan per Paket')); ?></h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><?php echo e(__('Analisis distribusi pendapatan dari paket internet')); ?></p>
                </div>
                <a href="<?php echo e(route('telecom.reports.index')); ?>"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i><?php echo e(__('Kembali')); ?>

                </a>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="<?php echo e(route('telecom.reports.revenue-by-package')); ?>" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo e(__('Dari Tanggal')); ?></label>
                        <input type="date" name="start_date" value="<?php echo e($filters['start_date'] instanceof \Carbon\Carbon ? $filters['start_date']->format('Y-m-d') : $filters['start_date']); ?>"
                            class="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo e(__('Sampai Tanggal')); ?></label>
                        <input type="date" name="end_date" value="<?php echo e($filters['end_date'] instanceof \Carbon\Carbon ? $filters['end_date']->format('Y-m-d') : $filters['end_date']); ?>"
                            class="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-filter mr-1"></i><?php echo e(__('Filter')); ?>

                        </button>
                        <a href="<?php echo e(route('telecom.reports.revenue-by-package')); ?>?export=excel&start_date=<?php echo e($filters['start_date'] instanceof \Carbon\Carbon ? $filters['start_date']->format('Y-m-d') : $filters['start_date']); ?>&end_date=<?php echo e($filters['end_date'] instanceof \Carbon\Carbon ? $filters['end_date']->format('Y-m-d') : $filters['end_date']); ?>"
                            class="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-file-excel mr-1"></i><?php echo e(__('Export Excel')); ?>

                        </a>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <?php if(isset($report['summary'])): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                        <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e(__('Total Pendapatan')); ?></p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($report['summary']['total_revenue'] ?? 0, 0, ',', '.')); ?></p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 border-l-4 border-blue-500">
                        <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e(__('Total Subscription Aktif')); ?></p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e(number_format($report['summary']['total_subscriptions'] ?? 0)); ?></p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 border-l-4 border-purple-500">
                        <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e(__('Jumlah Paket')); ?></p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e(count($report['packages'] ?? [])); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Report Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e(__('Detail per Paket')); ?></h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Paket')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Kecepatan')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Harga')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Subscription Aktif')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Total Pendapatan')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('% Kontribusi')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php $__empty_1 = true; $__currentLoopData = $report['packages'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white"><?php echo e($pkg['name'] ?? '-'); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400"><?php echo e($pkg['speed'] ?? '-'); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">Rp <?php echo e(number_format($pkg['price'] ?? 0, 0, ',', '.')); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white"><?php echo e(number_format($pkg['active_subscriptions'] ?? 0)); ?></td>
                                    <td class="px-6 py-4 text-sm font-semibold text-green-600 dark:text-green-400">Rp <?php echo e(number_format($pkg['total_revenue'] ?? 0, 0, ',', '.')); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white"><?php echo e(number_format($pkg['revenue_percentage'] ?? 0, 1)); ?>%</td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400"><?php echo e(__('Tidak ada data untuk periode ini')); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\reports\revenue-by-package.blade.php ENDPATH**/ ?>