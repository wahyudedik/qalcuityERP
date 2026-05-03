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
     <?php $__env->slot('header', null, []); ?> | <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('printing.dashboard')); ?>"
                    class="text-gray-500 hover:text-gray-700 transition text-sm">
                    ← Kembali
                </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <?php if($orders->count() === 0): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'document','title' => 'Belum ada pesanan web-to-print','message' => 'Belum ada pesanan dari portal web-to-print.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document','title' => 'Belum ada pesanan web-to-print','message' => 'Belum ada pesanan dari portal web-to-print.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                No. Order</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Customer</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Template</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Qty</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Total</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Pembayaran</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Fulfillment</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-indigo-600">
                                    <?php echo e($order->order_number); ?>

                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <?php echo e($order->customer?->name ?? ($order->customer_name ?? 'N/A')); ?>

                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <?php echo e($order->product_template ?? '-'); ?>

                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <?php echo e(number_format($order->quantity)); ?>

                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    Rp <?php echo e(number_format($order->total_price ?? 0, 0, ',', '.')); ?>

                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                        $payColors = ['pending' => 'yellow', 'paid' => 'green', 'refunded' => 'red'];
                                        $payColor = $payColors[$order->payment_status] ?? 'gray';
                                    ?>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-<?php echo e($payColor); ?>-100 text-<?php echo e($payColor); ?>-700 $payColor }}-500/20 $payColor }}-400">
                                        <?php echo e(ucfirst($order->payment_status ?? 'pending')); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                        $fulColors = [
                                            'pending' => 'gray',
                                            'in_production' => 'blue',
                                            'shipped' => 'purple',
                                            'delivered' => 'green',
                                        ];
                                        $fulColor = $fulColors[$order->fulfillment_status] ?? 'gray';
                                    ?>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-<?php echo e($fulColor); ?>-100 text-<?php echo e($fulColor); ?>-700 $fulColor }}-500/20 $fulColor }}-400">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $order->fulfillment_status ?? 'pending'))); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    <?php echo e($order->created_at?->format('d M Y') ?? '-'); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($orders->links()); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\printing\web-orders.blade.php ENDPATH**/ ?>