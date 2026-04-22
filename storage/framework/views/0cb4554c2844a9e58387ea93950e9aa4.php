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
     <?php $__env->slot('header', null, []); ?> Peserta Pelatihan — <?php echo e($session->program->name); ?> <?php $__env->endSlot(); ?>

    <div class="flex flex-col lg:flex-row gap-5">

        
        <div class="lg:w-72 shrink-0 space-y-4">
            
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 space-y-2 text-sm">
                <p class="font-semibold text-gray-900 dark:text-white"><?php echo e($session->program->name); ?></p>
                <?php if($session->program->category): ?>
                    <span
                        class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400"><?php echo e($session->program->category); ?></span>
                <?php endif; ?>
                <div class="space-y-1 text-xs text-gray-500 dark:text-slate-400 pt-1">
                    <p>📅 <?php echo e($session->start_date->format('d M Y')); ?> – <?php echo e($session->end_date->format('d M Y')); ?></p>
                    <?php if($session->location): ?>
                        <p>📍 <?php echo e($session->location); ?></p>
                    <?php endif; ?>
                    <?php if($session->trainer): ?>
                        <p>👤 <?php echo e($session->trainer); ?></p>
                    <?php endif; ?>
                    <p>👥
                        <?php echo e($session->participants->count()); ?><?php echo e($session->max_participants > 0 ? '/' . $session->max_participants : ''); ?>

                        peserta</p>
                </div>
                
                <form method="POST" action="<?php echo e(route('hrm.training.sessions.status', $session)); ?>" class="pt-2">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <div class="flex gap-2">
                        <select name="status"
                            class="flex-1 px-2 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php $__currentLoopData = ['scheduled' => 'Terjadwal', 'ongoing' => 'Berlangsung', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($v); ?>" <?php if($session->status === $v): echo 'selected'; endif; ?>><?php echo e($l); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <button type="submit"
                            class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>

            
            <?php if(!$session->isFull()): ?>
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3 text-sm">Tambah Peserta</h3>
                    <form method="POST" action="<?php echo e(route('hrm.training.sessions.participants.add', $session)); ?>"
                        class="space-y-2">
                        <?php echo csrf_field(); ?>
                        <select name="employee_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih karyawan...</option>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($emp->id); ?>"><?php echo e($emp->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <button type="submit"
                            class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Daftarkan</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4 text-xs text-amber-300">Sesi
                    sudah penuh.</div>
            <?php endif; ?>

            <a href="<?php echo e(route('hrm.training.index', ['tab' => 'sessions'])); ?>"
                class="block text-center px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                ← Kembali
            </a>
        </div>

        
        <div class="flex-1 min-w-0">
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Daftar Peserta</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Karyawan</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Nilai</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <?php $__empty_1 = true; $__currentLoopData = $session->participants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            <?php echo e($p->employee->name ?? '-'); ?></p>
                                        <p class="text-xs text-gray-400 dark:text-slate-500">
                                            <?php echo e($p->employee->department ?? ($p->employee->position ?? '')); ?></p>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form method="POST"
                                            action="<?php echo e(route('hrm.training.participants.update', $p)); ?>"
                                            class="inline-flex items-center gap-1">
                                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                            <select name="status" onchange="this.form.submit()"
                                                class="px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none">
                                                <?php $__currentLoopData = ['registered' => 'Terdaftar', 'attended' => 'Hadir', 'passed' => 'Lulus', 'failed' => 'Tidak Lulus', 'absent' => 'Absen']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($v); ?>" <?php if($p->status === $v): echo 'selected'; endif; ?>>
                                                        <?php echo e($l); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form method="POST"
                                            action="<?php echo e(route('hrm.training.participants.update', $p)); ?>"
                                            class="inline-flex items-center gap-1">
                                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                            <input type="hidden" name="status" value="<?php echo e($p->status); ?>">
                                            <input type="number" name="score" value="<?php echo e($p->score); ?>"
                                                min="0" max="100" placeholder="—"
                                                class="w-16 px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white text-center focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            <button type="submit"
                                                class="text-xs text-blue-400 hover:text-blue-300">Confirm</button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <form method="POST"
                                            action="<?php echo e(route('hrm.training.participants.remove', $p)); ?>">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" onclick="return confirm('Hapus peserta ini?')"
                                                class="text-xs text-red-400 hover:text-red-300">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center text-gray-400 dark:text-slate-500">
                                        Belum ada peserta terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hrm\training-session.blade.php ENDPATH**/ ?>