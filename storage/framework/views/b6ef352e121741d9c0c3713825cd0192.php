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
        <div class="flex justify-between items-center">
            <span>Material Requirement Planning (MRP)</span>
            <div class="flex gap-2">
                <a href="<?php echo e(route('manufacturing.mrp.accuracy')); ?>"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    📊 Accuracy Dashboard
                </a>
                <?php if(isset($planningReport) && $planningReport['status'] === 'success'): ?>
                    <form method="POST" action="<?php echo e(route('manufacturing.mrp.export-pdf')); ?>" target="_blank"
                        class="inline">
                        <?php echo csrf_field(); ?>
                        <?php if(request('bom_id')): ?>
                            <input type="hidden" name="bom_id" value="<?php echo e(request('bom_id')); ?>">
                        <?php endif; ?>
                        <input type="hidden" name="quantity" value="<?php echo e($quantity ?? 1); ?>">
                        <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                            📄 Export PDF Report
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    
    <?php if(isset($dashboardData)): ?>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <div class="text-sm text-gray-500 dark:text-slate-400">Work Orders Aktif</div>
                <div class="text-2xl font-bold text-blue-600"><?php echo e($dashboardData['pending_work_orders']); ?></div>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <div class="text-sm text-gray-500 dark:text-slate-400">PO Pending</div>
                <div class="text-2xl font-bold text-purple-600"><?php echo e($dashboardData['pending_purchase_orders']); ?></div>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <div class="text-sm text-gray-500 dark:text-slate-400">Stok Rendah</div>
                <div class="text-2xl font-bold text-orange-600"><?php echo e($dashboardData['low_stock_items']); ?></div>
            </div>
            <?php if(isset($dashboardData['planning']['summary'])): ?>
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                    <div class="text-sm text-gray-500 dark:text-slate-400">MRP Health</div>
                    <div
                        class="text-2xl font-bold <?php echo e($dashboardData['planning']['summary']['health_percentage'] >= 80 ? 'text-green-600' : 'text-red-600'); ?>">
                        <?php echo e($dashboardData['planning']['summary']['health_percentage']); ?>%
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    
    <?php if(isset($planningReport) && $planningReport['status'] === 'success'): ?>
        <div
            class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-2xl border border-blue-200 dark:border-blue-800 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white text-lg">📊 Planning Report Summary</h3>
                <span class="text-xs text-gray-500 dark:text-slate-400">Generated:
                    <?php echo e(\Carbon\Carbon::parse($planningReport['generated_at'])->format('d M Y H:i')); ?></span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
                <div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Total Items</div>
                    <div class="text-xl font-bold"><?php echo e($planningReport['summary']['total_items']); ?></div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Shortage</div>
                    <div class="text-xl font-bold text-red-600"><?php echo e($planningReport['summary']['items_with_shortage']); ?>

                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Critical</div>
                    <div class="text-xl font-bold text-red-700"><?php echo e($planningReport['summary']['critical_items']); ?>

                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">High Priority</div>
                    <div class="text-xl font-bold text-orange-600">
                        <?php echo e($planningReport['summary']['high_priority_items']); ?></div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Est. Shortage Value</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">Rp
                        <?php echo e(number_format($planningReport['summary']['estimated_shortage_value'], 0, ',', '.')); ?></div>
                </div>
            </div>

            <?php if($planningReport['summary']['critical_items'] > 0): ?>
                <div class="bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-lg p-3">
                    <span class="text-sm text-red-800 dark:text-red-200">⚠️ Ada
                        <?php echo e($planningReport['summary']['critical_items']); ?> item critical yang perlu segera
                        diorder!</span>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Kalkulasi Kebutuhan Material</h3>
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <select name="bom_id"
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <option value="">-- Pilih BOM --</option>
                <?php $__currentLoopData = $boms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($b->id); ?>" <?php if(request('bom_id') == $b->id): echo 'selected'; endif; ?>><?php echo e($b->name); ?>

                        (<?php echo e($b->product->name ?? '-'); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input type="number" name="quantity" min="1" step="1" value="<?php echo e($quantity); ?>"
                placeholder="Jumlah produksi"
                class="w-32 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Hitung</button>
            <button type="submit" name="full_mrp" value="1"
                class="px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">Full MRP (Semua
                WO)</button>
        </form>
    </div>

    
    <?php if($results !== null): ?>
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    Kebutuhan: <?php echo e($selectedBom->name ?? '-'); ?> × <?php echo e(number_format($quantity, 0, ',', '.')); ?>

                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Material</th>
                            <th class="px-4 py-3 text-right">Dibutuhkan</th>
                            <th class="px-4 py-3 text-right">Stok</th>
                            <th class="px-4 py-3 text-right">PO Pending</th>
                            <th class="px-4 py-3 text-right">Demand WO Lain</th>
                            <th class="px-4 py-3 text-right">Tersedia</th>
                            <th class="px-4 py-3 text-right">Kekurangan</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3 text-gray-900 dark:text-white">
                                    <?php if($r['level'] > 0): ?>
                                        <span class="text-gray-400"><?php echo e(str_repeat('└─ ', $r['level'])); ?></span>
                                    <?php endif; ?>
                                    <?php echo e($r['product_name']); ?>

                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                    <?php echo e(number_format($r['required'], 2, ',', '.')); ?> <?php echo e($r['unit']); ?></td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">
                                    <?php echo e(number_format($r['on_hand'], 2, ',', '.')); ?></td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">
                                    <?php echo e(number_format($r['on_order'], 2, ',', '.')); ?></td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">
                                    <?php echo e(number_format($r['other_demand'], 2, ',', '.')); ?></td>
                                <td class="px-4 py-3 text-right text-gray-900 dark:text-white">
                                    <?php echo e(number_format($r['available'], 2, ',', '.')); ?></td>
                                <td
                                    class="px-4 py-3 text-right font-bold <?php echo e($r['shortage'] > 0 ? 'text-red-500' : 'text-green-500'); ?>">
                                    <?php echo e($r['shortage'] > 0 ? number_format($r['shortage'], 2, ',', '.') : '—'); ?>

                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if($r['shortage'] > 0): ?>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">Kurang</span>
                                    <?php else: ?>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Cukup</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php $totalShortage = collect($results)->sum('shortage'); ?>
            <div class="px-6 py-3 border-t border-gray-100 dark:border-white/10 flex items-center gap-4">
                <?php if($totalShortage > 0): ?>
                    <span class="text-sm text-red-500">⚠️ Ada
                        <?php echo e(collect($results)->where('shortage', '>', 0)->count()); ?> material yang kurang stok.</span>
                <?php else: ?>
                    <span class="text-sm text-green-500">✅ Semua material tersedia untuk produksi.</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if($fullMrp !== null): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Full MRP — Semua Work Order Aktif</h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Agregasi kebutuhan material dari semua WO
                    pending/in-progress yang memiliki BOM</p>
            </div>
            <?php if(count($fullMrp) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Material</th>
                                <th class="px-4 py-3 text-right">Total Dibutuhkan</th>
                                <th class="px-4 py-3 text-right">Stok</th>
                                <th class="px-4 py-3 text-right">PO Pending</th>
                                <th class="px-4 py-3 text-right">Tersedia</th>
                                <th class="px-4 py-3 text-right">Kekurangan</th>
                                <th class="px-4 py-3 text-left">Work Order</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <?php $__currentLoopData = $fullMrp; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr
                                    class="hover:bg-gray-50 dark:hover:bg-white/5 <?php echo e($r['shortage'] > 0 ? 'bg-red-50/50 dark:bg-red-500/5' : ''); ?>">
                                    <td class="px-4 py-3 text-gray-900 dark:text-white"><?php echo e($r['product_name']); ?></td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                        <?php echo e(number_format($r['required'], 2, ',', '.')); ?> <?php echo e($r['unit']); ?></td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">
                                        <?php echo e(number_format($r['on_hand'], 2, ',', '.')); ?></td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">
                                        <?php echo e(number_format($r['on_order'], 2, ',', '.')); ?></td>
                                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white">
                                        <?php echo e(number_format($r['available'], 2, ',', '.')); ?></td>
                                    <td
                                        class="px-4 py-3 text-right font-bold <?php echo e($r['shortage'] > 0 ? 'text-red-500' : 'text-green-500'); ?>">
                                        <?php echo e($r['shortage'] > 0 ? number_format($r['shortage'], 2, ',', '.') : '—'); ?>

                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400">
                                        <?php echo e(implode(', ', array_slice($r['wo_refs'], 0, 3))); ?>

                                        <?php if(count($r['wo_refs']) > 3): ?>
                                            <span class="text-gray-400">+<?php echo e(count($r['wo_refs']) - 3); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if($r['shortage'] > 0): ?>
                                            <span
                                                class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">Kurang</span>
                                        <?php else: ?>
                                            <span
                                                class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Cukup</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php $shortageCount = collect($fullMrp)->where('shortage', '>', 0)->count(); ?>
                <div class="px-6 py-3 border-t border-gray-100 dark:border-white/10">
                    <?php if($shortageCount > 0): ?>
                        <span class="text-sm text-red-500">⚠️ <?php echo e($shortageCount); ?> material kekurangan stok. Buat
                            Purchase Order untuk memenuhi kebutuhan.</span>
                    <?php else: ?>
                        <span class="text-sm text-green-500">✅ Semua material tersedia untuk seluruh Work Order
                            aktif.</span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="px-6 py-12 text-center text-gray-400 dark:text-slate-500">
                    Tidak ada Work Order aktif yang memiliki BOM. Buat WO dengan BOM terlebih dahulu.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    
    <?php if(isset($planningReport) && $planningReport['status'] === 'success' && count($planningReport['items']) > 0): ?>
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">📋 Planning Recommendations</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Prioritas berdasarkan shortage, lead
                        time, dan quantity</p>
                </div>
                <div class="flex gap-2">
                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Critical</span>
                    <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs">High</span>
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs">Medium</span>
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Low</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Priority</th>
                            <th class="px-4 py-3 text-left">Material</th>
                            <th class="px-4 py-3 text-right">Shortage</th>
                            <th class="px-4 py-3 text-center">Lead Time</th>
                            <th class="px-4 py-3 text-center">Order By</th>
                            <th class="px-4 py-3 text-left">Supplier</th>
                            <th class="px-4 py-3 text-center">Action</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $planningReport['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($item['has_shortage']): ?>
                                <tr
                                    class="hover:bg-gray-50 dark:hover:bg-white/5 <?php echo e($item['action']['urgency'] === 'critical' ? 'bg-red-50/50 dark:bg-red-500/5' : ''); ?>">
                                    <td class="px-4 py-3 text-center">
                                        <div
                                            class="font-bold <?php echo e($item['priority'] >= 70 ? 'text-red-600' : ($item['priority'] >= 50 ? 'text-orange-600' : 'text-yellow-600')); ?>">
                                            <?php echo e($item['priority']); ?>

                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">
                                        <?php echo e($item['product_name']); ?></td>
                                    <td class="px-4 py-3 text-right font-bold text-red-600">
                                        <?php echo e(number_format($item['shortage'], 2, ',', '.')); ?> <?php echo e($item['unit']); ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded text-xs">
                                            <?php echo e($item['lead_time_days']); ?> days
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs text-gray-700 dark:text-slate-300">
                                        <?php echo e($item['order_by_date_formatted']); ?></td>
                                    <td class="px-4 py-3 text-xs text-gray-600 dark:text-slate-400">
                                        <?php if($item['supplier_info']): ?>
                                            <div class="font-medium"><?php echo e($item['supplier_info']['name']); ?></div>
                                            <?php if($item['supplier_info']['phone']): ?>
                                                <div class="text-gray-400"><?php echo e($item['supplier_info']['phone']); ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">No history</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if($item['action']['type'] === 'purchase_recommended'): ?>
                                            <button
                                                onclick="createAutoPO(<?php echo e($item['product_id']); ?>, <?php echo e($item['shortage']); ?>, '<?php echo e($item['product_name']); ?>')"
                                                class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs">
                                                Create PO
                                            </button>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if($item['action']['urgency'] === 'critical'): ?>
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400 font-bold">CRITICAL</span>
                                        <?php elseif($item['action']['urgency'] === 'high'): ?>
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400">HIGH</span>
                                        <?php elseif($item['action']['urgency'] === 'medium'): ?>
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400">MEDIUM</span>
                                        <?php else: ?>
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">LOW</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    
    <dialog id="autoPOModal" class="modal">
        <div class="modal-box max-w-lg">
            <h3 class="font-bold text-lg mb-4">Create Purchase Order</h3>
            <form id="autoPOForm" method="POST" action="<?php echo e(route('manufacturing.mrp.create-po')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" id="po_product_id" name="product_id">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Product</label>
                        <input type="text" id="po_product_name" readonly
                            class="w-full border rounded px-3 py-2 bg-gray-100 dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Quantity *</label>
                        <input type="number" id="po_quantity" name="quantity" step="0.01" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Supplier</label>
                        <select name="supplier_id" class="w-full border rounded px-3 py-2">
                            <option value="">-- Select Supplier --</option>
                            <?php
                                $suppliers = \App\Models\Supplier::where('tenant_id', auth()->user()->tenant_id)
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->get();
                            ?>
                            <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Expected Date</label>
                        <input type="date" name="expected_date" value="<?php echo e(now()->addDays(7)->format('Y-m-d')); ?>"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2"
                            placeholder="Auto-generated from MRP Planning"></textarea>
                    </div>
                </div>

                <div class="modal-action">
                    <button type="button" onclick="document.getElementById('autoPOModal').close()"
                        class="btn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create PO</button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        function createAutoPO(productId, quantity, productName) {
            document.getElementById('po_product_id').value = productId;
            document.getElementById('po_quantity').value = Math.ceil(quantity);
            document.getElementById('po_product_name').value = productName;
            document.getElementById('autoPOModal').showModal();
        }
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\manufacturing\mrp.blade.php ENDPATH**/ ?>