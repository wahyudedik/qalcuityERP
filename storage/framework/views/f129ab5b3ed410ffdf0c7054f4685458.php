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
        <?php echo e(__('Detail Subscription')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e(__('Detail Subscription')); ?></h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        <?php echo e($subscription->customer?->name ?? '-'); ?> — <?php echo e($subscription->package?->name ?? '-'); ?>

                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="<?php echo e(route('telecom.subscriptions.edit', $subscription)); ?>"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-700">
                        <i class="fas fa-edit mr-2"></i><?php echo e(__('Edit')); ?>

                    </a>
                    <a href="<?php echo e(route('telecom.subscriptions.index')); ?>"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i><?php echo e(__('Kembali')); ?>

                    </a>
                </div>
            </div>

            <?php if(session('success')): ?>
                <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg mb-4">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Subscription Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Status Card -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo e(__('Informasi Subscription')); ?></h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Status')); ?></p>
                                <span class="mt-1 px-3 py-1 inline-flex text-sm font-semibold rounded-full
                                    <?php echo e($subscription->status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : ($subscription->status === 'suspended' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400')); ?>">
                                    <?php echo e(ucfirst($subscription->status)); ?>

                                </span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Pelanggan')); ?></p>
                                <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white"><?php echo e($subscription->customer?->name ?? '-'); ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Paket')); ?></p>
                                <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white"><?php echo e($subscription->package?->name ?? '-'); ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Perangkat')); ?></p>
                                <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white"><?php echo e($subscription->device?->name ?? '-'); ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Tanggal Mulai')); ?></p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                    <?php echo e($subscription->started_at?->format('d M Y') ?? $subscription->activated_at?->format('d M Y') ?? '-'); ?>

                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Tagihan Berikutnya')); ?></p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                    <?php echo e($subscription->next_billing_date?->format('d M Y') ?? '-'); ?>

                                </p>
                            </div>
                            <?php if($subscription->hotspot_username): ?>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Username Hotspot')); ?></p>
                                    <p class="mt-1 text-sm font-mono text-gray-900 dark:text-white"><?php echo e($subscription->hotspot_username); ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if($subscription->notes): ?>
                                <div class="col-span-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Catatan')); ?></p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white"><?php echo e($subscription->notes); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Usage Summary -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo e(__('Ringkasan Penggunaan')); ?></h2>
                        <?php
                            $quotaBytes = $subscription->package?->quota_bytes ?? 0;
                            $usedBytes = $subscription->current_usage_bytes;
                            $percentage = $quotaBytes > 0 ? min(100, round(($usedBytes / $quotaBytes) * 100, 1)) : 0;
                            $color = $percentage > 90 ? 'red' : ($percentage > 70 ? 'yellow' : 'green');
                        ?>
                        <?php if($quotaBytes > 0): ?>
                            <div class="mb-4">
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-gray-600 dark:text-gray-400"><?php echo e(__('Terpakai')); ?>: <?php echo e(round($usedBytes / 1073741824, 2)); ?> GB</span>
                                    <span class="text-gray-600 dark:text-gray-400"><?php echo e(__('Total')); ?>: <?php echo e(round($quotaBytes / 1073741824, 2)); ?> GB</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                                    <div class="bg-<?php echo e($color); ?>-500 h-4 rounded-full transition-all" style="width: <?php echo e($percentage); ?>%"></div>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?php echo e($percentage); ?>% <?php echo e(__('terpakai')); ?></p>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-blue-600 dark:text-blue-400 font-semibold">∞ <?php echo e(__('Kuota Unlimited')); ?></p>
                        <?php endif; ?>

                        <?php if(isset($usageSummary)): ?>
                            <div class="grid grid-cols-3 gap-4 mt-4">
                                <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(__('Download')); ?></p>
                                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                        <?php echo e(round(($usageSummary['total_download'] ?? 0) / 1073741824, 2)); ?> GB
                                    </p>
                                </div>
                                <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(__('Upload')); ?></p>
                                    <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                        <?php echo e(round(($usageSummary['total_upload'] ?? 0) / 1073741824, 2)); ?> GB
                                    </p>
                                </div>
                                <div class="text-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(__('Total')); ?></p>
                                    <p class="text-lg font-bold text-purple-600 dark:text-purple-400">
                                        <?php echo e(round((($usageSummary['total_download'] ?? 0) + ($usageSummary['total_upload'] ?? 0)) / 1073741824, 2)); ?> GB
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions Panel -->
                <div class="space-y-4">
                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo e(__('Aksi Cepat')); ?></h2>
                        <div class="space-y-3">
                            <?php if($subscription->status === 'active'): ?>
                                <form action="<?php echo e(route('telecom.subscriptions.suspend', $subscription)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit"
                                        class="w-full bg-yellow-500 hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                                        onclick="return confirm('<?php echo e(__('Suspend subscription ini?')); ?>')">
                                        <i class="fas fa-pause mr-2"></i><?php echo e(__('Suspend Subscription')); ?>

                                    </button>
                                </form>
                            <?php elseif($subscription->status === 'suspended'): ?>
                                <form action="<?php echo e(route('telecom.subscriptions.reactivate', $subscription)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit"
                                        class="w-full bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                        <i class="fas fa-play mr-2"></i><?php echo e(__('Aktifkan Kembali')); ?>

                                    </button>
                                </form>
                            <?php endif; ?>

                            <form action="<?php echo e(route('telecom.subscriptions.reset-quota', $subscription)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit"
                                    class="w-full bg-purple-500 hover:bg-purple-600 dark:bg-purple-600 dark:hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                                    onclick="return confirm('<?php echo e(__('Reset kuota subscription ini?')); ?>')">
                                    <i class="fas fa-redo mr-2"></i><?php echo e(__('Reset Kuota')); ?>

                                </button>
                            </form>

                            <a href="<?php echo e(route('telecom.subscriptions.edit', $subscription)); ?>"
                                class="block w-full text-center bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                <i class="fas fa-edit mr-2"></i><?php echo e(__('Edit Subscription')); ?>

                            </a>
                        </div>
                    </div>

                    <!-- Package Info -->
                    <?php if($subscription->package): ?>
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo e(__('Info Paket')); ?></h2>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400"><?php echo e(__('Download')); ?></span>
                                    <span class="font-medium text-gray-900 dark:text-white"><?php echo e($subscription->package->download_speed_mbps); ?> Mbps</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400"><?php echo e(__('Upload')); ?></span>
                                    <span class="font-medium text-gray-900 dark:text-white"><?php echo e($subscription->package->upload_speed_mbps); ?> Mbps</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400"><?php echo e(__('Harga')); ?></span>
                                    <span class="font-medium text-gray-900 dark:text-white">Rp <?php echo e(number_format($subscription->package->price, 0, ',', '.')); ?></span>
                                </div>
                            </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\subscriptions\show.blade.php ENDPATH**/ ?>