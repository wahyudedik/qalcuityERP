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
        <?php echo e(__('Laporan Utilisasi Bandwidth')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Utilisasi Bandwidth')); ?></h1>
                    <p class="mt-1 text-sm text-gray-600"><?php echo e(__('Tren konsumsi bandwidth dan penggunaan per perangkat')); ?></p>
                </div>
                <a href="<?php echo e(route('telecom.reports.index')); ?>"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i><?php echo e(__('Kembali')); ?>

                </a>
            </div>

            <!-- Filters -->
            <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="<?php echo e(route('telecom.reports.bandwidth-utilization')); ?>" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Dari Tanggal')); ?></label>
                        <input type="date" name="start_date" value="<?php echo e($filters['start_date'] instanceof \Carbon\Carbon ? $filters['start_date']->format('Y-m-d') : $filters['start_date']); ?>"
                            class="rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Sampai Tanggal')); ?></label>
                        <input type="date" name="end_date" value="<?php echo e($filters['end_date'] instanceof \Carbon\Carbon ? $filters['end_date']->format('Y-m-d') : $filters['end_date']); ?>"
                            class="rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Kelompokkan')); ?></label>
                        <select name="group_by" class="rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="daily" <?php echo e(($filters['group_by'] ?? 'daily') === 'daily' ? 'selected' : ''); ?>><?php echo e(__('Harian')); ?></option>
                            <option value="weekly" <?php echo e(($filters['group_by'] ?? '') === 'weekly' ? 'selected' : ''); ?>><?php echo e(__('Mingguan')); ?></option>
                            <option value="monthly" <?php echo e(($filters['group_by'] ?? '') === 'monthly' ? 'selected' : ''); ?>><?php echo e(__('Bulanan')); ?></option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-filter mr-1"></i><?php echo e(__('Filter')); ?>

                        </button>
                        <a href="<?php echo e(route('telecom.reports.bandwidth-utilization')); ?>?export=excel&start_date=<?php echo e($filters['start_date'] instanceof \Carbon\Carbon ? $filters['start_date']->format('Y-m-d') : $filters['start_date']); ?>&end_date=<?php echo e($filters['end_date'] instanceof \Carbon\Carbon ? $filters['end_date']->format('Y-m-d') : $filters['end_date']); ?>"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-file-excel mr-1"></i><?php echo e(__('Export Excel')); ?>

                        </a>
                    </div>
                </form>
            </div>

            <!-- Summary -->
            <?php if(isset($report['summary'])): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-4 border-l-4 border-blue-500">
                        <p class="text-sm text-gray-600"><?php echo e(__('Total Download')); ?></p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo e(number_format(($report['summary']['total_download'] ?? 0) / 1073741824, 2)); ?> GB</p>
                    </div>
                    <div class="bg-white shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                        <p class="text-sm text-gray-600"><?php echo e(__('Total Upload')); ?></p>
                        <p class="text-2xl font-bold text-green-600"><?php echo e(number_format(($report['summary']['total_upload'] ?? 0) / 1073741824, 2)); ?> GB</p>
                    </div>
                    <div class="bg-white shadow-sm sm:rounded-lg p-4 border-l-4 border-purple-500">
                        <p class="text-sm text-gray-600"><?php echo e(__('Total Penggunaan')); ?></p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo e(number_format((($report['summary']['total_download'] ?? 0) + ($report['summary']['total_upload'] ?? 0)) / 1073741824, 2)); ?> GB</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Data Table -->
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900"><?php echo e(__('Data Penggunaan Bandwidth')); ?></h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('Periode')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('Download')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('Upload')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('Total')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__empty_1 = true; $__currentLoopData = $report['data'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900"><?php echo e($row['period'] ?? '-'); ?></td>
                                    <td class="px-6 py-4 text-sm text-blue-600"><?php echo e(number_format(($row['total_download'] ?? 0) / 1073741824, 2)); ?> GB</td>
                                    <td class="px-6 py-4 text-sm text-green-600"><?php echo e(number_format(($row['total_upload'] ?? 0) / 1073741824, 2)); ?> GB</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900"><?php echo e(number_format((($row['total_download'] ?? 0) + ($row['total_upload'] ?? 0)) / 1073741824, 2)); ?> GB</td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500"><?php echo e(__('Tidak ada data untuk periode ini')); ?></td>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\reports\bandwidth-utilization.blade.php ENDPATH**/ ?>