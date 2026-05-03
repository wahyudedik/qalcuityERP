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
     <?php $__env->slot('header', null, []); ?> Detail Penawaran — <?php echo e($quotation->number); ?> <?php $__env->endSlot(); ?>

    <div class="space-y-6">
        
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900"><?php echo e($quotation->number); ?></h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Customer: <span class="font-medium text-gray-700"><?php echo e($quotation->customer->name); ?></span>
                    </p>
                    <p class="text-sm text-gray-500">
                        Tanggal: <?php echo e($quotation->date->format('d M Y')); ?> —
                        Berlaku hingga: <span class="<?php echo e($quotation->valid_until < today() ? 'text-red-500' : ''); ?>"><?php echo e($quotation->valid_until?->format('d M Y') ?? '-'); ?></span>
                    </p>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <?php
                        $colors = ['draft'=>'gray','sent'=>'blue','accepted'=>'green','rejected'=>'red','expired'=>'orange'];
                        $labels = ['draft'=>'Draft','sent'=>'Terkirim','accepted'=>'Diterima','rejected'=>'Ditolak','expired'=>'Kadaluarsa'];
                        $c = $colors[$quotation->status] ?? 'gray';
                    ?>
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-<?php echo e($c); ?>-100 text-<?php echo e($c); ?>-700 $c }}-500/20 $c }}-400">
                        <?php echo e($labels[$quotation->status] ?? $quotation->status); ?>

                    </span>

                    
                    <?php if($quotation->status === 'draft'): ?>
                    <form method="POST" action="<?php echo e(route('quotations.status', $quotation)); ?>">
                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                        <input type="hidden" name="status" value="sent">
                        <button type="submit" class="text-sm px-3 py-1.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tandai Terkirim</button>
                    </form>
                    <?php elseif($quotation->status === 'sent'): ?>
                    <div class="flex gap-2">
                        <form method="POST" action="<?php echo e(route('quotations.status', $quotation)); ?>">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <input type="hidden" name="status" value="accepted">
                            <button type="submit" class="text-sm px-3 py-1.5 bg-green-600 text-white rounded-xl hover:bg-green-700">Diterima</button>
                        </form>
                        <form method="POST" action="<?php echo e(route('quotations.status', $quotation)); ?>">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="text-sm px-3 py-1.5 bg-red-600 text-white rounded-xl hover:bg-red-700">Ditolak</button>
                        </form>
                    </div>
                    <?php endif; ?>

                    <?php if(in_array($quotation->status, ['draft','sent']) && $quotation->valid_until >= today()): ?>
                    <form method="POST" action="<?php echo e(route('quotations.convert', $quotation)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-sm px-3 py-1.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700"
                            onclick="return confirm('Konversi penawaran ini ke Sales Order?')">
                            Konversi ke Sales Order
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left">Deskripsi</th>
                            <th class="px-4 py-2 text-right">Qty</th>
                            <th class="px-4 py-2 text-right">Harga</th>
                            <th class="px-4 py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $quotation->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="px-4 py-3 text-gray-700">
                                <?php echo e($item->description); ?>

                                <?php if($item->product): ?> <span class="text-xs text-gray-400">(<?php echo e($item->product->name); ?>)</span> <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900"><?php echo e($item->quantity); ?></td>
                            <td class="px-4 py-3 text-right text-gray-900">Rp <?php echo e(number_format($item->price, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp <?php echo e(number_format($item->total, 0, ',', '.')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    <tfoot class="border-t border-gray-200">
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right text-sm text-gray-500">Subtotal</td>
                            <td class="px-4 py-2 text-right text-gray-900">Rp <?php echo e(number_format($quotation->subtotal, 0, ',', '.')); ?></td>
                        </tr>
                        <?php if($quotation->discount > 0): ?>
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right text-sm text-gray-500">Diskon</td>
                            <td class="px-4 py-2 text-right text-red-500">- Rp <?php echo e(number_format($quotation->discount, 0, ',', '.')); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="font-semibold">
                            <td colspan="3" class="px-4 py-2 text-right text-gray-900">Total</td>
                            <td class="px-4 py-2 text-right text-lg text-gray-900">Rp <?php echo e(number_format($quotation->total, 0, ',', '.')); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if($quotation->notes): ?>
            <p class="mt-4 text-sm text-gray-500">Catatan: <?php echo e($quotation->notes); ?></p>
            <?php endif; ?>
        </div>

        
        <?php if($quotation->salesOrders->isNotEmpty()): ?>
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-3">Sales Order Terkait</h3>
            <div class="space-y-2">
                <?php $__currentLoopData = $quotation->salesOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $so): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between px-4 py-3 rounded-xl bg-gray-50">
                    <span class="font-mono text-sm text-gray-900"><?php echo e($so->number); ?></span>
                    <span class="text-sm text-gray-500"><?php echo e($so->date->format('d M Y')); ?></span>
                    <span class="font-medium text-gray-900">Rp <?php echo e(number_format($so->total, 0, ',', '.')); ?></span>
                    <span class="px-2 py-0.5 rounded-full text-xs bg-blue-500/20 text-blue-400"><?php echo e($so->status); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\quotations\show.blade.php ENDPATH**/ ?>