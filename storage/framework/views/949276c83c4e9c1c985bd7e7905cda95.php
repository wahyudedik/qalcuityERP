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
     <?php $__env->slot('header', null, []); ?> Kategori Pengeluaran <?php $__env->endSlot(); ?>

    <div class="max-w-4xl mx-auto space-y-5">

        <?php if(session('success')): ?>
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Tambah Kategori</h2>
            <form method="POST" action="<?php echo e(route('expenses.categories.store')); ?>" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Nama Kategori</label>
                    <input type="text" name="name" value="<?php echo e(old('name')); ?>" required placeholder="cth: Biaya Operasional"
                        class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-400 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Kode</label>
                    <input type="text" name="code" value="<?php echo e(old('code')); ?>" required placeholder="cth: OPS"
                        class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-400 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Tipe</label>
                    <select name="type" class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="operational">Operasional</option>
                        <option value="cogs">HPP (COGS)</option>
                        <option value="marketing">Marketing</option>
                        <option value="hr">SDM / HR</option>
                        <option value="admin">Administrasi</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">
                        Kode Akun GL
                        <span class="text-gray-400 font-normal">(opsional — override akun beban di jurnal)</span>
                    </label>
                    <input type="text" name="coa_account_code" value="<?php echo e(old('coa_account_code')); ?>" placeholder="cth: 5206 (kosong = otomatis)"
                        class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Deskripsi</label>
                    <input type="text" name="description" value="<?php echo e(old('description')); ?>" placeholder="Opsional"
                        class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="sm:col-span-2 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Tambah Kategori</button>
                </div>
            </form>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Daftar Kategori</h2>
                <a href="<?php echo e(route('expenses.index')); ?>" class="text-sm text-gray-400 hover:text-white transition">← Kembali</a>
            </div>
            <?php if($categories->isEmpty()): ?>
                <div class="px-6 py-10 text-center text-gray-400 text-sm">Belum ada kategori.</div>
            <?php else: ?>
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="px-6 py-4 flex items-center gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900 dark:text-white text-sm"><?php echo e($cat->name); ?></span>
                                    <span class="font-mono text-xs px-2 py-0.5 bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-slate-400 rounded-full"><?php echo e($cat->code); ?></span>
                                    <?php if(!$cat->is_active): ?>
                                        <span class="text-xs px-2 py-0.5 bg-gray-500/20 text-gray-400 rounded-full">Nonaktif</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                                    <?php echo e(ucfirst($cat->type)); ?> &bull; <?php echo e($cat->expense_count); ?> transaksi
                                    <?php if($cat->coa_account_code): ?>
                                    &bull; <span class="font-mono text-blue-500 dark:text-blue-400">GL: <?php echo e($cat->coa_account_code); ?></span>
                                    <?php else: ?>
                                    &bull; <span class="text-gray-400">GL: otomatis</span>
                                    <?php endif; ?>
                                    <?php if($cat->description): ?> &bull; <?php echo e($cat->description); ?> <?php endif; ?>
                                </p>
                            </div>
                            <form method="POST" action="<?php echo e(route('expenses.categories.update', $cat)); ?>">
                                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                <input type="hidden" name="name" value="<?php echo e($cat->name); ?>">
                                <input type="hidden" name="code" value="<?php echo e($cat->code); ?>">
                                <input type="hidden" name="type" value="<?php echo e($cat->type); ?>">
                                <input type="hidden" name="coa_account_code" value="<?php echo e($cat->coa_account_code); ?>">
                                <input type="hidden" name="description" value="<?php echo e($cat->description); ?>">
                                <input type="hidden" name="is_active" value="<?php echo e($cat->is_active ? '0' : '1'); ?>">
                                <button type="submit"
                                    class="text-xs px-3 py-1.5 rounded-lg border <?php echo e($cat->is_active ? 'border-yellow-500/30 text-yellow-400 hover:bg-yellow-500/10' : 'border-green-500/30 text-green-400 hover:bg-green-500/10'); ?> transition">
                                    <?php echo e($cat->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>

                                </button>
                            </form>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\expenses\categories.blade.php ENDPATH**/ ?>