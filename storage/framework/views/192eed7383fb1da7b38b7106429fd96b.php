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
        <?php echo e(__('Telecom Monitoring Dashboard')); ?>

     <?php $__env->endSlot(); ?>

    <?php $__env->startPush('styles'); ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php $__env->stopPush(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Telecom Monitoring Dashboard')); ?>

                    </h1>
                    <p class="text-gray-600 mt-1"><?php echo e(__('Real-time network monitoring & analytics')); ?>

                    </p>
                </div>
                <div class="flex gap-2 items-center">
                    <a href="<?php echo e(route('telecom.maps')); ?>"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-map"></i>
                        <?php echo e(__('View Maps')); ?>

                    </a>
                    <button onclick="refreshDashboard()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-sync-alt"></i>
                        <?php echo e(__('Refresh')); ?>

                    </button>
                    <span id="lastUpdate" class="text-sm text-gray-500 flex items-center">
                        <?php echo e(__('Last updated')); ?>: <?php echo e(now()->format('H:i:s')); ?>

                    </span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Devices Stats -->
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600"><?php echo e(__('Total Devices')); ?></p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['total_devices']); ?>

                            </p>
                            <p class="text-xs text-green-600 mt-1">
                                <span class="font-semibold"><?php echo e($stats['online_devices']); ?></span> <?php echo e(__('online')); ?>

                            </p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-server text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600"><?php echo e(__('Subscriptions')); ?></p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php echo e($stats['active_subscriptions']); ?></p>
                            <p class="text-xs text-gray-600 mt-1">
                                <?php echo e(__('of')); ?> <?php echo e($stats['total_subscriptions']); ?> <?php echo e(__('total')); ?>

                            </p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-users text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600"><?php echo e(__('Hotspot Users')); ?></p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php echo e($stats['online_hotspot_users']); ?></p>
                            <p class="text-xs text-gray-600 mt-1">
                                <?php echo e(__('of')); ?> <?php echo e($stats['total_hotspot_users']); ?> <?php echo e(__('online')); ?>

                            </p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="fas fa-wifi text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 <?php echo e($stats['critical_alerts'] > 0 ? 'border-red-500' : 'border-yellow-500'); ?>">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600"><?php echo e(__('Alerts')); ?></p>
                            <p
                                class="text-2xl font-bold <?php echo e($stats['critical_alerts'] > 0 ? 'text-red-600' : 'text-gray-900'); ?>">
                                <?php echo e($stats['total_alerts']); ?>

                            </p>
                            <?php if($stats['critical_alerts'] > 0): ?>
                                <p class="text-xs text-red-600 mt-1 font-semibold">
                                    <?php echo e($stats['critical_alerts']); ?> <?php echo e(__('critical')); ?>

                                </p>
                            <?php else: ?>
                                <p class="text-xs text-gray-600 mt-1"><?php echo e(__('No critical alerts')); ?>

                                </p>
                            <?php endif; ?>
                        </div>
                        <div
                            class="<?php echo e($stats['critical_alerts'] > 0 ? 'bg-red-100' : 'bg-yellow-100'); ?> p-3 rounded-full">
                            <i
                                class="fas fa-bell <?php echo e($stats['critical_alerts'] > 0 ? 'text-red-600' : 'text-yellow-600'); ?> text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Summary -->
            <div
                class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-sm p-6 mb-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-green-100 text-sm">
                            <?php echo e(__('Monthly Revenue (Active Subscriptions)')); ?></p>
                        <p class="text-4xl font-bold mt-2"><?php echo e($revenueSummary['formatted_current']); ?></p>
                        <?php if($revenueSummary['growth_percent'] != 0): ?>
                            <p
                                class="text-sm mt-2 <?php echo e($revenueSummary['growth_percent'] > 0 ? 'text-green-200' : 'text-red-200'); ?>">
                                <?php echo e($revenueSummary['growth_percent'] > 0 ? '↑' : '↓'); ?>

                                <?php echo e(abs($revenueSummary['growth_percent'])); ?>% <?php echo e(__('from last month')); ?>

                                (<?php echo e($revenueSummary['formatted_last']); ?>)
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="bg-white bg-opacity-20 p-4 rounded-full">
                        <i class="fas fa-dollar-sign text-4xl"></i>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Bandwidth Usage Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <?php echo e(__('Bandwidth Usage (Last 24 Hours)')); ?></h3>
                    <canvas id="bandwidthChart" height="250"></canvas>
                </div>

                <!-- Device Status Distribution -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <?php echo e(__('Device Status Distribution')); ?></h3>
                    <canvas id="deviceStatusChart" height="250"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Subscription Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <?php echo e(__('Subscription Status')); ?></h3>
                    <canvas id="subscriptionStatusChart" height="250"></canvas>
                </div>

                <!-- Top Devices -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <?php echo e(__('Top Devices by Active Subscriptions')); ?></h3>
                    <div class="space-y-3">
                        <?php $__empty_1 = true; $__currentLoopData = $topDevices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div
                                class="flex items-center justify-between p-3 <?php echo e($index % 2 == 0 ? 'bg-gray-50' : 'bg-white'); ?> rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full <?php echo e($device['status'] === 'online' ? 'bg-green-100' : 'bg-red-100'); ?> flex items-center justify-center">
                                        <i
                                            class="fas fa-server <?php echo e($device['status'] === 'online' ? 'text-green-600' : 'text-red-600'); ?>"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900"><?php echo e($device['name']); ?>

                                        </p>
                                        <p class="text-xs text-gray-500"><?php echo e($device['ip_address']); ?>

                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-blue-600">
                                        <?php echo e($device['active_subscriptions']); ?> <?php echo e(__('subs')); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo e($device['hotspot_users']); ?>

                                        <?php echo e(__('users')); ?></p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-center text-gray-500 py-8"><?php echo e(__('No devices found')); ?>

                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Network Topology & Alerts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Network Topology -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo e(__('Network Topology')); ?>

                        </h3>
                        <a href="<?php echo e(route('telecom.maps')); ?>"
                            class="text-sm text-green-600 hover:text-green-800 flex items-center gap-1">
                            <i class="fas fa-map"></i>
                            <?php echo e(__('View on Map')); ?>

                        </a>
                    </div>
                    <div id="topologyContainer" class="border border-gray-200 rounded-lg p-4"
                        style="min-height: 400px;">
                        <?php if(count($topologyData['nodes']) > 0): ?>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                <?php $__currentLoopData = $topologyData['nodes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $node): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div
                                        class="border-2 <?php echo e($node['status'] === 'online' ? 'border-green-500 bg-green-50' : ($node['status'] === 'offline' ? 'border-red-500 bg-red-50' : 'border-yellow-500 bg-yellow-50')); ?> rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-2">
                                            <div
                                                class="w-3 h-3 rounded-full <?php echo e($node['status'] === 'online' ? 'bg-green-500' : ($node['status'] === 'offline' ? 'bg-red-500' : 'bg-yellow-500')); ?>">
                                            </div>
                                            <span
                                                class="font-semibold text-sm truncate text-gray-900"><?php echo e($node['label']); ?></span>
                                            <?php if($node['has_coordinates'] ?? false): ?>
                                                <i
                                                    class="fas fa-map-marker-alt text-green-600 flex-shrink-0"></i>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-xs text-gray-600">
                                            <?php echo e(ucfirst($node['type'])); ?></p>
                                        <p class="text-xs text-gray-500 font-mono">
                                            <?php echo e($node['ip']); ?></p>
                                        <?php if(isset($node['location']) && $node['location']): ?>
                                            <p class="text-xs text-gray-600 mt-1 truncate"
                                                title="<?php echo e($node['location']); ?>">
                                                <i class="fas fa-location-dot inline"></i>
                                                <?php echo e(Str::limit($node['location'], 20)); ?>

                                            </p>
                                        <?php endif; ?>
                                        <?php if(isset($node['parent'])): ?>
                                            <p class="text-xs text-blue-600 mt-1">↓
                                                <?php echo e(__('Child of')); ?> <?php echo e($node['parent']); ?></p>
                                        <?php endif; ?>

                                        <?php if($node['has_coordinates'] ?? false): ?>
                                            <a href="<?php echo e(route('telecom.maps')); ?>?device_id=<?php echo e($node['id']); ?>"
                                                class="mt-2 pt-2 border-t border-gray-200 text-xs text-green-600 hover:text-green-800 flex items-center gap-1 transition-colors">
                                                <i class="fas fa-map flex-shrink-0"></i>
                                                <span class="truncate"><?php echo e(__('View on Map')); ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center justify-center h-full text-gray-400">
                                <div class="text-center">
                                    <i class="fas fa-network-wired text-4xl mb-2"></i>
                                    <p class="text-sm"><?php echo e(__('No devices registered yet')); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Alerts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo e(__('Recent Alerts')); ?></h3>
                        <?php if($stats['total_alerts'] > 0): ?>
                            <span
                                class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full"><?php echo e($stats['total_alerts']); ?>

                                <?php echo e(__('new')); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <?php $__empty_1 = true; $__currentLoopData = $recentAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div
                                class="border-l-4 <?php echo e($alert->severity === 'critical' ? 'border-red-500' : ($alert->severity === 'high' ? 'border-orange-500' : ($alert->severity === 'medium' ? 'border-yellow-500' : 'border-blue-500'))); ?> pl-3 py-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">
                                            <?php echo e($alert->title); ?></p>
                                        <p class="text-xs text-gray-600 mt-1">
                                            <?php echo e(Str::limit($alert->message, 60)); ?></p>
                                        <?php if($alert->device): ?>
                                            <p class="text-xs text-blue-600 mt-1">
                                                <?php echo e($alert->device->name); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <span
                                        class="text-xs text-gray-500 whitespace-nowrap ml-2"><?php echo e($alert->created_at->diffForHumans()); ?></span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center py-8 text-gray-400">
                                <i class="fas fa-check-circle text-4xl mb-2"></i>
                                <p class="text-sm"><?php echo e(__('No alerts')); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            const bandwidthCtx = document.getElementById('bandwidthChart').getContext('2d');
            const deviceStatusCtx = document.getElementById('deviceStatusChart').getContext('2d');
            const subscriptionStatusCtx = document.getElementById('subscriptionStatusChart').getContext('2d');

            new Chart(bandwidthCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($bandwidthData['labels'], 15, 512) ?>,
                    datasets: [{
                            label: '<?php echo e(__('Download (MB)')); ?>',
                            data: <?php echo json_encode($bandwidthData['downloads'], 15, 512) ?>,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: '<?php echo e(__('Upload (MB)')); ?>',
                            data: <?php echo json_encode($bandwidthData['uploads'], 15, 512) ?>,
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

            new Chart(deviceStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($deviceStatusData['labels'], 15, 512) ?>,
                    datasets: [{
                        data: <?php echo json_encode($deviceStatusData['data'], 15, 512) ?>,
                        backgroundColor: [
                            'rgb(34, 197, 94)',
                            'rgb(239, 68, 68)',
                            'rgb(234, 179, 8)',
                            'rgb(156, 163, 175)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            new Chart(subscriptionStatusCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($subscriptionStatusData['labels'], 15, 512) ?>,
                    datasets: [{
                        data: <?php echo json_encode($subscriptionStatusData['data'], 15, 512) ?>,
                        backgroundColor: [
                            'rgb(34, 197, 94)',
                            'rgb(234, 179, 8)',
                            'rgb(156, 163, 175)',
                            'rgb(239, 68, 68)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            function refreshDashboard() {
                const now = new Date();
                document.getElementById('lastUpdate').textContent = '<?php echo e(__('Last updated')); ?>: ' + now.toLocaleTimeString();

                fetch('<?php echo e(route('telecom.dashboard.device-status')); ?>')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Device status updated:', data.devices.length);
                        }
                    })
                    .catch(error => console.error('Error refreshing:', error));
            }

            setInterval(refreshDashboard, 30000);
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\dashboard\index.blade.php ENDPATH**/ ?>