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
     <?php $__env->slot('header', null, []); ?> <i class="fas fa-certificate mr-2 text-blue-600"></i>BPOM Registration Dashboard <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('cosmetic.bpom.create')); ?>"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                <i class="fas fa-plus mr-2"></i>New Registration
            </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Registrations</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900"><?php echo e($stats['total']); ?></div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Pending/Submitted</div>
                    <div class="mt-2 text-3xl font-bold text-yellow-600"><?php echo e($stats['pending'] + $stats['submitted']); ?>

                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Approved</div>
                    <div class="mt-2 text-3xl font-bold text-green-600"><?php echo e($stats['approved']); ?></div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Expiring Soon</div>
                    <div class="mt-2 text-3xl font-bold text-red-600"><?php echo e($stats['expiring_soon']); ?></div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if($expiringInfo['expiring_count'] > 0 || $expiringInfo['expired_count'] > 0): ?>
                <div class="space-y-4">
                    <?php if($expiringInfo['expiring_count'] > 0): ?>
                        <div
                            class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <i
                                    class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                                <div>
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        <?php echo e($expiringInfo['expiring_count']); ?> Registration(s) Expiring Within 90 Days
                                    </h3>
                                    <p class="mt-1 text-sm text-yellow-700">
                                        Please review and renew registrations before expiry
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($expiringInfo['expired_count'] > 0): ?>
                        <div
                            class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-times-circle text-red-600 mt-1 mr-3"></i>
                                <div>
                                    <h3 class="text-sm font-medium text-red-800">
                                        <?php echo e($expiringInfo['expired_count']); ?> Registration(s) Expired
                                    </h3>
                                    <p class="mt-1 text-sm text-red-700">
                                        These products cannot be sold legally until renewed
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="<?php echo e(route('cosmetic.bpom.dashboard')); ?>"
                    class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                            placeholder="Search by registration number or product name..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <select name="status"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="submitted" <?php echo e(request('status') == 'submitted' ? 'selected' : ''); ?>>Submitted
                        </option>
                        <option value="approved" <?php echo e(request('status') == 'approved' ? 'selected' : ''); ?>>Approved
                        </option>
                        <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Rejected
                        </option>
                        <option value="expired" <?php echo e(request('status') == 'expired' ? 'selected' : ''); ?>>Expired</option>
                    </select>
                    <?php if($categories->count() > 0): ?>
                        <select name="category"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">All Categories</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($cat); ?>"
                                    <?php echo e(request('category') == $cat ? 'selected' : ''); ?>><?php echo e(ucfirst($cat)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    <?php endif; ?>
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                </form>
            </div>

            <!-- Registrations Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Reg. Number</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Product</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Category</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Submitted</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Expiry</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__empty_1 = true; $__currentLoopData = $registrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-blue-600">
                                            <?php echo e($reg->registration_number); ?>

                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo e($reg->registration_type); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo e($reg->product_name); ?></div>
                                        <?php if($reg->formula): ?>
                                            <div class="text-xs text-gray-500">
                                                <?php echo e($reg->formula->formula_code); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="text-sm text-gray-600"><?php echo e(ucfirst($reg->product_category)); ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full
                                        <?php if($reg->status == 'pending'): ?> bg-gray-100 text-gray-800
                                        <?php elseif($reg->status == 'submitted'): ?> bg-yellow-100 text-yellow-800
                                        <?php elseif($reg->status == 'approved'): ?> bg-green-100 text-green-800
                                        <?php elseif($reg->status == 'rejected'): ?> bg-red-100 text-red-800
                                        <?php else: ?> bg-orange-100 text-orange-800 <?php endif; ?>">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $reg->status))); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-600">
                                            <?php echo e($reg->submission_date ? $reg->submission_date->format('d M Y') : '-'); ?>

                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm
                                        <?php if($reg->expiry_date && $reg->expiry_date->lt(now())): ?> text-red-600 font-bold
                                        <?php elseif($reg->expiry_date && $reg->expiry_date->lt(now()->addDays(90))): ?> text-yellow-600
                                        <?php else: ?> text-gray-600 <?php endif; ?>">
                                            <?php echo e($reg->expiry_date ? $reg->expiry_date->format('d M Y') : '-'); ?>

                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="<?php echo e(route('cosmetic.bpom.show', $reg)); ?>"
                                            class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if($reg->status == 'pending'): ?>
                                            <form method="POST" action="<?php echo e(route('cosmetic.bpom.submit', $reg)); ?>"
                                                class="inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="text-green-600 hover:text-green-900"
                                                    onclick="return confirm('Submit this registration to BPOM?')">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-certificate text-4xl mb-2"></i>
                                        <p>No registrations found. Create your first BPOM registration.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if($registrations->hasPages()): ?>
                    <div class="px-6 py-4 border-t border-gray-200">
                        <?php echo e($registrations->links()); ?>

                    </div>
                <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\bpom\dashboard.blade.php ENDPATH**/ ?>