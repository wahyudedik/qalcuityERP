

<?php $__env->startSection('title', 'BPOM Registrations'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">BPOM Registrations</h1>
                    <p class="mt-1 text-sm text-gray-500">Product registration & regulatory compliance</p>
                </div>
                <div class="flex gap-2">
                    <a href="<?php echo e(route('cosmetic.registrations.sds')); ?>"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">
                        Safety Data Sheets
                    </a>
                    <a href="<?php echo e(route('cosmetic.registrations.restrictions')); ?>"
                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition">
                        Ingredient Restrictions
                    </a>
                    <a href="<?php echo e(route('cosmetic.registrations.create')); ?>"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        + New Registration
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Total</div>
                <div class="mt-2 text-2xl font-bold text-gray-900"><?php echo e($stats['total_registrations']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Approved</div>
                <div class="mt-2 text-2xl font-bold text-green-600"><?php echo e($stats['approved']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Pending</div>
                <div class="mt-2 text-2xl font-bold text-yellow-600"><?php echo e($stats['pending']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Expiring Soon</div>
                <div class="mt-2 text-2xl font-bold text-orange-600"><?php echo e($stats['expiring_soon']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Expired</div>
                <div class="mt-2 text-2xl font-bold text-red-600"><?php echo e($stats['expired']); ?></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="<?php echo e(route('cosmetic.registrations.index')); ?>" class="flex gap-4">
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                    <option value="submitted" <?php echo e(request('status') == 'submitted' ? 'selected' : ''); ?>>Submitted</option>
                    <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved</option>
                    <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                    <option value="expired" <?php echo e(request('status') == 'expired' ? 'selected' : ''); ?>>Expired</option>
                </select>
                <select name="category" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Categories</option>
                    <option value="skincare" <?php echo e(request('category') == 'skincare' ? 'selected' : ''); ?>>Skincare</option>
                    <option value="haircare" <?php echo e(request('category') == 'haircare' ? 'selected' : ''); ?>>Haircare</option>
                    <option value="makeup" <?php echo e(request('category') == 'makeup' ? 'selected' : ''); ?>>Makeup</option>
                    <option value="fragrance" <?php echo e(request('category') == 'fragrance' ? 'selected' : ''); ?>>Fragrance</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">
                    Filter
                </button>
            </form>
        </div>

        <!-- Registrations Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reg. Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $registrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($reg->registration_number); ?></div>
                                <div class="text-xs text-gray-500"><?php echo e(ucfirst($reg->registration_type)); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900"><?php echo e($reg->product_name); ?></div>
                                <?php if($reg->formula): ?>
                                    <div class="text-xs text-gray-500"><?php echo e($reg->formula->formula_name); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-900"><?php echo e(ucfirst($reg->product_category)); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full
                            <?php if($reg->status == 'approved'): ?> bg-green-100 text-green-800
                            <?php elseif($reg->status == 'submitted'): ?> bg-blue-100 text-blue-800
                            <?php elseif($reg->status == 'rejected'): ?> bg-red-100 text-red-800
                            <?php elseif($reg->status == 'expired'): ?> bg-gray-100 text-gray-800
                            <?php else: ?> bg-yellow-100 text-yellow-800 <?php endif; ?>">
                                    <?php echo e($reg->status_label); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php if($reg->expiry_date): ?>
                                    <span
                                        class="<?php echo e($reg->isExpired() ? 'text-red-600 font-medium' : ($reg->isExpiringSoon() ? 'text-orange-600' : 'text-gray-500')); ?>">
                                        <?php echo e($reg->expiry_date->format('d M Y')); ?>

                                        <?php if($reg->isExpired()): ?>
                                            ⚠️ Expired
                                        <?php elseif($reg->isExpiringSoon()): ?>
                                            (<?php echo e($reg->days_until_expiry); ?> days)
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php if($reg->submission_date): ?>
                                    <?php echo e($reg->submission_date->format('d M Y')); ?>

                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <?php if($reg->status == 'pending'): ?>
                                        <form method="POST" action="<?php echo e(route('cosmetic.registrations.submit', $reg)); ?>"
                                            class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="text-blue-600 hover:text-blue-900">Submit</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if($reg->status == 'submitted'): ?>
                                        <form method="POST" action="<?php echo e(route('cosmetic.registrations.approve', $reg)); ?>"
                                            class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit"
                                                class="text-green-600 hover:text-green-900">Approve</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                No registrations found. Create your first BPOM registration!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($registrations->hasPages()): ?>
            <div class="mt-4"><?php echo e($registrations->links()); ?></div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\registrations\index.blade.php ENDPATH**/ ?>