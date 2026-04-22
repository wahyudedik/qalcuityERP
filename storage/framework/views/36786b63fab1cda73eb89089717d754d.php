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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Cold Chain Management</h1>
            <button onclick="document.getElementById('addColdStorageModal').classList.remove('hidden')"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium whitespace-nowrap">
                + Add Cold Storage
            </button>
        </div>
     <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
        <div
            class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">
            <?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6" x-data="{ units: <?php echo e(Js::from($stats['units'] ?? [])); ?>, alerts: <?php echo e(Js::from($stats['alerts'] ?? [])); ?> }">
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Unit</p>
            <p class="text-2xl font-bold text-blue-600" x-text="units.length"><?php echo e(count($stats['units'] ?? [])); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Suhu Normal</p>
            <p class="text-2xl font-bold text-green-600" x-text="units.filter(u => u.is_safe).length">
                <?php echo e($stats['safe_units'] ?? 0); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Alert Aktif</p>
            <p class="text-2xl font-bold text-red-600" x-text="alerts.length"><?php echo e($stats['active_alerts'] ?? 0); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Utilisasi Rata-rata</p>
            <p class="text-2xl font-bold text-purple-600"><?php echo e($stats['avg_utilization'] ?? 0); ?>%</p>
        </div>
    </div>

    
    <div class="flex items-center justify-between mb-4">
        <form class="flex items-center gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari unit..."
                class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white w-48">
            <select name="status" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <option value="safe" <?php if(request('status') === 'safe'): echo 'selected'; endif; ?>">Suhu Normal</option>
                <option value="warning" <?php if(request('status') === 'warning'): echo 'selected'; endif; ?>">Warning</option>
                <option value="critical" <?php if(request('status') === 'critical'): echo 'selected'; endif; ?>">Critical</option>
            </select>
        </form>
        <button onclick="document.getElementById('addUnitModal').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
            <span>➕</span> Tambah Unit
        </button>
    </div>

    
    <?php if(empty($storageUnits) || count($storageUnits) === 0): ?>
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
            <p class="text-4xl mb-3">❄️</p>
            <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada unit cold storage. Tambahkan unit pertama
                Anda.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <?php $__currentLoopData = $storageUnits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $tempClass = $unit->isTemperatureSafe()
                        ? 'green'
                        : ($unit->current_temperature > $unit->max_temperature
                            ? 'red'
                            : 'yellow');
                    $tempColor = $unit->isTemperatureSafe()
                        ? 'text-green-600'
                        : ($unit->current_temperature > $unit->max_temperature
                            ? 'text-red-600'
                            : 'text-yellow-600');
                ?>
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden hover:shadow-lg transition group"
                    x-data="{ showTempForm: false, currentTemp: <?php echo e($unit->current_temperature ?? 'null'); ?> }">

                    
                    <div
                        class="px-5 py-4 flex items-start justify-between border-b border-gray-100 dark:border-white/5">
                        <div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-lg font-bold text-gray-900 dark:text-white"><?php echo e($unit->unit_code); ?></span>
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full bg-<?php echo e($tempClass); ?>-100 text-<?php echo e($tempClass); ?>-700 dark:bg-<?php echo e($tempClass); ?>-500/20 dark:text-<?php echo e($tempClass); ?>-400">
                                    <?php echo e($unit->isTemperatureSafe() ? 'Normal' : 'Warning'); ?>

                                </span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5"><?php echo e($unit->name); ?></p>
                        </div>
                        <button @click="showTempForm = !showTempForm"
                            class="px-3 py-1 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            🌡️ Update Suhu
                        </button>
                    </div>

                    
                    <div class="px-5 py-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-slate-400">Suhu Saat Ini</p>
                                <p class="text-3xl font-bold <?php echo e($tempColor); ?>"
                                    x-text="currentTemp ? currentTemp.toFixed(1) + '°C' : 'N/A'">
                                    <?php echo e($unit->current_temperature ? number_format($unit->current_temperature, 1) . '°C' : 'N/A'); ?>

                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 dark:text-slate-400">Range Aman</p>
                                <p class="text-sm font-medium text-gray-700 dark:text-slate-300">
                                    <?php echo e(number_format($unit->min_temperature, 1)); ?>° -
                                    <?php echo e(number_format($unit->max_temperature, 1)); ?>°C
                                </p>
                            </div>
                        </div>

                        
                        <div x-show="showTempForm" x-transition
                            class="mt-3 p-3 bg-gray-50 dark:bg-[#0f172a] rounded-lg">
                            <form :action="'<?php echo e(route('fisheries.cold-chain.log-temperature', $unit->id)); ?>'"
                                method="POST" class="space-y-2">
                                <?php echo csrf_field(); ?>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Suhu
                                            (°C)
                                        </label>
                                        <input type="number" name="temperature" required step="0.1"
                                            placeholder="-18.5"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kelembaban
                                            (%)</label>
                                        <input type="number" name="humidity" step="0.1" placeholder="85.0"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">ID
                                        Sensor (opsional)</label>
                                    <input type="text" name="sensor_id" placeholder="SENSOR-001"
                                        class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit"
                                        class="flex-1 px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                        Simpan
                                    </button>
                                    <button type="button" @click="showTempForm = false"
                                        class="px-3 py-1.5 text-xs bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>

                        
                        <div class="grid grid-cols-2 gap-2 text-xs mt-3">
                            <?php if($unit->capacity_kg): ?>
                                <div>
                                    <span class="text-gray-400">Kapasitas:</span>
                                    <span
                                        class="text-gray-700 dark:text-slate-300"><?php echo e(number_format($unit->capacity_kg, 0)); ?>

                                        kg</span>
                                </div>
                            <?php endif; ?>
                            <?php if($unit->utilization_percentage): ?>
                                <div>
                                    <span class="text-gray-400">Utilisasi:</span>
                                    <span
                                        class="text-gray-700 dark:text-slate-300"><?php echo e(number_format($unit->utilization_percentage, 1)); ?>%</span>
                                </div>
                            <?php endif; ?>
                            <?php if($unit->location): ?>
                                <div class="col-span-2">
                                    <span class="text-gray-400">Lokasi:</span>
                                    <span class="text-gray-700 dark:text-slate-300"><?php echo e($unit->location); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div
                        class="px-5 py-3 bg-gray-50 dark:bg-[#0f172a] border-t border-gray-100 dark:border-white/5 flex items-center justify-between text-xs">
                        <span class="text-gray-500 dark:text-slate-400">
                            Terakhir update:
                            <?php echo e($unit->last_temperature_update ? $unit->last_temperature_update->diffForHumans() : 'Belum pernah'); ?>

                        </span>
                        <a href="<?php echo e(route('fisheries.cold-chain.show', $unit->id)); ?>"
                            class="text-blue-600 dark:text-blue-400 hover:underline">
                            Detail →
                        </a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="mt-4"><?php echo e($storageUnits->links()); ?></div>
    <?php endif; ?>

    
    <?php if(!empty($alerts) && count($alerts) > 0): ?>
        <div class="mt-6 bg-white dark:bg-[#1e293b] rounded-2xl border border-red-200 dark:border-red-500/30 p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="text-red-500">🚨</span> Alert Suhu Aktif (<?php echo e(count($alerts)); ?>)
            </h3>
            <div class="space-y-3">
                <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div
                        class="flex items-start gap-3 p-3 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20">
                        <div
                            class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-500/20 flex items-center justify-center text-sm">
                            ⚠️</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($alert['title']); ?></p>
                            <p class="text-xs text-gray-600 dark:text-slate-400"><?php echo e($alert['description']); ?></p>
                            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1"><?php echo e($alert['time']); ?></p>
                        </div>
                        <span
                            class="text-xs px-2 py-1 rounded-full bg-<?php echo e($alert['severity_color']); ?>-100 text-<?php echo e($alert['severity_color']); ?>-700 dark:bg-<?php echo e($alert['severity_color']); ?>-500/20 dark:text-<?php echo e($alert['severity_color']); ?>-400">
                            <?php echo e(ucfirst($alert['severity'])); ?>

                        </span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    
    <div id="addUnitModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Tambah Cold Storage Unit</h2>
                <button onclick="document.getElementById('addUnitModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('fisheries.cold-chain.store-storage')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kode Unit
                            *</label>
                        <input type="text" name="unit_code" required placeholder="CS-001"
                            class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label>
                        <input type="text" name="name" required placeholder="Cold Room A"
                            class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Suhu Min (°C)
                            *</label>
                        <input type="number" name="min_temperature" required step="0.1" value="-18"
                            class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Suhu Max (°C)
                            *</label>
                        <input type="number" name="max_temperature" required step="0.1" value="-15"
                            class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kapasitas
                            (kg)</label>
                        <input type="number" name="capacity_kg" step="0.01" placeholder="5000"
                            class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe</label>
                        <select name="type" class="<?php echo e($cls); ?>">
                            <option value="cold_room">Cold Room</option>
                            <option value="freezer">Freezer</option>
                            <option value="chiller">Chiller</option>
                            <option value="blast_freezer">Blast Freezer</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Lokasi</label>
                    <input type="text" name="location" placeholder="Gudang A, Lantai 1"
                        class="<?php echo e($cls); ?>">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" placeholder="Spesifikasi dan catatan tambahan"
                        class="<?php echo e($cls); ?>"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        💾 Simpan Unit
                    </button>
                    <button type="button" onclick="document.getElementById('addUnitModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    
    <script>
        // Refresh page every 30 seconds for live temperature updates
        setInterval(() => {
            window.location.reload();
        }, 30000);
    </script>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\fisheries\cold-chain.blade.php ENDPATH**/ ?>