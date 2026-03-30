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
     <?php $__env->slot('header', null, []); ?> Manajemen Kontrak <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Kontrak Aktif</p>
            <p class="text-2xl font-bold text-green-500"><?php echo e($stats['active']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Segera Expired</p>
            <p class="text-2xl font-bold text-amber-500"><?php echo e($stats['expiring']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Nilai Aktif</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($stats['value'], 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Billing Pending</p>
            <p class="text-2xl font-bold text-blue-500"><?php echo e($stats['pending_billing']); ?></p>
        </div>
    </div>

    
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari kontrak..."
                class="flex-1 min-w-[150px] px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = ['draft'=>'Draft','active'=>'Aktif','expired'=>'Expired','terminated'=>'Terminasi','renewed'=>'Renewed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($v); ?>" <?php if(request('status')===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <div class="flex gap-2">
            <a href="<?php echo e(route('contracts.templates')); ?>" class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Template</a>
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'contracts', 'create')): ?>
            <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Kontrak</button>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Kontrak</th>
                        <th class="px-4 py-3 text-left">Judul</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Pihak</th>
                        <th class="px-4 py-3 text-center hidden md:table-cell">Periode</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Nilai</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $contracts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $sc = ['draft'=>'gray','active'=>'green','expired'=>'red','terminated'=>'red','renewed'=>'purple'][$c->status] ?? 'gray';
                        $sl = ['draft'=>'Draft','active'=>'Aktif','expired'=>'Expired','terminated'=>'Terminasi','renewed'=>'Renewed'][$c->status] ?? $c->status;
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900 dark:text-white">
                            <a href="<?php echo e(route('contracts.show', $c)); ?>" class="hover:text-blue-500"><?php echo e($c->contract_number); ?></a>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300"><?php echo e(Str::limit($c->title, 40)); ?></td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500 dark:text-slate-400 text-xs">
                            <?php echo e($c->party_type === 'customer' ? '👤' : '🏢'); ?> <?php echo e($c->partyName()); ?>

                        </td>
                        <td class="px-4 py-3 text-center hidden md:table-cell text-xs text-gray-500 dark:text-slate-400">
                            <?php echo e($c->start_date->format('d/m/y')); ?> — <?php echo e($c->end_date->format('d/m/y')); ?>

                            <?php if($c->isExpiringSoon()): ?> <span class="text-amber-500 ml-1">⏰ <?php echo e($c->daysRemaining()); ?>d</span> <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900 dark:text-white">Rp <?php echo e(number_format($c->value, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 dark:bg-<?php echo e($sc); ?>-500/20 dark:text-<?php echo e($sc); ?>-400"><?php echo e($sl); ?></span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="<?php echo e(route('contracts.show', $c)); ?>" class="text-xs px-2 py-1 border border-gray-200 dark:border-white/10 rounded-lg text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Detail</a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada kontrak.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($contracts->hasPages()): ?><div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($contracts->links()); ?></div><?php endif; ?>
    </div>

    
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Kontrak Baru</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('contracts.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Judul Kontrak *</label><input type="text" name="title" required placeholder="Kontrak Sewa Gudang 2026" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Pihak *</label>
                        <select name="party_type" id="party-type" required onchange="toggleParty()" class="<?php echo e($cls); ?>">
                            <option value="customer">Customer</option><option value="supplier">Supplier</option>
                        </select>
                    </div>
                    <div id="party-customer"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Customer</label>
                        <select name="customer_id" class="<?php echo e($cls); ?>"><option value="">-- Pilih --</option>
                            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div id="party-supplier" class="hidden"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Supplier</label>
                        <select name="supplier_id" class="<?php echo e($cls); ?>"><option value="">-- Pilih --</option>
                            <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kategori *</label>
                        <select name="category" required class="<?php echo e($cls); ?>">
                            <option value="service">Jasa</option><option value="lease">Sewa</option><option value="supply">Supply</option><option value="maintenance">Maintenance</option><option value="subscription">Langganan</option>
                        </select>
                    </div>
                    <?php if($templates->isNotEmpty()): ?>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Template</label>
                        <select name="template_id" class="<?php echo e($cls); ?>"><option value="">-- Tanpa Template --</option>
                            <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($t->id); ?>"><?php echo e($t->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Mulai *</label><input type="date" name="start_date" required class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Berakhir *</label><input type="date" name="end_date" required class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nilai Kontrak (Rp) *</label><input type="number" name="value" required min="0" step="1000" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Siklus Billing *</label>
                        <select name="billing_cycle" required class="<?php echo e($cls); ?>">
                            <option value="monthly">Bulanan</option><option value="quarterly">Triwulan</option><option value="semi_annual">Semester</option><option value="annual">Tahunan</option><option value="one_time">Sekali</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Billing per Siklus (Rp)</label><input type="number" name="billing_amount" min="0" step="1000" class="<?php echo e($cls); ?>"></div>
                    <div><label class="flex items-center gap-2 cursor-pointer mt-5"><input type="checkbox" name="auto_renew" value="1" class="rounded"><span class="text-sm text-gray-700 dark:text-slate-300">Auto Renew</span></label></div>
                </div>
                <details class="text-sm">
                    <summary class="cursor-pointer text-blue-500 hover:underline">SLA & Ketentuan (opsional)</summary>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-3">
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Response Time (jam)</label><input type="number" name="sla_response_hours" min="1" class="<?php echo e($cls); ?>"></div>
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Resolution Time (jam)</label><input type="number" name="sla_resolution_hours" min="1" class="<?php echo e($cls); ?>"></div>
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Uptime (%)</label><input type="number" name="sla_uptime_pct" min="0" max="100" step="0.01" placeholder="99.90" class="<?php echo e($cls); ?>"></div>
                        <div class="sm:col-span-3"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Ketentuan SLA</label><textarea name="sla_terms" rows="2" class="<?php echo e($cls); ?>"></textarea></div>
                        <div class="sm:col-span-3"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Terms & Conditions</label><textarea name="terms" rows="2" class="<?php echo e($cls); ?>"></textarea></div>
                    </div>
                </details>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    function toggleParty() {
        const t = document.getElementById('party-type').value;
        document.getElementById('party-customer').classList.toggle('hidden', t !== 'customer');
        document.getElementById('party-supplier').classList.toggle('hidden', t !== 'supplier');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\contracts\index.blade.php ENDPATH**/ ?>