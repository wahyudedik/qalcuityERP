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
     <?php $__env->slot('header', null, []); ?> Manajemen Tarif Pajak <?php $__env->endSlot(); ?>

    <div class="max-w-4xl mx-auto space-y-6">

        <?php if(session('success')): ?>
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">
                <?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><?php echo e($e); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        
        <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-xl p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-white text-sm font-medium">Export e-Faktur DJP</p>
                    <p class="text-gray-400 text-xs mt-0.5">Export data PPN ke format CSV siap import ke aplikasi
                        e-Faktur DJP</p>
                </div>
                <form method="GET" action="<?php echo e(route('taxes.efaktur')); ?>" class="flex gap-2 items-center">
                    <input type="date" name="from" value="<?php echo e(now()->startOfMonth()->toDateString()); ?>"
                        class="bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <input type="date" name="to" value="<?php echo e(now()->toDateString()); ?>"
                        class="bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg">
                        ⬇ Export CSV
                    </button>
                </form>
            </div>
        </div>

        
        <div class="bg-white/5 border border-white/10 rounded-xl p-6">
            <h2 class="font-semibold text-white mb-4">Tambah Tarif Pajak</h2>

            <form method="POST" action="<?php echo e(route('taxes.store')); ?>" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Nama Pajak *</label>
                    <input type="text" name="name" value="<?php echo e(old('name')); ?>" placeholder="cth: PPN 11%"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Kode *</label>
                    <input type="text" name="code" value="<?php echo e(old('code')); ?>" placeholder="cth: PPN11"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Jenis Pajak *</label>
                    <select name="tax_type" required
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="ppn" <?php if(old('tax_type') == 'ppn'): echo 'selected'; endif; ?>>PPN (Pajak Pertambahan Nilai)</option>
                        <option value="pph21" <?php if(old('tax_type') == 'pph21'): echo 'selected'; endif; ?>>PPh 21 (Penghasilan Karyawan)</option>
                        <option value="pph23" <?php if(old('tax_type') == 'pph23'): echo 'selected'; endif; ?>>PPh 23 (Jasa/Royalti)</option>
                        <option value="pph4ayat2" <?php if(old('tax_type') == 'pph4ayat2'): echo 'selected'; endif; ?>>PPh 4 Ayat 2 (Final)</option>
                        <option value="custom" <?php if(old('tax_type') == 'custom'): echo 'selected'; endif; ?>>Custom</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Tipe Perhitungan *</label>
                    <select name="type"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="percentage" <?php if(old('type') == 'percentage'): echo 'selected'; endif; ?>>Persentase (%)</option>
                        <option value="fixed" <?php if(old('type') == 'fixed'): echo 'selected'; endif; ?>>Nominal Tetap (Rp)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Tarif *</label>
                    <input type="number" name="rate" value="<?php echo e(old('rate')); ?>" placeholder="cth: 11"
                        step="0.01" min="0" max="100"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Kode Akun GL (opsional)</label>
                    <input type="text" name="account_code" value="<?php echo e(old('account_code')); ?>" placeholder="cth: 2103"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="sm:col-span-2 flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_withholding" value="1" <?php if(old('is_withholding')): echo 'checked'; endif; ?>
                            class="rounded border-white/20 bg-white/5 text-indigo-500">
                        <span class="text-sm text-gray-300">Pajak Pemotongan (Withholding Tax) — PPh 21/23/4(2)</span>
                    </label>
                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl">
                        Tambah Tarif
                    </button>
                </div>
            </form>
        </div>

        
        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10">
                <h2 class="font-semibold text-white">Daftar Tarif Pajak</h2>
            </div>

            <?php if($taxes->isEmpty()): ?>
                <div class="px-6 py-10 text-center text-gray-500 text-sm">Belum ada tarif pajak.</div>
            <?php else: ?>
                <div class="divide-y divide-white/5">
                    <?php $__currentLoopData = $taxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="px-6 py-4 flex items-start gap-4">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-medium text-white text-sm"><?php echo e($tax->name); ?></span>
                                    <span
                                        class="px-2 py-0.5 bg-white/10 text-gray-400 text-xs rounded-full font-mono"><?php echo e($tax->code); ?></span>
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-full
                                    <?php echo e($tax->tax_type === 'ppn' ? 'bg-blue-500/20 text-blue-400' : ''); ?>

                                    <?php echo e(in_array($tax->tax_type, ['pph21', 'pph23', 'pph4ayat2']) ? 'bg-orange-500/20 text-orange-400' : ''); ?>

                                    <?php echo e($tax->tax_type === 'custom' ? 'bg-gray-500/20 text-gray-400' : ''); ?>">
                                        <?php echo e($tax->getTypeLabel()); ?>

                                    </span>
                                    <?php if($tax->is_withholding): ?>
                                        <span
                                            class="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 text-xs rounded-full">Withholding</span>
                                    <?php endif; ?>
                                    <?php if($tax->is_active): ?>
                                        <span
                                            class="px-2 py-0.5 bg-green-500/20 text-green-400 text-xs rounded-full">Aktif</span>
                                    <?php else: ?>
                                        <span
                                            class="px-2 py-0.5 bg-gray-500/20 text-gray-400 text-xs rounded-full">Nonaktif</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php echo e($tax->type === 'percentage' ? $tax->rate . '%' : 'Rp ' . number_format($tax->rate, 0, ',', '.')); ?>

                                    <?php if($tax->account_code): ?>
                                        &bull; Akun GL: <span class="font-mono"><?php echo e($tax->account_code); ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                
                                <form method="POST" action="<?php echo e(route('taxes.update', $tax)); ?>">
                                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                    <input type="hidden" name="name" value="<?php echo e($tax->name); ?>">
                                    <input type="hidden" name="code" value="<?php echo e($tax->code); ?>">
                                    <input type="hidden" name="type" value="<?php echo e($tax->type); ?>">
                                    <input type="hidden" name="tax_type" value="<?php echo e($tax->tax_type ?? 'ppn'); ?>">
                                    <input type="hidden" name="rate" value="<?php echo e($tax->rate); ?>">
                                    <input type="hidden" name="is_withholding"
                                        value="<?php echo e($tax->is_withholding ? '1' : '0'); ?>">
                                    <input type="hidden" name="account_code" value="<?php echo e($tax->account_code); ?>">
                                    <input type="hidden" name="is_active"
                                        value="<?php echo e($tax->is_active ? '0' : '1'); ?>">
                                    <button type="submit"
                                        class="text-xs px-3 py-1.5 rounded-lg border <?php echo e($tax->is_active ? 'border-yellow-500/30 text-yellow-400 hover:bg-yellow-500/10' : 'border-green-500/30 text-green-400 hover:bg-green-500/10'); ?>">
                                        <?php echo e($tax->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>

                                    </button>
                                </form>

                                <form method="POST" action="<?php echo e(route('taxes.destroy', $tax)); ?>"
                                    onsubmit="return confirm('Hapus tarif pajak <?php echo e($tax->name); ?>?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit"
                                        class="text-xs px-3 py-1.5 rounded-lg border border-red-500/30 text-red-400 hover:bg-red-500/10">Hapus</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl px-4 py-3 text-sm text-blue-400">
            <strong>Jenis Pajak:</strong> PPN = Pajak Pertambahan Nilai (dikenakan ke pelanggan). PPh = Pajak
            Penghasilan (dipotong dari pembayaran). Withholding Tax = pajak yang dipotong oleh pembeli saat membayar.
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/settings/taxes.blade.php ENDPATH**/ ?>