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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Compliance Report Details')); ?> -
                <?php echo e($report->report_number); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.compliance-reports.index')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <?php if(session('success')): ?>
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-file-alt mr-2 text-blue-600"></i>Report Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Report Number</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900"><?php echo e($report->report_number); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Report Type</dt>
                            <dd class="mt-1"><span
                                    class="px-2 py-1 text-sm font-semibold rounded-full <?php echo e($report->report_type === 'hipaa' ? 'bg-blue-100 text-blue-800' : ($report->report_type === 'jci' ? 'bg-green-100 text-green-800' : ($report->report_type === 'iso' ? 'bg-purple-100 text-purple-800' : ($report->report_type === 'regulatory' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800')))); ?>"><?php echo e(strtoupper($report->report_type)); ?></span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Report Date</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($report->report_date->format('d/m/Y')); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Reporting Period</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($report->reporting_period_start->format('d/m/Y')); ?> -
                                <?php echo e($report->reporting_period_end->format('d/m/Y')); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <?php if($report->status === 'draft'): ?>
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Draft</span>
                                <?php elseif($report->status === 'pending_review'): ?>
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">Pending
                                        Review</span>
                                <?php else: ?>
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-user mr-2 text-purple-600"></i>Workflow Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created By</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($report->createdBy->name ?? 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($report->created_at->format('d/m/Y H:i')); ?></dd>
                        </div>
                        <?php if($report->submitted_at): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Submitted At</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($report->submitted_at->format('d/m/Y H:i')); ?>

                                </dd>
                            </div>
                        <?php endif; ?>
                        <?php if($report->approved_at): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Approved By</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($report->reviewer->name ?? 'N/A'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Approved At</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($report->approved_at->format('d/m/Y H:i')); ?>

                                </dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <?php if($report->findings): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-search mr-2 text-orange-600"></i>Findings</h3>
                    <div class="space-y-2">
                        <?php if(is_array($report->findings)): ?>
                            <?php $__currentLoopData = $report->findings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $finding): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-start">
                                    <span
                                        class="flex-shrink-0 w-6 h-6 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center text-xs font-semibold mr-3"><?php echo e($index + 1); ?></span>
                                    <p class="text-sm text-gray-700"><?php echo e($finding); ?></p>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <p class="text-sm text-gray-700"><?php echo e($report->findings); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($report->recommendations): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-lightbulb mr-2 text-yellow-600"></i>Recommendations</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($report->recommendations); ?></p>
                </div>
            <?php endif; ?>

            <?php if($report->notes): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-sticky-note mr-2 text-green-600"></i>Additional Notes</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($report->notes); ?></p>
                </div>
            <?php endif; ?>

            <?php if($report->review_notes): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-clipboard-check mr-2 text-blue-600"></i>Review Notes</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($report->review_notes); ?></p>
                </div>
            <?php endif; ?>

            <?php if($report->status === 'draft'): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-tasks mr-2 text-indigo-600"></i>Actions</h3>
                    <button onclick="submitForReview()"
                        class="px-6 py-3 bg-purple-600 text-white rounded-md hover:bg-purple-700"><i
                            class="fas fa-paper-plane mr-2"></i>Submit for Review</button>
                </div>
            <?php elseif($report->status === 'pending_review'): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-tasks mr-2 text-indigo-600"></i>Review Actions</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="review_notes" class="block text-sm font-medium text-gray-700 mb-2">Review
                                Notes</label>
                            <textarea id="review_notes" rows="3"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter review notes..."></textarea>
                        </div>
                        <button onclick="approveReport()"
                            class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700"><i
                                class="fas fa-check mr-2"></i>Approve Report</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function submitForReview() {
            if (confirm('Submit this report for review?')) {
                fetch('<?php echo e(route('healthcare.compliance-reports.submit-for-review', $report)); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        location.reload();
                    })
                    .catch(error => alert('Submit failed'));
            }
        }

        function approveReport() {
            const reviewNotes = document.getElementById('review_notes').value;

            if (confirm('Approve this report?')) {
                fetch('<?php echo e(route('healthcare.compliance-reports.approve', $report)); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({
                            review_notes: reviewNotes
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        location.reload();
                    })
                    .catch(error => alert('Approval failed'));
            }
        }
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\compliance-reports\show.blade.php ENDPATH**/ ?>