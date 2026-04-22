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
            <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-300 leading-tight">
                Room Status Map
            </h2>
            <a href="<?php echo e(route('hotel.dashboard')); ?>"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 dark:bg-slate-700 text-gray-800 dark:text-slate-300 rounded-lg hover:bg-gray-300 dark:hover:bg-slate-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Dashboard
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                <div
                    class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        <span class="text-sm text-gray-600 dark:text-slate-400">Available</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($statusCounts['available']); ?></p>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <span class="text-sm text-gray-600 dark:text-slate-400">Occupied</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($statusCounts['occupied']); ?></p>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                        <span class="text-sm text-gray-600 dark:text-slate-400">Dirty</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($statusCounts['dirty']); ?></p>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                        <span class="text-sm text-gray-600 dark:text-slate-400">Cleaning</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($statusCounts['cleaning']); ?></p>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-gray-400"></div>
                        <span class="text-sm text-gray-600 dark:text-slate-400">Clean</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($statusCounts['clean']); ?></p>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-3 rounded-full bg-gray-800 dark:bg-gray-600"></div>
                        <span class="text-sm text-gray-600 dark:text-slate-400">OOO</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($statusCounts['out_of_order']); ?></p>
                </div>
            </div>

            
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-4 mb-6">
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                            Filter by Floor
                        </label>
                        <select id="floorFilter"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white">
                            <option value="">All Floors</option>
                            <?php $__currentLoopData = $floors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $floor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($floor); ?>" <?php echo e(request('floor') == $floor ? 'selected' : ''); ?>>
                                    Floor <?php echo e($floor); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                            Filter by Status
                        </label>
                        <select id="statusFilter"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-slate-700 dark:text-white">
                            <option value="">All Status</option>
                            <option value="available" <?php echo e(request('status') == 'available' ? 'selected' : ''); ?>>
                                Available</option>
                            <option value="occupied" <?php echo e(request('status') == 'occupied' ? 'selected' : ''); ?>>Occupied
                            </option>
                            <option value="dirty" <?php echo e(request('status') == 'dirty' ? 'selected' : ''); ?>>Dirty</option>
                            <option value="cleaning" <?php echo e(request('status') == 'cleaning' ? 'selected' : ''); ?>>Cleaning
                            </option>
                            <option value="clean" <?php echo e(request('status') == 'clean' ? 'selected' : ''); ?>>Clean</option>
                            <option value="out_of_order" <?php echo e(request('status') == 'out_of_order' ? 'selected' : ''); ?>>
                                Out of Order</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button id="resetFilters"
                            class="px-4 py-2 bg-gray-200 dark:bg-slate-700 text-gray-800 dark:text-slate-300 rounded-lg hover:bg-gray-300 dark:hover:bg-slate-600 transition-colors">
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            
            <?php
                $groupedRooms = $rooms->groupBy('floor');
            ?>

            <?php $__currentLoopData = $groupedRooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $floor => $floorRooms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6 mb-6"
                    data-floor="<?php echo e($floor); ?>">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Floor <?php echo e($floor); ?>

                        </h3>
                        <span class="text-sm text-gray-500 dark:text-slate-400"><?php echo e($floorRooms->count()); ?> rooms</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3">
                        <?php $__currentLoopData = $floorRooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $statusColors = [
                                    'available' =>
                                        'bg-green-50 dark:bg-green-900/20 border-green-500 text-green-700 dark:text-green-300',
                                    'occupied' =>
                                        'bg-red-50 dark:bg-red-900/20 border-red-500 text-red-700 dark:text-red-300',
                                    'dirty' =>
                                        'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-500 text-yellow-700 dark:text-yellow-300',
                                    'cleaning' =>
                                        'bg-blue-50 dark:bg-blue-900/20 border-blue-500 text-blue-700 dark:text-blue-300',
                                    'clean' =>
                                        'bg-gray-50 dark:bg-slate-700 border-gray-400 text-gray-700 dark:text-gray-300',
                                    'out_of_order' =>
                                        'bg-gray-100 dark:bg-slate-800 border-gray-600 text-gray-800 dark:text-gray-400 opacity-60',
                                ];
                                $colorClass = $statusColors[$room->status] ?? 'bg-gray-50 border-gray-300';
                            ?>

                            <div class="relative group cursor-pointer" data-status="<?php echo e($room->status); ?>"
                                data-room-id="<?php echo e($room->id); ?>">
                                <div
                                    class="p-4 rounded-lg border-2 <?php echo e($colorClass); ?> transition-all hover:shadow-md hover:scale-105">
                                    <div class="text-center">
                                        <p class="text-lg font-bold mb-1"><?php echo e($room->number); ?></p>
                                        <p class="text-xs opacity-75 mb-2"><?php echo e($room->roomType->name); ?></p>

                                        <?php if($room->status === 'occupied' && $room->currentReservation): ?>
                                            <div class="mt-2 pt-2 border-t border-current border-opacity-30">
                                                <p class="text-xs truncate">
                                                    <?php echo e($room->currentReservation->guest->name ?? 'Guest'); ?>

                                                </p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if($room->status === 'available'): ?>
                                            <div class="mt-2 pt-2 border-t border-current border-opacity-30">
                                                <p class="text-xs font-medium">Available</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                
                                <div
                                    class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 dark:bg-slate-700 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10 shadow-lg">
                                    <p class="font-semibold">Room <?php echo e($room->number); ?></p>
                                    <p><?php echo e($room->roomType->name); ?></p>
                                    <p class="capitalize"><?php echo e(ucfirst(str_replace('_', ' ', $room->status))); ?></p>
                                    <?php if($room->status === 'occupied' && $room->currentReservation): ?>
                                        <p class="mt-1 pt-1 border-t border-gray-600">
                                            <?php echo e($room->currentReservation->guest->name ?? 'N/A'); ?>

                                        </p>
                                    <?php endif; ?>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2">
                                        <div class="w-2 h-2 bg-gray-900 dark:bg-slate-700 rotate-45 -mt-1"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Legend</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-green-500"></div>
                        <span class="text-sm text-gray-700 dark:text-slate-300">Available</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-red-500"></div>
                        <span class="text-sm text-gray-700 dark:text-slate-300">Occupied</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-yellow-500"></div>
                        <span class="text-sm text-gray-700 dark:text-slate-300">Dirty</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-blue-500"></div>
                        <span class="text-sm text-gray-700 dark:text-slate-300">Cleaning</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-gray-400"></div>
                        <span class="text-sm text-gray-700 dark:text-slate-300">Clean</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-gray-800 dark:bg-gray-600"></div>
                        <span class="text-sm text-gray-700 dark:text-slate-300">Out of Order</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const floorFilter = document.getElementById('floorFilter');
                const statusFilter = document.getElementById('statusFilter');
                const resetBtn = document.getElementById('resetFilters');

                function applyFilters() {
                    const floor = floorFilter.value;
                    const status = statusFilter.value;

                    // Update URL
                    const params = new URLSearchParams();
                    if (floor) params.set('floor', floor);
                    if (status) params.set('status', status);

                    const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                    window.history.pushState({}, '', url);

                    // Show/hide floors
                    document.querySelectorAll('[data-floor]').forEach(floorEl => {
                        if (floor && floorEl.dataset.floor !== floor) {
                            floorEl.style.display = 'none';
                        } else {
                            floorEl.style.display = 'block';
                        }
                    });

                    // Show/hide rooms
                    document.querySelectorAll('[data-room-id]').forEach(roomEl => {
                        const roomStatus = roomEl.dataset.status;
                        if (status && roomStatus !== status) {
                            roomEl.style.display = 'none';
                        } else {
                            roomEl.style.display = 'block';
                        }
                    });
                }

                floorFilter.addEventListener('change', applyFilters);
                statusFilter.addEventListener('change', applyFilters);

                resetBtn.addEventListener('click', function() {
                    floorFilter.value = '';
                    statusFilter.value = '';
                    window.history.pushState({}, '', window.location.pathname);

                    document.querySelectorAll('[data-floor]').forEach(el => el.style.display = 'block');
                    document.querySelectorAll('[data-room-id]').forEach(el => el.style.display = 'block');
                });
            });
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\room-changes\room-map.blade.php ENDPATH**/ ?>