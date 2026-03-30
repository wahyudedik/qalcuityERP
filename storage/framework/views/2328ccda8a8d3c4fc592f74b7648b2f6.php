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
     <?php $__env->slot('header', null, []); ?> WMS — Zone & Bin Location <?php $__env->endSlot(); ?>

    
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <form method="GET" class="flex gap-2">
            <select name="warehouse_id" onchange="this.form.submit()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($w->id); ?>" <?php if($warehouseId==$w->id): echo 'selected'; endif; ?>><?php echo e($w->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </form>
        <div class="flex-1 grid grid-cols-2 sm:grid-cols-4 gap-3">
            <?php $__currentLoopData = [['Zone',$stats['zones'],'blue'],['Total Bin',$stats['bins'],'gray'],['Terisi',$stats['occupied'],'green'],['Produk',$stats['products'],'purple']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$l,$v,$c]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-3 text-center">
                <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($l); ?></p>
                <p class="text-xl font-bold text-<?php echo e($c); ?>-500"><?php echo e($v); ?></p>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <?php if($zones->isNotEmpty()): ?>
    <div class="flex flex-wrap gap-2 mb-4">
        <a href="?warehouse_id=<?php echo e($warehouseId); ?>" class="px-3 py-1.5 text-xs rounded-xl <?php echo e(!request('zone_id') ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300'); ?>">Semua</a>
        <?php $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $z): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="?warehouse_id=<?php echo e($warehouseId); ?>&zone_id=<?php echo e($z->id); ?>" class="px-3 py-1.5 text-xs rounded-xl <?php echo e(request('zone_id')==$z->id ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300'); ?>">
            <?php echo e($z->code); ?> — <?php echo e($z->name); ?> (<?php echo e($z->bins_count); ?>)
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    
    <div class="flex flex-wrap gap-2 mb-4">
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'wms', 'create')): ?>
        <button onclick="document.getElementById('modal-zone').classList.remove('hidden')" class="text-xs px-3 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Zone</button>
        <button onclick="document.getElementById('modal-bin').classList.remove('hidden')" class="text-xs px-3 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700">+ Bin</button>
        <button onclick="document.getElementById('modal-bulk').classList.remove('hidden')" class="text-xs px-3 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700">⚡ Bulk Bin</button>
        <button onclick="document.getElementById('modal-putaway').classList.remove('hidden')" class="text-xs px-3 py-2 bg-amber-600 text-white rounded-xl hover:bg-amber-700">📦 Putaway</button>
        <?php endif; ?>
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
        <?php $__empty_1 = true; $__currentLoopData = $bins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $occupied = $bin->stocks->sum('quantity') > 0;
            $bc = $occupied ? 'border-green-300 dark:border-green-500/30 bg-green-50 dark:bg-green-500/5' : 'border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b]';
        ?>
        <div class="rounded-xl border <?php echo e($bc); ?> p-3 text-center">
            <p class="font-mono text-xs font-bold text-gray-900 dark:text-white"><?php echo e($bin->code); ?></p>
            <p class="text-[10px] text-gray-400 dark:text-slate-500"><?php echo e($bin->zone->name ?? '-'); ?></p>
            <?php if($occupied): ?>
            <div class="mt-1 space-y-0.5">
                <?php $__currentLoopData = $bin->stocks->where('quantity', '>', 0)->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <p class="text-[10px] text-gray-600 dark:text-slate-300 truncate"><?php echo e($bs->product->name ?? '?'); ?>: <?php echo e(number_format($bs->quantity, 0)); ?></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($bin->stocks->where('quantity', '>', 0)->count() > 3): ?>
                <p class="text-[10px] text-gray-400">+<?php echo e($bin->stocks->where('quantity', '>', 0)->count() - 3); ?></p>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-1">Kosong</p>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-span-full text-center py-12 text-gray-400 dark:text-slate-500 text-sm">Belum ada bin. Buat zone dan bin terlebih dahulu.</div>
        <?php endif; ?>
    </div>
    <?php if($bins->hasPages()): ?><div class="mt-4"><?php echo e($bins->links()); ?></div><?php endif; ?>

    
    <div id="modal-zone" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Zone</h3>
                <button onclick="document.getElementById('modal-zone').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('wms.zones.store')); ?>" class="p-6 space-y-3">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="warehouse_id" value="<?php echo e($warehouseId); ?>">
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Kode *</label><input type="text" name="code" required maxlength="10" placeholder="Z01" class="<?php echo e($cls); ?>"></div>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Nama *</label><input type="text" name="name" required placeholder="Zona Dry Storage" class="<?php echo e($cls); ?>"></div>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                    <select name="type" required class="<?php echo e($cls); ?>"><option value="general">General</option><option value="cold">Cold Storage</option><option value="hazmat">Hazmat</option><option value="staging">Staging</option><option value="returns">Returns</option></select>
                </div>
                <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
            </form>
        </div>
    </div>

    
    <div id="modal-bin" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Bin</h3>
                <button onclick="document.getElementById('modal-bin').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('wms.bins.store')); ?>" class="p-6 space-y-3">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="warehouse_id" value="<?php echo e($warehouseId); ?>">
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Zone</label>
                    <select name="zone_id" class="<?php echo e($cls); ?>"><option value="">-- Tanpa Zone --</option>
                        <?php $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $z): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($z->id); ?>"><?php echo e($z->code); ?> — <?php echo e($z->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Aisle</label><input type="text" name="aisle" maxlength="10" placeholder="01" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Rack</label><input type="text" name="rack" maxlength="10" placeholder="01" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Shelf</label><input type="text" name="shelf" maxlength="10" placeholder="01" class="<?php echo e($cls); ?>"></div>
                </div>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Tipe</label>
                    <select name="bin_type" class="<?php echo e($cls); ?>"><option value="storage">Storage</option><option value="picking">Picking</option><option value="staging">Staging</option><option value="returns">Returns</option></select>
                </div>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Kapasitas Maks (0=unlimited)</label><input type="number" name="max_capacity" min="0" value="0" class="<?php echo e($cls); ?>"></div>
                <button type="submit" class="w-full py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Simpan</button>
            </form>
        </div>
    </div>

    
    <div id="modal-bulk" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">⚡ Bulk Create Bin</h3>
                <button onclick="document.getElementById('modal-bulk').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('wms.bins.bulk')); ?>" class="p-6 space-y-3">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="warehouse_id" value="<?php echo e($warehouseId); ?>">
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Zone</label>
                    <select name="zone_id" class="<?php echo e($cls); ?>"><option value="">-- Tanpa Zone --</option>
                        <?php $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $z): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($z->id); ?>"><?php echo e($z->code); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Aisle Dari</label><input type="number" name="aisle_from" required min="1" value="1" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Aisle Sampai</label><input type="number" name="aisle_to" required min="1" value="3" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Rack Dari</label><input type="number" name="rack_from" required min="1" value="1" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Rack Sampai</label><input type="number" name="rack_to" required min="1" value="5" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Shelf Dari</label><input type="number" name="shelf_from" required min="1" value="1" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Shelf Sampai</label><input type="number" name="shelf_to" required min="1" value="4" class="<?php echo e($cls); ?>"></div>
                </div>
                <div><select name="bin_type" class="<?php echo e($cls); ?>"><option value="storage">Storage</option><option value="picking">Picking</option></select></div>
                <button type="submit" class="w-full py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">Generate Bins</button>
            </form>
        </div>
    </div>

    
    <div id="modal-putaway" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">📦 Putaway Barang</h3>
                <button onclick="document.getElementById('modal-putaway').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('wms.putaway')); ?>" class="p-6 space-y-3">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Produk *</label>
                    <select name="product_id" required class="<?php echo e($cls); ?>"><option value="">-- Pilih --</option></select>
                </div>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Bin Lokasi *</label>
                    <select name="bin_id" required class="<?php echo e($cls); ?>"><option value="">-- Pilih --</option>
                        <?php $__currentLoopData = $bins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($b->id); ?>"><?php echo e($b->code); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Qty *</label><input type="number" name="quantity" required min="0.001" step="0.001" class="<?php echo e($cls); ?>"></div>
                <button type="submit" class="w-full py-2 text-sm bg-amber-600 text-white rounded-xl hover:bg-amber-700">Putaway</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\wms\index.blade.php ENDPATH**/ ?>