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
     <?php $__env->slot('header', null, []); ?> CCTV — Kamera <?php echo e($cameraId); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="<?php echo e(route('cctv.index')); ?>"
            class="hover:text-blue-600 transition-colors">CCTV</a>
        <span>/</span>
        <span class="text-gray-900">Kamera <?php echo e($cameraId); ?></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        <div class="lg:col-span-2">
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                
                <div class="relative aspect-video bg-gray-900 flex items-center justify-center">
                    <?php if(($stream['success'] ?? false) && ($stream['stream_url'] ?? null)): ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <p class="text-white text-sm">Stream: <?php echo e($stream['stream_url']); ?></p>
                        </div>
                        
                        <div class="absolute top-3 left-3">
                            <span
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-red-600 text-white">
                                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span> LIVE
                            </span>
                        </div>
                        <div class="absolute top-3 right-3 text-xs text-white/70">
                            <?php echo e($stream['resolution'] ?? '1920x1080'); ?>

                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <svg class="w-16 h-16 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            <p class="text-gray-400 text-sm"><?php echo e($stream['message'] ?? 'Stream tidak tersedia'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                
                <div class="p-4 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">
                        <?php echo e($stream['camera_name'] ?? 'Kamera ' . $cameraId); ?>

                    </h3>
                    <?php if($stream['location'] ?? null): ?>
                        <p class="text-xs text-gray-500 mt-1">📍 <?php echo e($stream['location']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="space-y-4">
            
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Status Kamera</h4>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Koneksi</span>
                        <?php if($status['online'] ?? false): ?>
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                Online
                            </span>
                        <?php else: ?>
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                Offline
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Rekaman</span>
                        <?php if($status['recording'] ?? false): ?>
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span> Merekam
                            </span>
                        <?php else: ?>
                            <span class="text-xs text-gray-600">Tidak aktif</span>
                        <?php endif; ?>
                    </div>
                    <?php if($status['storage_used'] ?? null): ?>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Penyimpanan</span>
                            <span class="text-xs text-gray-700"><?php echo e($status['storage_used']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if($status['last_motion'] ?? null): ?>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Gerakan Terakhir</span>
                            <span class="text-xs text-gray-700"><?php echo e($status['last_motion']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Aksi</h4>
                <div class="space-y-2">
                    <form method="POST" action="<?php echo e(route('cctv.snapshot', $cameraId)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                            class="w-full px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                            📸 Ambil Snapshot
                        </button>
                    </form>
                    <form method="POST" action="<?php echo e(route('cctv.motion-detect', $cameraId)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                            class="w-full px-4 py-2 text-sm bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition-colors">
                            🔍 Deteksi Gerakan
                        </button>
                    </form>
                    <a href="<?php echo e(route('cctv.recordings', ['camera_id' => $cameraId])); ?>"
                        class="block w-full px-4 py-2 text-sm text-center bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                        📹 Lihat Rekaman
                    </a>
                </div>
            </div>

            
            <a href="<?php echo e(route('cctv.index')); ?>"
                class="block w-full px-4 py-2 text-sm text-center bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                ← Kembali ke Daftar Kamera
            </a>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\security\cctv\camera.blade.php ENDPATH**/ ?>