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
     <?php $__env->slot('header', null, []); ?> Onboarding — <?php echo e($onboarding->employee->name); ?> <?php $__env->endSlot(); ?>

    <?php
    $pct = $onboarding->progressPercent();
    $tasksByCategory = $onboarding->tasks->groupBy('category');
    ?>

    
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="text-lg font-bold text-gray-900"><?php echo e($onboarding->employee->name); ?></h2>
                    <span id="ob-status-badge" class="px-2 py-0.5 rounded-full text-xs <?php echo e($onboarding->status === 'completed' ? 'bg-green-500/20 text-green-400' : 'bg-blue-500/20 text-blue-400'); ?>">
                        <?php echo e($onboarding->status === 'completed' ? 'Selesai' : 'Berjalan'); ?>

                    </span>
                </div>
                <p class="text-sm text-gray-500">
                    <?php echo e($onboarding->employee->position ?? '-'); ?> · <?php echo e($onboarding->employee->department ?? '-'); ?>

                    · Mulai: <?php echo e($onboarding->start_date->format('d M Y')); ?>

                </p>
            </div>
            <div class="text-center shrink-0">
                <p id="ob-pct" class="text-3xl font-bold <?php echo e($pct >= 100 ? 'text-green-400' : 'text-blue-400'); ?>"><?php echo e($pct); ?>%</p>
                <div class="w-40 h-2.5 bg-gray-200 rounded-full mt-2">
                    <div id="ob-bar" class="h-2.5 rounded-full <?php echo e($pct >= 100 ? 'bg-green-500' : 'bg-blue-500'); ?> transition-all" style="width:<?php echo e($pct); ?>%"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">
                    <?php echo e($onboarding->tasks->where('is_done', true)->count()); ?>/<?php echo e($onboarding->tasks->count()); ?> tugas selesai
                </p>
            </div>
        </div>
    </div>

    <a href="<?php echo e(route('hrm.onboarding.index')); ?>" class="text-sm text-blue-500 hover:underline mb-4 inline-block">← Kembali</a>

    
    <div class="space-y-4 mt-4">
        <?php $__currentLoopData = $tasksByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $tasks): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <?php echo e($category ?? 'Umum'); ?>

                    <span class="ml-2 font-normal normal-case">
                        (<?php echo e($tasks->where('is_done', true)->count()); ?>/<?php echo e($tasks->count()); ?>)
                    </span>
                </p>
            </div>
            <div class="divide-y divide-gray-100">
                <?php $__currentLoopData = $tasks->sortBy('sort_order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div id="task-row-<?php echo e($task->id); ?>" class="flex items-start gap-3 px-4 py-3 <?php echo e($task->is_done ? 'opacity-60' : ''); ?>">
                    <button onclick="toggleTask(<?php echo e($task->id); ?>)"
                        class="mt-0.5 shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center transition
                               <?php echo e($task->is_done ? 'bg-green-500 border-green-500' : 'border-gray-300 hover:border-blue-500'); ?>">
                        <?php if($task->is_done): ?>
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        <?php endif; ?>
                    </button>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 <?php echo e($task->is_done ? 'line-through' : ''); ?>">
                            <?php echo e($task->task); ?>

                            <?php if($task->required): ?><span class="text-red-400 text-xs ml-1">*</span><?php endif; ?>
                        </p>
                        <div class="flex flex-wrap gap-2 mt-0.5">
                            <span class="text-xs text-gray-400">Hari ke-<?php echo e($task->due_day); ?></span>
                            <?php if($task->is_done && $task->done_at): ?>
                            <span class="text-xs text-green-500">✓ <?php echo e($task->done_at->format('d M Y')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    async function toggleTask(taskId) {
        const row = document.getElementById('task-row-' + taskId);
        const btn = row.querySelector('button');

        try {
            const res  = await fetch('<?php echo e(url("hrm/onboarding/tasks")); ?>/' + taskId + '/toggle', {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            });
            const data = await res.json();

            // Update row UI
            const isDone = data.is_done;
            row.classList.toggle('opacity-60', isDone);
            const taskText = row.querySelector('p.text-sm');
            taskText.classList.toggle('line-through', isDone);

            btn.className = 'mt-0.5 shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center transition '
                + (isDone ? 'bg-green-500 border-green-500' : 'border-gray-300 hover:border-blue-500');
            btn.innerHTML = isDone
                ? '<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>'
                : '';

            // Update progress bar
            const pct = data.progress;
            document.getElementById('ob-pct').textContent = pct + '%';
            document.getElementById('ob-bar').style.width = pct + '%';
            const isComplete = data.status === 'completed';
            document.getElementById('ob-pct').className = 'text-3xl font-bold ' + (isComplete ? 'text-green-400' : 'text-blue-400');
            document.getElementById('ob-bar').className = 'h-2.5 rounded-full transition-all ' + (isComplete ? 'bg-green-500' : 'bg-blue-500');
            const badge = document.getElementById('ob-status-badge');
            badge.textContent = isComplete ? 'Selesai' : 'Berjalan';
            badge.className = 'px-2 py-0.5 rounded-full text-xs ' + (isComplete ? 'bg-green-500/20 text-green-400' : 'bg-blue-500/20 text-blue-400');
        } catch(e) {
            console.error(e);
        }
    }
    </script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hrm\onboarding-detail.blade.php ENDPATH**/ ?>