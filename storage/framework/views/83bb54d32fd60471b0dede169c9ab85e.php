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
     <?php $__env->slot('header', null, []); ?> Persetujuan <?php $__env->endSlot(); ?>

    <div class="space-y-6">

        
        <div class="flex items-center justify-between">
            <div></div>
            <a href="<?php echo e(route('approvals.workflows')); ?>"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Kelola Workflow
            </a>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Menunggu Persetujuan</h2>
                <span class="text-xs bg-amber-500/20 text-amber-300 px-2 py-1 rounded-full font-medium"><?php echo e($pending->count()); ?> pending</span>
            </div>

            <?php if($pending->isEmpty()): ?>
                <div class="px-6 py-10 text-center text-gray-400 dark:text-slate-500 text-sm">Tidak ada permintaan yang menunggu persetujuan.</div>
            <?php else: ?>
            <div class="divide-y divide-gray-100 dark:divide-white/5">
                <?php $__currentLoopData = $pending; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white text-sm"><?php echo e($req->workflow?->name ?? 'Permintaan Persetujuan'); ?></p>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                            Diminta oleh <span class="font-medium text-gray-700 dark:text-slate-300"><?php echo e($req->requester?->name); ?></span>
                            · <?php echo e($req->created_at->diffForHumans()); ?>

                        </p>
                        <?php if($req->amount): ?>
                        <p class="text-xs text-blue-400 font-medium mt-1">Rp <?php echo e(number_format($req->amount, 0, ',', '.')); ?></p>
                        <?php endif; ?>
                        <?php if($req->notes): ?>
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1 italic"><?php echo e($req->notes); ?></p>
                        <?php endif; ?>
                        
                        <?php if($req->workflow?->approver_roles): ?>
                        <div class="flex items-center gap-1 mt-2 flex-wrap">
                            <?php $__currentLoopData = $req->workflow->approver_roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($i > 0): ?>
                            <svg class="w-3 h-3 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <?php endif; ?>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                <?php echo e(in_array($role, ['admin','manager']) ? 'bg-blue-500/20 text-blue-400' : 'bg-gray-500/20 text-gray-400'); ?>">
                                <?php echo e(ucfirst($role)); ?>

                            </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex gap-2 sm:shrink-0">
                        <form method="POST" action="<?php echo e(route('approvals.approve', $req)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="px-3 py-1.5 bg-green-600 hover:bg-green-500 text-gray-900 dark:text-white text-xs font-medium rounded-lg transition">
                                Setujui
                            </button>
                        </form>
                        <button onclick="showRejectModal(<?php echo e($req->id); ?>)"
                            class="px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 text-xs font-medium rounded-lg transition">
                            Tolak
                        </button>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <h2 class="font-semibold text-gray-900 dark:text-white">Riwayat Persetujuan</h2>
            </div>
            <?php if($history->isEmpty()): ?>
                <div class="px-6 py-8 text-center text-gray-400 dark:text-slate-500 text-sm">Belum ada riwayat.</div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Permintaan</th>
                            <th class="px-6 py-3 text-left hidden sm:table-cell">Pemohon</th>
                            <th class="px-6 py-3 text-left hidden md:table-cell">Diproses oleh</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-6 py-3 font-medium text-gray-900 dark:text-white"><?php echo e($req->workflow?->name ?? '-'); ?></td>
                            <td class="px-6 py-3 text-gray-500 dark:text-slate-400 hidden sm:table-cell"><?php echo e($req->requester?->name); ?></td>
                            <td class="px-6 py-3 text-gray-500 dark:text-slate-400 hidden md:table-cell"><?php echo e($req->approver?->name ?? '-'); ?></td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    <?php echo e($req->status === 'approved' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'); ?>">
                                    <?php echo e($req->status === 'approved' ? 'Disetujui' : 'Ditolak'); ?>

                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-400 dark:text-slate-500 hidden sm:table-cell"><?php echo e($req->responded_at?->format('d M Y H:i')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div id="reject-modal" class="fixed inset-0 bg-black/70 z-50 hidden items-center justify-center">
        <div class="bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-white/10 rounded-2xl w-full max-w-sm mx-4 p-6 shadow-2xl">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Alasan Penolakan</h3>
            <form id="reject-form" method="POST">
                <?php echo csrf_field(); ?>
                <textarea name="reason" rows="3" required placeholder="Masukkan alasan penolakan..."
                    class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-slate-600 focus:outline-none focus:border-red-500 resize-none"></textarea>
                <div class="flex gap-2 mt-4">
                    <button type="button" onclick="closeRejectModal()"
                        class="flex-1 py-2 border border-gray-200 dark:border-white/10 rounded-xl text-sm font-medium text-gray-600 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-white/5 transition">Batal</button>
                    <button type="submit"
                        class="flex-1 py-2 bg-red-600 text-gray-900 dark:text-white rounded-xl text-sm font-medium hover:bg-red-500 transition">Tolak</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    function showRejectModal(id) {
        document.getElementById('reject-form').action = '<?php echo e(url("approvals")); ?>/' + id + '/reject';
        document.getElementById('reject-modal').classList.remove('hidden');
        document.getElementById('reject-modal').classList.add('flex');
    }
    function closeRejectModal() {
        document.getElementById('reject-modal').classList.add('hidden');
        document.getElementById('reject-modal').classList.remove('flex');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\approvals\index.blade.php ENDPATH**/ ?>