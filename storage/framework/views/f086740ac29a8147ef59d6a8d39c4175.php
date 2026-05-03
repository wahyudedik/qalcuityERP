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
        <?php echo e(__('Internet Packages')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Internet Packages')); ?></h1>
                    <p class="text-gray-600 mt-1"><?php echo e(__('Kelola paket internet dan pricing')); ?></p>
                </div>
                <a href="<?php echo e(route('telecom.packages.create')); ?>"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    <?php echo e(__('Tambah Package')); ?>

                </a>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600"><?php echo e(__('Total Packages')); ?></p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['total']); ?></p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="fas fa-box text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600"><?php echo e(__('Active')); ?></p>
                            <p class="text-2xl font-bold text-green-600"><?php echo e($stats['active']); ?></p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600"><?php echo e(__('Inactive')); ?></p>
                            <p class="text-2xl font-bold text-red-600"><?php echo e($stats['inactive']); ?></p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-full">
                            <i class="fas fa-times-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600"><?php echo e(__('Unlimited')); ?></p>
                            <p class="text-2xl font-bold text-blue-600"><?php echo e($stats['unlimited']); ?>

                            </p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-infinity text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters & Search -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="<?php echo e(route('telecom.packages.index')); ?>"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                            placeholder="<?php echo e(__('Cari package...')); ?>"
                            class="w-full px-4 py-2 border border-gray-300 bg-white text-gray-900 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <select name="status"
                            class="w-full px-4 py-2 border border-gray-300 bg-white text-gray-900 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value=""><?php echo e(__('Semua Status')); ?></option>
                            <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>
                                <?php echo e(__('Active')); ?></option>
                            <option value="inactive" <?php echo e(request('status') == 'inactive' ? 'selected' : ''); ?>>
                                <?php echo e(__('Inactive')); ?></option>
                        </select>
                    </div>

                    <div>
                        <select name="quota_type"
                            class="w-full px-4 py-2 border border-gray-300 bg-white text-gray-900 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value=""><?php echo e(__('Semua Tipe')); ?></option>
                            <option value="unlimited" <?php echo e(request('quota_type') == 'unlimited' ? 'selected' : ''); ?>>
                                <?php echo e(__('Unlimited')); ?></option>
                            <option value="limited" <?php echo e(request('quota_type') == 'limited' ? 'selected' : ''); ?>>
                                <?php echo e(__('Limited Quota')); ?></option>
                        </select>
                    </div>

                    <div class="md:col-span-4 flex gap-2">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-filter mr-1"></i> <?php echo e(__('Filter')); ?>

                        </button>
                        <a href="<?php echo e(route('telecom.packages.index')); ?>"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">
                            <i class="fas fa-redo mr-1"></i> <?php echo e(__('Reset')); ?>

                        </a>
                    </div>
                </form>
            </div>

            <!-- Success/Error Messages -->
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

            <!-- Packages Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php $__empty_1 = true; $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow">
                        <!-- Package Header -->
                        <div
                            class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-xl font-bold"><?php echo e($package->name); ?></h3>
                                    <p class="text-blue-100 text-sm mt-1">
                                        <?php echo e(ucfirst($package->billing_cycle)); ?></p>
                                </div>
                                <?php if($package->is_active): ?>
                                    <span
                                        class="px-2 py-1 text-xs bg-green-500 rounded-full"><?php echo e(__('Active')); ?></span>
                                <?php else: ?>
                                    <span
                                        class="px-2 py-1 text-xs bg-gray-500 rounded-full"><?php echo e(__('Inactive')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Package Details -->
                        <div class="p-6">
                            <!-- Speed -->
                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-gray-600 text-sm"><?php echo e(__('Download')); ?></span>
                                    <span
                                        class="font-bold text-gray-900"><?php echo e($package->download_speed_mbps); ?>

                                        Mbps</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full"
                                        style="width: <?php echo e(min(100, ($package->download_speed_mbps / 100) * 100)); ?>%">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-gray-600 text-sm"><?php echo e(__('Upload')); ?></span>
                                    <span
                                        class="font-bold text-gray-900"><?php echo e($package->upload_speed_mbps); ?>

                                        Mbps</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full"
                                        style="width: <?php echo e(min(100, ($package->upload_speed_mbps / 100) * 100)); ?>%">
                                    </div>
                                </div>
                            </div>

                            <!-- Quota -->
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 text-sm"><?php echo e(__('Quota')); ?></span>
                                    <span class="font-semibold text-gray-900">
                                        <?php if($package->isUnlimited()): ?>
                                            <span class="text-blue-600">∞
                                                <?php echo e(__('Unlimited')); ?></span>
                                        <?php else: ?>
                                            <?php echo e(number_format($package->quota_bytes / 1073741824, 0)); ?>

                                            GB/<?php echo e($package->quota_period); ?>

                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Pricing -->
                            <div class="mb-4">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-3xl font-bold text-gray-900">Rp
                                        <?php echo e(number_format($package->price, 0, ',', '.')); ?></span>
                                    <span class="text-gray-600 text-sm">/<?php echo e(__('bulan')); ?></span>
                                </div>
                                <?php if($package->setup_fee > 0): ?>
                                    <p class="text-xs text-gray-500 mt-1"><?php echo e(__('Setup fee')); ?>: Rp
                                        <?php echo e(number_format($package->setup_fee, 0, ',', '.')); ?></p>
                                <?php endif; ?>
                            </div>

                            <!-- Subscriptions Count -->
                            <div class="mb-4 text-sm text-gray-600">
                                <span
                                    class="font-semibold text-gray-900"><?php echo e($package->subscriptions_count); ?></span>
                                <?php echo e(__('active subscriptions')); ?>

                            </div>

                            <!-- Description -->
                            <?php if($package->description): ?>
                                <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                    <?php echo e($package->description); ?></p>
                            <?php endif; ?>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <a href="<?php echo e(route('telecom.packages.edit', $package)); ?>"
                                    class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-center text-sm">
                                    <i class="fas fa-edit mr-1"></i> <?php echo e(__('Edit')); ?>

                                </a>
                                <form action="<?php echo e(route('telecom.packages.toggle-status', $package)); ?>" method="POST"
                                    class="flex-1">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit"
                                        class="w-full <?php echo e($package->is_active ? 'bg-gray-500 hover:bg-gray-600' : 'bg-green-500 hover:bg-green-600'); ?> text-white px-4 py-2 rounded-lg text-sm">
                                        <?php echo e($package->is_active ? __('Nonaktifkan') : __('Aktifkan')); ?>

                                    </button>
                                </form>
                                <form action="<?php echo e(route('telecom.packages.destroy', $package)); ?>" method="POST"
                                    onsubmit="return confirm('<?php echo e(__('Yakin ingin menghapus package ini?')); ?>')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-span-full text-center py-12">
                        <div class="text-gray-400">
                            <i class="fas fa-box text-5xl mb-3"></i>
                            <p class="mt-2 text-sm"><?php echo e(__('Belum ada package yang dibuat')); ?></p>
                            <a href="<?php echo e(route('telecom.packages.create')); ?>"
                                class="mt-2 inline-block text-blue-600 hover:text-blue-800">
                                <?php echo e(__('Buat package pertama')); ?> →
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if($packages->hasPages()): ?>
                <div class="mt-6">
                    <?php echo e($packages->links()); ?>

                </div>
            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\packages\index.blade.php ENDPATH**/ ?>