<form method="post" action="<?php echo e(route('password.update')); ?>" class="space-y-4">
    <?php echo csrf_field(); ?> <?php echo method_field('put'); ?>

    <div>
        <label for="update_password_current_password" class="block text-sm font-medium text-gray-500 mb-1.5">Password Saat Ini</label>
        <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                   <?php if($errors->updatePassword->get('current_password')): ?> border-red-500/50 <?php endif; ?>"
            placeholder="••••••••">
        <?php if($errors->updatePassword->get('current_password')): ?>
        <p class="mt-1.5 text-xs text-red-400"><?php echo e($errors->updatePassword->first('current_password')); ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="update_password_password" class="block text-sm font-medium text-gray-500 mb-1.5">Password Baru</label>
        <input id="update_password_password" name="password" type="password" autocomplete="new-password"
            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                   <?php if($errors->updatePassword->get('password')): ?> border-red-500/50 <?php endif; ?>"
            placeholder="••••••••">
        <?php if($errors->updatePassword->get('password')): ?>
        <p class="mt-1.5 text-xs text-red-400"><?php echo e($errors->updatePassword->first('password')); ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-500 mb-1.5">Konfirmasi Password Baru</label>
        <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
            placeholder="••••••••">
    </div>

    <div class="flex items-center gap-3 pt-1">
        <button type="submit"
            class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition">
            Perbarui Password
        </button>
        <?php if(session('status') === 'password-updated'): ?>
        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
           class="text-sm text-green-400">Tersimpan.</p>
        <?php endif; ?>
    </div>
</form>

<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\profile\partials\update-password-form.blade.php ENDPATH**/ ?>