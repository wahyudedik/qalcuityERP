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
     <?php $__env->slot('header', null, []); ?> Manajemen Ruang Rawat <?php $__env->endSlot(); ?>

    <?php $tid = auth()->user()->tenant_id; ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <?php
            $totalWards = \App\Models\Ward::where('tenant_id', $tid)->count();
            $totalBeds = \App\Models\Bed::where('tenant_id', $tid)->count();
            $occupiedBeds = \App\Models\Bed::where('tenant_id', $tid)->where('status', 'occupied')->count();
            $availableBeds = \App\Models\Bed::where('tenant_id', $tid)->where('status', 'available')->count();
            $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0;
        ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Ruang</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e($totalWards); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Tempat Tidur</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e($totalBeds); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Tersedia</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1"><?php echo e($availableBeds); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Okupansi</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1"><?php echo e($occupancyRate); ?>%</p>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-green-500"></div>
                <span class="text-gray-700 dark:text-slate-300">Tersedia</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-red-500"></div>
                <span class="text-gray-700 dark:text-slate-300">Terisi</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-amber-500"></div>
                <span class="text-gray-700 dark:text-slate-300">Maintenance</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-gray-400"></div>
                <span class="text-gray-700 dark:text-slate-300">Nonaktif</span>
            </div>
        </div>
    </div>

    
    <div class="space-y-6">
        <?php $__empty_1 = true; $__currentLoopData = $wards ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ward): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div
                    class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e($ward->name); ?></h3>
                        <p class="text-sm text-gray-500 dark:text-slate-400"><?php echo e($ward->ward_type ?? '-'); ?> |
                            <?php echo e($ward->floor ?? '-'); ?></p>
                    </div>
                    <div class="flex items-center gap-4">
                        <?php
                            $wardBeds = $ward->beds ?? collect();
                            $wardOccupied = $wardBeds->where('status', 'occupied')->count();
                            $wardTotal = $wardBeds->count();
                            $wardOccupancy = $wardTotal > 0 ? round(($wardOccupied / $wardTotal) * 100, 1) : 0;
                        ?>
                        <div class="text-right">
                            <p class="text-sm text-gray-500 dark:text-slate-400">Okupansi</p>
                            <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                <?php echo e($wardOccupied); ?>/<?php echo e($wardTotal); ?> (<?php echo e($wardOccupancy); ?>%)</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-3">
                        <?php $__empty_2 = true; $__currentLoopData = $wardBeds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                            <button onclick="showBedDetails(<?php echo e($bed->id); ?>)"
                                class="bed-item relative aspect-square rounded-xl border-2 transition-all hover:scale-105 cursor-pointer
                                    <?php if($bed->status === 'available'): ?> bg-green-100 dark:bg-green-900/30 border-green-300 dark:border-green-700 hover:bg-green-200 dark:hover:bg-green-900/50
                                    <?php elseif($bed->status === 'occupied'): ?>
                                        bg-red-100 dark:bg-red-900/30 border-red-300 dark:border-red-700 hover:bg-red-200 dark:hover:bg-red-900/50
                                    <?php elseif($bed->status === 'maintenance'): ?>
                                        bg-amber-100 dark:bg-amber-900/30 border-amber-300 dark:border-amber-700 hover:bg-amber-200 dark:hover:bg-amber-900/50
                                    <?php else: ?>
                                        bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 hover:bg-gray-200 dark:hover:bg-gray-600 <?php endif; ?>">
                                <div class="flex flex-col items-center justify-center h-full">
                                    <svg class="w-5 h-5 mb-1 
                                        <?php if($bed->status === 'available'): ?> text-green-600 dark:text-green-400
                                        <?php elseif($bed->status === 'occupied'): ?> text-red-600 dark:text-red-400
                                        <?php elseif($bed->status === 'maintenance'): ?> text-amber-600 dark:text-amber-400
                                        <?php else: ?> text-gray-500 dark:text-gray-400 <?php endif; ?>"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                        </path>
                                    </svg>
                                    <span
                                        class="text-xs font-semibold 
                                        <?php if($bed->status === 'available'): ?> text-green-700 dark:text-green-300
                                        <?php elseif($bed->status === 'occupied'): ?> text-red-700 dark:text-red-300
                                        <?php elseif($bed->status === 'maintenance'): ?> text-amber-700 dark:text-amber-300
                                        <?php else: ?> text-gray-600 dark:text-gray-300 <?php endif; ?>">
                                        <?php echo e($bed->bed_number); ?>

                                    </span>
                                </div>
                                <?php if($bed->status === 'occupied' && $bed->admission): ?>
                                    <div
                                        class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white dark:border-gray-800">
                                    </div>
                                <?php endif; ?>
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                            <div class="col-span-full text-center py-8 text-gray-500 dark:text-slate-400">
                                Belum ada tempat tidur di ruang ini
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-slate-600" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                    </path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Belum Ada Ruang Rawat</h3>
                <p class="text-sm text-gray-500 dark:text-slate-400 mb-4">Silakan tambahkan ruang rawat terlebih dahulu
                </p>
                <button onclick="document.getElementById('modal-add-ward').classList.remove('hidden')"
                    class="px-6 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                    + Tambah Ruang
                </button>
            </div>
        <?php endif; ?>
    </div>

    
    <div id="modal-add-ward"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg">
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Ruang Rawat Baru</h3>
                <button onclick="document.getElementById('modal-add-ward').classList.add('hidden')"
                    class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-xl">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <form action="<?php echo e(route('healthcare.inpatient.wards.store')); ?>" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Nama Ruang
                            *</label>
                        <input type="text" name="name" required placeholder="Contoh: Ruang Melati"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Tipe Ruang
                            *</label>
                        <select name="ward_type" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Tipe</option>
                            <option value="VIP">VIP</option>
                            <option value="Kelas 1">Kelas 1</option>
                            <option value="Kelas 2">Kelas 2</option>
                            <option value="Kelas 3">Kelas 3</option>
                            <option value="ICU">ICU</option>
                            <option value="NICU">NICU</option>
                            <option value="HCU">HCU</option>
                            <option value="Isolasi">Isolasi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Lantai</label>
                        <input type="text" name="floor" placeholder="Contoh: Lantai 2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Kapasitas
                            Tempat Tidur</label>
                        <input type="number" name="capacity" min="1" placeholder="Jumlah tempat tidur"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Deskripsi</label>
                        <textarea name="description" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('modal-add-ward').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function showBedDetails(bedId) {
                // Implement bed details modal or navigation
                alert('Detail tempat tidur ID: ' + bedId);
            }
        </script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\inpatient\wards.blade.php ENDPATH**/ ?>