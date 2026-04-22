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
     <?php $__env->slot('header', null, []); ?> Reservation Calendar <?php $__env->endSlot(); ?>

     <?php $__env->slot('pageHeader', null, []); ?> 
        <a href="<?php echo e(route('hotel.reservations.index')); ?>"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white text-sm font-medium hover:bg-gray-200 dark:hover:bg-white/20 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            List View
        </a>
        <a href="<?php echo e(route('hotel.reservations.create')); ?>"
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Reservation
        </a>
     <?php $__env->endSlot(); ?>

    <?php
        $currentMonth = \Carbon\Carbon::create($year, $month, 1);
        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();
        $daysInMonth = $currentMonth->daysInMonth;
        $days = collect(range(1, $daysInMonth));
    ?>

    
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('hotel.reservations.calendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year])); ?>"
                class="p-2 rounded-xl border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                <?php echo e($currentMonth->format('F Y')); ?>

            </h2>
            <a href="<?php echo e(route('hotel.reservations.calendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year])); ?>"
                class="p-2 rounded-xl border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        
        <div class="flex flex-wrap items-center gap-4 text-xs">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-green-500"></span>
                <span class="text-gray-600 dark:text-slate-400">Confirmed</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-blue-500"></span>
                <span class="text-gray-600 dark:text-slate-400">Checked In</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-yellow-500"></span>
                <span class="text-gray-600 dark:text-slate-400">Pending</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-gray-400"></span>
                <span class="text-gray-600 dark:text-slate-400">Checked Out</span>
            </div>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        
        <div
            class="grid grid-cols-[180px_repeat(31,minmax(32px,1fr))] bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
            <div
                class="px-4 py-3 text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase sticky left-0 bg-gray-50 dark:bg-white/5">
                Room</div>
            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $date = $currentMonth->copy()->day($day); ?>
                <div
                    class="px-1 py-3 text-center text-xs <?php echo e($date->isWeekend() ? 'text-red-400' : 'text-gray-500 dark:text-slate-400'); ?> font-medium">
                    <div><?php echo e($date->format('D')); ?></div>
                    <div class="text-sm <?php echo e($date->isToday() ? 'text-blue-600 font-bold' : ''); ?>"><?php echo e($day); ?>

                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div x-data="{ expandedTypes: {} }" class="divide-y divide-gray-100 dark:divide-white/5">
            <?php $__empty_1 = true; $__currentLoopData = $roomTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roomType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                
                <div @click="expandedTypes['<?php echo e($roomType->id); ?>'] = !expandedTypes['<?php echo e($roomType->id); ?>']"
                    class="grid grid-cols-[180px_repeat(31,minmax(32px,1fr))] bg-gray-50 dark:bg-white/5 cursor-pointer hover:bg-gray-100 dark:hover:bg-white/10">
                    <div class="px-4 py-3 flex items-center gap-2 sticky left-0 bg-gray-50 dark:bg-white/5">
                        <svg class="w-4 h-4 text-gray-400 transition-transform"
                            :class="{ 'rotate-90': expandedTypes['<?php echo e($roomType->id); ?>'] }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="font-medium text-gray-900 dark:text-white text-sm"><?php echo e($roomType->name); ?></span>
                        <span class="text-xs text-gray-400">(<?php echo e($roomType->rooms->count()); ?> rooms)</span>
                    </div>
                    <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="px-1 py-3"></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <template x-if="expandedTypes['<?php echo e($roomType->id); ?>']">
                    <div class="divide-y divide-gray-50 dark:divide-white/5">
                        <?php $currentRoomType = $roomType; ?>
                        <?php $__currentLoopData = $roomType->rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $roomTypeId = $currentRoomType->id;
                                $roomReservations = $reservations->filter(function ($r) use ($room, $roomTypeId) {
                                    return $r->room_id == $room->id ||
                                        ($r->room_type_id == $roomTypeId && !$r->room_id);
                                });
                            ?>
                            <div
                                class="grid grid-cols-[180px_repeat(31,minmax(32px,1fr))] hover:bg-gray-50 dark:hover:bg-white/5">
                                <div class="px-4 py-2 flex items-center gap-2 sticky left-0 bg-white dark:bg-[#1e293b]">
                                    <span class="text-sm text-gray-700 dark:text-slate-300"><?php echo e($room->number); ?></span>
                                    <?php if($room->floor): ?>
                                        <span class="text-xs text-gray-400">Floor <?php echo e($room->floor); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $currentDate = $currentMonth->copy()->day($day);
                                        $dayReservations = $roomReservations->filter(function ($r) use ($currentDate) {
                                            $checkIn = \Carbon\Carbon::parse($r->check_in_date);
                                            $checkOut = \Carbon\Carbon::parse($r->check_out_date);
                                            return $checkIn->lte($currentDate) && $checkOut->gt($currentDate);
                                        });
                                    ?>
                                    <div class="px-0.5 py-1 relative min-h-[36px]">
                                        <?php $__currentLoopData = $dayReservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rsv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php break; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(isset($rsv)): ?>
                                        <?php
                                            $checkIn = \Carbon\Carbon::parse($rsv->check_in_date);
                                            $checkOut = \Carbon\Carbon::parse($rsv->check_out_date);
                                            $isStart = $checkIn->eq($currentDate);
                                            $isEnd = $checkOut->eq($currentDate->copy()->addDay());
                                            $statusColor = match ($rsv->status) {
                                                'confirmed' => 'bg-green-500',
                                                'checked_in' => 'bg-blue-500',
                                                'pending' => 'bg-yellow-500',
                                                'checked_out' => 'bg-gray-400',
                                                'cancelled' => 'bg-red-400',
                                                default => 'bg-gray-300',
                                            };
                                        ?>
                                        <?php if($isStart): ?>
                                            <a href="<?php echo e(route('hotel.reservations.show', $rsv)); ?>"
                                                class="absolute left-0 right-0 top-1 h-6 <?php echo e($statusColor); ?> rounded text-white text-xs flex items-center px-1 truncate hover:opacity-80 transition z-10"
                                                title="<?php echo e($rsv->guest?->name); ?> (<?php echo e($rsv->check_in_date->format('d M')); ?> - <?php echo e($rsv->check_out_date->format('d M')); ?>)">
                                                <span class="truncate"><?php echo e($rsv->guest?->name); ?></span>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php unset($rsv); ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </template>

            
            <div x-show="!expandedTypes['<?php echo e($roomType->id); ?>']"
                class="grid grid-cols-[180px_repeat(31,minmax(32px,1fr))] h-10">
                <div class="px-4 py-2 sticky left-0 bg-white dark:bg-[#1e293b] flex items-center">
                    <span class="text-xs text-gray-400">Click to expand rooms</span>
                </div>
                <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $currentDate = $currentMonth->copy()->day($day);
                        $typeReservations = $reservations->filter(function ($r) use ($roomType, $currentDate) {
                            if ($r->room_type_id != $roomType->id) {
                                return false;
                            }
                            $checkIn = \Carbon\Carbon::parse($r->check_in_date);
                            $checkOut = \Carbon\Carbon::parse($r->check_out_date);
                            return $checkIn->lte($currentDate) && $checkOut->gt($currentDate);
                        });
                        $occupiedCount = $typeReservations->count();
                        $occupancyPercent =
                            $roomType->rooms->count() > 0
                                ? round(($occupiedCount / $roomType->rooms->count()) * 100)
                                : 0;
                    ?>
                    <div class="px-0.5 py-1 relative flex items-center justify-center">
                        <?php if($occupiedCount > 0): ?>
                            <div
                                class="w-full h-6 rounded <?php echo e($occupancyPercent >= 80 ? 'bg-red-500' : ($occupancyPercent >= 50 ? 'bg-yellow-500' : 'bg-green-500')); ?> opacity-70 text-white text-xs flex items-center justify-center font-medium">
                                <?php echo e($occupiedCount); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">
                No room types configured. <a href="<?php echo e(route('hotel.room-types.index')); ?>"
                    class="text-blue-500 hover:underline">Configure room types</a>
            </div>
        <?php endif; ?>
    </div>
</div>


<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Reservations This Month</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($reservations->count()); ?></p>
    </div>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Currently Checked In</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
            <?php echo e($reservations->where('status', 'checked_in')->count()); ?></p>
    </div>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Arrivals Today</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
            <?php echo e($reservations->filter(fn($r) => \Carbon\Carbon::parse($r->check_in_date)->isToday())->count()); ?>

        </p>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\reservations\calendar.blade.php ENDPATH**/ ?>