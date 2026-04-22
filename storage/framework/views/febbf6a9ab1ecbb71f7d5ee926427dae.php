<div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 h-full">
    <div class="flex items-start justify-between mb-4">
        <p class="text-xs font-medium text-gray-500 dark:text-slate-400 leading-tight">Order Marketplace</p>
        <div class="w-9 h-9 rounded-xl bg-orange-500/20 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
        </div>
    </div>
    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e(number_format($data['this_month_orders'] ?? 0)); ?></p>
    <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">
        <?php echo e(($data['growth_percent'] ?? 0) >= 0 ? '▲' : '▼'); ?> <?php echo e(abs($data['growth_percent'] ?? 0)); ?>% vs bulan lalu
    </p>
    <?php if(($data['pending_orders'] ?? 0) > 0): ?>
        <p class="text-xs text-orange-400 dark:text-orange-300 mt-2 font-medium">
            <?php echo e(number_format($data['pending_orders'])); ?> order menunggu proses
        </p>
    <?php endif; ?>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\dashboard\widgets\ecommerce-orders.blade.php ENDPATH**/ ?>