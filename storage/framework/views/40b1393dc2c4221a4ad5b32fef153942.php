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
     <?php $__env->slot('header', null, []); ?> Piutang (Receivables) <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Outstanding</p>
            <p class="text-xl font-bold text-gray-900">Rp <?php echo e(number_format($stats['total_outstanding'], 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Belum Bayar</p>
            <p class="text-xl font-bold text-red-500"><?php echo e($stats['unpaid_count']); ?></p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Sebagian Bayar</p>
            <p class="text-xl font-bold text-amber-500"><?php echo e($stats['partial_count']); ?></p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Jatuh Tempo</p>
            <p class="text-xl font-bold text-red-600"><?php echo e($stats['overdue_count']); ?></p>
        </div>
    </div>

    
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nomor invoice / customer..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                <option value="unpaid" <?php if(request('status')==='unpaid'): echo 'selected'; endif; ?>>Belum Bayar</option>
                <option value="partial" <?php if(request('status')==='partial'): echo 'selected'; endif; ?>>Sebagian</option>
                <option value="paid" <?php if(request('status')==='paid'): echo 'selected'; endif; ?>>Lunas</option>
            </select>
            <label class="flex items-center gap-2 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white cursor-pointer">
                <input type="checkbox" name="overdue" value="1" <?php if(request('overdue')): echo 'checked'; endif; ?> class="rounded">
                <span class="text-gray-700">Jatuh Tempo</span>
            </label>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <a href="<?php echo e(route('payables.index')); ?>" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 text-center">
            Hutang (Payables) →
        </a>
        <a href="<?php echo e(route('receivables.aging')); ?>" class="px-4 py-2 text-sm border border-indigo-500/30 rounded-xl text-indigo-400 hover:bg-indigo-500/10 text-center">
            📊 Aging Analysis
        </a>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Invoice</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Sisa</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $overdue = in_array($inv->status, ['unpaid','partial']) && $inv->due_date < today();
                    ?>
                    <tr class="hover:bg-gray-50 <?php echo e($overdue ? 'bg-red-50/50' : ''); ?>">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900"><?php echo e($inv->number); ?></td>
                        <td class="px-4 py-3 text-gray-700"><?php echo e($inv->customer->name ?? '-'); ?></td>
                        <td class="px-4 py-3 text-right text-gray-900">Rp <?php echo e(number_format($inv->total_amount, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right font-semibold <?php echo e($inv->remaining_amount > 0 ? 'text-red-500' : 'text-green-500'); ?>">
                            Rp <?php echo e(number_format($inv->remaining_amount, 0, ',', '.')); ?>

                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php $colors = ['unpaid'=>'red','partial'=>'amber','paid'=>'green']; $c = $colors[$inv->status] ?? 'gray'; ?>
                            <span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($c); ?>-100 text-<?php echo e($c); ?>-700 $c }}-500/20 $c }}-400">
                                <?php echo e(['unpaid'=>'Belum Bayar','partial'=>'Sebagian','paid'=>'Lunas'][$inv->status] ?? $inv->status); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell text-xs <?php echo e($overdue ? 'text-red-500 font-semibold' : 'text-gray-500'); ?>">
                            <?php echo e($inv->due_date->format('d M Y')); ?>

                            <?php if($overdue): ?> <span class="text-red-400">(<?php echo e($inv->daysOverdue()); ?>h)</span> <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if($inv->status !== 'paid'): ?>
                            <div class="flex justify-center gap-1">
                                <button onclick="openPayModal('<?php echo e($inv->id); ?>','<?php echo e($inv->number); ?>','<?php echo e($inv->remaining_amount); ?>')"
                                    class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    Bayar
                                </button>
                                <a href="<?php echo e(route('receivables.installments', $inv)); ?>"
                                    class="text-xs px-2 py-1 bg-indigo-600/80 text-white rounded-lg hover:bg-indigo-700">
                                    Cicilan
                                </a>
                            </div>
                            <?php else: ?>
                            <span class="text-xs text-gray-400">Lunas</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Tidak ada piutang.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($invoices->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100"><?php echo e($invoices->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-pay" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Catat Pembayaran Piutang</h3>
                <button onclick="document.getElementById('modal-pay').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-pay" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <p class="text-sm text-gray-600">Invoice: <span id="pay-number" class="font-mono font-semibold text-gray-900"></span></p>
                <p class="text-sm text-gray-600">Sisa: <span id="pay-remaining" class="font-semibold text-red-500"></span></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Bayar *</label>
                    <input type="number" name="amount" id="pay-amount" required min="1" step="1"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Metode Bayar *</label>
                    <select name="method" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <input type="text" name="notes" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-pay').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    function openPayModal(id, number, remaining) {
        document.getElementById('pay-number').textContent = number;
        document.getElementById('pay-remaining').textContent = 'Rp ' + parseInt(remaining).toLocaleString('id-ID');
        document.getElementById('pay-amount').max = remaining;
        document.getElementById('pay-amount').value = remaining;
        document.getElementById('form-pay').action = '<?php echo e(url("receivables")); ?>/' + id + '/payment';
        document.getElementById('modal-pay').classList.remove('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\receivables\index.blade.php ENDPATH**/ ?>