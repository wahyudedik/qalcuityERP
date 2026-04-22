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
        <div class="flex items-center justify-between">
            <span>❄️ Cold Storage Detail - <?php echo e($unit->unit_code); ?></span>
            <a href="<?php echo e(route('fisheries.cold-chain.index')); ?>"
                class="px-3 py-1.5 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                ← Kembali
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
        <div
            class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">
            <?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($unit->unit_code); ?></h2>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1"><?php echo e($unit->name); ?></p>
                <?php if($unit->location): ?>
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">📍 <?php echo e($unit->location); ?></p>
                <?php endif; ?>
            </div>
            <div class="text-right">
                <span
                    class="inline-block px-3 py-1 text-sm rounded-full <?php echo e($unit->isTemperatureSafe() ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400'); ?>">
                    <?php echo e($unit->isTemperatureSafe() ? '✅ Suhu Normal' : '⚠️ Warning'); ?>

                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100 dark:border-white/5">
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Suhu Saat Ini</p>
                <p class="text-3xl font-bold <?php echo e($unit->isTemperatureSafe() ? 'text-green-600' : 'text-red-600'); ?>">
                    <?php echo e($unit->current_temperature ? number_format($unit->current_temperature, 1) . '°C' : 'N/A'); ?>

                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Range Aman</p>
                <p class="text-lg font-medium text-gray-700 dark:text-slate-300">
                    <?php echo e(number_format($unit->min_temperature, 1)); ?>° - <?php echo e(number_format($unit->max_temperature, 1)); ?>°C
                </p>
            </div>
            <?php if($unit->capacity_kg): ?>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Kapasitas</p>
                    <p class="text-lg font-medium text-gray-700 dark:text-slate-300">
                        <?php echo e(number_format($unit->capacity_kg, 0)); ?> kg</p>
                </div>
            <?php endif; ?>
            <?php if($unit->utilization_percentage): ?>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Utilisasi</p>
                    <p class="text-lg font-medium text-gray-700 dark:text-slate-300">
                        <?php echo e(number_format($unit->utilization_percentage, 1)); ?>%</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if($unit->description): ?>
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/5">
                <p class="text-sm text-gray-600 dark:text-slate-400"><?php echo e($unit->description); ?></p>
            </div>
        <?php endif; ?>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Riwayat Suhu (24 Jam Terakhir)</h2>
            <form class="flex items-center gap-2">
                <select name="period" onchange="this.form.submit()"
                    class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="24h" <?php if(request('period', '24h') === '24h'): echo 'selected'; endif; ?>>24 Jam</option>
                    <option value="7d" <?php if(request('period') === '7d'): echo 'selected'; endif; ?>>7 Hari</option>
                    <option value="30d" <?php if(request('period') === '30d'): echo 'selected'; endif; ?>>30 Hari</option>
                </select>
            </form>
        </div>

        
        <div
            class="h-64 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl flex items-center justify-center">
            <div class="text-center">
                <p class="text-4xl mb-2">🌡️</p>
                <p class="text-sm text-gray-600 dark:text-slate-400">Temperature trend visualization</p>
                <p class="text-xs text-gray-500 dark:text-slate-500 mt-1"><?php echo e($temperatureLogs->total()); ?> readings
                    recorded</p>
            </div>
        </div>

        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-100 dark:border-white/5">
            <div class="text-center">
                <p class="text-xs text-gray-500 dark:text-slate-400">Rata-rata</p>
                <p class="text-lg font-bold text-blue-600">
                    <?php echo e($temperatureLogs->avg('temperature') ? number_format($temperatureLogs->avg('temperature'), 1) . '°C' : 'N/A'); ?>

                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500 dark:text-slate-400">Minimum</p>
                <p class="text-lg font-bold text-cyan-600">
                    <?php echo e($temperatureLogs->min('temperature') ? number_format($temperatureLogs->min('temperature'), 1) . '°C' : 'N/A'); ?>

                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500 dark:text-slate-400">Maximum</p>
                <p class="text-lg font-bold text-orange-600">
                    <?php echo e($temperatureLogs->max('temperature') ? number_format($temperatureLogs->max('temperature'), 1) . '°C' : 'N/A'); ?>

                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500 dark:text-slate-400">Breach Count</p>
                <p class="text-lg font-bold text-red-600">
                    <?php echo e($temperatureLogs->filter(fn($log) => !$log->is_within_range)->count()); ?>

                </p>
            </div>
        </div>
    </div>

    
    <div
        class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Log Suhu Detail</h2>
        </div>

        <?php if($temperatureLogs->isEmpty()): ?>
            <div class="p-12 text-center">
                <p class="text-4xl mb-3">🌡️</p>
                <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada data suhu tercatat.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-[#0f172a]">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                                Waktu</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                                Suhu</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                                Kelembaban</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                                Sensor ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        <?php $__currentLoopData = $temperatureLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#0f172a] transition">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-white">
                                    <?php echo e($log->logged_at->format('d M Y, H:i:s')); ?>

                                    <p class="text-xs text-gray-500 dark:text-slate-400">
                                        <?php echo e($log->logged_at->diffForHumans()); ?></p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="font-bold <?php echo e($log->is_within_range ? 'text-green-600' : 'text-red-600'); ?>">
                                        <?php echo e(number_format($log->temperature, 1)); ?>°C
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-slate-300">
                                    <?php echo e($log->humidity ? number_format($log->humidity, 1) . '%' : '-'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-xs rounded-full <?php echo e($log->is_within_range ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400'); ?>">
                                        <?php echo e($log->is_within_range ? 'Normal' : 'Out of Range'); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-slate-400">
                                    <?php echo e($log->sensor_id ?? '-'); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">
                <?php echo e($temperatureLogs->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    
    <?php if($alerts->total() > 0): ?>
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-red-200 dark:border-red-500/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>🚨</span> Alert History (<?php echo e($alerts->total()); ?>)
                </h3>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-white/5">
                <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-[#0f172a] transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-full bg-<?php echo e($alert->severity === 'critical' ? 'red' : ($alert->severity === 'warning' ? 'yellow' : 'blue')); ?>-100 text-<?php echo e($alert->severity === 'critical' ? 'red' : ($alert->severity === 'warning' ? 'yellow' : 'blue')); ?>-700 dark:bg-<?php echo e($alert->severity === 'critical' ? 'red' : ($alert->severity === 'warning' ? 'yellow' : 'blue')); ?>-500/20 dark:text-<?php echo e($alert->severity === 'critical' ? 'red' : ($alert->severity === 'warning' ? 'yellow' : 'blue')); ?>-400">
                                        <?php echo e(ucfirst($alert->severity)); ?>

                                    </span>
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-full <?php echo e($alert->status === 'active' ? 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400' : 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400'); ?>">
                                        <?php echo e(ucfirst($alert->status)); ?>

                                    </span>
                                </div>
                                <p class="text-sm text-gray-900 dark:text-white font-medium"><?php echo e($alert->message); ?></p>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                    Suhu: <?php echo e(number_format($alert->current_temperature, 1)); ?>°C |
                                    Threshold: <?php echo e(number_format($alert->threshold_min, 1)); ?>° -
                                    <?php echo e(number_format($alert->threshold_max, 1)); ?>°C
                                </p>
                            </div>
                            <div class="text-right ml-4">
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    <?php echo e($alert->created_at->format('d M Y, H:i')); ?></p>
                                <p class="text-xs text-gray-400 dark:text-slate-500">
                                    <?php echo e($alert->created_at->diffForHumans()); ?></p>
                                <?php if($alert->acknowledged_at): ?>
                                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                                        Acknowledged <?php echo e($alert->acknowledged_at->diffForHumans()); ?>

                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">
                <?php echo e($alerts->links()); ?>

            </div>
        </div>
    <?php endif; ?>

    
    <div class="mt-6 flex gap-3">
        <button onclick="document.getElementById('logTempModal').classList.remove('hidden')"
            class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition flex items-center justify-center gap-2">
            <span>🌡️</span> Log Temperature
        </button>
        <a href="<?php echo e(route('fisheries.cold-chain.index')); ?>"
            class="px-4 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition flex items-center justify-center gap-2">
            <span>←</span> Back to List
        </a>
    </div>

    
    <div id="logTempModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Log Temperature Reading</h2>
                <button onclick="document.getElementById('logTempModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('fisheries.cold-chain.log-temperature', $unit->id)); ?>"
                class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Temperature (°C)
                        *</label>
                    <input type="number" name="temperature" required step="0.1" placeholder="-18.5"
                        class="<?php echo e($cls); ?>">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Humidity
                        (%)</label>
                    <input type="number" name="humidity" step="0.1" min="0" max="100"
                        placeholder="85.0" class="<?php echo e($cls); ?>">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Sensor ID
                        (Optional)</label>
                    <input type="text" name="sensor_id" placeholder="SENSOR-001" class="<?php echo e($cls); ?>">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        💾 Save Reading
                    </button>
                    <button type="button" onclick="document.getElementById('logTempModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition">
                        Cancel
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\fisheries\cold-chain-detail.blade.php ENDPATH**/ ?>