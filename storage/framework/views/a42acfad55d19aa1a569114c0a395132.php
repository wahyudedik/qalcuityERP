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
     <?php $__env->slot('header', null, []); ?> Aturan Bisnis <?php $__env->endSlot(); ?>

    <div class="max-w-3xl mx-auto space-y-4">

        <?php if(session('success')): ?>
        <div class="px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm">
            <?php echo e(session('success')); ?>

        </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('constraints.bulk')); ?>">
            <?php echo csrf_field(); ?>

            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-900">Aturan & Batasan Bisnis</p>
                    <p class="text-xs text-gray-500 mt-0.5">Konfigurasi aturan operasional yang diterapkan di seluruh sistem.</p>
                </div>

                <div class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $constraints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                        <input type="hidden" name="constraints[<?php echo e($i); ?>][id]" value="<?php echo e($c->id); ?>">

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900"><?php echo e($c->label); ?></p>
                            <p class="text-xs text-gray-400 font-mono mt-0.5"><?php echo e($c->key); ?></p>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <?php if($c->value_type === 'boolean'): ?>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden"   name="constraints[<?php echo e($i); ?>][value]"  value="false">
                                    <input type="hidden"   name="constraints[<?php echo e($i); ?>][active]" value="0">
                                    <input type="checkbox" name="constraints[<?php echo e($i); ?>][value]"  value="true"
                                           <?php echo e($c->value === 'true' ? 'checked' : ''); ?>

                                           onchange="this.previousElementSibling.previousElementSibling.value = this.checked ? 'true' : 'false'"
                                           class="sr-only peer">
                                    <div class="w-10 h-5 bg-gray-200 peer-checked:bg-blue-600 rounded-full transition peer-focus:ring-2 peer-focus:ring-blue-500/30 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition peer-checked:after:translate-x-5"></div>
                                </label>
                            <?php elseif($c->value_type === 'percentage'): ?>
                                <div class="flex items-center gap-1.5">
                                    <input type="number" name="constraints[<?php echo e($i); ?>][value]" value="<?php echo e($c->value); ?>"
                                           min="0" max="100" step="0.1"
                                           class="w-24 px-3 py-1.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <span class="text-sm text-gray-400">%</span>
                                </div>
                            <?php elseif($c->value_type === 'amount'): ?>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm text-gray-400">Rp</span>
                                    <input type="number" name="constraints[<?php echo e($i); ?>][value]" value="<?php echo e($c->value); ?>"
                                           min="0" step="1000"
                                           class="w-40 px-3 py-1.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            <?php else: ?>
                                <input type="text" name="constraints[<?php echo e($i); ?>][value]" value="<?php echo e($c->value); ?>"
                                       class="w-40 px-3 py-1.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php endif; ?>

                            
                            <?php if($c->value_type !== 'boolean'): ?>
                            <label class="relative inline-flex items-center cursor-pointer" title="Aktifkan/nonaktifkan">
                                <input type="hidden"   name="constraints[<?php echo e($i); ?>][active]" value="0">
                                <input type="checkbox" name="constraints[<?php echo e($i); ?>][active]" value="1"
                                       <?php echo e($c->is_active ? 'checked' : ''); ?>

                                       class="sr-only peer">
                                <div class="w-8 h-4 bg-gray-200 peer-checked:bg-green-500 rounded-full transition after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-3 after:w-3 after:transition peer-checked:after:translate-x-4"></div>
                            </label>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-5 py-12 text-center text-gray-400 text-sm">
                        Belum ada aturan bisnis.
                    </div>
                    <?php endif; ?>
                </div>

                <div class="px-5 py-4 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                        class="px-5 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium transition">
                        Simpan Semua
                    </button>
                </div>
            </div>
        </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\settings\business-constraints.blade.php ENDPATH**/ ?>