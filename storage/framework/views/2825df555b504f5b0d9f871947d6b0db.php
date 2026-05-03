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
     <?php $__env->slot('header', null, []); ?> Onboarding Karyawan <?php $__env->endSlot(); ?>

    <div class="flex items-center justify-between mb-5">
        <form method="GET" class="flex gap-2">
            <select name="status" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua</option>
                <option value="in_progress" <?php if(request('status')==='in_progress'): echo 'selected'; endif; ?>>Berjalan</option>
                <option value="completed"   <?php if(request('status')==='completed'): echo 'selected'; endif; ?>>Selesai</option>
            </select>
        </form>
        <button onclick="document.getElementById('modal-start-onboarding').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Mulai Onboarding</button>
    </div>

    <div class="space-y-3">
        <?php $__empty_1 = true; $__currentLoopData = $onboardings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ob): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php $pct = $ob->progressPercent(); ?>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <p class="font-semibold text-gray-900"><?php echo e($ob->employee->name); ?></p>
                        <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($ob->status === 'completed' ? 'bg-green-500/20 text-green-400' : 'bg-blue-500/20 text-blue-400'); ?>">
                            <?php echo e($ob->status === 'completed' ? 'Selesai' : 'Berjalan'); ?>

                        </span>
                    </div>
                    <p class="text-xs text-gray-500">
                        <?php echo e($ob->employee->position ?? '-'); ?> · Mulai: <?php echo e($ob->start_date->format('d M Y')); ?>

                        <?php if($ob->completed_at): ?> · Selesai: <?php echo e($ob->completed_at->format('d M Y')); ?><?php endif; ?>
                    </p>
                </div>
                <div class="flex items-center gap-4 shrink-0">
                    <div class="text-right">
                        <p class="text-sm font-bold <?php echo e($pct >= 100 ? 'text-green-400' : 'text-blue-400'); ?>"><?php echo e($pct); ?>%</p>
                        <div class="w-32 h-2 bg-gray-200 rounded-full mt-1">
                            <div class="h-2 rounded-full <?php echo e($pct >= 100 ? 'bg-green-500' : 'bg-blue-500'); ?>" style="width:<?php echo e($pct); ?>%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <?php echo e($ob->tasks->where('is_done', true)->count()); ?>/<?php echo e($ob->tasks->count()); ?> tugas
                        </p>
                    </div>
                    <a href="<?php echo e(route('hrm.onboarding.detail', $ob)); ?>"
                       class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">Detail</a>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
            <p class="text-gray-400 text-sm">Belum ada onboarding aktif.</p>
        </div>
        <?php endif; ?>
    </div>
    <?php if($onboardings->hasPages()): ?>
    <div class="mt-4"><?php echo e($onboardings->links()); ?></div>
    <?php endif; ?>

    
    <div id="modal-start-onboarding" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Mulai Onboarding</h3>
                <button onclick="document.getElementById('modal-start-onboarding').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('hrm.onboarding.start')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan *</label>
                    <select name="employee_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">Pilih karyawan...</option>
                        <?php $__currentLoopData = \App\Models\Employee::where('tenant_id', auth()->user()->tenant_id)->where('status','active')->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($emp->id); ?>"><?php echo e($emp->name); ?> — <?php echo e($emp->position ?? '-'); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai *</label>
                    <input type="date" name="start_date" value="<?php echo e(today()->format('Y-m-d')); ?>" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-start-onboarding').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Mulai</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hrm\onboarding.blade.php ENDPATH**/ ?>