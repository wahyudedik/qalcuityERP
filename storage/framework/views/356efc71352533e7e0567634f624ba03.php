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
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="<?php echo e(route('purchasing.orders')); ?>" class="hover:text-blue-600">Purchase Order</a>
            <span>/</span>
            <span class="text-gray-900 font-medium"><?php echo e($order->number); ?></span>
        </div>
     <?php $__env->endSlot(); ?>

    
    <?php if(session('success')): ?>
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>
    <?php if(session('warning')): ?>
        <div class="mb-4 px-4 py-3 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-xl text-sm">
            <?php echo e(session('warning')); ?>

        </div>
    <?php endif; ?>

    <div class="space-y-4">

        
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 font-mono"><?php echo e($order->number); ?></h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Dibuat oleh <?php echo e($order->user->name ?? '-'); ?> &middot; <?php echo e($order->date->format('d M Y')); ?>

                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    
                    <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo e($order->postingStatusColor()); ?>">
                        <?php echo e($order->postingStatusLabel()); ?>

                    </span>
                    
                    <?php
                        $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-600',
                            'sent' => 'bg-blue-100 text-blue-700',
                            'partial' => 'bg-yellow-100 text-yellow-700',
                            'received' => 'bg-green-100 text-green-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                        ];
                        $statusLabels = [
                            'draft' => 'Draft',
                            'sent' => 'Terkirim',
                            'partial' => 'Sebagian Diterima',
                            'received' => 'Diterima',
                            'cancelled' => 'Dibatalkan',
                        ];
                    ?>
                    <span
                        class="px-3 py-1 rounded-full text-xs font-medium <?php echo e($statusColors[$order->status] ?? 'bg-gray-100 text-gray-600'); ?>">
                        <?php echo e($statusLabels[$order->status] ?? ucfirst($order->status)); ?>

                    </span>

                    
                    <?php if($order->isDraft() && !in_array($order->status, ['cancelled'])): ?>
                        <form method="POST" action="<?php echo e(route('purchasing.orders.post', $order)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                class="px-4 py-1.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                                Posting PO
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if(!in_array($order->status, ['received', 'cancelled'])): ?>
                        <button onclick="document.getElementById('modal-cancel').classList.remove('hidden')"
                            class="px-4 py-1.5 text-sm border border-red-200 text-red-600 rounded-xl hover:bg-red-50">
                            Batalkan
                        </button>
                    <?php endif; ?>

                    <a href="<?php echo e(route('purchasing.orders')); ?>"
                        class="px-4 py-1.5 text-sm border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50">
                        ← Kembali
                    </a>
                </div>
            </div>

            
            <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Supplier</p>
                    <p class="text-sm font-medium text-gray-900"><?php echo e($order->supplier->name ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Gudang</p>
                    <p class="text-sm font-medium text-gray-900"><?php echo e($order->warehouse->name ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Estimasi Terima</p>
                    <p class="text-sm font-medium text-gray-900">
                        <?php echo e($order->expected_date ? $order->expected_date->format('d M Y') : '-'); ?>

                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Pembayaran</p>
                    <p class="text-sm font-medium text-gray-900">
                        <?php echo e($order->payment_type === 'cash' ? 'Cash' : 'Tempo/Kredit'); ?>

                    </p>
                </div>
                <?php if($order->notes): ?>
                    <div class="col-span-2 sm:col-span-4">
                        <p class="text-xs text-gray-400 mb-0.5">Catatan</p>
                        <p class="text-sm text-gray-700"><?php echo e($order->notes); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            
            <?php if($order->isPosted() && $order->posted_at): ?>
                <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-400">
                    Diposting pada <?php echo e($order->posted_at->format('d M Y H:i')); ?>

                </div>
            <?php endif; ?>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-medium text-gray-900">Item Pesanan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Produk</th>
                            <th class="px-4 py-3 text-right">Qty Pesan</th>
                            <th class="px-4 py-3 text-right">Qty Diterima</th>
                            <th class="px-4 py-3 text-right">Harga Satuan</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-4 py-3 text-gray-900"><?php echo e($item->product->name ?? '-'); ?></td>
                                <td class="px-4 py-3 text-right text-gray-700">
                                    <?php echo e(number_format($item->quantity_ordered, 0, ',', '.')); ?></td>
                                <td class="px-4 py-3 text-right">
                                    <?php if($item->quantity_received > 0): ?>
                                        <span
                                            class="text-green-600 font-medium"><?php echo e(number_format($item->quantity_received, 0, ',', '.')); ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700">Rp
                                    <?php echo e(number_format($item->price, 0, ',', '.')); ?></td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">Rp
                                    <?php echo e(number_format($item->total, 0, ',', '.')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Total
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">
                                Rp <?php echo e(number_format($order->total, 0, ',', '.')); ?>

                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        
        <?php if($order->goodsReceipts->isNotEmpty()): ?>
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-medium text-gray-900">Riwayat Penerimaan Barang</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Nomor GR</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Surat Jalan</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__currentLoopData = $order->goodsReceipts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900">
                                        <?php echo e($gr->number); ?></td>
                                    <td class="px-4 py-3 text-gray-700">
                                        <?php echo e(\Carbon\Carbon::parse($gr->receipt_date)->format('d M Y')); ?></td>
                                    <td class="px-4 py-3 text-gray-500"><?php echo e($gr->delivery_note ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">
                                            <?php echo e(ucfirst($gr->status)); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>

    
    <div id="modal-cancel" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Batalkan PO</h3>
                <button onclick="document.getElementById('modal-cancel').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('purchasing.orders.cancel', $order)); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <p class="text-sm text-gray-600">Masukkan alasan pembatalan PO <strong><?php echo e($order->number); ?></strong>.
                </p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Alasan Pembatalan *</label>
                    <textarea name="reason" required rows="3"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-500"
                        placeholder="Contoh: Supplier tidak tersedia, harga tidak sesuai..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-cancel').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">
                        Batalkan PO
                    </button>
                </div>
            </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/purchasing/order-detail.blade.php ENDPATH**/ ?>