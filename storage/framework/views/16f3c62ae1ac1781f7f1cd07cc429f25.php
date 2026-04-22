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
     <?php $__env->slot('header', null, []); ?> Transfer Stok Antar Gudang <?php $__env->endSlot(); ?>

    <div class="space-y-5">

        <?php if(session('success')): ?>
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Buat Transfer</h2>
                <form method="POST" action="<?php echo e(route('inventory.transfers.store')); ?>" id="transfer-form" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Dari Gudang <span class="text-red-400">*</span></label>
                        <select name="from_warehouse_id" id="from_wh" required
                            class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih gudang asal...</option>
                            <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Ke Gudang <span class="text-red-400">*</span></label>
                        <select name="to_warehouse_id" id="to_wh" required
                            class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih gudang tujuan...</option>
                            <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Catatan</label>
                        <input type="text" name="notes" placeholder="Opsional"
                            class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs text-gray-500 dark:text-slate-400">Produk yang Ditransfer</label>
                            <button type="button" id="add-item" class="text-xs px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">+ Tambah</button>
                        </div>
                        <div id="items-container" class="space-y-2">
                            <div class="item-row flex gap-2">
                                <select name="items[0][product_id]" required
                                    class="flex-1 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none">
                                    <option value="">Produk...</option>
                                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <input type="number" name="items[0][quantity]" min="1" value="1" required placeholder="Qty"
                                    class="w-20 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none">
                                <button type="button" class="remove-item text-red-400 hover:text-red-300 transition text-sm">✕</button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                        Proses Transfer
                    </button>
                </form>
            </div>

            
            <div class="lg:col-span-2 space-y-4">
                
                <form method="GET" class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 flex flex-wrap gap-3">
                    <select name="warehouse_id" class="bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none">
                        <option value="">Semua Gudang</option>
                        <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($w->id); ?>" <?php echo e(request('warehouse_id') == $w->id ? 'selected' : ''); ?>><?php echo e($w->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                        class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none">
                    <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"
                        class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none">
                    <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white rounded-xl text-sm hover:bg-gray-200 dark:hover:bg-white/20 transition">Filter</button>
                </form>

                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                    <?php if($transfers->isEmpty()): ?>
                        <div class="px-6 py-12 text-center text-gray-400 text-sm">Belum ada riwayat transfer.</div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-white/5 text-xs text-gray-500 dark:text-slate-400">
                                        <th class="px-4 py-3 text-left">Ref</th>
                                        <th class="px-4 py-3 text-left">Produk</th>
                                        <th class="px-4 py-3 text-left">Dari</th>
                                        <th class="px-4 py-3 text-left">Ke</th>
                                        <th class="px-4 py-3 text-right">Qty</th>
                                        <th class="px-4 py-3 text-left">Oleh</th>
                                        <th class="px-4 py-3 text-left">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                                    <?php $__currentLoopData = $transfers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                            <td class="px-4 py-3 font-mono text-xs text-blue-400"><?php echo e($t->reference ?? '-'); ?></td>
                                            <td class="px-4 py-3 text-gray-700 dark:text-slate-300"><?php echo e($t->product->name ?? '-'); ?></td>
                                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400"><?php echo e($t->warehouse->name ?? '-'); ?></td>
                                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400"><?php echo e($t->toWarehouse->name ?? '-'); ?></td>
                                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white"><?php echo e($t->quantity); ?></td>
                                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400"><?php echo e($t->user->name ?? '-'); ?></td>
                                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400"><?php echo e($t->created_at->format('d/m/Y H:i')); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if($transfers->hasPages()): ?>
                            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5"><?php echo e($transfers->links()); ?></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function() {
        let idx = 1;
        const products = <?php echo json_encode($products->map(function($p) { return ['id' => $p->id, 'name' => $p->name]; }), 512) ?>;

        function buildSelect(i) {
            return `<option value="">Produk...</option>` +
                products.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
        }

        document.getElementById('add-item').addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'item-row flex gap-2';
            row.innerHTML = `
                <select name="items[${idx}][product_id]" required
                    class="flex-1 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none">
                    ${buildSelect(idx)}
                </select>
                <input type="number" name="items[${idx}][quantity]" min="1" value="1" required placeholder="Qty"
                    class="w-20 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none">
                <button type="button" class="remove-item text-red-400 hover:text-red-300 transition text-sm">✕</button>`;
            document.getElementById('items-container').appendChild(row);
            row.querySelector('.remove-item').addEventListener('click', () => {
                if (document.querySelectorAll('.item-row').length > 1) row.remove();
            });
            idx++;
        });

        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', function() {
                if (document.querySelectorAll('.item-row').length > 1) btn.closest('.item-row').remove();
            });
        });
    })();
    </script>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\inventory\transfers.blade.php ENDPATH**/ ?>