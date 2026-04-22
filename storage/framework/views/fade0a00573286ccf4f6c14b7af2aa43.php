

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('delivery-orders.index')); ?>" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Buat Surat Jalan</h1>
    </div>

    <?php if($errors->any()): ?>
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 text-sm text-red-700 dark:text-red-400">
        <ul class="list-disc list-inside space-y-1"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('delivery-orders.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Sales Order <span class="text-red-500">*</span></label>
                    <select name="sales_order_id" id="soSelect" required onchange="loadSoItems()"
                            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">-- Pilih Sales Order --</option>
                        <?php $__currentLoopData = $salesOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $so): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($so->id); ?>" <?php echo e(old('sales_order_id', $selectedSo?->id) == $so->id ? 'selected' : ''); ?>>
                            <?php echo e($so->number); ?> — <?php echo e($so->customer->name ?? ''); ?> (<?php echo e($so->date->format('d/m/Y')); ?>)
                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Gudang <span class="text-red-500">*</span></label>
                    <select name="warehouse_id" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($w->id); ?>" <?php echo e(old('warehouse_id') == $w->id ? 'selected' : ''); ?>><?php echo e($w->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Pengiriman <span class="text-red-500">*</span></label>
                    <input type="date" name="delivery_date" value="<?php echo e(old('delivery_date', today()->toDateString())); ?>" required
                           class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kurir / Ekspedisi</label>
                    <input type="text" name="courier" value="<?php echo e(old('courier')); ?>" placeholder="JNE, J&T, Gojek, dll"
                           class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Alamat Pengiriman</label>
                    <textarea name="shipping_address" rows="2"
                              class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none resize-none"
                              placeholder="Alamat tujuan pengiriman"><?php echo e(old('shipping_address', $selectedSo?->shipping_address)); ?></textarea>
                </div>
            </div>

            
            <div>
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Item yang Dikirim</h3>
                <div id="itemsContainer">
                    <?php if($selectedSo): ?>
                        <?php
                            $deliveredQty = [];
                            foreach ($selectedSo->deliveryOrders->whereNotIn('status', ['cancelled']) as $existingDo) {
                                foreach ($existingDo->items as $doi) {
                                    $deliveredQty[$doi->sales_order_item_id] = ($deliveredQty[$doi->sales_order_item_id] ?? 0) + $doi->quantity_delivered;
                                }
                            }
                        ?>
                        <?php $__currentLoopData = $selectedSo->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $remaining = $item->quantity - ($deliveredQty[$item->id] ?? 0); ?>
                            <?php if($remaining > 0): ?>
                            <div class="grid grid-cols-12 gap-2 items-center mb-2 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                                <input type="hidden" name="items[<?php echo e($i); ?>][sales_order_item_id]" value="<?php echo e($item->id); ?>">
                                <input type="hidden" name="items[<?php echo e($i); ?>][product_id]" value="<?php echo e($item->product_id); ?>">
                                <div class="col-span-6 text-sm text-slate-800 dark:text-white font-medium"><?php echo e($item->product->name); ?></div>
                                <div class="col-span-2 text-xs text-slate-500 text-center">
                                    Dipesan: <?php echo e($item->quantity); ?><br>
                                    Sisa: <?php echo e($remaining); ?>

                                </div>
                                <div class="col-span-4">
                                    <input type="number" name="items[<?php echo e($i); ?>][quantity_delivered]"
                                           min="0.001" max="<?php echo e($remaining); ?>" step="0.001"
                                           value="<?php echo e($remaining); ?>" required
                                           class="w-full px-2 py-1.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none"
                                           placeholder="Qty kirim">
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <p class="text-sm text-slate-400 dark:text-slate-500 text-center py-6">Pilih Sales Order untuk melihat item</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="<?php echo e(route('delivery-orders.index')); ?>"
                   class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white border border-slate-300 dark:border-slate-600 rounded-lg">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                    Buat Surat Jalan
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function loadSoItems() {
    const soId = document.getElementById('soSelect').value;
    if (!soId) return;
    window.location.href = `<?php echo e(route('delivery-orders.create')); ?>?sales_order_id=${soId}`;
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\delivery-orders\create.blade.php ENDPATH**/ ?>