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
     <?php $__env->slot('header', null, []); ?> <?php echo e($groupBooking->group_name); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <a href="<?php echo e(route('hotel.group-bookings.index')); ?>"
            class="text-gray-600 hover:text-blue-600 inline-flex items-center gap-1 text-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="max-w-6xl mx-auto space-y-6">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Status</p>
                <?php
                    $statusColors = [
                        'pending' => 'bg-gray-100 text-gray-700',
                        'confirmed' => 'bg-blue-100 text-blue-700',
                        'active' => 'bg-green-100 text-green-700',
                        'completed' => 'bg-purple-100 text-purple-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                    ];
                ?>
                <p class="text-lg font-bold mt-1 <?php echo e($statusColors[$groupBooking->status]); ?>">
                    <?php echo e(ucfirst($groupBooking->status)); ?>

                </p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Rooms / Guests</p>
                <p class="text-lg font-bold text-gray-900 mt-1">
                    <?php echo e($groupBooking->total_rooms); ?> / <?php echo e($groupBooking->total_guests); ?>

                </p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Payment Progress</p>
                <p class="text-lg font-bold text-gray-900 mt-1">
                    <?php echo e(number_format($groupBooking->paid_amount, 0)); ?> /
                    <?php echo e(number_format($groupBooking->total_amount, 0)); ?>

                </p>
                <div class="mt-2 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-green-500 rounded-full"
                        style="width: <?php echo e($groupBooking->payment_percentage); ?>%"></div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Balance Due</p>
                <p class="text-lg font-bold text-red-600 mt-1">
                    <?php echo e(number_format($groupBooking->balance, 0)); ?>

                </p>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Group Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-gray-500">Organizer</p>
                    <p class="font-medium text-gray-900"><?php echo e($groupBooking->organizer->name); ?></p>
                    <p class="text-sm text-gray-600">
                        <?php echo e($groupBooking->organizer->email ?? $groupBooking->organizer->phone); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Group Type</p>
                    <p class="font-medium text-gray-900"><?php echo e(ucfirst($groupBooking->type)); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Stay Period</p>
                    <p class="font-medium text-gray-900">
                        <?php echo e(\Carbon\Carbon::parse($groupBooking->start_date)->format('d M Y')); ?> -
                        <?php echo e(\Carbon\Carbon::parse($groupBooking->end_date)->format('d M Y')); ?>

                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Created By</p>
                    <p class="font-medium text-gray-900">
                        <?php echo e($groupBooking->creator?->name ?? 'System'); ?></p>
                </div>
            </div>

            <?php if($groupBooking->benefits): ?>
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500 mb-2">Special Benefits</p>
                    <ul class="space-y-1">
                        <?php $__currentLoopData = $groupBooking->benefits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $benefit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center gap-2 text-sm text-gray-700">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <?php echo e($benefit); ?>

                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if($groupBooking->notes): ?>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-500 mb-1">Notes</p>
                    <p class="text-sm text-gray-700"><?php echo e($groupBooking->notes); ?></p>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">Reservations
                    (<?php echo e($reservations->count()); ?>)</h3>
                <button onclick="openAddReservationModal()"
                    class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Add Reservation
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-2 text-xs font-medium text-gray-500">Guest
                            </th>
                            <th class="text-left py-3 px-2 text-xs font-medium text-gray-500">Room
                                Type</th>
                            <th
                                class="text-left py-3 px-2 text-xs font-medium text-gray-500 hidden sm:table-cell">
                                Check-in</th>
                            <th
                                class="text-left py-3 px-2 text-xs font-medium text-gray-500 hidden sm:table-cell">
                                Check-out</th>
                            <th class="text-left py-3 px-2 text-xs font-medium text-gray-500">Status
                            </th>
                            <th class="text-right py-3 px-2 text-xs font-medium text-gray-500">
                                Amount</th>
                            <th class="text-right py-3 px-2 text-xs font-medium text-gray-500">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $reservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reservation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-b border-gray-100">
                                <td class="py-3 px-2">
                                    <p class="font-medium text-gray-900">
                                        <?php echo e($reservation->guest->name); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo e($reservation->guest->email ?? $reservation->guest->phone); ?></p>
                                </td>
                                <td class="py-3 px-2 text-gray-600">
                                    <?php echo e($reservation->roomType->name); ?></td>
                                <td class="py-3 px-2 text-gray-600 hidden sm:table-cell">
                                    <?php echo e(\Carbon\Carbon::parse($reservation->check_in_date)->format('d M')); ?>

                                </td>
                                <td class="py-3 px-2 text-gray-600 hidden sm:table-cell">
                                    <?php echo e(\Carbon\Carbon::parse($reservation->check_out_date)->format('d M')); ?>

                                </td>
                                <td class="py-3 px-2">
                                    <span
                                        class="px-2 py-1 rounded-lg text-xs font-medium
                                        <?php if($reservation->status === 'confirmed'): ?> bg-blue-100 text-blue-700
                                        <?php elseif($reservation->status === 'checked_in'): ?> bg-green-100 text-green-700
                                        <?php else: ?> bg-gray-100 text-gray-700 <?php endif; ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $reservation->status))); ?>

                                    </span>
                                </td>
                                <td class="py-3 px-2 text-right font-medium text-gray-900">
                                    <?php echo e(number_format($reservation->grand_total, 0)); ?>

                                </td>
                                <td class="py-3 px-2 text-right">
                                    <a href="<?php echo e(route('hotel.reservations.show', $reservation)); ?>"
                                        class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                                        View
                                    </a>
                                    <?php if($groupBooking->status !== 'completed' && $groupBooking->status !== 'cancelled'): ?>
                                        <form
                                            action="<?php echo e(route('hotel.group-bookings.remove-reservation', $reservation)); ?>"
                                            method="POST" class="inline ml-2">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-700 text-xs font-medium"
                                                onclick="return confirm('Remove from group?')">
                                                Remove
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-500">
                                    No reservations in this group yet
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <?php if($groupBooking->status === 'pending'): ?>
            <div class="flex items-center justify-end gap-3">
                <form action="<?php echo e(route('hotel.group-bookings.confirm', $groupBooking)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">
                        Confirm Group Booking
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    
    <div id="modal-add-reservation" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <form action="<?php echo e(route('hotel.group-bookings.add-reservation', $groupBooking)); ?>" method="POST">
                <?php echo csrf_field(); ?>

                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Reservation to Group</h3>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Select
                            Reservation *</label>
                        <select name="reservation_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Choose reservation...</option>
                            <!-- You could load available reservations via AJAX -->
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Create the reservation first, then add it to this group.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 pb-6">
                    <button type="button"
                        onclick="document.getElementById('modal-add-reservation').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Add to Group
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function openAddReservationModal() {
                document.getElementById('modal-add-reservation').classList.remove('hidden');
            }
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\group-bookings\show.blade.php ENDPATH**/ ?>