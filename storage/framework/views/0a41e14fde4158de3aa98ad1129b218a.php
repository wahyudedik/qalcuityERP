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
     <?php $__env->slot('header', null, []); ?> Deteksi Anomali <?php $__env->endSlot(); ?>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if(session('success')): ?>
            <div class="mb-4 p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-sm">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <!-- Header row: tabs + action -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div class="flex flex-wrap gap-2">
                <?php $__currentLoopData = ['open' => '🔴 Open', 'acknowledged' => '🟡 Ditinjau', 'resolved' => '🟢 Selesai']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(request()->fullUrlWithQuery(['status' => $s])); ?>"
                       class="px-3 py-1.5 rounded-full text-sm font-medium transition
                           <?php echo e(request('status') === $s
                               ? 'bg-gray-800 text-white dark:bg-gray-200 dark:text-gray-800'
                               : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'); ?>">
                        <?php echo e($label); ?>

                        <?php if(isset($counts[$s])): ?>
                            <span class="ml-1 text-xs opacity-70">(<?php echo e($counts[$s]); ?>)</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('anomalies.index')); ?>"
                   class="px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600">
                    Semua
                </a>
            </div>

            <form method="POST" action="<?php echo e(route('anomalies.detect')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 text-sm font-medium flex items-center gap-1.5">
                    🔍 Jalankan Deteksi
                </button>
            </form>
        </div>

        <?php if($anomalies->isEmpty()): ?>
            <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                <div class="text-5xl mb-4">✅</div>
                <p class="text-lg font-medium">Tidak ada anomali ditemukan</p>
                <p class="text-sm mt-1">Klik "Jalankan Deteksi" untuk memeriksa anomali terbaru.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php $__currentLoopData = $anomalies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anomaly): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $severityClass = match($anomaly->severity) {
                            'critical' => 'border-red-400 bg-red-50 dark:bg-red-900/20',
                            'warning'  => 'border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20',
                            default    => 'border-blue-400 bg-blue-50 dark:bg-blue-900/20',
                        };
                    ?>
                    <div class="border-l-4 <?php echo e($severityClass); ?> rounded-r-xl p-4 flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold text-gray-800 dark:text-gray-100 text-sm"><?php echo e($anomaly->title); ?></span>
                                <span class="text-xs px-2 py-0.5 rounded-full
                                    <?php echo e($anomaly->status === 'open' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' :
                                       ($anomaly->status === 'acknowledged' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' :
                                       'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300')); ?>">
                                    <?php echo e(ucfirst($anomaly->status)); ?>

                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e($anomaly->description); ?></p>
                            <p class="text-xs text-gray-400 mt-1"><?php echo e($anomaly->created_at->diffForHumans()); ?></p>
                        </div>
                        <div class="flex gap-2 shrink-0">
                            <?php if($anomaly->status === 'open'): ?>
                                <form method="POST" action="<?php echo e(route('anomalies.acknowledge', $anomaly)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button class="text-xs px-3 py-1.5 bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300 rounded-lg hover:bg-yellow-200">
                                        Tinjau
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if($anomaly->status !== 'resolved'): ?>
                                <form method="POST" action="<?php echo e(route('anomalies.resolve', $anomaly)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button class="text-xs px-3 py-1.5 bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300 rounded-lg hover:bg-green-200">
                                        Selesai
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="mt-6"><?php echo e($anomalies->links()); ?></div>
        <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\anomalies\index.blade.php ENDPATH**/ ?>