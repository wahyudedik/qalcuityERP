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
     <?php $__env->slot('header', null, []); ?> Inventori Farmasi <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Farmasi', 'url' => route('healthcare.pharmacy.prescriptions')],
        ['label' => 'Inventori'],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Farmasi', 'url' => route('healthcare.pharmacy.prescriptions')],
        ['label' => 'Inventori'],
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $attributes = $__attributesOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__attributesOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $component = $__componentOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__componentOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>

    <?php $tid = auth()->user()->tenant_id; ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
        <?php
            $totalItems = \App\Models\PharmacyItem::where('tenant_id', $tid)->count();
            $lowStock = \App\Models\PharmacyItem::where('tenant_id', $tid)
                ->whereColumn('current_stock', '<=', 'minimum_stock')
                ->count();
            $outOfStock = \App\Models\PharmacyItem::where('tenant_id', $tid)->where('current_stock', 0)->count();
            $expiringSoon = \App\Models\PharmacyItem::where('tenant_id', $tid)
                ->whereBetween('expiry_date', [now(), now()->addDays(30)])
                ->count();
            $expired = \App\Models\PharmacyItem::where('tenant_id', $tid)->where('expiry_date', '<', now())->count();
        ?>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Item</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($totalItems)); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Stok Menipis</p>
            <p class="text-2xl font-bold text-amber-600 mt-1"><?php echo e($lowStock); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Habis</p>
            <p class="text-2xl font-bold text-red-600 mt-1"><?php echo e($outOfStock); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Segera Kadaluarsa</p>
            <p class="text-2xl font-bold text-orange-600 mt-1"><?php echo e($expiringSoon); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Kadaluarsa</p>
            <p class="text-2xl font-bold text-red-600 mt-1"><?php echo e($expired); ?></p>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="p-4">
            <form method="GET" class="flex flex-col lg:flex-row gap-3">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                    placeholder="Cari obat / generic name..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="category"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Kategori</option>
                    <option value="tablet" <?php if(request('category') === 'tablet'): echo 'selected'; endif; ?>>Tablet</option>
                    <option value="capsule" <?php if(request('category') === 'capsule'): echo 'selected'; endif; ?>>Capsule</option>
                    <option value="syrup" <?php if(request('category') === 'syrup'): echo 'selected'; endif; ?>>Syrup</option>
                    <option value="injection" <?php if(request('category') === 'injection'): echo 'selected'; endif; ?>>Injection</option>
                    <option value="topical" <?php if(request('category') === 'topical'): echo 'selected'; endif; ?>>Topical</option>
                </select>
                <select name="stock_status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="available" <?php if(request('stock_status') === 'available'): echo 'selected'; endif; ?>>Available</option>
                    <option value="low" <?php if(request('stock_status') === 'low'): echo 'selected'; endif; ?>>Low Stock</option>
                    <option value="out" <?php if(request('stock_status') === 'out'): echo 'selected'; endif; ?>>Out of Stock</option>
                    <option value="expired" <?php if(request('stock_status') === 'expired'): echo 'selected'; endif; ?>>Expired</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
                <a href="<?php echo e(route('healthcare.pharmacy.inventory')); ?>"
                    class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 text-center">Reset</a>
            </form>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Nama Obat</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Generic Name</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Kategori</th>
                        <th class="px-4 py-3 text-center">Stok</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Harga</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Exp Date</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $items ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span
                                    class="font-mono text-xs text-gray-600"><?php echo e($item->item_code ?? '-'); ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900"><?php echo e($item->name); ?></p>
                                <p class="text-xs text-gray-500"><?php echo e($item->manufacturer ?? '-'); ?>

                                </p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">
                                <?php echo e($item->generic_name ?? '-'); ?></td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">
                                    <?php echo e(ucfirst($item->category ?? '-')); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-bold text-gray-900"><?php echo e($item->current_stock); ?></span>
                                <span class="text-xs text-gray-500"><?php echo e($item->unit); ?></span>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <span class="text-gray-900">Rp
                                    <?php echo e(number_format($item->unit_price, 0, ',', '.')); ?></span>
                            </td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell">
                                <?php if($item->expiry_date): ?>
                                    <?php
                                        $expiryDate = \Carbon\Carbon::parse($item->expiry_date);
                                        $daysUntilExpiry = $expiryDate->diffInDays(now(), false);
                                    ?>
                                    <span
                                        class="<?php echo e($daysUntilExpiry < 0 ? 'text-red-600' : ($daysUntilExpiry < 30 ? 'text-orange-600' : 'text-gray-600')); ?>">
                                        <?php echo e($expiryDate->format('d M Y')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if($item->current_stock == 0): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Out</span>
                                <?php elseif($item->current_stock <= $item->minimum_stock): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">Low</span>
                                <?php elseif($item->expiry_date && \Carbon\Carbon::parse($item->expiry_date)->isPast()): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Expired</span>
                                <?php else: ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Available</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?php echo e(route('healthcare.pharmacy.inventory.show', $item)); ?>"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
                                        title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                    <a href="<?php echo e(route('healthcare.pharmacy.inventory.edit', $item)); ?>"
                                        class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                <p>Belum ada data inventori</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <div class="md:hidden divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $items ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-mono text-xs text-gray-600">
                                <?php echo e($item->item_code ?? '-'); ?></p>
                            <p class="font-semibold text-gray-900 truncate mt-0.5"><?php echo e($item->name); ?>

                            </p>
                            <p class="text-xs text-gray-500"><?php echo e($item->manufacturer ?? '-'); ?></p>
                        </div>
                        <div class="text-right">
                            <?php if($item->current_stock == 0): ?>
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Out</span>
                            <?php elseif($item->current_stock <= $item->minimum_stock): ?>
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">Low</span>
                            <?php elseif($item->expiry_date && \Carbon\Carbon::parse($item->expiry_date)->isPast()): ?>
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Expired</span>
                            <?php else: ?>
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Available</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                        <div>
                            <p class="text-gray-500">Kategori</p>
                            <p class="font-medium text-gray-900"><?php echo e(ucfirst($item->category ?? '-')); ?>

                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500">Stok</p>
                            <p class="font-bold text-gray-900"><?php echo e($item->current_stock); ?>

                                <?php echo e($item->unit); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Harga</p>
                            <p class="font-medium text-gray-900">Rp
                                <?php echo e(number_format($item->unit_price, 0, ',', '.')); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Exp Date</p>
                            <?php if($item->expiry_date): ?>
                                <?php
                                    $expiryDate = \Carbon\Carbon::parse($item->expiry_date);
                                    $daysUntilExpiry = $expiryDate->diffInDays(now(), false);
                                ?>
                                <p
                                    class="font-medium <?php echo e($daysUntilExpiry < 0 ? 'text-red-600' : ($daysUntilExpiry < 30 ? 'text-orange-600' : 'text-gray-900')); ?>">
                                    <?php echo e($expiryDate->format('d M Y')); ?>

                                </p>
                            <?php else: ?>
                                <p class="font-medium text-gray-500">-</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                        <a href="<?php echo e(route('healthcare.pharmacy.inventory.show', $item)); ?>"
                            class="flex-1 px-3 py-2 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg text-center hover:bg-blue-100">
                            Detail
                        </a>
                        <a href="<?php echo e(route('healthcare.pharmacy.inventory.edit', $item)); ?>"
                            class="flex-1 px-3 py-2 text-xs font-medium text-green-600 bg-green-50 rounded-lg text-center hover:bg-green-100">
                            Edit
                        </a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <p>Belum ada data inventori</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if(isset($items) && $items->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-200">
                <?php echo e($items->links()); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\pharmacy\inventory.blade.php ENDPATH**/ ?>