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
        <h1 class="text-base font-semibold text-gray-900 dark:text-white">Tour Bookings</h1>
     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-4 flex justify-end">
                <a href="<?php echo e(route('tour-travel.bookings.create')); ?>"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                    + New Booking
                </a>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Total Bookings</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e($stats['total_bookings']); ?></p>
                </div>
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-xl border border-yellow-200 dark:border-yellow-500/30 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">
                        <?php echo e($stats['pending_bookings']); ?></p>
                </div>
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-blue-200 dark:border-blue-500/30 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Confirmed</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                        <?php echo e($stats['confirmed_bookings']); ?></p>
                </div>
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-xl border border-purple-200 dark:border-purple-500/30 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Upcoming Departures</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">
                        <?php echo e($stats['upcoming_departures']); ?>

                    </p>
                </div>
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-green-200 dark:border-green-500/30 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Total Revenue</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">Rp
                        <?php echo e(number_format($stats['total_revenue'], 0, ',', '.')); ?></p>
                </div>
            </div>

            
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">All Bookings</h2>
                    <div class="flex gap-2">
                        <select
                            class="text-sm border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="paid">Paid</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <input type="date"
                            class="text-sm border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                            placeholder="Tanggal Keberangkatan">
                    </div>
                </div>

                <?php if($bookings->count() === 0): ?>
                    <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'calendar','title' => 'Belum ada booking','message' => 'Belum ada booking tour travel. Buat booking pertama Anda.','actionText' => 'Buat Booking','actionUrl' => ''.e(route('tour-travel.bookings.create')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'calendar','title' => 'Belum ada booking','message' => 'Belum ada booking tour travel. Buat booking pertama Anda.','actionText' => 'Buat Booking','actionUrl' => ''.e(route('tour-travel.bookings.create')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-[#0f172a]">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Booking #</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Customer</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Package</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Departure</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Pax</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Total</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Payment</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Status</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#0f172a] transition">
                                <td class="px-6 py-4">
                                    <a href="<?php echo e(route('tour-travel.bookings.show', $booking)); ?>"
                                        class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                        <?php echo e($booking->booking_number); ?>

                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            <?php echo e($booking->customer_name); ?></p>
                                        <?php if($booking->customer_email): ?>
                                            <p class="text-xs text-gray-500 dark:text-slate-400">
                                                <?php echo e($booking->customer_email); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    <?php echo e($booking->tourPackage?->name ?? 'N/A'); ?>

                                </td>
                                <td class="px-6 py-4">
                                    <?php if($booking->departure_date): ?>
                                        <?php if($booking->departure_date->isPast()): ?>
                                            <span
                                                class="text-gray-500 dark:text-slate-400"><?php echo e($booking->departure_date->format('d M Y')); ?></span>
                                        <?php elseif($booking->departure_date->diffInDays(now()) <= 7): ?>
                                            <span
                                                class="text-orange-600 dark:text-orange-400 font-medium"><?php echo e($booking->departure_date->format('d M Y')); ?></span>
                                        <?php else: ?>
                                            <span
                                                class="text-gray-700 dark:text-slate-300"><?php echo e($booking->departure_date->format('d M Y')); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    <div class="text-sm">
                                        <p><?php echo e($booking->total_pax); ?> pax</p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400">
                                            <?php echo e($booking->adults); ?>A / <?php echo e($booking->children); ?>C /
                                            <?php echo e($booking->infants); ?>I
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900 dark:text-white">Rp
                                            <?php echo e(number_format($booking->total_amount, 0, ',', '.')); ?></p>
                                        <?php if(!$booking->is_fully_paid): ?>
                                            <p class="text-xs text-red-600 dark:text-red-400">
                                                Due: Rp <?php echo e(number_format($booking->balance_due, 0, ',', '.')); ?>

                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                        $payColor = match ($booking->payment_status) {
                                            'unpaid' => 'red',
                                            'partial' => 'yellow',
                                            'paid' => 'green',
                                            'refunded' => 'gray',
                                            default => 'gray',
                                        };
                                    ?>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-<?php echo e($payColor); ?>-100 text-<?php echo e($payColor); ?>-700 dark:bg-<?php echo e($payColor); ?>-500/20 dark:text-<?php echo e($payColor); ?>-400">
                                        <?php echo e(ucfirst($booking->payment_status)); ?>

                                    </span>
                                    <?php if($booking->paid_amount > 0): ?>
                                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                            Paid: Rp <?php echo e(number_format($booking->paid_amount, 0, ',', '.')); ?>

                                        </p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                        $statusColor = match ($booking->status) {
                                            'pending' => 'yellow',
                                            'confirmed' => 'blue',
                                            'paid' => 'green',
                                            'cancelled' => 'red',
                                            'completed' => 'gray',
                                            'refunded' => 'orange',
                                            default => 'gray',
                                        };
                                    ?>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-<?php echo e($statusColor); ?>-100 text-<?php echo e($statusColor); ?>-700 dark:bg-<?php echo e($statusColor); ?>-500/20 dark:text-<?php echo e($statusColor); ?>-400">
                                        <?php echo e(ucfirst($booking->status)); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <a href="<?php echo e(route('tour-travel.bookings.show', $booking)); ?>"
                                            class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs">View</a>

                                        <?php if($booking->status === 'pending'): ?>
                                            <form action="<?php echo e(route('tour-travel.bookings.confirm', $booking)); ?>"
                                                method="POST" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Confirm</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if($booking->status === 'confirmed' || $booking->status === 'paid'): ?>
                                            <form action="<?php echo e(route('tour-travel.bookings.complete', $booking)); ?>"
                                                method="POST" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="text-green-600 dark:text-green-400 hover:underline text-xs">Complete</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">
                <?php echo e($bookings->links()); ?>

            </div>
            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\tour-travel\bookings\index.blade.php ENDPATH**/ ?>