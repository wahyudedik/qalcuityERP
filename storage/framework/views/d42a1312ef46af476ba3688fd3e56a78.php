<?php
    $g = $data ?? [];
    $totalPoints = $g['total_points'] ?? 0;
    $level = $g['level'] ?? 1;
    $progressPct = $g['progress_percent'] ?? 0;
    $progressTo = $g['progress_to_next_level'] ?? 0;
    $pointsNeeded = $g['points_needed_for_next'] ?? 100;
    $recents = $g['recent_achievements'] ?? collect();
    $rank = $g['rank'] ?? '-';
    $totalUsers = $g['total_users'] ?? 0;
?>
<div class="bg-white rounded-2xl border border-gray-200 p-5 h-full flex flex-col">

    
    <div class="flex items-start justify-between mb-4">
        <div>
            <p class="text-xs font-medium text-gray-500 leading-tight">Achievement & Level</p>
            <p class="text-2xl font-bold text-gray-900 mt-0.5">Lv. <?php echo e($level); ?></p>
        </div>
        <div class="w-9 h-9 rounded-xl bg-yellow-500/20 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
        </div>
    </div>

    
    <div class="flex items-center gap-3 mb-4">
        <div class="flex-1 bg-gray-50 rounded-xl px-3 py-2 text-center">
            <p class="text-xs text-gray-500">Poin</p>
            <p class="text-base font-bold text-gray-900"><?php echo e(number_format($totalPoints)); ?></p>
        </div>
        <div class="flex-1 bg-gray-50 rounded-xl px-3 py-2 text-center">
            <p class="text-xs text-gray-500">Peringkat</p>
            <p class="text-base font-bold text-gray-900">#<?php echo e($rank); ?></p>
        </div>
    </div>

    
    <div class="mb-4">
        <div class="flex items-center justify-between mb-1.5">
            <span class="text-xs text-gray-500">Level <?php echo e($level); ?> →
                <?php echo e($level + 1); ?></span>
            <span class="text-xs font-semibold text-indigo-400"><?php echo e($progressPct); ?>%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-2 rounded-full transition-all duration-700"
                style="width: <?php echo e($progressPct); ?>%"></div>
        </div>
        <p class="text-[10px] text-gray-400 mt-1"><?php echo e($progressTo); ?>/<?php echo e($pointsNeeded); ?> poin ke
            level berikutnya</p>
    </div>

    
    <?php if($recents->isNotEmpty()): ?>
        <div class="flex-1 mb-4">
            <p class="text-xs font-medium text-gray-500 mb-2">Achievement Terbaru</p>
            <div class="space-y-2">
                <?php $__currentLoopData = $recents->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ua): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-2.5">
                        <span class="text-lg leading-none shrink-0"><?php echo e($ua->achievement->icon ?? '🏆'); ?></span>
                        <p class="text-xs text-gray-700 truncate">
                            <?php echo e($ua->achievement->name ?? '-'); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php else: ?>
        <div class="flex-1 mb-4 flex items-center justify-center">
            <p class="text-xs text-gray-400 text-center">Belum ada achievement.<br>Mulai
                beraktivitas untuk mendapatkan badge!</p>
        </div>
    <?php endif; ?>

    
    <a href="<?php echo e(route('gamification.index')); ?>"
        class="flex items-center justify-center gap-1.5 w-full text-xs font-semibold text-indigo-400 hover:text-indigo-300 py-2 border border-indigo-500/30 rounded-xl hover:bg-indigo-500/10 transition">
        Lihat semua
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </a>

</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/dashboard/widgets/gamification.blade.php ENDPATH**/ ?>