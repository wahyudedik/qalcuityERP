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
        <?php echo e(__('Laporan Konsumen Teratas')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e(__('Konsumen Teratas')); ?></h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><?php echo e(__('Identifikasi konsumen bandwidth tertinggi')); ?></p>
                </div>
                <a href="<?php echo e(route('telecom.reports.index')); ?>"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i><?php echo e(__('Kembali')); ?>

                </a>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="<?php echo e(route('telecom.reports.top-consumers')); ?>" class="flex flex-wrap gap-4 items-end">
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo e(__('Metrik')); ?></label>
                        <select name="metric" class="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="usage" <?php echo e(($filters['metric'] ?? 'usage') === 'usage' ? 'selected' : ''); ?>><?php echo e(__('Total Penggunaan')); ?></option>
                            <option value="download" <?php echo e(($filters['metric'] ?? '') === 'download' ? 'selected' : ''); ?>><?php echo e(__('Download')); ?></option>
                            <option value="upload" <?php echo e(($filters['metric'] ?? '') === 'upload' ? 'selected' : ''); ?>><?php echo e(__('Upload')); ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"><?php echo e(__('Jumlah')); ?></label>
                        <select name="limit" class="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="10" <?php echo e(($filters['limit'] ?? 20) == 10 ? 'selected' : ''); ?>>Top 10</option>
                            <option value="20" <?php echo e(($filters['limit'] ?? 20) == 20 ? 'selected' : ''); ?>>Top 20</option>
                            <option value="50" <?php echo e(($filters['limit'] ?? 20) == 50 ? 'selected' : ''); ?>>Top 50</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-filter mr-1"></i><?php echo e(__('Filter')); ?>

                        </button>
                        <a href="<?php echo e(route('telecom.reports.top-consumers')); ?>?export=excel&start_date=<?php echo e($filters['start_date'] instanceof \Carbon\Carbon ? $filters['start_date']->format('Y-m-d') : $filters['start_date']); ?>&end_date=<?php echo e($filters['end_date'] instanceof \Carbon\Carbon ? $filters['end_date']->format('Y-m-d') : $filters['end_date']); ?>"
                            class="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-file-excel mr-1"></i><?php echo e(__('Export Excel')); ?>

                        </a>
                    </div>
                </form>
            </div>

            <!-- Data Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e(__('Peringkat Konsumen Bandwidth')); ?></h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Pelanggan')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Paket')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Download')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Upload')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Total')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php $__empty_1 = true; $__currentLoopData = $report['consumers'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $consumer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm font-bold text-gray-500 dark:text-gray-400"><?php echo e($index + 1); ?></td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white"><?php echo e($consumer['customer_name'] ?? '-'); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400"><?php echo e($consumer['package_name'] ?? '-'); ?></td>
                                    <td class="px-6 py-4 text-sm text-blue-600 dark:text-blue-400"><?php echo e(number_format(($consumer['total_download'] ?? 0) / 1073741824, 2)); ?> GB</td>
                                    <td class="px-6 py-4 text-sm text-green-600 dark:text-green-400"><?php echo e(number_format(($consumer['total_upload'] ?? 0) / 1073741824, 2)); ?> GB</td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white"><?php echo e(number_format((($consumer['total_download'] ?? 0) + ($consumer['total_upload'] ?? 0)) / 1073741824, 2)); ?> GB</td>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\reports\top-consumers.blade.php ENDPATH**/ ?>