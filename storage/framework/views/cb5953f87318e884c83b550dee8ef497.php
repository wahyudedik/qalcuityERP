
<?php $__env->startSection('title', 'Pelacakan Pemborosan Bahan'); ?>
<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Pelacakan Pemborosan Bahan</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Pantau dan kurangi pemborosan bahan baku</p>
            </div>
            <button onclick="document.getElementById('wasteModal').classList.remove('hidden')"
                class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors min-h-[44px]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Catat Pemborosan
            </button>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 rounded text-green-700 dark:text-green-300">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <!-- Date Filter -->
        <form method="GET" class="mb-6 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="<?php echo e($startDate->format('Y-m-d')); ?>"
                    class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="<?php echo e($endDate->format('Y-m-d')); ?>"
                    class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors min-h-[38px]">
                Filter
            </button>
        </form>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Biaya Pemborosan</div>
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">Rp <?php echo e(number_format($stats['total_waste_cost'] ?? 0, 0, ',', '.')); ?></div>
            </div>
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg shadow p-4 border-l-4 border-yellow-500">
                <div class="text-sm text-yellow-600 dark:text-yellow-400">Item Terbuang</div>
                <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-300"><?php echo e($stats['total_items_wasted'] ?? 0); ?></div>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="text-sm text-blue-600 dark:text-blue-400">Rata-rata Harian</div>
                <div class="text-xl font-bold text-blue-700 dark:text-blue-300">Rp <?php echo e(number_format($stats['daily_average'] ?? 0, 0, ',', '.')); ?></div>
            </div>
            <?php
                $trendDir = $trends['trend_direction'] ?? 'stable';
                $trendBg = $trendDir === 'decreasing' ? 'bg-green-50 dark:bg-green-900/20 border-green-500' : ($trendDir === 'increasing' ? 'bg-red-50 dark:bg-red-900/20 border-red-500' : 'bg-gray-50 dark:bg-gray-800 border-gray-500');
                $trendText = $trendDir === 'decreasing' ? 'text-green-600 dark:text-green-400' : ($trendDir === 'increasing' ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400');
                $trendLabel = ['decreasing' => 'Menurun', 'increasing' => 'Meningkat', 'stable' => 'Stabil'][$trendDir] ?? ucfirst($trendDir);
            ?>
            <div class="<?php echo e($trendBg); ?> rounded-lg shadow p-4 border-l-4">
                <div class="text-sm <?php echo e($trendText); ?>">Tren</div>
                <div class="text-xl font-bold <?php echo e($trendText); ?>"><?php echo e($trendLabel); ?></div>
            </div>
        </div>

        <?php if(!empty($recommendations)): ?>
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-4 mb-6 rounded">
                <h3 class="font-semibold text-yellow-800 dark:text-yellow-300 mb-2">Rekomendasi untuk Mengurangi Pemborosan:</h3>
                <ul class="space-y-2">
                    <?php $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex flex-wrap items-start gap-2">
                            <span
                                class="px-2 py-0.5 text-xs rounded <?php echo e(($rec['priority'] ?? '') === 'high' ? 'bg-red-600 text-white' : 'bg-yellow-600 text-white'); ?>">
                                <?php echo e(($rec['priority'] ?? '') === 'high' ? 'TINGGI' : 'SEDANG'); ?>

                            </span>
                            <span class="text-sm text-gray-700 dark:text-gray-300 flex-1"><?php echo e($rec['message'] ?? ''); ?></span>
                            <?php if(!empty($rec['potential_savings'])): ?>
                                <span class="text-xs text-green-700 dark:text-green-400 font-medium">
                                    Potensi hemat: Rp <?php echo e(number_format($rec['potential_savings'], 0, ',', '.')); ?>

                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Quick Links -->
        <div class="flex flex-wrap gap-3 mb-6">
            <a href="<?php echo e(route('fnb.waste.by-item')); ?>"
                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 underline transition-colors">
                Laporan per Item →
            </a>
            <a href="<?php echo e(route('fnb.waste.reasons')); ?>"
                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 underline transition-colors">
                Analisis Penyebab →
            </a>
            <a href="<?php echo e(route('fnb.waste.export', request()->query())); ?>"
                class="text-sm text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 underline transition-colors">
                Export Laporan →
            </a>
        </div>

        <!-- Recent Wastes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Catatan Pemborosan Terbaru</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Biaya</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Departemen</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php $__empty_1 = true; $__currentLoopData = $recentWastes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $waste): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    <?php echo e($waste->wasted_at?->format('d M Y H:i') ?? '-'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100"><?php echo e($waste->item_name); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    <?php echo e($waste->quantity_wasted); ?> <?php echo e($waste->unit); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600 dark:text-red-400">
                                    Rp <?php echo e(number_format($waste->total_waste_cost ?? 0, 0, ',', '.')); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                        <?php echo e($waste->getWasteTypeLabel()); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize text-gray-700 dark:text-gray-300">
                                    <?php switch($waste->department):
                                        case ('kitchen'): ?> Dapur <?php break; ?>
                                        <?php case ('bar'): ?> Bar <?php break; ?>
                                        <?php case ('storage'): ?> Gudang <?php break; ?>
                                        <?php default: ?> <?php echo e($waste->department); ?>

                                    <?php endswitch; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <form action="<?php echo e(route('fnb.waste.destroy', $waste)); ?>" method="POST" class="inline"
                                        onsubmit="return confirm('Hapus catatan pemborosan ini?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit"
                                            class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-xs transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada catatan pemborosan
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Record Waste Modal -->
    <div id="wasteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4 max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Catat Pemborosan Bahan</h2>
                <button type="button" onclick="document.getElementById('wasteModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="<?php echo e(route('fnb.waste.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Item</label>
                        <input type="text" name="item_name" required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nama bahan yang terbuang">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah</label>
                            <input type="number" name="quantity_wasted" required step="0.001" min="0.001"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="0.000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Satuan</label>
                            <input type="text" name="unit" required placeholder="kg, pcs, liter"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga per Satuan (Rp)</label>
                        <input type="number" name="cost_per_unit" required step="0.01" min="0"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Pemborosan</label>
                        <select name="waste_type" required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="spoilage">Spoilage/Rusak</option>
                            <option value="over_production">Over Production</option>
                            <option value="preparation_error">Kesalahan Persiapan</option>
                            <option value="expired">Kadaluarsa</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alasan</label>
                        <textarea name="reason" rows="2"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Jelaskan penyebab pemborosan..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Departemen</label>
                        <select name="department" required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Departemen --</option>
                            <option value="kitchen">Dapur</option>
                            <option value="bar">Bar</option>
                            <option value="storage">Gudang</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('wasteModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors min-h-[44px]">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors min-h-[44px]">
                        Catat Pemborosan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('wasteModal').addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') document.getElementById('wasteModal').classList.add('hidden');
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\fnb\waste\index.blade.php ENDPATH**/ ?>