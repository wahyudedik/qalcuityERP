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
     <?php $__env->slot('title', null, []); ?> RFQ — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> Request for Quotation (RFQ) <?php $__env->endSlot(); ?>
     <?php $__env->slot('pageHeader', null, []); ?> 
        <button onclick="document.getElementById('modal-add-rfq').classList.remove('hidden')"
            class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat RFQ
        </button>
     <?php $__env->endSlot(); ?>

    <div class="space-y-4">
        <?php $__empty_1 = true; $__currentLoopData = $rfqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rfq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div>
                        <p class="font-mono text-sm font-semibold text-gray-900"><?php echo e($rfq->number); ?></p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Dibuat: <?php echo e($rfq->issue_date->format('d M Y')); ?> &bull;
                            Deadline: <span class="<?php echo e($rfq->deadline->isPast() && $rfq->status === 'open' ? 'text-red-500' : ''); ?>"><?php echo e($rfq->deadline->format('d M Y')); ?></span>
                        </p>
                    </div>
                    <?php
                        $rfqBadge = match($rfq->status) {
                            'open'      => 'bg-green-100 text-green-700',
                            'converted' => 'bg-blue-100 text-blue-700',
                            default     => 'bg-gray-100 text-gray-500',
                        };
                    ?>
                    <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($rfqBadge); ?>"><?php echo e($rfq->statusLabel()); ?></span>
                </div>
                <div class="flex items-center gap-2 mt-3 sm:mt-0">
                    <span class="text-xs text-gray-500"><?php echo e($rfq->responses->count()); ?> penawaran</span>
                    <?php if($rfq->status === 'open'): ?>
                    <button onclick="openAddResponse(<?php echo e($rfq->id); ?>)"
                        class="px-3 py-1.5 text-xs bg-amber-500 text-white rounded-lg hover:bg-amber-600">+ Penawaran</button>
                    <?php endif; ?>
                    <?php if($rfq->status === 'open' && $rfq->selectedResponse()): ?>
                    <button onclick="openConvertRfq(<?php echo e($rfq->id); ?>, '<?php echo e($rfq->number); ?>')"
                        class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">→ PO</button>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="px-5 py-3 border-b border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Item</p>
                <div class="flex flex-wrap gap-2">
                    <?php $__currentLoopData = $rfq->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="px-2 py-1 bg-gray-100 rounded-lg text-xs text-gray-700">
                        <?php echo e($item->description); ?> &times; <?php echo e(number_format($item->quantity, 0)); ?> <?php echo e($item->unit); ?>

                    </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            
            <?php if($rfq->responses->count()): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left">Supplier</th>
                            <th class="px-4 py-2 text-right">Total Harga</th>
                            <th class="px-4 py-2 text-center hidden md:table-cell">Pengiriman</th>
                            <th class="px-4 py-2 text-left hidden md:table-cell">Syarat Bayar</th>
                            <th class="px-4 py-2 text-center">Pilih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $rfq->responses->sortBy('total_price'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="<?php echo e($resp->is_selected ? 'bg-green-50' : 'hover:bg-gray-50'); ?>">
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <?php if($resp->is_selected): ?>
                                    <span class="w-2 h-2 rounded-full bg-green-500 shrink-0"></span>
                                    <?php endif; ?>
                                    <span class="font-medium text-gray-900"><?php echo e($resp->supplier->name); ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-right font-semibold text-gray-900">
                                Rp <?php echo e(number_format($resp->total_price, 0, ',', '.')); ?>

                            </td>
                            <td class="px-4 py-2.5 text-center hidden md:table-cell text-gray-500">
                                <?php echo e($resp->delivery_days ? $resp->delivery_days . ' hari' : '—'); ?>

                            </td>
                            <td class="px-4 py-2.5 hidden md:table-cell text-gray-500">
                                <?php echo e($resp->payment_terms ?? '—'); ?>

                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <?php if(!$resp->is_selected && $rfq->status === 'open'): ?>
                                <form method="POST" action="<?php echo e(route('purchasing.rfq.response.select', $resp)); ?>">
                                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="px-2 py-1 text-xs border border-green-500 text-green-600 rounded-lg hover:bg-green-50">Pilih</button>
                                </form>
                                <?php elseif($resp->is_selected): ?>
                                <span class="text-xs text-green-600 font-semibold">✓ Dipilih</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="bg-white rounded-2xl border border-gray-200 px-4 py-12 text-center text-gray-400">
            Belum ada RFQ.
        </div>
        <?php endif; ?>

        <?php if($rfqs->hasPages()): ?>
        <div><?php echo e($rfqs->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-add-rfq" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                <h3 class="font-semibold text-gray-900">Buat RFQ</h3>
                <button onclick="document.getElementById('modal-add-rfq').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('purchasing.rfq.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Terbit *</label>
                        <input type="date" name="issue_date" value="<?php echo e(today()->format('Y-m-d')); ?>" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Deadline Respon *</label>
                        <input type="date" name="deadline" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <?php if($requisitions->count()): ?>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Berdasarkan PR (opsional)</label>
                        <select name="purchase_requisition_id" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                            <option value="">Tidak ada</option>
                            <?php $__currentLoopData = $requisitions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($pr->id); ?>"><?php echo e($pr->number); ?> — <?php echo e($pr->department ?? 'Umum'); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 resize-none"></textarea>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Item</p>
                        <button type="button" onclick="addRfqItem()" class="text-xs text-blue-600 hover:underline">+ Tambah</button>
                    </div>
                    <div id="rfq-items" class="space-y-2">
                        <div class="rfq-item grid grid-cols-12 gap-2">
                            <div class="col-span-6"><input type="text" name="items[0][description]" placeholder="Deskripsi *" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900"></div>
                            <div class="col-span-3"><input type="number" name="items[0][quantity]" placeholder="Qty" min="0.01" step="0.01" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900"></div>
                            <div class="col-span-2"><input type="text" name="items[0][unit]" placeholder="Satuan" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900"></div>
                            <div class="col-span-1 flex items-center"><button type="button" onclick="removeRfqItem(this)" class="text-red-400 hover:text-red-600">✕</button></div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-rfq').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Kirim RFQ</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-add-response" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Input Penawaran Supplier</h3>
                <button onclick="document.getElementById('modal-add-response').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-add-response" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Supplier *</label>
                    <select name="supplier_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">Pilih supplier...</option>
                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Respon *</label>
                        <input type="date" name="response_date" value="<?php echo e(today()->format('Y-m-d')); ?>" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Total Harga *</label>
                        <input type="number" name="total_price" min="0" step="1000" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Estimasi Pengiriman (hari)</label>
                        <input type="number" name="delivery_days" min="1"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Syarat Pembayaran</label>
                        <input type="text" name="payment_terms" placeholder="cth: NET 30"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-response').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-convert-rfq" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Konversi RFQ ke PO</h3>
                <button onclick="document.getElementById('modal-convert-rfq').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-convert-rfq" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <p id="convert-rfq-num" class="text-sm font-medium text-gray-700"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Gudang *</label>
                    <select name="warehouse_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">Pilih gudang...</option>
                        <?php $__currentLoopData = \App\Models\Warehouse::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal PO *</label>
                        <input type="date" name="date" value="<?php echo e(today()->format('Y-m-d')); ?>" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Pembayaran *</label>
                        <select name="payment_type" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                            <option value="credit">Kredit</option>
                            <option value="cash">Tunai</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-convert-rfq').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat PO</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    let rfqItemCount = 1;
    function addRfqItem() {
        const i = rfqItemCount++;
        const div = document.createElement('div');
        div.className = 'rfq-item grid grid-cols-12 gap-2';
        div.innerHTML = `
            <div class="col-span-6"><input type="text" name="items[${i}][description]" placeholder="Deskripsi *" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900"></div>
            <div class="col-span-3"><input type="number" name="items[${i}][quantity]" placeholder="Qty" min="0.01" step="0.01" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900"></div>
            <div class="col-span-2"><input type="text" name="items[${i}][unit]" placeholder="Satuan" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900"></div>
            <div class="col-span-1 flex items-center"><button type="button" onclick="removeRfqItem(this)" class="text-red-400 hover:text-red-600">✕</button></div>`;
        document.getElementById('rfq-items').appendChild(div);
    }
    function removeRfqItem(btn) {
        const items = document.querySelectorAll('.rfq-item');
        if (items.length > 1) btn.closest('.rfq-item').remove();
    }
    function openAddResponse(rfqId) {
        document.getElementById('form-add-response').action = '<?php echo e(url("purchasing/rfq")); ?>/' + rfqId + '/response';
        document.getElementById('modal-add-response').classList.remove('hidden');
    }
    function openConvertRfq(id, num) {
        document.getElementById('form-convert-rfq').action = '<?php echo e(url("purchasing/rfq")); ?>/' + id + '/convert';
        document.getElementById('convert-rfq-num').textContent = 'RFQ: ' + num;
        document.getElementById('modal-convert-rfq').classList.remove('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/purchasing/rfq.blade.php ENDPATH**/ ?>