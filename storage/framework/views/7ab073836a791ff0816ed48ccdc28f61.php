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
     <?php $__env->slot('header', null, []); ?> Valuasi Inventori <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('inventory.costing.cogs')); ?>" class="text-xs text-gray-500 hover:text-blue-500">Laporan COGS →</a>
    </div>

    <div class="space-y-6">

        
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <p class="font-semibold text-gray-900">Metode Kalkulasi Biaya</p>
                    <p class="text-xs text-gray-500 mt-0.5">Berlaku untuk semua perhitungan HPP dan valuasi stok</p>
                </div>
                <form method="POST" action="<?php echo e(route('inventory.costing.method')); ?>" class="flex items-center gap-3">
                    <?php echo csrf_field(); ?>
                    <select name="costing_method" onchange="this.form.submit()"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="simple" <?php if($tenant->costing_method === 'simple'): echo 'selected'; endif; ?>>Simple (Harga Beli Tetap)</option>
                        <option value="avco"   <?php if($tenant->costing_method === 'avco'): echo 'selected'; endif; ?>>AVCO (Rata-rata Tertimbang)</option>
                        <option value="fifo"   <?php if($tenant->costing_method === 'fifo'): echo 'selected'; endif; ?>>FIFO (Masuk Pertama, Keluar Pertama)</option>
                    </select>
                </form>
            </div>
            <?php
                $methodDesc = [
                    'simple' => 'Menggunakan harga beli tetap dari master produk. Cocok untuk UMKM dan bisnis dengan harga beli stabil.',
                    'avco'   => 'Harga pokok dihitung dari rata-rata tertimbang semua pembelian. Sesuai PSAK 14, cocok untuk sebagian besar industri.',
                    'fifo'   => 'Barang yang masuk pertama dikeluarkan pertama. Cocok untuk produk dengan expiry date (makanan, farmasi, FMCG).',
                ];
            ?>
            <p class="mt-3 text-xs text-blue-600 bg-blue-50 border border-blue-200 rounded-xl px-3 py-2">
                <?php echo e($methodDesc[$tenant->costing_method] ?? ''); ?>

            </p>
        </div>

        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500">Total Nilai Stok</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                    Rp <?php echo e(number_format($report['total'], 0, ',', '.')); ?>

                </p>
                <p class="text-xs text-gray-400 mt-1">Metode: <?php echo e(strtoupper($report['method'])); ?></p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500">Jumlah SKU</p>
                <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e(count($report['rows'])); ?></p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500">Rata-rata Nilai/SKU</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                    Rp <?php echo e(count($report['rows']) > 0 ? number_format($report['total'] / count($report['rows']), 0, ',', '.') : '0'); ?>

                </p>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">Detail Valuasi per Produk</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Produk</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">SKU</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Gudang</th>
                            <th class="px-4 py-3 text-right">Qty</th>
                            <th class="px-4 py-3 text-right">Harga Beli</th>
                            <th class="px-4 py-3 text-right">HPP/Unit</th>
                            <th class="px-4 py-3 text-right">Total Nilai</th>
                            <th class="px-4 py-3 text-center hidden lg:table-cell">Selisih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $report['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $diff = $row['unit_cost'] - $row['price_buy'];
                            $diffPct = $row['price_buy'] > 0 ? ($diff / $row['price_buy']) * 100 : 0;
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($row['product_name']); ?></td>
                            <td class="px-4 py-3 text-gray-500 font-mono text-xs hidden sm:table-cell"><?php echo e($row['sku'] ?? '—'); ?></td>
                            <td class="px-4 py-3 text-gray-500 text-xs hidden md:table-cell"><?php echo e($row['warehouse_name']); ?></td>
                            <td class="px-4 py-3 text-right text-gray-900"><?php echo e(number_format($row['quantity'], 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right text-gray-500">Rp <?php echo e(number_format($row['price_buy'], 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp <?php echo e(number_format($row['unit_cost'], 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right font-semibold text-blue-600">Rp <?php echo e(number_format($row['total_value'], 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell">
                                <?php if(abs($diffPct) < 0.1): ?>
                                <span class="text-xs text-gray-400">—</span>
                                <?php elseif($diff > 0): ?>
                                <span class="text-xs text-amber-600">+<?php echo e(number_format($diffPct, 1)); ?>%</span>
                                <?php else: ?>
                                <span class="text-xs text-green-600"><?php echo e(number_format($diffPct, 1)); ?>%</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8" class="px-6 py-10 text-center text-gray-400">Belum ada data stok.</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if(count($report['rows']) > 0): ?>
                    <tfoot class="bg-gray-50 font-semibold text-sm">
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-right text-gray-700">Total Nilai Inventori</td>
                            <td class="px-4 py-3 text-right text-blue-600">Rp <?php echo e(number_format($report['total'], 0, ',', '.')); ?></td>
                            <td class="hidden lg:table-cell"></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <?php if($tenant->costing_method === 'simple'): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-700">
            <strong>Mode Simple:</strong> Nilai stok dihitung dari harga beli tetap di master produk. Untuk akurasi HPP yang lebih baik, pertimbangkan beralih ke AVCO atau FIFO.
        </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\inventory\costing\valuation.blade.php ENDPATH**/ ?>