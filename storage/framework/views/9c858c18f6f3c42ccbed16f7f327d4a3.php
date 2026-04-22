

<?php $__env->startSection('title', $workflow->name); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="<?php echo e(route('automation.workflows.index')); ?>" class="text-blue-600 hover:text-blue-900 text-sm">
                ← Back to Workflows
            </a>
            <div class="flex justify-between items-start mt-2">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e($workflow->name); ?></h1>
                    <p class="mt-1 text-sm text-gray-600"><?php echo e($workflow->description); ?></p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="testWorkflow(<?php echo e($workflow->id); ?>)"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Test Workflow
                    </button>
                    <form action="<?php echo e(route('automation.workflows.destroy', $workflow)); ?>" method="POST"
                        onsubmit="return confirm('Delete this workflow?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Workflow Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Trigger Type</dt>
                <dd class="mt-1 text-lg font-semibold text-gray-900 capitalize"><?php echo e($workflow->trigger_type); ?></dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="mt-1">
                    <span
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo e($workflow->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); ?>">
                        <?php echo e($workflow->is_active ? 'Active' : 'Inactive'); ?>

                    </span>
                </dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Priority</dt>
                <dd class="mt-1 text-lg font-semibold text-gray-900"><?php echo e($workflow->priority); ?></dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Total Executions</dt>
                <dd class="mt-1 text-lg font-semibold text-gray-900"><?php echo e($workflow->execution_count); ?></dd>
            </div>
        </div>

        <!-- Actions List -->
        <div class="bg-white shadow sm:rounded-lg mb-8">
            <div class="px-4 py-5 sm:p-6 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Actions (<?php echo e($actions->count()); ?>)</h3>
                <button onclick="showAddActionModal()"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Add Action
                </button>
            </div>
            <ul class="divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <span class="text-sm font-medium text-gray-900">#<?php echo e($action->order); ?></span>
                                    <span
                                        class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?php echo e(str_replace('_', ' ', $action->action_type)); ?>

                                    </span>
                                    <?php if(!$action->is_active): ?>
                                        <span
                                            class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if($action->condition): ?>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Condition: <?php echo e($action->condition['field'] ?? ''); ?>

                                        <?php echo e($action->condition['operator'] ?? ''); ?> <?php echo e($action->condition['value'] ?? ''); ?>

                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <button onclick="deleteAction(<?php echo e($action->id); ?>)"
                                    class="text-red-600 hover:text-red-900 text-sm">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li class="px-4 py-12 text-center text-sm text-gray-500">
                        No actions configured yet. Click "Add Action" to get started.
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Recent Execution Logs -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Executions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Triggered By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Started At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo e($log->triggered_by); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo e($log->status === 'success' ? 'bg-green-100 text-green-800' : ($log->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')); ?>">
                                        <?php echo e(ucfirst($log->status)); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($log->duration_ms ? $log->duration_ms . ' ms' : '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($log->started_at->diffForHumans()); ?></td>
                                <td class="px-6 py-4 text-sm text-red-600"><?php echo e(Str::limit($log->error_message, 50)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No execution logs yet
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Test Result Modal -->
    <div id="testResultModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeTestModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Test Result</h3>
                    <div class="mt-2">
                        <pre id="testResultContent" class="text-sm text-gray-700 bg-gray-50 p-4 rounded overflow-auto max-h-96"></pre>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeTestModal()"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function testWorkflow(workflowId) {
            fetch(`/automation/workflows/${workflowId}/test`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('testResultContent').textContent = JSON.stringify(data, null, 2);
                    document.getElementById('testResultModal').classList.remove('hidden');
                });
        }

        function closeTestModal() {
            document.getElementById('testResultModal').classList.add('hidden');
        }

        function deleteAction(actionId) {
            if (!confirm('Delete this action?')) return;

            fetch(`/automation/actions/${actionId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Content-Type': 'application/json',
                    },
                })
                .then(() => location.reload());
        }

        function showAddActionModal() {
            alert('Add Action modal - To be implemented with form for action configuration');
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\automation\workflows\show.blade.php ENDPATH**/ ?>