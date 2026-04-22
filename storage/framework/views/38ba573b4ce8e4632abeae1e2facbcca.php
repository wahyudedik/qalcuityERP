<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Maintenance Requests']); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Maintenance Requests</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-slate-400">Track and manage maintenance issues</p>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="space-y-6">
        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <form method="GET" class="flex flex-wrap gap-4">
                <select name="status" onchange="this.form.submit()"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">All Status</option>
                    <option value="reported" <?php echo e(request('status') === 'reported' ? 'selected' : ''); ?>>Reported</option>
                    <option value="in_progress" <?php echo e(request('status') === 'in_progress' ? 'selected' : ''); ?>>In Progress
                    </option>
                    <option value="completed" <?php echo e(request('status') === 'completed' ? 'selected' : ''); ?>>Completed
                    </option>
                </select>

                <select name="priority" onchange="this.form.submit()"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">All Priorities</option>
                    <option value="urgent" <?php echo e(request('priority') === 'urgent' ? 'selected' : ''); ?>>Urgent</option>
                    <option value="high" <?php echo e(request('priority') === 'high' ? 'selected' : ''); ?>>High</option>
                    <option value="normal" <?php echo e(request('priority') === 'normal' ? 'selected' : ''); ?>>Normal</option>
                    <option value="low" <?php echo e(request('priority') === 'low' ? 'selected' : ''); ?>>Low</option>
                </select>
            </form>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Room</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Title</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Category</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Priority</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Status</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Assigned To</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                    <?php echo e($request->room?->number); ?></td>
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo e($request->title); ?></p>
                                        <p class="text-xs text-gray-600 dark:text-slate-400">
                                            <?php echo e($request->created_at->diffForHumans()); ?></p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-slate-400">
                                    <?php echo e($request->category); ?></td>
                                <td class="px-4 py-3">
                                    <span
                                        class="text-xs px-2 py-1 rounded-full <?php echo e($request->priority === 'urgent'
                                            ? 'bg-red-100 text-red-700'
                                            : ($request->priority === 'high'
                                                ? 'bg-orange-100 text-orange-700'
                                                : ($request->priority === 'normal'
                                                    ? 'bg-blue-100 text-blue-700'
                                                    : 'bg-gray-100 text-gray-700'))); ?>">
                                        <?php echo e(ucfirst($request->priority)); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="text-xs px-2 py-1 rounded-full <?php echo e($request->status === 'completed'
                                            ? 'bg-green-100 text-green-700'
                                            : ($request->status === 'in_progress'
                                                ? 'bg-blue-100 text-blue-700'
                                                : 'bg-yellow-100 text-yellow-700')); ?>">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $request->status))); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-slate-400">
                                    <?php echo e($request->assignedTo?->name ?? 'Unassigned'); ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <?php if($request->status === 'reported'): ?>
                                            <button onclick="assignRequest(<?php echo e($request->id); ?>)"
                                                class="text-xs px-3 py-1 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Assign</button>
                                        <?php elseif($request->status === 'in_progress'): ?>
                                            <button onclick="openCompleteModal(<?php echo e($request->id); ?>)"
                                                class="text-xs px-3 py-1 rounded-lg bg-green-600 text-white hover:bg-green-700">Complete</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7"
                                    class="px-4 py-8 text-center text-sm text-gray-500 dark:text-slate-400">No
                                    maintenance requests found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                <?php echo e($requests->links()); ?>

            </div>
        </div>
    </div>

    
    <div id="modal-complete" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl max-w-lg w-full p-6">
            <form id="form-complete" method="POST">
                <?php echo csrf_field(); ?>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Complete Maintenance Request</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Resolution Notes
                            *</label>
                        <textarea name="resolution_notes" required rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"
                            placeholder="Describe what was done to fix the issue"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Cost
                            (Optional)</label>
                        <input type="number" name="cost" step="0.01" min="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"
                            placeholder="0.00">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeCompleteModal()"
                        class="px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-white/5 rounded-xl">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Complete</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function assignRequest(requestId) {
                const staffId = prompt('Enter technician user ID to assign:');
                if (staffId) {
                    fetch(`/hotel/housekeeping/maintenance/${requestId}/assign`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({
                            assigned_to: staffId
                        })
                    }).then(response => {
                        if (response.ok) location.reload();
                    });
                }
            }

            function openCompleteModal(requestId) {
                document.getElementById('form-complete').action = `/hotel/housekeeping/maintenance/${requestId}/complete`;
                document.getElementById('modal-complete').classList.remove('hidden');
            }

            function closeCompleteModal() {
                document.getElementById('modal-complete').classList.add('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\housekeeping\maintenance\index.blade.php ENDPATH**/ ?>