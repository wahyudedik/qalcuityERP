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
     <?php $__env->slot('header', null, []); ?> Stock Opname <?php $__env->endSlot(); ?>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <div class="flex-1"></div>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'wms', 'create')): ?>
        <button onclick="document.getElementById('modal-opname').classList.remove('hidden')" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Sesi Opname</button>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr><th class="px-4 py-3 text-left">Nomor</th><th class="px-4 py-3 text-left">Gudang</th><th class="px-4 py-3 text-center">Tanggal</th><th class="px-4 py-3 text-center">Status</th><th class="px-4 py-3 text-center">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $sc = ['draft'=>'gray','in_progress'=>'amber','completed'=>'green'][$s->status] ?? 'gray'; ?>
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs text-gray-900"><?php echo e($s->number); ?></td>
                        <td class="px-4 py-3 text-gray-700"><?php echo e($s->warehouse->name ?? '-'); ?></td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500"><?php echo e($s->opname_date->format('d/m/Y')); ?></td>
                        <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 $sc }}-500/20 $sc }}-400"><?php echo e(ucfirst($s->status)); ?></span></td>
                        <td class="px-4 py-3 text-center"><a href="<?php echo e(route('wms.opname.show', $s)); ?>" class="text-xs px-2 py-1 border border-gray-200 rounded-lg text-gray-600">Detail</a></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">Belum ada sesi opname.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modal-opname" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Buat Sesi Opname</h3>
                <button onclick="document.getElementById('modal-opname').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('wms.opname.store')); ?>" class="p-6 space-y-3">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; ?>
                <div><label class="block text-xs text-gray-600 mb-1">Gudang *</label>
                    <select name="warehouse_id" required class="<?php echo e($cls); ?>"><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                </div>
                <div><label class="block text-xs text-gray-600 mb-1">Tanggal *</label><input type="date" name="opname_date" required value="<?php echo e(date('Y-m-d')); ?>" class="<?php echo e($cls); ?>"></div>
                <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat & Auto-populate</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\wms\opname.blade.php ENDPATH**/ ?>