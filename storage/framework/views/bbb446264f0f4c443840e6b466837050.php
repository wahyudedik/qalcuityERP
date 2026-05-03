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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Triage Assessments')); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.triage.create')); ?>"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>New Assessment
            </a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-600 rounded-md p-3">
                            <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Critical (T1)</p>
                            <p class="text-2xl font-semibold text-red-600"><?php echo e($statistics['critical'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
                            <i class="fas fa-ambulance text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Emergency (T2)</p>
                            <p class="text-2xl font-semibold text-orange-600"><?php echo e($statistics['emergency'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <i class="fas fa-clock text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Urgent (T3)</p>
                            <p class="text-2xl font-semibold text-yellow-600"><?php echo e($statistics['urgent'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <i class="fas fa-user-md text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Semi-Urgent (T4)</p>
                            <p class="text-2xl font-semibold text-green-600"><?php echo e($statistics['semi_urgent'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <i class="fas fa-clipboard-check text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Non-Urgent (T5)</p>
                            <p class="text-2xl font-semibold text-blue-600"><?php echo e($statistics['non_urgent'] ?? 0); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Triage Queue Link -->
            <div class="bg-gradient-to-r from-red-500 to-orange-500 overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-list-ol text-white text-3xl mr-4"></i>
                        <div>
                            <h3 class="text-lg font-semibold text-white">View Priority Queue</h3>
                            <p class="text-sm text-white opacity-90">See patients sorted by triage priority</p>
                        </div>
                    </div>
                    <a href="<?php echo e(route('healthcare.triage.queue')); ?>"
                        class="px-4 py-2 bg-white text-red-600 rounded-md font-semibold hover:bg-gray-100">
                        <i class="fas fa-arrow-right mr-2"></i>View Queue
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="<?php echo e(route('healthcare.triage.index')); ?>" class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority Level</label>
                        <select name="priority_level"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Priorities</option>
                            <option value="critical" <?php if(request('priority_level') === 'critical'): echo 'selected'; endif; ?>>Critical (T1-RED)</option>
                            <option value="emergency" <?php if(request('priority_level') === 'emergency'): echo 'selected'; endif; ?>>Emergency (T2-ORANGE)</option>
                            <option value="urgent" <?php if(request('priority_level') === 'urgent'): echo 'selected'; endif; ?>>Urgent (T3-YELLOW)</option>
                            <option value="semi_urgent" <?php if(request('priority_level') === 'semi_urgent'): echo 'selected'; endif; ?>>Semi-Urgent (T4-GREEN)</option>
                            <option value="non_urgent" <?php if(request('priority_level') === 'non_urgent'): echo 'selected'; endif; ?>>Non-Urgent (T5-BLUE)</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>>Pending</option>
                            <option value="in_progress" <?php if(request('status') === 'in_progress'): echo 'selected'; endif; ?>>In Progress</option>
                            <option value="completed" <?php if(request('status') === 'completed'): echo 'selected'; endif; ?>>Completed</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Triage Assessments Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Assessment Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nurse</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $assessments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assessment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo e($assessment->triage_code); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo e($assessment->patient->name ?? 'N/A'); ?></div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo e($assessment->patient->medical_record_number ?? ''); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                    <?php if($assessment->priority_level === 'critical'): ?> bg-red-100 text-red-800
                                    <?php elseif($assessment->priority_level === 'emergency'): ?> bg-orange-100 text-orange-800
                                    <?php elseif($assessment->priority_level === 'urgent'): ?> bg-yellow-100 text-yellow-800
                                    <?php elseif($assessment->priority_level === 'semi_urgent'): ?> bg-green-100 text-green-800
                                    <?php else: ?> bg-blue-100 text-blue-800 <?php endif; ?>">
                                        <?php echo e($assessment->triage_code); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($assessment->assessment_time ? $assessment->assessment_time->format('d/m/Y H:i') : '-'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo e($assessment->nurse->name ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    <?php if($assessment->status === 'pending'): ?> bg-gray-100 text-gray-800
                                    <?php elseif($assessment->status === 'in_progress'): ?> bg-blue-100 text-blue-800
                                    <?php else: ?> bg-green-100 text-green-800 <?php endif; ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $assessment->status))); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="<?php echo e(route('healthcare.triage.show', $assessment)); ?>"
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('healthcare.triage.edit', $assessment)); ?>"
                                        class="text-yellow-600 hover:text-yellow-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="deleteAssessment(<?php echo e($assessment->id); ?>)"
                                        class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-assessment-<?php echo e($assessment->id); ?>"
                                        action="<?php echo e(route('healthcare.triage.destroy', $assessment)); ?>" method="POST"
                                        class="hidden">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No triage assessments
                                    found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if($assessments->hasPages()): ?>
                    <div class="px-6 py-4 border-t border-gray-200">
                        <?php echo e($assessments->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function deleteAssessment(id) {
                if (confirm('Are you sure you want to delete this assessment?')) {
                    document.getElementById(`delete-assessment-${id}`).submit();
                }
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\triage\index.blade.php ENDPATH**/ ?>