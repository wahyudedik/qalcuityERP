
<?php $__env->startSection('title', 'Biaya Resep — ' . $recipe->name); ?>
<?php $__env->startSection('content'); ?>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="<?php echo e(route('fnb.recipes.index')); ?>"
                class="text-blue-600 hover:text-blue-800 text-sm transition-colors">
                ← Kembali ke Daftar Resep
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2"><?php echo e($recipe->name); ?></h1>
            <?php if($recipe->description): ?>
                <p class="mt-1 text-sm text-gray-600"><?php echo e($recipe->description); ?></p>
            <?php endif; ?>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded text-green-700">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 border border-gray-200">
                <div class="text-sm text-gray-500">Total Biaya</div>
                <div class="text-2xl font-bold text-gray-900">
                    Rp <?php echo e(number_format($costData['total_cost'] ?? 0, 0, ',', '.')); ?>

                </div>
            </div>
            <div class="bg-blue-50 rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="text-sm text-blue-600">Biaya per Porsi</div>
                <div class="text-2xl font-bold text-blue-700">
                    Rp <?php echo e(number_format($costData['cost_per_serving'] ?? 0, 0, ',', '.')); ?>

                </div>
            </div>
            <div class="bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="text-sm text-green-600">Harga Jual</div>
                <div class="text-2xl font-bold text-green-700">
                    Rp <?php echo e(number_format($costData['profit_margin']['selling_price'] ?? 0, 0, ',', '.')); ?>

                </div>
            </div>
            <?php
                $marginPct = $costData['profit_margin']['margin_percentage'] ?? 0;
                $marginGood = $marginPct >= 30;
            ?>
            <div class="<?php echo e($marginGood ? 'bg-purple-50 border-purple-500' : 'bg-red-50 border-red-500'); ?> rounded-lg shadow p-4 border-l-4">
                <div class="text-sm <?php echo e($marginGood ? 'text-purple-600' : 'text-red-600'); ?>">
                    Margin Keuntungan
                </div>
                <div class="text-2xl font-bold <?php echo e($marginGood ? 'text-purple-700' : 'text-red-700'); ?>">
                    <?php echo e($marginPct); ?>%
                </div>
            </div>
        </div>

        <!-- Ingredients Breakdown -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6 border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900">Rincian Bahan</h2>
                <span class="text-sm text-gray-500">
                    <?php echo e(count($costData['ingredients'] ?? [])); ?> bahan
                </span>
            </div>
            <?php if(empty($costData['ingredients'])): ?>
                <div class="text-center py-8 text-gray-500">
                    Belum ada bahan dalam resep ini
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bahan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga/Satuan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% Biaya</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $costData['ingredients']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ingredient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo e($ingredient['name'] ?? '-'); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <?php echo e($ingredient['quantity'] ?? 0); ?> <?php echo e($ingredient['unit'] ?? ''); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        Rp <?php echo e(number_format($ingredient['cost_per_unit'] ?? 0, 0, ',', '.')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        Rp <?php echo e(number_format($ingredient['line_total'] ?? 0, 0, ',', '.')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full"
                                                    style="width: <?php echo e(min($ingredient['percentage'] ?? 0, 100)); ?>%"></div>
                                            </div>
                                            <span class="text-xs text-gray-600"><?php echo e($ingredient['percentage'] ?? 0); ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Profit Analysis -->
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Analisis Keuntungan</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                    <span class="text-gray-700">Harga Jual per Porsi</span>
                    <span class="font-semibold text-gray-900">
                        Rp <?php echo e(number_format($costData['profit_margin']['selling_price'] ?? 0, 0, ',', '.')); ?>

                    </span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                    <span class="text-gray-700">Biaya per Porsi</span>
                    <span class="font-semibold text-gray-900">
                        Rp <?php echo e(number_format($costData['profit_margin']['cost_per_serving'] ?? 0, 0, ',', '.')); ?>

                    </span>
                </div>
                <?php $isProfitable = $costData['profit_margin']['is_profitable'] ?? false; ?>
                <div class="flex justify-between items-center p-3 <?php echo e($isProfitable ? 'bg-green-50' : 'bg-red-50'); ?> rounded">
                    <span class="font-medium text-gray-700">Keuntungan per Porsi</span>
                    <span class="font-bold <?php echo e($isProfitable ? 'text-green-700' : 'text-red-700'); ?>">
                        Rp <?php echo e(number_format($costData['profit_margin']['profit_per_serving'] ?? 0, 0, ',', '.')); ?>

                    </span>
                </div>
                <div class="flex justify-between items-center p-3 <?php echo e($marginGood ? 'bg-green-50' : 'bg-yellow-50'); ?> rounded">
                    <span class="font-medium text-gray-700">Persentase Margin</span>
                    <span class="font-bold <?php echo e($marginGood ? 'text-green-700' : 'text-yellow-700'); ?>">
                        <?php echo e($marginPct); ?>%
                    </span>
                </div>
            </div>

            <?php if(!$isProfitable): ?>
                <div class="mt-4 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                    <p class="text-red-700 font-medium">
                        ⚠️ Resep ini merugi! Pertimbangkan untuk menaikkan harga jual atau mengurangi biaya bahan.
                    </p>
                </div>
            <?php elseif(!$marginGood): ?>
                <div class="mt-4 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded">
                    <p class="text-yellow-700 font-medium">
                        ⚠️ Margin keuntungan di bawah 30%. Pertimbangkan untuk mengoptimalkan biaya bahan.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\fnb\recipes\calculate.blade.php ENDPATH**/ ?>