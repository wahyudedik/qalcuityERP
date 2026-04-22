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
        <?php echo e(__('Detail Penggunaan Pelanggan')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($customer->name); ?></h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        <?php echo e($customer->email ?? __('Tidak ada email')); ?> •
                        <?php echo e($customer->phone ?? __('Tidak ada telepon')); ?>

                    </p>
                </div>
                <a href="<?php echo e(route('telecom.customers.usage')); ?>"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i><?php echo e(__('Kembali')); ?>

                </a>
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
                <!-- Left: Subscription & Usage -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Subscription Info -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo e(__('Subscription Aktif')); ?></h2>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Paket')); ?></p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white"><?php echo e($subscription->package?->name ?? '-'); ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Status')); ?></p>
                                <span class="mt-1 px-2 py-0.5 inline-flex text-xs font-semibold rounded-full
                                    <?php echo e($subscription->status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400'); ?>">
                                    <?php echo e(ucfirst($subscription->status)); ?>

                                </span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Kecepatan')); ?></p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white">
                                    <?php echo e($subscription->package?->download_speed_mbps ?? 0); ?>/<?php echo e($subscription->package?->upload_speed_mbps ?? 0); ?> Mbps
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Perangkat')); ?></p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white"><?php echo e($subscription->device?->name ?? '-'); ?></p>
                            </div>
                            <?php if($subscription->hotspot_username): ?>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Username')); ?></p>
                                    <p class="mt-1 font-mono text-gray-900 dark:text-white"><?php echo e($subscription->hotspot_username); ?></p>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Tagihan Berikutnya')); ?></p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white">
                                    <?php echo e($subscription->next_billing_date?->format('d M Y') ?? '-'); ?>

                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Usage Summary -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e(__('Ringkasan Penggunaan')); ?></h2>
                            <div class="flex gap-2">
                                <?php $__currentLoopData = ['daily' => __('Harian'), 'weekly' => __('Mingguan'), 'monthly' => __('Bulanan')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e(route('telecom.customers.show-usage', $customer)); ?>?period=<?php echo e($p); ?>"
                                        class="px-3 py-1 text-xs rounded-full <?php echo e($period === $p ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'); ?>">
                                        <?php echo e($label); ?>

                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <?php
                            $quotaBytes = $subscription->package?->quota_bytes ?? 0;
                            $usedBytes = $subscription->current_usage_bytes;
                            $percentage = $quotaBytes > 0 ? min(100, round(($usedBytes / $quotaBytes) * 100, 1)) : 0;
                            $color = $percentage > 90 ? 'red' : ($percentage > 70 ? 'yellow' : 'green');
                        ?>

                        <?php if($quotaBytes > 0): ?>
                            <div class="mb-6">
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-gray-600 dark:text-gray-400"><?php echo e(round($usedBytes / 1073741824, 2)); ?> GB <?php echo e(__('terpakai')); ?></span>
                                    <span class="text-gray-600 dark:text-gray-400"><?php echo e(round($quotaBytes / 1073741824, 2)); ?> GB <?php echo e(__('total')); ?></span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                                    <div class="bg-<?php echo e($color); ?>-500 h-4 rounded-full" style="width: <?php echo e($percentage); ?>%"></div>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><?php echo e($percentage); ?>% <?php echo e(__('terpakai')); ?></p>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-blue-600 dark:text-blue-400 font-semibold mb-4">∞ <?php echo e(__('Kuota Unlimited')); ?></p>
                        <?php endif; ?>

                        <?php if(isset($usageSummary)): ?>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1"><?php echo e(__('Download')); ?></p>
                                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
                                        <?php echo e(round(($usageSummary['total_download'] ?? 0) / 1073741824, 2)); ?> GB
                                    </p>
                                </div>
                                <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1"><?php echo e(__('Upload')); ?></p>
                                    <p class="text-xl font-bold text-green-600 dark:text-green-400">
                                        <?php echo e(round(($usageSummary['total_upload'] ?? 0) / 1073741824, 2)); ?> GB
                                    </p>
                                </div>
                                <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1"><?php echo e(__('Total')); ?></p>
                                    <p class="text-xl font-bold text-purple-600 dark:text-purple-400">
                                        <?php echo e(round((($usageSummary['total_download'] ?? 0) + ($usageSummary['total_upload'] ?? 0)) / 1073741824, 2)); ?> GB
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Usage Chart -->
                    <?php if(isset($chartData) && count($chartData['labels']) > 0): ?>
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo e(__('Tren Penggunaan (7 Hari Terakhir)')); ?></h2>
                            <canvas id="usageChart" height="200"></canvas>
                        </div>
                    <?php endif; ?>

                    <!-- Usage History -->
                    <?php if(isset($usageHistory) && $usageHistory->count() > 0): ?>
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e(__('Riwayat Penggunaan')); ?></h2>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Periode')); ?></th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Download')); ?></th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Upload')); ?></th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Total')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <?php $__currentLoopData = $usageHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="px-6 py-3 text-sm text-gray-900 dark:text-white">
                                                    <?php echo e($record->period_start?->format('d M Y') ?? '-'); ?>

                                                </td>
                                                <td class="px-6 py-3 text-sm text-blue-600 dark:text-blue-400">
                                                    <?php echo e(round(($record->bytes_in ?? 0) / 1048576, 2)); ?> MB
                                                </td>
                                                <td class="px-6 py-3 text-sm text-green-600 dark:text-green-400">
                                                    <?php echo e(round(($record->bytes_out ?? 0) / 1048576, 2)); ?> MB
                                                </td>
                                                <td class="px-6 py-3 text-sm text-gray-900 dark:text-white">
                                                    <?php echo e(round((($record->bytes_in ?? 0) + ($record->bytes_out ?? 0)) / 1048576, 2)); ?> MB
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right: Actions -->
                <div class="space-y-4">
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo e(__('Aksi')); ?></h2>
                        <div class="space-y-3">
                            <?php if($subscription->status === 'active'): ?>
                                <form action="<?php echo e(route('telecom.customers.suspend', $customer)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit"
                                        class="w-full bg-yellow-500 hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                                        onclick="return confirm('<?php echo e(__('Suspend subscription pelanggan ini?')); ?>')">
                                        <i class="fas fa-pause mr-2"></i><?php echo e(__('Suspend')); ?>

                                    </button>
                                </form>
                            <?php elseif($subscription->status === 'suspended'): ?>
                                <form action="<?php echo e(route('telecom.customers.reactivate', $customer)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit"
                                        class="w-full bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                        <i class="fas fa-play mr-2"></i><?php echo e(__('Aktifkan Kembali')); ?>

                                    </button>
                                </form>
                            <?php endif; ?>

                            <form action="<?php echo e(route('telecom.customers.reset-quota', $customer)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit"
                                    class="w-full bg-purple-500 hover:bg-purple-600 dark:bg-purple-600 dark:hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                                    onclick="return confirm('<?php echo e(__('Reset kuota pelanggan ini?')); ?>')">
                                    <i class="fas fa-redo mr-2"></i><?php echo e(__('Reset Kuota')); ?>

                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo e(__('Info Pelanggan')); ?></h2>
                        <div class="space-y-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Nama')); ?></p>
                                <p class="mt-1 font-medium text-gray-900 dark:text-white"><?php echo e($customer->name); ?></p>
                            </div>
                            <?php if($customer->email): ?>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Email')); ?></p>
                                    <p class="mt-1 text-gray-900 dark:text-white"><?php echo e($customer->email); ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if($customer->phone): ?>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase"><?php echo e(__('Telepon')); ?></p>
                                    <p class="mt-1 text-gray-900 dark:text-white"><?php echo e($customer->phone); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(isset($chartData) && count($chartData['labels']) > 0): ?>
        <?php $__env->startPush('scripts'); ?>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                const ctx = document.getElementById('usageChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($chartData['labels'], 15, 512) ?>,
                        datasets: [{
                            label: '<?php echo e(__('Download (MB)')); ?>',
                            data: <?php echo json_encode($chartData['downloads'], 15, 512) ?>,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }, {
                            label: '<?php echo e(__('Upload (MB)')); ?>',
                            data: <?php echo json_encode($chartData['uploads'], 15, 512) ?>,
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top' } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            </script>
        <?php $__env->stopPush(); ?>
    <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\customers\detail.blade.php ENDPATH**/ ?>