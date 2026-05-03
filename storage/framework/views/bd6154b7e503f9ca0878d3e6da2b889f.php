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
     <?php $__env->slot('header', null, []); ?> Manajemen Pengeluaran <?php $__env->endSlot(); ?>

    <div class="space-y-5">

        <?php if(session('success')): ?>
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500">Pengeluaran Bulan Ini</p>
                <p class="text-2xl font-bold text-red-400 mt-1">Rp <?php echo e(number_format($thisMonth, 0, ',', '.')); ?></p>
                <?php $diff = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 0; ?>
                <p class="text-xs mt-1 <?php echo e($diff > 0 ? 'text-red-400' : 'text-green-400'); ?>">
                    <?php echo e($diff > 0 ? '▲' : '▼'); ?> <?php echo e(abs(round($diff, 1))); ?>% vs bulan lalu
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500">Bulan Lalu</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp <?php echo e(number_format($lastMonth, 0, ',', '.')); ?></p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-2">Top Kategori Bulan Ini</p>
                <?php $__currentLoopData = $topCategories->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-600"><?php echo e($tc->category->name ?? 'Lainnya'); ?></span>
                        <span class="font-medium text-gray-900">Rp <?php echo e(number_format($tc->total, 0, ',', '.')); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'expenses', 'create')): ?>
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Catat Pengeluaran</h2>
                <form method="POST" action="<?php echo e(route('expenses.store')); ?>" enctype="multipart/form-data" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Kategori <span class="text-red-400">*</span></label>
                        <select name="expense_category_id" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih kategori...</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($cat->id); ?>" <?php echo e(old('expense_category_id') == $cat->id ? 'selected' : ''); ?>><?php echo e($cat->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['expense_category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-400 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tanggal <span class="text-red-400">*</span></label>
                        <input type="date" name="date" value="<?php echo e(old('date', today()->format('Y-m-d'))); ?>" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Jumlah (Rp) <span class="text-red-400">*</span></label>
                        <input type="number" name="amount" value="<?php echo e(old('amount')); ?>" min="0.01" step="100" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-400 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Metode Pembayaran <span class="text-red-400">*</span></label>
                        <select name="payment_method" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Tunai</option>
                            <option value="transfer">Transfer</option>
                            <option value="card">Kartu</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Keterangan <span class="text-red-400">*</span></label>
                        <input type="text" name="description" value="<?php echo e(old('description')); ?>" required placeholder="Deskripsi pengeluaran"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">No. Referensi</label>
                        <input type="text" name="reference" value="<?php echo e(old('reference')); ?>" placeholder="No. nota / kwitansi"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Lampiran (foto/PDF)</label>
                        <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                    </div>
                    <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                        Catat Pengeluaran
                    </button>
                </form>
            </div>
            <?php endif; ?>

            
            <div class="lg:col-span-2 space-y-4">

                
                <form method="GET" class="bg-white rounded-2xl border border-gray-200 p-4 flex flex-wrap gap-3">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari keterangan..."
                        class="flex-1 min-w-[150px] bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none">
                    <select name="category_id" class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none">
                        <option value="">Semua Kategori</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->id); ?>" <?php echo e(request('category_id') == $cat->id ? 'selected' : ''); ?>><?php echo e($cat->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                        class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none">
                    <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"
                        class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none">
                    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm hover:bg-gray-200 transition">Filter</button>
                    <a href="<?php echo e(route('expenses.categories')); ?>" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm hover:bg-gray-200 transition">Kategori</a>
                </form>

                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <?php if($expenses->isEmpty()): ?>
                        <div class="px-6 py-12 text-center text-gray-400 text-sm">Belum ada pengeluaran.</div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 text-xs text-gray-500">
                                        <th class="px-4 py-3 text-left">Tanggal</th>
                                        <th class="px-4 py-3 text-left">Kategori</th>
                                        <th class="px-4 py-3 text-left">Keterangan</th>
                                        <th class="px-4 py-3 text-right">Jumlah</th>
                                        <th class="px-4 py-3 text-left">Metode</th>
                                        <th class="px-4 py-3 text-left">GL</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <?php $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-3 text-xs text-gray-500"><?php echo e($exp->date->format('d/m/Y')); ?></td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs">
                                                    <?php echo e($exp->category->name ?? '-'); ?>

                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-700">
                                                <?php echo e($exp->description); ?>

                                                <?php if($exp->reference): ?>
                                                    <span class="text-xs text-gray-400 ml-1">#<?php echo e($exp->reference); ?></span>
                                                <?php endif; ?>
                                                <?php if($exp->attachment): ?>
                                                    <a href="<?php echo e($exp->attachment); ?>" target="_blank" class="ml-1 text-blue-400 text-xs">📎</a>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 text-right font-medium text-red-400">
                                                Rp <?php echo e(number_format($exp->amount, 0, ',', '.')); ?>

                                            </td>
                                            <td class="px-4 py-3 text-xs text-gray-500 capitalize"><?php echo e($exp->payment_method); ?></td>
                                            <td class="px-4 py-3">
                                                <?php if($exp->journalEntry): ?>
                                                    <span title="Jurnal: <?php echo e($exp->journalEntry->number); ?>"
                                                        class="inline-flex items-center gap-1 text-xs px-2 py-0.5 bg-green-500/10 text-green-400 rounded-full border border-green-500/20">
                                                        ✓ GL
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-xs text-gray-400">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3">
                                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'expenses', 'delete')): ?>
                                                <form method="POST" action="<?php echo e(route('expenses.destroy', $exp)); ?>"
                                                    onsubmit="return confirm('Hapus pengeluaran ini?')">
                                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="text-xs text-red-400 hover:text-red-300 transition">Hapus</button>
                                                </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if($expenses->hasPages()): ?>
                            <div class="px-6 py-4 border-t border-gray-100"><?php echo e($expenses->links()); ?></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\expenses\index.blade.php ENDPATH**/ ?>