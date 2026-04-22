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
     <?php $__env->slot('header', null, []); ?> Landed Cost <?php $__env->endSlot(); ?>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nomor / deskripsi..."
                class="flex-1 min-w-[150px] px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = ['draft'=>'Draft','allocated'=>'Dialokasi','posted'=>'Diposting']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($v); ?>" <?php if(request('status')===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'landed_cost', 'create')): ?>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Landed Cost</button>
        <?php endif; ?>
    </div>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor</th>
                        <th class="px-4 py-3 text-left">PO</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Deskripsi</th>
                        <th class="px-4 py-3 text-right">Total Biaya</th>
                        <th class="px-4 py-3 text-center">Metode</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $landedCosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $sc = ['draft'=>'gray','allocated'=>'amber','posted'=>'green'][$lc->status] ?? 'gray';
                        $sl = ['draft'=>'Draft','allocated'=>'Dialokasi','posted'=>'Diposting'][$lc->status] ?? $lc->status;
                        $ml = ['by_value'=>'Nilai','by_quantity'=>'Qty','by_weight'=>'Berat','equal'=>'Rata'][$lc->allocation_method] ?? $lc->allocation_method;
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900 dark:text-white">
                            <a href="<?php echo e(route('landed-cost.show', $lc)); ?>" class="hover:text-blue-500"><?php echo e($lc->number); ?></a>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300 text-xs"><?php echo e($lc->purchaseOrder->number ?? '-'); ?></td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500 dark:text-slate-400"><?php echo e(Str::limit($lc->description, 40)); ?></td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp <?php echo e(number_format($lc->total_additional_cost, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500 dark:text-slate-400"><?php echo e($ml); ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 dark:bg-<?php echo e($sc); ?>-500/20 dark:text-<?php echo e($sc); ?>-400"><?php echo e($sl); ?></span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="<?php echo e(route('landed-cost.show', $lc)); ?>" class="text-xs px-2 py-1 border border-gray-200 dark:border-white/10 rounded-lg text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Detail</a>
                                <?php if($lc->status !== 'posted'): ?>
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'landed_cost', 'delete')): ?>
                                <form method="POST" action="<?php echo e(route('landed-cost.destroy', $lc)); ?>" class="inline" onsubmit="return confirm('Hapus?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                                </form>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada landed cost.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($landedCosts->hasPages()): ?><div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($landedCosts->links()); ?></div><?php endif; ?>
    </div>

    
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Landed Cost</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('landed-cost.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Purchase Order *</label>
                        <select name="purchase_order_id" required class="<?php echo e($cls); ?>">
                            <option value="">-- Pilih PO --</option>
                            <?php $__currentLoopData = $purchaseOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($po->id); ?>"><?php echo e($po->number); ?> — <?php echo e($po->supplier->name ?? '-'); ?> (Rp <?php echo e(number_format($po->total, 0, ',', '.')); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal *</label>
                        <input type="date" name="date" required value="<?php echo e(date('Y-m-d')); ?>" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Metode Alokasi *</label>
                        <select name="allocation_method" required class="<?php echo e($cls); ?>">
                            <option value="by_value">Berdasarkan Nilai</option>
                            <option value="by_quantity">Berdasarkan Qty</option>
                            <option value="by_weight">Berdasarkan Berat</option>
                            <option value="equal">Rata (Equal)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi</label>
                        <input type="text" name="description" placeholder="Biaya impor PO-xxx" class="<?php echo e($cls); ?>">
                    </div>
                </div>

                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">Komponen Biaya</h4>
                        <button type="button" onclick="addCostLine()" class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">+ Tambah</button>
                    </div>
                    <div id="cost-lines" class="space-y-2"></div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    let costIdx = 0;
    function addCostLine() {
        const i = costIdx++;
        const container = document.getElementById('cost-lines');
        const div = document.createElement('div');
        div.className = 'grid grid-cols-12 gap-2 items-end';
        div.id = 'cost-line-' + i;
        const cls = 'w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white';
        div.innerHTML = `
            <div class="col-span-3"><select name="components[${i}][type]" required class="${cls}">
                <option value="freight">Freight</option><option value="customs">Bea Masuk</option><option value="insurance">Asuransi</option><option value="handling">Handling</option><option value="other">Lainnya</option>
            </select></div>
            <div class="col-span-3"><input type="text" name="components[${i}][name]" required placeholder="Nama biaya" class="${cls}"></div>
            <div class="col-span-2"><input type="number" name="components[${i}][amount]" required min="0.01" step="0.01" placeholder="Jumlah" class="${cls}"></div>
            <div class="col-span-2"><input type="text" name="components[${i}][vendor]" placeholder="Vendor" class="${cls}"></div>
            <div class="col-span-1"><input type="text" name="components[${i}][reference]" placeholder="Ref" class="${cls}"></div>
            <div class="col-span-1"><button type="button" onclick="document.getElementById('cost-line-${i}').remove()" class="text-red-500 hover:text-red-700 text-xs">✕</button></div>
        `;
        container.appendChild(div);
    }
    addCostLine();
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\landed-cost\index.blade.php ENDPATH**/ ?>