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
     <?php $__env->slot('header', null, []); ?> Absensi Saya <?php $__env->endSlot(); ?>

    <?php if(!$employee): ?>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <p class="text-gray-500 dark:text-slate-400 text-sm">Akun Anda belum terhubung ke data karyawan.</p>
        <p class="text-gray-400 dark:text-slate-500 text-xs mt-1">Hubungi admin untuk menghubungkan akun ke profil karyawan.</p>
    </div>
    <?php else: ?>

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

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        
        <div class="lg:col-span-1 bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 flex flex-col items-center text-center">
            <p class="text-xs text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-2"><?php echo e(now()->translatedFormat('l, d F Y')); ?></p>

            
            <p id="live-clock" class="text-4xl font-black text-gray-900 dark:text-white tabular-nums mb-4"><?php echo e(now()->format('H:i:s')); ?></p>

            <?php if($today): ?>
                <div class="w-full space-y-2 mb-5">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-slate-400">Clock In</span>
                        <span class="font-semibold text-green-600 dark:text-green-400"><?php echo e($today->check_in ?? '—'); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-slate-400">Clock Out</span>
                        <span class="font-semibold <?php echo e($today->check_out ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-slate-500'); ?>">
                            <?php echo e($today->check_out ?? '—'); ?>

                        </span>
                    </div>
                    <?php if($today->check_in && $today->check_out): ?>
                    <?php
                        $duration = \Carbon\Carbon::parse($today->check_in)->diffInMinutes(\Carbon\Carbon::parse($today->check_out));
                        $h = intdiv($duration, 60); $m = $duration % 60;
                    ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-slate-400">Durasi</span>
                        <span class="font-semibold text-gray-900 dark:text-white"><?php echo e($h); ?>j <?php echo e($m); ?>m</span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-slate-400">Status</span>
                        <?php
                            $sc = match($today->status) {
                                'present' => 'text-green-600 dark:text-green-400',
                                'late'    => 'text-amber-600 dark:text-amber-400',
                                'absent'  => 'text-red-600 dark:text-red-400',
                                default   => 'text-gray-500 dark:text-slate-400',
                            };
                            $sl = match($today->status) {
                                'present' => 'Hadir', 'late' => 'Terlambat', 'absent' => 'Absen',
                                'leave' => 'Cuti', 'sick' => 'Sakit', default => ucfirst($today->status),
                            };
                        ?>
                        <span class="font-semibold <?php echo e($sc); ?>"><?php echo e($sl); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            
            <?php if(!$today || !$today->check_in): ?>
            <form method="POST" action="<?php echo e(route('self-service.attendance.clock-in')); ?>" class="w-full">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl flex items-center justify-center gap-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    Clock In
                </button>
            </form>
            <?php elseif($today->check_in && !$today->check_out): ?>
            <form method="POST" action="<?php echo e(route('self-service.attendance.clock-out')); ?>" class="w-full">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl flex items-center justify-center gap-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Clock Out
                </button>
            </form>
            <?php else: ?>
            <div class="w-full py-3 bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-slate-400 font-medium rounded-xl text-center text-sm">
                ✓ Selesai hari ini
            </div>
            <?php endif; ?>
        </div>

        
        <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-4 content-start">
            <?php
                $statItems = [
                    ['label' => 'Hadir', 'key' => 'present', 'color' => 'text-green-600 dark:text-green-400'],
                    ['label' => 'Terlambat', 'key' => 'late', 'color' => 'text-amber-600 dark:text-amber-400'],
                    ['label' => 'Absen', 'key' => 'absent', 'color' => 'text-red-600 dark:text-red-400'],
                    ['label' => 'Cuti/Sakit', 'key' => 'leave', 'color' => 'text-blue-600 dark:text-blue-400'],
                ];
            ?>
            <?php $__currentLoopData = $statItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($s['label']); ?></p>
                <p class="text-2xl font-bold <?php echo e($s['color']); ?> mt-1">
                    <?php echo e(($monthStats[$s['key']] ?? 0) + ($s['key'] === 'leave' ? ($monthStats['sick'] ?? 0) : 0)); ?>

                </p>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">hari bulan ini</p>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 dark:border-white/10">
            <p class="font-semibold text-gray-900 dark:text-white">Riwayat Absensi (30 Hari Terakhir)</p>
        </div>
        <?php if($history->isEmpty()): ?>
        <div class="px-5 py-10 text-center text-sm text-gray-400 dark:text-slate-500">Belum ada data absensi.</div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-center">Clock In</th>
                        <th class="px-4 py-3 text-center">Clock Out</th>
                        <th class="px-4 py-3 text-center">Durasi</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $dur = '';
                        if ($att->check_in && $att->check_out) {
                            $mins = \Carbon\Carbon::parse($att->check_in)->diffInMinutes(\Carbon\Carbon::parse($att->check_out));
                            $dur = intdiv($mins, 60) . 'j ' . ($mins % 60) . 'm';
                        }
                        $sc = match($att->status) {
                            'present' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                            'late'    => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                            'absent'  => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                            'leave'   => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                            'sick'    => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                            default   => 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400',
                        };
                        $sl = match($att->status) {
                            'present' => 'Hadir', 'late' => 'Terlambat', 'absent' => 'Absen',
                            'leave' => 'Cuti', 'sick' => 'Sakit', default => ucfirst($att->status),
                        };
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">
                            <?php echo e($att->date->translatedFormat('D, d M Y')); ?>

                        </td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-slate-300"><?php echo e($att->check_in ?? '—'); ?></td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-slate-300"><?php echo e($att->check_out ?? '—'); ?></td>
                        <td class="px-4 py-3 text-center text-gray-500 dark:text-slate-400"><?php echo e($dur ?: '—'); ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($sc); ?>"><?php echo e($sl); ?></span>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php $__env->startPush('scripts'); ?>
    <script>
    // Live clock
    function updateClock() {
        const el = document.getElementById('live-clock');
        if (!el) return;
        const now = new Date();
        el.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
    }
    setInterval(updateClock, 1000);
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/self-service/attendance.blade.php ENDPATH**/ ?>