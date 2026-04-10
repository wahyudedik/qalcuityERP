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
     <?php $__env->slot('title', null, []); ?> Purchase Requisition — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> Purchase Requisition (PR) <?php $__env->endSlot(); ?>
     <?php $__env->slot('topbarActions', null, []); ?> 
        <button onclick="document.getElementById('modal-add-pr').classList.remove('hidden')"
            class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat PR
        </button>
     <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Menunggu Persetujuan</p>
            <p class="text-2xl font-bold text-amber-500 mt-1"><?php echo e($stats['pending']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Disetujui</p>
            <p class="text-2xl font-bold text-green-500 mt-1"><?php echo e($stats['approved']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total PR</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e($stats['total']); ?></p>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 mb-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = ['pending' => 'Pending', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'converted' => 'Sudah Jadi PO']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($val); ?>" <?php if(request('status') === $val): echo 'selected'; endif; ?>><?php echo e($lbl); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            <a href="<?php echo e(route('purchasing.requisitions')); ?>" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Reset</a>
        </form>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Pemohon</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Departemen</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Tgl Dibutuhkan</th>
                        <th class="px-4 py-3 text-right">Est. Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $requisitions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3">
                            <p class="font-mono text-xs font-semibold text-gray-900 dark:text-white"><?php echo e($pr->number); ?></p>
                            <p class="text-xs text-gray-400 mt-0.5"><?php echo e($pr->created_at->format('d M Y')); ?></p>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-700 dark:text-slate-300"><?php echo e($pr->requester->name); ?></td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500 dark:text-slate-400"><?php echo e($pr->department ?? '—'); ?></td>
                        <td class="px-4 py-3 hidden lg:table-cell text-gray-500 dark:text-slate-400 text-xs">
                            <?php echo e($pr->required_date?->format('d M Y') ?? '—'); ?>

                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                            Rp <?php echo e(number_format($pr->estimated_total, 0, ',', '.')); ?>

                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($pr->statusColor()); ?>"><?php echo e($pr->statusLabel()); ?></span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <?php if($pr->status === 'pending'): ?>
                                <button onclick="openApprove(<?php echo e($pr->id); ?>, '<?php echo e($pr->number); ?>')"
                                    class="px-2 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700">Proses</button>
                                <?php endif; ?>
                                <?php if($pr->status === 'approved'): ?>
                                <button onclick="openConvert(<?php echo e($pr->id); ?>, '<?php echo e($pr->number); ?>')"
                                    class="px-2 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">→ PO</button>
                                <?php endif; ?>
                                <button onclick="openDetail(<?php echo e($pr->id); ?>)"
                                    class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada Purchase Requisition.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($requisitions->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($requisitions->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-add-pr" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Purchase Requisition</h3>
                <button onclick="document.getElementById('modal-add-pr').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('purchasing.requisitions.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Departemen</label>
                        <input type="text" name="department" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Dibutuhkan</label>
                        <input type="date" name="required_date" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tujuan / Keperluan</label>
                        <textarea name="purpose" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white resize-none"></textarea>
                    </div>
                </div>

                
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide">Item yang Diminta</p>
                        <button type="button" onclick="addPrItem()" class="text-xs text-blue-600 hover:underline">+ Tambah Item</button>
                    </div>
                    <div id="pr-items" class="space-y-2">
                        <div class="pr-item grid grid-cols-12 gap-2 items-start">
                            <div class="col-span-5">
                                <input type="text" name="items[0][description]" placeholder="Deskripsi item *" required
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            </div>
                            <div class="col-span-2">
                                <input type="number" name="items[0][quantity]" placeholder="Qty" min="0.01" step="0.01" required
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            </div>
                            <div class="col-span-2">
                                <input type="text" name="items[0][unit]" placeholder="Satuan"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            </div>
                            <div class="col-span-2">
                                <input type="number" name="items[0][estimated_price]" placeholder="Est. Harga" min="0" step="1000"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            </div>
                            <div class="col-span-1 flex items-center pt-1">
                                <button type="button" onclick="removePrItem(this)" class="text-red-400 hover:text-red-600">✕</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-pr').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Kirim PR</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-approve-pr" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Proses PR</h3>
                <button onclick="document.getElementById('modal-approve-pr').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-approve-pr" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <p id="approve-pr-num" class="text-sm font-medium text-gray-700 dark:text-slate-300"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Keputusan *</label>
                    <select name="action" id="pr-action" onchange="togglePrRejection()" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="approved">Setujui</option>
                        <option value="rejected">Tolak</option>
                    </select>
                </div>
                <div id="pr-rejection-wrap" class="hidden">
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Alasan Penolakan</label>
                    <textarea name="rejection_reason" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-approve-pr').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-convert-pr" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Konversi PR ke PO</h3>
                <button onclick="document.getElementById('modal-convert-pr').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-convert-pr" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <p id="convert-pr-num" class="text-sm font-medium text-gray-700 dark:text-slate-300"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Supplier *</label>
                    <select name="supplier_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="">Pilih supplier...</option>
                        <?php $__currentLoopData = \App\Models\Supplier::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Gudang *</label>
                    <select name="warehouse_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="">Pilih gudang...</option>
                        <?php $__currentLoopData = \App\Models\Warehouse::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal PO *</label>
                        <input type="date" name="date" value="<?php echo e(today()->format('Y-m-d')); ?>" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Pembayaran *</label>
                        <select name="payment_type" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="credit">Kredit</option>
                            <option value="cash">Tunai</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-convert-pr').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat PO</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    let prItemCount = 1;
    function addPrItem() {
        const i = prItemCount++;
        const div = document.createElement('div');
        div.className = 'pr-item grid grid-cols-12 gap-2 items-start';
        div.innerHTML = `
            <div class="col-span-5"><input type="text" name="items[${i}][description]" placeholder="Deskripsi item *" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></div>
            <div class="col-span-2"><input type="number" name="items[${i}][quantity]" placeholder="Qty" min="0.01" step="0.01" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></div>
            <div class="col-span-2"><input type="text" name="items[${i}][unit]" placeholder="Satuan" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></div>
            <div class="col-span-2"><input type="number" name="items[${i}][estimated_price]" placeholder="Est. Harga" min="0" step="1000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></div>
            <div class="col-span-1 flex items-center pt-1"><button type="button" onclick="removePrItem(this)" class="text-red-400 hover:text-red-600">✕</button></div>`;
        document.getElementById('pr-items').appendChild(div);
    }
    function removePrItem(btn) {
        const items = document.querySelectorAll('.pr-item');
        if (items.length > 1) btn.closest('.pr-item').remove();
    }
    function openApprove(id, num) {
        document.getElementById('form-approve-pr').action = '<?php echo e(url("purchasing/requisitions")); ?>/' + id + '/approve';
        document.getElementById('approve-pr-num').textContent = 'PR: ' + num;
        document.getElementById('modal-approve-pr').classList.remove('hidden');
    }
    function openConvert(id, num) {
        document.getElementById('form-convert-pr').action = '<?php echo e(url("purchasing/requisitions")); ?>/' + id + '/convert';
        document.getElementById('convert-pr-num').textContent = 'PR: ' + num;
        document.getElementById('modal-convert-pr').classList.remove('hidden');
    }
    function togglePrRejection() {
        document.getElementById('pr-rejection-wrap').classList.toggle('hidden', document.getElementById('pr-action').value !== 'rejected');
    }
    function openDetail(id) { /* future */ }
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/purchasing/requisitions.blade.php ENDPATH**/ ?>