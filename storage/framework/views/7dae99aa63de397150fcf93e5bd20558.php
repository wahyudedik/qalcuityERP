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
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center justify-between">
            <span><?php echo e($report['supplier']->name); ?> - Performance Report</span>
            <a href="<?php echo e(route('suppliers.scorecards.index')); ?>"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($report['supplier']->name); ?></h2>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1"><?php echo e($report['supplier']->code ?? ''); ?> |
                    <?php echo e($report['supplier']->email ?? ''); ?></p>
            </div>
            <?php
                $ratingColors = ['A' => 'green', 'B' => 'blue', 'C' => 'yellow', 'D' => 'orange', 'F' => 'red'];
                $color = $ratingColors[$report['current_rating']] ?? 'gray';
            ?>
            <div class="text-right">
                <span
                    class="inline-block px-4 py-2 text-2xl font-bold rounded-full bg-<?php echo e($color); ?>-100 text-<?php echo e($color); ?>-700 dark:bg-<?php echo e($color); ?>-500/20 dark:text-<?php echo e($color); ?>-400">
                    <?php echo e($report['current_rating']); ?>

                </span>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-2">Current Rating</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100 dark:border-white/5">
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Overall Score</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">
                    <?php echo e(number_format($report['current_score'], 1)); ?>/100</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Trend</p>
                <p
                    class="text-lg font-medium <?php echo e($report['trend'] === 'improving' ? 'text-green-600 dark:text-green-400' : ($report['trend'] === 'declining' ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400')); ?>">
                    <?php if($report['trend'] === 'improving'): ?>
                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        Improving
                    <?php elseif($report['trend'] === 'declining'): ?>
                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                        Declining
                    <?php else: ?>
                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
                        </svg>
                        Stable
                    <?php endif; ?>
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Total Incidents</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($report['total_incidents']); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Scorecards</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e(count($report['scorecards'])); ?></p>
            </div>
        </div>
    </div>

    
    <?php if(count($report['scorecards']) > 1): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Performance Trend
                (<?php echo e(count($report['scorecards'])); ?> Months)</h2>

            <div class="h-64 flex items-end gap-2">
                <?php
                    $maxScore = $report['scorecards']->max('overall_score') ?: 100;
                ?>
                <?php $__currentLoopData = $report['scorecards']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scorecard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $height = ($scorecard->overall_score / $maxScore) * 100;
                        $ratingColor = $ratingColors[$scorecard->rating] ?? 'gray';
                    ?>
                    <div class="flex-1 min-w-[40px] flex flex-col items-center group">
                        <div class="w-full bg-<?php echo e($ratingColor); ?>-500 hover:bg-<?php echo e($ratingColor); ?>-600 rounded-t transition-all relative"
                            style="height: <?php echo e(max($height, 5)); ?>px">
                            <div
                                class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-10">
                                <?php echo e(number_format($scorecard->overall_score, 1)); ?> - <?php echo e($scorecard->rating); ?>

                            </div>
                        </div>
                        <span class="text-[10px] text-gray-500 dark:text-slate-400 mt-2 rotate-45 origin-left">
                            <?php echo e($scorecard->period_end->format('M/y')); ?>

                        </span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    
    <div
        class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Score History</h2>
        </div>

        <?php if(count($report['scorecards']) === 0): ?>
            <div class="p-12 text-center">
                <svg class="mx-auto w-16 h-16 text-gray-300 dark:text-slate-600 mb-3" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada scorecard untuk supplier ini.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-[#0f172a]">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Period</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Overall</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Rating</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Quality</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Delivery</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Cost</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Service</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        <?php $__currentLoopData = $report['scorecards']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scorecard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#0f172a] transition">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-white">
                                    <?php echo e($scorecard->period_end->format('M Y')); ?>

                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 w-20">
                                            <div class="bg-<?php echo e($ratingColors[$scorecard->rating] ?? 'gray'); ?>-600 h-2 rounded-full"
                                                style="width: <?php echo e($scorecard->overall_score); ?>%"></div>
                                        </div>
                                        <span
                                            class="font-bold"><?php echo e(number_format($scorecard->overall_score, 1)); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 text-xs font-bold rounded-full bg-<?php echo e($ratingColors[$scorecard->rating] ?? 'gray'); ?>-100 text-<?php echo e($ratingColors[$scorecard->rating] ?? 'gray'); ?>-700 dark:bg-<?php echo e($ratingColors[$scorecard->rating] ?? 'gray'); ?>-500/20 dark:text-<?php echo e($ratingColors[$scorecard->rating] ?? 'gray'); ?>-400">
                                        <?php echo e($scorecard->rating); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    <?php echo e(number_format($scorecard->quality_score, 1)); ?></td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    <?php echo e(number_format($scorecard->delivery_score, 1)); ?></td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    <?php echo e(number_format($scorecard->cost_score, 1)); ?></td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    <?php echo e(number_format($scorecard->service_score, 1)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    
    <?php if(count($report['recent_incidents']) > 0): ?>
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-red-200 dark:border-red-500/30 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Recent Incidents (<?php echo e(count($report['recent_incidents'])); ?>)
                </h3>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-white/5">
                <?php $__currentLoopData = $report['recent_incidents']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-[#0f172a] transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-full bg-<?php echo e($incident->severity_color); ?>-100 text-<?php echo e($incident->severity_color); ?>-700 dark:bg-<?php echo e($incident->severity_color); ?>-500/20 dark:text-<?php echo e($incident->severity_color); ?>-400">
                                        <?php echo e(ucfirst($incident->severity)); ?>

                                    </span>
                                    <span
                                        class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($incident->incident_type); ?></span>
                                </div>
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <?php echo e(Str::limit($incident->description, 150)); ?></p>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                    Reported <?php echo e($incident->reported_at->diffForHumans()); ?>

                                    <?php if($incident->financial_impact > 0): ?>
                                        | Impact: Rp <?php echo e(number_format($incident->financial_impact, 0, ',', '.')); ?>

                                    <?php endif; ?>
                                </p>
                            </div>
                            <span
                                class="px-2 py-1 text-xs rounded-full <?php echo e($incident->status === 'resolved' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400'); ?>">
                                <?php echo e(ucfirst($incident->status)); ?>

                            </span>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(
        $report['scorecards']->last() &&
            ($report['scorecards']->last()->strengths || $report['scorecards']->last()->areas_for_improvement)): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Assessment Notes</h2>

            <?php if($report['scorecards']->last()->strengths): ?>
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-green-600 dark:text-green-400 mb-2">Strengths</h4>
                    <p class="text-sm text-gray-700 dark:text-slate-300">
                        <?php echo e($report['scorecards']->last()->strengths); ?>

                    </p>
                </div>
            <?php endif; ?>

            <?php if($report['scorecards']->last()->areas_for_improvement): ?>
                <div>
                    <h4 class="text-sm font-medium text-orange-600 dark:text-orange-400 mb-2">Areas for Improvement
                    </h4>
                    <p class="text-sm text-gray-700 dark:text-slate-300">
                        <?php echo e($report['scorecards']->last()->areas_for_improvement); ?></p>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\suppliers\scorecard-detail.blade.php ENDPATH**/ ?>