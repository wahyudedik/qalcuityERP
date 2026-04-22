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
     <?php $__env->slot('header', null, []); ?> Reimbursement <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Menunggu Approval</p>
            <p class="text-2xl font-bold text-amber-500"><?php echo e($stats['submitted']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Approved (Belum Bayar)</p>
            <p class="text-2xl font-bold text-blue-500"><?php echo e($stats['approved']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Pending</p>
            <p class="text-lg font-bold text-red-500">Rp <?php echo e(number_format($stats['total_pending'], 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Dibayar Bulan Ini</p>
            <p class="text-lg font-bold text-green-500">Rp <?php echo e(number_format($stats['paid_month'], 0, ',', '.')); ?></p>
        </div>
    </div>

    
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari..."
                class="flex-1 min-w-[120px] px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = ['submitted'=>'Submitted','approved'=>'Approved','rejected'=>'Rejected','paid'=>'Paid']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($v); ?>" <?php if(request('status')===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="category" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Kategori</option>
                <?php $__currentLoopData = ['transport'=>'Transportasi','meal'=>'Makan','medical'=>'Kesehatan','office'=>'Kantor','travel'=>'Perjalanan','training'=>'Pelatihan','other'=>'Lainnya']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($v); ?>" <?php if(request('category')===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'reimbursement', 'create')): ?>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Reimbursement</button>
        <?php endif; ?>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No.</th>
                        <th class="px-4 py-3 text-left">Karyawan</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Kategori</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $reimbursements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $sc = ['draft'=>'gray','submitted'=>'amber','approved'=>'blue','rejected'=>'red','paid'=>'green'][$r->status] ?? 'gray'; ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs text-gray-900 dark:text-white"><?php echo e($r->number); ?></td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300"><?php echo e($r->employee->name ?? '-'); ?></td>
                        <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500 dark:text-slate-400"><?php echo e($r->categoryLabel()); ?></td>
                        <td class="px-4 py-3 text-gray-900 dark:text-white">
                            <?php echo e(Str::limit($r->description, 30)); ?>

                            <?php if($r->receipt_image): ?> <span class="text-xs text-blue-400">📎</span> <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp <?php echo e(number_format($r->amount, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 dark:bg-<?php echo e($sc); ?>-500/20 dark:text-<?php echo e($sc); ?>-400"><?php echo e(ucfirst($r->status)); ?></span>
                            <?php if($r->reject_reason): ?><p class="text-xs text-red-400 mt-0.5"><?php echo e(Str::limit($r->reject_reason, 20)); ?></p><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'reimbursement', 'edit')): ?>
                                <?php if($r->status === 'submitted'): ?>
                                <form method="POST" action="<?php echo e(route('reimbursement.approve', $r)); ?>" class="inline"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="text-xs px-2 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Approve</button>
                                </form>
                                <form method="POST" action="<?php echo e(route('reimbursement.reject', $r)); ?>" class="inline" onsubmit="const r=prompt('Alasan reject:'); if(!r) return false; this.querySelector('[name=reason]').value=r;">
                                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?> <input type="hidden" name="reason" value="">
                                    <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Reject</button>
                                </form>
                                <?php elseif($r->status === 'approved'): ?>
                                <form method="POST" action="<?php echo e(route('reimbursement.pay', $r)); ?>" class="inline" onsubmit="return confirm('Bayar reimbursement ini?')">
                                    <?php echo csrf_field(); ?>
                                    <select name="payment_method" class="text-xs px-1 py-0.5 rounded border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white">
                                        <option value="transfer">Transfer</option><option value="cash">Cash</option>
                                    </select>
                                    <button type="submit" class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Bayar</button>
                                </form>
                                <?php endif; ?>
                                <?php endif; ?>
                                <?php if(in_array($r->status, ['draft', 'submitted'])): ?>
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'reimbursement', 'delete')): ?>
                                <form method="POST" action="<?php echo e(route('reimbursement.destroy', $r)); ?>" class="inline" onsubmit="return confirm('Hapus?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-xs px-2 py-1 text-red-400 hover:text-red-300">✕</button>
                                </form>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada reimbursement.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($reimbursements->hasPages()): ?><div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($reimbursements->links()); ?></div><?php endif; ?>
    </div>

    
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Ajukan Reimbursement</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('reimbursement.store')); ?>" enctype="multipart/form-data" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Karyawan *</label>
                    <select name="employee_id" required class="<?php echo e($cls); ?>"><option value="">-- Pilih --</option>
                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($e->id); ?>"><?php echo e($e->name); ?> (<?php echo e($e->employee_id); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kategori *</label>
                        <select name="category" required class="<?php echo e($cls); ?>">
                            <option value="transport">Transportasi</option><option value="meal">Makan & Minum</option><option value="medical">Kesehatan</option>
                            <option value="office">Perlengkapan Kantor</option><option value="travel">Perjalanan Dinas</option><option value="training">Pelatihan</option><option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal *</label><input type="date" name="expense_date" required value="<?php echo e(date('Y-m-d')); ?>" class="<?php echo e($cls); ?>"></div>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi *</label><input type="text" name="description" required placeholder="Ongkos taksi meeting client" class="<?php echo e($cls); ?>"></div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah (Rp) *</label><input type="number" name="amount" required min="1000" step="500" class="<?php echo e($cls); ?>"></div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Foto Struk/Bukti</label><input type="file" name="receipt_image" accept="image/*" class="<?php echo e($cls); ?>"></div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label><input type="text" name="notes" class="<?php echo e($cls); ?>"></div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Ajukan</button>
                </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\reimbursement\index.blade.php ENDPATH**/ ?>