

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Uang Muka (Down Payment)</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola uang muka customer dan supplier</p>
        </div>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'down_payments', 'create')): ?>
        <button onclick="document.getElementById('modalDP').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Catat Uang Muka
        </button>
        <?php endif; ?>
    </div>

    
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-xl p-4 border border-slate-200">
            <p class="text-xs text-slate-500">DP Customer Belum Dipakai</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">Rp <?php echo e(number_format($stats['customer_pending'], 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200">
            <p class="text-xs text-slate-500">DP Supplier Belum Dipakai</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">Rp <?php echo e(number_format($stats['supplier_pending'], 0, ',', '.')); ?></p>
        </div>
    </div>

    
    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nomor..."
               class="flex-1 px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
        <select name="type" class="px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800">
            <option value="">Semua Tipe</option>
            <option value="customer" <?php if(request('type') === 'customer'): echo 'selected'; endif; ?>>Customer</option>
            <option value="supplier" <?php if(request('type') === 'supplier'): echo 'selected'; endif; ?>>Supplier</option>
        </select>
        <select name="status" class="px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800">
            <option value="">Semua Status</option>
            <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>>Menunggu</option>
            <option value="partial" <?php if(request('status') === 'partial'): echo 'selected'; endif; ?>>Sebagian</option>
            <option value="applied" <?php if(request('status') === 'applied'): echo 'selected'; endif; ?>>Sudah Dipakai</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white text-sm rounded-lg">Filter</button>
    </form>

    
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Nomor</th>
                    <th class="px-4 py-3 text-left">Tipe</th>
                    <th class="px-4 py-3 text-left">Pihak</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3 text-right">Sisa</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php $__empty_1 = true; $__currentLoopData = $downPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 font-mono font-medium text-slate-800"><?php echo e($dp->number); ?></td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full <?php echo e($dp->type === 'customer' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'); ?>">
                            <?php echo e(ucfirst($dp->type)); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-600"><?php echo e($dp->party?->name ?? '-'); ?></td>
                    <td class="px-4 py-3 text-slate-500"><?php echo e($dp->payment_date->format('d/m/Y')); ?></td>
                    <td class="px-4 py-3 text-right font-medium text-slate-800">Rp <?php echo e(number_format($dp->amount, 0, ',', '.')); ?></td>
                    <td class="px-4 py-3 text-right text-slate-600">Rp <?php echo e(number_format($dp->remaining_amount, 0, ',', '.')); ?></td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo e($dp->statusColor()); ?>">
                            <?php echo e($dp->statusLabel()); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <?php if($dp->type === 'customer' && in_array($dp->status, ['pending', 'partial'])): ?>
                        <button onclick="openApplyModal(<?php echo e($dp->id); ?>, '<?php echo e($dp->number); ?>', <?php echo e($dp->remaining_amount); ?>)"
                                class="text-xs px-2 py-1 bg-green-100 text-green-700 hover:bg-green-200 rounded">
                            Aplikasikan
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-slate-400">Belum ada uang muka</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php echo e($downPayments->links()); ?>

</div>


<div id="modalDP" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between p-5 border-b border-slate-200">
            <h3 class="font-semibold text-slate-800">Catat Uang Muka</h3>
            <button onclick="document.getElementById('modalDP').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="<?php echo e(route('down-payments.store')); ?>" class="p-5 space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tipe</label>
                <select name="type" id="dpType" required onchange="toggleParty()"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="customer">Customer (DP Masuk)</option>
                    <option value="supplier">Supplier (DP Keluar)</option>
                </select>
            </div>
            <div id="customerParty">
                <label class="block text-sm font-medium text-slate-700 mb-1">Customer</label>
                <select name="party_id" id="customerPartySelect"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">-- Pilih Customer --</option>
                    <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div id="supplierParty" class="hidden">
                <label class="block text-sm font-medium text-slate-700 mb-1">Supplier</label>
                <select name="party_id" id="supplierPartySelect"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">-- Pilih Supplier --</option>
                    <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal</label>
                    <input type="date" name="payment_date" value="<?php echo e(today()->toDateString()); ?>" required
                           class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Metode</label>
                    <select name="payment_method" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="transfer">Transfer</option>
                        <option value="cash">Tunai</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah</label>
                <input type="number" name="amount" min="1" step="1" required placeholder="0"
                       class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalDP').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-slate-600 border border-slate-300 rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>


<div id="modalApply" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between p-5 border-b border-slate-200">
            <h3 class="font-semibold text-slate-800">Aplikasikan DP ke Invoice</h3>
            <button onclick="document.getElementById('modalApply').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="applyForm" method="POST" class="p-5 space-y-4">
            <?php echo csrf_field(); ?>
            <p class="text-sm text-slate-600">DP: <span id="applyDpNumber" class="font-mono font-medium"></span></p>
            <p class="text-sm text-slate-600">Sisa: <span id="applyDpRemaining" class="font-medium text-green-600"></span></p>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Invoice</label>
                <select name="invoice_id" required
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">-- Pilih Invoice --</option>
                    <?php $__currentLoopData = \App\Models\Invoice::where('tenant_id', auth()->user()->tenant_id)->whereIn('status', ['unpaid', 'partial'])->orderBy('number')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($inv->id); ?>"><?php echo e($inv->number); ?> — Sisa Rp <?php echo e(number_format($inv->remaining_amount, 0, ',', '.')); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah</label>
                <input type="number" name="amount" id="applyAmount" min="1" step="1" required
                       class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalApply').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-slate-600 border border-slate-300 rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">Aplikasikan</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleParty() {
    const type = document.getElementById('dpType').value;
    document.getElementById('customerParty').classList.toggle('hidden', type !== 'customer');
    document.getElementById('supplierParty').classList.toggle('hidden', type !== 'supplier');
    document.getElementById('customerPartySelect').required = type === 'customer';
    document.getElementById('supplierPartySelect').required = type === 'supplier';
}

function openApplyModal(dpId, dpNumber, remaining) {
    document.getElementById('applyForm').action = '<?php echo e(url("down-payments")); ?>/' + dpId + '/apply';
    document.getElementById('applyDpNumber').textContent = dpNumber;
    document.getElementById('applyDpRemaining').textContent = 'Rp ' + remaining.toLocaleString('id-ID');
    document.getElementById('applyAmount').max = remaining;
    document.getElementById('applyAmount').value = '';
    document.getElementById('modalApply').classList.remove('hidden');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\down-payments\index.blade.php ENDPATH**/ ?>