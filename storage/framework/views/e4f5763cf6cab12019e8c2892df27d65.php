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
     <?php $__env->slot('header', null, []); ?> Manajemen Lembur <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Menunggu Persetujuan</p>
            <p class="text-2xl font-bold text-amber-500 mt-1"><?php echo e($summary['pending']); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Disetujui Bulan Ini</p>
            <p class="text-2xl font-bold text-green-500 mt-1"><?php echo e($summary['approved']); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Upah Lembur Belum Dibayar</p>
            <p class="text-xl font-bold text-blue-500 mt-1">Rp <?php echo e(number_format($summary['total_pay'], 0, ',', '.')); ?>

            </p>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-5">

        
        <div class="lg:w-72 shrink-0 space-y-4">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-4 text-sm">Ajukan Lembur</h3>
                <form method="POST" action="<?php echo e(route('hrm.overtime.store')); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan
                            *</label>
                        <select name="employee_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih karyawan...</option>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($emp->id); ?>"><?php echo e($emp->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal
                            *</label>
                        <input type="date" name="date" required value="<?php echo e(today()->format('Y-m-d')); ?>"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Mulai
                                *</label>
                            <input type="time" name="start_time" required value="17:00"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Selesai
                                *</label>
                            <input type="time" name="end_time" required value="20:00"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Alasan</label>
                        <textarea name="reason" rows="2" placeholder="Keterangan pekerjaan lembur..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Ajukan Lembur
                    </button>
                </form>
            </div>

            
            <div class="bg-blue-500/10 border border-blue-500/20 rounded-2xl p-4 text-xs text-blue-300 space-y-1.5">
                <p class="font-semibold text-blue-200">Perhitungan Upah Lembur</p>
                <p>Berdasarkan Permenaker No.102/2004:</p>
                <p>• Jam ke-1: 1,5× upah/jam</p>
                <p>• Jam ke-2 dst: 2× upah/jam</p>
                <p>• Upah/jam = Gaji Pokok ÷ 173</p>
            </div>
        </div>

        
        <div class="flex-1 min-w-0">

            
            <form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="month" name="month" value="<?php echo e($month); ?>"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all" <?php if($status === 'all'): echo 'selected'; endif; ?>>Semua Status</option>
                    <option value="pending" <?php if($status === 'pending'): echo 'selected'; endif; ?>>Menunggu</option>
                    <option value="approved" <?php if($status === 'approved'): echo 'selected'; endif; ?>>Disetujui</option>
                    <option value="rejected" <?php if($status === 'rejected'): echo 'selected'; endif; ?>>Ditolak</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            </form>

            
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Karyawan</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-center">Waktu</th>
                                <th class="px-4 py-3 text-center">Durasi</th>
                                <th class="px-4 py-3 text-right hidden sm:table-cell">Upah</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900">
                                            <?php echo e($ot->employee->name ?? '-'); ?></p>
                                        <p class="text-xs text-gray-400">
                                            <?php echo e($ot->employee->department ?? ($ot->employee->position ?? '')); ?></p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                                        <?php echo e($ot->date->format('d M Y')); ?>

                                    </td>
                                    <td
                                        class="px-4 py-3 text-center text-gray-600 whitespace-nowrap text-xs">
                                        <?php echo e($ot->start_time); ?> – <?php echo e($ot->end_time); ?>

                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="text-sm font-medium text-gray-900"><?php echo e($ot->durationLabel()); ?></span>
                                    </td>
                                    <td class="px-4 py-3 text-right hidden sm:table-cell">
                                        <?php if($ot->status === 'approved'): ?>
                                            <span class="text-green-600 font-medium">Rp
                                                <?php echo e(number_format($ot->overtime_pay, 0, ',', '.')); ?></span>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if($ot->status === 'pending'): ?>
                                            <span
                                                class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700">Menunggu</span>
                                        <?php elseif($ot->status === 'approved'): ?>
                                            <div>
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Disetujui</span>
                                                <?php if($ot->included_in_payroll): ?>
                                                    <p class="text-xs text-gray-400 mt-0.5">Payroll
                                                        <?php echo e($ot->payroll_period); ?></p>
                                                <?php else: ?>
                                                    <p class="text-xs text-blue-400 mt-0.5">Belum dibayar</p>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div>
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700">Ditolak</span>
                                                <?php if($ot->rejection_reason): ?>
                                                    <p class="text-xs text-gray-400 mt-0.5 max-w-[120px] truncate"
                                                        title="<?php echo e($ot->rejection_reason); ?>">
                                                        <?php echo e($ot->rejection_reason); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if($ot->status === 'pending'): ?>
                                            <div class="flex items-center justify-center gap-1">
                                                <form method="POST" action="<?php echo e(route('hrm.overtime.approve', $ot)); ?>">
                                                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                                    <button type="submit"
                                                        class="px-2.5 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700">Setuju</button>
                                                </form>
                                                <button onclick="openReject(<?php echo e($ot->id); ?>)"
                                                    class="px-2.5 py-1 text-xs border border-red-500/30 text-red-400 rounded-lg hover:bg-red-500/10">✕
                                                    Tolak</button>
                                            </div>
                                        <?php elseif($ot->status === 'pending'): ?>
                                            <form method="POST" action="<?php echo e(route('hrm.overtime.destroy', $ot)); ?>">
                                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                                <button type="submit"
                                                    onclick="return confirm('Hapus pengajuan ini?')"
                                                    class="text-xs text-gray-400 hover:text-red-400">Hapus</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-300">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if($ot->reason && $ot->status !== 'rejected'): ?>
                                    <tr class="bg-gray-50/50">
                                        <td colspan="7"
                                            class="px-4 py-1.5 text-xs text-gray-400 italic">
                                            Alasan: <?php echo e($ot->reason); ?>

                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7"
                                        class="px-4 py-12 text-center text-gray-400">
                                        Tidak ada pengajuan lembur untuk filter ini.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($requests->hasPages()): ?>
                    <div class="px-4 py-3 border-t border-gray-100">
                        <?php echo e($requests->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div id="modal-reject"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-sm shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <p class="font-semibold text-gray-900 text-sm">Tolak Pengajuan Lembur</p>
                <button onclick="document.getElementById('modal-reject').classList.add('hidden')"
                    class="text-gray-400 hover:text-white">✕</button>
            </div>
            <form id="form-reject" method="POST" class="p-5 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Alasan Penolakan
                        (opsional)</label>
                    <textarea name="rejection_reason" rows="3" placeholder="Masukkan alasan penolakan..."
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-reject').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">Tolak</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            const REJECT_BASE = '<?php echo e(url('hrm/overtime')); ?>';

            function openReject(id) {
                document.getElementById('form-reject').action = `${REJECT_BASE}/${id}/reject`;
                document.getElementById('modal-reject').classList.remove('hidden');
            }

            document.getElementById('modal-reject').addEventListener('click', function(e) {
                if (e.target === this) this.classList.add('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hrm\overtime.blade.php ENDPATH**/ ?>