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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Financial Reports')); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.financial-reports.aging')); ?>"
                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700"><i
                        class="fas fa-clock mr-2"></i>Aging Report</a>
        <button onclick="exportReport()"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"><i
                        class="fas fa-file-export mr-2"></i>Export</button>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="<?php echo e(route('healthcare.financial-reports.index')); ?>"
                        class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date" name="date_from"
                                value="<?php echo e($dateFrom instanceof \Carbon\Carbon ? $dateFrom->format('Y-m-d') : $dateFrom); ?>"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                            <input type="date" name="date_to"
                                value="<?php echo e($dateTo instanceof \Carbon\Carbon ? $dateTo->format('Y-m-d') : $dateTo); ?>"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                    class="fas fa-filter mr-2"></i>Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3"><i
                                class="fas fa-dollar-sign text-green-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                            <p class="text-2xl font-bold text-gray-900">Rp
                                <?php echo e(number_format($statistics['total_revenue'], 0, ',', '.')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3"><i
                                class="fas fa-money-bill-wave text-blue-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Paid</p>
                            <p class="text-2xl font-bold text-gray-900">Rp
                                <?php echo e(number_format($statistics['total_paid'], 0, ',', '.')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-100 rounded-md p-3"><i
                                class="fas fa-exclamation-triangle text-red-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Outstanding</p>
                            <p class="text-2xl font-bold text-gray-900">Rp
                                <?php echo e(number_format($statistics['total_outstanding'], 0, ',', '.')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-md p-3"><i
                                class="fas fa-file-invoice text-purple-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Insurance Claims</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e($statistics['insurance_claims']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-teal-100 rounded-md p-3"><i
                                class="fas fa-check-circle text-teal-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Insurance Approved</p>
                            <p class="text-2xl font-bold text-gray-900">Rp
                                <?php echo e(number_format($statistics['insurance_approved'], 0, ',', '.')); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if(count($revenueByDepartment) > 0): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-hospital mr-2 text-blue-600"></i>Revenue by Department</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total
                                        Revenue</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Percentage</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__currentLoopData = $revenueByDepartment; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo e(ucfirst($dept->department)); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Rp
                                            <?php echo e(number_format($dept->total, 0, ',', '.')); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-32 bg-gray-200 rounded-full h-2.5 mr-3">
                                                    <div class="bg-blue-600 h-2.5 rounded-full"
                                                        style="width: <?php echo e($statistics['total_revenue'] > 0 ? ($dept->total / $statistics['total_revenue']) * 100 : 0); ?>%">
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-sm font-semibold text-gray-900"><?php echo e($statistics['total_revenue'] > 0 ? number_format(($dept->total / $statistics['total_revenue']) * 100, 1) : 0); ?>%</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(count($revenueByPaymentMethod) > 0): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-credit-card mr-2 text-green-600"></i>Revenue by Payment Method</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment
                                        Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total
                                        Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Percentage</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__currentLoopData = $revenueByPaymentMethod; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo e(ucfirst($method->payment_method)); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Rp
                                            <?php echo e(number_format($method->total, 0, ',', '.')); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-32 bg-gray-200 rounded-full h-2.5 mr-3">
                                                    <div class="bg-green-600 h-2.5 rounded-full"
                                                        style="width: <?php echo e($statistics['total_revenue'] > 0 ? ($method->total / $statistics['total_revenue']) * 100 : 0); ?>%">
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-sm font-semibold text-gray-900"><?php echo e($statistics['total_revenue'] > 0 ? number_format(($method->total / $statistics['total_revenue']) * 100, 1) : 0); ?>%</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function exportReport() {
            const url = '<?php echo e(route('healthcare.financial-reports.export')); ?>' +
                '?date_from=<?php echo e($dateFrom instanceof \Carbon\Carbon ? $dateFrom->format('Y-m-d') : $dateFrom); ?>' +
                '&date_to=<?php echo e($dateTo instanceof \Carbon\Carbon ? $dateTo->format('Y-m-d') : $dateTo); ?>';

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Toast.success(data.message);
                })
                .catch(error => Toast.error('Export failed'));
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\financial-reports\index.blade.php ENDPATH**/ ?>