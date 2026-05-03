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
     <?php $__env->slot('header', null, []); ?> Housekeeping Board <?php $__env->endSlot(); ?>

    <div x-data="housekeepingBoard()" class="space-y-6">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Housekeeping Board</h1>
                <p class="text-sm text-gray-500">Today's tasks •
                    <?php echo e($board['pending']->count() + $board['in_progress']->count() + $board['completed']->count() + $board['inspected']->count()); ?>

                    total</p>
            </div>
            <button @click="showNewTaskModal = true"
                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Task
            </button>
        </div>

        
        <div class="overflow-x-auto pb-4">
            <div class="flex gap-4 min-w-max">
                
                <div class="w-72 flex-shrink-0">
                    <div
                        class="bg-amber-50 rounded-t-2xl px-4 py-3 border-b border-amber-200">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-amber-800">Pending</h3>
                            <span
                                class="px-2 py-0.5 text-xs font-medium bg-amber-200 text-amber-800 rounded-full"><?php echo e($board['pending']->count()); ?></span>
                        </div>
                    </div>
                    <div
                        class="bg-white rounded-b-2xl border border-t-0 border-amber-200 p-3 space-y-3 min-h-[200px]">
                        <?php $__empty_1 = true; $__currentLoopData = $board['pending']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php echo $__env->make('hotel.housekeeping.partials.task-card', ['task' => $task], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center py-8 text-gray-400 text-sm">
                                No pending tasks
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="w-72 flex-shrink-0">
                    <div
                        class="bg-blue-50 rounded-t-2xl px-4 py-3 border-b border-blue-200">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-blue-800">In Progress</h3>
                            <span
                                class="px-2 py-0.5 text-xs font-medium bg-blue-200 text-blue-800 rounded-full"><?php echo e($board['in_progress']->count()); ?></span>
                        </div>
                    </div>
                    <div
                        class="bg-white rounded-b-2xl border border-t-0 border-blue-200 p-3 space-y-3 min-h-[200px]">
                        <?php $__empty_1 = true; $__currentLoopData = $board['in_progress']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php echo $__env->make('hotel.housekeeping.partials.task-card', ['task' => $task], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center py-8 text-gray-400 text-sm">
                                No tasks in progress
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="w-72 flex-shrink-0">
                    <div
                        class="bg-green-50 rounded-t-2xl px-4 py-3 border-b border-green-200">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-green-800">Completed</h3>
                            <span
                                class="px-2 py-0.5 text-xs font-medium bg-green-200 text-green-800 rounded-full"><?php echo e($board['completed']->count()); ?></span>
                        </div>
                    </div>
                    <div
                        class="bg-white rounded-b-2xl border border-t-0 border-green-200 p-3 space-y-3 min-h-[200px]">
                        <?php $__empty_1 = true; $__currentLoopData = $board['completed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php echo $__env->make('hotel.housekeeping.partials.task-card', ['task' => $task], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center py-8 text-gray-400 text-sm">
                                No completed tasks
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="w-72 flex-shrink-0">
                    <div
                        class="bg-gray-100 rounded-t-2xl px-4 py-3 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-gray-700">Inspected</h3>
                            <span
                                class="px-2 py-0.5 text-xs font-medium bg-gray-200 text-gray-700 rounded-full"><?php echo e($board['inspected']->count()); ?></span>
                        </div>
                    </div>
                    <div
                        class="bg-white rounded-b-2xl border border-t-0 border-gray-200 p-3 space-y-3 min-h-[200px]">
                        <?php $__empty_1 = true; $__currentLoopData = $board['inspected']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php echo $__env->make('hotel.housekeeping.partials.task-card', ['task' => $task], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center py-8 text-gray-400 text-sm">
                                No inspected tasks
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        
        <div x-show="showNewTaskModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            @click.self="showNewTaskModal = false">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-xl" @click.stop>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">New Housekeeping Task</h3>
                    <button @click="showNewTaskModal = false"
                        class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <form method="POST" action="<?php echo e(route('hotel.housekeeping.store')); ?>" class="p-6 space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Room *</label>
                        <select name="room_id" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select room</option>
                            <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($room->id); ?>">Room <?php echo e($room->number); ?> —
                                    <?php echo e($room->roomType?->name ?? 'N/A'); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Task Type
                            *</label>
                        <select name="type" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php $__currentLoopData = $taskTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $type))); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Priority
                            *</label>
                        <select name="priority" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php $__currentLoopData = $priorities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $priority): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($priority); ?>" <?php if($priority === 'normal'): echo 'selected'; endif; ?>>
                                    <?php echo e(ucfirst($priority)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Assigned
                            To</label>
                        <select name="assigned_to"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Unassigned</option>
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Notes</label>
                        <textarea name="notes" rows="2" placeholder="Optional notes..."
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showNewTaskModal = false"
                            class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Create
                            Task</button>
                    </div>
                </form>
            </div>
        </div>

        
        <div x-show="showAssignModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            @click.self="showAssignModal = false">
            <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl" @click.stop>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Assign Task</h3>
                    <button @click="showAssignModal = false"
                        class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <form :action="assignUrl" method="POST" class="p-6 space-y-4">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Assign to
                            *</label>
                        <select name="assigned_to" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select staff</option>
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showAssignModal = false"
                            class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <script>
        // Define housekeepingBoard component for Alpine.js
        window.housekeepingBoard = function() {
            return {
                showNewTaskModal: false,
                showAssignModal: false,
                assignUrl: '',

                openAssignModal(taskId) {
                    this.assignUrl = '<?php echo e(url('hotel/housekeeping/tasks')); ?>/' + taskId + '/assign';
                    this.showAssignModal = true;
                },
            }
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\housekeeping\room-board.blade.php ENDPATH**/ ?>