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
     <?php $__env->slot('header', null, []); ?> Purchase Order <?php $__env->endSlot(); ?>

    
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nomor PO / supplier..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = ['draft' => 'Draft', 'sent' => 'Terkirim', 'partial' => 'Sebagian', 'received' => 'Diterima', 'cancelled' => 'Dibatalkan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($v); ?>" <?php if(request('status') === $v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <div class="flex gap-2">
            <a href="<?php echo e(route('suppliers.index')); ?>"
                class="px-3 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Supplier</a>
            <button onclick="document.getElementById('modal-create-po').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Buat PO</button>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor PO</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Supplier</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Gudang</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs font-medium">
                                <a href="<?php echo e(route('purchasing.orders.show', $po)); ?>"
                                    class="text-blue-600 hover:text-blue-800 hover:underline"><?php echo e($po->number); ?></a>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-700"><?php echo e($po->supplier->name ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3 hidden md:table-cell text-gray-500"><?php echo e($po->warehouse->name ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp
                                <?php echo e(number_format($po->total, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php
                                    $colors = ['draft' => 'gray', 'sent' => 'blue', 'partial' => 'yellow', 'received' => 'green', 'cancelled' => 'red'];
                                    $c = $colors[$po->status] ?? 'gray';
                                ?>
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($c); ?>-100 text-<?php echo e($c); ?>-700 $c }}-500/20 $c }}-400">
                                    <?php echo e(ucfirst($po->status)); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500">
                                <?php echo e($po->date->format('d M Y')); ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php if($po->status !== 'received' && $po->status !== 'cancelled'): ?>
                                    <form method="POST" action="<?php echo e(route('purchasing.orders.status', $po)); ?>"
                                        class="inline">
                                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                        <select name="status" onchange="this.form.submit()"
                                            class="text-xs px-2 py-1 rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                                            <?php $__currentLoopData = ['draft' => 'Draft', 'sent' => 'Terkirim', 'partial' => 'Sebagian', 'received' => 'Diterima', 'cancelled' => 'Batal']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($v); ?>" <?php if($po->status === $v): echo 'selected'; endif; ?>>
                                                    <?php echo e($l); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </form>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">—</span>
                                <?php endif; ?>
                                <?php if(in_array($po->status, ['draft', 'cancelled'])): ?>
                                    <form method="POST" action="<?php echo e(route('purchasing.orders.destroy', $po)); ?>"
                                        class="inline ml-1"
                                        onsubmit="return confirm('Hapus PO <?php echo e($po->number); ?>? Tindakan ini tidak bisa dibatalkan.')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50"
                                            title="Hapus PO">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada purchase order.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($orders->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-100"><?php echo e($orders->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-create-po" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                <h3 class="font-semibold text-gray-900">Buat Purchase Order</h3>
                <button onclick="document.getElementById('modal-create-po').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('purchasing.orders.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Supplier *</label>
                        <select name="supplier_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Supplier --</option>
                            <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Gudang Tujuan *</label>
                        <select name="warehouse_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Gudang --</option>
                            <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal PO *</label>
                        <input type="date" name="date" value="<?php echo e(today()->format('Y-m-d')); ?>" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Estimasi Terima</label>
                        <input type="date" name="expected_date"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pembayaran</label>
                        <select name="payment_type"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="credit">Tempo/Kredit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                        <input type="text" name="notes"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-medium text-gray-600">Item Produk *</label>
                        <button type="button" onclick="addPoItem()" class="text-xs text-blue-600 hover:underline">+
                            Tambah Item</button>
                    </div>
                    <div id="po-items" class="space-y-2">
                        <div class="po-item grid grid-cols-12 gap-2 items-center">
                            <div class="col-span-5">
                                <select name="items[0][product_id]" required
                                    class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Produk --</option>
                                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($p->id); ?>" data-price="<?php echo e($p->price_buy); ?>">
                                            <?php echo e($p->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-span-3">
                                <input type="number" name="items[0][quantity]" placeholder="Qty" min="1"
                                    required
                                    class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="col-span-3">
                                <input type="number" name="items[0][price]" placeholder="Harga" min="0"
                                    required
                                    class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="col-span-1 text-center">
                                <button type="button" onclick="this.closest('.po-item').remove()"
                                    class="text-red-500 hover:text-red-700 text-lg leading-none">×</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button"
                        onclick="document.getElementById('modal-create-po').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat PO</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            let poItemCount = 1;
            const productOptions =
                `<?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id); ?>" data-price="<?php echo e($p->price_buy); ?>"><?php echo e(addslashes($p->name)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>`;

            function addPoItem() {
                const i = poItemCount++;
                const div = document.createElement('div');
                div.className = 'po-item grid grid-cols-12 gap-2 items-center';
                div.innerHTML = `
            <div class="col-span-5">
                <select name="items[${i}][product_id]" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Produk --</option>${productOptions}
                </select>
            </div>
            <div class="col-span-3">
                <input type="number" name="items[${i}][quantity]" placeholder="Qty" min="1" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="col-span-3">
                <input type="number" name="items[${i}][price]" placeholder="Harga" min="0" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="col-span-1 text-center">
                <button type="button" onclick="this.closest('.po-item').remove()" class="text-red-500 hover:text-red-700 text-lg leading-none">×</button>
            </div>`;
                document.getElementById('po-items').appendChild(div);
            }
        </script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/purchasing/orders.blade.php ENDPATH**/ ?>