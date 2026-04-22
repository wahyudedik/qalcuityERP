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
     <?php $__env->slot('title', null, []); ?> AI Model Monitor — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> AI Model Monitor <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
        <div class="mb-4 px-4 py-3 bg-green-500/20 border border-green-500/30 text-green-400 text-sm rounded-xl">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="mb-4 px-4 py-3 bg-red-500/20 border border-red-500/30 text-red-400 text-sm rounded-xl">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    
    <?php
        $activeAvailability = collect($modelAvailability)->firstWhere('model', $activeModel);
        $activeReason = $activeAvailability['reason'] ?? null;
        $activeAvailable = $activeAvailability['available'] ?? true;

        if (!$activeAvailable && $activeReason === 'quota_exceeded') {
            $statusLabel = 'Quota Exceeded';
            $statusColor = 'text-red-400 bg-red-500/15 border-red-500/30';
            $dotColor = 'bg-red-400';
        } elseif (!$activeAvailable && $activeReason === 'rate_limit') {
            $statusLabel = 'Rate Limited';
            $statusColor = 'text-yellow-400 bg-yellow-500/15 border-yellow-500/30';
            $dotColor = 'bg-yellow-400';
        } else {
            $statusLabel = 'Available';
            $statusColor = 'text-green-400 bg-green-500/15 border-green-500/30';
            $dotColor = 'bg-green-400';
        }
    ?>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="sm:col-span-2 bg-[#1e293b] border border-white/10 rounded-2xl p-5 flex items-center gap-5">
            <div class="w-12 h-12 rounded-xl bg-blue-500/15 border border-blue-500/30 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Model Aktif Saat Ini</p>
                <p class="text-xl font-bold text-slate-100 font-mono truncate"><?php echo e($activeModel); ?></p>
            </div>
            <div class="shrink-0">
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full border <?php echo e($statusColor); ?>">
                    <span class="w-1.5 h-1.5 rounded-full <?php echo e($dotColor); ?>"></span>
                    <?php echo e($statusLabel); ?>

                </span>
            </div>
        </div>

        <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-5 flex flex-col justify-between">
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Force Reset</p>
                <p class="text-xs text-slate-400 leading-relaxed">Reset semua cooldown model AI secara manual.</p>
            </div>
            <form method="POST" action="<?php echo e(route('super-admin.ai-model.reset')); ?>"
                onsubmit="return confirm('Reset semua cooldown model AI? Tindakan ini tidak dapat dibatalkan.')">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="mt-4 w-full px-4 py-2.5 text-sm font-semibold bg-red-500/15 hover:bg-red-500/25 text-red-400 border border-red-500/30 rounded-xl transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Force Reset Semua
                </button>
            </form>
        </div>
    </div>

    
    <div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-white/10 flex items-center justify-between">
            <p class="text-sm font-semibold text-slate-200">Status Availability Model</p>
            <span class="text-xs text-slate-500"><?php echo e(count($modelAvailability)); ?> model dalam fallback chain</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10 bg-white/5">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Model</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">Alasan</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Estimasi Recovery</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $modelAvailability; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            if ($item['available']) {
                                $rowStatus = 'Available';
                                $rowStatusClass = 'text-green-400 bg-green-500/15';
                                $rowDot = 'bg-green-400';
                            } elseif ($item['reason'] === 'quota_exceeded') {
                                $rowStatus = 'Quota Exceeded';
                                $rowStatusClass = 'text-red-400 bg-red-500/15';
                                $rowDot = 'bg-red-400';
                            } else {
                                $rowStatus = 'Rate Limited';
                                $rowStatusClass = 'text-yellow-400 bg-yellow-500/15';
                                $rowDot = 'bg-yellow-400';
                            }
                        ?>
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-slate-200 text-xs"><?php echo e($item['model']); ?></span>
                                    <?php if($item['model'] === $activeModel): ?>
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30">AKTIF</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full <?php echo e($rowStatusClass); ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?php echo e($rowDot); ?>"></span>
                                    <?php echo e($rowStatus); ?>

                                </span>
                            </td>
                            <td class="px-5 py-3.5 hidden md:table-cell">
                                <?php if($item['reason']): ?>
                                    <span class="text-xs text-slate-400 font-mono"><?php echo e($item['reason']); ?></span>
                                <?php else: ?>
                                    <span class="text-xs text-slate-600">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3.5 hidden lg:table-cell">
                                <?php if($item['recovers_at']): ?>
                                    <div>
                                        <p class="text-xs text-slate-300"><?php echo e($item['recovers_at']->format('d M Y H:i:s')); ?></p>
                                        <p class="text-[10px] text-slate-500 mt-0.5"><?php echo e($item['recovers_at']->diffForHumans()); ?></p>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs text-slate-600">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-500 text-sm">
                                Tidak ada model dalam fallback chain.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/10 flex items-center justify-between">
            <p class="text-sm font-semibold text-slate-200">Riwayat Switch Event</p>
            <span class="text-xs text-slate-500"><?php echo e($switchLogs->total()); ?> total event</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-white/10 bg-white/5">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Dari Model</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Ke Model</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">Alasan</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Waktu Switch</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden xl:table-cell">Tenant ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $switchLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $reasonColors = [
                                'rate_limit'          => 'text-yellow-400 bg-yellow-500/15',
                                'quota_exceeded'      => 'text-red-400 bg-red-500/15',
                                'service_unavailable' => 'text-orange-400 bg-orange-500/15',
                                'recovery'            => 'text-green-400 bg-green-500/15',
                            ];
                            $reasonColor = $reasonColors[$log->reason] ?? 'text-slate-400 bg-white/10';
                        ?>
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-slate-300 text-xs"><?php echo e($log->from_model); ?></span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3 h-3 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                    <span class="font-mono text-slate-200 text-xs"><?php echo e($log->to_model); ?></span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 hidden md:table-cell">
                                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full <?php echo e($reasonColor); ?>">
                                    <?php echo e(str_replace('_', ' ', ucfirst($log->reason))); ?>

                                </span>
                            </td>
                            <td class="px-5 py-3.5 hidden lg:table-cell">
                                <p class="text-xs text-slate-300"><?php echo e($log->switched_at->format('d M Y')); ?></p>
                                <p class="text-[10px] text-slate-500 mt-0.5"><?php echo e($log->switched_at->format('H:i:s')); ?></p>
                            </td>
                            <td class="px-5 py-3.5 hidden xl:table-cell">
                                <?php if($log->triggered_by_tenant_id): ?>
                                    <span class="text-xs font-mono text-slate-400"><?php echo e($log->triggered_by_tenant_id); ?></span>
                                <?php else: ?>
                                    <span class="text-xs text-slate-600">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-500 text-sm">
                                Belum ada switch event yang tercatat.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($switchLogs->hasPages()): ?>
            <div class="px-6 py-4 border-t border-white/10">
                <?php echo e($switchLogs->links()); ?>

            </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\super-admin\ai-model\index.blade.php ENDPATH**/ ?>