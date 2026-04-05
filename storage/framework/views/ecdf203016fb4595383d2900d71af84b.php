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
     <?php $__env->slot('header', null, []); ?> Rate Calendar <?php $__env->endSlot(); ?>

    <?php
        $monthName = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
        $prevMonth = $month - 1 < 1 ? 12 : $month - 1;
        $prevYear = $month - 1 < 1 ? $year - 1 : $year;
        $nextMonth = $month + 1 > 12 ? 1 : $month + 1;
        $nextYear = $month + 1 > 12 ? $year + 1 : $year;
        $daysInMonth = \Carbon\Carbon::create($year, $month, 1)->daysInMonth;
        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    ?>

    <div class="space-y-6">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Rate Calendar</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400">View and manage rates across dates</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?php echo e(route('hotel.rates.calendar', ['month' => $prevMonth, 'year' => $prevYear])); ?>"
                    class="p-2 rounded-xl border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <span
                    class="px-4 py-2 font-medium text-gray-900 dark:text-white min-w-[160px] text-center"><?php echo e($monthName); ?></span>
                <a href="<?php echo e(route('hotel.rates.calendar', ['month' => $nextMonth, 'year' => $nextYear])); ?>"
                    class="p-2 rounded-xl border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="<?php echo e(route('hotel.rates.calendar')); ?>"
                    class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                    Today
                </a>
            </div>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/5">
                            <th
                                class="px-3 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase sticky left-0 bg-gray-50 dark:bg-[#1e293b] z-10">
                                Room Type</th>
                            <?php for($day = 1; $day <= $daysInMonth; $day++): ?>
                                <?php
                                    $date = \Carbon\Carbon::create($year, $month, $day);
                                    $isWeekend = $date->isWeekend();
                                    $isToday = $date->isToday();
                                ?>
                                <th
                                    class="px-2 py-3 text-center text-xs font-medium <?php echo e($isWeekend ? 'bg-blue-50 dark:bg-blue-500/10' : ''); ?> <?php echo e($isToday ? 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-slate-400'); ?>">
                                    <div><?php echo e($dayNames[$date->dayOfWeek]); ?></div>
                                    <div class="text-base font-semibold"><?php echo e($day); ?></div>
                                </th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__empty_1 = true; $__currentLoopData = $roomTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roomType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td
                                    class="px-3 py-3 font-medium text-gray-900 dark:text-white sticky left-0 bg-white dark:bg-[#1e293b] z-10">
                                    <?php echo e($roomType->name); ?>

                                    <p class="text-xs text-gray-500 dark:text-slate-400">Base: Rp
                                        <?php echo e(number_format($roomType->base_rate, 0, ',', '.')); ?></p>
                                </td>
                                <?php for($day = 1; $day <= $daysInMonth; $day++): ?>
                                    <?php
                                        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                        $date = \Carbon\Carbon::create($year, $month, $day);
                                        $isWeekend = $date->isWeekend();
                                        $isToday = $date->isToday();
                                        $dayData = $calendar[$dateStr]['rates'][$roomType->id] ?? null;
                                        $effectiveRate = $dayData['effective_rate'] ?? $roomType->base_rate;
                                        $hasCustomRate = $effectiveRate != $roomType->base_rate;
                                    ?>
                                    <td
                                        class="px-1 py-2 text-center <?php echo e($isWeekend ? 'bg-blue-50/50 dark:bg-blue-500/5' : ''); ?> <?php echo e($isToday ? 'bg-green-50/50 dark:bg-green-500/5' : ''); ?>">
                                        <div
                                            class="px-1 py-1 rounded-lg <?php echo e($hasCustomRate ? 'bg-blue-100 dark:bg-blue-500/20 font-medium text-blue-700 dark:text-blue-300' : 'text-gray-600 dark:text-slate-400'); ?>">
                                            <span class="text-xs"><?php echo e(number_format($effectiveRate / 1000, 0)); ?>K</span>
                                        </div>
                                    </td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="<?php echo e($daysInMonth + 1); ?>"
                                    class="px-4 py-8 text-center text-gray-400 dark:text-slate-500">
                                    No room types found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="flex flex-wrap gap-4 text-xs text-gray-500 dark:text-slate-400">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-gray-100 dark:bg-white/10"></div>
                <span>Base Rate</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-blue-100 dark:bg-blue-500/20"></div>
                <span>Custom Rate</span>
            </div>
            <div class="flex items-center gap-2">
                <div
                    class="w-4 h-4 rounded bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20">
                </div>
                <span>Weekend</span>
            </div>
            <div class="flex items-center gap-2">
                <div
                    class="w-4 h-4 rounded bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20">
                </div>
                <span>Today</span>
            </div>
        </div>

        
        <div x-data="bulkUpdateForm()"
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-4">Bulk Rate
                Update</h3>
            <form method="POST" action="<?php echo e(route('hotel.rates.bulk-update')); ?>"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="rates[0][rate_type]" value="standard">

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Room Type</label>
                    <select name="rates[0][room_type_id]" required
                        class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php $__currentLoopData = $roomTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roomType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($roomType->id); ?>"><?php echo e($roomType->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Start Date</label>
                    <input type="date" name="rates[0][start_date]" required
                        class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">End Date</label>
                    <input type="date" name="rates[0][end_date]" required
                        class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">New Rate
                        (IDR)</label>
                    <input type="number" name="rates[0][amount]" required min="0" step="1000"
                        class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-end">
                    <button type="submit"
                        class="w-full px-4 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl">
                        Apply Rate
                    </button>
                </div>
            </form>
        </div>

        
        <div class="pt-4">
            <a href="<?php echo e(route('hotel.rates.index')); ?>"
                class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white">
                ← Back to Rate Management
            </a>
        </div>
    </div>

    
    <script>
        window.bulkUpdateForm = function() {
            return {};
        };
    </script>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/hotel/rates/calendar.blade.php ENDPATH**/ ?>