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
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Quality Control Dashboard</h2>
            <div class="flex gap-2">
                <a href="<?php echo e(route('manufacturing.quality.checks.create')); ?>"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                    + New Quality Check
                </a>
                <a href="<?php echo e(route('manufacturing.quality.defects')); ?>"
                    class="px-4 py-2 text-sm bg-orange-600 text-white rounded-xl hover:bg-orange-700">
                    View Defects
                </a>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="max-w-7xl mx-auto">
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            <?php echo e($statistics['quality_checks']['total']); ?></p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Total QC Checks</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                            <?php echo e(number_format($statistics['quality_checks']['pass_rate'], 1)); ?>%</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Pass Rate</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                            <?php echo e($statistics['defects']['open']); ?></p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Open Defects</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">Rp
                            <?php echo e(number_format($statistics['defects']['total_cost_impact'], 0, ',', '.')); ?></p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Cost Impact</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            
            <div
                class="lg:col-span-2 bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">QC Trend (Last 7 Days)</h3>
                <canvas id="qcTrendChart" style="max-height: 300px;"></canvas>
            </div>

            
            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">QC by Stage</h3>
                <div class="space-y-3">
                    <?php
                        $stages = [
                            'pre_production' => 'Pre-Production',
                            'in_process' => 'In-Process',
                            'post_production' => 'Post-Production',
                            'final' => 'Final',
                        ];
                    ?>
                    <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-white/5 rounded-lg">
                            <span class="text-sm text-gray-700 dark:text-slate-300"><?php echo e($label); ?></span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                <?php echo e($qc_by_stage[$key]->count ?? 0); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Quality Checks</h3>
                <div class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $recent_checks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-lg">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        <?php echo e($check->check_number); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-slate-400">
                                        <?php echo e($check->product?->name ?? 'N/A'); ?></p>
                                </div>
                                <?php
                                    $statusColor = match ($check->status) {
                                        'passed'
                                            => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                                        'failed' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                                        'conditional_pass'
                                            => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400',
                                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-400',
                                    };
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo e($statusColor); ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $check->status))); ?>

                                </span>
                            </div>
                            <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-slate-400">
                                <span>Pass Rate: <?php echo e(number_format($check->pass_rate, 1)); ?>%</span>
                                <span>•</span>
                                <span><?php echo e($check->inspected_at?->diffForHumans() ?? 'Pending'); ?></span>
                            </div>
                            <?php if(in_array($check->status, ['passed', 'conditional_pass'])): ?>
                                <a href="<?php echo e(route('manufacturing.quality.coa', $check)); ?>"
                                    class="mt-2 inline-block text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                    View COA →
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-500 dark:text-slate-400 text-center py-8">No quality checks yet</p>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Open CAPAs</h3>
                <div class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $open_capas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $capa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div
                            class="p-4 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-500/10 dark:to-red-500/10 rounded-lg border border-orange-200 dark:border-orange-500/20">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($capa->defect_code); ?></p>
                            <p class="text-xs text-gray-600 dark:text-slate-300 mt-1"><?php echo e($capa->product?->name); ?></p>
                            <div class="flex items-center gap-2 mt-2">
                                <?php
                                    $severityColor = match ($capa->severity) {
                                        'critical' => 'bg-red-600',
                                        'major' => 'bg-orange-600',
                                        default => 'bg-yellow-600',
                                    };
                                ?>
                                <span class="px-2 py-0.5 text-xs text-white rounded <?php echo e($severityColor); ?>">
                                    <?php echo e(ucfirst($capa->severity)); ?>

                                </span>
                                <span class="text-xs text-gray-500 dark:text-slate-400">
                                    <?php echo e($capa->quantity_defected); ?> units
                                </span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-500 dark:text-slate-400 text-center py-8">No open CAPAs</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // QC Trend Chart
            const trendData = <?php echo json_encode($trend_data, 15, 512) ?>;

            if (trendData.length > 0) {
                const ctx = document.getElementById('qcTrendChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: trendData.map(d => new Date(d.date).toLocaleDateString()),
                        datasets: [{
                                label: 'Passed',
                                data: trendData.map(d => parseInt(d.passed)),
                                borderColor: 'rgb(34, 197, 94)',
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                tension: 0.4,
                                fill: true,
                            },
                            {
                                label: 'Failed',
                                data: trendData.map(d => parseInt(d.failed)),
                                borderColor: 'rgb(239, 68, 68)',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                tension: 0.4,
                                fill: true,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\manufacturing\quality\dashboard-enhanced.blade.php ENDPATH**/ ?>