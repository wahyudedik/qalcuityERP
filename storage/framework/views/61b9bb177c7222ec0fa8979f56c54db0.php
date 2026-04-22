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
     <?php $__env->slot('header', null, []); ?> <?php echo e($deferredItem->typeLabel()); ?>: <?php echo e($deferredItem->number); ?> <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
    <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl text-sm text-red-700 dark:text-red-400"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <div class="lg:col-span-2 bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e($deferredItem->description); ?></h2>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-1"><?php echo e($deferredItem->start_date->format('d M Y')); ?> – <?php echo e($deferredItem->end_date->format('d M Y')); ?> · <?php echo e($deferredItem->total_periods); ?> bulan</p>
                </div>
                <?php if($deferredItem->isActive()): ?>
                <form method="POST" action="<?php echo e(route('deferred.cancel', $deferredItem)); ?>" onsubmit="return confirm('Batalkan item ini?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <button type="submit" class="px-3 py-1.5 text-xs border border-red-200 dark:border-red-500/30 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10">Batalkan</button>
                </form>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
                <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Total</p>
                    <p class="font-semibold text-gray-900 dark:text-white mt-0.5">Rp <?php echo e(number_format($deferredItem->total_amount,0,',','.')); ?></p>
                </div>
                <div class="bg-green-50 dark:bg-green-500/10 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Diakui</p>
                    <p class="font-semibold text-green-700 dark:text-green-400 mt-0.5">Rp <?php echo e(number_format($deferredItem->recognized_amount,0,',','.')); ?></p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-500/10 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Sisa</p>
                    <p class="font-semibold text-blue-700 dark:text-blue-400 mt-0.5">Rp <?php echo e(number_format($deferredItem->remaining_amount,0,',','.')); ?></p>
                </div>
                <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Progress</p>
                    <p class="font-semibold text-gray-900 dark:text-white mt-0.5"><?php echo e($deferredItem->recognized_periods); ?>/<?php echo e($deferredItem->total_periods); ?></p>
                </div>
            </div>

            
            <div class="mb-4">
                <div class="flex justify-between text-xs text-gray-500 dark:text-slate-400 mb-1">
                    <span>Progress Amortisasi</span>
                    <span><?php echo e($deferredItem->progressPercent()); ?>%</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-white/10 rounded-full h-3">
                    <div class="bg-blue-500 h-3 rounded-full transition-all" style="width:<?php echo e($deferredItem->progressPercent()); ?>%"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-gray-500 dark:text-slate-400">Akun Deferred:</span>
                    <span class="ml-1 text-gray-900 dark:text-white"><?php echo e($deferredItem->deferredAccount->code); ?> - <?php echo e($deferredItem->deferredAccount->name); ?></span>
                </div>
                <div>
                    <span class="text-gray-500 dark:text-slate-400">Akun Pengakuan:</span>
                    <span class="ml-1 text-gray-900 dark:text-white"><?php echo e($deferredItem->recognitionAccount->code); ?> - <?php echo e($deferredItem->recognitionAccount->name); ?></span>
                </div>
                <?php if($deferredItem->reference_number): ?>
                <div>
                    <span class="text-gray-500 dark:text-slate-400">Referensi:</span>
                    <span class="ml-1 text-gray-900 dark:text-white"><?php echo e($deferredItem->reference_number); ?></span>
                </div>
                <?php endif; ?>
                <div>
                    <span class="text-gray-500 dark:text-slate-400">Dibuat oleh:</span>
                    <span class="ml-1 text-gray-900 dark:text-white"><?php echo e($deferredItem->user->name); ?></span>
                </div>
            </div>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Status</h3>
            <?php
                $statusColor = match($deferredItem->status) {
                    'active'    => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                    'completed' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                    default     => 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400',
                };
            ?>
            <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo e($statusColor); ?>"><?php echo e(ucfirst($deferredItem->status)); ?></span>

            <?php $nextPending = $deferredItem->schedules->where('status', 'pending')->first(); ?>
            <?php if($nextPending): ?>
            <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-500/10 rounded-xl border border-amber-200 dark:border-amber-500/20">
                <p class="text-xs font-medium text-amber-700 dark:text-amber-400">Jadwal Berikutnya</p>
                <p class="text-sm font-semibold text-amber-800 dark:text-amber-300 mt-1"><?php echo e($nextPending->recognition_date->format('d M Y')); ?></p>
                <p class="text-xs text-amber-600 dark:text-amber-400">Rp <?php echo e(number_format($nextPending->amount,0,',','.')); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/5">
            <h3 class="font-semibold text-gray-900 dark:text-white">Jadwal Amortisasi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center">Periode</th>
                        <th class="px-4 py-3 text-left">Tanggal Pengakuan</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Jurnal</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__currentLoopData = $deferredItem->schedules->sortBy('period_number'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $sc = match($schedule->status) {
                            'posted'  => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                            'skipped' => 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400',
                            default   => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                        };
                        $isDue = $schedule->isPending() && $schedule->recognition_date->lte(today());
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 <?php echo e($isDue ? 'bg-amber-50/50 dark:bg-amber-500/5' : ''); ?>">
                        <td class="px-4 py-3 text-center font-medium text-gray-900 dark:text-white"><?php echo e($schedule->period_number); ?></td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300"><?php echo e($schedule->recognition_date->format('d M Y')); ?></td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp <?php echo e(number_format($schedule->amount,0,',','.')); ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($sc); ?>"><?php echo e(ucfirst($schedule->status)); ?></span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if($schedule->journalEntry): ?>
                            <a href="<?php echo e(route('journals.show', $schedule->journalEntry)); ?>" class="text-xs text-blue-600 dark:text-blue-400 hover:underline"><?php echo e($schedule->journalEntry->number); ?></a>
                            <?php else: ?>
                            <span class="text-xs text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if($schedule->isPending() && $deferredItem->isActive()): ?>
                            <form method="POST" action="<?php echo e(route('deferred.schedule.post', $schedule)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="px-2 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">Post Jurnal</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\deferred\show.blade.php ENDPATH**/ ?>