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
     <?php $__env->slot('header', null, []); ?> Trip / Penugasan Kendaraan <?php $__env->endSlot(); ?>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari trip..."
                class="flex-1 min-w-[150px] px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = ['planned'=>'Direncanakan','in_progress'=>'Berjalan','completed'=>'Selesai','cancelled'=>'Batal']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($v); ?>" <?php if(request('status')===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'fleet', 'create')): ?>
        <button onclick="document.getElementById('modal-add-trip').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Trip</button>
        <?php endif; ?>
    </div>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Trip</th>
                        <th class="px-4 py-3 text-left">Kendaraan</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Driver</th>
                        <th class="px-4 py-3 text-left">Tujuan</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Jarak</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $trips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $tc = ['planned'=>'amber','in_progress'=>'blue','completed'=>'green','cancelled'=>'red'][$t->status] ?? 'gray';
                        $tl = ['planned'=>'Direncanakan','in_progress'=>'Berjalan','completed'=>'Selesai','cancelled'=>'Batal'][$t->status] ?? $t->status;
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900 dark:text-white"><?php echo e($t->trip_number); ?></td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300"><?php echo e($t->vehicle->plate_number ?? '-'); ?></td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-700 dark:text-slate-300"><?php echo e($t->driver->name ?? '-'); ?></td>
                        <td class="px-4 py-3 text-gray-900 dark:text-white"><?php echo e($t->purpose); ?>

                            <?php if($t->destination): ?> <span class="text-xs text-gray-400">→ <?php echo e($t->destination); ?></span> <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900 dark:text-white">
                            <?php echo e($t->distanceKm() !== null ? number_format($t->distanceKm(), 0, ',', '.') . ' km' : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($tc); ?>-100 text-<?php echo e($tc); ?>-700 dark:bg-<?php echo e($tc); ?>-500/20 dark:text-<?php echo e($tc); ?>-400"><?php echo e($tl); ?></span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if($t->status === 'in_progress'): ?>
                            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'fleet', 'edit')): ?>
                            <button onclick="openComplete(<?php echo e($t->id); ?>, '<?php echo e($t->trip_number); ?>', <?php echo e($t->odometer_start ?? 0); ?>)"
                                class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Selesai</button>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada trip.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($trips->hasPages()): ?><div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($trips->links()); ?></div><?php endif; ?>
    </div>

    
    <div id="modal-add-trip" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Trip</h3>
                <button onclick="document.getElementById('modal-add-trip').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('fleet.trips.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kendaraan *</label>
                        <select name="vehicle_id" required class="<?php echo e($cls); ?>"><option value="">-- Pilih --</option>
                            <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($v->id); ?>"><?php echo e($v->plate_number); ?> — <?php echo e($v->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Driver</label>
                        <select name="driver_id" class="<?php echo e($cls); ?>"><option value="">-- Tanpa Driver --</option>
                            <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($d->id); ?>"><?php echo e($d->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tujuan *</label><input type="text" name="purpose" required placeholder="Kirim barang ke customer" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Asal</label><input type="text" name="origin" placeholder="Gudang Pusat" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tujuan</label><input type="text" name="destination" placeholder="Jakarta Selatan" class="<?php echo e($cls); ?>"></div>
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Waktu Berangkat</label><input type="datetime-local" name="departed_at" class="<?php echo e($cls); ?>"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-trip').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat Trip</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-complete" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Selesaikan Trip</h3>
                <button onclick="document.getElementById('modal-complete').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-complete" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <p class="text-sm text-gray-600 dark:text-slate-400">Trip: <span id="c-trip" class="font-mono font-semibold text-gray-900 dark:text-white"></span></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Odometer Akhir (km) *</label>
                    <input type="number" name="odometer_end" id="c-odo" required min="0" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-complete').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Selesai</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    function openComplete(id, number, odoStart) {
        document.getElementById('form-complete').action = '<?php echo e(url("fleet/trips")); ?>/' + id + '/complete';
        document.getElementById('c-trip').textContent = number;
        document.getElementById('c-odo').value = odoStart;
        document.getElementById('c-odo').min = odoStart;
        document.getElementById('modal-complete').classList.remove('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/fleet/trips.blade.php ENDPATH**/ ?>