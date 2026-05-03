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
     <?php $__env->slot('header', null, []); ?> Room Availability <?php $__env->endSlot(); ?>

    <?php
        $currentMonth = \Carbon\Carbon::create($year, $month, 1);
        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();
        $daysInMonth = $currentMonth->daysInMonth;
        $today = today()->toDateString();
    ?>

    <div x-data="availabilityCalendar()" class="space-y-6">
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Room Availability Calendar</h2>
                <p class="text-sm text-gray-500">View and manage room occupancy by date</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?php echo e(route('hotel.rooms.availability', ['month' => $prevMonth->month, 'year' => $prevMonth->year])); ?>"
                    class="p-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <span class="px-4 py-2 text-sm font-medium text-gray-900 min-w-[160px] text-center">
                    <?php echo e($currentMonth->format('F Y')); ?>

                </span>
                <a href="<?php echo e(route('hotel.rooms.availability', ['month' => $nextMonth->month, 'year' => $nextMonth->year])); ?>"
                    class="p-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="<?php echo e(route('hotel.rooms.availability')); ?>"
                    class="ml-2 px-3 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                    Today
                </a>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex flex-wrap items-center gap-4">
                <span class="text-xs font-medium text-gray-500">Legend:</span>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-green-500"></span>
                        <span class="text-xs text-gray-600">Available</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-red-500"></span>
                        <span class="text-xs text-gray-600">Occupied</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-blue-500"></span>
                        <span class="text-xs text-gray-600">Reserved</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-yellow-500"></span>
                        <span class="text-xs text-gray-600">Cleaning</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-gray-500"></span>
                        <span class="text-xs text-gray-600">Blocked</span>
                    </div>
                </div>
                <div class="ml-auto">
                    <select x-model="selectedRoomType" @change="filterRoomType()"
                        class="px-3 py-1.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">All Room Types</option>
                        <?php $__currentLoopData = $roomTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($rt->id); ?>"><?php echo e($rt->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <?php
                $todayStats = $calendar[$today] ?? null;
                $totalRooms = $roomTypes->sum('rooms_count');
                $todayOccupied = $todayStats['overall']['occupied'] ?? 0;
                $todayAvailable = $todayStats['overall']['available'] ?? $totalRooms;
                $todayRate = $todayStats['overall']['occupancy_rate'] ?? 0;
            ?>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Total Rooms</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e($totalRooms); ?></p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Available Today</p>
                <p class="text-2xl font-bold text-green-600 mt-1"><?php echo e($todayAvailable); ?></p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Occupied Today</p>
                <p class="text-2xl font-bold text-red-600 mt-1"><?php echo e($todayOccupied); ?></p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <p class="text-xs text-gray-500">Today's Occupancy</p>
                <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e($todayRate); ?>%</p>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    
                    <thead>
                        <tr class="bg-gray-50">
                            <th
                                class="sticky left-0 z-10 bg-gray-50 px-3 py-2 text-left font-medium text-gray-500 min-w-[140px] border-r border-gray-200">
                                Room Type
                            </th>
                            <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                                <?php
                                    $date = $currentMonth->copy()->day($d);
                                    $dateStr = $date->toDateString();
                                    $isToday = $dateStr === $today;
                                    $isWeekend = $date->isWeekend();
                                ?>
                                <th class="px-1 py-2 text-center font-medium <?php echo e($isToday ? 'bg-blue-500 text-white' : ($isWeekend ? 'text-gray-400' : 'text-gray-500')); ?> <?php echo e($isToday ? '' : ''); ?>"
                                    style="min-width: 36px;">
                                    <div class="text-[10px] uppercase"><?php echo e($date->format('D')); ?></div>
                                    <div class="font-bold"><?php echo e($d); ?></div>
                                </th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        
                        <?php $__empty_1 = true; $__currentLoopData = $roomTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 room-type-row"
                                data-room-type-id="<?php echo e($rt->id); ?>">
                                <td
                                    class="sticky left-0 z-10 bg-white px-3 py-2 border-r border-gray-200">
                                    <div class="font-medium text-gray-900"><?php echo e($rt->name); ?></div>
                                    <div class="text-gray-400"><?php echo e($rt->rooms_count); ?> rooms</div>
                                </td>
                                <?php for($d = 1; $d <= $daysInMonth; $d++): ?>
                                    <?php
                                        $date = $currentMonth->copy()->day($d);
                                        $dateStr = $date->toDateString();
                                        $dayData = $calendar[$dateStr] ?? null;
                                        $rtData = $dayData['room_types'][$rt->id] ?? null;
                                        $available = $rtData['available'] ?? $rt->rooms_count;
                                        $occupied = $rtData['occupied'] ?? 0;
                                        $total = $rtData['total'] ?? $rt->rooms_count;
                                        $rate = $rtData['occupancy_rate'] ?? 0;
                                        $isToday = $dateStr === $today;
                                    ?>
                                    <td
                                        class="px-0.5 py-1 text-center <?php echo e($isToday ? 'bg-blue-50' : ''); ?>">
                                        <?php
                                            // Determine color based on availability
                                            if ($occupied >= $total) {
                                                $bgClass = 'bg-red-500 text-white';
                                            } elseif ($rate >= 80) {
                                                $bgClass = 'bg-orange-500 text-white';
                                            } elseif ($rate >= 50) {
                                                $bgClass = 'bg-yellow-500 text-gray-900';
                                            } else {
                                                $bgClass = 'bg-green-500 text-white';
                                            }
                                        ?>
                                        <div class="w-8 h-8 mx-auto rounded-lg <?php echo e($bgClass); ?> flex items-center justify-center font-bold cursor-pointer hover:ring-2 hover:ring-blue-400"
                                            x-data="{ showTooltip: false }" @mouseenter="showTooltip = true"
                                            @mouseleave="showTooltip = false"
                                            title="<?php echo e($available); ?>/<?php echo e($total); ?> available">
                                            <span><?php echo e($available); ?></span>
                                            
                                            <div x-show="showTooltip" x-cloak
                                                class="absolute z-50 px-2 py-1 text-xs bg-gray-900 text-white rounded shadow-lg -translate-x-1/2 left-1/2 top-full mt-1 whitespace-nowrap">
                                                <?php echo e($rt->name); ?>: <?php echo e($available); ?>/<?php echo e($total); ?>

                                                available (<?php echo e($rate); ?>% occupied)
                                            </div>
                                        </div>
                                    </td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="<?php echo e($daysInMonth + 1); ?>"
                                    class="px-4 py-12 text-center text-gray-400">
                                    No room types defined. <a href="<?php echo e(route('hotel.room-types.index')); ?>"
                                        class="text-blue-500 hover:underline">Create room types first</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div x-show="showDayDetail" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="showDayDetail = false"
                class="bg-white rounded-2xl w-full max-w-md shadow-xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900" x-text="dayDetailTitle"></h3>
                    <button @click="showDayDetail = false"
                        class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <template x-for="(rt, index) in dayDetailData" :key="index">
                            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50">
                                <div>
                                    <p class="font-medium text-gray-900" x-text="rt.name"></p>
                                    <p class="text-xs text-gray-500">
                                        <span x-text="rt.occupied"></span> / <span x-text="rt.total"></span> occupied
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900">
                                        <span x-text="rt.available"></span> available
                                    </p>
                                    <p class="text-xs"
                                        :class="rt.occupancy_rate >= 80 ? 'text-red-500' : (rt.occupancy_rate >= 50 ?
                                            'text-yellow-500' : 'text-green-500')">
                                        <span x-text="rt.occupancy_rate"></span>% occupied
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <a href="#"
                            class="block w-full py-2 text-center text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                            New Reservation
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <script>
        window.availabilityCalendar = function() {
            return {
                selectedRoomType: '',
                showDayDetail: false,
                dayDetailTitle: '',
                dayDetailData: [],
                calendar: <?php echo json_encode($calendar, 15, 512) ?>,
                roomTypes: <?php echo json_encode($roomTypes, 15, 512) ?>,

                filterRoomType() {
                    const rows = document.querySelectorAll('.room-type-row');
                    rows.forEach(row => {
                        if (!this.selectedRoomType || row.dataset.roomTypeId === this.selectedRoomType) {
                            row.classList.remove('hidden');
                        } else {
                            row.classList.add('hidden');
                        }
                    });
                },

                showDay(dateStr) {
                    const dayData = this.calendar[dateStr];
                    if (!dayData) return;

                    const date = new Date(dateStr);
                    this.dayDetailTitle = date.toLocaleDateString('en-US', {
                        weekday: 'long',
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    });

                    this.dayDetailData = Object.values(dayData.room_types).map(rt => ({
                        name: rt.name,
                        total: rt.total,
                        occupied: rt.occupied,
                        available: rt.available,
                        occupancy_rate: rt.occupancy_rate
                    }));

                    this.showDayDetail = true;
                }
            }
        };

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
            requestAnimationFrame(() => toast.classList.remove('translate-y-4', 'opacity-0'));
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

    <style>
        /* Smooth scrollbar for calendar */
        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.25);
        }

        .dark .overflow-x-auto::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .dark .overflow-x-auto::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
        }

        .dark .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.25);
        }
    </style>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\rooms\availability.blade.php ENDPATH**/ ?>