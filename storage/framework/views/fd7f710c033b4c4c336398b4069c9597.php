
<?php
    $display = $data['display'] ?? '—';
    $title = $data['title'] ?? 'Custom Metric';
    $subtitle = $data['subtitle'] ?? null;
    $customId = $data['custom_id'] ?? null;
    $canEdit = in_array(auth()->user()->role, ['admin', 'manager', 'super_admin']);
?>

<div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 h-full flex flex-col">
    <div class="flex items-start justify-between mb-3">
        <p class="text-xs font-medium text-gray-500 dark:text-slate-400 leading-tight pr-2"><?php echo e($title); ?></p>
        <?php if($canEdit && $customId): ?>
            <button onclick="openWidgetBuilder(<?php echo e($customId); ?>)"
                class="shrink-0 p-1 rounded-lg text-gray-300 dark:text-slate-600 hover:text-blue-400 hover:bg-blue-500/10 transition"
                title="Edit widget">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </button>
        <?php endif; ?>
    </div>

    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-auto"><?php echo e($display); ?></p>

    <?php if($subtitle): ?>
        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1"><?php echo e($subtitle); ?></p>
    <?php else: ?>
        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Metrik kustom</p>
    <?php endif; ?>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\dashboard\widgets\custom-metric.blade.php ENDPATH**/ ?>