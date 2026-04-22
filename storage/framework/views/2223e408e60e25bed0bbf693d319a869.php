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
     <?php $__env->slot('header', null, []); ?> Okupansi Tempat Tidur <?php $__env->endSlot(); ?>

    <?php $tid = auth()->user()->tenant_id; ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <?php
            $totalBeds = \App\Models\Bed::where('tenant_id', $tid)->count();
            $availableBeds = \App\Models\Bed::where('tenant_id', $tid)->where('status', 'available')->count();
            $occupiedBeds = \App\Models\Bed::where('tenant_id', $tid)->where('status', 'occupied')->count();
            $maintenanceBeds = \App\Models\Bed::where('tenant_id', $tid)->where('status', 'maintenance')->count();
            $reservedBeds = \App\Models\Bed::where('tenant_id', $tid)->where('status', 'reserved')->count();
            $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0;
        ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Bed</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e($totalBeds); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Tersedia</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1"><?php echo e($availableBeds); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Terisi</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1"><?php echo e($occupiedBeds); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Maintenance</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1"><?php echo e($maintenanceBeds); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Okupansi</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1"><?php echo e($occupancyRate); ?>%</p>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tingkat Okupansi Keseluruhan</h3>
            <span class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?php echo e($occupancyRate); ?>%</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
            <div class="h-full transition-all duration-500 rounded-full
                <?php if($occupancyRate >= 90): ?> bg-red-500
                <?php elseif($occupancyRate >= 70): ?> bg-amber-500
                <?php else: ?> bg-green-500 <?php endif; ?>"
                style="width: <?php echo e($occupancyRate); ?>%"></div>
        </div>
        <div class="flex items-center justify-between mt-2 text-xs text-gray-500 dark:text-slate-400">
            <span>0%</span>
            <span>50%</span>
            <span>100%</span>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <select name="ward"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Ruang</option>
                    <?php
                        $wards = \App\Models\Ward::where('tenant_id', $tid)->get();
                    ?>
                    <?php $__currentLoopData = $wards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ward): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($ward->id); ?>" <?php if(request('ward') == $ward->id): echo 'selected'; endif; ?>><?php echo e($ward->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="available" <?php if(request('status') === 'available'): echo 'selected'; endif; ?>>Tersedia</option>
                    <option value="occupied" <?php if(request('status') === 'occupied'): echo 'selected'; endif; ?>>Terisi</option>
                    <option value="maintenance" <?php if(request('status') === 'maintenance'): echo 'selected'; endif; ?>>Maintenance</option>
                    <option value="reserved" <?php if(request('status') === 'reserved'): echo 'selected'; endif; ?>>Direservasi</option>
                </select>
                <select name="ward_type"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Tipe</option>
                    <option value="VIP" <?php if(request('ward_type') === 'VIP'): echo 'selected'; endif; ?>>VIP</option>
                    <option value="Kelas 1" <?php if(request('ward_type') === 'Kelas 1'): echo 'selected'; endif; ?>>Kelas 1</option>
                    <option value="Kelas 2" <?php if(request('ward_type') === 'Kelas 2'): echo 'selected'; endif; ?>>Kelas 2</option>
                    <option value="Kelas 3" <?php if(request('ward_type') === 'Kelas 3'): echo 'selected'; endif; ?>>Kelas 3</option>
                    <option value="ICU" <?php if(request('ward_type') === 'ICU'): echo 'selected'; endif; ?>>ICU</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            </form>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Bed</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Ruang</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Tipe</th>
                        <?php if(request('status') === 'occupied' || !request('status')): ?>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Pasien</th>
                        <?php endif; ?>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $beds ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl 
                                        <?php if($bed->status === 'available'): ?> bg-green-100 dark:bg-green-900/30
                                        <?php elseif($bed->status === 'occupied'): ?> bg-red-100 dark:bg-red-900/30
                                        <?php elseif($bed->status === 'maintenance'): ?> bg-amber-100 dark:bg-amber-900/30
                                        <?php else: ?> bg-gray-100 dark:bg-gray-700 <?php endif; ?> flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 
                                            <?php if($bed->status === 'available'): ?> text-green-600 dark:text-green-400
                                            <?php elseif($bed->status === 'occupied'): ?> text-red-600 dark:text-red-400
                                            <?php elseif($bed->status === 'maintenance'): ?> text-amber-600 dark:text-amber-400
                                            <?php else: ?> text-gray-500 dark:text-gray-400 <?php endif; ?>"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                            </path>
                                        </svg>
                                    </div>
                                    <span
                                        class="font-semibold text-gray-900 dark:text-white"><?php echo e($bed->bed_number); ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-900 dark:text-white"><?php echo e($bed->ward ? $bed->ward->name : '-'); ?></p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    <?php echo e($bed->ward ? $bed->ward->floor : ''); ?></p>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                    <?php echo e($bed->ward ? $bed->ward->ward_type : '-'); ?>

                                </span>
                            </td>
                            <?php if(request('status') === 'occupied' || !request('status')): ?>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <?php if($bed->status === 'occupied' && $bed->admission): ?>
                                        <p class="text-gray-900 dark:text-white font-medium">
                                            <?php echo e($bed->admission->patient ? $bed->admission->patient->full_name : '-'); ?>

                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400">Sejak
                                            <?php echo e($bed->admission->admission_date ? \Carbon\Carbon::parse($bed->admission->admission_date)->format('d M') : '-'); ?>

                                        </p>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <td class="px-4 py-3 text-center">
                                <?php if($bed->status === 'available'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Tersedia</span>
                                <?php elseif($bed->status === 'occupied'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Terisi</span>
                                <?php elseif($bed->status === 'maintenance'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Maintenance</span>
                                <?php elseif($bed->status === 'reserved'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Direservasi</span>
                                <?php else: ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300"><?php echo e($bed->status); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <?php if($bed->status === 'available'): ?>
                                        <a href="<?php echo e(route('healthcare.inpatient.admissions.create', ['bed_id' => $bed->id])); ?>"
                                            class="p-1.5 text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/30 rounded-lg"
                                            title="Terima Pasien">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                                                </path>
                                            </svg>
                                        </a>
                                    <?php elseif($bed->status === 'occupied' && $bed->admission): ?>
                                        <a href="<?php echo e(route('healthcare.inpatient.admissions.show', $bed->admission)); ?>"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg"
                                            title="Detail Pasien">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                    <button onclick="updateBedStatus(<?php echo e($bed->id); ?>)"
                                        class="p-1.5 text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30 rounded-lg"
                                        title="Update Status">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-slate-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                    </path>
                                </svg>
                                <p>Belum ada data tempat tidur</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <?php if(isset($beds) && $beds->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                <?php echo e($beds->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function updateBedStatus(bedId) {
                // Implement bed status update modal
                alert('Update status bed ID: ' + bedId);
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\inpatient\beds.blade.php ENDPATH**/ ?>