

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('bulk-payments.index')); ?>" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Buat Bulk Payment</h1>
    </div>

    <?php if($errors->any()): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('bulk-payments.store')); ?>" id="bpForm">
        <?php echo csrf_field(); ?>
        <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Customer <span class="text-red-500">*</span></label>
                    <select name="customer_id" id="customerSelect" required onchange="loadInvoices()"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">-- Pilih Customer --</option>
                        <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>" <?php echo e(old('customer_id', $selectedCustomer?->id) == $c->id ? 'selected' : ''); ?>><?php echo e($c->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Pembayaran <span class="text-red-500">*</span></label>
                    <input type="date" name="payment_date" value="<?php echo e(old('payment_date', today()->toDateString())); ?>" required
                           class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
                    <select name="payment_method" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="transfer">Transfer Bank</option>
                        <option value="cash">Tunai</option>
                        <option value="qris">QRIS</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Total Pembayaran Diterima <span class="text-red-500">*</span></label>
                    <input type="number" name="total_amount" id="totalAmount" min="1" step="1" required
                           value="<?php echo e(old('total_amount')); ?>" onchange="calcOverpayment()"
                           class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none"
                           placeholder="Jumlah total yang diterima dari customer">
                </div>
            </div>

            
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Invoice yang Dibayar</h3>
                <div id="invoiceList" class="space-y-2">
                    <?php if($pendingInvoices->isNotEmpty()): ?>
                        <?php $__currentLoopData = $pendingInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg">
                            <input type="checkbox" name="invoices[<?php echo e($loop->index); ?>][invoice_id]" value="<?php echo e($inv->id); ?>"
                                   class="invoice-check w-4 h-4 text-blue-600 rounded" onchange="calcOverpayment()">
                            <input type="hidden" name="invoices[<?php echo e($loop->index); ?>][invoice_id]" value="<?php echo e($inv->id); ?>" disabled class="inv-id-hidden">
                            <div class="flex-1">
                                <p class="text-sm font-mono font-medium text-slate-800"><?php echo e($inv->number); ?></p>
                                <p class="text-xs text-slate-500">Jatuh tempo: <?php echo e($inv->due_date->format('d/m/Y')); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-500">Sisa tagihan</p>
                                <p class="text-sm font-medium text-slate-800">Rp <?php echo e(number_format($inv->remaining_amount, 0, ',', '.')); ?></p>
                            </div>
                            <div class="w-36">
                                <input type="number" name="invoices[<?php echo e($loop->index); ?>][amount]"
                                       class="inv-amount w-full px-2 py-1.5 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none"
                                       min="0" step="1" value="<?php echo e($inv->remaining_amount); ?>" max="<?php echo e($inv->remaining_amount); ?>"
                                       placeholder="Jumlah" onchange="calcOverpayment()">
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <p class="text-sm text-slate-400 text-center py-6">
                            <?php echo e($selectedCustomer ? 'Tidak ada invoice outstanding untuk customer ini.' : 'Pilih customer untuk melihat invoice outstanding.'); ?>

                        </p>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-slate-50 rounded-lg p-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600">Total Diterapkan ke Invoice</span>
                    <span id="totalApplied" class="font-medium text-slate-800">Rp 0</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-600">Total Pembayaran</span>
                    <span id="totalPaymentDisplay" class="font-medium text-slate-800">Rp 0</span>
                </div>
                <div class="flex justify-between text-sm border-t border-slate-200 pt-2">
                    <span class="text-slate-600">Overpayment → Saldo Customer</span>
                    <span id="overpaymentDisplay" class="font-medium text-amber-600">Rp 0</span>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="<?php echo e(route('bulk-payments.index')); ?>"
                   class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 border border-slate-300 rounded-lg">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                    Terapkan Pembayaran
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function calcOverpayment() {
    const total = parseFloat(document.getElementById('totalAmount').value) || 0;
    let applied = 0;
    document.querySelectorAll('.inv-amount').forEach(inp => {
        applied += parseFloat(inp.value) || 0;
    });
    const over = Math.max(0, total - applied);
    document.getElementById('totalApplied').textContent = 'Rp ' + applied.toLocaleString('id-ID');
    document.getElementById('totalPaymentDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('overpaymentDisplay').textContent = 'Rp ' + over.toLocaleString('id-ID');
}

function loadInvoices() {
    const customerId = document.getElementById('customerSelect').value;
    if (!customerId) return;
    window.location.href = `<?php echo e(route('bulk-payments.create')); ?>?customer_id=${customerId}`;
}

// Init calc
calcOverpayment();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\bulk-payments\create.blade.php ENDPATH**/ ?>