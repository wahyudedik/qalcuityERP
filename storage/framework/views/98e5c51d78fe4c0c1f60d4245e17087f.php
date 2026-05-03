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
        Batch / Lot — <?php echo e($product->name); ?>

        <span class="text-sm font-normal text-gray-400 ml-2"><?php echo e($product->sku); ?></span>
     <?php $__env->endSlot(); ?>

    <div class="max-w-5xl mx-auto space-y-5">

        <?php if(session('success')): ?>
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        
        <div class="bg-white rounded-2xl border border-gray-200 p-5 flex items-center gap-4">
            <div class="w-10 h-10 bg-blue-500/20 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-gray-900"><?php echo e($product->name); ?></p>
                <p class="text-xs text-gray-500">
                    Alert expired: <strong class="text-yellow-400"><?php echo e($product->expiry_alert_days); ?> hari sebelum</strong>
                    &bull; Total stok: <?php echo e($product->totalStock()); ?> <?php echo e($product->unit); ?>

                </p>
            </div>
            <a href="<?php echo e(route('inventory.index')); ?>"
                class="text-sm text-gray-400 hover:text-white transition">← Kembali</a>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Daftar Batch</h2>
                <span class="text-xs text-gray-400"><?php echo e($batches->total()); ?> batch</span>
            </div>

            <?php if($batches->isEmpty()): ?>
                <div class="px-6 py-12 text-center text-gray-400 text-sm">
                    Belum ada batch. Tambah stok dengan mengisi nomor batch dan tanggal expired.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-xs text-gray-500">
                                <th class="px-4 py-3 text-left">No. Batch</th>
                                <th class="px-4 py-3 text-left">Gudang</th>
                                <th class="px-4 py-3 text-right">Qty</th>
                                <th class="px-4 py-3 text-left">Tgl Produksi</th>
                                <th class="px-4 py-3 text-left">Tgl Expired</th>
                                <th class="px-4 py-3 text-left">Sisa Hari</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $days = $batch->daysUntilExpiry();
                                    $alertDays = $product->expiry_alert_days;
                                    $rowClass = match(true) {
                                        $batch->status !== 'active'   => 'opacity-50',
                                        $days < 0                     => 'bg-red-500/5',
                                        $days <= $alertDays           => 'bg-yellow-500/5',
                                        default                       => '',
                                    };
                                ?>
                                <tr class="<?php echo e($rowClass); ?>">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-700">
                                        <?php echo e($batch->batch_number); ?>

                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        <?php echo e($batch->warehouse->name ?? '-'); ?>

                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900">
                                        <?php echo e(number_format($batch->quantity)); ?> <?php echo e($product->unit); ?>

                                    </td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">
                                        <?php echo e($batch->manufacture_date?->format('d/m/Y') ?? '-'); ?>

                                    </td>
                                    <td class="px-4 py-3 text-xs font-medium
                                        <?php echo e($days < 0 ? 'text-red-400' : ($days <= $alertDays ? 'text-yellow-400' : 'text-gray-600')); ?>">
                                        <?php echo e($batch->expiry_date->format('d/m/Y')); ?>

                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        <?php if($batch->status !== 'active'): ?>
                                            <span class="text-gray-400">—</span>
                                        <?php elseif($days < 0): ?>
                                            <span class="text-red-400 font-semibold">Expired <?php echo e(abs($days)); ?>h lalu</span>
                                        <?php elseif($days === 0): ?>
                                            <span class="text-red-400 font-semibold">Expired hari ini</span>
                                        <?php elseif($days <= $alertDays): ?>
                                            <span class="text-yellow-400 font-semibold"><?php echo e($days); ?> hari lagi</span>
                                        <?php else: ?>
                                            <span class="text-green-400"><?php echo e($days); ?> hari</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php
                                            $statusColors = [
                                                'active'   => 'bg-green-500/20 text-green-400',
                                                'expired'  => 'bg-red-500/20 text-red-400',
                                                'recalled' => 'bg-orange-500/20 text-orange-400',
                                                'consumed' => 'bg-gray-500/20 text-gray-400',
                                            ];
                                            $statusLabels = [
                                                'active'   => 'Aktif',
                                                'expired'  => 'Expired',
                                                'recalled' => 'Ditarik',
                                                'consumed' => 'Habis',
                                            ];
                                        ?>
                                        <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($statusColors[$batch->status] ?? ''); ?>">
                                            <?php echo e($statusLabels[$batch->status] ?? $batch->status); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if($batch->status === 'active'): ?>
                                            <form method="POST" action="<?php echo e(route('inventory.batches.status', $batch)); ?>">
                                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                                <select name="status" onchange="this.form.submit()"
                                                    class="text-xs bg-gray-100 border-0 rounded-lg px-2 py-1 text-gray-700 cursor-pointer">
                                                    <option value="">Ubah status...</option>
                                                    <option value="consumed">Tandai Habis</option>
                                                    <option value="recalled">Tarik (Recall)</option>
                                                    <option value="expired">Tandai Expired</option>
                                                </select>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <?php if($batches->hasPages()): ?>
                    <div class="px-6 py-4 border-t border-gray-100">
                        <?php echo e($batches->links()); ?>

                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        
        <div class="flex flex-wrap gap-3 text-xs text-gray-500">
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-red-500/20 inline-block"></span> Expired / akan expired hari ini
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-yellow-500/20 inline-block"></span> Dalam window alert (<?php echo e($product->expiry_alert_days); ?> hari)
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded bg-green-500/20 inline-block"></span> Aman
            </span>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\inventory\batches.blade.php ENDPATH**/ ?>