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
     <?php $__env->slot('header', null, []); ?> Data Customer <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($stats['total']); ?></p>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Total Customer</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 text-center">
            <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo e($stats['active']); ?></p>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Aktif</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 text-center">
            <p class="text-2xl font-bold text-gray-400"><?php echo e($stats['inactive']); ?></p>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Nonaktif</p>
        </div>
    </div>

    
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
        <form method="GET" class="flex gap-2 flex-1">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                placeholder="Cari nama, perusahaan, email..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Aktif</option>
                <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Nonaktif</option>
            </select>
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'customers', 'create')): ?>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 whitespace-nowrap">+
            Customer</button>
        <?php endif; ?>
    </div>

    
    <div
        class="hidden md:block bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Perusahaan</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Telepon</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Email</th>
                        <th class="px-4 py-3 text-right hidden lg:table-cell">Credit Limit</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 <?php echo e(!$c->is_active ? 'opacity-60' : ''); ?>">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-white"><?php echo e($c->name); ?></p>
                                <?php if($c->npwp): ?>
                                    <p class="text-xs text-gray-400 dark:text-slate-500">NPWP: <?php echo e($c->npwp); ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-500 dark:text-slate-400">
                                <?php echo e($c->company ?? '-'); ?></td>
                            <td class="px-4 py-3 hidden md:table-cell text-gray-500 dark:text-slate-400">
                                <?php echo e($c->phone ?? '-'); ?></td>
                            <td class="px-4 py-3 hidden lg:table-cell text-gray-500 dark:text-slate-400">
                                <?php echo e($c->email ?? '-'); ?></td>
                            <td class="px-4 py-3 hidden lg:table-cell text-right text-gray-500 dark:text-slate-400">
                                <?php echo e($c->credit_limit ? 'Rp ' . number_format($c->credit_limit, 0, ',', '.') : '-'); ?>

                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs <?php echo e($c->is_active ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400'); ?>">
                                    <?php echo e($c->is_active ? 'Aktif' : 'Nonaktif'); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'customers', 'edit')): ?>
                                    <button
                                        onclick="openEdit(<?php echo e($c->id); ?>, <?php echo e(json_encode($c->name)); ?>, <?php echo e(json_encode($c->company ?? '')); ?>, <?php echo e(json_encode($c->phone ?? '')); ?>, <?php echo e(json_encode($c->email ?? '')); ?>, <?php echo e(json_encode($c->address ?? '')); ?>, <?php echo e(json_encode($c->npwp ?? '')); ?>, <?php echo e($c->credit_limit ?? 0); ?>, <?php echo e($c->is_active ? 'true' : 'false'); ?>)"
                                        class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <form method="POST" action="<?php echo e(route('customers.toggle', $c)); ?>">
                                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                        <button type="submit"
                                            class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10"
                                            title="<?php echo e($c->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>">
                                            <?php if($c->is_active): ?>
                                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            <?php else: ?>
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'customers', 'delete')): ?>
                                    <form method="POST" action="<?php echo e(route('customers.destroy', $c)); ?>"
                                        onsubmit="return confirm('Hapus customer <?php echo e(addslashes($c->name)); ?>?')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit"
                                            class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10"
                                            title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">
                                Belum ada customer.
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'customers', 'create')): ?>
                                <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
                                    class="text-blue-500 hover:underline ml-1">Tambah sekarang</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($customers->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($customers->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div class="md:hidden space-y-3">
        <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-sm">
                
                <div class="px-4 py-3 border-b border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white truncate"><?php echo e($c->name); ?></h3>
                            <?php if($c->company): ?>
                                <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5 truncate"><?php echo e($c->company); ?></p>
                            <?php endif; ?>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap <?php echo e($c->is_active ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400'); ?>">
                            <?php echo e($c->is_active ? 'Aktif' : 'Nonaktif'); ?>

                        </span>
                    </div>
                </div>
                
                <div class="px-4 py-3 space-y-2.5">
                    <?php if($c->npwp): ?>
                    <div class="flex justify-between gap-3">
                        <span class="text-sm text-gray-500 dark:text-slate-400 shrink-0">NPWP</span>
                        <span class="text-sm text-gray-900 dark:text-white text-right"><?php echo e($c->npwp); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between gap-3">
                        <span class="text-sm text-gray-500 dark:text-slate-400 shrink-0">Telepon</span>
                        <?php if($c->phone): ?>
                            <a href="tel:<?php echo e($c->phone); ?>" class="text-sm text-gray-900 dark:text-white text-right hover:text-blue-600"><?php echo e($c->phone); ?></a>
                        <?php else: ?>
                            <span class="text-sm text-gray-400">-</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-sm text-gray-500 dark:text-slate-400 shrink-0">Email</span>
                        <?php if($c->email): ?>
                            <a href="mailto:<?php echo e($c->email); ?>" class="text-sm text-gray-900 dark:text-white text-right break-all hover:text-blue-600"><?php echo e($c->email); ?></a>
                        <?php else: ?>
                            <span class="text-sm text-gray-400">-</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex justify-between gap-3">
                        <span class="text-sm text-gray-500 dark:text-slate-400 shrink-0">Credit Limit</span>
                        <span class="text-sm text-gray-900 dark:text-white text-right">
                            <?php echo e($c->credit_limit ? 'Rp ' . number_format($c->credit_limit, 0, ',', '.') : '-'); ?>

                        </span>
                    </div>
                    <?php if($c->address): ?>
                    <div class="flex justify-between gap-3">
                        <span class="text-sm  shrink-0">Alamat</span>
                        <span class="text-sm text-gray-900 dark:text-white text-right"><?php echo e($c->address); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
                    <div class="flex items-center justify-end gap-2">
                        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'customers', 'edit')): ?>
                        <button
                            onclick="openEdit(<?php echo e($c->id); ?>, <?php echo e(json_encode($c->name)); ?>, <?php echo e(json_encode($c->company ?? '')); ?>, <?php echo e(json_encode($c->phone ?? '')); ?>, <?php echo e(json_encode($c->email ?? '')); ?>, <?php echo e(json_encode($c->address ?? '')); ?>, <?php echo e(json_encode($c->npwp ?? '')); ?>, <?php echo e($c->credit_limit ?? 0); ?>, <?php echo e($c->is_active ? 'true' : 'false'); ?>)"
                            class="min-h-[44px] px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Edit
                        </button>
                        <form method="POST" action="<?php echo e(route('customers.toggle', $c)); ?>" class="inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="min-h-[44px] px-3 py-2 text-sm <?php echo e($c->is_active ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-600 hover:bg-gray-700'); ?> text-white rounded-lg">
                                <?php echo e($c->is_active ? 'Aktif' : 'Nonaktif'); ?>

                            </button>
                        </form>
                        <?php endif; ?>
                        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'customers', 'delete')): ?>
                        <form method="POST" action="<?php echo e(route('customers.destroy', $c)); ?>" class="inline"
                            onsubmit="return confirm('Hapus customer <?php echo e(addslashes($c->name)); ?>?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="min-h-[44px] px-3 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">
                                Hapus
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-12">
                <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada customer.</p>
                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'customers', 'create')): ?>
                <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
                    class="text-blue-500 hover:underline ml-1 text-sm">Tambah sekarang</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if($customers->hasPages()): ?>
            <div class="py-3"><?php echo e($customers->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Customer</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('customers.store')); ?>" class="p-6 space-y-3">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama <span
                                class="text-red-400">*</span></label>
                        <input type="text" name="name" required value="<?php echo e(old('name')); ?>"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Perusahaan</label>
                        <input type="text" name="company" value="<?php echo e(old('company')); ?>"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">NPWP</label>
                        <input type="text" name="npwp" value="<?php echo e(old('npwp')); ?>"
                            placeholder="00.000.000.0-000.000"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Telepon</label>
                        <input type="text" name="phone" value="<?php echo e(old('phone')); ?>"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Email</label>
                        <input type="email" name="email" value="<?php echo e(old('email')); ?>"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Alamat</label>
                        <textarea name="address" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e(old('address')); ?></textarea>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Credit Limit
                            (Rp)</label>
                        <input type="number" name="credit_limit" value="<?php echo e(old('credit_limit', 0)); ?>"
                            min="0" step="1000000"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Isi 0 untuk tanpa batas kredit</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Edit Customer</h3>
                <button onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-edit" method="POST" class="p-6 space-y-3">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama <span
                                class="text-red-400">*</span></label>
                        <input type="text" id="e-name" name="name" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Perusahaan</label>
                        <input type="text" id="e-company" name="company"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">NPWP</label>
                        <input type="text" id="e-npwp" name="npwp"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Telepon</label>
                        <input type="text" id="e-phone" name="phone"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Email</label>
                        <input type="email" id="e-email" name="email"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Alamat</label>
                        <textarea id="e-address" name="address" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Credit Limit
                            (Rp)</label>
                        <input type="number" id="e-credit" name="credit_limit" min="0" step="1000000"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center gap-2 pt-4">
                        <input type="checkbox" id="e-active" name="is_active" value="1" class="rounded">
                        <label for="e-active" class="text-sm text-gray-700 dark:text-slate-300">Customer Aktif</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function openEdit(id, name, company, phone, email, address, npwp, creditLimit, isActive) {
                document.getElementById('form-edit').action = '/customers/' + id;
                document.getElementById('e-name').value = name;
                document.getElementById('e-company').value = company;
                document.getElementById('e-phone').value = phone;
                document.getElementById('e-email').value = email;
                document.getElementById('e-address').value = address;
                document.getElementById('e-npwp').value = npwp;
                document.getElementById('e-credit').value = creditLimit;
                document.getElementById('e-active').checked = isActive;
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/customers/index.blade.php ENDPATH**/ ?>