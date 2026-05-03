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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('QC Inspections')); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('qc.inspections.create')); ?>"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus mr-2"></i>New Inspection
            </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Inspections</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['total']); ?></p>
                        </div>
                        <i class="fas fa-clipboard-check text-3xl text-blue-500"></i>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Passed</p>
                            <p class="text-2xl font-bold text-green-600"><?php echo e($stats['passed']); ?></p>
                        </div>
                        <i class="fas fa-check-circle text-3xl text-green-500"></i>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Failed</p>
                            <p class="text-2xl font-bold text-red-600"><?php echo e($stats['failed']); ?></p>
                        </div>
                        <i class="fas fa-times-circle text-3xl text-red-500"></i>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Pass Rate</p>
                            <p class="text-2xl font-bold text-blue-600"><?php echo e($stats['pass_rate']); ?>%</p>
                        </div>
                        <i class="fas fa-chart-line text-3xl text-blue-500"></i>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="<?php echo e(route('qc.inspections.index')); ?>"
                    class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                            class="w-full rounded-md border-gray-300">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending
                            </option>
                            <option value="in_progress" <?php echo e(request('status') == 'in_progress' ? 'selected' : ''); ?>>In
                                Progress</option>
                            <option value="passed" <?php echo e(request('status') == 'passed' ? 'selected' : ''); ?>>Passed</option>
                            <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>>Failed
                            </option>
                            <option value="conditional_pass"
                                <?php echo e(request('status') == 'conditional_pass' ? 'selected' : ''); ?>>Conditional Pass
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stage</label>
                        <select name="stage"
                            class="w-full rounded-md border-gray-300">
                            <option value="">All Stages</option>
                            <option value="incoming" <?php echo e(request('stage') == 'incoming' ? 'selected' : ''); ?>>Incoming
                            </option>
                            <option value="in-process" <?php echo e(request('stage') == 'in-process' ? 'selected' : ''); ?>>
                                In-Process</option>
                            <option value="final" <?php echo e(request('stage') == 'final' ? 'selected' : ''); ?>>Final</option>
                            <option value="random" <?php echo e(request('stage') == 'random' ? 'selected' : ''); ?>>Random</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Work
                            Order</label>
                        <select name="work_order_id"
                            class="w-full rounded-md border-gray-300">
                            <option value="">All Work Orders</option>
                            <?php $__currentLoopData = $workOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($wo->id); ?>"
                                    <?php echo e(request('work_order_id') == $wo->id ? 'selected' : ''); ?>>
                                    <?php echo e($wo->number); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                            class="w-full rounded-md border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"
                            class="w-full rounded-md border-gray-300">
                    </div>

                    <div class="md:col-span-5 flex justify-end gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-filter mr-2"></i>Apply Filters
                        </button>
                        <a href="<?php echo e(route('qc.inspections.index')); ?>"
                            class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg">
                            <i class="fas fa-undo mr-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Inspections Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Inspection #</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Work Order</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Stage</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Sample</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Pass Rate</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Grade</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__empty_1 = true; $__currentLoopData = $inspections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inspection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="text-sm font-medium text-blue-600"><?php echo e($inspection->inspection_number); ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo e($inspection->workOrder->number ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-700">
                                            <?php echo e($inspection->stage_label); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo e($inspection->sample_passed); ?>/<?php echo e($inspection->sample_size); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="text-sm font-semibold <?php echo e($inspection->pass_rate >= 95 ? 'text-green-600' : ($inspection->pass_rate >= 85 ? 'text-yellow-600' : 'text-red-600')); ?>">
                                            <?php echo e($inspection->pass_rate ?? 'N/A'); ?>%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($inspection->grade): ?>
                                            <span
                                                class="px-2 py-1 text-xs font-bold rounded 
                                            <?php echo e($inspection->grade == 'A' ? 'bg-green-100 text-green-700' : ''); ?>

                                            <?php echo e($inspection->grade == 'B' ? 'bg-blue-100 text-blue-700' : ''); ?>

                                            <?php echo e($inspection->grade == 'C' ? 'bg-yellow-100 text-yellow-700' : ''); ?>

                                            <?php echo e($inspection->grade == 'D' ? 'bg-orange-100 text-orange-700' : ''); ?>

                                            <?php echo e($inspection->grade == 'F' ? 'bg-red-100 text-red-700' : ''); ?>">
                                                <?php echo e($inspection->grade); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-400">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded 
                                        <?php echo e($inspection->status_color == 'green' ? 'bg-green-100 text-green-700' : ''); ?>

                                        <?php echo e($inspection->status_color == 'red' ? 'bg-red-100 text-red-700' : ''); ?>

                                        <?php echo e($inspection->status_color == 'yellow' ? 'bg-yellow-100 text-yellow-700' : ''); ?>

                                        <?php echo e($inspection->status_color == 'blue' ? 'bg-blue-100 text-blue-700' : ''); ?>

                                        <?php echo e($inspection->status_color == 'gray' ? 'bg-gray-100 text-gray-700' : ''); ?>">
                                            <?php echo e(str_replace('_', ' ', ucfirst($inspection->status))); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo e($inspection->created_at->format('Y-m-d H:i')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex gap-2">
                                            <a href="<?php echo e(route('qc.inspections.show', $inspection)); ?>"
                                                class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if($inspection->status == 'pending' || $inspection->status == 'in_progress'): ?>
                                                <a href="<?php echo e(route('qc.inspections.edit', $inspection)); ?>"
                                                    class="text-green-600 hover:text-green-800">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center">
                                        <i
                                            class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
                                        <p class="text-gray-500">No QC inspections found</p>
                                        <a href="<?php echo e(route('qc.inspections.create')); ?>"
                                            class="mt-4 inline-block text-blue-600 hover:text-blue-800">
                                            Create your first inspection
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                <?php echo e($inspections->links()); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\qc\inspections\index.blade.php ENDPATH**/ ?>