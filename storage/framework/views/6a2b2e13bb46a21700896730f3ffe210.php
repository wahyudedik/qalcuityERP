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
     <?php $__env->slot('header', null, []); ?> Multi Company <?php $__env->endSlot(); ?>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if(session('success')): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <div class="flex items-center justify-between mb-5">
            <p class="text-sm text-gray-500">Kelola beberapa perusahaan dan laporan konsolidasi.</p>
            <a href="<?php echo e(route('company-groups.create')); ?>"
               class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-medium transition">
                + Buat Grup
            </a>
        </div>

        <?php if($groups->isEmpty()): ?>
            <div class="text-center py-16 text-gray-500">
                <div class="text-5xl mb-4">🏢</div>
                <p class="text-lg font-medium">Belum ada grup perusahaan</p>
                <p class="text-sm mt-1">Buat grup untuk mengelola beberapa perusahaan dan laporan konsolidasi.</p>
                <a href="<?php echo e(route('company-groups.create')); ?>"
                   class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm">
                    Buat Grup Pertama
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-800"><?php echo e($group->name); ?></h3>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <?php echo e($group->members_count); ?> perusahaan · <?php echo e($group->currency_code); ?>

                                </p>
                            </div>
                            <span class="text-2xl">🏢</span>
                        </div>
                        <a href="<?php echo e(route('company-groups.show', $group)); ?>"
                           class="block w-full text-center px-3 py-2 bg-blue-50 text-blue-600 rounded-xl text-sm hover:bg-blue-100 transition">
                            Lihat Konsolidasi →
                        </a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\company-groups\index.blade.php ENDPATH**/ ?>