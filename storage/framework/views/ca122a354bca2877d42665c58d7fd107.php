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
    <div class="mb-7">
        <h2 class="text-2xl font-bold text-gray-900 leading-tight">Buat akun perusahaan</h2>
        <p class="mt-1.5 text-sm text-gray-500">Coba gratis 14 hari, tanpa kartu kredit</p>
    </div>

    <form method="POST" action="<?php echo e(route('register')); ?>" class="space-y-4">
        <?php echo csrf_field(); ?>
        <?php if(request('ref')): ?><input type="hidden" name="ref" value="<?php echo e(request('ref')); ?>"><?php endif; ?>

        <div>
            <label for="company_name" class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Perusahaan</label>
            <input id="company_name" type="text" name="company_name" value="<?php echo e(old('company_name')); ?>" required autofocus
                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-900 text-sm placeholder-gray-400
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition
                       <?php $__errorArgs = ['company_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                placeholder="PT. Contoh Jaya">
            <?php $__errorArgs = ['company_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="mt-1.5 text-xs text-red-600 font-medium"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div>
            <label for="business_type" class="block text-sm font-semibold text-gray-700 mb-1.5">Jenis Bisnis</label>
            <select id="business_type" name="business_type"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 text-sm
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                <option value="">-- Pilih jenis bisnis (opsional) --</option>
                <option value="warung_makan"  <?php echo e(old('business_type') === 'warung_makan'  ? 'selected' : ''); ?>>Warung Makan / Rumah Makan</option>
                <option value="kafe"          <?php echo e(old('business_type') === 'kafe'          ? 'selected' : ''); ?>>Kafe / Coffee Shop</option>
                <option value="toko_retail"   <?php echo e(old('business_type') === 'toko_retail'   ? 'selected' : ''); ?>>Toko Retail / Minimarket</option>
                <option value="konveksi"      <?php echo e(old('business_type') === 'konveksi'      ? 'selected' : ''); ?>>Konveksi / Garmen</option>
                <option value="distributor"   <?php echo e(old('business_type') === 'distributor'   ? 'selected' : ''); ?>>Distributor / Grosir</option>
                <option value="jasa"          <?php echo e(old('business_type') === 'jasa'          ? 'selected' : ''); ?>>Usaha Jasa</option>
                <option value="lainnya"       <?php echo e(old('business_type') === 'lainnya'       ? 'selected' : ''); ?>>Lainnya</option>
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Admin</label>
                <input id="name" type="text" name="name" value="<?php echo e(old('name')); ?>" required autocomplete="name"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-900 text-sm placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition
                           <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    placeholder="Nama lengkap">
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1.5 text-xs text-red-600 font-medium"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Telepon <span class="text-gray-400 font-normal">(opsional)</span>
                </label>
                <input id="phone" type="text" name="phone" value="<?php echo e(old('phone')); ?>"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-900 text-sm placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    placeholder="+62...">
            </div>
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" required autocomplete="username"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-900 text-sm placeholder-gray-400
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition
                       <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                placeholder="admin@perusahaan.com">
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="mt-1.5 text-xs text-red-600 font-medium"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-900 text-sm placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition
                           <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 bg-red-50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    placeholder="••••••••">
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1.5 text-xs text-red-600 font-medium"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">Konfirmasi</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-900 text-sm placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    placeholder="••••••••">
            </div>
        </div>

        <button type="submit"
            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700
                   text-white font-semibold py-2.5 rounded-xl text-sm transition-all shadow-md shadow-blue-200/60 active:scale-[.98] mt-1">
            Buat Akun Gratis
        </button>

        <div class="relative my-1">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
            <div class="relative flex justify-center text-xs"><span class="bg-white px-3 text-gray-400">atau</span></div>
        </div>

        <a href="<?php echo e(route('auth.google')); ?>"
            class="w-full flex items-center justify-center gap-3 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 hover:border-gray-400 transition active:scale-[.98]">
            <svg class="w-5 h-5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
            Daftar dengan Google
        </a>

        <p class="text-center text-sm text-gray-500">
            Sudah punya akun?
            <a href="<?php echo e(route('login')); ?>" class="text-blue-600 font-semibold hover:underline">Masuk</a>
        </p>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\auth\register.blade.php ENDPATH**/ ?>