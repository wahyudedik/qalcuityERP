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
     <?php $__env->slot('header', null, []); ?> Pengingat <?php $__env->endSlot(); ?>

    <div class="space-y-6">

        <?php if($overdueCount > 0): ?>
        <div class="flex items-center gap-3 bg-amber-500/10 border border-amber-500/20 text-amber-600 text-sm px-4 py-3 rounded-xl">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <?php echo e($overdueCount); ?> pengingat sudah jatuh tempo dan belum diselesaikan.
        </div>
        <?php endif; ?>

        
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Buat Pengingat Baru</h2>
            <form method="POST" action="<?php echo e(route('reminders.store')); ?>"
                  class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php echo csrf_field(); ?>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Judul *</label>
                    <input type="text" name="title" required placeholder="Judul pengingat..."
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Waktu Pengingat *</label>
                    <input type="datetime-local" name="remind_at" required
                        min="<?php echo e(now()->addMinutes(5)->format('Y-m-d\TH:i')); ?>"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Channel</label>
                    <select name="channel"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                        <option value="app">Notifikasi App</option>
                        <option value="email">Email</option>
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-4">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
                    <input type="text" name="notes" placeholder="Keterangan tambahan..."
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                </div>
                <div class="sm:col-span-2 lg:col-span-4 flex justify-end">
                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">
                        Buat Pengingat
                    </button>
                </div>
            </form>
        </div>

        
        <form method="GET" class="flex gap-3">
            <select name="status"
                class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-blue-500">
                <option value="pending" <?php echo e(request('status','pending') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                <option value="sent" <?php echo e(request('status') === 'sent' ? 'selected' : ''); ?>>Terkirim</option>
                <option value="done" <?php echo e(request('status') === 'done' ? 'selected' : ''); ?>>Selesai</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">Filter</button>
        </form>

        
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <?php if($reminders->isEmpty()): ?>
                <div class="px-6 py-16 text-center text-gray-400 text-sm">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Tidak ada pengingat.
                </div>
            <?php else: ?>
            <div class="divide-y divide-gray-100">
                <?php $__currentLoopData = $reminders ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reminder): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $isOverdue = $reminder->status === 'pending' && $reminder->remind_at->isPast(); ?>
                <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-4 <?php echo e($isOverdue ? 'bg-amber-50' : ''); ?>">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                        <?php echo e($isOverdue ? 'bg-amber-500/20' : ($reminder->status === 'done' ? 'bg-green-500/20' : 'bg-blue-500/20')); ?>">
                        <?php if($reminder->status === 'done'): ?>
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <?php elseif($isOverdue): ?>
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/></svg>
                        <?php else: ?>
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 text-sm <?php echo e($reminder->status === 'done' ? 'line-through opacity-60' : ''); ?>">
                            <?php echo e($reminder->title); ?>

                        </p>
                        <?php if($reminder->notes): ?>
                        <p class="text-xs text-gray-400 mt-0.5"><?php echo e($reminder->notes); ?></p>
                        <?php endif; ?>
                        <div class="flex items-center gap-3 mt-1">
                            <span class="text-xs <?php echo e($isOverdue ? 'text-amber-500 font-medium' : 'text-gray-400'); ?>">
                                ⏰ <?php echo e($reminder->remind_at->format('d M Y H:i')); ?>

                                <?php if($isOverdue): ?> · Terlambat <?php endif; ?>
                            </span>
                            <span class="text-xs text-gray-400">
                                <?php echo e($reminder->channel === 'email' ? '📧 Email' : '🔔 App'); ?>

                            </span>
                        </div>
                    </div>
                    <div class="flex gap-2 sm:shrink-0">
                        <?php if($reminder->status === 'pending'): ?>
                        <form method="POST" action="<?php echo e(route('reminders.done', $reminder)); ?>">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <button type="submit"
                                class="px-3 py-1.5 bg-green-600 hover:bg-green-500 text-white text-xs font-medium rounded-lg transition">
                                Selesai
                            </button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" action="<?php echo e(route('reminders.destroy', $reminder)); ?>"
                              onsubmit="return confirm('Hapus pengingat ini?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit"
                                class="px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 text-xs font-medium rounded-lg transition">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">
                <?php echo e($reminders->links()); ?>

            </div>
            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/reminders/index.blade.php ENDPATH**/ ?>