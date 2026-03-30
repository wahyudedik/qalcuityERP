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
     <?php $__env->slot('header', null, []); ?> Bagan Akun (Chart of Accounts) <?php $__env->endSlot(); ?>

    <?php $tid = auth()->user()->tenant_id; ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <?php
            $allAccounts = \App\Models\ChartOfAccount::where('tenant_id', $tid);
            $statTotal   = (clone $allAccounts)->count();
            $statActive  = (clone $allAccounts)->where('is_active', true)->count();
            $statHeader  = (clone $allAccounts)->where('is_header', true)->count();
            $statDetail  = (clone $allAccounts)->where('is_header', false)->count();
            $statTypes   = (clone $allAccounts)->where('is_header', false)->distinct()->count('type');
        ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Akun</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e($statTotal); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Aktif</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1"><?php echo e($statActive); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Header</p>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1"><?php echo e($statHeader); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Detail</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1"><?php echo e($statDetail); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10 col-span-2 sm:col-span-1">
            <p class="text-xs text-gray-500 dark:text-slate-400">Nonaktif</p>
            <p class="text-2xl font-bold text-gray-500 dark:text-slate-400 mt-1"><?php echo e($statTotal - $statActive); ?></p>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari kode / nama akun..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="type" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Tipe</option>
                    <option value="asset"     <?php if(request('type')==='asset'): echo 'selected'; endif; ?>>Aset</option>
                    <option value="liability" <?php if(request('type')==='liability'): echo 'selected'; endif; ?>>Kewajiban</option>
                    <option value="equity"    <?php if(request('type')==='equity'): echo 'selected'; endif; ?>>Ekuitas</option>
                    <option value="revenue"   <?php if(request('type')==='revenue'): echo 'selected'; endif; ?>>Pendapatan</option>
                    <option value="expense"   <?php if(request('type')==='expense'): echo 'selected'; endif; ?>>Beban</option>
                </select>
                <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="active"   <?php if(request('status')==='active'): echo 'selected'; endif; ?>>Aktif</option>
                    <option value="inactive" <?php if(request('status')==='inactive'): echo 'selected'; endif; ?>>Nonaktif</option>
                </select>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
            </form>
            <div class="flex gap-2 flex-wrap">
                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'accounting', 'create')): ?>
                <form method="POST" action="<?php echo e(route('accounting.coa.seed')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" onclick="return confirm('Muat COA default Indonesia? Akun yang sudah ada tidak akan ditimpa.')"
                        class="px-3 py-2 text-sm border border-yellow-400/40 text-yellow-600 dark:text-yellow-400 rounded-xl hover:bg-yellow-50 dark:hover:bg-yellow-500/10">
                        ⚡ COA Default
                    </button>
                </form>
                <button onclick="document.getElementById('modal-add-coa').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Akun</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Nama Akun</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tipe</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Saldo Normal</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Lv</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Induk</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $typeColor = match($acc->type) {
                            'asset'     => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                            'liability' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                            'equity'    => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                            'revenue'   => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                            'expense'   => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                            default     => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-slate-400',
                        };
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 <?php echo e($acc->is_header ? 'bg-gray-50/50 dark:bg-white/[0.02]' : ''); ?>">
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700 dark:text-slate-300"><?php echo e($acc->code); ?></td>
                        <td class="px-4 py-3" style="padding-left: <?php echo e(($acc->level - 1) * 18 + 16); ?>px">
                            <div class="flex items-center gap-1.5">
                                <?php if($acc->is_header): ?>
                                <svg class="w-3.5 h-3.5 text-purple-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                                <?php endif; ?>
                                <span class="<?php echo e($acc->is_header ? 'font-semibold text-gray-900 dark:text-white' : 'text-gray-700 dark:text-slate-300'); ?>"><?php echo e($acc->name); ?></span>
                            </div>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($typeColor); ?>"><?php echo e($acc->getTypeLabel()); ?></span>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-xs text-gray-500 dark:text-slate-400 capitalize"><?php echo e($acc->normal_balance); ?></td>
                        <td class="px-4 py-3 text-center hidden lg:table-cell text-xs text-gray-500 dark:text-slate-400"><?php echo e($acc->level); ?></td>
                        <td class="px-4 py-3 hidden lg:table-cell font-mono text-xs text-gray-400 dark:text-slate-500"><?php echo e($acc->parent?->code ?? '—'); ?></td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell">
                            <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($acc->is_active ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400'); ?>">
                                <?php echo e($acc->is_active ? 'Aktif' : 'Nonaktif'); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'accounting', 'edit')): ?>
                                <button onclick="openEditCoa(<?php echo e($acc->id); ?>, <?php echo \Illuminate\Support\Js::from($acc->name)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($acc->description ?? '')->toHtml() ?>, <?php echo e($acc->is_active ? 'true' : 'false'); ?>)"
                                    class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <?php endif; ?>
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'accounting', 'delete')): ?>
                                <form method="POST" action="<?php echo e(route('accounting.coa.destroy', $acc)); ?>" onsubmit="return confirm('Hapus akun <?php echo e(addslashes($acc->name)); ?>?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <p class="text-gray-400 dark:text-slate-500 mb-3">Belum ada akun.</p>
                            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'accounting', 'create')): ?>
                            <form method="POST" action="<?php echo e(route('accounting.coa.seed')); ?>" class="inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="px-4 py-2 text-sm bg-yellow-500 text-white rounded-xl hover:bg-yellow-600">
                                    ⚡ Muat COA Default Indonesia
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(method_exists($accounts, 'hasPages') && $accounts->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($accounts->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-add-coa" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b] z-10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Akun Baru</h3>
                <button onclick="document.getElementById('modal-add-coa').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('accounting.coa.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kode Akun *</label>
                        <input type="text" name="code" required placeholder="1101"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Level *</label>
                        <select name="level" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="1">1 — Header Utama</option>
                            <option value="2">2 — Sub Header</option>
                            <option value="3" selected>3 — Detail</option>
                            <option value="4">4 — Sub Detail</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Akun *</label>
                    <input type="text" name="name" required placeholder="Kas dan Setara Kas"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                        <select name="type" id="add-type" required onchange="autoNormalBalance(this.value)"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="asset">Aset</option>
                            <option value="liability">Kewajiban</option>
                            <option value="equity">Ekuitas</option>
                            <option value="revenue">Pendapatan</option>
                            <option value="expense">Beban</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Saldo Normal *</label>
                        <select name="normal_balance" id="add-normal-balance" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="debit">Debit</option>
                            <option value="credit">Kredit</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Akun Induk</label>
                    <select name="parent_id" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Tidak ada (akun root) —</option>
                        <?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($h->id); ?>"><?php echo e($h->code); ?> — <?php echo e($h->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi</label>
                    <input type="text" name="description" placeholder="Opsional"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_header" value="1" id="add-is-header" class="rounded">
                    <label for="add-is-header" class="text-sm text-gray-700 dark:text-slate-300">Akun header (tidak bisa diposting langsung)</label>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-coa').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-edit-coa" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Edit Akun</h3>
                <button onclick="document.getElementById('modal-edit-coa').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-edit-coa" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Akun *</label>
                    <input type="text" id="edit-coa-name" name="name" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi</label>
                    <input type="text" id="edit-coa-desc" name="description"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="edit-coa-active" name="is_active" value="1" class="rounded">
                    <label for="edit-coa-active" class="text-sm text-gray-700 dark:text-slate-300">Akun Aktif</label>
                </div>
                <p class="text-xs text-gray-400 dark:text-slate-500">Kode, tipe, saldo normal, dan level tidak dapat diubah setelah dibuat.</p>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit-coa').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    // Auto-set saldo normal berdasarkan tipe akun
    function autoNormalBalance(type) {
        const nb = document.getElementById('add-normal-balance');
        if (!nb) return;
        nb.value = ['asset', 'expense'].includes(type) ? 'debit' : 'credit';
    }

    function openEditCoa(id, name, description, isActive) {
        const form = document.getElementById('form-edit-coa');
        form.action = '/accounting/coa/' + id;
        document.getElementById('edit-coa-name').value    = name;
        document.getElementById('edit-coa-desc').value    = description;
        document.getElementById('edit-coa-active').checked = isActive;
        document.getElementById('modal-edit-coa').classList.remove('hidden');
    }

    // Auto-set saldo normal saat halaman load
    document.getElementById('add-type')?.addEventListener('change', function() {
        autoNormalBalance(this.value);
    });
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\accounting\coa.blade.php ENDPATH**/ ?>