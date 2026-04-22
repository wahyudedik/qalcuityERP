
<?php $__env->startSection('title', 'Resep Margin Rendah'); ?>
<?php $__env->startSection('content'); ?>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="<?php echo e(route('fnb.recipes.index')); ?>"
                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm transition-colors">
                ← Kembali ke Kalkulator Biaya Resep
            </a>
        </div>

        <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Resep Margin Rendah</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Resep dengan margin keuntungan di bawah threshold</p>
            </div>
        </div>

        <!-- Filter -->
        <form method="GET" class="mb-6 flex items-end gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Threshold Margin (%)</label>
                <input type="number" name="threshold" value="<?php echo e($threshold); ?>" min="0" max="100"
                    class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 w-24">
            </div>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors min-h-[38px]">
                Filter
            </button>
        </form>

        <?php if($lowMarginRecipes->isEmpty()): ?>
            <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-6 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-green-700 dark:text-green-300 font-medium">
                        Semua resep memiliki margin di atas <?php echo e($threshold); ?>%. Bagus!
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6 rounded">
                <p class="text-red-700 dark:text-red-300 font-medium">
                    ⚠️ <?php echo e($lowMarginRecipes->count()); ?> resep memiliki margin keuntungan di bawah <?php echo e($threshold); ?>%
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Resep</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Menu Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Harga Jual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Biaya per Porsi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Margin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php $__currentLoopData = $lowMarginRecipes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $recipe = $item['recipe'] ?? null;
                                    $margin = $item['margin_percentage'] ?? $item['profit_margin'] ?? 0;
                                    $sellingPrice = $item['selling_price'] ?? ($recipe?->menuItem?->price ?? 0);
                                    $costPerServing = $item['cost_per_serving'] ?? 0;
                                ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        <?php echo e($recipe?->name ?? ($item['name'] ?? '-')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        <?php echo e($recipe?->menuItem?->name ?? '-'); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        Rp <?php echo e(number_format($sellingPrice, 0, ',', '.')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        Rp <?php echo e(number_format($costPerServing, 0, ',', '.')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full font-semibold
                                            <?php echo e($margin < 0 ? 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300' : 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300'); ?>">
                                            <?php echo e(round($margin, 1)); ?>%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if($recipe): ?>
                                            <a href="<?php echo e(route('fnb.recipes.calculate', $recipe)); ?>"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                                                Analisis →
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/fnb/recipes/low-margin.blade.php ENDPATH**/ ?>