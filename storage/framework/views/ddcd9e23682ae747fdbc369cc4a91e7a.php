

<?php $__env->startSection('title', 'Analytics Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Analytics & Insights Dashboard</h1>
            <p class="mt-2 text-sm text-gray-600">Real-time business intelligence dan predictive analytics</p>
        </div>

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Today's Revenue</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900">Rp
                    <?php echo e(number_format($quickStats['today_revenue'], 0, ',', '.')); ?></dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">MTD Revenue</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900">Rp
                    <?php echo e(number_format($quickStats['mtd_revenue'], 0, ',', '.')); ?></dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Total Customers</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900"><?php echo e(number_format($quickStats['total_customers'])); ?>

                </dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Active Products</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900"><?php echo e(number_format($quickStats['active_products'])); ?>

                </dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Outstanding Invoices</dt>
                <dd class="mt-1 text-2xl font-semibold text-red-600">Rp
                    <?php echo e(number_format($quickStats['outstanding_invoices'], 0, ',', '.')); ?></dd>
            </div>
        </div>

        <!-- Business Health Score - Hero Card -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-lg p-8 mb-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Business Health Score</h2>
                    <p class="text-blue-100">Composite score dari 6 metrik bisnis utama</p>
                </div>
                <div class="text-right">
                    <div class="text-6xl font-bold"><?php echo e($healthScore['overall_score']); ?></div>
                    <div class="text-3xl font-semibold mt-2">Grade: <?php echo e($healthScore['grade']); ?></div>
                </div>
            </div>

            <!-- Component Breakdown -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mt-6 pt-6 border-t border-blue-400">
                <?php $__currentLoopData = $healthScore['components']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $component): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div>
                        <div class="text-xs text-blue-200 uppercase"><?php echo e(str_replace('_', ' ', $key)); ?></div>
                        <div class="text-xl font-semibold"><?php echo e($component['score']); ?>/100</div>
                        <div class="text-xs text-blue-200">Weight: <?php echo e($component['weight']); ?>%</div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php if(!empty($healthScore['recommendations'])): ?>
                <div class="mt-6 pt-6 border-t border-blue-400">
                    <h3 class="text-sm font-semibold mb-2">Recommendations:</h3>
                    <ul class="space-y-1">
                        <?php $__currentLoopData = $healthScore['recommendations']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="text-sm text-blue-100 flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <?php echo e($rec); ?>

                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <!-- Analytics Modules Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Customer Segmentation -->
            <a href="<?php echo e(route('analytics.customer-segmentation')); ?>"
                class="bg-white rounded-lg shadow hover:shadow-lg transition p-6 block">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Customer Segmentation</h3>
                <p class="text-sm text-gray-600">RFM analysis dengan 10 customer segments</p>
            </a>

            <!-- Product Profitability -->
            <a href="<?php echo e(route('analytics.product-profitability')); ?>"
                class="bg-white rounded-lg shadow hover:shadow-lg transition p-6 block">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Product Profitability</h3>
                <p class="text-sm text-gray-600">Matrix 4 kuadran: Stars, Cash Cows, Question Marks, Dogs</p>
            </a>

            <!-- Employee Performance -->
            <a href="<?php echo e(route('analytics.employee-performance')); ?>"
                class="bg-white rounded-lg shadow hover:shadow-lg transition p-6 block">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Employee Performance</h3>
                <p class="text-sm text-gray-600">Leaderboard dengan performance score & ranking</p>
            </a>

            <!-- Cashflow Forecast -->
            <a href="<?php echo e(route('analytics.cashflow-forecast')); ?>"
                class="bg-white rounded-lg shadow hover:shadow-lg transition p-6 block">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Cashflow Forecast</h3>
                <p class="text-sm text-gray-600">Prediksi arus kas 30/60/90 hari ke depan</p>
            </a>

            <!-- Churn Risk -->
            <a href="<?php echo e(route('analytics.churn-risk')); ?>"
                class="bg-white rounded-lg shadow hover:shadow-lg transition p-6 block">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Churn Risk Prediction</h3>
                <p class="text-sm text-gray-600">Identifikasi customer berisiko churn</p>
            </a>

            <!-- Seasonal Trends -->
            <a href="<?php echo e(route('analytics.seasonal-trends')); ?>"
                class="bg-white rounded-lg shadow hover:shadow-lg transition p-6 block">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Seasonal Trends</h3>
                <p class="text-sm text-gray-600">Analisis tren musiman & YoY comparison</p>
            </a>
        </div>

        <!-- Last Updated -->
        <div class="text-center text-sm text-gray-500">
            Last updated: <?php echo e(now()->format('d M Y H:i:s')); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\dashboard.blade.php ENDPATH**/ ?>