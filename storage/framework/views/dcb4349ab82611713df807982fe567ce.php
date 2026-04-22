

<?php $__env->startSection('title', 'Product Lifecycle Report'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Product Lifecycle Report</h1>
                    <p class="mt-1 text-sm text-gray-500">Track products from launch to discontinuation</p>
                </div>
                <a href="<?php echo e(route('cosmetic.analytics.dashboard')); ?>" class="text-blue-600 hover:text-blue-800">← Back to
                    Analytics</a>
            </div>
        </div>

        <!-- Lifecycle Distribution -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="?status=<?php echo e($status); ?>"
                    class="bg-white rounded-lg shadow p-4 hover:shadow-md transition <?php echo e($status == request('status') ? 'ring-2 ring-blue-500' : ''); ?>">
                    <div class="text-sm font-medium text-gray-500"><?php echo e(ucfirst($status)); ?></div>
                    <div class="mt-2 text-3xl font-bold text-gray-900"><?php echo e($lifecycleStats[$status] ?? 0); ?></div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Products Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Product Details</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Batches</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">First Production</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Production</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days in Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo e($product['formula']->formula_name); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full
                                    <?php echo e($product['formula']->status === 'production'
                                        ? 'bg-green-100 text-green-800'
                                        : ($product['formula']->status === 'discontinued'
                                            ? 'bg-red-100 text-red-800'
                                            : 'bg-blue-100 text-blue-800')); ?>">
                                    <?php echo e(ucfirst($product['formula']->status)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo e($product['total_batches']); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e($product['first_production']?->format('d M Y') ?? '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e($product['last_production']?->format('d M Y') ?? '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full
                                    <?php echo e($product['registration_status'] === 'approved' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); ?>">
                                    <?php echo e($product['registration_status']); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e($product['days_in_current_status']); ?> days</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\analytics\product-lifecycle.blade.php ENDPATH**/ ?>