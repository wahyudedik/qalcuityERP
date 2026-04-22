
<?php $__env->startSection('title', 'Pemborosan per Item'); ?>
<?php $__env->startSection('content'); ?>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="<?php echo e(route('fnb.waste.index')); ?>"
                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm transition-colors">
                ← Kembali ke Pelacakan Pemborosan
            </a>
        </div>

        <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Pemborosan per Item</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Analisis item dengan pemborosan tertinggi</p>
            </div>
        </div>

        <!-- Filter -->
        <form method="GET" class="mb-6 flex items-end gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Periode (hari)</label>
                <select name="days"
                    class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="7" <?php echo e($daysBack == 7 ? 'selected' : ''); ?>>7 hari terakhir</option>
                    <option value="30" <?php echo e($daysBack == 30 ? 'selected' : ''); ?>>30 hari terakhir</option>
                    <option value="90" <?php echo e($daysBack == 90 ? 'selected' : ''); ?>>90 hari terakhir</option>
                </select>
            </div>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors min-h-[38px]">
                Filter
            </button>
        </form>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Top Item Terbuang (<?php echo e($daysBack); ?> hari terakhir)
                </h2>
            </div>
            <?php if(empty($wasteByItem) || count($wasteByItem) === 0): ?>
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    Tidak ada data pemborosan untuk periode ini
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Frekuensi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Terbuang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Biaya</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php $__currentLoopData = $wasteByItem; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        <?php echo e($item['item_name'] ?? $item->item_name ?? '-'); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        <?php echo e($item['occurrences'] ?? $item['count'] ?? 0); ?>x
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        <?php echo e(number_format($item['total_quantity'] ?? $item->total_quantity ?? 0, 2)); ?>

                                        <?php echo e($item['unit'] ?? $item->unit ?? ''); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600 dark:text-red-400">
                                        Rp <?php echo e(number_format($item['total_cost'] ?? $item->total_cost ?? 0, 0, ',', '.')); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\fnb\waste\by-item.blade.php ENDPATH**/ ?>