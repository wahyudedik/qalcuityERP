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
     <?php $__env->slot('header', null, []); ?> Jurnal Umum <?php $__env->endSlot(); ?>

    <div class="space-y-5">

        <?php if(session('success')): ?>
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nomor / deskripsi..."
                    class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 w-52">
                <select name="status" class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="draft" <?php if(request('status')=='draft'): echo 'selected'; endif; ?>>Draft</option>
                    <option value="posted" <?php if(request('status')=='posted'): echo 'selected'; endif; ?>>Posted</option>
                    <option value="reversed" <?php if(request('status')=='reversed'): echo 'selected'; endif; ?>>Reversed</option>
                </select>
                <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                    class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"
                    class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
            </form>
            <div class="flex gap-2">
                <a href="<?php echo e(route('journals.recurring')); ?>" class="bg-white/10 hover:bg-white/20 text-white text-sm px-4 py-2 rounded-lg">🔄 Jurnal Berulang</a>
                <a href="<?php echo e(route('journals.create')); ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">+ Buat Jurnal</a>
            </div>
        </div>

        
        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <table class="w-full text-sm text-gray-300">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-left">Periode</th>
                        <th class="px-4 py-3 text-right">Total Debit</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $journals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs text-indigo-400"><?php echo e($j->number); ?></td>
                        <td class="px-4 py-3"><?php echo e($j->date->format('d/m/Y')); ?></td>
                        <td class="px-4 py-3"><?php echo e(Str::limit($j->description, 50)); ?></td>
                        <td class="px-4 py-3 text-gray-500 text-xs"><?php echo e($j->period?->name ?? '-'); ?></td>
                        <td class="px-4 py-3 text-right">Rp <?php echo e(number_format($j->lines->sum('debit'), 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs
                                <?php echo e($j->status === 'draft' ? 'bg-yellow-500/20 text-yellow-400' : ''); ?>

                                <?php echo e($j->status === 'posted' ? 'bg-green-500/20 text-green-400' : ''); ?>

                                <?php echo e($j->status === 'reversed' ? 'bg-gray-500/20 text-gray-400' : ''); ?>">
                                <?php echo e(ucfirst($j->status)); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="<?php echo e(route('journals.show', $j)); ?>" class="text-indigo-400 hover:text-indigo-300 text-xs">Detail</a>
                                <?php if($j->status === 'draft'): ?>
                                <form method="POST" action="<?php echo e(route('journals.post', $j)); ?>" onsubmit="return confirm('Post jurnal ini?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                    <button class="text-green-400 hover:text-green-300 text-xs">Post</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada jurnal.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo e($journals->links()); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/accounting/journals/index.blade.php ENDPATH**/ ?>