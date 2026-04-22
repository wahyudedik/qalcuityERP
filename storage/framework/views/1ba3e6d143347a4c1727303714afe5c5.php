<div x-data="{ open: false }">
    <button @click="open = true"
        class="bg-red-500/20 hover:bg-red-500/30 text-red-400 text-sm font-semibold px-5 py-2.5 rounded-xl border border-red-500/20 transition">
        Hapus Akun
    </button>

    
    <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div @click.outside="open = false" class="bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-white/10 rounded-2xl shadow-xl w-full max-w-md p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">Hapus Akun?</p>
                    <p class="text-sm text-gray-500 dark:text-slate-400">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>

            <p class="text-sm text-gray-500 dark:text-slate-400 mb-5">
                Semua data akun Anda akan dihapus permanen. Masukkan password untuk konfirmasi.
            </p>

            <form method="post" action="<?php echo e(route('profile.destroy')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('delete'); ?>
                <div>
                    <label for="del_password" class="block text-sm font-medium text-gray-500 dark:text-slate-400 mb-1.5">Password</label>
                    <input id="del_password" name="password" type="password"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition
                               <?php if($errors->userDeletion->get('password')): ?> border-red-500/50 <?php endif; ?>"
                        placeholder="••••••••">
                    <?php if($errors->userDeletion->get('password')): ?>
                    <p class="mt-1.5 text-xs text-red-400"><?php echo e($errors->userDeletion->first('password')); ?></p>
                    <?php endif; ?>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" @click="open = false"
                        class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-slate-400 bg-gray-50 dark:bg-white/5 hover:bg-[#f8f8f8] dark:bg-white/10 rounded-xl border border-gray-200 dark:border-white/10 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-semibold text-gray-900 dark:text-white bg-red-600 hover:bg-red-700 rounded-xl transition">
                        Ya, Hapus Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\profile\partials\delete-user-form.blade.php ENDPATH**/ ?>