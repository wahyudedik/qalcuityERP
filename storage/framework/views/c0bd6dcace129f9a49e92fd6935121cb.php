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
     <?php $__env->slot('header', null, []); ?> Maintenance Kendaraan <?php $__env->endSlot(); ?>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <select name="vehicle_id"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Kendaraan</option>
                <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($v->id); ?>" <?php if(request('vehicle_id') == $v->id): echo 'selected'; endif; ?>><?php echo e($v->plate_number); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="status"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = ['scheduled' => 'Terjadwal', 'in_progress' => 'Dikerjakan', 'completed' => 'Selesai', 'cancelled' => 'Batal']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($v); ?>" <?php if(request('status') === $v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'fleet', 'create')): ?>
        <button onclick="document.getElementById('modal-add-maint').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Jadwal</button>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Kendaraan</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Deskripsi</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Jadwal</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Biaya</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $maintenances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $mc =
                                [
                                    'scheduled' => 'amber',
                                    'in_progress' => 'blue',
                                    'completed' => 'green',
                                    'cancelled' => 'gray',
                                ][$m->status] ?? 'gray';
                            $ml =
                                [
                                    'scheduled' => 'Terjadwal',
                                    'in_progress' => 'Dikerjakan',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Batal',
                                ][$m->status] ?? $m->status;
                            $tl =
                                [
                                    'routine' => 'Rutin',
                                    'repair' => 'Perbaikan',
                                    'inspection' => 'Inspeksi',
                                    'tire' => 'Ban',
                                    'oil_change' => 'Ganti Oli',
                                ][$m->type] ?? $m->type;
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs text-gray-900">
                                <?php echo e($m->vehicle?->plate_number ?? '-'); ?></td>
                            <td class="px-4 py-3 text-xs text-gray-500"><?php echo e($tl); ?></td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-700">
                                <?php echo e($m->description); ?></td>
                            <td
                                class="px-4 py-3 text-center hidden sm:table-cell text-xs text-gray-500">
                                <?php echo e($m->scheduled_date?->format('d/m/Y') ?? '-'); ?></td>
                            <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900">Rp
                                <?php echo e(number_format($m->cost, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($mc); ?>-100 text-<?php echo e($mc); ?>-700 $mc }}-500/20 $mc }}-400"><?php echo e($ml); ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <?php if($m->status === 'scheduled' || $m->status === 'in_progress'): ?>
                                        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'fleet', 'edit')): ?>
                                        <button onclick="openCompleteMaint(<?php echo e($m->id); ?>)"
                                            class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Selesai</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'fleet', 'delete')): ?>
                                    <form method="POST" action="<?php echo e(route('fleet.maintenance.destroy', $m)); ?>"
                                        class="inline" onsubmit="return confirm('Hapus?')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit"
                                            class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum
                                ada jadwal maintenance.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($maintenances->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-100"><?php echo e($maintenances->links()); ?></div>
        <?php endif; ?>
    </div>

    <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500'; ?>

    
    <div id="modal-add-maint" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Jadwal Maintenance</h3>
                <button onclick="document.getElementById('modal-add-maint').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('fleet.maintenance.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Kendaraan
                            *</label>
                        <select name="vehicle_id" required class="<?php echo e($cls); ?>">
                            <option value="">-- Pilih --</option>
                            <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($v->id); ?>"><?php echo e($v->plate_number); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Tipe *</label>
                        <select name="type" required class="<?php echo e($cls); ?>">
                            <option value="routine">Rutin</option>
                            <option value="repair">Perbaikan</option>
                            <option value="inspection">Inspeksi</option>
                            <option value="tire">Ban</option>
                            <option value="oil_change">Ganti Oli</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2"><label
                            class="block text-xs font-medium text-gray-600 mb-1">Deskripsi
                            *</label><input type="text" name="description" required
                            placeholder="Service rutin 10.000 km" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Tanggal
                            Jadwal</label><input type="date" name="scheduled_date" class="<?php echo e($cls); ?>">
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Estimasi
                            Biaya</label><input type="number" name="cost" min="0" step="1000"
                            value="0" class="<?php echo e($cls); ?>"></div>
                    <div><label
                            class="block text-xs font-medium text-gray-600 mb-1">Vendor/Bengkel</label><input
                            type="text" name="vendor" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Next di
                            KM</label><input type="number" name="next_km" min="0" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button"
                        onclick="document.getElementById('modal-add-maint').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-complete-maint" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Selesaikan Maintenance</h3>
                <button onclick="document.getElementById('modal-complete-maint').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-complete-maint" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Biaya Aktual
                            *</label><input type="number" name="cost" required min="0" step="1000"
                            class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Tanggal
                            Selesai *</label><input type="date" name="completed_date" required
                            value="<?php echo e(date('Y-m-d')); ?>" class="<?php echo e($cls); ?>"></div>
                    <div><label
                            class="block text-xs font-medium text-gray-600 mb-1">Odometer</label><input
                            type="number" name="odometer_at" min="0" class="<?php echo e($cls); ?>"></div>
                    <div><label
                            class="block text-xs font-medium text-gray-600 mb-1">Vendor</label><input
                            type="text" name="vendor" class="<?php echo e($cls); ?>"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button"
                        onclick="document.getElementById('modal-complete-maint').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Selesai</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function openCompleteMaint(id) {
                document.getElementById('form-complete-maint').action = '<?php echo e(url('fleet/maintenance')); ?>/' + id + '/complete';
                document.getElementById('modal-complete-maint').classList.remove('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\fleet\maintenance.blade.php ENDPATH**/ ?>