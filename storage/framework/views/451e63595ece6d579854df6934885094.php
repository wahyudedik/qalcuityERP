

<?php $__env->startSection('title', 'Gantt Chart - ' . $project->name); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e($project->name); ?></h1>
                    <p class="text-sm text-gray-600 mt-1">Project Number: <?php echo e($project->number); ?></p>
                </div>
                <div class="flex space-x-3">
                    <a href="<?php echo e(route('construction.gantt.export', $project->id)); ?>"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Export JSON
                    </a>
                    <a href="<?php echo e(route('projects.show', $project->id)); ?>"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Back to Project
                    </a>
                </div>
            </div>
        </div>

        <!-- Project Timeline Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="text-sm text-gray-600">Total Duration</div>
                <div class="text-2xl font-bold text-blue-700"><?php echo e($ganttData['timeline']['total_days']); ?> days</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="text-sm text-gray-600">Elapsed</div>
                <div class="text-2xl font-bold text-green-700"><?php echo e($ganttData['timeline']['elapsed_days']); ?> days</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
                <div class="text-sm text-gray-600">Remaining</div>
                <div class="text-2xl font-bold text-yellow-700"><?php echo e($ganttData['timeline']['remaining_days']); ?> days</div>
            </div>
            <div
                class="bg-white rounded-lg shadow p-4 border-l-4 <?php echo e($ganttData['timeline']['is_overdue'] ? 'border-red-500' : 'border-purple-500'); ?>">
                <div class="text-sm text-gray-600">Status</div>
                <div
                    class="text-2xl font-bold <?php echo e($ganttData['timeline']['is_overdue'] ? 'text-red-700' : 'text-purple-700'); ?>">
                    <?php echo e($ganttData['timeline']['is_overdue'] ? 'OVERDUE' : 'On Track'); ?>

                </div>
            </div>
        </div>

        <!-- Conflicts Alert -->
        <?php if($conflicts['has_conflicts']): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-red-800">Scheduling Conflicts Detected</h3>
                        <ul class="mt-2 text-sm text-red-700">
                            <?php $__currentLoopData = $conflicts['conflicts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conflict): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>• <?php echo e($conflict['message']); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Gantt Chart Container -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Project Timeline</h2>
            </div>

            <div class="p-6">
                <!-- Simple Gantt Visualization using HTML/CSS -->
                <div class="space-y-4">
                    <?php $__empty_1 = true; $__currentLoopData = $ganttData['tasks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="relative">
                            <div class="flex items-center mb-2">
                                <div class="w-1/3 pr-4">
                                    <div class="flex items-center">
                                        <?php if($task['is_milestone']): ?>
                                            <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                                </path>
                                            </svg>
                                        <?php endif; ?>
                                        <span class="font-medium text-sm"><?php echo e($task['name']); ?></span>
                                    </div>
                                    <div class="text-xs text-gray-500 ml-7">
                                        <?php echo e($task['assigned_to']); ?> • Weight: <?php echo e($task['weight']); ?>%
                                    </div>
                                </div>
                                <div class="w-2/3">
                                    <div class="relative h-8 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="absolute top-0 left-0 h-full bg-<?php echo e($task['status'] === 'done' ? 'green' : ($task['status'] === 'in_progress' ? 'blue' : 'gray')); ?>-500 rounded-full transition-all duration-500"
                                            style="width: <?php echo e($task['progress']); ?>%">
                                        </div>
                                        <div
                                            class="absolute inset-0 flex items-center justify-center text-xs font-medium text-gray-700">
                                            <?php echo e($task['progress']); ?>%
                                        </div>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span><?php echo e($task['start']); ?></span>
                                        <span><?php echo e($task['end'] ?? 'No deadline'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-12 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                            <p class="mt-2">No tasks found for this project.</p>
                            <a href="<?php echo e(route('projects.tasks.create', $project->id)); ?>"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Add First Task
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Critical Path Section -->
        <?php if(!empty($ganttData['critical_path'])): ?>
            <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Critical Path Tasks</h2>
                    <p class="text-sm text-gray-600 mt-1">High-priority tasks that may impact project timeline</p>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <?php $__currentLoopData = $ganttData['critical_path']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div
                                class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo e($task['name']); ?></div>
                                    <div class="text-sm text-gray-600">Weight: <?php echo e($task['weight']); ?>% • Due:
                                        <?php echo e($task['due_date'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-yellow-700"><?php echo e($task['progress']); ?>%</div>
                                    <div class="text-xs text-gray-500">Complete</div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\construction\gantt\index.blade.php ENDPATH**/ ?>