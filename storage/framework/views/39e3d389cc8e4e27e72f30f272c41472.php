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
     <?php $__env->slot('header', null, []); ?> <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>Recall Management <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <form method="POST" action="<?php echo e(route('cosmetic.recall.auto-expire')); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit"
                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition"
                        onclick="return confirm('Auto-expire all expired batches?')">
                        <i class="fas fa-clock mr-2"></i>Auto-Expire Batches
                    </button>
        <a href="<?php echo e(route('cosmetic.recall.create')); ?>"
                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>New Recall
                </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Recalls</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900"><?php echo e($stats['total']); ?></div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Active Recalls</div>
                    <div class="mt-2 text-3xl font-bold text-orange-600"><?php echo e($stats['active']); ?></div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Critical</div>
                    <div class="mt-2 text-3xl font-bold text-red-600"><?php echo e($stats['critical']); ?></div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Resolution Rate</div>
                    <div class="mt-2 text-3xl font-bold text-green-600"><?php echo e($stats['resolution_rate']); ?>%</div>
                </div>
            </div>

            <!-- Expiry Alerts -->
            <?php if($expiryInfo['expiring_count'] > 0 || $expiryInfo['expired_count'] > 0): ?>
                <div class="space-y-4">
                    <?php if($expiryInfo['expiring_count'] > 0): ?>
                        <div
                            class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <i
                                    class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                                <div>
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        <?php echo e($expiryInfo['expiring_count']); ?> Batch(es) Expiring Within 90 Days
                                    </h3>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($expiryInfo['expired_count'] > 0): ?>
                        <div
                            class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-times-circle text-red-600 mt-1 mr-3"></i>
                                <div>
                                    <h3 class="text-sm font-medium text-red-800">
                                        <?php echo e($expiryInfo['expired_count']); ?> Batch(es) Expired
                                    </h3>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Active Recalls -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Active Recalls</h3>
                </div>

                <div class="overflow-x-auto">
                    <?php if($activeRecalls->count() > 0): ?>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Recall #</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Product</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Severity</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Type</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Affected Units</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__currentLoopData = $activeRecalls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recall): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-red-600">
                                                <?php echo e($recall->recall_number); ?></div>
                                            <div class="text-xs text-gray-500">
                                                <?php echo e($recall->start_date->format('d M Y')); ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo e($recall->product->formula_name ?? 'Unknown'); ?>

                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full
                                        <?php if($recall->severity == 'critical'): ?> bg-red-100 text-red-800
                                        <?php elseif($recall->severity == 'major'): ?> bg-orange-100 text-orange-800
                                        <?php else: ?> bg-yellow-100 text-yellow-800 <?php endif; ?>">
                                                <?php echo e(ucfirst($recall->severity)); ?>

                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full
                                        <?php if($recall->recall_type == 'mandatory'): ?> bg-red-100 text-red-800
                                        <?php else: ?> bg-blue-100 text-blue-800 <?php endif; ?>">
                                                <?php echo e(ucfirst($recall->recall_type)); ?>

                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                <?php echo e(number_format($recall->affected_units)); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full
                                        <?php if($recall->status == 'initiated'): ?> bg-yellow-100 text-yellow-800
                                        <?php else: ?> bg-blue-100 text-blue-800 <?php endif; ?>">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $recall->status))); ?>

                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="<?php echo e(route('cosmetic.recall.show', $recall)); ?>"
                                                class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-check-circle text-4xl mb-2 text-green-500"></i>
                            <p>No active recalls</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\recall\dashboard.blade.php ENDPATH**/ ?>