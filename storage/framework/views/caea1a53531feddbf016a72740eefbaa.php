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
     <?php $__env->slot('header', null, []); ?> Konsinyasi — <?php echo e($consignmentShipment->number); ?> <?php $__env->endSlot(); ?>

    <?php $ship = $consignmentShipment; ?>
    <div class="space-y-6">
        
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900"><?php echo e($ship->number); ?></h2>
                    <p class="text-sm text-gray-500">🏪 <?php echo e($ship->partner->name ?? '-'); ?> · <?php echo e($ship->warehouse->name ?? '-'); ?></p>
                </div>
                <?php
                    $sc = ['draft'=>'gray','shipped'=>'blue','partial_sold'=>'amber','settled'=>'green','returned'=>'purple'][$ship->status] ?? 'gray';
                    $sl = ['draft'=>'Draft','shipped'=>'Dikirim','partial_sold'=>'Sebagian Terjual','settled'=>'Settled','returned'=>'Diretur'][$ship->status] ?? $ship->status;
                ?>
                <span class="px-3 py-1 rounded-full text-sm font-medium bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 $sc }}-500/20 $sc }}-400"><?php echo e($sl); ?></span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><p class="text-xs text-gray-500">Tanggal Kirim</p><p class="text-gray-900"><?php echo e($ship->ship_date->format('d/m/Y')); ?></p></div>
                <div><p class="text-xs text-gray-500">Nilai HPP</p><p class="text-gray-900">Rp <?php echo e(number_format($ship->total_cost, 0, ',', '.')); ?></p></div>
                <div><p class="text-xs text-gray-500">Nilai Retail</p><p class="font-semibold text-gray-900">Rp <?php echo e(number_format($ship->total_retail, 0, ',', '.')); ?></p></div>
                <div><p class="text-xs text-gray-500">Komisi Partner</p><p class="text-gray-900"><?php echo e($ship->partner->commission_pct ?? 0); ?>%</p></div>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Item Titipan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Produk</th>
                            <th class="px-4 py-3 text-right">Dikirim</th>
                            <th class="px-4 py-3 text-right">Terjual</th>
                            <th class="px-4 py-3 text-right">Diretur</th>
                            <th class="px-4 py-3 text-right">Sisa</th>
                            <th class="px-4 py-3 text-right">HPP</th>
                            <th class="px-4 py-3 text-right">Harga Jual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $ship->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="px-4 py-3 text-gray-900"><?php echo e($item->product->name ?? '-'); ?></td>
                            <td class="px-4 py-3 text-right text-gray-700"><?php echo e(number_format($item->quantity_sent, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right text-green-500 font-medium"><?php echo e(number_format($item->quantity_sold, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right text-purple-500"><?php echo e(number_format($item->quantity_returned, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right font-semibold <?php echo e($item->remainingQty() > 0 ? 'text-amber-500' : 'text-gray-400'); ?>"><?php echo e(number_format($item->remainingQty(), 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right text-gray-500">Rp <?php echo e(number_format($item->cost_price, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right text-gray-900">Rp <?php echo e(number_format($item->retail_price, 0, ',', '.')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <?php if(in_array($ship->status, ['shipped', 'partial_sold'])): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'consignment', 'create')): ?>
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Lapor Penjualan</h3>
                <form method="POST" action="<?php echo e(route('consignment.sales-report.store', $ship)); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; ?>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Periode Mulai</label><input type="date" name="period_start" required class="<?php echo e($cls); ?>"></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Periode Akhir</label><input type="date" name="period_end" required value="<?php echo e(date('Y-m-d')); ?>" class="<?php echo e($cls); ?>"></div>
                    </div>
                    <?php $__currentLoopData = $ship->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($item->remainingQty() > 0): ?>
                    <div class="flex items-center gap-3 text-sm">
                        <input type="hidden" name="items[<?php echo e($loop->index); ?>][item_id]" value="<?php echo e($item->id); ?>">
                        <span class="flex-1 text-gray-700"><?php echo e($item->product->name ?? '-'); ?> <span class="text-xs text-gray-400">(sisa <?php echo e(number_format($item->remainingQty(), 0)); ?>)</span></span>
                        <input type="number" name="items[<?php echo e($loop->index); ?>][quantity_sold]" min="0" max="<?php echo e($item->remainingQty()); ?>" value="0" step="1"
                            class="w-24 px-2 py-1 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <button type="submit" class="w-full px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Simpan Laporan</button>
                </form>
            </div>
            <?php endif; ?>

            
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'consignment', 'edit')): ?>
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Retur Barang</h3>
                <form method="POST" action="<?php echo e(route('consignment.return', $ship)); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <?php $__currentLoopData = $ship->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($item->remainingQty() > 0): ?>
                    <div class="flex items-center gap-3 text-sm">
                        <input type="hidden" name="items[<?php echo e($loop->index); ?>][item_id]" value="<?php echo e($item->id); ?>">
                        <span class="flex-1 text-gray-700"><?php echo e($item->product->name ?? '-'); ?> <span class="text-xs text-gray-400">(sisa <?php echo e(number_format($item->remainingQty(), 0)); ?>)</span></span>
                        <input type="number" name="items[<?php echo e($loop->index); ?>][quantity_returned]" min="0" max="<?php echo e($item->remainingQty()); ?>" value="0" step="1"
                            class="w-24 px-2 py-1 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <button type="submit" class="w-full px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">Proses Retur</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        
        <?php if($ship->salesReports->isNotEmpty()): ?>
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Laporan Penjualan & Settlement</h3>
            </div>
            <div class="divide-y divide-gray-100">
                <?php $__currentLoopData = $ship->salesReports->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rpt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <span class="font-mono text-xs font-medium text-gray-900"><?php echo e($rpt->number); ?></span>
                            <span class="text-xs text-gray-500 ml-2"><?php echo e($rpt->period_start->format('d/m')); ?> — <?php echo e($rpt->period_end->format('d/m/Y')); ?></span>
                        </div>
                        <?php $rc = ['draft'=>'gray','confirmed'=>'blue','settled'=>'green'][$rpt->status] ?? 'gray'; ?>
                        <span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($rc); ?>-100 text-<?php echo e($rc); ?>-700 $rc }}-500/20 $rc }}-400"><?php echo e(ucfirst($rpt->status)); ?></span>
                    </div>
                    <div class="grid grid-cols-4 gap-3 text-xs mb-2">
                        <div><span class="text-gray-500">Penjualan:</span> <span class="text-gray-900">Rp <?php echo e(number_format($rpt->total_sales, 0, ',', '.')); ?></span></div>
                        <div><span class="text-gray-500">Komisi (<?php echo e($rpt->commission_pct); ?>%):</span> <span class="text-red-500">Rp <?php echo e(number_format($rpt->commission_amount, 0, ',', '.')); ?></span></div>
                        <div><span class="text-gray-500">Net:</span> <span class="font-semibold text-gray-900">Rp <?php echo e(number_format($rpt->net_receivable, 0, ',', '.')); ?></span></div>
                        <div><span class="text-gray-500">Sisa:</span> <span class="<?php echo e($rpt->remainingBalance() > 0 ? 'text-amber-500' : 'text-green-500'); ?>">Rp <?php echo e(number_format($rpt->remainingBalance(), 0, ',', '.')); ?></span></div>
                    </div>
                    <?php if($rpt->remainingBalance() > 0.01 && $rpt->status !== 'settled'): ?>
                    <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'consignment', 'create')): ?>
                    <form method="POST" action="<?php echo e(route('consignment.settlement.store', $rpt)); ?>" class="flex items-end gap-2 mt-2">
                        <?php echo csrf_field(); ?>
                        <input type="date" name="settlement_date" required value="<?php echo e(date('Y-m-d')); ?>" class="px-2 py-1 text-xs rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                        <input type="number" name="amount" required min="0.01" max="<?php echo e($rpt->remainingBalance()); ?>" step="0.01" value="<?php echo e($rpt->remainingBalance()); ?>" placeholder="Jumlah"
                            class="w-32 px-2 py-1 text-xs rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                        <select name="payment_method" class="px-2 py-1 text-xs rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                            <option value="transfer">Transfer</option><option value="cash">Cash</option>
                        </select>
                        <button type="submit" class="px-3 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700">Settle</button>
                    </form>
                    <?php endif; ?>
                    <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\consignment\show.blade.php ENDPATH**/ ?>