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
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    <?php echo e(__('Production Gantt Chart')); ?>

                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Visual production scheduling & capacity planning
                </p>
            </div>
            <div class="flex gap-2">
                <button onclick="optimizeSchedule()"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-magic mr-2"></i>Optimize
                </button>
                <button onclick="rescheduleOverdue()"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">
                    <i class="fas fa-clock mr-2"></i>Fix Overdue
                </button>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Scheduled</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                <?php echo e($analytics['total_scheduled']); ?></p>
                        </div>
                        <div
                            class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">On-Time Rate</p>
                            <p class="text-2xl font-bold text-green-600"><?php echo e($analytics['on_time_delivery_rate']); ?>%</p>
                        </div>
                        <div
                            class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Overdue</p>
                            <p class="text-2xl font-bold text-red-600"><?php echo e($analytics['overdue']); ?></p>
                        </div>
                        <div
                            class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Avg Variance</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                <?php echo e($analytics['avg_schedule_variance_days']); ?>d</p>
                        </div>
                        <div
                            class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-purple-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <form method="GET" class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Start
                            Date</label>
                        <input type="date" name="start_date" value="<?php echo e($startDate->format('Y-m-d')); ?>"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                        <input type="date" name="end_date" value="<?php echo e($endDate->format('Y-m-d')); ?>"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>

            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Production Schedule</h2>
                </div>

                <div class="overflow-x-auto">
                    <div class="p-6" id="gantt-chart" style="min-width: 1000px;">
                        <?php $__empty_1 = true; $__currentLoopData = $schedule['work_orders']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="mb-4">
                                
                                <div class="flex items-center gap-3 mb-2">
                                    <span
                                        class="text-sm font-medium text-gray-900 dark:text-white w-32"><?php echo e($wo['number']); ?></span>
                                    <span
                                        class="text-xs text-gray-600 dark:text-gray-400 flex-1"><?php echo e($wo['product_name']); ?></span>

                                    
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded 
                            <?php echo e($wo['priority'] == 1 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : ''); ?>

                            <?php echo e($wo['priority'] == 2 ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' : ''); ?>

                            <?php echo e($wo['priority'] == 3 ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : ''); ?>

                            <?php echo e($wo['priority'] == 4 ? 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400' : ''); ?>">
                                        <?php echo e($wo['priority_label']); ?>

                                    </span>

                                    
                                    <span
                                        class="text-xs text-gray-600 dark:text-gray-400 w-16 text-right"><?php echo e($wo['progress']); ?>%</span>
                                </div>

                                
                                <div class="relative h-8 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden ml-32">
                                    
                                    <div class="absolute h-full bg-blue-500 dark:bg-blue-600 rounded-lg opacity-30"
                                        style="left: 0%; width: 100%;"></div>

                                    
                                    <div class="absolute h-full rounded-lg transition-all
                                    <?php echo e($wo['is_overdue'] ? 'bg-red-500 dark:bg-red-600' : 'bg-green-500 dark:bg-green-600'); ?>"
                                        style="left: 0%; width: <?php echo e($wo['progress']); ?>%;"></div>

                                    
                                    <div class="absolute inset-0 flex items-center px-3">
                                        <span class="text-xs font-medium text-white drop-shadow"><?php echo e($wo['progress']); ?>%
                                            Complete</span>
                                    </div>
                                </div>

                                
                                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 ml-32 mt-1">
                                    <span>Start: <?php echo e($wo['start']); ?></span>
                                    <span>End: <?php echo e($wo['end']); ?></span>
                                    <?php if($wo['is_overdue']): ?>
                                        <span class="text-red-600 font-semibold"><i
                                                class="fas fa-exclamation-triangle mr-1"></i>Overdue</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center py-12">
                                <i class="fas fa-calendar-times text-6xl text-gray-300 dark:text-slate-600 mb-4"></i>
                                <p class="text-gray-500 dark:text-slate-400">No work orders scheduled for this period
                                </p>
                                <a href="<?php echo e(route('production.index')); ?>"
                                    class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Create Work Order
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Priority Distribution</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400"><i
                                    class="fas fa-circle text-red-600 mr-2"></i>Urgent</span>
                            <span
                                class="font-semibold text-gray-900 dark:text-white"><?php echo e($analytics['priority_distribution']['urgent']); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400"><i
                                    class="fas fa-circle text-orange-600 mr-2"></i>High</span>
                            <span
                                class="font-semibold text-gray-900 dark:text-white"><?php echo e($analytics['priority_distribution']['high']); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400"><i
                                    class="fas fa-circle text-blue-600 mr-2"></i>Normal</span>
                            <span
                                class="font-semibold text-gray-900 dark:text-white"><?php echo e($analytics['priority_distribution']['normal']); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400"><i
                                    class="fas fa-circle text-gray-600 mr-2"></i>Low</span>
                            <span
                                class="font-semibold text-gray-900 dark:text-white"><?php echo e($analytics['priority_distribution']['low']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Schedule Summary</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Completed</span>
                            <span class="font-semibold text-green-600"><?php echo e($analytics['completed']); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">In Progress</span>
                            <span class="font-semibold text-blue-600"><?php echo e($analytics['in_progress']); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Pending</span>
                            <span class="font-semibold text-orange-600"><?php echo e($analytics['pending']); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">On-Time Rate</span>
                            <span
                                class="font-semibold text-purple-600"><?php echo e($analytics['on_time_delivery_rate']); ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php $__env->startPush('scripts'); ?>
            <script>
                function optimizeSchedule() {
                    if (!confirm('Optimize production schedule? This will analyze all pending work orders.')) return;

                    fetch('<?php echo e(route('production.gantt.optimize')); ?>', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                                'Accept': 'application/json',
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            alert(`Found ${data.total_optimizations} optimization opportunities`);
                            location.reload();
                        })
                        .catch(err => {
                            alert('Error optimizing schedule');
                            console.error(err);
                        });
                }

                function rescheduleOverdue() {
                    if (!confirm('Reschedule all overdue work orders?')) return;

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?php echo e(route('production.gantt.reschedule-overdue')); ?>';

                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '<?php echo e(csrf_token()); ?>';
                    form.appendChild(csrf);

                    document.body.appendChild(form);
                    form.submit();
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\production\gantt.blade.php ENDPATH**/ ?>