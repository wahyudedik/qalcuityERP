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
     <?php $__env->slot('title', null, []); ?> Notifikasi — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> Notifikasi <?php $__env->endSlot(); ?>
     <?php $__env->slot('topbarActions', null, []); ?> 
        <form method="POST" action="<?php echo e(route('notifications.read-all')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit"
                class="text-sm text-blue-400 hover:text-blue-300 font-medium px-3 py-2 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition">
                Tandai semua dibaca
            </button>
        </form>
     <?php $__env->endSlot(); ?>

    <?php
        $tabs = [
            'all' => ['label' => 'Semua', 'icon' => '🔔'],
            'inventory' => ['label' => 'Inventori', 'icon' => '📦'],
            'finance' => ['label' => 'Keuangan', 'icon' => '💰'],
            'hrm' => ['label' => 'HRM', 'icon' => '👥'],
            'sales' => ['label' => 'Penjualan', 'icon' => '🛒'],
            'ai' => ['label' => 'AI', 'icon' => '🤖'],
            'system' => ['label' => 'Sistem', 'icon' => '⚙️'],
        ];

        $moduleColors = [
            'inventory' => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
            'finance' => 'bg-green-500/20 text-green-400 border-green-500/30',
            'hrm' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
            'sales' => 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30',
            'ai' => 'bg-pink-500/20 text-pink-400 border-pink-500/30',
            'system' => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
        ];

        $iconColors = [
            'inventory' => ['bg' => 'bg-orange-500/20', 'text' => 'text-orange-400'],
            'finance' => ['bg' => 'bg-green-500/20', 'text' => 'text-green-400'],
            'hrm' => ['bg' => 'bg-purple-500/20', 'text' => 'text-purple-400'],
            'sales' => ['bg' => 'bg-cyan-500/20', 'text' => 'text-cyan-400'],
            'ai' => ['bg' => 'bg-pink-500/20', 'text' => 'text-pink-400'],
            'system' => ['bg' => 'bg-slate-500/20', 'text' => 'text-slate-400'],
        ];

        $totalUnread = $moduleCounts->sum();
    ?>

    <div class="max-w-2xl space-y-4">

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-1.5">
            <div class="flex flex-wrap gap-1">
                <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $count = $key === 'all' ? $totalUnread : $moduleCounts[$key] ?? 0;
                        $isActive = $activeModule === $key;
                    ?>
                    <a href="<?php echo e(route('notifications.index', $key !== 'all' ? ['module' => $key] : [])); ?>"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-medium transition-all
                               <?php echo e($isActive
                                   ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/30'
                                   : 'text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white'); ?>">
                        <span><?php echo e($tab['icon']); ?></span>
                        <span><?php echo e($tab['label']); ?></span>
                        <?php if($count > 0): ?>
                            <span
                                class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-xs font-bold
                                         <?php echo e($isActive ? 'bg-white/20 text-white' : 'bg-red-500 text-white'); ?>">
                                <?php echo e($count > 99 ? '99+' : $count); ?>

                            </span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $mod = $notif->module ?? 'system';
                    $iconBg = $iconColors[$mod]['bg'] ?? 'bg-blue-500/20';
                    $iconTxt = $iconColors[$mod]['text'] ?? 'text-blue-400';
                    $badgeCls = $moduleColors[$mod] ?? 'bg-slate-500/20 text-slate-400 border-slate-500/30';
                    $badgeLabel = $tabs[$mod]['label'] ?? ucfirst($mod);
                    $badgeIcon = $tabs[$mod]['icon'] ?? '🔔';
                ?>
                <div
                    class="flex items-start gap-4 px-6 py-4 border-b border-gray-100 dark:border-white/5 last:border-0 hover:bg-gray-50 dark:hover:bg-white/5 transition <?php echo e($notif->isRead() ? 'opacity-50' : ''); ?>">
                    <div class="w-9 h-9 rounded-xl shrink-0 flex items-center justify-center <?php echo e($iconBg); ?>">
                        <svg class="w-4 h-4 <?php echo e($iconTxt); ?>" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2 flex-wrap">
                            <div class="flex items-center gap-2 flex-wrap min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($notif->title); ?></p>
                                <?php if($activeModule === 'all'): ?>
                                    <span
                                        class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-xs font-medium border <?php echo e($badgeCls); ?> shrink-0">
                                        <?php echo e($badgeIcon); ?> <?php echo e($badgeLabel); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if(!$notif->isRead()): ?>
                                <form method="POST" action="<?php echo e(route('notifications.read', $notif)); ?>"
                                    class="shrink-0">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit"
                                        class="text-xs text-blue-400 hover:underline whitespace-nowrap">Tandai
                                        dibaca</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5 leading-relaxed"><?php echo e($notif->body); ?>

                        </p>
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1.5">
                            <?php echo e($notif->created_at->diffForHumans()); ?></p>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="flex flex-col items-center py-16 text-gray-400 dark:text-slate-500">
                    <svg class="w-12 h-12 mb-3 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-sm">Tidak ada notifikasi</p>
                    <?php if($activeModule !== 'all'): ?>
                        <a href="<?php echo e(route('notifications.index')); ?>"
                            class="mt-2 text-xs text-blue-400 hover:underline">Lihat semua notifikasi</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if(method_exists($notifications, 'hasPages') && $notifications->hasPages()): ?>
            <div class="mt-4"><?php echo e($notifications->links()); ?></div>
        <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/notifications/index.blade.php ENDPATH**/ ?>