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
     <?php $__env->slot('title', null, []); ?> Achievement & Gamification — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> Achievement & Gamification <?php $__env->endSlot(); ?>

    <?php
        $categoryLabels = [
            'sales' => 'Penjualan',
            'finance' => 'Keuangan',
            'inventory' => 'Inventori',
            'hrm' => 'SDM',
            'production' => 'Produksi',
            'general' => 'Umum',
        ];
        $categoryColors = [
            'sales' => [
                'bg' => 'bg-blue-500/20',
                'text' => 'text-blue-400',
                'border' => 'border-blue-500/30',
                'badge' => 'bg-blue-600',
            ],
            'finance' => [
                'bg' => 'bg-emerald-500/20',
                'text' => 'text-emerald-400',
                'border' => 'border-emerald-500/30',
                'badge' => 'bg-emerald-600',
            ],
            'inventory' => [
                'bg' => 'bg-amber-500/20',
                'text' => 'text-amber-400',
                'border' => 'border-amber-500/30',
                'badge' => 'bg-amber-600',
            ],
            'hrm' => [
                'bg' => 'bg-pink-500/20',
                'text' => 'text-pink-400',
                'border' => 'border-pink-500/30',
                'badge' => 'bg-pink-600',
            ],
            'production' => [
                'bg' => 'bg-cyan-500/20',
                'text' => 'text-cyan-400',
                'border' => 'border-cyan-500/30',
                'badge' => 'bg-cyan-600',
            ],
            'general' => [
                'bg' => 'bg-purple-500/20',
                'text' => 'text-purple-400',
                'border' => 'border-purple-500/30',
                'badge' => 'bg-purple-600',
            ],
        ];
        $activeTab = request()->routeIs('gamification.achievements')
            ? 'achievements'
            : (request()->routeIs('gamification.leaderboard')
                ? 'leaderboard'
                : (request()->routeIs('gamification.points')
                    ? 'points'
                    : 'all'));
    ?>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-gray-500 dark:text-slate-400">Total Poin</p>
                <div class="w-9 h-9 rounded-xl bg-yellow-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e(number_format($stats['total_points'])); ?></p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">poin terkumpul</p>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-gray-500 dark:text-slate-400">Level</p>
                <div class="w-9 h-9 rounded-xl bg-indigo-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($stats['level']); ?></p>
            <div class="mt-2">
                <div class="flex items-center justify-between mb-1">
                    <span
                        class="text-xs text-gray-400 dark:text-slate-500"><?php echo e($stats['progress_to_next_level']); ?>/<?php echo e($stats['points_needed_for_next']); ?>

                        poin</span>
                    <span class="text-xs font-medium text-indigo-400"><?php echo e($stats['progress_percent']); ?>%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-white/10 rounded-full h-1.5">
                    <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-500"
                        style="width: <?php echo e($stats['progress_percent']); ?>%"></div>
                </div>
            </div>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-gray-500 dark:text-slate-400">Peringkat</p>
                <div class="w-9 h-9 rounded-xl bg-orange-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">#<?php echo e($stats['rank']); ?></p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">dari <?php echo e($stats['total_users']); ?> pengguna</p>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-gray-500 dark:text-slate-400">Achievement</p>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($stats['earned_achievements']); ?><span
                    class="text-base font-normal text-gray-400 dark:text-slate-500">/<?php echo e($stats['total_achievements']); ?></span>
            </p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">achievement terbuka</p>
        </div>

    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-1.5 mb-6">
        <div class="flex gap-1">
            <a href="<?php echo e(route('gamification.index')); ?>"
                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-all
                      <?php echo e($activeTab === 'all' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30' : 'text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white'); ?>">
                🏆 Semua
            </a>
            <a href="<?php echo e(route('gamification.achievements')); ?>"
                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-all
                      <?php echo e($activeTab === 'achievements' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30' : 'text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white'); ?>">
                🎖️ Achievement
            </a>
            <a href="<?php echo e(route('gamification.leaderboard')); ?>"
                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-all
                      <?php echo e($activeTab === 'leaderboard' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30' : 'text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white'); ?>">
                📊 Leaderboard
            </a>
            <a href="<?php echo e(route('gamification.points')); ?>"
                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-all
                      <?php echo e($activeTab === 'points' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30' : 'text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white'); ?>">
                💰 Riwayat Poin
            </a>
        </div>
    </div>

    
    <?php if(!($showLeaderboardOnly ?? false) && $stats['recent_achievements']->isNotEmpty()): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Achievement Terbaru</h3>
            <div class="flex gap-3 overflow-x-auto pb-1 scrollbar-hide">
                <?php $__currentLoopData = $stats['recent_achievements']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ua): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div
                        class="flex-shrink-0 flex flex-col items-center gap-2 p-3 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 w-28 text-center">
                        <div
                            class="w-12 h-12 rounded-full flex items-center justify-center text-2xl
                    <?php
$cat = $ua->achievement->category ?? 'general';
                        echo $categoryColors[$cat]['bg'] ?? 'bg-purple-500/20'; ?>">
                            <?php echo e($ua->achievement->icon ?? '🏆'); ?>

                        </div>
                        <p class="text-xs font-medium text-gray-900 dark:text-white leading-tight line-clamp-2">
                            <?php echo e($ua->achievement->name); ?></p>
                        <p class="text-[10px] text-gray-400 dark:text-slate-500">
                            <?php echo e(\Carbon\Carbon::parse($ua->earned_at)->format('d M Y')); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(!($showAchievementsOnly ?? false)): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Leaderboard</h3>
                <?php if($activeTab !== 'leaderboard'): ?>
                    <a href="<?php echo e(route('gamification.leaderboard')); ?>"
                        class="text-xs text-indigo-400 hover:text-indigo-300 transition">Lihat semua →</a>
                <?php endif; ?>
            </div>

            <?php if($leaderboard->isEmpty()): ?>
                <div class="text-center py-8">
                    <p class="text-sm text-gray-400 dark:text-slate-500">Belum ada data leaderboard</p>
                </div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php $__currentLoopData = $leaderboard; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $rank = $i + 1;
                            $isCurrentUser = $member->id === $user->id;
                            $medalEmoji = match ($rank) {
                                1 => '🥇',
                                2 => '🥈',
                                3 => '🥉',
                                default => null,
                            };
                            $rowClass = $isCurrentUser
                                ? 'bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 rounded-xl'
                                : 'hover:bg-gray-50 dark:hover:bg-white/5 rounded-xl transition';
                        ?>
                        <div class="flex items-center gap-3 px-3 py-2.5 <?php echo e($rowClass); ?>">
                            
                            <div class="w-8 text-center shrink-0">
                                <?php if($medalEmoji): ?>
                                    <span class="text-lg"><?php echo e($medalEmoji); ?></span>
                                <?php else: ?>
                                    <span
                                        class="text-sm font-semibold text-gray-500 dark:text-slate-400">#<?php echo e($rank); ?></span>
                                <?php endif; ?>
                            </div>

                            
                            <div class="flex items-center gap-2.5 flex-1 min-w-0">
                                <?php if($member->avatar): ?>
                                    <img src="<?php echo e($member->avatar); ?>" alt="<?php echo e($member->name); ?>"
                                        class="w-8 h-8 rounded-full object-cover shrink-0">
                                <?php else: ?>
                                    <div
                                        class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center shrink-0">
                                        <span
                                            class="text-xs font-bold text-indigo-400"><?php echo e(strtoupper(substr($member->name, 0, 1))); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="min-w-0">
                                    <p
                                        class="text-sm font-medium text-gray-900 dark:text-white truncate <?php echo e($isCurrentUser ? 'text-indigo-600 dark:text-indigo-400' : ''); ?>">
                                        <?php echo e($member->name); ?>

                                        <?php if($isCurrentUser): ?>
                                            <span class="text-xs text-indigo-400">(Kamu)</span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-slate-500 capitalize">
                                        <?php echo e($member->role); ?>

                                    </p>
                                </div>
                            </div>

                            
                            <div class="shrink-0 text-center hidden sm:block">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-indigo-500/20 text-indigo-400">
                                    Lv. <?php echo e($member->gamification_level ?? 1); ?>

                                </span>
                            </div>

                            
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-bold text-gray-900 dark:text-white">
                                    <?php echo e(number_format($member->gamification_points)); ?></p>
                                <p class="text-xs text-gray-400 dark:text-slate-500">poin</p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    
    <?php if(!($showLeaderboardOnly ?? false)): ?>
        <div class="space-y-8">
            <?php $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $achievements): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $label = $categoryLabels[$category] ?? ucfirst($category);
                    $colors = $categoryColors[$category] ?? $categoryColors['general'];
                ?>
                <div>
                    
                    <div class="flex items-center gap-2 mb-4">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold <?php echo e($colors['bg']); ?> <?php echo e($colors['text']); ?> border <?php echo e($colors['border']); ?>">
                            <?php echo e($label); ?>

                        </span>
                        <div class="flex-1 h-px bg-gray-200 dark:bg-white/10"></div>
                        <span class="text-xs text-gray-400 dark:text-slate-500">
                            <?php echo e($achievements->filter(fn($a) => in_array($a->id, $earnedIds ?? []))->count()); ?>/<?php echo e($achievements->count()); ?>

                            tercapai
                        </span>
                    </div>

                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <?php $__currentLoopData = $achievements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $achievement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $earned = in_array($achievement->id, $earnedIds ?? []);
                                $progress = $userAchievements[$achievement->id] ?? 0;
                                $target = $achievement->requirement_value ?? 1;
                                $pct = $target > 0 ? min(100, (int) round(($progress / $target) * 100)) : 0;
                            ?>

                            <div
                                class="relative rounded-2xl border overflow-hidden transition-all duration-200
                    <?php echo e($earned
                        ? $colors['bg'] . ' ' . $colors['border'] . ' shadow-sm'
                        : 'bg-white dark:bg-[#1e293b] border-gray-200 dark:border-white/10 opacity-70 hover:opacity-90'); ?>">

                                
                                <?php if(!$earned): ?>
                                    <div class="absolute top-2 right-2 z-10">
                                        <div
                                            class="w-6 h-6 rounded-lg bg-gray-500/20 flex items-center justify-center">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="p-4">
                                    
                                    <div class="text-3xl mb-3 leading-none"><?php echo e($achievement->icon ?? '🏆'); ?></div>

                                    
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                                        <?php echo e($achievement->name); ?></h4>
                                    <p class="text-xs text-gray-500 dark:text-slate-400 leading-relaxed mb-3">
                                        <?php echo e($achievement->description); ?></p>

                                    
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-semibold <?php echo e($colors['bg']); ?> <?php echo e($colors['text']); ?>">
                                            ⭐ <?php echo e($achievement->points); ?> poin
                                        </span>

                                        <?php if($earned): ?>
                                            <span class="text-xs font-medium text-emerald-400 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Tercapai!
                                            </span>
                                        <?php else: ?>
                                            
                                            <span
                                                class="text-xs text-gray-400 dark:text-slate-500"><?php echo e($progress); ?>/<?php echo e($target); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    
                                    <?php if(!$earned): ?>
                                        <div class="mt-3">
                                            <div class="w-full bg-gray-200 dark:bg-white/10 rounded-full h-1.5">
                                                <div class="h-1.5 rounded-full transition-all duration-500 <?php echo e($colors['badge']); ?>"
                                                    style="width: <?php echo e($pct); ?>%"></div>
                                            </div>
                                            <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-1 text-right">
                                                <?php echo e($pct); ?>%</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($grouped->isEmpty()): ?>
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
                    <div class="text-5xl mb-4">🎖️</div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">Belum ada achievement</p>
                    <p class="text-xs text-gray-400 dark:text-slate-500">Achievement akan muncul setelah dikonfigurasi
                        oleh admin.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    
    <?php if(!empty($showPointsHistory)): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Riwayat Poin</h3>
            <?php if(isset($points) && $points->isNotEmpty()): ?>
                <div class="space-y-2">
                    <?php $__currentLoopData = $points; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full <?php echo e($log->points > 0 ? 'bg-emerald-500/20' : 'bg-red-500/20'); ?> flex items-center justify-center shrink-0">
                                    <span class="text-sm"><?php echo e($log->points > 0 ? '⬆' : '⬇'); ?></span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-900 dark:text-white"><?php echo e($log->reason); ?></p>
                                    <p class="text-xs text-gray-400 dark:text-slate-500">
                                        <?php echo e($log->created_at->diffForHumans()); ?></p>
                                </div>
                            </div>
                            <span
                                class="text-sm font-bold <?php echo e($log->points > 0 ? 'text-emerald-400' : 'text-red-400'); ?> shrink-0">
                                <?php echo e($log->points > 0 ? '+' : ''); ?><?php echo e(number_format($log->points)); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="text-center py-10">
                    <div class="text-4xl mb-3">💰</div>
                    <p class="text-sm text-gray-400 dark:text-slate-500">Belum ada riwayat poin.</p>
                </div>
            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\gamification\index.blade.php ENDPATH**/ ?>