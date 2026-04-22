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
     <?php $__env->slot('header', null, []); ?> Picking List <?php $__env->endSlot(); ?>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <div class="flex gap-2">
            <?php $__currentLoopData = ['' => 'Semua', 'pending' => 'Pending', 'in_progress' => 'Progress', 'completed' => 'Selesai']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="?status=<?php echo e($v); ?>"
                    class="px-3 py-1.5 text-xs rounded-xl <?php echo e(request('status') === $v ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300'); ?>"><?php echo e($l); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="flex-1"></div>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'wms', 'create')): ?>
        <button onclick="document.getElementById('modal-pick').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Picking List</button>
        <?php endif; ?>
    </div>

    <div class="space-y-4">
        <?php $__empty_1 = true; $__currentLoopData = $lists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $sc = ['pending'=>'amber','in_progress'=>'blue','completed'=>'green','cancelled'=>'gray'][$list->status] ?? 'gray'; ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <span
                            class="font-mono text-sm font-bold text-gray-900 dark:text-white"><?php echo e($list->number); ?></span>
                        <span
                            class="text-xs text-gray-500 dark:text-slate-400 ml-2"><?php echo e($list->warehouse->name ?? '-'); ?></span>
                        <?php if($list->assignee): ?>
                            <span class="text-xs text-blue-500 ml-2">→ <?php echo e($list->assignee->name); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if(in_array($list->status, ['pending', 'in_progress'])): ?>
                            <a href="<?php echo e(route('wms.picking.scan', $list)); ?>"
                                class="inline-flex items-center gap-1 px-3 py-1 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9V6a1 1 0 011-1h3M3 15v3a1 1 0 001 1h3m11-4v3a1 1 0 01-1 1h-3m4-11h-3a1 1 0 00-1 1v3M9 3H6a1 1 0 00-1 1v3m0 6v3a1 1 0 001 1h3m6-10h3a1 1 0 011 1v3" />
                                </svg>
                                Scan & Pick
                            </a>
                        <?php endif; ?>
                        <span
                            class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 dark:bg-<?php echo e($sc); ?>-500/20 dark:text-<?php echo e($sc); ?>-400"><?php echo e(ucfirst(str_replace('_', ' ', $list->status))); ?></span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-xs text-gray-500 dark:text-slate-400">
                            <tr>
                                <th class="text-left py-1">Produk</th>
                                <th class="text-left py-1">Bin</th>
                                <th class="text-right py-1">Diminta</th>
                                <th class="text-right py-1">Diambil</th>
                                <th class="text-center py-1">Status</th>
                                <th class="text-center py-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <?php $__currentLoopData = $list->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $ic = ['pending'=>'amber','picked'=>'green','short'=>'red'][$item->status] ?? 'gray'; ?>
                                <tr>
                                    <td class="py-1.5 text-gray-900 dark:text-white"><?php echo e($item->product->name ?? '-'); ?>

                                    </td>
                                    <td class="py-1.5 font-mono text-xs text-gray-500 dark:text-slate-400">
                                        <?php echo e($item->bin->code ?? '-'); ?></td>
                                    <td class="py-1.5 text-right text-gray-700 dark:text-slate-300">
                                        <?php echo e(number_format($item->quantity_requested, 0)); ?></td>
                                    <td class="py-1.5 text-right text-gray-900 dark:text-white">
                                        <?php echo e(number_format($item->quantity_picked, 0)); ?></td>
                                    <td class="py-1.5 text-center"><span
                                            class="px-1.5 py-0.5 rounded text-[10px] bg-<?php echo e($ic); ?>-100 text-<?php echo e($ic); ?>-700 dark:bg-<?php echo e($ic); ?>-500/20 dark:text-<?php echo e($ic); ?>-400"><?php echo e(ucfirst($item->status)); ?></span>
                                    </td>
                                    <td class="py-1.5 text-center">
                                        <?php if($item->status === 'pending'): ?>
                                            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'wms', 'edit')): ?>
                                            <form method="POST" action="<?php echo e(route('wms.picking.confirm', $item)); ?>"
                                                class="inline flex items-center gap-1">
                                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                                <input type="number" name="quantity_picked"
                                                    value="<?php echo e($item->quantity_requested); ?>" min="0"
                                                    step="1"
                                                    class="w-16 px-1 py-0.5 text-xs rounded border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                                                <button type="submit"
                                                    class="text-xs px-2 py-0.5 bg-green-600 text-white rounded">✓</button>
                                            </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-12 text-gray-400 dark:text-slate-500 text-sm">Belum ada picking list.</div>
        <?php endif; ?>
    </div>
    <?php if($lists->hasPages()): ?>
        <div class="mt-4"><?php echo e($lists->links()); ?></div>
    <?php endif; ?>

    
    <div id="modal-pick" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Picking List</h3>
                <button onclick="document.getElementById('modal-pick').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('wms.picking.store')); ?>" class="p-6 space-y-3">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Gudang *</label>
                    <select name="warehouse_id" required class="<?php echo e($cls); ?>">
                        <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Assign ke</label>
                    <select name="assigned_to" class="<?php echo e($cls); ?>">
                        <option value="">-- Auto --</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div id="pick-items">
                    <p class="text-xs text-gray-400">Item akan ditambahkan setelah simpan (via edit).</p>
                </div>
                <p class="text-xs text-gray-400 dark:text-slate-500">Minimal 1 item. Tambah via JS di bawah.</p>
                <div id="pick-lines" class="space-y-2"></div>
                <button type="button" onclick="addPickLine()"
                    class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg">+ Item</button>
                <button type="submit"
                    class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat</button>
            </form>
        </div>
    </div>
    <?php $__env->startPush('scripts'); ?>
        <script>
            let pickIdx = 0;

            function addPickLine() {
                const i = pickIdx++;
                const c = document.getElementById('pick-lines');
                const d = document.createElement('div');
                d.className = 'grid grid-cols-2 gap-2';
                const cls =
                    'w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white';
                d.innerHTML =
                    `<input type="number" name="items[${i}][product_id]" required placeholder="ID Produk" class="${cls}"><input type="number" name="items[${i}][quantity]" required min="0.001" step="1" placeholder="Jml" class="${cls}">`;
                c.appendChild(d);
            }
            addPickLine();
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\wms\picking.blade.php ENDPATH**/ ?>