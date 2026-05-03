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
     <?php $__env->slot('header', null, []); ?> Write-off <?php echo e($type === 'receivable' ? 'Piutang' : 'Hutang'); ?> <?php $__env->endSlot(); ?>

    <div class="max-w-2xl">
        <?php if($errors->any()): ?>
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('writeoffs.store')); ?>" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="type" value="<?php echo e($type); ?>">

            <div class="p-3 rounded-xl <?php echo e($type === 'receivable' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'); ?> text-sm font-medium">
                Write-off <?php echo e($type === 'receivable' ? 'Piutang → Jurnal: Dr Bad Debt Expense / Cr Piutang Usaha' : 'Hutang → Jurnal: Dr Hutang Usaha / Cr Pendapatan Lain-lain'); ?>

            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Pilih <?php echo e($type === 'receivable' ? 'Invoice (Piutang)' : 'Hutang (Payable)'); ?> *
                </label>
                <select name="reference_id" required id="ref-select" onchange="updateAmount(this)"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih --</option>
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $label = $type === 'receivable'
                            ? "{$item->number} - {$item->customer->name} (Sisa: Rp " . number_format($item->remaining_amount,0,',','.') . ")"
                            : "{$item->number} - {$item->supplier->name} (Sisa: Rp " . number_format($item->remaining_amount,0,',','.') . ")";
                    ?>
                    <option value="<?php echo e($item->id); ?>" data-remaining="<?php echo e($item->remaining_amount); ?>" <?php echo e(old('reference_id') == $item->id ? 'selected' : ''); ?>>
                        <?php echo e($label); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sisa Tagihan</label>
                    <input type="text" id="remaining-display" readonly value="-"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-100 text-gray-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Write-off (Rp) *</label>
                    <input type="number" name="writeoff_amount" id="writeoff-amount" value="<?php echo e(old('writeoff_amount')); ?>" required min="0.01" step="0.01"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Alasan Write-off *</label>
                <textarea name="reason" required rows="3" placeholder="Contoh: Nilai terlalu kecil untuk ditagih, pelanggan tidak dapat dihubungi..."
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e(old('reason')); ?></textarea>
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-700">
                ⚠️ Write-off memerlukan persetujuan admin/manager sebelum jurnal dapat diposting.
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="<?php echo e(route('writeoffs.index')); ?>" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</a>
                <button type="submit" class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">Ajukan Write-off</button>
            </div>
        </form>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    function updateAmount(sel) {
        const opt = sel.options[sel.selectedIndex];
        const remaining = opt.dataset.remaining || 0;
        document.getElementById('remaining-display').value = 'Rp ' + parseFloat(remaining).toLocaleString('id-ID');
        document.getElementById('writeoff-amount').value = remaining;
        document.getElementById('writeoff-amount').max = remaining;
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\writeoffs\create.blade.php ENDPATH**/ ?>