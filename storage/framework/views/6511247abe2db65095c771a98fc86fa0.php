<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Verifikasi Two-Factor</h2>
        <p class="mt-1.5 text-sm text-gray-500">Masukkan kode dari aplikasi authenticator Anda.</p>
    </div>

    <?php if($errors->any()): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <?php echo e($errors->first('code')); ?>

        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('two-factor.verify')); ?>" class="space-y-5" x-data="{ useRecovery: false }">
        <?php echo csrf_field(); ?>

        <div x-show="!useRecovery">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kode Authenticator</label>
            <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                   autofocus autocomplete="one-time-code"
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-center text-2xl font-mono tracking-widest
                          focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="000000">
        </div>

        <div x-show="useRecovery">
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Recovery Code</label>
            <input type="text" name="code" maxlength="10"
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-center font-mono tracking-widest
                          focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="XXXXXXXXXX">
        </div>

        <button type="submit"
                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700
                       text-white font-semibold py-2.5 rounded-xl text-sm transition-all shadow-md">
            Verifikasi
        </button>

        <button type="button" @click="useRecovery = !useRecovery"
                class="w-full text-sm text-gray-500 hover:text-gray-700 text-center">
            <span x-text="useRecovery ? '← Gunakan kode authenticator' : 'Gunakan recovery code'"></span>
        </button>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\auth\two-factor\challenge.blade.php ENDPATH**/ ?>