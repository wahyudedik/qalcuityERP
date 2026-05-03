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
     <?php $__env->slot('title', null, []); ?> Profil Saya — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> Profil Saya <?php $__env->endSlot(); ?>

    <div class="max-w-2xl space-y-5">

        
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">

                
                <div class="relative shrink-0 group" id="avatar-wrapper">
                    <img id="avatar-preview"
                        src="<?php echo e($user->avatarUrl()); ?>"
                        alt="<?php echo e($user->name); ?>"
                        class="w-24 h-24 rounded-2xl object-cover ring-4 ring-white shadow-md">

                    
                    <label for="avatar-input"
                        class="absolute inset-0 rounded-2xl bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition cursor-pointer">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </label>
                </div>

                
                <div class="text-center sm:text-left flex-1">
                    <h2 class="text-xl font-bold text-gray-900"><?php echo e($user->name); ?></h2>
                    <p class="text-sm text-gray-500 mt-0.5"><?php echo e($user->email); ?></p>
                    <div class="flex items-center justify-center sm:justify-start gap-2 mt-2">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                            <?php echo e(match($user->role) {
                                'admin'       => 'bg-blue-100 text-blue-700',
                                'manager'     => 'bg-purple-100 text-purple-700',
                                'super_admin' => 'bg-red-100 text-red-700',
                                default       => 'bg-gray-100 text-gray-600',
                            }); ?>">
                            <?php echo e(ucfirst(str_replace('_', ' ', $user->role))); ?>

                        </span>
                        <?php if($user->tenant): ?>
                        <span class="text-xs text-gray-400"><?php echo e($user->tenant->name); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if($user->avatar): ?>
                    <form method="POST" action="<?php echo e(route('profile.avatar.remove')); ?>" class="mt-3">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition">
                            Hapus foto
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <p class="font-semibold text-gray-900 mb-1">Informasi Profil</p>
            <p class="text-sm text-gray-500 mb-5">Perbarui nama, foto, dan informasi kontak Anda.</p>

            <?php if(session('status') === 'profile-updated'): ?>
            <div class="mb-4 flex items-center gap-2 text-sm text-green-600 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Profil berhasil diperbarui.
            </div>
            <?php endif; ?>
            <?php if(session('status') === 'avatar-removed'): ?>
            <div class="mb-4 flex items-center gap-2 text-sm text-green-600 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Foto profil dihapus.
            </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('profile.update')); ?>" enctype="multipart/form-data" class="space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>

                
                <input type="file" id="avatar-input" name="avatar" accept="image/jpg,image/jpeg,image/png,image/webp" class="sr-only">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" required autofocus
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">No. Telepon / WhatsApp</label>
                        <input type="text" name="phone" value="<?php echo e(old('phone', $user->phone)); ?>"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="08xxxxxxxxxx">
                        <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" required
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail()): ?>
                    <p class="mt-1 text-xs text-amber-500">Email belum diverifikasi.
                        <form id="send-verification" method="post" action="<?php echo e(route('verification.send')); ?>" class="inline"><?php echo csrf_field(); ?></form>
                        <button form="send-verification" class="underline">Kirim ulang</button>
                    </p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Bio <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <textarea name="bio" rows="3" maxlength="500"
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                        placeholder="Ceritakan sedikit tentang diri Anda..."><?php echo e(old('bio', $user->bio)); ?></textarea>
                    <p class="text-xs text-gray-400 mt-1 text-right"><span id="bio-count"><?php echo e(strlen($user->bio ?? '')); ?></span>/500</p>
                    <?php $__errorArgs = ['bio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div id="avatar-info" class="hidden text-xs text-blue-600 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Foto baru dipilih — klik Simpan untuk mengupload.
                </div>

                <div class="pt-1">
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <p class="font-semibold text-gray-900 mb-1">Ubah Password</p>
            <p class="text-sm text-gray-500 mb-5">Gunakan password yang kuat dan unik.</p>

            <?php if(session('status') === 'password-updated'): ?>
            <div class="mb-4 flex items-center gap-2 text-sm text-green-600 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Password berhasil diubah.
            </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('password.update')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password Saat Ini</label>
                    <input type="password" name="current_password" autocomplete="current-password"
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['current_password', 'updatePassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <?php $__errorArgs = ['current_password', 'updatePassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password Baru</label>
                        <input type="password" name="password" autocomplete="new-password"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['password', 'updatePassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php $__errorArgs = ['password', 'updatePassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-white text-sm font-semibold transition">
                    Ubah Password
                </button>
            </form>
        </div>

        
        <div class="bg-white rounded-2xl border border-red-200 p-6">
            <p class="font-semibold text-red-600 mb-1">Hapus Akun</p>
            <p class="text-sm text-gray-500 mb-5">Setelah dihapus, semua data akun Anda akan hilang permanen.</p>

            <button onclick="document.getElementById('delete-modal').classList.remove('hidden')"
                class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition">
                Hapus Akun Saya
            </button>
        </div>

    </div>

    
    <div id="delete-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl border border-gray-200 p-6 w-full max-w-md shadow-2xl">
            <h3 class="font-bold text-gray-900 text-lg mb-2">Hapus akun?</h3>
            <p class="text-sm text-gray-500 mb-5">Masukkan password Anda untuk konfirmasi. Tindakan ini tidak bisa dibatalkan.</p>
            <form method="POST" action="<?php echo e(route('profile.destroy')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <input type="password" name="password" placeholder="Password Anda" required
                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-500">
                <?php $__errorArgs = ['password', 'userDeletion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition">
                        Ya, Hapus Akun
                    </button>
                    <button type="button" onclick="document.getElementById('delete-modal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        // Avatar preview on file select
        document.getElementById('avatar-input').addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatar-preview').src = e.target.result;
                document.getElementById('avatar-info').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        });

        // Bio character counter
        const bioEl = document.querySelector('textarea[name="bio"]');
        const bioCount = document.getElementById('bio-count');
        if (bioEl && bioCount) {
            bioEl.addEventListener('input', function() {
                bioCount.textContent = this.value.length;
            });
        }
    </script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\profile\edit.blade.php ENDPATH**/ ?>