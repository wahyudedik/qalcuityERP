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
     <?php $__env->slot('header', null, []); ?> 📊 MRP Accuracy Dashboard <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('manufacturing.mrp')); ?>"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                ← Back to MRP
            </a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <div class="flex gap-2">
                    <button onclick="switchPeriod('7')" class="period-btn px-4 py-2 rounded-lg bg-blue-600 text-white"
                        data-period="7">7 Days</button>
                    <button onclick="switchPeriod('30')"
                        class="period-btn px-4 py-2 rounded-lg bg-gray-200" data-period="30">30
                        Days</button>
                    <button onclick="switchPeriod('90')"
                        class="period-btn px-4 py-2 rounded-lg bg-gray-200" data-period="90">90
                        Days</button>
                    <button onclick="switchPeriod('all')"
                        class="period-btn px-4 py-2 rounded-lg bg-gray-200" data-period="all">All
                        Time</button>
                </div>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="kpi-cards">
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="text-sm text-gray-500 mb-1">Accuracy Rate</div>
                    <div class="text-3xl font-bold text-green-600" id="accuracy-rate">
                        <?php echo e($dashboardData['last_30_days']['accuracy_rate']); ?>%
                    </div>
                    <div class="text-xs text-gray-400 mt-2">±5% tolerance</div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="text-sm text-gray-500 mb-1">Avg Variance</div>
                    <div class="text-3xl font-bold <?php echo e($dashboardData['last_30_days']['avg_variance_percent'] > 0 ? 'text-red-600' : 'text-green-600'); ?>"
                        id="avg-variance">
                        <?php echo e($dashboardData['last_30_days']['avg_variance_percent']); ?>%
                    </div>
                    <div class="text-xs text-gray-400 mt-2">Planned vs Actual</div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="text-sm text-gray-500 mb-1">Total Records</div>
                    <div class="text-3xl font-bold text-blue-600" id="total-records">
                        <?php echo e($dashboardData['last_30_days']['total_records']); ?>

                    </div>
                    <div class="text-xs text-gray-400 mt-2">Tracking entries</div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="text-sm text-gray-500 mb-1">Cost Variance</div>
                    <div class="text-3xl font-bold <?php echo e($dashboardData['last_30_days']['total_savings_loss'] > 0 ? 'text-red-600' : 'text-green-600'); ?>"
                        id="cost-variance">
                        Rp <?php echo e(number_format(abs($dashboardData['last_30_days']['total_savings_loss']), 0, ',', '.')); ?>

                    </div>
                    <div class="text-xs text-gray-400 mt-2">
                        <?php echo e($dashboardData['last_30_days']['total_savings_loss'] > 0 ? 'Over budget' : 'Under budget'); ?>

                    </div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">📈 Planned vs Actual Quantity</h3>
                    <canvas id="plannedVsActualChart" height="300"></canvas>
                </div>

                
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">📉 Variance Trend (%)</h3>
                    <canvas id="varianceChart" height="300"></canvas>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">💰 Planned vs Actual Cost</h3>
                <canvas id="costChart" height="100"></canvas>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">⚠️ Top 10 Products with Highest Variance
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Product</th>
                                <th class="px-4 py-3 text-right">Avg |Variance|</th>
                                <th class="px-4 py-3 text-right">Records</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__currentLoopData = $topVarianceProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-900 font-medium">
                                        <?php echo e($product->product_name); ?></td>
                                    <td
                                        class="px-4 py-3 text-right font-bold <?php echo e($product->avg_abs_variance > 10 ? 'text-red-600' : 'text-yellow-600'); ?>">
                                        <?php echo e(number_format($product->avg_abs_variance, 2)); ?>%
                                    </td>
                                    <td class="px-4 py-3 text-right"><?php echo e($product->record_count); ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if($product->avg_abs_variance > 15): ?>
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700">Critical</span>
                                        <?php elseif($product->avg_abs_variance > 10): ?>
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">Warning</span>
                                        <?php else: ?>
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">Acceptable</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">📋 Recent Tracking Records</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Work Order</th>
                                <th class="px-4 py-3 text-left">Product</th>
                                <th class="px-4 py-3 text-right">Planned</th>
                                <th class="px-4 py-3 text-right">Actual</th>
                                <th class="px-4 py-3 text-right">Variance</th>
                                <th class="px-4 py-3 text-right">Cost Var</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__currentLoopData = $recentRecords->take(20); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-700">
                                        <?php echo e($record->tracking_date->format('d M Y')); ?></td>
                                    <td class="px-4 py-3 text-gray-900">
                                        <?php echo e($record->workOrder?->number ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-gray-900 font-medium">
                                        <?php echo e($record->product?->name ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-right"><?php echo e(number_format($record->planned_quantity, 2)); ?>

                                    </td>
                                    <td class="px-4 py-3 text-right"><?php echo e(number_format($record->actual_quantity, 2)); ?>

                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-bold <?php echo e($record->variance_percent > 0 ? 'text-red-600' : 'text-green-600'); ?>">
                                        <?php echo e($record->variance_percent > 0 ? '+' : ''); ?><?php echo e(number_format($record->variance_percent, 2)); ?>%
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right <?php echo e($record->cost_variance > 0 ? 'text-red-600' : 'text-green-600'); ?>">
                                        Rp <?php echo e(number_format(abs($record->cost_variance), 0, ',', '.')); ?>

                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if(abs($record->variance_percent) <= 5): ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">✓
                                                Accurate</span>
                                        <?php elseif(abs($record->variance_percent) <= 10): ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">⚠
                                                Close</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700">✗
                                                Off</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Dashboard data from backend
        const dashboardData = <?php echo json_encode($dashboardData['last_30_days'], 15, 512) ?>;
        const allData = <?php echo json_encode($dashboardData, 15, 512) ?>;

        let currentPeriod = '30';
        let charts = {};

        // Initialize charts
        function initializeCharts() {
            const chartData = dashboardData.chart_data;

            // Planned vs Actual Chart
            charts.plannedVsActual = new Chart(document.getElementById('plannedVsActualChart'), {
                type: 'line',
                data: {
                    labels: chartData.labels.map(d => new Date(d).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short'
                    })),
                    datasets: [{
                            label: 'Planned',
                            data: chartData.planned,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Actual',
                            data: chartData.actual,
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
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
                            beginAtZero: true
                        }
                    }
                }
            });

            // Variance Chart
            charts.variance = new Chart(document.getElementById('varianceChart'), {
                type: 'bar',
                data: {
                    labels: chartData.labels.map(d => new Date(d).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short'
                    })),
                    datasets: [{
                        label: 'Variance %',
                        data: chartData.variance,
                        backgroundColor: chartData.variance.map(v => v > 5 ? 'rgba(239, 68, 68, 0.7)' : v <
                            -5 ? 'rgba(34, 197, 94, 0.7)' : 'rgba(59, 130, 246, 0.7)'),
                        borderColor: chartData.variance.map(v => v > 5 ? 'rgb(239, 68, 68)' : v < -5 ?
                            'rgb(34, 197, 94)' : 'rgb(59, 130, 246)'),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Variance %'
                            }
                        }
                    }
                }
            });

            // Cost Chart
            charts.cost = new Chart(document.getElementById('costChart'), {
                type: 'bar',
                data: {
                    labels: chartData.labels.map(d => new Date(d).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short'
                    })),
                    datasets: [{
                            label: 'Planned Cost',
                            data: chartData.planned.map((p, i) => p * 10000), // Example calculation
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        },
                        {
                            label: 'Actual Cost',
                            data: chartData.actual.map((a, i) => a * 10000), // Example calculation
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
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
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Switch period
        function switchPeriod(period) {
            currentPeriod = period;

            // Update button styles
            document.querySelectorAll('.period-btn').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-200');
            });
            event.target.classList.remove('bg-gray-200');
            event.target.classList.add('bg-blue-600', 'text-white');

            // Update KPI cards
            const data = period === 'all' ? allData.all_time : allData[`last_${period}_days`];
            if (data && data.total_records > 0) {
                document.getElementById('accuracy-rate').textContent = data.accuracy_rate + '%';
                document.getElementById('avg-variance').textContent = data.avg_variance_percent + '%';
                document.getElementById('total-records').textContent = data.total_records;

                const costVar = Math.abs(data.total_savings_loss);
                document.getElementById('cost-variance').textContent = 'Rp ' + costVar.toLocaleString('id-ID');
            }

            // TODO: Update charts with new period data (requires AJAX or page reload)
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', initializeCharts);
    </script>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\manufacturing\mrp-accuracy.blade.php ENDPATH**/ ?>