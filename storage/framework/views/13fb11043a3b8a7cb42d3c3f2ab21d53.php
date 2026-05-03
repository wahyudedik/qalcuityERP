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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Shared Report: ') . $sharedReport->name); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Report Info -->
            <div class="bg-white rounded-xl p-6 shadow mb-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">
                            <?php echo e($sharedReport->name); ?>

                        </h3>
                        <p class="text-sm text-gray-600">
                            Shared by <strong><?php echo e($sharedReport->creator->name ?? 'Unknown'); ?></strong>
                            on <?php echo e($sharedReport->created_at->format('d M Y H:i')); ?>

                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <?php if($canDownload): ?>
                            <div class="dropdown relative">
                                <button
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                    📥 Download
                                </button>
                                <div
                                    class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden">
                                    <a href="<?php echo e(route('analytics.shared.download', ['id' => $sharedReport->report_id, 'format' => 'pdf'])); ?>"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        📄 PDF
                                    </a>
                                    <a href="<?php echo e(route('analytics.shared.download', ['id' => $sharedReport->report_id, 'format' => 'excel'])); ?>"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        📊 Excel
                                    </a>
                                    <a href="<?php echo e(route('analytics.shared.download', ['id' => $sharedReport->report_id, 'format' => 'csv'])); ?>"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        📋 CSV
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Type:</span>
                        <p class="font-semibold text-gray-900 capitalize">
                            <?php echo e(str_replace('_', ' ', $sharedReport->type)); ?>

                        </p>
                    </div>
                    <div>
                        <span class="text-gray-500">Access Level:</span>
                        <p class="font-semibold text-gray-900 capitalize">
                            <?php echo e($sharedReport->access_level); ?>

                        </p>
                    </div>
                    <div>
                        <span class="text-gray-500">Expires:</span>
                        <p
                            class="font-semibold <?php echo e($sharedReport->isExpired() ? 'text-red-600' : 'text-gray-900'); ?>">
                            <?php echo e($sharedReport->expires_at?->format('d M Y') ?? 'Never'); ?>

                        </p>
                    </div>
                    <div>
                        <span class="text-gray-500">Views:</span>
                        <p class="font-semibold text-gray-900">
                            <?php echo e($sharedReport->access_count); ?>

                        </p>
                    </div>
                </div>
            </div>

            <!-- Report Content -->
            <div class="bg-white rounded-xl p-6 shadow">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">📊 Report Data</h4>

                <?php if(isset($sharedReport->report_data['financial_kpis'])): ?>
                    <!-- Executive Dashboard Data -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div
                            class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg">
                            <p class="text-sm text-gray-600">Revenue</p>
                            <p class="text-2xl font-bold text-gray-900">
                                Rp
                                <?php echo e(number_format($sharedReport->report_data['financial_kpis']['revenue']['current'] ?? 0, 0, ',', '.')); ?>

                            </p>
                        </div>
                        <div
                            class="p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg">
                            <p class="text-sm text-gray-600">Profit Margin</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php echo e($sharedReport->report_data['financial_kpis']['profit_margin']['current'] ?? 0); ?>%
                            </p>
                        </div>
                        <div
                            class="p-4 bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg">
                            <p class="text-sm text-gray-600">Orders</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php echo e(number_format($sharedReport->report_data['operational_kpis']['orders']['current'] ?? 0, 0, ',', '.')); ?>

                            </p>
                        </div>
                        <div
                            class="p-4 bg-gradient-to-br from-orange-50 to-red-50 rounded-lg">
                            <p class="text-sm text-gray-600">Customers</p>
                            <p class="text-2xl font-bold text-gray-900">
                                <?php echo e(number_format($sharedReport->report_data['customer_kpis']['new_customers']['current'] ?? 0, 0, ',', '.')); ?>

                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if(isset($sharedReport->report_data['data'])): ?>
                    <!-- Generic Report Data -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="bg-gray-50 text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left">Metric</th>
                                    <th class="px-4 py-3 text-right">Value</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php $__currentLoopData = $sharedReport->report_data['data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            <?php echo e(ucwords(str_replace('_', ' ', $metric))); ?>

                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-600">
                                            <?php if(is_array($value)): ?>
                                                <?php echo e(json_encode($value)); ?>

                                            <?php elseif(is_numeric($value)): ?>
                                                <?php echo e(number_format($value, 0, ',', '.')); ?>

                                            <?php else: ?>
                                                <?php echo e($value); ?>

                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>This report was shared with you and will expire on
                    <?php echo e($sharedReport->expires_at?->format('d M Y H:i') ?? 'never'); ?>.</p>
                <p class="mt-1">Powered by <?php echo e(config('app.name')); ?></p>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            // Dropdown toggle
            document.querySelectorAll('.dropdown').forEach(dropdown => {
                const button = dropdown.querySelector('button');
                const menu = dropdown.querySelector('.dropdown-menu');

                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    menu.classList.toggle('hidden');
                });

                document.addEventListener('click', () => {
                    menu.classList.add('hidden');
                });
            });
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\shared-report-view.blade.php ENDPATH**/ ?>