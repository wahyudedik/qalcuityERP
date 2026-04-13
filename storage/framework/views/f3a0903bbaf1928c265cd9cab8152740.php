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
     <?php $__env->slot('header', null, []); ?> Simulasi Bisnis (What If) <?php $__env->endSlot(); ?>
     <?php $__env->slot('topbarActions', null, []); ?> 
        <a href="<?php echo e(route('simulations.create')); ?>"
           class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 text-sm font-medium transition">
            + Simulasi Baru
        </a>
     <?php $__env->endSlot(); ?>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if(session('success')): ?>
            <div class="mb-4 p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-sm">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if($simulations->isEmpty()): ?>
            <div class="text-center py-16 text-gray-500 dark:text-slate-400">
                <div class="text-5xl mb-4">🔮</div>
                <p class="text-lg font-medium">Belum ada simulasi</p>
                <p class="text-sm mt-1">Buat simulasi "What If" untuk proyeksi dampak keputusan bisnis.</p>
                <a href="<?php echo e(route('simulations.create')); ?>"
                   class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                    Buat Simulasi Pertama
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php $__currentLoopData = $simulations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sim): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $icons = [
                            'price_increase' => '📈',
                            'new_branch'     => '🏪',
                            'stock_out'      => '📦',
                            'cost_reduction' => '✂️',
                            'demand_change'  => '📊',
                        ];
                        $labels = [
                            'price_increase' => 'Kenaikan Harga',
                            'new_branch'     => 'Cabang Baru',
                            'stock_out'      => 'Stok Habis',
                            'cost_reduction' => 'Efisiensi Biaya',
                            'demand_change'  => 'Perubahan Demand',
                        ];
                    ?>
                    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 flex flex-col gap-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-2xl"><?php echo e($icons[$sim->scenario_type] ?? '🔮'); ?></span>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mt-1"><?php echo e($sim->name); ?></h3>
                                <span class="text-xs text-gray-500 dark:text-slate-400">
                                    <?php echo e($labels[$sim->scenario_type] ?? $sim->scenario_type); ?>

                                </span>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full
                                <?php echo e($sim->status === 'calculated' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-[#0f172a] dark:text-slate-300'); ?>">
                                <?php echo e($sim->status === 'calculated' ? 'Selesai' : 'Draft'); ?>

                            </span>
                        </div>

                        <?php if($sim->ai_narrative): ?>
                            <p class="text-sm text-gray-600 dark:text-slate-400 line-clamp-2"><?php echo e($sim->ai_narrative); ?></p>
                        <?php endif; ?>

                        <div class="flex items-center justify-between mt-auto pt-2 border-t border-gray-100 dark:border-white/10">
                            <span class="text-xs text-gray-400"><?php echo e($sim->created_at->diffForHumans()); ?></span>
                            <div class="flex gap-2">
                                <a href="<?php echo e(route('simulations.show', $sim)); ?>"
                                   class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Detail</a>
                                <form method="POST" action="<?php echo e(route('simulations.destroy', $sim)); ?>"
                                      onsubmit="return confirm('Hapus simulasi ini?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="text-xs text-red-500 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="mt-6"><?php echo e($simulations->links()); ?></div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/simulations/index.blade.php ENDPATH**/ ?>