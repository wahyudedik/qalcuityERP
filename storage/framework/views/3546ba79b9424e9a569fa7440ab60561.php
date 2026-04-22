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
     <?php $__env->slot('header', null, []); ?> Produksi & Work Order <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Pending</p>
            <p class="text-2xl font-bold text-amber-500"><?php echo e($stats['pending']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Sedang Dikerjakan</p>
            <p class="text-2xl font-bold text-blue-500"><?php echo e($stats['in_progress']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Selesai</p>
            <p class="text-2xl font-bold text-green-500"><?php echo e($stats['completed']); ?></p>
        </div>
    </div>

    
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nomor WO / produk..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>>Pending</option>
                <option value="in_progress" <?php if(request('status') === 'in_progress'): echo 'selected'; endif; ?>>Sedang Dikerjakan</option>
                <option value="completed" <?php if(request('status') === 'completed'): echo 'selected'; endif; ?>>Selesai</option>
                <option value="cancelled" <?php if(request('status') === 'cancelled'): echo 'selected'; endif; ?>>Dibatalkan</option>
            </select>
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <div class="flex gap-2">
            <a href="<?php echo e(route('production.recipes')); ?>"
                class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                Resep/BOM
            </a>
            <a href="<?php echo e(route('manufacturing.bom')); ?>"
                class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                BOM Multi-Level
            </a>
            <a href="<?php echo e(route('manufacturing.mrp')); ?>"
                class="px-3 py-2 text-sm border border-purple-200 dark:border-purple-500/30 rounded-xl text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-500/10">
                MRP
            </a>
            <button onclick="document.getElementById('modal-create-wo').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Buat WO</button>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor WO</th>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-right hidden sm:table-cell">Target</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Output</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $workOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900 dark:text-white">
                                <a href="<?php echo e(route('production.show', $wo)); ?>"
                                    class="hover:text-blue-500"><?php echo e($wo->number); ?></a>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-slate-300">
                                <?php echo e($wo->product->name ?? '-'); ?>

                                <?php if($wo->recipe): ?>
                                    <span
                                        class="text-xs text-gray-400 dark:text-slate-500">(<?php echo e($wo->recipe->name); ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right hidden sm:table-cell text-gray-900 dark:text-white">
                                <?php echo e(number_format($wo->target_quantity, 0, ',', '.')); ?> <?php echo e($wo->unit); ?>

                            </td>
                            <td class="px-4 py-3 text-right hidden md:table-cell">
                                <?php
                                    $good = $wo->totalGoodQty();
                                    $reject = $wo->totalRejectQty();
                                ?>
                                <span class="text-green-500"><?php echo e($good); ?></span>
                                <?php if($reject > 0): ?>
                                    <span class="text-red-400 text-xs">/ <?php echo e($reject); ?> reject</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php
                                    $colors = [
                                        'pending' => 'amber',
                                        'in_progress' => 'blue',
                                        'completed' => 'green',
                                        'cancelled' => 'red',
                                    ];
                                    $labels = [
                                        'pending' => 'Pending',
                                        'in_progress' => 'Dikerjakan',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Batal',
                                    ];
                                    $c = $colors[$wo->status] ?? 'gray';
                                ?>
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($c); ?>-100 text-<?php echo e($c); ?>-700 dark:bg-<?php echo e($c); ?>-500/20 dark:text-<?php echo e($c); ?>-400">
                                    <?php echo e($labels[$wo->status] ?? $wo->status); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="<?php echo e(route('production.show', $wo)); ?>"
                                        class="text-xs px-2 py-1 border border-gray-200 dark:border-white/10 rounded-lg text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                                        Detail
                                    </a>
                                    <?php if($wo->status === 'pending'): ?>
                                        <form method="POST" action="<?php echo e(route('production.status', $wo)); ?>"
                                            class="inline">
                                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit"
                                                class="text-xs px-2 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Mulai</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if($wo->status === 'in_progress'): ?>
                                        <button onclick="openOutputModal('<?php echo e($wo->id); ?>','<?php echo e($wo->number); ?>')"
                                            class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Output</button>
                                        <?php if($wo->bom_id && !$wo->materials_consumed): ?>
                                            <a href="<?php echo e(route('manufacturing.work-orders.scan-materials', $wo)); ?>"
                                                class="text-xs px-2 py-1 bg-purple-600 text-white rounded-lg hover:bg-purple-700 inline-flex items-center gap-1 mb-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Scan & Consume
                                            </a>
                                            <form method="POST"
                                                action="<?php echo e(url('manufacturing')); ?>/<?php echo e($wo->id); ?>/consume"
                                                class="inline"
                                                onsubmit="return confirm('Konsumsi material dari stok?')">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="text-xs px-2 py-1 bg-amber-600 text-white rounded-lg hover:bg-amber-700">Konsumsi</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum
                                ada work order.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($workOrders->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($workOrders->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-create-wo" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Work Order</h3>
                <button onclick="document.getElementById('modal-create-wo').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('production.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Produk
                            *</label>
                        <select name="product_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Produk --</option>
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Resep/BOM</label>
                        <select name="recipe_id"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Tanpa Resep --</option>
                            <?php $__currentLoopData = $recipes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($r->id); ?>"><?php echo e($r->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">BOM
                            Multi-Level</label>
                        <select name="bom_id"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Tanpa BOM --</option>
                            <?php $__currentLoopData = $boms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($b->id); ?>"><?php echo e($b->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Target Produksi
                            *</label>
                        <input type="number" name="target_quantity" required min="0.001" step="0.001"
                            placeholder="100"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Biaya Tenaga
                            Kerja</label>
                        <input type="number" name="labor_cost" min="0" step="1000" placeholder="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Biaya
                            Overhead</label>
                        <input type="number" name="overhead_cost" min="0" step="1000" placeholder="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                        <input type="text" name="notes"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button"
                        onclick="document.getElementById('modal-create-wo').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat WO</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-output" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Catat Output Produksi</h3>
                <button onclick="document.getElementById('modal-output').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-output" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <p class="text-sm text-gray-600 dark:text-slate-400">WO: <span id="output-wo"
                        class="font-mono font-semibold text-gray-900 dark:text-white"></span></p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Qty Bagus
                            *</label>
                        <input type="number" name="good_qty" required min="0" step="0.001"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Qty
                            Reject</label>
                        <input type="number" name="reject_qty" min="0" step="0.001" value="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Alasan
                        Reject</label>
                    <input type="text" name="reject_reason"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="auto_complete" value="1" class="rounded">
                    <span class="text-sm text-gray-700 dark:text-slate-300">Selesaikan WO & tambah stok otomatis</span>
                </label>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-output').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function openOutputModal(id, number) {
                document.getElementById('output-wo').textContent = number;
                document.getElementById('form-output').action = '<?php echo e(url('production')); ?>/' + id + '/output';
                document.getElementById('modal-output').classList.remove('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\production\index.blade.php ENDPATH**/ ?>