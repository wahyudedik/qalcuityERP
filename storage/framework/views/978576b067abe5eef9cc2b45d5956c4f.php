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
     <?php $__env->slot('title', null, []); ?> Tambah Pengguna — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> Tambah Pengguna <?php $__env->endSlot(); ?>

    <div class="max-w-lg">
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <form method="POST" action="<?php echo e(route('tenant.users.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                    <input id="name" type="text" name="name" value="<?php echo e(old('name')); ?>" required autofocus
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                               <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
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
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                               <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
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
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1.5">Role</label>
                    <select id="role" name="role"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <option value="manager" <?php echo e(old('role') === 'manager' ? 'selected' : ''); ?>>Manajer — akses penuh semua modul</option>
                        <option value="staff"   <?php echo e(old('role') === 'staff'   ? 'selected' : ''); ?>>Staff — akses terbatas (baca + POS)</option>
                        <option value="kasir"   <?php echo e(old('role') === 'kasir'   ? 'selected' : ''); ?>>Kasir — hanya akses POS & penjualan</option>
                        <option value="gudang"  <?php echo e(old('role') === 'gudang'  ? 'selected' : ''); ?>>Gudang — hanya akses inventori & stok</option>
                    </select>
                    <p class="text-xs text-slate-500 mt-1.5">Pilih role sesuai divisi pengguna. Role menentukan menu dan AI tools yang bisa diakses.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                                   <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            placeholder="••••••••">
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-xs text-red-400"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="<?php echo e(route('tenant.users.index')); ?>"
                       class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-900 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\tenant\users\create.blade.php ENDPATH**/ ?>