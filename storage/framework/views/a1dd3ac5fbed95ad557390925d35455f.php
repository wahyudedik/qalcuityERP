<div class="bg-white rounded-2xl border border-gray-200 p-5 h-full">
    <div class="flex items-start justify-between mb-4">
        <p class="text-xs font-medium text-gray-500 leading-tight">Order Bulan Ini</p>
        <div class="w-9 h-9 rounded-xl bg-green-500/20 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
    </div>
    <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($data['this_month_orders'] ?? 0)); ?></p>
    <p class="text-xs text-gray-400 mt-1">
        <?php echo e(($data['growth_percent'] ?? 0) >= 0 ? '▲' : '▼'); ?> <?php echo e(abs($data['growth_percent'] ?? 0)); ?>% vs bulan lalu
    </p>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/dashboard/widgets/kpi-orders.blade.php ENDPATH**/ ?>