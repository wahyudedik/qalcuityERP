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
     <?php $__env->slot('header', null, []); ?> Reimbursement Saya <?php $__env->endSlot(); ?>

    <?php if(!$employee): ?>
    <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 rounded-2xl p-4 mb-4">
        <p class="text-sm text-amber-700 dark:text-amber-400">Akun Anda belum terhubung ke data karyawan. Hubungi admin.</p>
    </div>
    <?php else: ?>
    <div class="flex justify-end mb-4">
        <button onclick="document.getElementById('modal-submit').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Ajukan Reimbursement</button>
    </div>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No.</th>
                        <th class="px-4 py-3 text-left">Kategori</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-center">Tanggal</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $reimbursements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $sc = ['draft'=>'gray','submitted'=>'amber','approved'=>'blue','rejected'=>'red','paid'=>'green'][$r->status] ?? 'gray'; ?>
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs text-gray-900 dark:text-white"><?php echo e($r->number); ?></td>
                        <td class="px-4 py-3 text-gray-500 dark:text-slate-400 text-xs"><?php echo e($r->categoryLabel()); ?></td>
                        <td class="px-4 py-3 text-gray-900 dark:text-white"><?php echo e($r->description); ?></td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500"><?php echo e($r->expense_date->format('d/m/Y')); ?></td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp <?php echo e(number_format($r->amount, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 dark:bg-<?php echo e($sc); ?>-500/20 dark:text-<?php echo e($sc); ?>-400"><?php echo e(ucfirst($r->status)); ?></span>
                            <?php if($r->reject_reason): ?><p class="text-xs text-red-400 mt-0.5"><?php echo e($r->reject_reason); ?></p><?php endif; ?>
                            <?php if($r->status === 'paid'): ?><p class="text-xs text-green-500 mt-0.5">Dibayar <?php echo e($r->paid_at?->format('d/m')); ?></p><?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada pengajuan reimbursement.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($reimbursements->hasPages()): ?><div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($reimbursements->links()); ?></div><?php endif; ?>
    </div>

    
    <div id="modal-submit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Ajukan Reimbursement</h3>
                <button onclick="document.getElementById('modal-submit').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('reimbursement.my.store')); ?>" enctype="multipart/form-data" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
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
                    <button type="button" onclick="document.getElementById('modal-submit').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Ajukan</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/reimbursement/my.blade.php ENDPATH**/ ?>