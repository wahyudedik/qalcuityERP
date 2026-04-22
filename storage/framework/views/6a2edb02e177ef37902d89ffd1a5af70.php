

<?php $__env->startSection('title', 'Regulatory Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Regulatory Compliance Dashboard</h1>
                    <p class="mt-1 text-sm text-gray-500">BPOM registration and regulatory overview</p>
                </div>
                <a href="<?php echo e(route('cosmetic.analytics.dashboard')); ?>" class="text-blue-600 hover:text-blue-800">← Back to
                    Analytics</a>
            </div>
        </div>

        <!-- Registration Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Total Registrations</div>
                <div class="mt-2 text-3xl font-bold text-gray-900"><?php echo e($registrationStats['total']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Approved</div>
                <div class="mt-2 text-3xl font-bold text-green-600"><?php echo e($registrationStats['approved']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Pending</div>
                <div class="mt-2 text-3xl font-bold text-yellow-600"><?php echo e($registrationStats['pending']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Expired</div>
                <div class="mt-2 text-3xl font-bold text-red-600"><?php echo e($registrationStats['expired']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Expiring Soon (90d)</div>
                <div class="mt-2 text-3xl font-bold text-orange-600"><?php echo e($registrationStats['expiring_soon']); ?></div>
            </div>
        </div>

        <!-- Compliance Metrics -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">Compliance Health Metrics</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 bg-green-50 rounded-lg">
                    <div class="text-sm text-gray-600">Products with Valid Registration</div>
                    <div class="mt-2 text-2xl font-bold text-green-700">
                        <?php echo e($complianceMetrics['products_with_valid_registration']); ?></div>
                </div>
                <div class="p-4 bg-red-50 rounded-lg">
                    <div class="text-sm text-gray-600">Products Missing Registration</div>
                    <div class="mt-2 text-2xl font-bold text-red-700">
                        <?php echo e($complianceMetrics['products_missing_registration']); ?></div>
                    <?php if($complianceMetrics['products_missing_registration'] > 0): ?>
                        <div class="text-xs text-red-600 mt-1">⚠️ Action Required</div>
                    <?php endif; ?>
                </div>
                <div class="p-4 bg-yellow-50 rounded-lg">
                    <div class="text-sm text-gray-600">Restricted Ingredients in Use</div>
                    <div class="mt-2 text-2xl font-bold text-yellow-700">
                        <?php echo e($complianceMetrics['restricted_ingredients_in_use']); ?></div>
                </div>
                <div class="p-4 bg-blue-50 rounded-lg">
                    <div class="text-sm text-gray-600">SDS Up to Date</div>
                    <div class="mt-2 text-2xl font-bold text-blue-700"><?php echo e($complianceMetrics['sds_up_to_date']); ?></div>
                </div>
            </div>
        </div>

        <!-- Upcoming Expirations -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Upcoming Registration Expirations (180 days)</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registration No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Remaining</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $upcomingExpirations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo e($reg->formula?->formula_name ?? 'Unknown'); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo e($reg->registration_number); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e($reg->expiry_date->format('d M Y')); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php $days = now()->diffInDays($reg->expiry_date, false); ?>
                                <span
                                    class="text-sm font-semibold <?php echo e($days < 30 ? 'text-red-600' : ($days < 90 ? 'text-orange-600' : 'text-green-600')); ?>">
                                    <?php echo e($days); ?> days
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Renewal
                                    Needed</span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No upcoming expirations</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Submissions -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Recent Submissions</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__currentLoopData = $recentSubmissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo e($reg->formula?->formula_name); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo e($reg->submitted_at?->format('d M Y') ?? '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full 
                                    <?php echo e($reg->status === 'approved' ? 'bg-green-100 text-green-800' : ($reg->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')); ?>">
                                    <?php echo e(ucfirst($reg->status)); ?>

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\analytics\regulatory.blade.php ENDPATH**/ ?>