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
     <?php $__env->slot('header', null, []); ?> IoT Device Management <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('iot.devices.create')); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                <i class="fas fa-plus"></i> Tambah Device
            </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if(session('success')): ?>
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3">
                    <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm text-green-800"><?php echo e(session('success')); ?></p>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Device</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo e($devices->total()); ?></p>
                        </div>
                        <div class="text-4xl text-blue-100">
                            <i class="fas fa-microchip"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Online</p>
                            <p class="text-3xl font-bold text-green-600 mt-2"><?php echo e($devices->where('is_connected', true)->count()); ?></p>
                        </div>
                        <div class="text-4xl text-green-100">
                            <i class="fas fa-wifi"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Aktif / Offline</p>
                            <p class="text-3xl font-bold text-yellow-600 mt-2"><?php echo e($devices->where('is_active', true)->where('is_connected', false)->count()); ?></p>
                        </div>
                        <div class="text-4xl text-yellow-100">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Nonaktif</p>
                            <p class="text-3xl font-bold text-gray-600 mt-2"><?php echo e($devices->where('is_active', false)->count()); ?></p>
                        </div>
                        <div class="text-4xl text-gray-100">
                            <i class="fas fa-power-off"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Devices Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Device</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tipe</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Lokasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Module</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Terakhir Online</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Log</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php $__empty_1 = true; $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900"><?php echo e($device->name); ?></div>
                                    <div class="text-xs text-gray-500 font-mono"><?php echo e($device->device_id); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                        $icons = ['esp32'=>'🔌','arduino'=>'⚡','raspberry_pi'=>'🍓','generic'=>'📡'];
                                    ?>
                                    <span class="text-lg"><?php echo e($icons[$device->device_type] ?? '📡'); ?></span>
                                    <span class="text-sm text-gray-700"><?php echo e(\App\Models\IotDevice::deviceTypes()[$device->device_type] ?? $device->device_type); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo e($device->location ?? '-'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo e(\App\Models\IotDevice::targetModules()[$device->target_module] ?? $device->target_module); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if(!$device->is_active): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-circle text-gray-400 me-1"></i> Nonaktif
                                        </span>
                                    <?php elseif($device->is_connected): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-circle text-green-500 me-1"></i> Online
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-circle text-yellow-500 me-1"></i> Offline
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <?php echo e($device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Belum pernah'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo e(number_format($device->telemetry_logs_count)); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="<?php echo e(route('iot.devices.show', $device)); ?>" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-800 border border-blue-200 rounded-lg hover:bg-blue-50 transition">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-microchip text-4xl text-gray-300 mb-3"></i>
                                        <p class="text-gray-600 mb-2">Belum ada device IoT</p>
                                        <a href="<?php echo e(route('iot.devices.create')); ?>" class="text-blue-600 hover:text-blue-800 font-medium">Tambah sekarang</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <?php echo e($devices->links()); ?>

            </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\iot\devices\index.blade.php ENDPATH**/ ?>