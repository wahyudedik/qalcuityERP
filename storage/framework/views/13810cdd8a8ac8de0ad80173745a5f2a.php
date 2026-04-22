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
     <?php $__env->slot('header', null, []); ?> Fraud Monitor — Affiliate Audit Log <?php $__env->endSlot(); ?>

    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-4">
            <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Total Log</p>
            <p class="text-2xl font-black text-white"><?php echo e($logs->total()); ?></p>
        </div>
        <div class="bg-[#1e293b] border border-red-500/30 rounded-2xl p-4">
            <p class="text-[10px] text-red-400 uppercase tracking-wider mb-1">🚨 Fraud Alerts</p>
            <p class="text-2xl font-black text-red-400"><?php echo e($fraudCount); ?></p>
        </div>
        <div class="bg-[#1e293b] border border-amber-500/30 rounded-2xl p-4">
            <p class="text-[10px] text-amber-400 uppercase tracking-wider mb-1">Warnings</p>
            <p class="text-2xl font-black text-amber-400"><?php echo e($warningCount); ?></p>
        </div>
    </div>

    <div class="flex gap-2 mb-4">
        <?php $__currentLoopData = ['' => 'Semua', 'info' => 'Info', 'warning' => 'Warning', 'fraud' => 'Fraud']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $c = match ($v) {
                    'fraud' => 'bg-red-600',
                    'warning' => 'bg-amber-600',
                    default => '',
                };
            ?>
            <a href="?severity=<?php echo e($v); ?>"
                class="px-3 py-1.5 text-xs rounded-xl <?php echo e(request('severity') === $v ? ($c ?: 'bg-blue-600') . ' text-white' : 'bg-white/5 border border-white/10 text-slate-300 hover:bg-white/10'); ?>"><?php echo e($l); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white/5 text-xs text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center">Severity</th>
                        <th class="px-4 py-3 text-left">Affiliate</th>
                        <th class="px-4 py-3 text-left">Event</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-left">IP</th>
                        <th class="px-4 py-3 text-center">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $sc = match ($log->severity) {
                                'fraud' => 'red',
                                'warning' => 'amber',
                                default => 'blue',
                            };
                            $icon = match ($log->severity) {
                                'fraud' => '!',
                                'warning' => '!',
                                default => 'i',
                            };
                        ?>
                        <tr class="hover:bg-white/5 <?php echo e($log->severity === 'fraud' ? 'bg-red-500/5' : ''); ?>">
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-<?php echo e($sc); ?>-500/20 text-<?php echo e($sc); ?>-400"><?php echo e($icon); ?>

                                    <?php echo e(strtoupper($log->severity)); ?></span>
                            </td>
                            <td class="px-4 py-3 text-white text-xs"><?php echo e($log->affiliate->user->name ?? '-'); ?></td>
                            <td class="px-4 py-3 text-slate-400 text-xs font-mono"><?php echo e($log->event); ?></td>
                            <td class="px-4 py-3 text-slate-300 text-xs"><?php echo e(Str::limit($log->description, 60)); ?></td>
                            <td class="px-4 py-3 text-slate-500 text-xs font-mono"><?php echo e($log->ip_address ?? '-'); ?></td>
                            <td class="px-4 py-3 text-center text-slate-500 text-xs">
                                <?php echo e($log->created_at->format('d/m H:i')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500">Belum ada audit log.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($logs->hasPages()): ?>
            <div class="px-4 py-3 border-t border-white/5"><?php echo e($logs->links()); ?></div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\super-admin\affiliates\audit-logs.blade.php ENDPATH**/ ?>