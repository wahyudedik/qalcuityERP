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
     <?php $__env->slot('header', null, []); ?> Pesanan Saya <?php $__env->endSlot(); ?>

    
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <select name="status"
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = ['pending' => 'Menunggu', 'confirmed' => 'Dikonfirmasi', 'processing' => 'Diproses', 'shipped' => 'Dikirim', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($v); ?>" <?php if(request('status') === $v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900"
                placeholder="Dari">
            <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900"
                placeholder="Sampai">
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Pesanan</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $sc = match ($order->status) {
                                'completed', 'delivered' => 'green',
                                'cancelled' => 'red',
                                'shipped' => 'purple',
                                'processing' => 'blue',
                                default => 'amber',
                            };
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">
                                <?php echo e($order->number ?? '#' . $order->id); ?></td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-500">
                                <?php echo e($order->created_at?->format('d/m/Y')); ?></td>
                            <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900">Rp
                                <?php echo e(number_format($order->total_amount ?? 0, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 $sc }}-500/20 $sc }}-400"><?php echo e(ucfirst($order->status)); ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="<?php echo e(route('customer-portal.orders.show', $order)); ?>"
                                    class="text-blue-600 hover:underline text-xs">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-400">Belum
                                ada pesanan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($orders->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-100"><?php echo e($orders->links()); ?></div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\customer-portal\orders\index.blade.php ENDPATH**/ ?>