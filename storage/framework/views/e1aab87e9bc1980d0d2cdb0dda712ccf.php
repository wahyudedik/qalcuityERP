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
     <?php $__env->slot('header', null, []); ?> Rule Komisi <?php $__env->endSlot(); ?>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <a href="<?php echo e(route('commission.index')); ?>" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">← Komisi Sales</a>
        <div class="flex-1"></div>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'commission', 'create')): ?>
        <button onclick="document.getElementById('modal-add-rule').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Rule</button>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-center">Tipe</th>
                        <th class="px-4 py-3 text-right">Rate</th>
                        <th class="px-4 py-3 text-center">Basis</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $rules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-900"><?php echo e($r->name); ?></td>
                        <td class="px-4 py-3 text-center text-xs">
                            <span class="px-2 py-0.5 rounded-full <?php echo e(['flat_pct'=>'bg-blue-100 text-blue-700','tiered'=>'bg-purple-100 text-purple-700','flat_amount'=>'bg-green-100 text-green-700'][$r->type] ?? 'bg-gray-100 text-gray-500'); ?>">
                                <?php echo e(['flat_pct'=>'Flat %','tiered'=>'Tiered','flat_amount'=>'Flat Rp'][$r->type] ?? $r->type); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-900">
                            <?php if($r->type === 'flat_pct'): ?> <?php echo e($r->rate); ?>%
                            <?php elseif($r->type === 'flat_amount'): ?> Rp <?php echo e(number_format($r->rate, 0, ',', '.')); ?>

                            <?php else: ?> <span class="text-xs text-gray-400"><?php echo e(count($r->tiers ?? [])); ?> tier</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500"><?php echo e(['revenue'=>'Revenue','profit'=>'Profit','quantity'=>'Qty'][$r->basis] ?? $r->basis); ?></td>
                        <td class="px-4 py-3 text-center">
                            <?php if($r->is_active): ?><span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Aktif</span>
                            <?php else: ?><span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Nonaktif</span><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'commission', 'delete')): ?>
                            <form method="POST" action="<?php echo e(route('commission.rules.destroy', $r)); ?>" class="inline" onsubmit="return confirm('Hapus rule ini?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada rule komisi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($rules->hasPages()): ?><div class="px-4 py-3 border-t border-gray-100"><?php echo e($rules->links()); ?></div><?php endif; ?>
    </div>

    
    <div id="modal-add-rule" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Buat Rule Komisi</h3>
                <button onclick="document.getElementById('modal-add-rule').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('commission.rules.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; ?>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Nama *</label><input type="text" name="name" required placeholder="Komisi Sales Standard" class="<?php echo e($cls); ?>"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Tipe *</label>
                        <select name="type" id="rule-type" required onchange="toggleTiers()" class="<?php echo e($cls); ?>">
                            <option value="flat_pct">Flat %</option><option value="flat_amount">Flat Rp</option><option value="tiered">Tiered</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Basis *</label>
                        <select name="basis" required class="<?php echo e($cls); ?>">
                            <option value="revenue">Revenue</option><option value="profit">Profit</option><option value="quantity">Quantity</option>
                        </select>
                    </div>
                </div>
                <div id="rate-field"><label class="block text-xs font-medium text-gray-600 mb-1">Rate (% atau Rp)</label><input type="number" name="rate" min="0" step="0.01" class="<?php echo e($cls); ?>"></div>
                <div id="tiers-field" class="hidden">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tiers (JSON)</label>
                    <textarea name="tiers" rows="3" placeholder='[{"min":0,"max":10000000,"rate":2},{"min":10000000,"max":null,"rate":3}]' class="<?php echo e($cls); ?>"></textarea>
                    <p class="text-xs text-gray-400 mt-1">Format: [{"min":0,"max":10000000,"rate":2}, ...]</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-rule').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    function toggleTiers() {
        const t = document.getElementById('rule-type').value;
        document.getElementById('tiers-field').classList.toggle('hidden', t !== 'tiered');
        document.getElementById('rate-field').classList.toggle('hidden', t === 'tiered');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\commission\rules.blade.php ENDPATH**/ ?>