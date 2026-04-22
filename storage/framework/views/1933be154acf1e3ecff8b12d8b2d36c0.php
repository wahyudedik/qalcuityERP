

<?php $__env->startSection('title', 'Buka Sesi Kasir'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-4 sm:p-6 max-w-lg mx-auto space-y-6">

    
    <div>
        <a href="<?php echo e(route('pos.sessions.index')); ?>"
            class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-3 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Buka Sesi Kasir</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Masukkan modal awal dan informasi kasir</p>
    </div>

    
    <form method="POST" action="<?php echo e(route('pos.sessions.store')); ?>"
        class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 space-y-5">
        <?php echo csrf_field(); ?>

        
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kasir</label>
            <input type="text" value="<?php echo e(auth()->user()->name); ?>" readonly
                class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 cursor-not-allowed">
        </div>

        
        <div>
            <label for="register_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Nama Register / Terminal
            </label>
            <input type="text" id="register_name" name="register_name"
                value="<?php echo e(old('register_name', 'Kasir Utama')); ?>"
                placeholder="Contoh: Kasir 1, Terminal A"
                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition">
            <?php $__errorArgs = ['register_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1 text-xs text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <?php if($warehouses->isNotEmpty()): ?>
        <div>
            <label for="warehouse_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Gudang / Lokasi
            </label>
            <select id="warehouse_id" name="warehouse_id"
                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition">
                <option value="">— Pilih Gudang (opsional) —</option>
                <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($warehouse->id); ?>" <?php echo e(old('warehouse_id') == $warehouse->id ? 'selected' : ''); ?>>
                        <?php echo e($warehouse->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['warehouse_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1 text-xs text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <?php endif; ?>

        
        <div>
            <label for="opening_balance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Modal Awal (Kas di Laci) <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400 font-medium">Rp</span>
                <input type="number" id="opening_balance" name="opening_balance"
                    value="<?php echo e(old('opening_balance', 0)); ?>"
                    min="0" step="1000" required
                    class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl pl-10 pr-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition">
            </div>
            <?php $__errorArgs = ['opening_balance'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1 text-xs text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Catatan (opsional)
            </label>
            <textarea id="notes" name="notes" rows="2"
                placeholder="Catatan tambahan untuk sesi ini..."
                class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition resize-none"><?php echo e(old('notes')); ?></textarea>
            <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1 text-xs text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl px-4 py-3">
            <p class="text-xs text-blue-700 dark:text-blue-300">
                <span class="font-medium">Waktu buka:</span> <?php echo e(now()->format('d/m/Y H:i:s')); ?>

            </p>
        </div>

        
        <div class="flex gap-3 pt-2">
            <a href="<?php echo e(route('pos.sessions.index')); ?>"
                class="flex-1 text-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                Batal
            </a>
            <button type="submit"
                class="flex-1 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition">
                Buka Sesi Kasir
            </button>
        </div>
    </form>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\pos\sessions\create.blade.php ENDPATH**/ ?>