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
     <?php $__env->slot('header', null, []); ?> Invoice — <?php echo e($invoice->number ?? '#' . $invoice->id); ?> <?php $__env->endSlot(); ?>

    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="<?php echo e(route('customer-portal.invoices.index')); ?>"
            class="hover:text-blue-600">Invoice</a>
        <span>/</span>
        <span class="text-gray-900"><?php echo e($invoice->number ?? '#' . $invoice->id); ?></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900">Detail Invoice</h3>
                    <a href="<?php echo e(route('customer-portal.invoices.download', $invoice)); ?>"
                        class="px-3 py-1.5 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700">⬇ Download
                        PDF</a>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-500">No. Invoice</p>
                        <p class="font-medium text-gray-900"><?php echo e($invoice->number ?? '#' . $invoice->id); ?>

                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Tanggal</p>
                        <p class="font-medium text-gray-900">
                            <?php echo e($invoice->created_at?->format('d/m/Y')); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Total</p>
                        <p class="font-medium text-gray-900">Rp
                            <?php echo e(number_format($invoice->total_amount ?? 0, 0, ',', '.')); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Sisa Bayar</p>
                        <p class="font-medium text-red-600">Rp
                            <?php echo e(number_format($invoice->remaining_amount ?? 0, 0, ',', '.')); ?></p>
                    </div>
                </div>
            </div>

            
            <?php if($invoice->salesOrder?->items): ?>
                <div
                    class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Item</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="bg-gray-50 text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left">Produk</th>
                                    <th class="px-4 py-3 text-right">Qty</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php $__currentLoopData = $invoice->salesOrder->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-4 py-3 text-gray-900">
                                            <?php echo e($item->product?->name ?? '-'); ?></td>
                                        <td class="px-4 py-3 text-right text-gray-700">
                                            <?php echo e($item->quantity); ?></td>
                                        <td class="px-4 py-3 text-right text-gray-900">Rp
                                            <?php echo e(number_format(($item->quantity ?? 0) * ($item->price ?? 0), 0, ',', '.')); ?>

                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            
            <?php if($invoice->payments && $invoice->payments->count() > 0): ?>
                <div
                    class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Riwayat Pembayaran</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $invoice->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between px-4 py-3">
                                <div>
                                    <p class="text-sm text-gray-900">Rp
                                        <?php echo e(number_format($payment->amount ?? 0, 0, ',', '.')); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo e($payment->payment_date?->format('d/m/Y') ?? '-'); ?> —
                                        <?php echo e(ucfirst($payment->payment_method ?? '-')); ?></p>
                                </div>
                                <?php $pc = ($payment->status ?? 'pending') === 'confirmed' ? 'green' : 'amber'; ?>
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($pc); ?>-100 text-<?php echo e($pc); ?>-700 $pc }}-500/20 $pc }}-400"><?php echo e(ucfirst($payment->status ?? 'pending')); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="space-y-6">
            <?php if(!in_array($invoice->status, ['paid', 'voided', 'cancelled']) && ($invoice->remaining_amount ?? 0) > 0): ?>
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Bayar Invoice</h3>
                    <form method="POST" action="<?php echo e(route('customer-portal.invoices.pay', $invoice)); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah
                                    Bayar *</label>
                                <input type="number" name="amount" required min="1"
                                    max="<?php echo e($invoice->remaining_amount); ?>" value="<?php echo e($invoice->remaining_amount); ?>"
                                    step="1"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Metode
                                    Pembayaran *</label>
                                <select name="payment_method" required
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="bank_transfer">Transfer Bank</option>
                                    <option value="credit_card">Kartu Kredit</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-600 mb-1">Referensi
                                    Pembayaran</label>
                                <input type="text" name="payment_reference"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="No. transfer / referensi">
                            </div>
                            <button type="submit"
                                class="w-full px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">Bayar
                                Sekarang</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\customer-portal\invoices\show.blade.php ENDPATH**/ ?>