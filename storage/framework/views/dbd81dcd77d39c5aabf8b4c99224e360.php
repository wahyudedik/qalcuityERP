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
     <?php $__env->slot('header', null, []); ?> 🐠 Aquaculture Management <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
        <div
            class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            <?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Kolam</p>
            <p class="text-2xl font-bold text-cyan-600"><?php echo e($stats['total_ponds'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Kolam Aktif</p>
            <p class="text-2xl font-bold text-emerald-600"><?php echo e($stats['active_ponds'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Utilisasi Rata-rata</p>
            <p class="text-2xl font-bold text-blue-600"><?php echo e(number_format($stats['avg_utilization'] ?? 0, 1)); ?>%</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">FCR Rata-rata</p>
            <p class="text-2xl font-bold text-purple-600"><?php echo e(number_format($stats['avg_fcr'] ?? 0, 2)); ?></p>
        </div>
    </div>

    
    <div class="flex items-center justify-between mb-4">
        <form class="flex items-center gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari kolam..."
                class="px-3 py-2 text-sm rounded-lg border border-gray-200 bg-white text-gray-900 w-48">
            <select name="status" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = \App\Models\AquaculturePond::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($v); ?>" <?php if(request('status') === $v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </form>
        <button onclick="document.getElementById('addPondModal').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition flex items-center gap-2">
            <span>🏊</span> Tambah Kolam
        </button>
    </div>

    
    <?php if(empty($ponds) || count($ponds) === 0): ?>
        <div
            class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
            <p class="text-4xl mb-3">🐠</p>
            <p class="text-sm text-gray-500">Belum ada kolam budidaya. Tambahkan kolam pertama Anda.
            </p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <?php $__currentLoopData = $ponds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pond): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $sc = match ($pond->status) {
                        'active' => 'emerald',
                        'preparing' => 'blue',
                        'resting' => 'gray',
                        'maintenance' => 'yellow',
                        default => 'gray',
                    };
                ?>
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition group"
                    x-data="{ showWaterForm: false, showFeedingForm: false }">

                    
                    <div
                        class="px-5 py-4 flex items-start justify-between border-b border-gray-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-bold text-gray-900"><?php echo e($pond->code); ?></span>
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 $sc }}-500/20 $sc }}-400">
                                    <?php echo e($pond->status_label); ?>

                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-0.5"><?php echo e($pond->name); ?></p>
                        </div>
                        <span
                            class="text-xs text-gray-400"><?php echo e(number_format($pond->area_size, 1)); ?>

                            m²</span>
                    </div>

                    
                    <div class="px-5 py-4">
                        
                        <?php if($pond->utilization_percentage > 0): ?>
                            <div class="mb-3">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-gray-500">Utilisasi</span>
                                    <span
                                        class="font-medium text-gray-700"><?php echo e(number_format($pond->utilization_percentage, 1)); ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-cyan-600 h-2 rounded-full transition-all"
                                        style="width: <?php echo e(min($pond->utilization_percentage, 100)); ?>%"></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        
                        <?php if($pond->current_stock_species): ?>
                            <div class="mb-3 p-3 bg-cyan-50 rounded-lg">
                                <p class="text-xs text-gray-500 mb-1">Stok Saat Ini</p>
                                <p class="text-sm font-medium text-gray-900">
                                    🐟 <?php echo e($pond->current_stock_species); ?> -
                                    <?php echo e(number_format($pond->current_stock_count, 0)); ?> ekor
                                </p>
                                <?php if($pond->stocked_at): ?>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Dit stocking: <?php echo e($pond->stocked_at->format('d M Y')); ?>

                                        (<?php echo e($pond->stocked_at->diffInDays(now())); ?> hari lalu)
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        
                        <?php if($pond->latest_water_quality): ?>
                            <div class="mb-3 p-3 bg-blue-50 rounded-lg">
                                <p class="text-xs text-gray-500 mb-2">Kualitas Air Terakhir</p>
                                <div class="grid grid-cols-3 gap-2 text-xs">
                                    <div>
                                        <span class="text-gray-400 block">pH</span>
                                        <span
                                            class="font-medium text-gray-700"><?php echo e(number_format($pond->latest_water_quality->ph, 1)); ?></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 block">Oksigen</span>
                                        <span
                                            class="font-medium text-gray-700"><?php echo e(number_format($pond->latest_water_quality->dissolved_oxygen, 1)); ?>

                                            mg/L</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 block">Suhu</span>
                                        <span
                                            class="font-medium text-gray-700"><?php echo e(number_format($pond->latest_water_quality->temperature, 1)); ?>°C</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        
                        <div class="flex gap-2 mt-3">
                            <button @click="showWaterForm = !showWaterForm"
                                class="flex-1 px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                💧 Cek Kualitas Air
                            </button>
                            <button @click="showFeedingForm = !showFeedingForm"
                                class="flex-1 px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                                🍽️ Catat Pakan
                            </button>
                        </div>

                        
                        <div x-show="showWaterForm" x-transition
                            class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <form :action="'<?php echo e(route('fisheries.aquaculture.log-water-quality', $pond->id)); ?>'"
                                method="POST" class="space-y-2">
                                <?php echo csrf_field(); ?>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1">pH
                                            *</label>
                                        <input type="number" name="ph" required step="0.1" min="0"
                                            max="14" placeholder="7.0"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1">Oksigen
                                            Terlarut (mg/L) *</label>
                                        <input type="number" name="dissolved_oxygen" required step="0.1"
                                            min="0" placeholder="6.5"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1">Suhu
                                            Air (°C)</label>
                                        <input type="number" name="temperature" step="0.1" placeholder="28.5"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1">Amonia
                                            (mg/L)</label>
                                        <input type="number" name="ammonia" step="0.01" min="0"
                                            placeholder="0.02"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-600 mb-1">Salinitas
                                        (ppt)</label>
                                    <input type="number" name="salinity" step="0.1" min="0"
                                        placeholder="15.0"
                                        class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit"
                                        class="flex-1 px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                        Simpan
                                    </button>
                                    <button type="button" @click="showWaterForm = false"
                                        class="px-3 py-1.5 text-xs bg-gray-200 text-gray-700 rounded-lg transition">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>

                        
                        <div x-show="showFeedingForm" x-transition
                            class="mt-3 p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                            <form :action="'<?php echo e(route('fisheries.aquaculture.log-feeding', $pond->id)); ?>'"
                                method="POST" class="space-y-2">
                                <?php echo csrf_field(); ?>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1">Jumlah
                                            Pakan (kg) *</label>
                                        <input type="number" name="feed_quantity" required step="0.01"
                                            min="0" placeholder="5.5"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1">Biaya
                                            Pakan (Rp)</label>
                                        <input type="number" name="feed_cost" step="100" min="0"
                                            placeholder="50000"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-600 mb-1">Jenis
                                        Pakan</label>
                                    <input type="text" name="feed_type" placeholder="Pelet 781-2"
                                        class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                                    <textarea name="notes" rows="2" placeholder="Waktu pemberian, kondisi ikan, dll."
                                        class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900"></textarea>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit"
                                        class="flex-1 px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                                        Simpan
                                    </button>
                                    <button type="button" @click="showFeedingForm = false"
                                        class="px-3 py-1.5 text-xs bg-gray-200 text-gray-700 rounded-lg transition">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>

                        
                        <div class="grid grid-cols-2 gap-2 text-xs mt-3">
                            <?php if($pond->pond_type): ?>
                                <div>
                                    <span class="text-gray-400">Tipe:</span>
                                    <span
                                        class="text-gray-700"><?php echo e(ucfirst(str_replace('_', ' ', $pond->pond_type))); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if($pond->water_source): ?>
                                <div>
                                    <span class="text-gray-400">Sumber Air:</span>
                                    <span
                                        class="text-gray-700"><?php echo e(ucfirst($pond->water_source)); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div
                        class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs">
                        <span class="text-gray-500">
                            <?php if($pond->last_feeding_at): ?>
                                Terakhir pakan: <?php echo e($pond->last_feeding_at->diffForHumans()); ?>

                            <?php else: ?>
                                Belum ada pakan
                            <?php endif; ?>
                        </span>
                        <a href="<?php echo e(route('fisheries.aquaculture.show', $pond->id)); ?>"
                            class="text-cyan-600 hover:underline">
                            Detail →
                        </a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="mt-4"><?php echo e($ponds->links()); ?></div>
    <?php endif; ?>

    
    <div id="addPondModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">🏊 Tambah Kolam Budidaya</h3>
                <button onclick="document.getElementById('addPondModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('fisheries.aquaculture.store-pond')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; ?>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kode Kolam
                            *</label>
                        <input type="text" name="code" required placeholder="POND-001"
                            class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama *</label>
                        <input type="text" name="name" required placeholder="Kolam A1"
                            class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Luas (m²)
                            *</label>
                        <input type="number" name="area_size" required step="0.01" placeholder="500"
                            class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kedalaman
                            (m)</label>
                        <input type="number" name="depth" step="0.1" placeholder="1.5"
                            class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe
                            Kolam</label>
                        <select name="pond_type" class="<?php echo e($cls); ?>">
                            <option value="earthen">Kolam Tanah</option>
                            <option value="concrete">Kolam Beton</option>
                            <option value="tarpaulin">Terpal</option>
                            <option value="floating">Keramba Apung</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sumber
                            Air</label>
                        <select name="water_source" class="<?php echo e($cls); ?>">
                            <option value="river">Sungai</option>
                            <option value="well">Sumur</option>
                            <option value="reservoir">Waduk</option>
                            <option value="sea">Laut</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Lokasi</label>
                    <input type="text" name="location" placeholder="Area A, Blok 1" class="<?php echo e($cls); ?>">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" placeholder="Spesifikasi dan catatan tambahan"
                        class="<?php echo e($cls); ?>"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition">
                        💾 Simpan Kolam
                    </button>
                    <button type="button" onclick="document.getElementById('addPondModal').classList.add('hidden')"
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\fisheries\aquaculture.blade.php ENDPATH**/ ?>