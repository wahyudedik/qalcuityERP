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
     <?php $__env->slot('header', null, []); ?> Periode Akuntansi <?php $__env->endSlot(); ?>

    <div class="space-y-5">

        <?php if(session('success')): ?>
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="flex justify-end">
            <button onclick="document.getElementById('modal-add-period').classList.remove('hidden')"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">
                + Buat Periode Baru
            </button>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <table class="w-full text-sm text-gray-300">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama Periode</th>
                        <th class="px-4 py-3 text-left">Mulai</th>
                        <th class="px-4 py-3 text-left">Selesai</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left">Ditutup Oleh</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3 font-medium text-white"><?php echo e($period->name); ?></td>
                        <td class="px-4 py-3"><?php echo e($period->start_date->format('d M Y')); ?></td>
                        <td class="px-4 py-3"><?php echo e($period->end_date->format('d M Y')); ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs
                                <?php echo e($period->status === 'open' ? 'bg-green-500/20 text-green-400' : ''); ?>

                                <?php echo e($period->status === 'closed' ? 'bg-yellow-500/20 text-yellow-400' : ''); ?>

                                <?php echo e($period->status === 'locked' ? 'bg-red-500/20 text-red-400' : ''); ?>">
                                <?php echo e(ucfirst($period->status)); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            <?php echo e($period->closedBy?->name ?? '-'); ?>

                            <?php if($period->closed_at): ?> <span class="text-xs">(<?php echo e($period->closed_at->format('d/m/Y')); ?>)</span> <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                <?php if($period->status === 'open'): ?>
                                <form method="POST" action="<?php echo e(route('accounting.periods.close', $period)); ?>" onsubmit="return confirm('Tutup periode ini?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                    <button class="text-yellow-400 hover:text-yellow-300 text-xs">Tutup</button>
                                </form>
                                <?php endif; ?>
                                <?php if($period->status === 'closed'): ?>
                                <form method="POST" action="<?php echo e(route('accounting.periods.lock', $period)); ?>" onsubmit="return confirm('Kunci periode ini? Tidak bisa dibuka kembali.')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                    <button class="text-red-400 hover:text-red-300 text-xs">🔒 Kunci</button>
                                </form>
                                <?php endif; ?>
                                <?php if($period->status === 'locked'): ?>
                                <span class="text-gray-600 text-xs">🔒 Terkunci</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada periode akuntansi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div id="modal-add-period" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-md p-6">
            <h3 class="text-white font-semibold mb-4">Buat Periode Akuntansi</h3>
            <form method="POST" action="<?php echo e(route('accounting.periods.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="text-xs text-gray-400 mb-1 block">Nama Periode *</label>
                    <input type="text" name="name" required placeholder="Maret 2026"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Tanggal Mulai *</label>
                        <input type="date" name="start_date" required
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Tanggal Selesai *</label>
                        <input type="date" name="end_date" required
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm">Buat</button>
                    <button type="button" onclick="document.getElementById('modal-add-period').classList.add('hidden')"
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\accounting\periods.blade.php ENDPATH**/ ?>