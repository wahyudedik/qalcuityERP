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
     <?php $__env->slot('header', null, []); ?> Lembur Saya <?php $__env->endSlot(); ?>

    <?php if(!$employee): ?>
    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <p class="text-gray-500 text-sm">Akun Anda belum terhubung ke data karyawan.</p>
        <p class="text-gray-400 text-xs mt-1">Hubungi admin untuk menghubungkan akun ke profil karyawan.</p>
    </div>
    <?php else: ?>

    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500 mb-1">Menunggu Persetujuan</p>
            <p class="text-3xl font-black text-amber-500"><?php echo e($stats['pending']); ?></p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500 mb-1">Disetujui Tahun Ini</p>
            <p class="text-3xl font-black text-green-500"><?php echo e($stats['approved']); ?></p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500 mb-1">Total Jam Lembur (Tahun Ini)</p>
            <p class="text-3xl font-black text-blue-500"><?php echo e(number_format($stats['total_hours'], 1)); ?><span class="text-base font-normal text-gray-400 ml-1">jam</span></p>
        </div>
    </div>

    <?php if($errors->any()): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4">
        <?php echo e($errors->first()); ?>

    </div>
    <?php endif; ?>
    <?php if(session('success')): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-4">
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>

    <div class="flex flex-col lg:flex-row gap-6">

        
        <div class="lg:w-80 shrink-0">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Ajukan Lembur</h3>
                <form method="POST" action="<?php echo e(route('self-service.overtime.store')); ?>" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Lembur</label>
                        <input type="date" name="date" value="<?php echo e(old('date')); ?>" required
                            class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <?php $__errorArgs = ['date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Mulai</label>
                            <input type="time" name="start_time" value="<?php echo e(old('start_time')); ?>" required
                                class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Selesai</label>
                            <input type="time" name="end_time" value="<?php echo e(old('end_time')); ?>" required
                                class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    <?php $__errorArgs = ['end_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs -mt-2"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Alasan Lembur</label>
                        <textarea name="reason" rows="3" required placeholder="Jelaskan alasan lembur..."
                            class="w-full rounded-xl border border-gray-300 bg-white text-gray-900 text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"><?php echo e(old('reason')); ?></textarea>
                        <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <button type="submit"
                        class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
                        Kirim Pengajuan
                    </button>
                </form>
            </div>
        </div>

        
        <div class="flex-1 min-w-0">
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Riwayat Pengajuan Lembur</h3>
                </div>
                <?php if($overtimes->isEmpty()): ?>
                <div class="p-12 text-center">
                    <p class="text-gray-400 text-sm">Belum ada pengajuan lembur.</p>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Waktu</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Durasi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Alasan</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Upah Est.</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__currentLoopData = $overtimes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-gray-900 font-medium">
                                    <?php echo e($ot->date->format('d M Y')); ?>

                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    <?php echo e(substr($ot->start_time, 0, 5)); ?> – <?php echo e(substr($ot->end_time, 0, 5)); ?>

                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    <?php echo e($ot->durationLabel()); ?>

                                </td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs truncate">
                                    <?php echo e($ot->reason); ?>

                                </td>
                                <td class="px-4 py-3 text-right text-gray-900">
                                    Rp <?php echo e(number_format($ot->overtime_pay, 0, ',', '.')); ?>

                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php
                                        $badge = match($ot->status) {
                                            'approved' => 'bg-green-100 text-green-700',
                                            'rejected' => 'bg-red-100 text-red-700',
                                            default    => 'bg-amber-100 text-amber-700',
                                        };
                                        $label = match($ot->status) {
                                            'approved' => 'Disetujui',
                                            'rejected' => 'Ditolak',
                                            default    => 'Menunggu',
                                        };
                                    ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium <?php echo e($badge); ?>"><?php echo e($label); ?></span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <?php if($ot->status === 'pending'): ?>
                                    <form method="POST" action="<?php echo e(route('self-service.overtime.cancel', $ot)); ?>"
                                        onsubmit="return confirm('Batalkan pengajuan lembur ini?')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition-colors">
                                            Batalkan
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-3 border-t border-gray-100">
                    <?php echo e($overtimes->links()); ?>

                </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\self-service\overtime.blade.php ENDPATH**/ ?>