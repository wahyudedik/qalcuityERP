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
        <?php echo e(__('Voucher Management')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Voucher Management')); ?></h1>
                    <p class="text-gray-600 mt-1">
                        <?php echo e(__('Generate, print & manage internet vouchers')); ?></p>
                </div>
                <div class="flex gap-2">
                    <a href="<?php echo e(route('telecom.vouchers.create')); ?>"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        <?php echo e(__('Generate Vouchers')); ?>

                    </a>
                    <a href="<?php echo e(route('telecom.dashboard')); ?>"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        <?php echo e(__('Back to Dashboard')); ?>

                    </a>
                </div>
            </div>

            <?php if(session('success')): ?>
                <div
                    class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div
                    class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-600"><?php echo e(__('Total Vouchers')); ?></p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($stats['total'])); ?>

                    </p>
                </div>
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                    <p class="text-sm text-gray-600"><?php echo e(__('Unused')); ?></p>
                    <p class="text-2xl font-bold text-green-600">
                        <?php echo e(number_format($stats['unused'])); ?></p>
                </div>
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-purple-500">
                    <p class="text-sm text-gray-600"><?php echo e(__('Used')); ?></p>
                    <p class="text-2xl font-bold text-purple-600">
                        <?php echo e(number_format($stats['used'])); ?></p>
                </div>
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-red-500">
                    <p class="text-sm text-gray-600"><?php echo e(__('Expired')); ?></p>
                    <p class="text-2xl font-bold text-red-600"><?php echo e(number_format($stats['expired'])); ?>

                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="<?php echo e(route('telecom.vouchers.index')); ?>"
                    class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Status')); ?></label>
                        <select name="status"
                            class="w-full border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value=""><?php echo e(__('All Status')); ?></option>
                            <option value="unused" <?php echo e(request('status') === 'unused' ? 'selected' : ''); ?>>
                                <?php echo e(__('Unused')); ?></option>
                            <option value="used" <?php echo e(request('status') === 'used' ? 'selected' : ''); ?>>
                                <?php echo e(__('Used')); ?></option>
                            <option value="expired" <?php echo e(request('status') === 'expired' ? 'selected' : ''); ?>>
                                <?php echo e(__('Expired')); ?></option>
                            <option value="revoked" <?php echo e(request('status') === 'revoked' ? 'selected' : ''); ?>>
                                <?php echo e(__('Revoked')); ?></option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Batch Number')); ?></label>
                        <select name="batch_number"
                            class="w-full border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value=""><?php echo e(__('All Batches')); ?></option>
                            <?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($batch); ?>"
                                    <?php echo e(request('batch_number') === $batch ? 'selected' : ''); ?>>
                                    <?php echo e($batch); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Package')); ?></label>
                        <select name="package_id"
                            class="w-full border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value=""><?php echo e(__('All Packages')); ?></option>
                            <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($package->id); ?>"
                                    <?php echo e(request('package_id') == $package->id ? 'selected' : ''); ?>>
                                    <?php echo e($package->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('Search Code')); ?></label>
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                            placeholder="<?php echo e(__('Search voucher code...')); ?>"
                            class="w-full border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-filter mr-1"></i> <?php echo e(__('Filter')); ?>

                        </button>
                    </div>
                </form>
            </div>

            <!-- Print Selected -->
            <?php if($vouchers->count() > 0): ?>
                <div class="mb-4 flex justify-between items-center">
                    <p class="text-sm text-gray-600"><?php echo e(__('Showing')); ?>

                        <?php echo e($vouchers->firstItem()); ?> - <?php echo e($vouchers->lastItem()); ?> <?php echo e(__('of')); ?>

                        <?php echo e($vouchers->total()); ?> <?php echo e(__('vouchers')); ?></p>
                    <form action="<?php echo e(route('telecom.vouchers.print')); ?>" method="GET" target="_blank" class="inline">
                        <?php if(request('batch_number')): ?>
                            <input type="hidden" name="batch_number" value="<?php echo e(request('batch_number')); ?>">
                        <?php endif; ?>
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                            <i class="fas fa-print"></i>
                            <?php echo e(__('Print Unused Vouchers')); ?>

                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Vouchers Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php echo e(__('Code')); ?>

                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php echo e(__('Package')); ?>

                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php echo e(__('Batch')); ?>

                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php echo e(__('Validity')); ?>

                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php echo e(__('Price')); ?>

                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php echo e(__('Status')); ?>

                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php echo e(__('Customer')); ?>

                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php echo e(__('Actions')); ?>

                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__empty_1 = true; $__currentLoopData = $vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-mono font-bold text-gray-900">
                                            <?php echo e($voucher->code); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo e($voucher->package?->name ?? '-'); ?></div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo e($voucher->package?->download_speed_mbps ?? 0); ?>/<?php echo e($voucher->package?->upload_speed_mbps ?? 0); ?>

                                            Mbps
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="text-xs text-gray-600"><?php echo e($voucher->batch_number ?? '-'); ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs text-gray-900">
                                            <?php echo e($voucher->valid_from->format('d M Y')); ?>

                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo e(__('to')); ?> <?php echo e($voucher->valid_until->format('d M Y H:i')); ?>

                                        </div>
                                        <?php if($voucher->isExpired()): ?>
                                            <span
                                                class="text-xs text-red-600 font-semibold"><?php echo e(__('EXPIRED')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo e($voucher->sale_price ? 'Rp ' . number_format($voucher->sale_price, 0, ',', '.') : '-'); ?>

                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo e($voucher->status === 'unused'
                                                ? 'bg-green-100 text-green-800'
                                                : ($voucher->status === 'used'
                                                    ? 'bg-purple-100 text-purple-800'
                                                    : ($voucher->status === 'expired'
                                                        ? 'bg-red-100 text-red-800'
                                                        : 'bg-gray-100 text-gray-800'))); ?>">
                                            <?php echo e(ucfirst($voucher->status)); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($voucher->customer): ?>
                                            <div class="text-sm text-gray-900">
                                                <?php echo e($voucher->customer->name); ?></div>
                                            <div class="text-xs text-gray-500">
                                                <?php echo e($voucher->used_at?->format('d M Y H:i')); ?></div>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <?php if($voucher->status === 'unused' && !$voucher->isExpired()): ?>
                                            <form action="<?php echo e(route('telecom.vouchers.revoke', $voucher)); ?>"
                                                method="POST" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-900"
                                                    onclick="return confirm('<?php echo e(__('Revoke this voucher?')); ?>')"><?php echo e(__('Revoke')); ?></button>
                                            </form>

                                            <form action="<?php echo e(route('telecom.vouchers.extend', $voucher)); ?>"
                                                method="POST" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="hours" value="24">
                                                <button type="submit"
                                                    class="text-blue-600 hover:text-blue-900">+24h</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8"
                                        class="px-6 py-12 text-center text-gray-500">
                                        <i
                                            class="fas fa-ticket-alt text-gray-400 text-5xl mb-3"></i>
                                        <p class="mt-2 text-sm"><?php echo e(__('Tidak ada voucher ditemukan')); ?></p>
                                        <a href="<?php echo e(route('telecom.vouchers.create')); ?>"
                                            class="text-blue-600 hover:text-blue-900 text-sm mt-2 inline-block">
                                            <?php echo e(__('Generate vouchers sekarang')); ?>

                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if($vouchers->hasPages()): ?>
                    <div class="px-6 py-4 border-t border-gray-200">
                        <?php echo e($vouchers->links()); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\vouchers\index.blade.php ENDPATH**/ ?>