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
        <?php echo e(__('Laporan & Analitik Telecom')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Laporan & Analitik Telecom')); ?></h1>
                        <p class="text-gray-600 mt-1"><?php echo e(__('Business intelligence untuk operasional telecom Anda')); ?></p>
                    </div>
                    <a href="<?php echo e(route('telecom.dashboard')); ?>"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <?php echo e(__('Kembali ke Dashboard')); ?>

                    </a>
                </div>
            </div>

            <!-- Reports Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Revenue by Package -->
                <a href="<?php echo e(route('telecom.reports.revenue-by-package')); ?>"
                    class="bg-white rounded-lg shadow hover:shadow-xl transition-shadow p-6 border-l-4 border-green-500 block">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo e(__('Pendapatan per Paket')); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo e(__('Analisis distribusi pendapatan dari berbagai paket internet')); ?></p>
                            <ul class="mt-3 text-xs text-gray-500 space-y-1">
                                <li>✓ <?php echo e(__('Total pendapatan per paket')); ?></li>
                                <li>✓ <?php echo e(__('Jumlah subscription aktif')); ?></li>
                                <li>✓ <?php echo e(__('Persentase kontribusi pendapatan')); ?></li>
                                <li>✓ <?php echo e(__('Export ke Excel')); ?></li>
                            </ul>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full flex-shrink-0">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Bandwidth Utilization -->
                <a href="<?php echo e(route('telecom.reports.bandwidth-utilization')); ?>"
                    class="bg-white rounded-lg shadow hover:shadow-xl transition-shadow p-6 border-l-4 border-blue-500 block">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo e(__('Utilisasi Bandwidth')); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo e(__('Pantau tren konsumsi bandwidth dan penggunaan per perangkat')); ?></p>
                            <ul class="mt-3 text-xs text-gray-500 space-y-1">
                                <li>✓ <?php echo e(__('Tren harian/mingguan/bulanan')); ?></li>
                                <li>✓ <?php echo e(__('Rasio download vs upload')); ?></li>
                                <li>✓ <?php echo e(__('Perangkat dengan penggunaan tertinggi')); ?></li>
                                <li>✓ <?php echo e(__('Export ke Excel')); ?></li>
                            </ul>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full flex-shrink-0">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Customer Usage Analytics -->
                <a href="<?php echo e(route('telecom.reports.customer-usage-analytics')); ?>"
                    class="bg-white rounded-lg shadow hover:shadow-xl transition-shadow p-6 border-l-4 border-purple-500 block">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo e(__('Analitik Penggunaan Pelanggan')); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo e(__('Analisis mendalam perilaku pelanggan dan pola subscription')); ?></p>
                            <ul class="mt-3 text-xs text-gray-500 space-y-1">
                                <li>✓ <?php echo e(__('Segmentasi pelanggan')); ?></li>
                                <li>✓ <?php echo e(__('Analisis distribusi penggunaan')); ?></li>
                                <li>✓ <?php echo e(__('Breakdown status subscription')); ?></li>
                                <li>✓ <?php echo e(__('Export ke Excel')); ?></li>
                            </ul>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full flex-shrink-0">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Top Consumers -->
                <a href="<?php echo e(route('telecom.reports.top-consumers')); ?>"
                    class="bg-white rounded-lg shadow hover:shadow-xl transition-shadow p-6 border-l-4 border-orange-500 block">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo e(__('Laporan Konsumen Teratas')); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo e(__('Identifikasi konsumen bandwidth tertinggi dan pola penggunaan')); ?></p>
                            <ul class="mt-3 text-xs text-gray-500 space-y-1">
                                <li>✓ <?php echo e(__('Peringkat 20 konsumen teratas')); ?></li>
                                <li>✓ <?php echo e(__('Urutkan berdasarkan download/upload/total')); ?></li>
                                <li>✓ <?php echo e(__('Statistik penggunaan voucher')); ?></li>
                                <li>✓ <?php echo e(__('Export ke Excel')); ?></li>
                            </ul>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full flex-shrink-0">
                            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Quick Tips -->
            <div class="mt-8 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow p-6 text-white">
                <h2 class="text-xl font-bold mb-4"><?php echo e(__('Tips Penggunaan')); ?></h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="font-semibold mb-1">📊 <?php echo e(__('Kebaruan Data')); ?></p>
                        <p class="opacity-90"><?php echo e(__('Laporan menggunakan data real-time dari database. Pastikan scheduled jobs berjalan untuk hasil terbaik.')); ?></p>
                    </div>
                    <div>
                        <p class="font-semibold mb-1">📅 <?php echo e(__('Rentang Tanggal')); ?></p>
                        <p class="opacity-90"><?php echo e(__('Sesuaikan rentang tanggal untuk menganalisis periode tertentu. Default adalah bulan berjalan.')); ?></p>
                    </div>
                    <div>
                        <p class="font-semibold mb-1">💾 <?php echo e(__('Export Excel')); ?></p>
                        <p class="opacity-90"><?php echo e(__('Semua laporan dapat diekspor ke Excel untuk analisis lebih lanjut atau presentasi.')); ?></p>
                    </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\reports\index.blade.php ENDPATH**/ ?>