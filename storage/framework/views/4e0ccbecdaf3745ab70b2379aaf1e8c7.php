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
     <?php $__env->slot('header', null, []); ?> Cuti Saya <?php $__env->endSlot(); ?>

    <?php if(!$employee): ?>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <p class="text-gray-500 dark:text-slate-400 text-sm">Akun Anda belum terhubung ke data karyawan.</p>
        <p class="text-gray-400 dark:text-slate-500 text-xs mt-1">Hubungi admin untuk menghubungkan akun ke profil karyawan.</p>
    </div>
    <?php else: ?>

    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Kuota Cuti Tahunan</p>
            <div class="flex items-end gap-2">
                <p class="text-3xl font-black text-gray-900 dark:text-white"><?php echo e($quota - $usedDays); ?></p>
                <p class="text-sm text-gray-400 dark:text-slate-500 mb-1">/ <?php echo e($quota); ?> hari tersisa</p>
            </div>
            <div class="mt-3 h-2 bg-gray-100 dark:bg-white/10 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 rounded-full transition-all"
                    style="width: <?php echo e($quota > 0 ? min(100, ($usedDays / $quota) * 100) : 0); ?>%"></div>
            </div>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1"><?php echo e($usedDays); ?> hari terpakai tahun <?php echo e(now()->year); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Menunggu Persetujuan</p>
            <p class="text-3xl font-black text-amber-500"><?php echo e($leaves->where('status','pending')->count()); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Disetujui Tahun Ini</p>
            <p class="text-3xl font-black text-green-500"><?php echo e($leaves->where('status','approved')->count()); ?></p>
        </div>
    </div>

    <?php if($errors->any()): ?>
    <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl text-sm mb-4">
        <?php echo e($errors->first()); ?>

    </div>
    <?php endif; ?>
    <?php if(session('success')): ?>
    <div class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 text-green-700 dark:text-green-400 px-4 py-3 rounded-xl text-sm mb-4">
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>

    <div class="flex flex-col lg:flex-row gap-6">

        
        <div class="w-full lg:w-72 shrink-0">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Ajukan Cuti</h3>
                <form method="POST" action="<?php echo e(route('self-service.leave.store')); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jenis Cuti *</label>
                        <select name="type" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="annual">Cuti Tahunan</option>
                            <option value="sick">Sakit</option>
                            <option value="maternity">Cuti Melahirkan</option>
                            <option value="paternity">Cuti Ayah</option>
                            <option value="unpaid">Cuti Tanpa Gaji</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Mulai *</label>
                        <input type="date" name="start_date" required min="<?php echo e(today()->format('Y-m-d')); ?>"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Selesai *</label>
                        <input type="date" name="end_date" required min="<?php echo e(today()->format('Y-m-d')); ?>"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Alasan</label>
                        <textarea name="reason" rows="3" placeholder="Opsional..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                        Kirim Pengajuan
                    </button>
                </form>
            </div>
        </div>

        
        <div class="flex-1">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 dark:border-white/10">
                    <p class="font-semibold text-gray-900 dark:text-white">Riwayat Pengajuan</p>
                </div>
                <?php if($leaves->isEmpty()): ?>
                <div class="px-5 py-12 text-center text-sm text-gray-400 dark:text-slate-500">Belum ada pengajuan cuti.</div>
                <?php else: ?>
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $badge = match($leave->status) {
                            'approved' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                            'rejected' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                            default    => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                        };
                        $statusLabel = match($leave->status) {
                            'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => 'Menunggu',
                        };
                    ?>
                    <div class="px-5 py-4 flex items-start gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($leave->typeLabel()); ?></p>
                                <span class="text-xs px-2 py-0.5 rounded-full <?php echo e($badge); ?>"><?php echo e($statusLabel); ?></span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                <?php echo e($leave->start_date->format('d M Y')); ?> — <?php echo e($leave->end_date->format('d M Y')); ?>

                                · <span class="font-medium"><?php echo e($leave->days); ?> hari</span>
                            </p>
                            <?php if($leave->reason): ?>
                            <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 italic"><?php echo e($leave->reason); ?></p>
                            <?php endif; ?>
                            <?php if($leave->status === 'rejected' && $leave->rejection_reason): ?>
                            <p class="text-xs text-red-500 dark:text-red-400 mt-1">Alasan penolakan: <?php echo e($leave->rejection_reason); ?></p>
                            <?php endif; ?>
                            <?php if($leave->status === 'approved' && $leave->approver): ?>
                            <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Disetujui oleh: <?php echo e($leave->approver->name); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-xs text-gray-400 dark:text-slate-500"><?php echo e($leave->created_at->format('d M Y')); ?></p>
                            <?php if($leave->status === 'pending'): ?>
                            <form method="POST" action="<?php echo e(route('self-service.leave.cancel', $leave)); ?>"
                                onsubmit="return confirm('Batalkan pengajuan ini?')" class="mt-1">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 dark:hover:text-red-400">Batalkan</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php if($leaves->hasPages()): ?>
                <div class="px-5 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($leaves->links()); ?></div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/self-service/leave.blade.php ENDPATH**/ ?>