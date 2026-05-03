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
     <?php $__env->slot('header', null, []); ?> Detail Tagihan <?php $__env->endSlot(); ?>

    <?php if(!isset($invoice)): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6">
            <p class="text-amber-800">Invoice tidak ditemukan</p>
        </div>
    <?php else: ?>
        
        <div
            class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
            <div
                class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-purple-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900"><?php echo e($invoice->invoice_number ?? '-'); ?>

                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Tanggal:
                            <?php echo e($invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d F Y') : '-'); ?>

                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <?php if($invoice->status === 'paid'): ?>
                            <span class="px-4 py-2 text-sm font-bold bg-green-500 text-white rounded-xl">LUNAS</span>
                        <?php elseif($invoice->status === 'partial'): ?>
                            <span class="px-4 py-2 text-sm font-bold bg-amber-500 text-white rounded-xl">SEBAGIAN</span>
                        <?php elseif($invoice->status === 'overdue'): ?>
                            <span class="px-4 py-2 text-sm font-bold bg-red-500 text-white rounded-xl">OVERDUE</span>
                        <?php else: ?>
                            <span class="px-4 py-2 text-sm font-bold bg-gray-500 text-white rounded-xl">BELUM
                                BAYAR</span>
                        <?php endif; ?>
                        <a href="<?php echo e(route('healthcare.billing.invoices')); ?>"
                            class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50">
                            Kembali
                        </a>
                    </div>
                </div>
            </div>

            
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Informasi Pasien
                    </h4>
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs text-gray-500">Nama Pasien</p>
                            <p class="text-sm font-semibold text-gray-900">
                                <?php echo e($invoice->patient ? $invoice->patient->full_name : '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">No. Rekam Medis</p>
                            <p class="text-sm text-gray-900">
                                <?php echo e($invoice->patient ? $invoice->patient->medical_record_number : '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">No. Telepon</p>
                            <p class="text-sm text-gray-900">
                                <?php echo e($invoice->patient?->phone_primary ?? '-'); ?></p>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Informasi Tagihan
                    </h4>
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs text-gray-500">Layanan</p>
                            <p class="text-sm text-gray-900"><?php echo e($invoice->service_type ?? '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Dokter</p>
                            <p class="text-sm text-gray-900">
                                <?php echo e($invoice->doctor ? $invoice->doctor->name : '-'); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Asuransi</p>
                            <p class="text-sm text-gray-900">
                                <?php echo e($invoice->insurance_provider ?? 'Tidak Ada'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div
            class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Detail Layanan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Layanan</th>
                            <th class="px-4 py-3 text-center hidden sm:table-cell">Qty</th>
                            <th class="px-4 py-3 text-right hidden md:table-cell">Harga Satuan</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $invoice->items ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-900"><?php echo e($item['description'] ?? '-'); ?>

                                </td>
                                <td class="px-4 py-3 text-center hidden sm:table-cell"><?php echo e($item['quantity'] ?? 1); ?>

                                </td>
                                <td class="px-4 py-3 text-right hidden md:table-cell">Rp
                                    <?php echo e(number_format($item['unit_price'] ?? 0, 0, ',', '.')); ?></td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp
                                    <?php echo e(number_format($item['total'] ?? 0, 0, ',', '.')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                    <p>Tidak ada item</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-900">
                                Subtotal</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp
                                <?php echo e(number_format($invoice->subtotal ?? 0, 0, ',', '.')); ?></td>
                        </tr>
                        <?php if($invoice->discount > 0): ?>
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right text-gray-600">Diskon
                                </td>
                                <td class="px-4 py-3 text-right text-red-600">- Rp
                                    <?php echo e(number_format($invoice->discount, 0, ',', '.')); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if($invoice->tax > 0): ?>
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-right text-gray-600">Pajak
                                    (<?php echo e($invoice->tax_percentage ?? 0); ?>%)</td>
                                <td class="px-4 py-3 text-right text-gray-600">Rp
                                    <?php echo e(number_format($invoice->tax, 0, ',', '.')); ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="border-t-2 border-gray-300">
                            <td colspan="3"
                                class="px-4 py-4 text-right text-lg font-bold text-gray-900">TOTAL</td>
                            <td class="px-4 py-4 text-right text-lg font-bold text-blue-600">Rp
                                <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right text-gray-600">Dibayar
                            </td>
                            <td class="px-4 py-3 text-right text-green-600">Rp
                                <?php echo e(number_format($invoice->paid_amount ?? 0, 0, ',', '.')); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right text-gray-600">Sisa</td>
                            <td class="px-4 py-3 text-right text-red-600 font-bold">Rp
                                <?php echo e(number_format($invoice->total_amount - ($invoice->paid_amount ?? 0), 0, ',', '.')); ?>

                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        
        <?php if($invoice->payments && count($invoice->payments) > 0): ?>
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Riwayat Pembayaran</h3>
                </div>
                <div class="p-6 space-y-3">
                    <?php $__currentLoopData = $invoice->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div
                            class="flex items-center justify-between p-4 bg-green-50 rounded-xl border border-green-200">
                            <div>
                                <p class="font-semibold text-gray-900">
                                    <?php echo e($payment['method'] ?? 'Cash'); ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($payment['date'] ? \Carbon\Carbon::parse($payment['date'])->format('d M Y H:i') : '-'); ?>

                                </p>
                            </div>
                            <p class="text-lg font-bold text-green-600">Rp
                                <?php echo e(number_format($payment['amount'], 0, ',', '.')); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="flex items-center justify-end gap-3">
            <?php if($invoice->status !== 'paid'): ?>
                <a href="<?php echo e(route('healthcare.billing.invoices.payment', $invoice)); ?>"
                    class="px-6 py-2.5 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium">
                    Proses Pembayaran
                </a>
            <?php endif; ?>
            <button onclick="window.print()"
                class="px-6 py-2.5 text-sm border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50">
                Print Invoice
            </button>
        </div>
    <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\billing\invoices\show.blade.php ENDPATH**/ ?>