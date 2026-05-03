<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Housekeeping Tasks']); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Task Management</h1>
                <p class="mt-1 text-sm text-gray-600">Manage and track housekeeping tasks</p>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="space-y-6">
        
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <select name="status" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="in_progress" <?php echo e(request('status') === 'in_progress' ? 'selected' : ''); ?>>In
                            Progress</option>
                        <option value="completed" <?php echo e(request('status') === 'completed' ? 'selected' : ''); ?>>Completed
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                    <select name="type" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">All Types</option>
                        <option value="checkout_clean" <?php echo e(request('type') === 'checkout_clean' ? 'selected' : ''); ?>>
                            Checkout Clean</option>
                        <option value="stay_clean" <?php echo e(request('type') === 'stay_clean' ? 'selected' : ''); ?>>Stay Clean
                        </option>
                        <option value="deep_clean" <?php echo e(request('type') === 'deep_clean' ? 'selected' : ''); ?>>Deep Clean
                        </option>
                        <option value="inspection" <?php echo e(request('type') === 'inspection' ? 'selected' : ''); ?>>Inspection
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Assigned To</label>
                    <select name="assigned_to" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">All Staff</option>
                        <?php $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user->id); ?>"
                                <?php echo e(request('assigned_to') == $user->id ? 'selected' : ''); ?>><?php echo e($user->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="button"
                        onclick="window.location.href='<?php echo e(route('hotel.housekeeping.tasks.index')); ?>'"
                        class="w-full px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300">
                        Reset Filters
                    </button>
                </div>
            </form>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Room</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Type</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Priority</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Status</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Assigned To</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Scheduled</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $tasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo e($task->room?->number); ?></p>
                                        <p class="text-xs text-gray-600">
                                            <?php echo e($task->room?->roomType?->name); ?></p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?php echo e(ucwords(str_replace('_', ' ', $task->type))); ?></td>
                                <td class="px-4 py-3">
                                    <span
                                        class="text-xs px-2 py-1 rounded-full <?php echo e($task->priority === 'urgent'
                                            ? 'bg-red-100 text-red-700'
                                            : ($task->priority === 'high'
                                                ? 'bg-orange-100 text-orange-700'
                                                : ($task->priority === 'normal'
                                                    ? 'bg-blue-100 text-blue-700'
                                                    : 'bg-gray-100 text-gray-700'))); ?>">
                                        <?php echo e(ucfirst($task->priority)); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="text-xs px-2 py-1 rounded-full <?php echo e($task->status === 'completed'
                                            ? 'bg-green-100 text-green-700'
                                            : ($task->status === 'in_progress'
                                                ? 'bg-blue-100 text-blue-700'
                                                : 'bg-yellow-100 text-yellow-700')); ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $task->status))); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?php echo e($task->assignedTo?->name ?? 'Unassigned'); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?php echo e($task->scheduled_at?->format('M d, H:i')); ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <?php if($task->status === 'pending'): ?>
                                            <button onclick="startTask(<?php echo e($task->id); ?>)"
                                                class="text-xs px-3 py-1 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Start</button>
                                        <?php elseif($task->status === 'in_progress'): ?>
                                            <button onclick="openCompleteModal(<?php echo e($task->id); ?>)"
                                                class="text-xs px-3 py-1 rounded-lg bg-green-600 text-white hover:bg-green-700">Complete</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7"
                                    class="px-4 py-8 text-center text-sm text-gray-500">No tasks
                                    found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <div class="px-4 py-3 border-t border-gray-200">
                <?php echo e($tasks->links()); ?>

            </div>
        </div>
    </div>

    
    <div id="modal-complete-task" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6">
            <form id="form-complete-task" method="POST">
                <?php echo csrf_field(); ?>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Complete Task</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Checklist
                            Items</label>
                        <textarea name="checklist" rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter completed checklist items (one per line)"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                        <textarea name="notes" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Additional notes"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeCompleteModal()"
                        class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Complete
                        Task</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function startTask(taskId) {
                if (confirm('Start this task?')) {
                    fetch(`/hotel/housekeeping/tasks/${taskId}/start`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        }
                    }).then(response => {
                        if (response.ok) location.reload();
                    });
                }
            }

            function openCompleteModal(taskId) {
                document.getElementById('form-complete-task').action = `/hotel/housekeeping/tasks/${taskId}/complete`;
                document.getElementById('modal-complete-task').classList.remove('hidden');
            }

            function closeCompleteModal() {
                document.getElementById('modal-complete-task').classList.add('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\housekeeping\tasks\index.blade.php ENDPATH**/ ?>