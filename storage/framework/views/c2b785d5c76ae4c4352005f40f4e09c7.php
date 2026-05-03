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
     <?php $__env->slot('header', null, []); ?> Log BBM <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total BBM Bulan Ini</p>
            <p class="text-xl font-bold text-gray-900">Rp
                <?php echo e(number_format($monthlySummary->total_cost ?? 0, 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Liter</p>
            <p class="text-xl font-bold text-blue-500">
                <?php echo e(number_format($monthlySummary->total_liters ?? 0, 1, ',', '.')); ?> L</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Transaksi</p>
            <p class="text-xl font-bold text-green-500"><?php echo e($monthlySummary->count ?? 0); ?>×</p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <select name="vehicle_id"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Kendaraan</option>
                <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($v->id); ?>" <?php if(request('vehicle_id') == $v->id): echo 'selected'; endif; ?>><?php echo e($v->plate_number); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input type="month" name="month" value="<?php echo e(request('month', now()->format('Y-m'))); ?>"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'fleet', 'create')): ?>
        <button onclick="document.getElementById('modal-add-fuel').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Catat BBM</button>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Kendaraan</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Jenis BBM</th>
                        <th class="px-4 py-3 text-right">Liter</th>
                        <th class="px-4 py-3 text-right hidden sm:table-cell">Harga/L</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Odometer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $fuelLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-xs text-gray-500">
                                <?php echo e($f->date->format('d/m/Y')); ?></td>
                            <td class="px-4 py-3 text-gray-900"><?php echo e($f->vehicle?->plate_number ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-700">
                                <?php echo e(ucfirst($f->fuel_type)); ?></td>
                            <td class="px-4 py-3 text-right text-gray-900">
                                <?php echo e(number_format($f->liters, 1, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right hidden sm:table-cell text-gray-500">Rp
                                <?php echo e(number_format($f->price_per_liter, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp
                                <?php echo e(number_format($f->total_cost, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right hidden md:table-cell text-gray-500">
                                <?php echo e(number_format($f->odometer, 0, ',', '.')); ?> km</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum
                                ada log BBM.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($fuelLogs->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-100"><?php echo e($fuelLogs->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-add-fuel" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Catat BBM</h3>
                <button onclick="document.getElementById('modal-add-fuel').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('fleet.fuel-logs.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500'; ?>
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
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Driver</label>
                        <select name="driver_id" class="<?php echo e($cls); ?>">
                            <option value="">-- Tanpa Driver --</option>
                            <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($d->id); ?>"><?php echo e($d->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Tanggal
                            *</label><input type="date" name="date" required value="<?php echo e(date('Y-m-d')); ?>"
                            class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Odometer (km)
                            *</label><input type="number" name="odometer" required min="0"
                            class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Jenis BBM
                            *</label>
                        <select name="fuel_type" required class="<?php echo e($cls); ?>">
                            <option value="pertalite">Pertalite</option>
                            <option value="pertamax">Pertamax</option>
                            <option value="pertamax_turbo">Pertamax Turbo</option>
                            <option value="solar">Solar</option>
                            <option value="dexlite">Dexlite</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Liter
                            *</label><input type="number" name="liters" required min="0.01" step="0.01"
                            class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Harga/Liter
                            *</label><input type="number" name="price_per_liter" required min="0" step="100"
                            value="13900" class="<?php echo e($cls); ?>"></div>
                    <div><label
                            class="block text-xs font-medium text-gray-600 mb-1">SPBU</label><input
                            type="text" name="station" placeholder="SPBU 34.xxx" class="<?php echo e($cls); ?>">
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">No.
                            Struk</label><input type="text" name="receipt_number" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-fuel').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\fleet\fuel-logs.blade.php ENDPATH**/ ?>