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
     <?php $__env->slot('header', null, []); ?> Jurnal Berulang (Recurring) <?php $__env->endSlot(); ?>

    <div class="space-y-5">

        <?php if(session('success')): ?>
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <div class="flex justify-between items-center">
            <a href="<?php echo e(route('journals.index')); ?>" class="text-gray-400 hover:text-white text-sm">← Kembali ke Jurnal</a>
            <button onclick="document.getElementById('modal-add-recurring').classList.remove('hidden')"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">+ Tambah Jurnal Berulang</button>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <table class="w-full text-sm text-gray-300">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left">Frekuensi</th>
                        <th class="px-4 py-3 text-left">Mulai</th>
                        <th class="px-4 py-3 text-left">Selesai</th>
                        <th class="px-4 py-3 text-left">Jadwal Berikutnya</th>
                        <th class="px-4 py-3 text-left">Terakhir Dijalankan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $recurring; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3 font-medium text-white"><?php echo e($r->name); ?></td>
                        <td class="px-4 py-3 capitalize"><?php echo e($r->frequency); ?></td>
                        <td class="px-4 py-3"><?php echo e($r->start_date->format('d/m/Y')); ?></td>
                        <td class="px-4 py-3"><?php echo e($r->end_date?->format('d/m/Y') ?? '∞'); ?></td>
                        <td class="px-4 py-3 text-indigo-400"><?php echo e($r->next_run_date->format('d/m/Y')); ?></td>
                        <td class="px-4 py-3 text-gray-500"><?php echo e($r->last_run_date?->format('d/m/Y') ?? '-'); ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs <?php echo e($r->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400'); ?>">
                                <?php echo e($r->is_active ? 'Aktif' : 'Nonaktif'); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <form method="POST" action="<?php echo e(route('journals.recurring.toggle', $r)); ?>">
                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                <button class="text-xs <?php echo e($r->is_active ? 'text-yellow-400 hover:text-yellow-300' : 'text-green-400 hover:text-green-300'); ?>">
                                    <?php echo e($r->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>

                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Belum ada jurnal berulang.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div id="modal-add-recurring" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4 overflow-y-auto">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-2xl p-6 my-4">
            <h3 class="text-white font-semibold mb-4">Tambah Jurnal Berulang</h3>
            <form method="POST" action="<?php echo e(route('journals.recurring.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="text-xs text-gray-400 mb-1 block">Nama *</label>
                        <input type="text" name="name" required placeholder="Biaya Sewa Bulanan"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Frekuensi *</label>
                        <select name="frequency" required class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="monthly">Bulanan</option>
                            <option value="weekly">Mingguan</option>
                            <option value="quarterly">Triwulan</option>
                            <option value="yearly">Tahunan</option>
                            <option value="daily">Harian</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Tanggal Mulai *</label>
                        <input type="date" name="start_date" required value="<?php echo e(date('Y-m-d')); ?>"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Tanggal Selesai (opsional)</label>
                        <input type="date" name="end_date"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div class="border-t border-white/10 pt-4">
                    <h4 class="text-white text-sm font-medium mb-3">Baris Jurnal (min. 2)</h4>
                    <div id="rec-lines" class="space-y-2">
                        <?php for($i = 0; $i < 2; $i++): ?>
                        <div class="grid grid-cols-12 gap-2">
                            <div class="col-span-5">
                                <select name="lines[<?php echo e($i); ?>][account_id]" required class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                                    <option value="">— Akun —</option>
                                    <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($acc->id); ?>"><?php echo e($acc->code); ?> - <?php echo e($acc->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-span-3">
                                <input type="number" name="lines[<?php echo e($i); ?>][debit]" placeholder="Debit" min="0" step="0.01"
                                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div class="col-span-3">
                                <input type="number" name="lines[<?php echo e($i); ?>][credit]" placeholder="Kredit" min="0" step="0.01"
                                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div class="col-span-1 flex items-center justify-center text-gray-600 text-xs"><?php echo e($i + 1); ?></div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm">Simpan</button>
                    <button type="button" onclick="document.getElementById('modal-add-recurring').classList.add('hidden')"
                        class="flex-1 bg-white/10 hover:bg-white/20 text-white py-2 rounded-lg text-sm">Batal</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\accounting\journals\recurring.blade.php ENDPATH**/ ?>