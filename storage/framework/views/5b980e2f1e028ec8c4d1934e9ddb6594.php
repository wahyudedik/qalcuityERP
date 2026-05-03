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
     <?php $__env->slot('header', null, []); ?> Group Bookings <?php $__env->endSlot(); ?>

     <?php $__env->slot('pageHeader', null, []); ?> 
        <a href="<?php echo e(route('hotel.group-bookings.create')); ?>"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Group Booking
        </a>
     <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
        <div
            class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200">
            <p class="text-green-800 text-sm"><?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?>

    
    <div class="bg-white rounded-2xl border border-gray-200 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-3 p-4">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                placeholder="Search group name or code..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">

            <select name="type"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Types</option>
                <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type); ?>" <?php echo e(request('type') == $type ? 'selected' : ''); ?>>
                        <?php echo e(ucfirst($type)); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <select name="status"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Statuses</option>
                <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status); ?>" <?php echo e(request('status') == $status ? 'selected' : ''); ?>>
                        <?php echo e(ucfirst(str_replace('_', ' ', $status))); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
                Filter
            </button>
        </form>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php $__empty_1 = true; $__currentLoopData = $groupBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div
                class="bg-white rounded-2xl border border-gray-200 p-6 hover:shadow-lg transition">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900"><?php echo e($group->group_name); ?></h3>
                        <p class="text-xs text-gray-500 mt-1"><?php echo e($group->group_code); ?></p>
                    </div>
                    <?php
                        $statusColors = [
                            'pending' => 'bg-gray-100 text-gray-700',
                            'confirmed' => 'bg-blue-100 text-blue-700',
                            'active' => 'bg-green-100 text-green-700',
                            'completed' => 'bg-purple-100 text-purple-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                        ];
                        $typeIcons = [
                            'corporate' => '🏢',
                            'family' => '👨‍👩‍👧‍👦',
                            'tour' => '🚌',
                            'event' => '🎉',
                            'government' => '🏛️',
                            'other' => '📋',
                        ];
                    ?>
                    <span
                        class="px-2 py-1 rounded-lg text-xs font-medium <?php echo e($statusColors[$group->status] ?? $statusColors['pending']); ?>">
                        <?php echo e(ucfirst(str_replace('_', ' ', $group->status))); ?>

                    </span>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-lg"><?php echo e($typeIcons[$group->type] ?? '📋'); ?></span>
                        <span class="text-gray-600"><?php echo e(ucfirst($group->type)); ?> Group</span>
                    </div>

                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-gray-600">
                            <?php echo e(\Carbon\Carbon::parse($group->start_date)->format('d M Y')); ?> -
                            <?php echo e(\Carbon\Carbon::parse($group->end_date)->format('d M Y')); ?>

                        </span>
                    </div>

                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="text-gray-600">
                            <?php echo e($group->total_rooms); ?> rooms · <?php echo e($group->total_guests); ?> guests
                        </span>
                    </div>

                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Payment:</span>
                                <span class="font-medium text-gray-900">
                                    <?php echo e(number_format($group->paid_amount, 0)); ?> /
                                    <?php echo e(number_format($group->total_amount, 0)); ?>

                                </span>
                            </div>
                            <?php
                                $paymentPercent =
                                    $group->total_amount > 0 ? ($group->paid_amount / $group->total_amount) * 100 : 0;
                            ?>
                            <div class="mt-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full transition-all duration-300"
                                    style="width: <?php echo e($paymentPercent); ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <a href="<?php echo e(route('hotel.group-bookings.show', $group)); ?>"
                        class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        View Details →
                    </a>
                    <?php if($group->status === 'pending'): ?>
                        <form action="<?php echo e(route('hotel.group-bookings.confirm', $group)); ?>" method="POST"
                            class="inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                class="px-3 py-1.5 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                Confirm
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-span-full text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <p class="text-gray-500 text-sm">No group bookings found</p>
                <a href="<?php echo e(route('hotel.group-bookings.create')); ?>"
                    class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-medium">
                    Create Your First Group Booking
                </a>
            </div>
        <?php endif; ?>
    </div>

    
    <div class="mt-6">
        <?php echo e($groupBookings->links()); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\group-bookings\index.blade.php ENDPATH**/ ?>