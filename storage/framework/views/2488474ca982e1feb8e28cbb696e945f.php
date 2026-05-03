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
     <?php $__env->slot('header', null, []); ?> CCTV Monitoring <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Kamera</p>
            <p class="text-2xl font-bold text-gray-900"><?php echo e($cameras['total'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Online</p>
            <p class="text-2xl font-bold text-green-500">
                <?php echo e(collect($cameras['cameras'] ?? [])->filter(fn($c) => $c['status']['online'] ?? false)->count()); ?>

            </p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Offline</p>
            <p class="text-2xl font-bold text-red-500">
                <?php echo e(collect($cameras['cameras'] ?? [])->filter(fn($c) => !($c['status']['online'] ?? false))->count()); ?>

            </p>
        </div>
    </div>

    
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <a href="<?php echo e(route('cctv.recordings')); ?>"
            class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors text-center">
            📹 Rekaman
        </a>
        <a href="<?php echo e(route('security.dashboard')); ?>"
            class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors text-center">
            🔒 Dashboard Keamanan
        </a>
    </div>

    
    <?php if(($cameras['success'] ?? false) && !empty($cameras['cameras'])): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php $__currentLoopData = $cameras['cameras']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $camera): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div
                    class="bg-white rounded-2xl border border-gray-200 overflow-hidden group">
                    
                    <div class="relative aspect-video bg-gray-900 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-gray-600 mx-auto mb-2" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <p class="text-xs text-gray-500">Kamera <?php echo e($camera['id'] ?? '-'); ?></p>
                        </div>
                        
                        <div class="absolute top-2 right-2">
                            <?php if($camera['status']['online'] ?? false): ?>
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-500/90 text-white">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Online
                                </span>
                            <?php else: ?>
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/90 text-white">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span> Offline
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="p-4">
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">
                            <?php echo e($camera['name'] ?? 'Kamera ' . ($camera['id'] ?? '')); ?>

                        </h3>
                        <?php if($camera['location'] ?? null): ?>
                            <p class="text-xs text-gray-500 mb-3">
                                📍 <?php echo e($camera['location']); ?>

                            </p>
                        <?php endif; ?>

                        <div class="flex flex-wrap gap-2">
                            <a href="<?php echo e(route('cctv.camera', $camera['id'] ?? 0)); ?>"
                                class="flex-1 min-w-[80px] px-3 py-2 text-xs text-center bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                                Lihat
                            </a>
                            <form method="POST" action="<?php echo e(route('cctv.snapshot', $camera['id'] ?? 0)); ?>"
                                class="flex-1 min-w-[80px]">
                                <?php echo csrf_field(); ?>
                                <button type="submit"
                                    class="w-full px-3 py-2 text-xs bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                                    📸 Snapshot
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <p class="text-gray-500 mb-2">Belum ada kamera yang dikonfigurasi.</p>
            <p class="text-xs text-gray-400">
                Konfigurasi kamera CCTV melalui menu Pengaturan &gt; Integrasi &gt; CCTV.
            </p>
        </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\security\cctv\dashboard.blade.php ENDPATH**/ ?>