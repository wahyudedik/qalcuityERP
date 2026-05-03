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
     <?php $__env->slot('header', null, []); ?> Data Gudang <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Gudang</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e($totalWarehouses); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Gudang Aktif</p>
            <p class="text-2xl font-bold text-green-600 mt-1"><?php echo e($activeWarehouses); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Gudang Nonaktif</p>
            <p class="text-2xl font-bold text-gray-500 mt-1"><?php echo e($totalWarehouses - $activeWarehouses); ?></p>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama / kode gudang..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="active"   <?php if(request('status')==='active'): echo 'selected'; endif; ?>>Aktif</option>
                    <option value="inactive" <?php if(request('status')==='inactive'): echo 'selected'; endif; ?>>Nonaktif</option>
                </select>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
            </form>
            <div class="flex gap-2">
                <a href="<?php echo e(route('inventory.index')); ?>" class="px-3 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Inventori</a>
                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'warehouses', 'create')): ?>
                <button onclick="document.getElementById('modal-add-warehouse').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Gudang</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama Gudang</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Kode</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Alamat</th>
                        <th class="px-4 py-3 text-right hidden lg:table-cell">Jml Produk</th>
                        <th class="px-4 py-3 text-right">Total Stok</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900"><?php echo e($wh->name); ?></p>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell font-mono text-xs text-gray-500"><?php echo e($wh->code); ?></td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500 text-xs max-w-xs truncate"><?php echo e($wh->address ?? '-'); ?></td>
                        <td class="px-4 py-3 text-right hidden lg:table-cell text-gray-700"><?php echo e(number_format($wh->product_stocks_count ?? 0)); ?></td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900"><?php echo e(number_format($wh->product_stocks_sum_quantity ?? 0)); ?></td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell">
                            <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($wh->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>">
                                <?php echo e($wh->is_active ? 'Aktif' : 'Nonaktif'); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'warehouses', 'edit')): ?>
                                <button onclick="openEditWarehouse(<?php echo e($wh->id); ?>, <?php echo \Illuminate\Support\Js::from($wh->name)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($wh->code ?? '')->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($wh->address ?? '')->toHtml() ?>)"
                                    class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <?php endif; ?>
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'warehouses', 'edit')): ?>
                                <form method="POST" action="<?php echo e(route('warehouses.toggle', $wh)); ?>">
                                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="p-1.5 rounded-lg <?php echo e($wh->is_active ? 'text-yellow-500 hover:bg-yellow-50' : 'text-green-500 hover:bg-green-50'); ?>" title="<?php echo e($wh->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'warehouses', 'delete')): ?>
                                <form method="POST" action="<?php echo e(route('warehouses.destroy', $wh)); ?>" onsubmit="return confirm('Hapus gudang ini?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada gudang. Klik "+ Gudang" untuk menambahkan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($warehouses->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100"><?php echo e($warehouses->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-add-warehouse" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Tambah Gudang</h3>
                <button onclick="document.getElementById('modal-add-warehouse').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('warehouses.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Gudang *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kode (opsional, auto-generate jika kosong)</label>
                    <input type="text" name="code" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Alamat</label>
                    <textarea name="address" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-warehouse').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-edit-warehouse" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Edit Gudang</h3>
                <button onclick="document.getElementById('modal-edit-warehouse').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-edit-warehouse" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Gudang *</label>
                    <input type="text" id="edit-name" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kode</label>
                    <input type="text" id="edit-code" name="code" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Alamat</label>
                    <textarea id="edit-address" name="address" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit-warehouse').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    function openEditWarehouse(id, name, code, address) {
        const form = document.getElementById('form-edit-warehouse');
        form.action = '/warehouses/' + id;
        document.getElementById('edit-name').value    = name;
        document.getElementById('edit-code').value    = code;
        document.getElementById('edit-address').value = address;
        document.getElementById('modal-edit-warehouse').classList.remove('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\warehouses\index.blade.php ENDPATH**/ ?>