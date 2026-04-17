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
     <?php $__env->slot('header', null, []); ?> Fleet Management <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Kendaraan</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($stats['total']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Tersedia</p>
            <p class="text-2xl font-bold text-green-500"><?php echo e($stats['available']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Sedang Dipakai</p>
            <p class="text-2xl font-bold text-blue-500"><?php echo e($stats['in_use']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Maintenance</p>
            <p class="text-2xl font-bold text-amber-500"><?php echo e($stats['maintenance']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">BBM Bulan Ini</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($stats['fuel_month'], 0, ',', '.')); ?></p>
        </div>
    </div>

    
    <?php if(count($alerts) > 0 || $upcomingMaint->isNotEmpty()): ?>
    <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 rounded-2xl p-4 mb-4">
        <p class="text-sm font-medium text-amber-700 dark:text-amber-400 mb-1">⚠️ Perhatian</p>
        <ul class="text-xs text-amber-600 dark:text-amber-300 space-y-0.5">
            <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($a); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php $__currentLoopData = $upcomingMaint; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li>Maintenance <?php echo e($m->vehicle->plate_number ?? '-'); ?>: <?php echo e($m->description); ?> (<?php echo e($m->scheduled_date?->format('d/m')); ?>)</li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <?php endif; ?>

    
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari plat / nama..."
                class="flex-1 min-w-[150px] px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = ['available'=>'Tersedia','in_use'=>'Dipakai','maintenance'=>'Maintenance','retired'=>'Pensiun']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($v); ?>" <?php if(request('status')===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'fleet', 'create')): ?>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Kendaraan</button>
        <?php endif; ?>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Plat</th>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Tipe</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Odometer</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $sc = ['available'=>'green','in_use'=>'blue','maintenance'=>'amber','retired'=>'gray'][$v->status] ?? 'gray';
                        $sl = ['available'=>'Tersedia','in_use'=>'Dipakai','maintenance'=>'Maintenance','retired'=>'Pensiun'][$v->status] ?? $v->status;
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900 dark:text-white"><?php echo e($v->plate_number); ?></td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300">
                            <?php echo e($v->name); ?>

                            <?php if($v->brand): ?> <span class="text-xs text-gray-400">(<?php echo e($v->brand); ?>)</span> <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell text-xs text-gray-500 dark:text-slate-400">
                            <?php echo e(['car'=>'Mobil','truck'=>'Truk','motorcycle'=>'Motor','van'=>'Van'][$v->type] ?? $v->type); ?>

                        </td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900 dark:text-white"><?php echo e(number_format($v->odometer, 0, ',', '.')); ?> km</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 dark:bg-<?php echo e($sc); ?>-500/20 dark:text-<?php echo e($sc); ?>-400"><?php echo e($sl); ?></span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'fleet', 'edit')): ?>
                                <button onclick='openEdit(<?php echo json_encode($v, 15, 512) ?>)' class="text-xs px-2 py-1 border border-gray-200 dark:border-white/10 rounded-lg text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Edit</button>
                                <?php endif; ?>
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'fleet', 'delete')): ?>
                                <form method="POST" action="<?php echo e(url('fleet/vehicles')); ?>/<?php echo e($v->id); ?>" class="inline" onsubmit="return confirm('Hapus kendaraan ini?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada kendaraan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($vehicles->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($vehicles->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Kendaraan</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('fleet.vehicles.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500'; ?>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">No. Plat *</label><input type="text" name="plate_number" required placeholder="B 1234 XYZ" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label><input type="text" name="name" required placeholder="Toyota Avanza 2024" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                        <select name="type" required class="<?php echo e($cls); ?>">
                            <option value="car">Mobil</option><option value="truck">Truk</option><option value="motorcycle">Motor</option><option value="van">Van</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Merek</label><input type="text" name="brand" placeholder="Toyota" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Model</label><input type="text" name="model" placeholder="Avanza" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tahun</label><input type="number" name="year" min="1990" max="2030" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Warna</label><input type="text" name="color" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Odometer (km)</label><input type="number" name="odometer" min="0" value="0" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">STNK Expired</label><input type="date" name="registration_expiry" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Asuransi Expired</label><input type="date" name="insurance_expiry" class="<?php echo e($cls); ?>"></div>
                    <?php if($assets->isNotEmpty()): ?>
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Link ke Aset</label>
                        <select name="asset_id" class="<?php echo e($cls); ?>"><option value="">-- Tanpa Link --</option>
                            <?php $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($a->id); ?>"><?php echo e($a->asset_code); ?> — <?php echo e($a->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label><input type="text" name="notes" class="<?php echo e($cls); ?>"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Edit Kendaraan</h3>
                <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-edit" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">No. Plat *</label><input type="text" name="plate_number" id="e-plate" required class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label><input type="text" name="name" id="e-name" required class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                        <select name="type" id="e-type" required class="<?php echo e($cls); ?>">
                            <option value="car">Mobil</option><option value="truck">Truk</option><option value="motorcycle">Motor</option><option value="van">Van</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Status</label>
                        <select name="status" id="e-status" class="<?php echo e($cls); ?>">
                            <option value="available">Tersedia</option><option value="in_use">Dipakai</option><option value="maintenance">Maintenance</option><option value="retired">Pensiun</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Merek</label><input type="text" name="brand" id="e-brand" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Model</label><input type="text" name="model" id="e-model" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Odometer</label><input type="number" name="odometer" id="e-odo" min="0" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">STNK Expired</label><input type="date" name="registration_expiry" id="e-reg" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Asuransi Expired</label><input type="date" name="insurance_expiry" id="e-ins" class="<?php echo e($cls); ?>"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    function openEdit(v) {
        document.getElementById('form-edit').action = '<?php echo e(url("fleet/vehicles")); ?>/' + v.id;
        document.getElementById('e-plate').value = v.plate_number;
        document.getElementById('e-name').value = v.name;
        document.getElementById('e-type').value = v.type;
        document.getElementById('e-status').value = v.status;
        document.getElementById('e-brand').value = v.brand || '';
        document.getElementById('e-model').value = v.model || '';
        document.getElementById('e-odo').value = v.odometer;
        document.getElementById('e-reg').value = v.registration_expiry ? v.registration_expiry.substring(0,10) : '';
        document.getElementById('e-ins').value = v.insurance_expiry ? v.insurance_expiry.substring(0,10) : '';
        document.getElementById('modal-edit').classList.remove('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/fleet/index.blade.php ENDPATH**/ ?>