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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Ministry Reports')); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.ministry-reports.create')); ?>"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                    class="fas fa-plus mr-2"></i>New Report</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <?php if(session('success')): ?>
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="<?php echo e(route('healthcare.ministry-reports.index')); ?>"
                        class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                            <select name="report_type" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Types</option>
                                <option value="monthly" <?php echo e(request('report_type') === 'monthly' ? 'selected' : ''); ?>>
                                    Monthly</option>
                                <option value="quarterly"
                                    <?php echo e(request('report_type') === 'quarterly' ? 'selected' : ''); ?>>Quarterly</option>
                                <option value="annual" <?php echo e(request('report_type') === 'annual' ? 'selected' : ''); ?>>
                                    Annual</option>
                                <option value="episode" <?php echo e(request('report_type') === 'episode' ? 'selected' : ''); ?>>
                                    Episode</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">All Status</option>
                                <option value="draft" <?php echo e(request('status') === 'draft' ? 'selected' : ''); ?>>Draft
                                </option>
                                <option value="submitted" <?php echo e(request('status') === 'submitted' ? 'selected' : ''); ?>>
                                    Submitted</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                    class="fas fa-search mr-2"></i>Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Reports</h3>
                </div>
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Report ID</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Period</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created At</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">#<?php echo e($report->id); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full <?php echo e($report->report_type === 'monthly' ? 'bg-blue-100 text-blue-800' : ($report->report_type === 'quarterly' ? 'bg-green-100 text-green-800' : ($report->report_type === 'annual' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800'))); ?>"><?php echo e(ucfirst($report->report_type)); ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <?php echo e(\Carbon\Carbon::parse($report->reporting_period)->format('F Y')); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <?php echo e($report->created_at->format('d/m/Y H:i')); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($report->status === 'draft'): ?>
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Draft</span>
                                        <?php else: ?>
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Submitted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="<?php echo e(route('healthcare.ministry-reports.show', $report)); ?>"
                                            class="text-blue-600 hover:text-blue-900 mr-3"><i
                                                class="fas fa-eye"></i></a>
                                        <?php if($report->status === 'draft'): ?>
                                            <button onclick="submitReport(<?php echo e($report->id); ?>)"
                                                class="text-green-600 hover:text-green-900 mr-3"><i
                                                    class="fas fa-paper-plane"></i></button>
                                        <?php endif; ?>
                                        <button onclick="deleteReport(<?php echo e($report->id); ?>)"
                                            class="text-red-600 hover:text-red-900"><i
                                                class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No ministry reports
                                        found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                <?php echo e($reports->links()); ?>

            </div>
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

        function deleteReport(id) {
            if (confirm('Are you sure you want to delete this report?')) {
                fetch(`<?php echo e(route('healthcare.ministry-reports.destroy', '')); ?>/${id}`, {
                        method: 'DELETE',
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
                    .catch(error => Toast.error('Delete failed'));
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\ministry-reports\index.blade.php ENDPATH**/ ?>