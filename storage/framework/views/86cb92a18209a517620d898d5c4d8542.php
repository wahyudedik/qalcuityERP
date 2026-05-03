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
     <?php $__env->slot('header', null, []); ?> Data Supplier <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['total']); ?></p>
            <p class="text-xs text-gray-500 mt-0.5">Total Supplier</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-green-600"><?php echo e($stats['active']); ?></p>
            <p class="text-xs text-gray-500 mt-0.5">Aktif</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-400"><?php echo e($stats['inactive']); ?></p>
            <p class="text-xs text-gray-500 mt-0.5">Nonaktif</p>
        </div>
    </div>

    
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
        <form method="GET" class="flex gap-2 flex-1">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama, perusahaan, email..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="active"   <?php echo e(request('status') === 'active'   ? 'selected' : ''); ?>>Aktif</option>
                <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Nonaktif</option>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <div class="flex gap-2">
            <a href="<?php echo e(route('purchasing.orders')); ?>"
                class="px-3 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                Purchase Order
            </a>
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'suppliers', 'create')): ?>
            <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Supplier</button>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Perusahaan</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Telepon</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Email</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Bank</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 <?php echo e(!$sup->is_active ? 'opacity-60' : ''); ?>">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900"><?php echo e($sup->name); ?></p>
                            <?php if($sup->npwp): ?>
                            <p class="text-xs text-gray-400">NPWP: <?php echo e($sup->npwp); ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500"><?php echo e($sup->company ?? '-'); ?></td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500"><?php echo e($sup->phone ?? '-'); ?></td>
                        <td class="px-4 py-3 hidden lg:table-cell text-gray-500"><?php echo e($sup->email ?? '-'); ?></td>
                        <td class="px-4 py-3 hidden lg:table-cell text-gray-500">
                            <?php if($sup->bank_name): ?>
                                <p class="text-xs"><?php echo e($sup->bank_name); ?></p>
                                <p class="text-xs text-gray-400"><?php echo e($sup->bank_account); ?></p>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell">
                            <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($sup->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>">
                                <?php echo e($sup->is_active ? 'Aktif' : 'Nonaktif'); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'suppliers', 'edit')): ?>
                                <button onclick="openEdit(<?php echo e($sup->id); ?>, <?php echo e(json_encode($sup->name)); ?>, <?php echo e(json_encode($sup->company ?? '')); ?>, <?php echo e(json_encode($sup->phone ?? '')); ?>, <?php echo e(json_encode($sup->email ?? '')); ?>, <?php echo e(json_encode($sup->address ?? '')); ?>, <?php echo e(json_encode($sup->npwp ?? '')); ?>, <?php echo e(json_encode($sup->bank_name ?? '')); ?>, <?php echo e(json_encode($sup->bank_account ?? '')); ?>, <?php echo e(json_encode($sup->bank_holder ?? '')); ?>, <?php echo e($sup->is_active ? 'true' : 'false'); ?>)"
                                    class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="<?php echo e(route('suppliers.toggle', $sup)); ?>">
                                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"
                                        title="<?php echo e($sup->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>">
                                        <?php if($sup->is_active): ?>
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <?php else: ?>
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <?php endif; ?>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'suppliers', 'delete')): ?>
                                <form method="POST" action="<?php echo e(route('suppliers.destroy', $sup)); ?>"
                                    onsubmit="return confirm('Hapus supplier <?php echo e(addslashes($sup->name)); ?>?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="p-1.5 rounded-lg text-red-400 hover:bg-red-50" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            Belum ada supplier.
                            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'suppliers', 'create')): ?>
                            <button onclick="document.getElementById('modal-add').classList.remove('hidden')" class="text-blue-500 hover:underline ml-1">Tambah sekarang</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($suppliers->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100"><?php echo e($suppliers->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                <h3 class="font-semibold text-gray-900">Tambah Supplier</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('suppliers.store')); ?>" class="p-6 space-y-3">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama <span class="text-red-400">*</span></label>
                        <input type="text" name="name" required value="<?php echo e(old('name')); ?>"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Perusahaan</label>
                        <input type="text" name="company" value="<?php echo e(old('company')); ?>"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">NPWP</label>
                        <input type="text" name="npwp" value="<?php echo e(old('npwp')); ?>"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Telepon</label>
                        <input type="text" name="phone" value="<?php echo e(old('phone')); ?>"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                        <input type="email" name="email" value="<?php echo e(old('email')); ?>"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Alamat</label>
                        <textarea name="address" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e(old('address')); ?></textarea>
                    </div>
                    <div class="col-span-2 pt-1">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Info Bank</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Bank</label>
                        <input type="text" name="bank_name" value="<?php echo e(old('bank_name')); ?>" placeholder="BCA, BRI, Mandiri..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">No. Rekening</label>
                        <input type="text" name="bank_account" value="<?php echo e(old('bank_account')); ?>"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Atas Nama</label>
                        <input type="text" name="bank_holder" value="<?php echo e(old('bank_holder')); ?>"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                <h3 class="font-semibold text-gray-900">Edit Supplier</h3>
                <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-edit" method="POST" class="p-6 space-y-3">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama <span class="text-red-400">*</span></label>
                        <input type="text" id="e-name" name="name" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Perusahaan</label>
                        <input type="text" id="e-company" name="company"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">NPWP</label>
                        <input type="text" id="e-npwp" name="npwp"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Telepon</label>
                        <input type="text" id="e-phone" name="phone"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                        <input type="email" id="e-email" name="email"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Alamat</label>
                        <textarea id="e-address" name="address" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="col-span-2 pt-1">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Info Bank</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Bank</label>
                        <input type="text" id="e-bank-name" name="bank_name"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">No. Rekening</label>
                        <input type="text" id="e-bank-account" name="bank_account"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Atas Nama</label>
                        <input type="text" id="e-bank-holder" name="bank_holder"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2 flex items-center gap-2">
                        <input type="checkbox" id="e-active" name="is_active" value="1" class="rounded">
                        <label for="e-active" class="text-sm text-gray-700">Supplier Aktif</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    function openEdit(id, name, company, phone, email, address, npwp, bankName, bankAccount, bankHolder, isActive) {
        document.getElementById('form-edit').action = '/suppliers/' + id;
        document.getElementById('e-name').value         = name;
        document.getElementById('e-company').value      = company;
        document.getElementById('e-phone').value        = phone;
        document.getElementById('e-email').value        = email;
        document.getElementById('e-address').value      = address;
        document.getElementById('e-npwp').value         = npwp;
        document.getElementById('e-bank-name').value    = bankName;
        document.getElementById('e-bank-account').value = bankAccount;
        document.getElementById('e-bank-holder').value  = bankHolder;
        document.getElementById('e-active').checked     = isActive;
        document.getElementById('modal-edit').classList.remove('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\suppliers\index.blade.php ENDPATH**/ ?>