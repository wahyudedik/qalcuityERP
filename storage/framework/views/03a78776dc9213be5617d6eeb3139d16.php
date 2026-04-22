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
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('Ministry Report #' . $report->id)); ?>

            </h2>
            <a href="<?php echo e(route('healthcare.ministry-reports.index')); ?>" class="text-blue-600 hover:text-blue-900"><i
                    class="fas fa-arrow-left mr-2"></i>Back to List</a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Information</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Report ID</dt>
                        <dd class="mt-1 text-lg font-bold text-gray-900">#<?php echo e($report->id); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Report Type</dt>
                        <dd class="mt-1">
                            <span
                                class="px-3 py-1 text-sm font-semibold rounded-full <?php echo e($report->report_type === 'monthly' ? 'bg-blue-100 text-blue-800' : ($report->report_type === 'quarterly' ? 'bg-green-100 text-green-800' : ($report->report_type === 'annual' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800'))); ?>"><?php echo e(ucfirst($report->report_type)); ?></span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Reporting Period</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <?php echo e(\Carbon\Carbon::parse($report->reporting_period)->format('F Y')); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <?php if($report->status === 'draft'): ?>
                                <span
                                    class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Draft</span>
                            <?php else: ?>
                                <span
                                    class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Submitted</span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo e($report->created_at->format('d/m/Y H:i')); ?></dd>
                    </div>
                    <?php if($report->submitted_at): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Submitted At</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($report->submitted_at->format('d/m/Y H:i')); ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Data</h3>
                <pre class="bg-gray-50 p-4 rounded text-sm overflow-x-auto"><?php echo e(json_encode($report->report_data, JSON_PRETTY_PRINT)); ?></pre>
            </div>

            <?php if($report->notes): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
                    <p class="text-sm text-gray-700"><?php echo e($report->notes); ?></p>
                </div>
            <?php endif; ?>

            <?php if($report->status === 'draft'): ?>
                <div class="flex justify-end space-x-3">
                    <button onclick="submitReport(<?php echo e($report->id); ?>)"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"><i
                            class="fas fa-paper-plane mr-2"></i>Submit to Ministry</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function submitReport(id) {
            if (confirm('Submit this report to Ministry of Health?')) {
                fetch(`<?php echo e(route('healthcare.ministry-reports.submit', '')); ?>/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        Toast.success(data.message);
                        setTimeout(() => location.reload(), 1500);
                    })
                    .catch(error => Toast.error('Submit failed'));
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\ministry-reports\show.blade.php ENDPATH**/ ?>