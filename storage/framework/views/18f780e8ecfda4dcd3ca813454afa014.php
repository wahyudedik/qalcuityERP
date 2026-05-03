<?php
    $count = $data['low_stock_count'] ?? 0;
    $bgColor = $count > 0 ? 'bg-red-500/20' : 'bg-green-500/20';
    $icColor = $count > 0 ? 'text-red-400' : 'text-green-400';
?>
<div class="bg-white rounded-2xl border border-gray-200 p-5 h-full">
    <div class="flex items-start justify-between mb-4">
        <p class="text-xs font-medium text-gray-500 leading-tight">Stok Menipis</p>
        <div class="w-9 h-9 rounded-xl <?php echo e($bgColor); ?> flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 <?php echo e($icColor); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
        </div>
    </div>
    <p class="text-2xl font-bold text-gray-900"><?php echo e($count); ?></p>
    <p class="text-xs text-gray-400 mt-1"><?php echo e($data['total_products'] ?? 0); ?> total produk</p>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\dashboard\widgets\kpi-low-stock.blade.php ENDPATH**/ ?>