<form id="send-verification" method="post" action="<?php echo e(route('verification.send')); ?>"><?php echo csrf_field(); ?></form>

<form method="post" action="<?php echo e(route('profile.update')); ?>" class="space-y-4">
    <?php echo csrf_field(); ?> <?php echo method_field('patch'); ?>

    <div>
        <label for="name" class="block text-sm font-medium text-gray-500 mb-1.5">Nama</label>
        <input id="name" name="name" type="text" value="<?php echo e(old('name', $user->name)); ?>" required autofocus autocomplete="name"
            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                   <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-xs text-red-400"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-500 mb-1.5">Email</label>
        <input id="email" name="email" type="email" value="<?php echo e(old('email', $user->email)); ?>" required autocomplete="username"
            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                   <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500/50 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-xs text-red-400"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

        <?php if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail()): ?>
        <div class="mt-2 flex items-center gap-2">
            <p class="text-xs text-amber-400">Email belum diverifikasi.</p>
            <button form="send-verification" class="text-xs text-blue-400 hover:underline">Kirim ulang verifikasi</button>
        </div>
        <?php if(session('status') === 'verification-link-sent'): ?>
        <p class="mt-1 text-xs text-green-400">Link verifikasi baru telah dikirim.</p>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="flex items-center gap-3 pt-1">
        <button type="submit"
            class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition">
            Simpan
        </button>
        <?php if(session('status') === 'profile-updated'): ?>
        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
           class="text-sm text-green-400">Tersimpan.</p>
        <?php endif; ?>
    </div>
</form>

<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\profile\partials\update-profile-information-form.blade.php ENDPATH**/ ?>