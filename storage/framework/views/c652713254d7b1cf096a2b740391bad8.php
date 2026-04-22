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
        <div class="flex items-center gap-2 text-sm">
            <a href="<?php echo e(route('supplier-performance.dashboard')); ?>" class="text-gray-400 dark:text-slate-500 hover:text-blue-500 transition-colors">
                Supplier Performance
            </a>
            <span class="text-gray-300 dark:text-slate-600">/</span>
            <span class="text-gray-600 dark:text-slate-300 font-medium truncate"><?php echo e($supplier->name); ?></span>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100 truncate">
                        <?php echo e($supplier->name); ?>

                    </h1>
                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                        <?php if($supplier->company): ?>
                            <span class="text-sm text-gray-500 dark:text-slate-400"><?php echo e($supplier->company); ?></span>
                        <?php endif; ?>
                        <?php if($supplier->email): ?>
                            <span class="text-gray-300 dark:text-slate-600">·</span>
                            <span class="text-sm text-gray-400 dark:text-slate-500"><?php echo e($supplier->email); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <form method="GET" action="<?php echo e(request()->url()); ?>">
                        <select name="period" onchange="this.form.submit()"
                            class="text-sm border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="30" <?php echo e($period == 30 ? 'selected' : ''); ?>>30 Hari</option>
                            <option value="90" <?php echo e($period == 90 ? 'selected' : ''); ?>>90 Hari</option>
                            <option value="180" <?php echo e($period == 180 ? 'selected' : ''); ?>>6 Bulan</option>
                        </select>
                    </form>
                    <a href="<?php echo e(route('supplier-performance.dashboard')); ?>"
                        class="inline-flex items-center gap-1.5 text-sm bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-700 dark:text-gray-200 px-3 py-1.5 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <div class="text-sm text-gray-500 dark:text-slate-400 mb-1">Current Grade</div>
                    <div
                        class="text-4xl font-bold <?php echo e(str_starts_with($performance['current_grade'], 'A') ? 'text-green-600' : 'text-blue-600'); ?>">
                        <?php echo e($performance['current_grade']); ?>

                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <div class="text-sm text-gray-500 dark:text-slate-400 mb-1">Overall Score</div>
                    <div class="text-3xl font-bold text-purple-600">
                        <?php echo e(number_format($performance['avg_overall_score'], 1)); ?></div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <div class="text-sm text-gray-500 dark:text-slate-400 mb-1">On-Time Rate</div>
                    <div
                        class="text-3xl font-bold <?php echo e($performance['on_time_delivery_rate'] >= 90 ? 'text-green-600' : 'text-red-600'); ?>">
                        <?php echo e($performance['on_time_delivery_rate']); ?>%
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <div class="text-sm text-gray-500 dark:text-slate-400 mb-1">Quality Rate</div>
                    <div class="text-3xl font-bold text-blue-600">
                        <?php echo e(number_format($performance['avg_quality_rate'], 1)); ?>%</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <div class="text-sm text-gray-500 dark:text-slate-400 mb-1">Total Evaluations</div>
                    <div class="text-3xl font-bold text-orange-600"><?php echo e($performance['total_evaluations']); ?></div>
                </div>
            </div>

            
            <?php if($performance['chart_data']['labels']): ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Performance Trends</h3>
                    <canvas id="performanceChart" height="100"></canvas>
                </div>
            <?php endif; ?>

            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Score Breakdown</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium">Delivery (30%)</span>
                                <span
                                    class="text-sm font-bold"><?php echo e(number_format($performance['avg_delivery_score'], 1)); ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-blue-600 h-3 rounded-full"
                                    style="width: <?php echo e($performance['avg_delivery_score']); ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium">Quality (35%)</span>
                                <span
                                    class="text-sm font-bold"><?php echo e(number_format($performance['avg_quality_score'], 1)); ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-green-600 h-3 rounded-full"
                                    style="width: <?php echo e($performance['avg_quality_score']); ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium">Cost (20%)</span>
                                <span
                                    class="text-sm font-bold"><?php echo e(number_format($performance['avg_cost_score'], 1)); ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-purple-600 h-3 rounded-full"
                                    style="width: <?php echo e($performance['avg_cost_score']); ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Purchase Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total POs Evaluated:</span>
                            <span class="font-bold"><?php echo e($performance['total_pos']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total PO Value:</span>
                            <span class="font-bold">Rp
                                <?php echo e(number_format($performance['total_po_value'], 0, ',', '.')); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Trend:</span>
                            <?php if($performance['trend'] === 'improving'): ?>
                                <span class="text-green-600 font-bold">Improving</span>
                            <?php elseif($performance['trend'] === 'declining'): ?>
                                <span class="text-red-600 font-bold">Declining</span>
                            <?php else: ?>
                                <span class="text-gray-600">Stable</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Evaluation History</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">PO Number</th>
                                <th class="px-4 py-3 text-center">Grade</th>
                                <th class="px-4 py-3 text-right">Score</th>
                                <th class="px-4 py-3 text-right">Delivery</th>
                                <th class="px-4 py-3 text-right">Quality</th>
                                <th class="px-4 py-3 text-right">Cost</th>
                                <th class="px-4 py-3 text-center">On-Time</th>
                                <th class="px-4 py-3 text-left">Evaluated By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <?php $__empty_1 = true; $__currentLoopData = $evaluations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-3"><?php echo e($eval->evaluation_date->format('d M Y')); ?></td>
                                    <td class="px-4 py-3">
                                        <?php if($eval->purchaseOrder): ?>
                                            <a href="#"
                                                class="text-blue-600 hover:underline"><?php echo e($eval->purchaseOrder->number); ?></a>
                                        <?php else: ?>
                                            <span class="text-gray-400">Manual</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php
                                            $gradeColor = match (str_split($eval->rating_grade)[0]) {
                                                'A' => 'bg-green-100 text-green-700',
                                                'B' => 'bg-blue-100 text-blue-700',
                                                'C' => 'bg-yellow-100 text-yellow-700',
                                                'D' => 'bg-orange-100 text-orange-700',
                                                default => 'bg-red-100 text-red-700',
                                            };
                                        ?>
                                        <span class="px-2 py-1 rounded-full text-xs font-bold <?php echo e($gradeColor); ?>">
                                            <?php echo e($eval->rating_grade); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold">
                                        <?php echo e(number_format($eval->overall_score, 1)); ?></td>
                                    <td class="px-4 py-3 text-right"><?php echo e(number_format($eval->delivery_score, 1)); ?></td>
                                    <td class="px-4 py-3 text-right"><?php echo e(number_format($eval->quality_score, 1)); ?></td>
                                    <td class="px-4 py-3 text-right"><?php echo e(number_format($eval->cost_score, 1)); ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if($eval->on_time_delivery): ?>
                                            <span class="text-green-600">✓ Yes</span>
                                        <?php else: ?>
                                            <span class="text-red-600">✗ No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3"><?php echo e($eval->evaluatedBy?->name ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                        No evaluations recorded yet
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <?php echo e($evaluations->links()); ?>

                </div>
            </div>

        </div>
    </div>

    <?php if($performance['chart_data']['labels']): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            const chartData = <?php echo json_encode($performance['chart_data'], 15, 512) ?>;

            new Chart(document.getElementById('performanceChart'), {
                type: 'line',
                data: {
                    labels: chartData.labels.map(d => new Date(d).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short'
                    })),
                    datasets: [{
                            label: 'Overall Score',
                            data: chartData.overall,
                            borderColor: 'rgb(147, 51, 234)',
                            backgroundColor: 'rgba(147, 51, 234, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Delivery',
                            data: chartData.delivery,
                            borderColor: 'rgb(59, 130, 246)',
                            tension: 0.4
                        },
                        {
                            label: 'Quality',
                            data: chartData.quality,
                            borderColor: 'rgb(16, 185, 129)',
                            tension: 0.4
                        },
                        {
                            label: 'Cost',
                            data: chartData.cost,
                            borderColor: 'rgb(249, 115, 22)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Score (0-100)'
                            }
                        }
                    }
                }
            });
        </script>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\procurement\supplier-performance-detail.blade.php ENDPATH**/ ?>