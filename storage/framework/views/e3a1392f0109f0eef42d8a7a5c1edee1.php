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
     <?php $__env->slot('header', null, []); ?> Analitik Healthcare <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Analitik'],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Analitik'],
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $attributes = $__attributesOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__attributesOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $component = $__componentOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__componentOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <a href="<?php echo e(route('healthcare.analytics.dashboard')); ?>"
                    class="bg-white dark:bg-[#1e293b] rounded-2xl p-6 border border-gray-200 dark:border-white/10 hover:shadow-lg transition-shadow">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Dashboard Analitik</h3>
                            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Overview semua metrik dan tren
                                utama</p>
                        </div>
                    </div>
                </a>

                <a href="<?php echo e(route('healthcare.analytics.kpi')); ?>"
                    class="bg-white dark:bg-[#1e293b] rounded-2xl p-6 border border-gray-200 dark:border-white/10 hover:shadow-lg transition-shadow">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Dashboard KPI</h3>
                            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Tracking key performance
                                indicators</p>
                        </div>
                    </div>
                </a>

                <a href="<?php echo e(route('healthcare.analytics.financial')); ?>"
                    class="bg-white dark:bg-[#1e293b] rounded-2xl p-6 border border-gray-200 dark:border-white/10 hover:shadow-lg transition-shadow">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Analitik Finansial</h3>
                            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Analisis pendapatan, pengeluaran,
                                dan koleksi</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <a href="<?php echo e(route('healthcare.analytics.bor')); ?>"
                    class="bg-white dark:bg-[#1e293b] rounded-2xl p-6 border border-gray-200 dark:border-white/10 hover:shadow-lg transition-shadow">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Bed Occupancy Rate</h3>
                            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Analisis BOR per bangsal dan tren
                            </p>
                        </div>
                    </div>
                </a>

                <a href="<?php echo e(route('healthcare.analytics.alos')); ?>"
                    class="bg-white dark:bg-[#1e293b] rounded-2xl p-6 border border-gray-200 dark:border-white/10 hover:shadow-lg transition-shadow">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Average Length of Stay</h3>
                            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">ALOS per bangsal dan diagnosa</p>
                        </div>
                    </div>
                </a> <a href="<?php echo e(route('healthcare.analytics.financial')); ?>"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-md p-4"><i
                                class="fas fa-dollar-sign text-purple-600 text-2xl"></i></div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Financial Analytics</h3>
                            <p class="text-sm text-gray-500 mt-1">Revenue, expenses, and collection analysis</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <a href="<?php echo e(route('healthcare.analytics.bor')); ?>"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-100 rounded-md p-4"><i
                                class="fas fa-bed text-indigo-600 text-2xl"></i></div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Bed Occupancy Rate</h3>
                            <p class="text-sm text-gray-500 mt-1">BOR analysis by ward and trend</p>
                        </div>
                    </div>
                </a>

                <a href="<?php echo e(route('healthcare.analytics.alos')); ?>"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-4"><i
                                class="fas fa-clock text-yellow-600 text-2xl"></i></div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Average Length of Stay</h3>
                            <p class="text-sm text-gray-500 mt-1">ALOS by ward and diagnosis</p>
                        </div>
                    </div>
                </a>

                <a href="<?php echo e(route('healthcare.analytics.mortality')); ?>"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-100 rounded-md p-4"><i
                                class="fas fa-heartbeat text-red-600 text-2xl"></i></div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Mortality Rate</h3>
                            <p class="text-sm text-gray-500 mt-1">Mortality analysis by cause and ward</p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <a href="<?php echo e(route('healthcare.analytics.infection')); ?>"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-orange-100 rounded-md p-4"><i
                                class="fas fa-virus text-orange-600 text-2xl"></i></div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Infection Rate</h3>
                            <p class="text-sm text-gray-500 mt-1">Infection tracking by type and ward</p>
                        </div>
                    </div>
                </a>

                <a href="<?php echo e(route('healthcare.analytics.satisfaction')); ?>"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-teal-100 rounded-md p-4"><i
                                class="fas fa-smile text-teal-600 text-2xl"></i></div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Patient Satisfaction</h3>
                            <p class="text-sm text-gray-500 mt-1">Satisfaction scores and NPS tracking</p>
                        </div>
                    </div>
                </a>

                <a href="<?php echo e(route('healthcare.financial-reports.index')); ?>"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-pink-100 rounded-md p-4"><i
                                class="fas fa-file-invoice-dollar text-pink-600 text-2xl"></i></div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Financial Reports</h3>
                            <p class="text-sm text-gray-500 mt-1">Revenue reports and aging analysis</p>
                        </div>
                    </div>
                </a>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\analytics\index.blade.php ENDPATH**/ ?>