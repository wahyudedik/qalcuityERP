<div class="bg-white rounded-2xl border border-gray-200 p-5 h-full">
    <div class="flex items-start justify-between mb-4">
        <p class="text-xs font-medium text-gray-500 leading-tight">Omzet POS Hari Ini</p>
        <div class="w-9 h-9 rounded-xl bg-emerald-500/20 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
    </div>
    <p class="text-2xl font-bold text-gray-900">Rp <?php echo e(number_format($data['revenue'] ?? 0, 0, ',', '.')); ?></p>
    <p class="text-xs text-gray-400 mt-1"><?php echo e($data['count'] ?? 0); ?> transaksi · Avg Rp <?php echo e(number_format($data['avg_ticket'] ?? 0, 0, ',', '.')); ?></p>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\dashboard\widgets\pos-today.blade.php ENDPATH**/ ?>