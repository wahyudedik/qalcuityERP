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
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Aktifkan Two-Factor Authentication</h2>
        <p class="mt-1 text-sm text-gray-500">Scan QR code dengan aplikasi authenticator (Google Authenticator, Authy, dll).</p>
    </div>

    <?php if(session('warning')): ?>
        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl text-sm">
            ⚠️ <?php echo e(session('warning')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <?php echo e($errors->first()); ?>

        </div>
    <?php endif; ?>

    <div class="space-y-6">
        <!-- QR Code -->
        <div class="flex flex-col items-center gap-3 p-5 bg-gray-50 rounded-xl border border-gray-200">
            <?php if($qrSvg): ?>
                <div class="bg-white p-3 rounded-lg shadow-sm"><?php echo $qrSvg; ?></div>
            <?php else: ?>
                <p class="text-xs text-gray-500 break-all text-center">
                    Buka aplikasi authenticator dan masukkan kode manual:<br>
                    <span class="font-mono font-bold text-gray-800"><?php echo e($secret); ?></span>
                </p>
            <?php endif; ?>
            <p class="text-xs text-gray-500">Atau masukkan kode manual:</p>
            <code class="text-sm font-mono bg-white px-3 py-1.5 rounded-lg border border-gray-200 tracking-widest select-all">
                <?php echo e($secret); ?>

            </code>
        </div>

        <!-- Konfirmasi OTP -->
        <form method="POST" action="<?php echo e(route('two-factor.confirm')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Masukkan kode 6 digit dari aplikasi authenticator
                </label>
                <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                       autofocus autocomplete="one-time-code"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-center text-2xl font-mono tracking-widest
                              focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="000000">
            </div>
            <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700
                           text-white font-semibold py-2.5 rounded-xl text-sm transition-all shadow-md">
                Aktifkan 2FA
            </button>
        </form>

        <?php if(auth()->guard()->check()): ?>
            <a href="<?php echo e(route('dashboard')); ?>" class="block text-center text-sm text-gray-500 hover:text-gray-700">
                Lewati untuk sekarang
            </a>
        <?php endif; ?>
    </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/auth/two-factor/setup.blade.php ENDPATH**/ ?>