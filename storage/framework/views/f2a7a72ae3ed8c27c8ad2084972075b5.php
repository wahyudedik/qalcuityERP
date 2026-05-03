

<?php $__env->startSection('title', 'Agriculture Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">🌾 Agriculture Dashboard</h1>
            <p class="mt-2 text-sm text-gray-600">Smart farming management system</p>
        </div>

        <!-- Weather Widget -->
        <?php if($weather): ?>
            <div class="bg-gradient-to-r from-blue-500 to-cyan-500 rounded-lg shadow-lg p-6 mb-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold"><?php echo e($weather->location_name ?? 'Farm Location'); ?></h2>
                        <p class="text-4xl font-bold mt-2"><?php echo e(round($weather->temperature)); ?>°C</p>
                        <p class="text-lg"><?php echo e(ucfirst($weather->weather_description)); ?></p>
                    </div>
                    <div class="text-right">
                        <div class="text-6xl">
                            <?php if(stripos($weather->weather_condition, 'rain') !== false): ?>
                                🌧️
                            <?php elseif(stripos($weather->weather_condition, 'cloud') !== false): ?>
                                ☁️
                            <?php else: ?>
                                ☀️
                            <?php endif; ?>
                        </div>
                        <p class="mt-2">Humidity: <?php echo e($weather->humidity); ?>%</p>
                        <p>Wind: <?php echo e($weather->wind_speed); ?> m/s</p>
                    </div>
                </div>

                <?php if(count($recommendations) > 0): ?>
                    <div class="mt-4 bg-white/20 rounded-lg p-4">
                        <h3 class="font-semibold mb-2">🌱 Farming Recommendations:</h3>
                        <ul class="space-y-1">
                            <?php $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-start">
                                    <span class="mr-2">
                                        <?php if($rec['type'] === 'warning'): ?>
                                            ⚠️
                                        <?php elseif($rec['type'] === 'alert'): ?>
                                            🔴
                                        <?php elseif($rec['type'] === 'success'): ?>
                                            ✅
                                        <?php else: ?>
                                            ℹ️
                                        <?php endif; ?>
                                    </span>
                                    <span><?php echo e($rec['message']); ?></span>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Active Crops -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                        <span class="text-2xl">🌾</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Crops</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?php echo e($activeCrops->count()); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Upcoming Irrigations -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                        <span class="text-2xl">💧</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Upcoming Irrigations</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?php echo e(count($upcomingIrrigations)); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Market Prices Tracked -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                        <span class="text-2xl">💰</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Commodities Tracked</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?php echo e(count($marketSummary)); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Weather Status -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                        <span class="text-2xl">🌤️</span>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Weather Status</dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                <?php if($weather): ?>
                                    <?php if($weather->isSuitableForFarming()): ?>
                                        <span class="text-green-600">✅ Suitable</span>
                                    <?php else: ?>
                                        <span class="text-red-600">⚠️ Not Ideal</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-gray-600">No Data</span>
                                <?php endif; ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Crops List -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Active Crop Cycles</h2>
            </div>
            <div class="p-6">
                <?php if($activeCrops->count() > 0): ?>
                    <div class="space-y-4">
                        <?php $__currentLoopData = $activeCrops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $crop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="border rounded-lg p-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="font-semibold text-gray-900"><?php echo e($crop->crop_name); ?></h3>
                                        <p class="text-sm text-gray-600"><?php echo e($crop->area_hectares); ?> hectares • Planted:
                                            <?php echo e($crop->planting_date->format('d M Y')); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                <?php if($crop->growth_stage === 'ready_to_harvest'): ?> bg-green-100 text-green-800
                                <?php elseif($crop->growth_stage === 'flowering'): ?> bg-yellow-100 text-yellow-800
                                <?php else: ?> bg-blue-100 text-blue-800 <?php endif; ?>">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $crop->growth_stage))); ?>

                                        </span>
                                        <p class="text-xs text-gray-500 mt-1">Day <?php echo e($crop->days_since_planted); ?></p>
                                    </div>
                                </div>

                                <?php if($crop->pest_detections_count > 0): ?>
                                    <div class="mt-2 text-sm text-orange-600">
                                        ⚠️ <?php echo e($crop->pest_detections_count); ?> pest detection(s)
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-8">No active crops. Start a new crop cycle to begin tracking.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="#" class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <span class="text-3xl mr-4">📸</span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Detect Pests</h3>
                        <p class="text-sm text-gray-600">Upload plant photo for AI analysis</p>
                    </div>
                </div>
            </a>

            <a href="#" class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <span class="text-3xl mr-4">💧</span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Manage Irrigation</h3>
                        <p class="text-sm text-gray-600">View and adjust schedules</p>
                    </div>
                </div>
            </a>

            <a href="#" class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <span class="text-3xl mr-4">💰</span>
                    <div>
                        <h3 class="font-semibold text-gray-900">Market Prices</h3>
                        <p class="text-sm text-gray-600">Check commodity prices & trends</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\agriculture\dashboard.blade.php ENDPATH**/ ?>