

<?php $__env->startSection('title', 'Revenue Management Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Revenue Management</h1>
                <p class="text-gray-600">Optimize pricing and maximize revenue</p>
            </div>
            <div class="flex space-x-3">
                <a href="<?php echo e(route('revenue.rate-calendar')); ?>"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Rate Calendar
                </a>
                <a href="<?php echo e(route('revenue.yield-optimization')); ?>"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Yield Optimization
                </a>
            </div>
        </div>

        <!-- KPI Cards -->
        <?php if(isset($kpis) && !isset($kpis['message'])): ?>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Avg Occupancy (30d)</div>
                    <div class="text-2xl font-bold text-blue-600"><?php echo e(number_format($kpis['occupancy']['average'], 1)); ?>%
                    </div>
                    <div class="text-xs text-gray-500">Range: <?php echo e(number_format($kpis['occupancy']['lowest'], 1)); ?>% -
                        <?php echo e(number_format($kpis['occupancy']['highest'], 1)); ?>%</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Avg ADR (30d)</div>
                    <div class="text-2xl font-bold text-green-600">$<?php echo e(number_format($kpis['adr']['average'], 2)); ?></div>
                    <div class="text-xs text-gray-500">Range: $<?php echo e(number_format($kpis['adr']['lowest'], 2)); ?> -
                        $<?php echo e(number_format($kpis['adr']['highest'], 2)); ?></div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Avg RevPAR (30d)</div>
                    <div class="text-2xl font-bold text-purple-600">$<?php echo e(number_format($kpis['revpar']['average'], 2)); ?>

                    </div>
                    <div class="text-xs text-gray-500">Range: $<?php echo e(number_format($kpis['revpar']['lowest'], 2)); ?> -
                        $<?php echo e(number_format($kpis['revpar']['highest'], 2)); ?></div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Total Revenue (30d)</div>
                    <div class="text-2xl font-bold text-orange-600">$<?php echo e(number_format($kpis['revenue']['total'], 2)); ?>

                    </div>
                    <div class="text-xs text-gray-500">Avg Daily:
                        $<?php echo e(number_format($kpis['revenue']['average_daily'], 2)); ?></div>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Demand Indicators -->
                <?php if(isset($demandIndicators)): ?>
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-4 py-3 border-b flex justify-between items-center">
                            <h3 class="font-semibold text-gray-800">Demand Indicators (Next 30 Days)</h3>
                            <a href="<?php echo e(route('revenue.forecasts')); ?>" class="text-blue-600 text-sm hover:underline">View
                                All</a>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-3 gap-4 mb-4">
                                <div class="text-center p-3 bg-red-50 rounded">
                                    <div class="text-2xl font-bold text-red-600">
                                        <?php echo e($demandIndicators['high_demand_days']); ?></div>
                                    <div class="text-sm text-gray-600">High Demand Days</div>
                                </div>
                                <div class="text-center p-3 bg-yellow-50 rounded">
                                    <div class="text-2xl font-bold text-yellow-600">
                                        <?php echo e($demandIndicators['low_demand_days']); ?></div>
                                    <div class="text-sm text-gray-600">Low Demand Days</div>
                                </div>
                                <div class="text-center p-3 bg-blue-50 rounded">
                                    <div class="text-2xl font-bold text-blue-600">
                                        <?php echo e(number_format($demandIndicators['average_occupancy'], 1)); ?>%</div>
                                    <div class="text-sm text-gray-600">Avg Forecast</div>
                                </div>
                            </div>
                            <?php if(!empty($demandIndicators['recommendations'])): ?>
                                <?php $__currentLoopData = $demandIndicators['recommendations']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="p-3 bg-gray-50 rounded mb-2">
                                        <div class="flex items-center">
                                            <span
                                                class="px-2 py-1 text-xs rounded <?php echo e($rec['priority'] === 'high' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'); ?>">
                                                <?php echo e(strtoupper($rec['priority'])); ?>

                                            </span>
                                            <span class="ml-2 text-sm"><?php echo e($rec['message']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Forecast Chart -->
                <?php if(isset($forecasts) && $forecasts->count() > 0): ?>
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-4 py-3 border-b">
                            <h3 class="font-semibold text-gray-800">Occupancy Forecast</h3>
                        </div>
                        <div class="p-4">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-3 py-2 text-left">Date</th>
                                            <th class="px-3 py-2 text-center">Occupancy</th>
                                            <th class="px-3 py-2 text-center">Projected ADR</th>
                                            <th class="px-3 py-2 text-center">RevPAR</th>
                                            <th class="px-3 py-2 text-center">Confidence</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $forecasts->take(14); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $forecast): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="border-b hover:bg-gray-50">
                                                <td class="px-3 py-2"><?php echo e($forecast->forecast_date->format('M d, Y')); ?></td>
                                                <td class="px-3 py-2 text-center">
                                                    <span
                                                        class="px-2 py-1 rounded text-xs
                                            <?php echo e($forecast->projected_occupancy_rate >= 80
                                                ? 'bg-red-100 text-red-700'
                                                : ($forecast->projected_occupancy_rate >= 60
                                                    ? 'bg-green-100 text-green-700'
                                                    : 'bg-yellow-100 text-yellow-700')); ?>">
                                                        <?php echo e(number_format($forecast->projected_occupancy_rate, 1)); ?>%
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2 text-center">
                                                    $<?php echo e(number_format($forecast->projected_adr, 2)); ?></td>
                                                <td class="px-3 py-2 text-center">
                                                    $<?php echo e(number_format($forecast->projected_revpar, 2)); ?></td>
                                                <td class="px-3 py-2 text-center">
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="bg-blue-600 h-2 rounded-full"
                                                            style="width: <?php echo e($forecast->confidence_level); ?>%"></div>
                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-500"><?php echo e(number_format($forecast->confidence_level, 0)); ?>%</span>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Pending Recommendations -->
                <?php if(isset($recommendations) && $recommendations->count() > 0): ?>
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-4 py-3 border-b flex justify-between items-center">
                            <h3 class="font-semibold text-gray-800">Pricing Recommendations</h3>
                            <a href="<?php echo e(route('revenue.recommendations')); ?>"
                                class="text-blue-600 text-sm hover:underline">View All</a>
                        </div>
                        <div class="p-4 space-y-3">
                            <?php $__currentLoopData = $recommendations->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="p-3 border rounded hover:bg-gray-50">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="font-medium"><?php echo e($rec->roomType?->name ?? 'Unknown'); ?></div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo e($rec->recommendation_date->format('M d, Y')); ?></div>
                                        </div>
                                        <span
                                            class="px-2 py-1 text-xs rounded <?php echo e($rec->suggested_change_percentage > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'); ?>">
                                            <?php echo e($rec->suggested_change_percentage > 0 ? '+' : ''); ?><?php echo e(number_format($rec->suggested_change_percentage, 1)); ?>%
                                        </span>
                                    </div>
                                    <div class="mt-2 text-sm">
                                        <span class="text-gray-500">$<?php echo e(number_format($rec->current_rate, 2)); ?></span>
                                        <span class="mx-1">→</span>
                                        <span class="font-medium">$<?php echo e(number_format($rec->recommended_rate, 2)); ?></span>
                                    </div>
                                    <div class="mt-2 flex space-x-2">
                                        <form action="<?php echo e(route('revenue.recommendations.apply', $rec)); ?>" method="POST"
                                            class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit"
                                                class="text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">Apply</button>
                                        </form>
                                        <form action="<?php echo e(route('revenue.recommendations.reject', $rec)); ?>" method="POST"
                                            class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit"
                                                class="text-xs bg-gray-500 text-white px-2 py-1 rounded hover:bg-gray-600">Reject</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Rate Alerts -->
                <?php if(isset($rateAlerts) && $rateAlerts['alert_count'] > 0): ?>
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-4 py-3 border-b">
                            <h3 class="font-semibold text-gray-800">Competitor Alerts</h3>
                        </div>
                        <div class="p-4">
                            <div class="text-sm text-gray-600 mb-3">
                                <?php echo e($rateAlerts['alert_count']); ?> significant rate changes detected
                            </div>
                            <?php $__currentLoopData = $rateAlerts['alerts']->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="p-2 bg-gray-50 rounded mb-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="font-medium"><?php echo e($alert['competitor']); ?></span>
                                        <span
                                            class="text-xs text-gray-500"><?php echo e($alert['detected_at']->diffForHumans()); ?></span>
                                    </div>
                                    <div class="text-gray-600">
                                        Rate <?php echo e($alert['change_percentage'] > 0 ? 'increased' : 'decreased'); ?>

                                        by <?php echo e(number_format(abs($alert['change_percentage']), 1)); ?>%
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('revenue.competitor-rates')); ?>"
                                class="text-blue-600 text-sm hover:underline">View all alerts</a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Quick Links -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-4 py-3 border-b">
                        <h3 class="font-semibold text-gray-800">Quick Links</h3>
                    </div>
                    <div class="p-4 space-y-2">
                        <a href="<?php echo e(route('revenue.rate-plans')); ?>" class="block p-2 hover:bg-gray-50 rounded">
                            <div class="font-medium">Rate Plans</div>
                            <div class="text-sm text-gray-500">Manage pricing strategies</div>
                        </a>
                        <a href="<?php echo e(route('revenue.pricing-rules')); ?>" class="block p-2 hover:bg-gray-50 rounded">
                            <div class="font-medium">Pricing Rules</div>
                            <div class="text-sm text-gray-500">Configure dynamic pricing</div>
                        </a>
                        <a href="<?php echo e(route('revenue.special-events')); ?>" class="block p-2 hover:bg-gray-50 rounded">
                            <div class="font-medium">Special Events</div>
                            <div class="text-sm text-gray-500">Manage event-based pricing</div>
                        </a>
                        <a href="<?php echo e(route('revenue.reports')); ?>" class="block p-2 hover:bg-gray-50 rounded">
                            <div class="font-medium">Reports</div>
                            <div class="text-sm text-gray-500">View detailed analytics</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\revenue\dashboard.blade.php ENDPATH**/ ?>