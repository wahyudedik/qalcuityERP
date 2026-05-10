

<div <?php echo e($attributes->merge(['class' => 'bg-white rounded-2xl border border-gray-200 overflow-hidden'])); ?>

    x-data="statisticsWidget({ loading: <?php echo e($loading ? 'true' : 'false'); ?>, error: <?php echo e($error ? 'true' : 'false'); ?> })" role="region" aria-label="<?php echo e($title ? "Statistik: {$title}" : 'Widget Statistik'); ?>">

    
    <?php if($title): ?>
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700"><?php echo e($title); ?></h3>
        </div>
    <?php endif; ?>

    
    <div x-show="loading" x-cloak class="p-4" aria-hidden="true">
        <div class="grid <?php echo e($gridClasses); ?> gap-4">
            <?php for($i = 0; $i < max(count($stats), 2); $i++): ?>
                <div class="animate-pulse space-y-3 p-3">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 bg-gray-200 rounded-lg"></div>
                        <div class="h-3 bg-gray-200 rounded w-20"></div>
                    </div>
                    <div class="h-7 bg-gray-200 rounded w-16"></div>
                    <div class="h-4 bg-gray-200 rounded w-14"></div>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    
    <div x-show="error && !loading" x-cloak class="p-6 text-center" role="alert" aria-live="assertive">
        <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
            </path>
        </svg>
        <p class="text-sm text-gray-500 mb-3"><?php echo e($getErrorMessage()); ?></p>
        <button type="button" x-on:click="retry()"
            class="text-blue-600 text-sm hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded px-2 py-1">
            Coba Lagi
        </button>
    </div>

    
    <div x-show="!loading && !error" class="p-4">
        <?php if(count($formattedStats) > 0): ?>
            <div class="grid <?php echo e($gridClasses); ?> gap-4">
                <?php $__currentLoopData = $formattedStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="relative p-3 rounded-xl bg-gray-50/50 hover:bg-gray-50 transition-colors">
                        
                        <div class="flex items-center gap-2 mb-2">
                            <?php if($stat['icon']): ?>
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                    <?php echo $__env->make('components.widget.partials.stat-icon', [
                                        'icon' => $stat['icon'],
                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </div>
                            <?php endif; ?>
                            <span class="text-xs font-medium text-gray-500 truncate"><?php echo e($stat['label']); ?></span>
                        </div>

                        
                        <div class="flex items-baseline gap-1">
                            <?php if($stat['prefix']): ?>
                                <span class="text-sm text-gray-500"><?php echo e($stat['prefix']); ?></span>
                            <?php endif; ?>
                            <span class="text-2xl font-bold text-gray-900"
                                aria-label="<?php echo e($stat['label']); ?>: <?php echo e(number_format($stat['value'])); ?>">
                                <?php echo e($stat['formattedValue']); ?>

                            </span>
                            <?php if($stat['suffix']): ?>
                                <span class="text-sm text-gray-500"><?php echo e($stat['suffix']); ?></span>
                            <?php endif; ?>
                        </div>

                        
                        <?php if($stat['trend'] != 0): ?>
                            <div class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($stat['trendColorClass']); ?> <?php echo e($stat['trendBgClass']); ?>"
                                aria-label="Tren: <?php echo e($stat['formattedTrend']); ?>">
                                <?php if($stat['trendDirection'] === 'up'): ?>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 15l7-7 7 7"></path>
                                    </svg>
                                <?php elseif($stat['trendDirection'] === 'down'): ?>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                <?php endif; ?>
                                <span><?php echo e($stat['formattedTrend']); ?></span>
                            </div>
                        <?php else: ?>
                            <div class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium text-gray-500 bg-gray-50"
                                aria-label="Tren: tidak berubah">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14">
                                    </path>
                                </svg>
                                <span>0%</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="text-center py-6">
                <p class="text-sm text-gray-500">Tidak ada data statistik</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('f8debd5e-9787-4e86-9c4c-4b32f702862d')): $__env->markAsRenderedOnce('f8debd5e-9787-4e86-9c4c-4b32f702862d'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('statisticsWidget', (config = {}) => ({
                    loading: config.loading || false,
                    error: config.error || false,

                    retry() {
                        this.loading = true;
                        this.error = false;
                        this.$dispatch('statistics-widget-retry');

                        // Fallback: reset loading after timeout if no external handler
                        setTimeout(() => {
                            if (this.loading) {
                                this.loading = false;
                            }
                        }, 5000);
                    },

                    setLoaded() {
                        this.loading = false;
                        this.error = false;
                    },

                    setError() {
                        this.loading = false;
                        this.error = true;
                    }
                }));
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/components/widget/statistics.blade.php ENDPATH**/ ?>