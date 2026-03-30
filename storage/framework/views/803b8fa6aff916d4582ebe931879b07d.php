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
     <?php $__env->slot('header', null, []); ?> Write-off Hutang / Piutang <?php $__env->endSlot(); ?>

    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-6">
        <form method="GET" class="flex gap-2 flex-wrap">
            <select name="type" onchange="this.form.submit()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Tipe</option>
                <option value="receivable" <?php echo e(request('type') === 'receivable' ? 'selected' : ''); ?>>Piutang</option>
                <option value="payable" <?php echo e(request('type') === 'payable' ? 'selected' : ''); ?>>Hutang</option>
            </select>
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                <option value="approved" <?php echo e(request('status') === 'approved' ? 'selected' : ''); ?>>Disetujui</option>
                <option value="posted" <?php echo e(request('status') === 'posted' ? 'selected' : ''); ?>>Diposting</option>
                <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Ditolak</option>
            </select>
        </form>
        <div class="ml-auto flex gap-2">
            <a href="<?php echo e(route('writeoffs.create', ['type' => 'receivable'])); ?>" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Write-off Piutang</a>
            <a href="<?php echo e(route('writeoffs.create', ['type' => 'payable'])); ?>" class="px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">+ Write-off Hutang</a>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tipe</th>
                        <th class="px-4 py-3 text-left">Referensi</th>
                        <th class="px-4 py-3 text-right">Jumlah Write-off</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Alasan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $writeoffs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white"><?php echo e($wo->number); ?></p>
                            <p class="text-xs text-gray-400 dark:text-slate-500"><?php echo e($wo->created_at->format('d M Y')); ?></p>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($wo->type === 'receivable' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400' : 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400'); ?>">
                                <?php echo e($wo->typeLabel()); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300"><?php echo e($wo->reference_number); ?></td>
                        <td class="px-4 py-3 text-right font-semibold text-red-600 dark:text-red-400">Rp <?php echo e(number_format($wo->writeoff_amount,0,',','.')); ?></td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500 dark:text-slate-400 max-w-xs truncate"><?php echo e($wo->reason); ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($wo->statusColor()); ?>"><?php echo e(ucfirst($wo->status)); ?></span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <?php if($wo->isPending() && in_array(auth()->user()->role, ['admin', 'manager'])): ?>
                                <form method="POST" action="<?php echo e(route('writeoffs.approve', $wo)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="px-2 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700">Setujui</button>
                                </form>
                                <button onclick="openReject(<?php echo e($wo->id); ?>)" class="px-2 py-1 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700">Tolak</button>
                                <?php endif; ?>
                                <?php if($wo->isApproved() && in_array(auth()->user()->role, ['admin', 'manager'])): ?>
                                <form method="POST" action="<?php echo e(route('writeoffs.post', $wo)); ?>" onsubmit="return confirm('Post jurnal write-off ini?')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="px-2 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">Post Jurnal</button>
                                </form>
                                <?php endif; ?>
                                <?php if($wo->journalEntry): ?>
                                <a href="<?php echo e(route('journals.show', $wo->journalEntry)); ?>" class="px-2 py-1 text-xs border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5">Jurnal</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada data write-off.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($writeoffs->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($writeoffs->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-reject" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tolak Write-off</h3>
                <button onclick="document.getElementById('modal-reject').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-reject" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Alasan Penolakan *</label>
                    <textarea name="reason" required rows="3" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-reject').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">Tolak</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    function openReject(id) {
        document.getElementById('form-reject').action = '<?php echo e(url("writeoffs")); ?>/' + id + '/reject';
        document.getElementById('modal-reject').classList.remove('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\writeoffs\index.blade.php ENDPATH**/ ?>