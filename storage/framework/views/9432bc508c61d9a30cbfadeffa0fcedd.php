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
     <?php $__env->slot('header', null, []); ?> Template Kontrak <?php $__env->endSlot(); ?>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <a href="<?php echo e(route('contracts.index')); ?>" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">← Daftar Kontrak</a>
        <div class="flex-1"></div>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'contracts', 'create')): ?>
        <button onclick="document.getElementById('modal-add-tpl').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Template</button>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-center">Kategori</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-900"><?php echo e($t->name); ?></td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500"><?php echo e(['service'=>'Jasa','lease'=>'Sewa','supply'=>'Supply','maintenance'=>'Maintenance','subscription'=>'Langganan'][$t->category] ?? $t->category); ?></td>
                        <td class="px-4 py-3 text-center">
                            <?php if($t->is_active): ?><span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Aktif</span>
                            <?php else: ?><span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Nonaktif</span><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'contracts', 'delete')): ?>
                            <form method="POST" action="<?php echo e(route('contracts.templates.destroy', $t)); ?>" class="inline" onsubmit="return confirm('Hapus template ini?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="px-4 py-12 text-center text-gray-400">Belum ada template.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($templates->hasPages()): ?><div class="px-4 py-3 border-t border-gray-100"><?php echo e($templates->links()); ?></div><?php endif; ?>
    </div>

    
    <div id="modal-add-tpl" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Buat Template</h3>
                <button onclick="document.getElementById('modal-add-tpl').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('contracts.templates.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; ?>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Nama *</label><input type="text" name="name" required placeholder="Template Kontrak Sewa" class="<?php echo e($cls); ?>"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Kategori *</label>
                    <select name="category" required class="<?php echo e($cls); ?>">
                        <option value="service">Jasa</option><option value="lease">Sewa</option><option value="supply">Supply</option><option value="maintenance">Maintenance</option><option value="subscription">Langganan</option>
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Body Template</label><textarea name="body_template" rows="4" placeholder="Isi template kontrak... Gunakan {customer_name}, {start_date}, {end_date}, {value}" class="<?php echo e($cls); ?>"></textarea></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Default Terms</label><textarea name="default_terms" rows="3" class="<?php echo e($cls); ?>"></textarea></div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-tpl').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\contracts\templates.blade.php ENDPATH**/ ?>