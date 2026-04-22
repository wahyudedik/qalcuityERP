<?php
    $isGroup = $item->type === 'group';
    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth);
    $realPct = $item->realizationPercent();
    $overBudget = $item->actual_cost > $item->subtotal && $item->subtotal > 0;
?>
<tr class="<?php echo e($isGroup ? 'bg-gray-50/50 dark:bg-white/[0.02]' : ''); ?> hover:bg-gray-50 dark:hover:bg-white/5 transition">
    
    <td class="px-4 py-2.5 text-xs text-gray-500 dark:text-slate-400 font-mono"><?php echo e($item->code); ?></td>

    
    <td class="px-4 py-2.5">
        <span
            class="<?php echo e($isGroup ? 'font-semibold text-gray-900 dark:text-white' : 'text-gray-700 dark:text-slate-300'); ?> text-sm">
            <?php echo $indent; ?><?php echo e($item->name); ?>

        </span>
        <?php if($item->category): ?>
            <span
                class="ml-1.5 text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-slate-400 capitalize"><?php echo e($item->category); ?></span>
        <?php endif; ?>
    </td>

    
    <td class="px-4 py-2.5 text-right text-sm text-gray-600 dark:text-slate-300 font-mono">
        <?php if(!$isGroup && $item->volume > 0): ?>
            <?php echo e(number_format($item->volume, $item->volume == (int) $item->volume ? 0 : 2)); ?>

            <?php if($item->actual_volume > 0): ?>
                <div class="text-[10px] text-blue-500">
                    <?php echo e(number_format($item->actual_volume, $item->actual_volume == (int) $item->actual_volume ? 0 : 2)); ?>

                    real</div>
            <?php endif; ?>
        <?php endif; ?>
    </td>

    
    <td class="px-4 py-2.5 text-xs text-gray-500 dark:text-slate-400"><?php echo e($item->unit); ?></td>

    
    <td class="px-4 py-2.5 text-right text-sm text-gray-600 dark:text-slate-300 font-mono">
        <?php if(!$isGroup && $item->unit_price > 0): ?>
            <?php echo e(number_format($item->unit_price, 0, ',', '.')); ?>

        <?php endif; ?>
    </td>

    
    <td class="px-4 py-2.5 text-right text-xs text-gray-500 dark:text-slate-400 font-mono">
        <?php if(!$isGroup && $item->coefficient != 1): ?>
            <?php echo e($item->coefficient); ?>

        <?php endif; ?>
    </td>

    
    <td
        class="px-4 py-2.5 text-right text-sm font-mono <?php echo e($isGroup ? 'font-semibold text-gray-900 dark:text-white' : 'text-gray-700 dark:text-slate-300'); ?>">
        Rp <?php echo e(number_format($item->subtotal, 0, ',', '.')); ?>

    </td>

    
    <td
        class="px-4 py-2.5 text-right text-sm font-mono <?php echo e($overBudget ? 'text-red-500 font-semibold' : 'text-gray-600 dark:text-slate-400'); ?>">
        <?php if($item->actual_cost > 0): ?>
            Rp <?php echo e(number_format($item->actual_cost, 0, ',', '.')); ?>

            <div class="text-[10px] <?php echo e($overBudget ? 'text-red-400' : 'text-gray-400'); ?>"><?php echo e($realPct); ?>%</div>
        <?php else: ?>
            <span class="text-gray-300 dark:text-slate-600">—</span>
        <?php endif; ?>
    </td>

    
    <td class="px-4 py-2.5">
        <div class="flex items-center gap-1">
            <?php if(!$isGroup): ?>
                <button
                    onclick="openActualModal(<?php echo e($item->id); ?>, '<?php echo e(addslashes($item->name)); ?>', <?php echo e($item->actual_cost); ?>, <?php echo e($item->actual_volume); ?>)"
                    class="text-[10px] text-blue-500 hover:text-blue-600 px-1.5 py-0.5 rounded hover:bg-blue-50 dark:hover:bg-blue-500/10"
                    title="Catat Realisasi">📝</button>
            <?php endif; ?>
            <form method="POST" action="<?php echo e(route('projects.rab.destroy', $item)); ?>"
                onsubmit="return confirm('Hapus item ini?')" class="inline">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit"
                    class="text-[10px] text-red-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 dark:hover:bg-red-500/10"
                    title="Hapus">Hapus</button>
            </form>
        </div>
    </td>
</tr>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\projects\_rab_row.blade.php ENDPATH**/ ?>