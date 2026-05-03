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
     <?php $__env->slot('header', null, []); ?> Penawaran Harga (Quotation) <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Draft</p>
            <p class="text-2xl font-bold text-gray-500"><?php echo e($stats['draft']); ?></p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Terkirim</p>
            <p class="text-2xl font-bold text-blue-500"><?php echo e($stats['sent']); ?></p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Diterima</p>
            <p class="text-2xl font-bold text-green-500"><?php echo e($stats['accepted']); ?></p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Kadaluarsa</p>
            <p class="text-2xl font-bold text-red-500"><?php echo e($stats['expired']); ?></p>
        </div>
    </div>

    
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nomor QT / customer..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = ['draft'=>'Draft','sent'=>'Terkirim','accepted'=>'Diterima','rejected'=>'Ditolak','expired'=>'Kadaluarsa']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($v); ?>" <?php if(request('status')===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <button onclick="document.getElementById('modal-create-qt').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Buat Penawaran</button>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Berlaku Hingga</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $quotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $expired = in_array($qt->status, ['draft','sent']) && $qt->valid_until && $qt->valid_until < today();
                    ?>
                    <tr class="hover:bg-gray-50 <?php echo e($expired ? 'opacity-60' : ''); ?>">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900">
                            <a href="<?php echo e(route('quotations.show', $qt)); ?>" class="hover:text-blue-500"><?php echo e($qt->number); ?></a>
                        </td>
                        <td class="px-4 py-3 text-gray-700"><?php echo e($qt->customer->name ?? '-'); ?></td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">Rp <?php echo e(number_format($qt->total, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-center">
                            <?php
                                $colors = ['draft'=>'gray','sent'=>'blue','accepted'=>'green','rejected'=>'red','expired'=>'orange'];
                                $labels = ['draft'=>'Draft','sent'=>'Terkirim','accepted'=>'Diterima','rejected'=>'Ditolak','expired'=>'Kadaluarsa'];
                                $status = $expired ? 'expired' : $qt->status;
                                $c = $colors[$status] ?? 'gray';
                            ?>
                            <span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($c); ?>-100 text-<?php echo e($c); ?>-700 $c }}-500/20 $c }}-400">
                                <?php echo e($labels[$status] ?? $status); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell text-xs <?php echo e($expired ? 'text-red-500' : 'text-gray-500'); ?>">
                            <?php echo e($qt->valid_until?->format('d M Y') ?? '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="<?php echo e(route('quotations.show', $qt)); ?>" class="text-xs px-2 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">
                                    Detail
                                </a>
                                <?php if(in_array($qt->status, ['draft','sent']) && !$expired): ?>
                                <button onclick="openEditQt(<?php echo e($qt->id); ?>, <?php echo e($qt->customer_id); ?>, <?php echo e($qt->valid_until ? $qt->date->diffInDays($qt->valid_until) : 7); ?>, <?php echo e($qt->discount); ?>, '<?php echo e(addslashes($qt->notes ?? '')); ?>', <?php echo json_encode($qt->items, 15, 512) ?>)"
                                    class="text-xs px-2 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">
                                    Edit
                                </button>
                                <form method="POST" action="<?php echo e(route('quotations.convert', $qt)); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700"
                                        onclick="return confirm('Konversi ke Sales Order?')">
                                        → SO
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if($qt->status !== 'accepted'): ?>
                                <form method="POST" action="<?php echo e(route('quotations.destroy', $qt)); ?>" class="inline">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-xs px-2 py-1 text-red-500 hover:text-red-700"
                                        onclick="return confirm('Hapus penawaran ini?')">✕</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada penawaran.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($quotations->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100"><?php echo e($quotations->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-create-qt" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                <h3 class="font-semibold text-gray-900">Buat Penawaran Harga</h3>
                <button onclick="document.getElementById('modal-create-qt').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('quotations.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Customer *</label>
                        <select name="customer_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Customer --</option>
                            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Berlaku (hari) *</label>
                        <input type="number" name="valid_days" value="7" required min="1" max="365"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-medium text-gray-600">Item Penawaran *</label>
                        <button type="button" onclick="addQtItem()" class="text-xs text-blue-600 hover:underline">+ Tambah Item</button>
                    </div>
                    <div id="qt-items" class="space-y-2">
                        <div class="qt-item grid grid-cols-12 gap-2 items-center">
                            <div class="col-span-4">
                                <select name="items[0][product_id]" onchange="fillDesc(this,0)" class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Produk (opsional) --</option>
                                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id); ?>" data-name="<?php echo e($p->name); ?>" data-price="<?php echo e($p->price_sell); ?>"><?php echo e($p->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-span-3">
                                <input type="text" name="items[0][description]" id="desc-0" placeholder="Deskripsi *" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="col-span-2">
                                <input type="number" name="items[0][quantity]" placeholder="Qty" min="0.001" step="0.001" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="col-span-2">
                                <input type="number" name="items[0][price]" id="price-0" placeholder="Harga" min="0" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="col-span-1 text-center">
                                <button type="button" onclick="this.closest('.qt-item').remove()" class="text-red-500 hover:text-red-700 text-lg leading-none">×</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Diskon (Rp)</label>
                        <input type="number" name="discount" min="0" step="1000" value="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                        <input type="text" name="notes" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-create-qt').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat Penawaran</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-edit-qt" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                <h3 class="font-semibold text-gray-900">Edit Penawaran</h3>
                <button onclick="document.getElementById('modal-edit-qt').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-edit-qt" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Customer *</label>
                        <select id="eq-customer" name="customer_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Customer --</option>
                            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Berlaku (hari) *</label>
                        <input type="number" id="eq-valid-days" name="valid_days" value="7" required min="1" max="365"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-medium text-gray-600">Item Penawaran *</label>
                        <button type="button" onclick="addEqItem()" class="text-xs text-blue-600 hover:underline">+ Tambah Item</button>
                    </div>
                    <div id="eq-items" class="space-y-2"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Diskon (Rp)</label>
                        <input type="number" id="eq-discount" name="discount" min="0" step="1000" value="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                        <input type="text" id="eq-notes" name="notes" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-edit-qt').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    let qtItemCount = 1;
    let eqItemCount = 0;
    const productOpts = <?php echo json_encode($products->map(function($p) { return ['id'=>$p->id, 'name'=>$p->name, 'price'=>$p->price_sell]; })) ?>;

    function fillDesc(sel, idx) {
        const opt = sel.options[sel.selectedIndex];
        const desc = document.getElementById('desc-' + idx);
        const price = document.getElementById('price-' + idx);
        if (opt.value) {
            if (desc) desc.value = opt.dataset.name;
            if (price) price.value = opt.dataset.price;
        }
    }

    function buildItemRow(prefix, i, item = null) {
        const opts = productOpts.map(p =>
            `<option value="${p.id}" data-name="${p.name}" data-price="${p.price}" ${item && item.product_id == p.id ? 'selected' : ''}>${p.name}</option>`
        ).join('');
        const div = document.createElement('div');
        div.className = `${prefix}-item grid grid-cols-12 gap-2 items-center`;
        div.innerHTML = `
            <div class="col-span-4">
                <select name="items[${i}][product_id]" onchange="fillDesc(this,'${prefix}-${i}')" class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Produk (opsional) --</option>${opts}
                </select>
            </div>
            <div class="col-span-3">
                <input type="text" name="items[${i}][description]" id="desc-${prefix}-${i}" placeholder="Deskripsi *" required value="${item ? item.description : ''}"
                    class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="col-span-2">
                <input type="number" name="items[${i}][quantity]" placeholder="Qty" min="0.001" step="0.001" required value="${item ? item.quantity : ''}"
                    class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="col-span-2">
                <input type="number" name="items[${i}][price]" id="price-${prefix}-${i}" placeholder="Harga" min="0" required value="${item ? item.price : ''}"
                    class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="col-span-1 text-center">
                <button type="button" onclick="this.closest('.${prefix}-item').remove()" class="text-red-500 hover:text-red-700 text-lg leading-none">×</button>
            </div>`;
        return div;
    }

    function addQtItem() {
        const i = qtItemCount++;
        document.getElementById('qt-items').appendChild(buildItemRow('qt', i));
    }

    function addEqItem(item = null) {
        const i = eqItemCount++;
        document.getElementById('eq-items').appendChild(buildItemRow('eq', i, item));
    }

    function openEditQt(id, customerId, validDays, discount, notes, items) {
        const form = document.getElementById('form-edit-qt');
        form.action = '<?php echo e(route("quotations.index")); ?>/' + id;
        document.getElementById('eq-customer').value = customerId;
        document.getElementById('eq-valid-days').value = validDays;
        document.getElementById('eq-discount').value = discount;
        document.getElementById('eq-notes').value = notes;

        // Reset items
        eqItemCount = 0;
        document.getElementById('eq-items').innerHTML = '';
        items.forEach(item => addEqItem(item));

        document.getElementById('modal-edit-qt').classList.remove('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\quotations\index.blade.php ENDPATH**/ ?>