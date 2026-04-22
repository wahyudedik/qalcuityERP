

<?php $__env->startSection('title', 'Advanced Analytics Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Advanced Analytics Dashboard</h1>
                    <p class="mt-2 text-sm text-gray-600">Real-time KPI tracking dan business intelligence</p>
                </div>
                <div class="flex gap-3">
                    <a href="<?php echo e(route('analytics.predictive')); ?>"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-brain mr-2"></i>AI Predictions
                    </a>
                    <a href="<?php echo e(route('analytics.report-builder')); ?>"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-file-export mr-2"></i>Custom Report
                    </a>
                    <a href="<?php echo e(route('analytics.scheduled-reports')); ?>"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-clock mr-2"></i>Scheduled
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="GET" action="<?php echo e(route('analytics.advanced')); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" name="start_date" value="<?php echo e($startDate); ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" name="end_date" value="<?php echo e($endDate); ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Module</label>
                    <select name="module"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="all" <?php echo e($module == 'all' ? 'selected' : ''); ?>>All Modules</option>
                        <option value="sales" <?php echo e($module == 'sales' ? 'selected' : ''); ?>>Sales</option>
                        <option value="inventory" <?php echo e($module == 'inventory' ? 'selected' : ''); ?>>Inventory</option>
                        <option value="finance" <?php echo e($module == 'finance' ? 'selected' : ''); ?>>Finance</option>
                        <option value="crm" <?php echo e($module == 'crm' ? 'selected' : ''); ?>>CRM</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-filter mr-2"></i>Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- KPI Cards - Revenue -->
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Revenue Metrics</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-500">Daily Revenue</span>
                        <i class="fas fa-calendar-day text-green-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">
                        Rp <?php echo e(number_format($kpis['revenue']['daily'], 0, ',', '.')); ?>

                    </div>
                    <div class="mt-2 text-sm <?php echo e($kpis['revenue']['growth'] >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                        <i class="fas fa-<?php echo e($kpis['revenue']['growth'] >= 0 ? 'arrow-up' : 'arrow-down'); ?>"></i>
                        <?php echo e(number_format(abs($kpis['revenue']['growth']), 1)); ?>% vs previous period
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-500">Weekly Revenue</span>
                        <i class="fas fa-calendar-week text-blue-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">
                        Rp <?php echo e(number_format($kpis['revenue']['weekly'], 0, ',', '.')); ?>

                    </div>
                    <div class="mt-2 text-sm text-gray-500">Last 7 days</div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-500">Monthly Revenue</span>
                        <i class="fas fa-calendar-alt text-purple-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">
                        Rp <?php echo e(number_format($kpis['revenue']['monthly'], 0, ',', '.')); ?>

                    </div>
                    <div class="mt-2 text-sm text-gray-500">Last 30 days</div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-500">Growth Rate</span>
                        <i class="fas fa-chart-line text-indigo-500"></i>
                    </div>
                    <div
                        class="text-2xl font-bold <?php echo e($kpis['revenue']['growth'] >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                        <?php echo e(number_format($kpis['revenue']['growth'], 1)); ?>%
                    </div>
                    <div class="mt-2 text-sm text-gray-500">Period over period</div>
                </div>
            </div>
        </div>

        <!-- KPI Cards - Orders -->
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Order Metrics</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-500">Total Orders</span>
                        <i class="fas fa-shopping-cart text-blue-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">
                        <?php echo e(number_format($kpis['orders']['total'])); ?>

                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-500">Completed</span>
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-green-600">
                        <?php echo e(number_format($kpis['orders']['completed'])); ?>

                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-500">Conversion Rate</span>
                        <i class="fas fa-percentage text-purple-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">
                        <?php echo e(number_format($kpis['orders']['conversion_rate'], 1)); ?>%
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-500">Avg Order Value</span>
                        <i class="fas fa-money-bill-wave text-orange-500"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900">
                        Rp <?php echo e(number_format($kpis['orders']['avg_value'], 0, ',', '.')); ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Cards - Inventory -->
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Inventory Metrics</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500 mb-2">Total Products</div>
                    <div class="text-2xl font-bold text-gray-900">
                        <?php echo e(number_format($kpis['inventory']['total_products'])); ?>

                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500 mb-2">In Stock</div>
                    <div class="text-2xl font-bold text-green-600">
                        <?php echo e(number_format($kpis['inventory']['in_stock'])); ?>

                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500 mb-2">Low Stock</div>
                    <div class="text-2xl font-bold text-yellow-600">
                        <?php echo e(number_format($kpis['inventory']['low_stock'])); ?>

                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500 mb-2">Out of Stock</div>
                    <div class="text-2xl font-bold text-red-600">
                        <?php echo e(number_format($kpis['inventory']['out_of_stock'])); ?>

                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-sm font-medium text-gray-500 mb-2">Turnover Rate</div>
                    <div class="text-2xl font-bold text-gray-900">
                        <?php echo e(number_format($kpis['inventory']['turnover_rate'], 2)); ?>x
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Cards - Customers -->
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Customer Metrics</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <div class="text-sm font-medium text-gray-500 mb-2">Total Customers</div>
                    <div class="text-2xl font-bold text-gray-900">
                        <?php echo e(number_format($kpis['customers']['total'])); ?>

                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <div class="text-sm font-medium text-gray-500 mb-2">New This Month</div>
                    <div class="text-2xl font-bold text-green-600">
                        +<?php echo e(number_format($kpis['customers']['new_this_month'])); ?>

                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                    <div class="text-sm font-medium text-gray-500 mb-2">Active Customers</div>
                    <div class="text-2xl font-bold text-purple-600">
                        <?php echo e(number_format($kpis['customers']['active'])); ?>

                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
                    <div class="text-sm font-medium text-gray-500 mb-2">Retention Rate</div>
                    <div class="text-2xl font-bold text-indigo-600">
                        <?php echo e(number_format($kpis['customers']['retention_rate'], 1)); ?>%
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Revenue Trend Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Trend</h3>
                <div id="revenueChart" style="height: 300px;"></div>
            </div>

            <!-- Orders Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Orders Over Time</h3>
                <div id="ordersChart" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Top Metrics Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Top Products -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Products by Revenue</h3>
                <div class="space-y-3">
                    <?php $__currentLoopData = $topMetrics['top_products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-lg font-bold text-gray-400">#<?php echo e($index + 1); ?></span>
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo e($item->product->name ?? 'Unknown'); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e(number_format($item->total_qty)); ?> units</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-gray-900">Rp
                                    <?php echo e(number_format($item->total_revenue, 0, ',', '.')); ?></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Top Customers -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Customers</h3>
                <div class="space-y-3">
                    <?php $__currentLoopData = $topMetrics['top_customers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-lg font-bold text-gray-400">#<?php echo e($index + 1); ?></span>
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo e($customer->name); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e($customer->email ?? 'No email'); ?></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-gray-900">Rp
                                    <?php echo e(number_format($customer->total_spent ?? 0, 0, ',', '.')); ?></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Top Categories -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Categories</h3>
                <div class="space-y-3">
                    <?php $__currentLoopData = $topMetrics['top_categories']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-lg font-bold text-gray-400">#<?php echo e($index + 1); ?></span>
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo e($category->category ?? 'Uncategorized'); ?>

                                    </div>
                                    <div class="text-sm text-gray-500"><?php echo e(number_format($category->sales_count)); ?> sales
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-gray-900">Rp
                                    <?php echo e(number_format($category->total_revenue, 0, ',', '.')); ?></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue Trend Data
            const revenueData = <?php echo json_encode($revenueTrend, 15, 512) ?>;

            if (revenueData && revenueData.length > 0) {
                // Revenue Chart
                const revenueOptions = {
                    chart: {
                        type: 'area',
                        height: 300,
                        toolbar: {
                            show: true
                        },
                        animations: {
                            enabled: true
                        }
                    },
                    series: [{
                        name: 'Revenue',
                        data: revenueData.map(item => item.revenue)
                    }],
                    xaxis: {
                        categories: revenueData.map(item => item.date),
                        labels: {
                            rotate: -45,
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            }
                        }
                    },
                    colors: ['#10B981'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.7,
                            opacityTo: 0.3
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                };

                const revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
                revenueChart.render();

                // Orders Chart
                const ordersOptions = {
                    chart: {
                        type: 'bar',
                        height: 300,
                        toolbar: {
                            show: true
                        }
                    },
                    series: [{
                        name: 'Orders',
                        data: revenueData.map(item => item.orders)
                    }],
                    xaxis: {
                        categories: revenueData.map(item => item.date),
                        labels: {
                            rotate: -45,
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    colors: ['#3B82F6'],
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            columnWidth: '70%'
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return value + ' orders';
                            }
                        }
                    }
                };

                const ordersChart = new ApexCharts(document.querySelector("#ordersChart"), ordersOptions);
                ordersChart.render();
            }
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\advanced-dashboard.blade.php ENDPATH**/ ?>