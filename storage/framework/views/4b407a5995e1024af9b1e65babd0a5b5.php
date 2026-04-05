

<?php $__env->startSection('content'); ?>
<?php $title = 'Cost Center / Divisi'; ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Cost Center / Divisi</h2>
            <p class="text-sm text-slate-500 mt-0.5">Kelola divisi, cabang, dan proyek untuk segment reporting</p>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(route('cost-centers.report')); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/10 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Laporan Segment
            </a>
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'cost_centers', 'create')): ?>
            <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Cost Center
            </button>
            <?php endif; ?>
        </div>
    </div>

    
    <form method="GET" class="flex gap-3 flex-wrap">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari kode / nama..."
            class="px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 w-56">
        <select name="type" class="px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Tipe</option>
            <option value="department" <?php if(request('type')=='department'): echo 'selected'; endif; ?>>Departemen</option>
            <option value="branch" <?php if(request('type')=='branch'): echo 'selected'; endif; ?>>Cabang</option>
            <option value="project" <?php if(request('type')=='project'): echo 'selected'; endif; ?>>Proyek</option>
            <option value="product_line" <?php if(request('type')=='product_line'): echo 'selected'; endif; ?>>Lini Produk</option>
        </select>
        <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-blue-600 text-white hover:bg-blue-700 transition">Filter</button>
        <?php if(request()->hasAny(['search','type'])): ?>
        <a href="<?php echo e(route('cost-centers.index')); ?>" class="px-4 py-2 rounded-xl text-sm bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-white/10 transition">Reset</a>
        <?php endif; ?>
    </form>

    
    <div class="bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500 dark:text-slate-400">Kode</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500 dark:text-slate-400">Nama</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500 dark:text-slate-400">Tipe</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500 dark:text-slate-400">Induk</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500 dark:text-slate-400">Status</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500 dark:text-slate-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                <?php $__empty_1 = true; $__currentLoopData = $centers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                    <td class="px-4 py-3 font-mono text-blue-600 dark:text-blue-400 font-medium"><?php echo e($cc->code); ?></td>
                    <td class="px-4 py-3 text-gray-900 dark:text-white">
                        <?php echo e($cc->name); ?>

                        <?php if($cc->description): ?>
                        <p class="text-xs text-slate-400 mt-0.5"><?php echo e($cc->description); ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php $typeColors = ['department'=>'blue','branch'=>'purple','project'=>'amber','product_line'=>'green']; $c = $typeColors[$cc->type] ?? 'gray'; ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-<?php echo e($c); ?>-100 text-<?php echo e($c); ?>-700 dark:bg-<?php echo e($c); ?>-500/20 dark:text-<?php echo e($c); ?>-300">
                            <?php echo e($cc->typeLabel()); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400"><?php echo e($cc->parent?->name ?? '—'); ?></td>
                    <td class="px-4 py-3">
                        <?php if($cc->is_active): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300">Aktif</span>
                        <?php else: ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'cost_centers', 'edit')): ?>
                        <button onclick='openEdit(<?php echo e(json_encode(["id"=>$cc->id,"name"=>$cc->name,"type"=>$cc->type,"parent_id"=>$cc->parent_id,"is_active"=>$cc->is_active,"description"=>$cc->description])); ?>)'
                            class="text-xs text-blue-600 dark:text-blue-400 hover:underline mr-3">Edit</button>
                        <?php endif; ?>
                        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'cost_centers', 'delete')): ?>
                        <form method="POST" action="<?php echo e(route('cost-centers.destroy', $cc)); ?>" class="inline"
                            onsubmit="return confirm('Hapus cost center <?php echo e($cc->name); ?>?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-xs text-red-500 hover:underline">Hapus</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada cost center. Tambahkan yang pertama.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl shadow-xl w-full max-w-md border border-gray-200 dark:border-white/10">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
            <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Cost Center</h3>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-slate-400 hover:text-gray-600 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="<?php echo e(route('cost-centers.store')); ?>" class="px-6 py-4 space-y-4">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Kode *</label>
                    <input type="text" name="code" required maxlength="20" placeholder="mis. DIV-01"
                        class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Tipe *</label>
                    <select name="type" required class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="department">Departemen</option>
                        <option value="branch">Cabang</option>
                        <option value="project">Proyek</option>
                        <option value="product_line">Lini Produk</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Nama *</label>
                <input type="text" name="name" required maxlength="100"
                    class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Induk (opsional)</label>
                <select name="parent_id" class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Tidak ada —</option>
                    <?php $__currentLoopData = $parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($p->id); ?>"><?php echo e($p->code); ?> — <?php echo e($p->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Deskripsi</label>
                <input type="text" name="description" maxlength="255"
                    class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <?php if($errors->any()): ?>
            <div class="text-xs text-red-500"><?php echo e($errors->first()); ?></div>
            <?php endif; ?>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="px-4 py-2 rounded-xl text-sm border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-white/10 transition">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-blue-600 hover:bg-blue-700 text-white transition">Simpan</button>
            </div>
        </form>
    </div>
</div>


<div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl shadow-xl w-full max-w-md border border-gray-200 dark:border-white/10">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
            <h3 class="font-semibold text-gray-900 dark:text-white">Edit Cost Center</h3>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-slate-400 hover:text-gray-600 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="form-edit" method="POST" class="px-6 py-4 space-y-4">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Nama *</label>
                <input type="text" id="edit-name" name="name" required maxlength="100"
                    class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Tipe *</label>
                <select id="edit-type" name="type" required class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="department">Departemen</option>
                    <option value="branch">Cabang</option>
                    <option value="project">Proyek</option>
                    <option value="product_line">Lini Produk</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Induk</label>
                <select id="edit-parent" name="parent_id" class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Tidak ada —</option>
                    <?php $__currentLoopData = $parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($p->id); ?>"><?php echo e($p->code); ?> — <?php echo e($p->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Deskripsi</label>
                <input type="text" id="edit-description" name="description" maxlength="255"
                    class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="edit-active" name="is_active" value="1" class="rounded">
                <label for="edit-active" class="text-sm text-gray-700 dark:text-slate-300">Aktif</label>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="px-4 py-2 rounded-xl text-sm border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-white/10 transition">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-blue-600 hover:bg-blue-700 text-white transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php if($errors->any()): ?>
<script>document.getElementById('modal-add').classList.remove('hidden');</script>
<?php endif; ?>

<script>
function openEdit(data) {
    document.getElementById('form-edit').action = '<?php echo e(route("cost-centers.index")); ?>/' + data.id;
    document.getElementById('edit-name').value = data.name;
    document.getElementById('edit-type').value = data.type;
    document.getElementById('edit-parent').value = data.parent_id || '';
    document.getElementById('edit-description').value = data.description || '';
    document.getElementById('edit-active').checked = !!data.is_active;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/cost-centers/index.blade.php ENDPATH**/ ?>