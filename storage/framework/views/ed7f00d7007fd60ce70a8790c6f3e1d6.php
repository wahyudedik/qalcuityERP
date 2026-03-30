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

    <div class="max-w-2xl">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex items-start gap-4 px-6 py-4 border-b border-white/5 last:border-0 hover:bg-gray-50 dark:hover:bg-white/5 transition <?php echo e($notif->isRead() ? 'opacity-50' : ''); ?>">
                <div class="w-9 h-9 rounded-xl shrink-0 flex items-center justify-center
                    <?php echo e($notif->type === 'low_stock' ? 'bg-red-500/20' : ($notif->type === 'missing_report' ? 'bg-amber-500/20' : 'bg-blue-500/20')); ?>">
                    <svg class="w-4 h-4 <?php echo e($notif->type === 'low_stock' ? 'text-red-400' : ($notif->type === 'missing_report' ? 'text-amber-400' : 'text-blue-400')); ?>"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($notif->title); ?></p>
                        <?php if(!$notif->isRead()): ?>
                        <form method="POST" action="<?php echo e(route('notifications.read', $notif)); ?>" class="shrink-0">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="text-xs text-blue-400 hover:underline whitespace-nowrap">Tandai dibaca</button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5 leading-relaxed"><?php echo e($notif->body); ?></p>
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-1.5"><?php echo e($notif->created_at->diffForHumans()); ?></p>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="flex flex-col items-center py-16 text-gray-400 dark:text-slate-500">
                <svg class="w-12 h-12 mb-3 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-sm">Tidak ada notifikasi</p>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\notifications\index.blade.php ENDPATH**/ ?>