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
     <?php $__env->slot('header', null, []); ?> Manajemen Proyek <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <?php $__currentLoopData = [
            ['label'=>'Total Proyek','value'=>$stats['total'],'color'=>'text-gray-900 dark:text-white'],
            ['label'=>'Aktif','value'=>$stats['active'],'color'=>'text-blue-600 dark:text-blue-400'],
            ['label'=>'Selesai','value'=>$stats['completed'],'color'=>'text-green-600 dark:text-green-400'],
            ['label'=>'Terlambat','value'=>$stats['overdue'],'color'=>'text-red-600 dark:text-red-400'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($s['label']); ?></p>
            <p class="text-2xl font-bold <?php echo e($s['color']); ?> mt-1"><?php echo e($s['value']); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama / nomor proyek..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = ['planning'=>'Perencanaan','active'=>'Aktif','on_hold'=>'Ditunda','completed'=>'Selesai','cancelled'=>'Dibatalkan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($v); ?>" <?php if(request('status')===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'projects', 'create')): ?>
        <button onclick="document.getElementById('modal-add-project').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 shrink-0">+ Proyek Baru</button>
        <?php endif; ?>
    </div>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php $__empty_1 = true; $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $statusColors = ['planning'=>'gray','active'=>'blue','on_hold'=>'amber','completed'=>'green','cancelled'=>'red'];
            $statusLabels = ['planning'=>'Perencanaan','active'=>'Aktif','on_hold'=>'Ditunda','completed'=>'Selesai','cancelled'=>'Dibatalkan'];
            $c = $statusColors[$project->status] ?? 'gray';
            $overBudget = $project->budget > 0 && $project->actual_cost > $project->budget;
            $overdue = $project->end_date && $project->end_date->isPast() && !in_array($project->status, ['completed','cancelled']);
        ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 flex flex-col gap-3 hover:shadow-md transition">
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <a href="<?php echo e(route('projects.show', $project)); ?>" class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 line-clamp-1"><?php echo e($project->name); ?></a>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5"><?php echo e($project->number); ?> <?php echo e($project->customer ? '· '.$project->customer->name : ''); ?></p>
                </div>
                <span class="shrink-0 px-2 py-0.5 rounded-full text-xs bg-<?php echo e($c); ?>-100 text-<?php echo e($c); ?>-700 dark:bg-<?php echo e($c); ?>-500/20 dark:text-<?php echo e($c); ?>-400">
                    <?php echo e($statusLabels[$project->status] ?? $project->status); ?>

                </span>
            </div>

            
            <div>
                <div class="flex justify-between text-xs text-gray-500 dark:text-slate-400 mb-1">
                    <span>Progress</span>
                    <span><?php echo e(number_format($project->progress, 0)); ?>%</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-white/10 rounded-full h-2">
                    <div class="h-2 rounded-full <?php echo e($project->progress >= 100 ? 'bg-green-500' : 'bg-blue-500'); ?>" style="width:<?php echo e(min(100,$project->progress)); ?>%"></div>
                </div>
            </div>

            
            <?php if($project->budget > 0): ?>
            <div class="flex justify-between text-xs">
                <span class="text-gray-500 dark:text-slate-400">Anggaran</span>
                <span class="<?php echo e($overBudget ? 'text-red-500 font-semibold' : 'text-gray-700 dark:text-slate-300'); ?>">
                    Rp <?php echo e(number_format($project->actual_cost,0,',','.')); ?> / Rp <?php echo e(number_format($project->budget,0,',','.')); ?>

                    <?php if($overBudget): ?> ⚠️ <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>

            
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-slate-400">
                <span>
                    <?php if($project->end_date): ?>
                        <?php echo e($overdue ? '⚠️ ' : ''); ?>Deadline: <span class="<?php echo e($overdue ? 'text-red-500 font-medium' : ''); ?>"><?php echo e($project->end_date->format('d M Y')); ?></span>
                    <?php else: ?>
                        Tanpa deadline
                    <?php endif; ?>
                </span>
                <span><?php echo e($project->tasks->count()); ?> task</span>
            </div>

            <a href="<?php echo e(route('projects.show', $project)); ?>"
                class="mt-auto text-center text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                Lihat Detail →
            </a>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-span-full py-16 text-center text-gray-400 dark:text-slate-500">
            Belum ada proyek. Buat proyek pertama Anda.
        </div>
        <?php endif; ?>
    </div>

    <?php if($projects->hasPages()): ?>
    <div class="mt-4"><?php echo e($projects->links()); ?></div>
    <?php endif; ?>

    
    <div id="modal-add-project" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Proyek Baru</h3>
                <button onclick="document.getElementById('modal-add-project').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('projects.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Proyek *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Klien</label>
                        <select name="customer_id" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Tanpa klien --</option>
                            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe Proyek</label>
                        <input type="text" name="type" placeholder="misal: Website, Konstruksi..." class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deadline</label>
                        <input type="date" name="end_date" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Anggaran (Rp)</label>
                        <input type="number" name="budget" min="0" step="100000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi</label>
                        <textarea name="description" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-project').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat Proyek</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/projects/index.blade.php ENDPATH**/ ?>