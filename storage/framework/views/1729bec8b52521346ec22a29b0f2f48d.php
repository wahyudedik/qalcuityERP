

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('purchase-returns.index')); ?>" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Buat Retur Pembelian</h1>
    </div>

    <?php if($errors->any()): ?>
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 text-sm text-red-700 dark:text-red-400">
        <ul class="list-disc list-inside space-y-1"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('purchase-returns.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Purchase Order <span class="text-red-500">*</span></label>
                    <select name="purchase_order_id" id="poSelect" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">-- Pilih PO --</option>
                        <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($po->id); ?>" data-supplier="<?php echo e($po->supplier_id); ?>" <?php echo e(old('purchase_order_id') == $po->id ? 'selected' : ''); ?>>
                            <?php echo e($po->number); ?> — <?php echo e($po->supplier->name ?? ''); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Supplier <span class="text-red-500">*</span></label>
                    <select name="supplier_id" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">-- Pilih Supplier --</option>
                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s->id); ?>" <?php echo e(old('supplier_id') == $s->id ? 'selected' : ''); ?>><?php echo e($s->name); ?></option>
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
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Retur <span class="text-red-500">*</span></label>
                    <input type="date" name="return_date" value="<?php echo e(old('return_date', today()->toDateString())); ?>" required
                           class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Metode Refund <span class="text-red-500">*</span></label>
                    <select name="refund_method" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="debit_note" <?php echo e(old('refund_method') === 'debit_note' ? 'selected' : ''); ?>>Debit Note</option>
                        <option value="cash" <?php echo e(old('refund_method') === 'cash' ? 'selected' : ''); ?>>Tunai</option>
                        <option value="bank_transfer" <?php echo e(old('refund_method') === 'bank_transfer' ? 'selected' : ''); ?>>Transfer Bank</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Alasan Retur <span class="text-red-500">*</span></label>
                    <textarea name="reason" rows="2" required
                              class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none resize-none"
                              placeholder="Jelaskan alasan retur..."><?php echo e(old('reason')); ?></textarea>
                </div>
            </div>

            
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Item Retur</h3>
                    <button type="button" id="addItemBtn"
                            class="text-xs px-3 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg">
                        + Tambah Item
                    </button>
                </div>
                <div id="itemsContainer" class="space-y-2">
                    <div class="item-row grid grid-cols-12 gap-2 items-end">
                        <div class="col-span-5">
                            <label class="block text-xs text-slate-500 mb-1">Produk</label>
                            <select name="items[0][product_id]" required
                                    class="w-full px-2 py-1.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">-- Pilih Produk --</option>
                                <?php $__currentLoopData = \App\Models\Product::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>" data-price="<?php echo e($p->price_buy ?? $p->price_sell); ?>"><?php echo e($p->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-slate-500 mb-1">Qty</label>
                            <input type="number" name="items[0][quantity]" min="0.001" step="0.001" required
                                   class="w-full px-2 py-1.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div class="col-span-3">
                            <label class="block text-xs text-slate-500 mb-1">Harga Beli</label>
                            <input type="number" name="items[0][price]" min="0" step="1" required
                                   class="w-full px-2 py-1.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div class="col-span-2 flex justify-end">
                            <button type="button" class="remove-item text-red-400 hover:text-red-600 p-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="<?php echo e(route('purchase-returns.index')); ?>"
                   class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white border border-slate-300 dark:border-slate-600 rounded-lg">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                    Simpan Retur
                </button>
            </div>
        </div>
    </form>
</div>

<script>
let rowIdx = 1;
document.getElementById('addItemBtn').addEventListener('click', () => {
    const container = document.getElementById('itemsContainer');
    const first = container.querySelector('.item-row');
    const clone = first.cloneNode(true);
    clone.querySelectorAll('input').forEach(i => i.value = '');
    clone.querySelectorAll('select').forEach(s => { s.name = s.name.replace(/\[\d+\]/, `[${rowIdx}]`); s.selectedIndex = 0; });
    clone.querySelectorAll('input').forEach(i => { i.name = i.name.replace(/\[\d+\]/, `[${rowIdx}]`); });
    container.appendChild(clone);
    rowIdx++;
    bindRemove();
});

function bindRemove() {
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.onclick = () => { if (document.querySelectorAll('.item-row').length > 1) btn.closest('.item-row').remove(); };
    });
}
bindRemove();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\purchase-returns\create.blade.php ENDPATH**/ ?>