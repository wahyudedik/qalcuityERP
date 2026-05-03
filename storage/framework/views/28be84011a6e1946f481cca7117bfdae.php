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
     <?php $__env->slot('header', null, []); ?> Hotel Dashboard <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Occupancy Rate</p>
            <div class="flex items-center gap-3 mt-2">
                <div class="relative w-12 h-12">
                    <svg class="w-12 h-12 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-gray-200" stroke="currentColor" stroke-width="3"
                            fill="none"
                            d="M18 2.0845a 15.9155 15.9155 0 0 1 0 31.831a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path
                            class="<?php echo e($occupancyRate >= 80 ? 'text-green-500' : ($occupancyRate >= 50 ? 'text-blue-500' : 'text-amber-500')); ?>"
                            stroke="currentColor" stroke-width="3" fill="none"
                            stroke-dasharray="<?php echo e($occupancyRate); ?>, 100"
                            d="M18 2.0845a 15.9155 15.9155 0 0 1 0 31.831a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <span
                        class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-900">
                        <?php echo e($occupancyRate); ?>%
                    </span>
                </div>
                <div>
                    <p class="text-lg font-bold text-gray-900"><?php echo e($occupiedRooms); ?>/<?php echo e($totalRooms); ?>

                    </p>
                    <p class="text-xs text-gray-500">rooms occupied</p>
                </div>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Today's Arrivals</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e($expectedArrivals->count()); ?></p>
            <a href="<?php echo e(route('hotel.reservations.index', ['status' => 'confirmed', 'date' => today()->toDateString()])); ?>"
                class="text-xs text-blue-500 hover:underline">View reservations</a>
        </div>

        
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Today's Departures</p>
            <p class="text-2xl font-bold text-amber-600 mt-1"><?php echo e($expectedDepartures->count()); ?></p>
            <span class="text-xs text-gray-500">check-outs expected</span>
        </div>

        
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Revenue (This Month)</p>
            <p class="text-2xl font-bold text-green-600 mt-1">
                Rp <?php echo e(number_format($monthlyRevenue, 0, ',', '.')); ?>

            </p>
            <span class="text-xs text-gray-500"><?php echo e(now()->format('F Y')); ?></span>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        
        <div
            class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Room Status Overview</h3>
            <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                <?php
                    $statusConfig = [
                        'available' => [
                            'label' => 'Available',
                            'color' => 'bg-green-500',
                            'text' => 'text-green-600',
                        ],
                        'occupied' => [
                            'label' => 'Occupied',
                            'color' => 'bg-red-500',
                            'text' => 'text-red-600',
                        ],
                        'cleaning' => [
                            'label' => 'Cleaning',
                            'color' => 'bg-yellow-500',
                            'text' => 'text-yellow-600',
                        ],
                        'maintenance' => [
                            'label' => 'Maintenance',
                            'color' => 'bg-orange-500',
                            'text' => 'text-orange-600',
                        ],
                        'out_of_order' => [
                            'label' => 'Blocked',
                            'color' => 'bg-gray-500',
                            'text' => 'text-gray-600',
                        ],
                    ];
                ?>
                <?php $__currentLoopData = $statusConfig; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="text-center">
                        <div
                            class="inline-flex items-center justify-center w-12 h-12 rounded-xl <?php echo e($config['color']); ?> bg-opacity-20 mb-2">
                            <span class="text-xl font-bold <?php echo e($config['text']); ?>">
                                <?php echo e($roomStatusSummary[$status] ?? 0); ?>

                            </span>
                        </div>
                        <p class="text-xs text-gray-600"><?php echo e($config['label']); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Housekeeping Tasks</h3>
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-amber-500/20 flex items-center justify-center">
                    <span
                        class="text-2xl font-bold text-amber-600"><?php echo e($pendingHousekeeping); ?></span>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Pending tasks</p>
                    <a href="<?php echo e(route('hotel.housekeeping.room-board')); ?>"
                        class="text-sm text-blue-500 hover:underline">View board</a>
                </div>
            </div>
            <?php if($expectedDepartures->count() > 0): ?>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-2">Rooms to clean after checkout:</p>
                    <div class="flex flex-wrap gap-1">
                        <?php $__currentLoopData = $expectedDepartures->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($dep->room): ?>
                                <span
                                    class="px-2 py-0.5 text-xs bg-gray-100 rounded-full text-gray-700">
                                    <?php echo e($dep->room->number); ?>

                                </span>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($expectedDepartures->count() > 5): ?>
                            <span class="px-2 py-0.5 text-xs text-gray-500">+<?php echo e($expectedDepartures->count() - 5); ?>

                                more</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="<?php echo e(route('hotel.reservations.create')); ?>"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Reservation
        </a>
        <a href="<?php echo e(route('hotel.rooms.availability')); ?>"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Room Availability
        </a>
        <a href="<?php echo e(route('hotel.housekeeping.room-board')); ?>"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m4-4h1m-1 4h1" />
            </svg>
            Housekeeping Board
        </a>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">Recent Reservations</h3>
            <a href="<?php echo e(route('hotel.reservations.index')); ?>" class="text-sm text-blue-500 hover:underline">View
                all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Reservation #</th>
                        <th class="px-4 py-3 text-left">Guest</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Room Type</th>
                        <th class="px-4 py-3 text-left">Check-in</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Check-out</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Source</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $recentReservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="<?php echo e(route('hotel.reservations.show', $res)); ?>"
                                    class="font-medium text-blue-600 hover:underline">
                                    <?php echo e($res->reservation_number ?? '#' . $res->id); ?>

                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900"><?php echo e($res->guest?->name ?? '-'); ?>

                                </p>
                                <p class="text-xs text-gray-500"><?php echo e($res->guest?->phone ?? ''); ?>

                                </p>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-600">
                                <?php echo e($res->roomType?->name ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <?php echo e(\Carbon\Carbon::parse($res->check_in_date)->format('d M')); ?>

                            </td>
                            <td class="px-4 py-3 hidden md:table-cell text-gray-600">
                                <?php echo e(\Carbon\Carbon::parse($res->check_out_date)->format('d M')); ?>

                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php
                                    $statusColors = [
                                        'pending' => 'bg-gray-100 text-gray-600',
                                        'confirmed' =>
                                            'bg-blue-100 text-blue-700',
                                        'checked_in' =>
                                            'bg-green-100 text-green-700',
                                        'checked_out' =>
                                            'bg-gray-100 text-gray-600',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Pending',
                                        'confirmed' => 'Confirmed',
                                        'checked_in' => 'Checked In',
                                        'checked_out' => 'Checked Out',
                                        'cancelled' => 'Cancelled',
                                    ];
                                ?>
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs <?php echo e($statusColors[$res->status] ?? $statusColors['pending']); ?>">
                                    <?php echo e($statusLabels[$res->status] ?? $res->status); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell text-gray-500 capitalize">
                                <?php echo e($res->source ?? 'Direct'); ?>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                No reservations yet. <a href="<?php echo e(route('hotel.reservations.create')); ?>"
                                    class="text-blue-500 hover:underline">Create the first one</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            // Toast notification for flash messages
            function showToast(message, type = 'success') {
                const colors = {
                    success: 'bg-green-600',
                    error: 'bg-red-600',
                    warning: 'bg-yellow-500',
                    info: 'bg-blue-600',
                };
                const toast = document.createElement('div');
                toast.className =
                    `fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-4 py-3 rounded-2xl text-white text-sm font-medium shadow-xl transition-all duration-300 translate-y-4 opacity-0 ${colors[type] || colors.success}`;
                toast.innerHTML = `<span>${message}</span>`;
                document.body.appendChild(toast);
                requestAnimationFrame(() => {
                    toast.classList.remove('translate-y-4', 'opacity-0');
                });
                setTimeout(() => {
                    toast.classList.add('translate-y-4', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 3500);
            }

            <?php if(session('success')): ?>
                showToast(<?php echo json_encode(session('success'), 15, 512) ?>, 'success');
            <?php endif; ?>
            <?php if(session('error')): ?>
                showToast(<?php echo json_encode(session('error'), 15, 512) ?>, 'error');
            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\dashboard.blade.php ENDPATH**/ ?>