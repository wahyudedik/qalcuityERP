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
     <?php $__env->slot('header', null, []); ?> Buat Grup Perusahaan <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('company-groups.index')); ?>" class="text-gray-400 hover:text-gray-600">←</a>
    </div>

    <div class="py-6 max-w-lg mx-auto px-4">
        <form method="POST" action="<?php echo e(route('company-groups.store')); ?>"
              class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Grup</label>
                <input type="text" name="name" value="<?php echo e(old('name')); ?>" required
                       placeholder="Contoh: Holding ABC Group"
                       class="w-full rounded-lg border-gray-300 text-sm">
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mata Uang Konsolidasi</label>
                <select name="currency_code" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="IDR" selected>IDR — Rupiah</option>
                    <option value="USD">USD — US Dollar</option>
                    <option value="SGD">SGD — Singapore Dollar</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <a href="<?php echo e(route('company-groups.index')); ?>"
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    Buat Grup
                </button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\company-groups\create.blade.php ENDPATH**/ ?>