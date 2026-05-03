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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Approval Workflow')); ?> - <?php echo e($document->title); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('documents.index')); ?>"
                    class="text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Document Status -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Document Status</p>
                        <p class="text-2xl font-semibold mt-1">
                            <?php
                                $statusColors = [
                                    'draft' => 'text-gray-600',
                                    'pending_approval' => 'text-yellow-600',
                                    'approved' => 'text-green-600',
                                    'rejected' => 'text-red-600',
                                ];
                            ?>
                            <span class="<?php echo e($statusColors[$document->status] ?? 'text-gray-600'); ?>">
                                <?php echo e(ucfirst(str_replace('_', ' ', $document->status))); ?>

                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Approved By</p>
                        <p class="text-2xl font-semibold mt-1 text-gray-900">
                            <?php echo e($history['approved_by'] ?? 'Not yet'); ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Approved At</p>
                        <p class="text-2xl font-semibold mt-1 text-gray-900">
                            <?php echo e($history['approved_at'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Version</p>
                        <p class="text-2xl font-semibold mt-1 text-blue-600">v<?php echo e($document->version); ?></p>
                    </div>
                </div>
                <?php if($history['approval_notes']): ?>
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-700"><strong>Notes:</strong>
                            <?php echo e($history['approval_notes']); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Approval Steps -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Approval Steps</h3>

                    <div class="space-y-4">
                        <?php $__empty_1 = true; $__currentLoopData = $history['steps']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div
                                class="flex items-start space-x-4 p-4 border border-gray-200 rounded-lg <?php if($step['status'] === 'approved'): ?> bg-green-50 <?php elseif($step['status'] === 'rejected'): ?> bg-red-50 <?php else: ?> bg-white <?php endif; ?>">
                                <div class="flex-shrink-0">
                                    <?php if($step['status'] === 'approved'): ?>
                                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    <?php elseif($step['status'] === 'rejected'): ?>
                                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900">Step
                                            <?php echo e($step['step_number']); ?></p>
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full <?php if($step['status'] === 'approved'): ?> bg-green-100 text-green-800 <?php elseif($step['status'] === 'rejected'): ?> bg-red-100 text-red-800 <?php else: ?> bg-yellow-100 text-yellow-800 <?php endif; ?>">
                                            <?php echo e(ucfirst($step['status'])); ?>

                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">Approver:
                                        <?php echo e($step['approver']); ?></p>
                                    <?php if($step['comments']): ?>
                                        <p
                                            class="mt-2 text-sm text-gray-700 bg-white p-2 rounded">
                                            <strong>Comments:</strong> <?php echo e($step['comments']); ?>

                                        </p>
                                    <?php endif; ?>
                                    <?php if($step['actioned_at']): ?>
                                        <p class="mt-1 text-xs text-gray-400">Actioned at: <?php echo e($step['actioned_at']); ?>

                                        </p>
                                    <?php endif; ?>
                                </div>
                                <?php if($step['status'] === 'pending' && $document->isPendingApproval()): ?>
                                    <div class="flex-shrink-0 space-x-2">
                                        <button
                                            onclick="document.getElementById('approveModal<?php echo e($step['step_number']); ?>').showModal()"
                                            class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                            Approve
                                        </button>
                                        <button
                                            onclick="document.getElementById('rejectModal<?php echo e($step['step_number']); ?>').showModal()"
                                            class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                            Reject
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Approve Modal -->
                            <dialog id="approveModal<?php echo e($step['step_number']); ?>" class="modal rounded-lg shadow-xl p-0">
                                <div class="bg-white w-full max-w-md p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Approve Step
                                        <?php echo e($step['step_number']); ?></h3>
                                    <form method="POST"
                                        action="<?php echo e(route('documents.approval.approve', [$document, $step['step_number']])); ?>">
                                        <?php echo csrf_field(); ?>
                                        <div class="mb-4">
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-1">Comments
                                                (optional)</label>
                                            <textarea name="comments" rows="3"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"></textarea>
                                        </div>
                                        <div class="flex justify-end space-x-3">
                                            <button type="button"
                                                onclick="document.getElementById('approveModal<?php echo e($step['step_number']); ?>').close()"
                                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                                Approve
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </dialog>

                            <!-- Reject Modal -->
                            <dialog id="rejectModal<?php echo e($step['step_number']); ?>" class="modal rounded-lg shadow-xl p-0">
                                <div class="bg-white w-full max-w-md p-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Step
                                        <?php echo e($step['step_number']); ?></h3>
                                    <form method="POST"
                                        action="<?php echo e(route('documents.approval.reject', [$document, $step['step_number']])); ?>">
                                        <?php echo csrf_field(); ?>
                                        <div class="mb-4">
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-1">Reason
                                                for rejection *</label>
                                            <textarea name="comments" rows="3" required
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
                                        </div>
                                        <div class="flex justify-end space-x-3">
                                            <button type="button"
                                                onclick="document.getElementById('rejectModal<?php echo e($step['step_number']); ?>').close()"
                                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                                Reject
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </dialog>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center py-8">
                                <p class="text-gray-500">No approval steps configured</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Submit for Approval -->
            <?php if($document->status === 'draft'): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form method="POST" action="<?php echo e(route('documents.approval.submit', $document)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                            class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700"
                            onclick="return confirm('Submit document for approval?')">
                            Submit for Approval
                        </button>
                    </form>
                </div>
            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\documents\approval-workflow.blade.php ENDPATH**/ ?>