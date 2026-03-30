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
     <?php $__env->slot('header', null, []); ?> Cicilan Invoice <?php echo e($invoice->number); ?> <?php $__env->endSlot(); ?>

    <div class="max-w-3xl mx-auto space-y-5">

        <?php if(session('success')): ?>
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($e); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        
        <div class="bg-white/5 border border-white/10 rounded-xl p-5 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <div class="text-gray-400 text-xs mb-1">Invoice</div>
                <div class="text-white font-mono font-semibold"><?php echo e($invoice->number); ?></div>
            </div>
            <div>
                <div class="text-gray-400 text-xs mb-1">Customer</div>
                <div class="text-white"><?php echo e($invoice->customer?->name); ?></div>
            </div>
            <div>
                <div class="text-gray-400 text-xs mb-1">Total</div>
                <div class="text-white font-semibold">Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></div>
            </div>
            <div>
                <div class="text-gray-400 text-xs mb-1">Sisa</div>
                <div class="text-red-400 font-semibold">Rp <?php echo e(number_format($invoice->remaining_amount, 0, ',', '.')); ?></div>
            </div>
        </div>

        
        <?php if($invoice->installments->count() > 0): ?>
        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-white/10 flex items-center justify-between">
                <h3 class="text-white font-semibold text-sm">Jadwal Cicilan</h3>
                <span class="text-xs text-gray-400"><?php echo e($invoice->installments->count()); ?> cicilan</span>
            </div>
            <table class="w-full text-sm text-gray-300">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-right">Sudah Bayar</th>
                        <th class="px-4 py-3 text-right">Sisa</th>
                        <th class="px-4 py-3 text-left">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php $__currentLoopData = $invoice->installments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inst): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $overdue = $inst->status !== 'paid' && $inst->due_date < today(); ?>
                    <tr class="hover:bg-white/5 <?php echo e($overdue ? 'bg-red-900/10' : ''); ?>">
                        <td class="px-4 py-3 font-semibold text-white"><?php echo e($inst->installment_number); ?></td>
                        <td class="px-4 py-3 text-right">Rp <?php echo e(number_format($inst->amount, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right text-green-400">Rp <?php echo e(number_format($inst->paid_amount, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right <?php echo e($inst->remaining() > 0 ? 'text-red-400' : 'text-gray-500'); ?>">
                            Rp <?php echo e(number_format($inst->remaining(), 0, ',', '.')); ?>

                        </td>
                        <td class="px-4 py-3 <?php echo e($overdue ? 'text-red-400 font-semibold' : ''); ?>">
                            <?php echo e($inst->due_date->format('d M Y')); ?>

                            <?php if($overdue): ?> <span class="text-xs">(Terlambat)</span> <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs
                                <?php echo e($inst->status === 'paid' ? 'bg-green-500/20 text-green-400' : ''); ?>

                                <?php echo e($inst->status === 'partial' ? 'bg-yellow-500/20 text-yellow-400' : ''); ?>

                                <?php echo e($inst->status === 'unpaid' ? 'bg-red-500/20 text-red-400' : ''); ?>">
                                <?php echo e(['paid' => 'Lunas', 'partial' => 'Sebagian', 'unpaid' => 'Belum Bayar'][$inst->status]); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if($inst->status !== 'paid'): ?>
                            <button onclick="openPayModal('<?php echo e($inst->id); ?>', <?php echo e($inst->remaining()); ?>)"
                                class="text-xs px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded-lg">Bayar</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        
        <div class="bg-white/5 border border-white/10 rounded-xl p-5 space-y-4">
            <h3 class="text-white font-semibold text-sm">
                <?php echo e($invoice->installments->count() > 0 ? 'Ubah Jadwal Cicilan' : 'Buat Jadwal Cicilan'); ?>

            </h3>

            <form method="POST" action="<?php echo e(route('receivables.installments.store', $invoice)); ?>" id="installment-form" class="space-y-4">
                <?php echo csrf_field(); ?>

                <div id="installment-lines" class="space-y-2">
                    <?php if($invoice->installments->count() > 0): ?>
                        <?php $__currentLoopData = $invoice->installments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $inst): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="installment-row grid grid-cols-12 gap-2 items-center">
                            <div class="col-span-1 text-center text-gray-400 text-sm font-semibold"><?php echo e($i + 1); ?></div>
                            <div class="col-span-4">
                                <input type="number" name="installments[<?php echo e($i); ?>][amount]" value="<?php echo e($inst->amount); ?>"
                                    placeholder="Jumlah" min="1" step="1" required
                                    class="amount-input w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div class="col-span-4">
                                <input type="date" name="installments[<?php echo e($i); ?>][due_date]" value="<?php echo e($inst->due_date->format('Y-m-d')); ?>" required
                                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div class="col-span-2">
                                <input type="text" name="installments[<?php echo e($i); ?>][notes]" value="<?php echo e($inst->notes); ?>" placeholder="Ket."
                                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div class="col-span-1 text-center">
                                <button type="button" onclick="this.closest('.installment-row').remove(); updateTotal();"
                                    class="text-red-400 hover:text-red-300 text-lg">×</button>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <div class="installment-row grid grid-cols-12 gap-2 items-center">
                            <div class="col-span-1 text-center text-gray-400 text-sm font-semibold">1</div>
                            <div class="col-span-4">
                                <input type="number" name="installments[0][amount]" placeholder="Jumlah" min="1" step="1" required
                                    class="amount-input w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div class="col-span-4">
                                <input type="date" name="installments[0][due_date]" required
                                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div class="col-span-2">
                                <input type="text" name="installments[0][notes]" placeholder="Ket."
                                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div class="col-span-1"></div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex items-center justify-between">
                    <button type="button" id="add-installment" class="text-indigo-400 hover:text-indigo-300 text-sm">+ Tambah Cicilan</button>
                    <div class="text-sm text-gray-400">
                        Total cicilan: <span id="installment-total" class="text-white font-mono font-semibold">0</span>
                        / <span class="text-indigo-400">Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></span>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm">Simpan Jadwal</button>
                    <a href="<?php echo e(route('receivables.index')); ?>" class="bg-white/10 hover:bg-white/20 text-white px-5 py-2 rounded-lg text-sm">Kembali</a>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-pay-inst" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-sm p-6">
            <h3 class="text-white font-semibold mb-4">Bayar Cicilan</h3>
            <form id="form-pay-inst" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="text-xs text-gray-400 mb-1 block">Jumlah Bayar *</label>
                    <input type="number" name="amount" id="inst-amount" required min="1" step="1"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="text-xs text-gray-400 mb-1 block">Metode</label>
                    <select name="method" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="transfer">Transfer Bank</option>
                        <option value="cash">Cash</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm">Bayar</button>
                    <button type="button" onclick="document.getElementById('modal-pay-inst').classList.add('hidden')"
                        class="flex-1 bg-white/10 hover:bg-white/20 text-white py-2 rounded-lg text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const invoiceTotal = <?php echo e($invoice->total_amount); ?>;
    let lineCount = <?php echo e(max($invoice->installments->count(), 1)); ?>;

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.amount-input').forEach(i => total += parseFloat(i.value || 0));
        document.getElementById('installment-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    document.addEventListener('input', e => {
        if (e.target.classList.contains('amount-input')) updateTotal();
    });

    document.getElementById('add-installment').addEventListener('click', () => {
        const idx = lineCount++;
        const div = document.createElement('div');
        div.className = 'installment-row grid grid-cols-12 gap-2 items-center';
        div.innerHTML = `
            <div class="col-span-1 text-center text-gray-400 text-sm font-semibold">${idx + 1}</div>
            <div class="col-span-4">
                <input type="number" name="installments[${idx}][amount]" placeholder="Jumlah" min="1" step="1" required
                    class="amount-input w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div class="col-span-4">
                <input type="date" name="installments[${idx}][due_date]" required
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div class="col-span-2">
                <input type="text" name="installments[${idx}][notes]" placeholder="Ket."
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div class="col-span-1 text-center">
                <button type="button" onclick="this.closest('.installment-row').remove(); updateTotal();"
                    class="text-red-400 hover:text-red-300 text-lg">×</button>
            </div>`;
        document.getElementById('installment-lines').appendChild(div);
    });

    function openPayModal(id, remaining) {
        document.getElementById('inst-amount').value = remaining;
        document.getElementById('inst-amount').max = remaining;
        document.getElementById('form-pay-inst').action = '<?php echo e(url("receivables/installment")); ?>/' + id + '/pay';
        document.getElementById('modal-pay-inst').classList.remove('hidden');
    }

    updateTotal();
    </script>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\receivables\installments.blade.php ENDPATH**/ ?>