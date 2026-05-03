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
     <?php $__env->slot('title', null, []); ?> AI Routing Rules — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> AI Routing Rules — SuperAdmin <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
        <div
            class="mb-4 p-4 bg-green-50 border border-green-200 rounded-2xl text-sm text-green-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('warning')): ?>
        <div
            class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-2xl text-sm text-amber-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <?php echo e(session('warning')); ?>

        </div>
    <?php endif; ?>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <?php $__currentLoopData = $providerStatus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900"><?php echo e($status['label']); ?></p>
                        <p class="text-xs text-gray-400 mt-0.5">Provider AI</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            class="w-2 h-2 rounded-full <?php echo e($status['status_color'] === 'green' ? 'bg-green-500' : ($status['status_color'] === 'amber' ? 'bg-amber-500' : 'bg-gray-400')); ?>"></span>
                        <span
                            class="text-sm font-medium <?php echo e($status['status_color'] === 'green' ? 'text-green-600' : ($status['status_color'] === 'amber' ? 'text-amber-600' : 'text-gray-500')); ?>">
                            <?php echo e($status['status_label']); ?>

                        </span>
                    </div>
                </div>
                <?php if($status['reason']): ?>
                    <p class="text-xs text-gray-400 mt-2"><?php echo e($status['reason']); ?></p>
                <?php endif; ?>
                <?php if($status['recovers_at']): ?>
                    <p class="text-xs text-gray-400 mt-1">Pulih:
                        <?php echo e(\Carbon\Carbon::parse($status['recovers_at'])->format('d M Y H:i')); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="flex flex-wrap gap-3 mb-4">
        <a href="<?php echo e(route('super-admin.ai.monitor.index')); ?>"
            class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Monitoring Dashboard
        </a>
        <button type="button" @click="showAddModal = true" x-data="{ showAddModal: false }"
            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Use Case Baru
        </button>
        <form method="POST" action="<?php echo e(route('super-admin.ai.routing.reset')); ?>" x-data="{ confirmReset: false }"
            @submit.prevent="if (confirmReset || confirm('Reset semua routing rules ke konfigurasi default? Perubahan custom akan hilang.')) { $el.submit(); }">
            <?php echo csrf_field(); ?>
            <button type="submit"
                class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Reset ke Default
            </button>
        </form>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th
                            class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Use Case</th>
                        <th
                            class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Provider</th>
                        <th
                            class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            Model</th>
                        <th
                            class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">
                            Min Plan</th>
                        <th
                            class="px-4 sm:px-6 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                            Status</th>
                        <th
                            class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden xl:table-cell">
                            Penggunaan 30 Hari</th>
                        <th
                            class="px-4 sm:px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $stats = $usageStats[$route->use_case] ?? null;
                            $providerStatusInfo = $providerStatus[$route->provider] ?? null;
                        ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 sm:px-6 py-4">
                                <p class="text-sm font-semibold text-gray-900"><?php echo e($route->use_case); ?></p>
                                <?php if($route->description): ?>
                                    <p class="text-xs text-gray-400 mt-0.5"><?php echo e(Str::limit($route->description, 50)); ?>

                                    </p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-medium text-gray-900"><?php echo e(ucfirst($route->provider)); ?></span>
                                    <?php if($providerStatusInfo): ?>
                                        <span
                                            class="w-1.5 h-1.5 rounded-full <?php echo e($providerStatusInfo['status_color'] === 'green' ? 'bg-green-500' : ($providerStatusInfo['status_color'] === 'amber' ? 'bg-amber-500' : 'bg-gray-400')); ?>"
                                            title="<?php echo e($providerStatusInfo['status_label']); ?>"></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-500 hidden lg:table-cell">
                                <?php echo e($route->model ?? '—'); ?>

                            </td>
                            <td class="px-4 sm:px-6 py-4 hidden md:table-cell">
                                <?php if($route->min_plan): ?>
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-purple-500/20 text-purple-400">
                                        <?php echo e(ucfirst($route->min_plan)); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">Semua Plan</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-center hidden sm:table-cell">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium <?php echo e($route->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400'); ?>">
                                    <span
                                        class="w-1.5 h-1.5 rounded-full <?php echo e($route->is_active ? 'bg-green-500' : 'bg-gray-500'); ?>"></span>
                                    <?php echo e($route->is_active ? 'Aktif' : 'Nonaktif'); ?>

                                </span>
                            </td>
                            <td class="px-4 sm:px-6 py-4 hidden xl:table-cell">
                                <?php if($stats): ?>
                                    <div class="text-sm">
                                        <p class="text-gray-900 font-semibold">
                                            <?php echo e(number_format($stats['request_count'])); ?> request</p>
                                        <p class="text-gray-400 text-xs mt-0.5">Rp
                                            <?php echo e(number_format($stats['total_cost'], 2)); ?></p>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">Belum ada data</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="<?php echo e(route('super-admin.ai.routing.edit', $route)); ?>"
                                        class="p-2 rounded-lg text-gray-500 hover:text-blue-400 hover:bg-blue-500/10 transition"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <p class="text-sm text-gray-400">Tidak ada routing rules ditemukan.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div x-data="{ showAddModal: false }" x-show="showAddModal" x-cloak @keydown.escape.window="showAddModal = false"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            
            <div x-show="showAddModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" @click="showAddModal = false"
                class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75"></div>

            
            <div x-show="showAddModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                <form method="POST" action="<?php echo e(route('super-admin.ai.routing.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="bg-white px-6 pt-5 pb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Tambah Use Case Baru</h3>

                        <div class="space-y-4">
                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    Use Case <span class="text-red-400">*</span>
                                </label>
                                <input type="text" name="use_case" required placeholder="contoh: custom_analysis"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    Provider <span class="text-red-400">*</span>
                                </label>
                                <select name="provider" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <?php $__currentLoopData = $availableProviders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($provider); ?>"><?php echo e(ucfirst($provider)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    Model
                                </label>
                                <input type="text" name="model"
                                    placeholder="Opsional — kosongkan untuk model default"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    Min Plan
                                </label>
                                <select name="min_plan"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Semua Plan</option>
                                    <?php $__currentLoopData = $availablePlans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($plan); ?>"><?php echo e(ucfirst($plan)); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                                    Deskripsi
                                </label>
                                <textarea name="description" rows="3" placeholder="Deskripsi use case..."
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                            </div>

                            <div class="flex items-center gap-3">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active_add" value="1" checked
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                                <label for="is_active_add" class="text-sm text-gray-700">Aktifkan routing rule</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-3 flex gap-3 justify-end">
                        <button type="button" @click="showAddModal = false"
                            class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                            Simpan
                        </button>
                    </div>
                </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\super-admin\ai-routing\index.blade.php ENDPATH**/ ?>