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
     <?php $__env->slot('header', null, []); ?> Portal Karyawan <?php $__env->endSlot(); ?>

    <?php if(!$employee): ?>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <p class="text-gray-500 dark:text-slate-400 text-sm font-medium">Akun Anda belum terhubung ke data karyawan.</p>
        <p class="text-gray-400 dark:text-slate-500 text-xs mt-1">Hubungi admin untuk menghubungkan akun ke profil karyawan.</p>
    </div>
    <?php else: ?>

    <?php if(session('success')): ?>
    <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm mb-5"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-5 flex items-center gap-5">
        <img src="<?php echo e(auth()->user()->avatarUrl()); ?>" alt="avatar"
            class="w-16 h-16 rounded-full object-cover ring-2 ring-blue-500/30">
        <div class="flex-1 min-w-0">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo e($employee->name); ?></h2>
            <p class="text-sm text-gray-500 dark:text-slate-400">
                <?php echo e($employee->position ?? 'Karyawan'); ?>

                <?php if($employee->department): ?> · <?php echo e($employee->department); ?> <?php endif; ?>
            </p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">
                ID: <?php echo e($employee->employee_id); ?> · Bergabung <?php echo e($employee->join_date?->format('d M Y') ?? '-'); ?>

            </p>
        </div>
        <a href="<?php echo e(route('self-service.profile')); ?>"
            class="shrink-0 px-4 py-2 text-sm bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition">
            Edit Profil
        </a>
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Sisa Cuti Tahunan</p>
            <p class="text-3xl font-black text-blue-500"><?php echo e($leaveQuota - $leaveUsed); ?></p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">dari <?php echo e($leaveQuota); ?> hari</p>
            <?php if($leavePending > 0): ?>
            <p class="text-xs text-amber-500 mt-1"><?php echo e($leavePending); ?> menunggu persetujuan</p>
            <?php endif; ?>
        </div>
        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Hadir Bulan Ini</p>
            <p class="text-3xl font-black text-green-500"><?php echo e($monthStats['present'] ?? 0); ?></p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">
                Terlambat: <?php echo e($monthStats['late'] ?? 0); ?> · Absen: <?php echo e($monthStats['absent'] ?? 0); ?>

            </p>
        </div>
        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Gaji Terakhir</p>
            <?php if($latestPayslip): ?>
            <p class="text-lg font-black text-gray-900 dark:text-white">Rp <?php echo e(number_format($latestPayslip->net_salary, 0, ',', '.')); ?></p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1"><?php echo e($latestPayslip->payrollRun?->period ?? '-'); ?></p>
            <?php else: ?>
            <p class="text-sm text-gray-400 dark:text-slate-500 mt-2">Belum ada</p>
            <?php endif; ?>
        </div>
        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Status Hari Ini</p>
            <?php if($todayAttendance): ?>
                <?php
                    $statusColor = match($todayAttendance->status) {
                        'present' => 'text-green-500', 'late' => 'text-amber-500',
                        'absent'  => 'text-red-500',   default => 'text-gray-400',
                    };
                    $statusLabel = match($todayAttendance->status) {
                        'present' => 'Hadir', 'late' => 'Terlambat',
                        'absent'  => 'Absen', 'leave' => 'Cuti', 'sick' => 'Sakit', default => '-',
                    };
                ?>
                <p class="text-xl font-black <?php echo e($statusColor); ?>"><?php echo e($statusLabel); ?></p>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">
                    Masuk: <?php echo e($todayAttendance->check_in ?? '-'); ?>

                    <?php if($todayAttendance->check_out): ?> · Keluar: <?php echo e($todayAttendance->check_out); ?> <?php endif; ?>
                </p>
            <?php else: ?>
                <p class="text-xl font-black text-gray-400 dark:text-slate-500">Belum Absen</p>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-1"><?php echo e(today()->format('d M Y')); ?></p>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Absensi Hari Ini</h3>
            <p class="text-3xl font-mono font-bold text-gray-900 dark:text-white mb-1" id="ess-clock">--:--:--</p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mb-5"><?php echo e(today()->translatedFormat('l, d F Y')); ?></p>

            <?php if(!$todayAttendance || !$todayAttendance->check_in): ?>
            <form method="POST" action="<?php echo e(route('self-service.attendance.clock-in')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="w-full py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold text-sm transition">
                    ✓ Clock In
                </button>
            </form>
            <?php elseif(!$todayAttendance->check_out): ?>
            <div class="text-xs text-gray-500 dark:text-slate-400 mb-3">
                Masuk pukul <span class="font-semibold text-gray-900 dark:text-white"><?php echo e($todayAttendance->check_in); ?></span>
            </div>
            <form method="POST" action="<?php echo e(route('self-service.attendance.clock-out')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="w-full py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold text-sm transition">
                    ✗ Clock Out
                </button>
            </form>
            <?php else: ?>
            <div class="text-center py-3 bg-gray-50 dark:bg-white/5 rounded-xl">
                <p class="text-sm text-gray-500 dark:text-slate-400">Selesai hari ini</p>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">
                    <?php echo e($todayAttendance->check_in); ?> — <?php echo e($todayAttendance->check_out); ?>

                </p>
            </div>
            <?php endif; ?>

            <?php $__errorArgs = ['clock'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="text-xs text-red-400 mt-2"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Menu Cepat</h3>
            <div class="space-y-2">
                <a href="<?php echo e(route('self-service.leave.index')); ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-500/20 transition text-sm font-medium">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Ajukan Cuti
                    <?php if($leavePending > 0): ?>
                    <span class="ml-auto text-xs bg-amber-500/20 text-amber-500 px-1.5 py-0.5 rounded-md"><?php echo e($leavePending); ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo e(route('payroll.slip.index')); ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-500/20 transition text-sm font-medium">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Slip Gaji
                </a>
                <a href="<?php echo e(route('self-service.attendance.index')); ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-500/20 transition text-sm font-medium">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Riwayat Absensi
                </a>
                <a href="<?php echo e(route('self-service.profile')); ?>"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gray-50 dark:bg-white/5 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-white/10 transition text-sm font-medium">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Update Profil
                </a>
            </div>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Kinerja Terakhir</h3>
            <?php if($latestReview): ?>
            <?php
                $scoreColor = match(true) {
                    $latestReview->overall_score >= 4.5 => 'text-green-500',
                    $latestReview->overall_score >= 3.5 => 'text-blue-500',
                    $latestReview->overall_score >= 2.5 => 'text-amber-500',
                    default => 'text-red-500',
                };
            ?>
            <div class="flex items-center gap-3 mb-4">
                <div class="text-4xl font-black <?php echo e($scoreColor); ?>"><?php echo e(number_format($latestReview->overall_score, 1)); ?></div>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($latestReview->overallLabel()); ?></p>
                    <p class="text-xs text-gray-400 dark:text-slate-500"><?php echo e($latestReview->period); ?> · <?php echo e(ucfirst($latestReview->period_type)); ?></p>
                </div>
            </div>
            <div class="space-y-2">
                <?php $__currentLoopData = ['score_work_quality' => 'Kualitas Kerja', 'score_productivity' => 'Produktivitas', 'score_teamwork' => 'Kerjasama', 'score_initiative' => 'Inisiatif', 'score_attendance' => 'Kehadiran']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-slate-400 w-28 shrink-0"><?php echo e($label); ?></span>
                    <div class="flex-1 h-1.5 bg-gray-100 dark:bg-white/10 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full" style="width: <?php echo e(($latestReview->$field / 5) * 100); ?>%"></div>
                    </div>
                    <span class="text-xs font-medium text-gray-700 dark:text-slate-300 w-4"><?php echo e($latestReview->$field); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php if($latestReview->recommendation): ?>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-3">Rekomendasi: <span class="font-medium text-gray-700 dark:text-slate-300"><?php echo e($latestReview->recommendationLabel()); ?></span></p>
            <?php endif; ?>
            <?php else: ?>
            <div class="text-center py-6">
                <p class="text-sm text-gray-400 dark:text-slate-500">Belum ada penilaian kinerja.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if($latestPayslip): ?>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-900 dark:text-white">Slip Gaji Terakhir — <?php echo e($latestPayslip->payrollRun?->period); ?></h3>
            <a href="<?php echo e(route('payroll.slip.show', $latestPayslip)); ?>"
                class="text-sm text-blue-500 hover:text-blue-400 transition">Lihat Detail →</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Gaji Pokok</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">Rp <?php echo e(number_format($latestPayslip->base_salary, 0, ',', '.')); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Tunjangan</p>
                <p class="text-sm font-semibold text-green-500 mt-0.5">+ Rp <?php echo e(number_format($latestPayslip->allowances, 0, ',', '.')); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Potongan</p>
                <p class="text-sm font-semibold text-red-400 mt-0.5">- Rp <?php echo e(number_format($latestPayslip->deduction_absent + $latestPayslip->deduction_late + $latestPayslip->deduction_other + $latestPayslip->tax_pph21 + $latestPayslip->bpjs_employee, 0, ',', '.')); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Gaji Bersih</p>
                <p class="text-lg font-black text-blue-500 mt-0.5">Rp <?php echo e(number_format($latestPayslip->net_salary, 0, ',', '.')); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php endif; ?>

    <script>
    (function() {
        const el = document.getElementById('ess-clock');
        if (!el) return;
        function tick() {
            const now = new Date();
            el.textContent = now.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
        }
        tick();
        setInterval(tick, 1000);
    })();
    </script>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/self-service/dashboard.blade.php ENDPATH**/ ?>