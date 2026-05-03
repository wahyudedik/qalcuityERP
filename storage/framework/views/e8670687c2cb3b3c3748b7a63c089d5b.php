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
     <?php $__env->slot('header', null, []); ?> 🐠 Pond Detail - <?php echo e($pond->code); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('fisheries.aquaculture.index')); ?>"
                class="px-3 py-1.5 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                ← Kembali
            </a>
    </div>

    <?php if(session('success')): ?>
        <div
            class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            <?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900"><?php echo e($pond->code); ?></h2>
                <p class="text-sm text-gray-500 mt-1"><?php echo e($pond->name); ?></p>
                <?php if($pond->location): ?>
                    <p class="text-xs text-gray-400 mt-1">📍 <?php echo e($pond->location); ?></p>
                <?php endif; ?>
            </div>
            <?php
                $statusColors = [
                    'active' => 'emerald',
                    'preparing' => 'blue',
                    'resting' => 'gray',
                    'maintenance' => 'yellow',
                ];
                $color = $statusColors[$pond->status] ?? 'gray';
            ?>
            <span
                class="px-3 py-1 text-sm rounded-full bg-<?php echo e($color); ?>-100 text-<?php echo e($color); ?>-700 $color }}-500/20 $color }}-400">
                <?php echo e($pond->status_label); ?>

            </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
            <div>
                <p class="text-xs text-gray-500">Luas Area</p>
                <p class="text-lg font-medium text-gray-900"><?php echo e(number_format($pond->area_size, 1)); ?>

                    m²</p>
            </div>
            <?php if($pond->depth): ?>
                <div>
                    <p class="text-xs text-gray-500">Kedalaman</p>
                    <p class="text-lg font-medium text-gray-900"><?php echo e(number_format($pond->depth, 1)); ?> m
                    </p>
                </div>
            <?php endif; ?>
            <?php if($pond->pond_type): ?>
                <div>
                    <p class="text-xs text-gray-500">Tipe Kolam</p>
                    <p class="text-lg font-medium text-gray-900">
                        <?php echo e(ucfirst(str_replace('_', ' ', $pond->pond_type))); ?></p>
                </div>
            <?php endif; ?>
            <?php if($pond->water_source): ?>
                <div>
                    <p class="text-xs text-gray-500">Sumber Air</p>
                    <p class="text-lg font-medium text-gray-900"><?php echo e(ucfirst($pond->water_source)); ?></p>
                </div>
            <?php endif; ?>
        </div>

        
        <?php if($pond->current_stock_species): ?>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-gray-700">Stok Saat Ini</p>
                    <span class="text-xs text-gray-500">Sejak
                        <?php echo e($pond->stocked_at?->format('d M Y') ?? '-'); ?></span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🐟</span>
                    <div>
                        <p class="text-base font-bold text-gray-900"><?php echo e($pond->current_stock_species); ?>

                        </p>
                        <p class="text-sm text-gray-600">
                            <?php echo e(number_format($pond->current_stock_count, 0)); ?> ekor</p>
                    </div>
                </div>
                <?php if($pond->utilization_percentage): ?>
                    <div class="mt-3">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-500">Utilisasi Kapasitas</span>
                            <span
                                class="font-medium text-gray-700"><?php echo e(number_format($pond->utilization_percentage, 1)); ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-cyan-600 h-2 rounded-full transition-all"
                                style="width: <?php echo e(min($pond->utilization_percentage, 100)); ?>%"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    
    <?php if($pond->latestWaterQuality): ?>
        <div
            class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl border border-blue-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-900">💧 Kualitas Air Terakhir</h3>
                <span
                    class="text-xs text-gray-500"><?php echo e($pond->latestWaterQuality->logged_at->format('d M Y, H:i')); ?></span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="text-center">
                    <p class="text-xs text-gray-500 mb-1">pH</p>
                    <p
                        class="text-2xl font-bold <?php echo e($pond->latestWaterQuality->ph >= 6.5 && $pond->latestWaterQuality->ph <= 8.5 ? 'text-green-600' : 'text-red-600'); ?>">
                        <?php echo e(number_format($pond->latestWaterQuality->ph, 1)); ?>

                    </p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 mb-1">Oksigen (mg/L)</p>
                    <p
                        class="text-2xl font-bold <?php echo e($pond->latestWaterQuality->dissolved_oxygen >= 5 ? 'text-green-600' : 'text-yellow-600'); ?>">
                        <?php echo e(number_format($pond->latestWaterQuality->dissolved_oxygen, 1)); ?>

                    </p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 mb-1">Suhu (°C)</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?php echo e(number_format($pond->latestWaterQuality->temperature ?? 0, 1)); ?>

                    </p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 mb-1">Amonia (mg/L)</p>
                    <p
                        class="text-2xl font-bold <?php echo e(($pond->latestWaterQuality->ammonia ?? 0) <= 0.02 ? 'text-green-600' : 'text-red-600'); ?>">
                        <?php echo e(number_format($pond->latestWaterQuality->ammonia ?? 0, 2)); ?>

                    </p>
                </div>
                <?php if($pond->latestWaterQuality->salinity): ?>
                    <div class="text-center">
                        <p class="text-xs text-gray-500 mb-1">Salinitas (ppt)</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php echo e(number_format($pond->latestWaterQuality->salinity, 1)); ?>

                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    
    <div
        class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900">Riwayat Kualitas Air</h2>
            <button onclick="document.getElementById('addWaterQualityModal').classList.remove('hidden')"
                class="px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                ➕ Log Baru
            </button>
        </div>

        <?php if($waterQualityLogs->isEmpty()): ?>
            <div class="p-12 text-center">
                <p class="text-4xl mb-3">💧</p>
                <p class="text-sm text-gray-500">Belum ada data kualitas air.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Waktu</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                pH</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Oksigen</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Suhu</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Amonia</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__currentLoopData = $waterQualityLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isGood =
                                    $log->ph >= 6.5 &&
                                    $log->ph <= 8.5 &&
                                    $log->dissolved_oxygen >= 5 &&
                                    ($log->ammonia ?? 0) <= 0.02;
                            ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                    <?php echo e($log->logged_at->format('d M Y, H:i')); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="font-medium <?php echo e($log->ph >= 6.5 && $log->ph <= 8.5 ? 'text-green-600' : 'text-red-600'); ?>">
                                        <?php echo e(number_format($log->ph, 1)); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                    <?php echo e(number_format($log->dissolved_oxygen, 1)); ?> mg/L
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                    <?php echo e(number_format($log->temperature ?? 0, 1)); ?>°C
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="<?php echo e(($log->ammonia ?? 0) <= 0.02 ? 'text-green-600' : 'text-red-600'); ?>">
                                        <?php echo e(number_format($log->ammonia ?? 0, 2)); ?> mg/L
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-xs rounded-full <?php echo e($isGood ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'); ?>">
                                        <?php echo e($isGood ? 'Baik' : 'Perlu Perhatian'); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($waterQualityLogs->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    
    <?php if($feedings->total() > 0): ?>
        <div
            class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900">Riwayat Pemberian Pakan</h2>
            </div>

            <div class="divide-y divide-gray-200">
                <?php $__currentLoopData = $feedings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feeding): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-6 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo e(number_format($feeding->feed_quantity, 2)); ?> kg
                                    <?php if($feeding->feed_type): ?>
                                        <span class="text-gray-500">-
                                            <?php echo e($feeding->feed_type); ?></span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php echo e($feeding->feeding_time->format('d M Y, H:i')); ?>

                                </p>
                            </div>
                            <?php if($feeding->feed_cost): ?>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-orange-600">Rp
                                        <?php echo e(number_format($feeding->feed_cost, 0, ',', '.')); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if($feeding->notes): ?>
                            <p class="text-xs text-gray-500 mt-2"><?php echo e($feeding->notes); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($feedings->links()); ?>

            </div>
        </div>
    <?php endif; ?>

    
    <div class="mt-6 flex gap-3">
        <button onclick="document.getElementById('addWaterQualityModal').classList.remove('hidden')"
            class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition flex items-center justify-center gap-2">
            <span>💧</span> Log Kualitas Air
        </button>
        <button onclick="document.getElementById('addFeedingModal').classList.remove('hidden')"
            class="flex-1 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition flex items-center justify-center gap-2">
            <span>🍽️</span> Catat Pakan
        </button>
    </div>

    
    <div id="addWaterQualityModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">💧 Log Kualitas Air</h3>
                <button onclick="document.getElementById('addWaterQualityModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('fisheries.aquaculture.log-water-quality', $pond->id)); ?>"
                class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; ?>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">pH *</label>
                        <input type="number" name="ph" required step="0.1" min="0" max="14"
                            placeholder="7.0" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Oksigen
                            Terlarut (mg/L) *</label>
                        <input type="number" name="dissolved_oxygen" required step="0.1" min="0"
                            placeholder="6.5" class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Suhu Air
                            (°C)</label>
                        <input type="number" name="temperature" step="0.1" placeholder="28.5"
                            class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Amonia
                            (mg/L)</label>
                        <input type="number" name="ammonia" step="0.01" min="0" placeholder="0.02"
                            class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Salinitas
                        (ppt)</label>
                    <input type="number" name="salinity" step="0.1" min="0" placeholder="15.0"
                        class="<?php echo e($cls); ?>">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        💾 Simpan
                    </button>
                    <button type="button"
                        onclick="document.getElementById('addWaterQualityModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="addFeedingModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-gray-900">Catat Pemberian Pakan</h2>
                <button onclick="document.getElementById('addFeedingModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('fisheries.aquaculture.log-feeding', $pond->id)); ?>"
                class="space-y-4">
                <?php echo csrf_field(); ?>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Pakan (kg)
                        *</label>
                    <input type="number" name="feed_quantity" required step="0.01" min="0"
                        placeholder="5.5" class="<?php echo e($cls); ?>">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Biaya Pakan
                        (Rp)</label>
                    <input type="number" name="feed_cost" step="100" min="0" placeholder="50000"
                        class="<?php echo e($cls); ?>">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Pakan</label>
                    <input type="text" name="feed_type" placeholder="Pelet 781-2" class="<?php echo e($cls); ?>">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Waktu pemberian, kondisi ikan, dll."
                        class="<?php echo e($cls); ?>"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                        💾 Simpan
                    </button>
                    <button type="button"
                        onclick="document.getElementById('addFeedingModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg transition">
                        Batal
                    </button>
                </div>
            </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\fisheries\aquaculture-detail.blade.php ENDPATH**/ ?>