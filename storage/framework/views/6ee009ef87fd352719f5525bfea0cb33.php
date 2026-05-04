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
     <?php $__env->slot('header', null, []); ?> Data Produk <?php $__env->endSlot(); ?>

    <?php $tid = auth()->user()->tenant_id; ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <?php
            $totalProducts = \App\Models\Product::where('tenant_id', $tid)->count();
            $activeProducts = \App\Models\Product::where('tenant_id', $tid)->where('is_active', true)->count();
            $totalStock = \App\Models\ProductStock::whereHas('product', fn($q) => $q->where('tenant_id', $tid))->sum(
                'quantity',
            );
        ?>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Produk</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e($totalProducts); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Produk Aktif</p>
            <p class="text-2xl font-bold text-green-600 mt-1"><?php echo e($activeProducts); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Stok</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e(number_format($totalStock)); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Stok Menipis</p>
            <p class="text-2xl font-bold text-red-600 mt-1"><?php echo e($lowCount); ?></p>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama / SKU..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="category"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Kategori</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cat); ?>" <?php if(request('category') === $cat): echo 'selected'; endif; ?>><?php echo e($cat); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="active" <?php if(request('status') === 'active'): echo 'selected'; endif; ?>>Aktif</option>
                    <option value="inactive" <?php if(request('status') === 'inactive'): echo 'selected'; endif; ?>>Nonaktif</option>
                    <option value="low" <?php if(request('status') === 'low'): echo 'selected'; endif; ?>>Stok Menipis</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
            </form>
            <div class="flex gap-2">
                <a href="<?php echo e(route('inventory.index')); ?>"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Inventori</a>
                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'products', 'create')): ?>
                <button onclick="document.getElementById('modal-add-product').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Produk</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-3 py-3 w-8">
                            <input type="checkbox" id="select-all-products"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" title="Pilih semua">
                        </th>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">SKU</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Kategori</th>
                        <th class="px-4 py-3 text-right">Harga Jual</th>
                        <th class="px-4 py-3 text-right">Harga Beli</th>
                        <th class="px-4 py-3 text-right hidden lg:table-cell">Stok</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php if(!is_object($product)): ?>
                            <?php continue; ?>
                        <?php endif; ?>
                        <?php
                            $totalStk = $product->productStocks->sum('quantity');
                            $isLow = $totalStk <= $product->stock_min;
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-3">
                                <input type="checkbox" name="product_ids[]" value="<?php echo e($product->id); ?>"
                                    class="product-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <?php if($product->image): ?>
                                        <img src="<?php echo e($product->image); ?>" alt="<?php echo e($product->name); ?>"
                                            class="w-9 h-9 rounded-xl object-cover shrink-0 border border-gray-200">
                                    <?php else: ?>
                                        <div
                                            class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo e($product->name); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo e($product->unit); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-500 font-mono text-xs">
                                <?php echo e($product->sku); ?></td>
                            <td class="px-4 py-3 hidden md:table-cell text-gray-500">
                                <?php echo e($product->category ?? '-'); ?></td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp
                                <?php echo e(number_format($product->price_sell, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right text-gray-500">Rp
                                <?php echo e(number_format($product->price_buy, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right hidden lg:table-cell">
                                <span
                                    class="font-semibold <?php echo e($isLow ? 'text-red-600' : 'text-gray-900'); ?>"><?php echo e($totalStk); ?></span>
                                <?php if($isLow): ?>
                                    <span class="ml-1 text-xs text-red-500">⚠</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs <?php echo e($product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>">
                                    <?php echo e($product->is_active ? 'Aktif' : 'Nonaktif'); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    
                                    <button onclick="printBarcode(<?php echo e($product->id); ?>)"
                                        class="p-1.5 rounded-lg text-green-600 hover:bg-green-50" title="Print Barcode">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </button>

                                    <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'products', 'edit')): ?>
                                    <button
                                        onclick="openEditProduct(<?php echo e($product->id); ?>, <?php echo \Illuminate\Support\Js::from($product->name)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($product->sku ?? '')->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($product->category ?? '')->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($product->unit)->toHtml() ?>, <?php echo e($product->price_sell); ?>, <?php echo e($product->price_buy); ?>, <?php echo e($product->stock_min); ?>, <?php echo e($product->is_active ? 'true' : 'false'); ?>, <?php echo \Illuminate\Support\Js::from($product->image ?? '')->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($product->description ?? '')->toHtml() ?>)"
                                        class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <?php endif; ?>

                                    <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'products', 'edit')): ?>
                                    <form method="POST" action="<?php echo e(route('products.toggle', $product)); ?>">
                                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                        <button type="submit"
                                            class="p-1.5 rounded-lg <?php echo e($product->is_active ? 'text-yellow-500 hover:bg-yellow-50' : 'text-green-500 hover:bg-green-50'); ?>"
                                            title="<?php echo e($product->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'products', 'delete')): ?>
                                    <form method="POST" action="<?php echo e(route('products.destroy', $product)); ?>"
                                        onsubmit="return confirm('Hapus produk ini?')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50"
                                            title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400">Belum
                                ada produk. Klik "+ Produk" untuk menambahkan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($products->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-100"><?php echo e($products->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div class="md:hidden">
        <div class="space-y-3">
            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $totalStk = $product->productStocks->sum('quantity');
                    $isLow = $totalStk <= $product->stock_min;
                ?>
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                    
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <?php if($product->image): ?>
                                    <img src="<?php echo e($product->image); ?>" alt="<?php echo e($product->name); ?>"
                                        class="w-10 h-10 rounded-xl object-cover shrink-0 border border-gray-200">
                                <?php else: ?>
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base font-semibold text-gray-900 truncate">
                                        <?php echo e($product->name); ?></h3>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        <?php echo e($product->category ?? 'Tanpa Kategori'); ?></p>
                                </div>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-medium <?php echo e($product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>">
                                <?php echo e($product->is_active ? 'Aktif' : 'Nonaktif'); ?>

                            </span>
                        </div>
                    </div>

                    
                    <div class="px-4 py-3 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">SKU</span>
                            <span class="text-sm font-mono text-gray-900"><?php echo e($product->sku ?? '-'); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Harga Jual</span>
                            <span class="text-sm font-semibold text-green-600">Rp
                                <?php echo e(number_format($product->price_sell, 0, ',', '.')); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Harga Beli</span>
                            <span class="text-sm text-gray-900">Rp
                                <?php echo e(number_format($product->price_buy, 0, ',', '.')); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Stok</span>
                            <span class="text-sm font-semibold <?php echo e($isLow ? 'text-red-600' : 'text-gray-900'); ?>">
                                <?php echo e($totalStk); ?> <?php echo e($product->unit); ?>

                                <?php if($isLow): ?>
                                    <span class="text-xs">(Menipis)</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    
                    <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="printBarcode(<?php echo e($product->id); ?>)"
                                class="p-2.5 rounded-lg text-green-600 hover:bg-green-50 min-w-[44px] min-h-[44px] flex items-center justify-center"
                                title="Print Barcode">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                            </button>
                            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'products', 'edit')): ?>
                            <button
                                onclick="openEditProduct(<?php echo e($product->id); ?>, <?php echo \Illuminate\Support\Js::from($product->name)->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($product->sku ?? '')->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($product->category ?? '')->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($product->unit)->toHtml() ?>, <?php echo e($product->price_sell); ?>, <?php echo e($product->price_buy); ?>, <?php echo e($product->stock_min); ?>, <?php echo e($product->is_active ? 'true' : 'false'); ?>, <?php echo \Illuminate\Support\Js::from($product->image ?? '')->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($product->description ?? '')->toHtml() ?>)"
                                class="p-2.5 rounded-lg text-gray-500 hover:bg-gray-100 min-w-[44px] min-h-[44px] flex items-center justify-center"
                                title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <?php endif; ?>
                            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'products', 'delete')): ?>
                            <form method="POST" action="<?php echo e(route('products.destroy', $product)); ?>"
                                onsubmit="return confirm('Hapus produk ini?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit"
                                    class="p-2.5 rounded-lg text-red-500 hover:bg-red-50 min-w-[44px] min-h-[44px] flex items-center justify-center"
                                    title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php if (isset($component)) { $__componentOriginaled75e76121cf44ad18b999cf45dd9612 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaled75e76121cf44ad18b999cf45dd9612 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile-empty-state','data' => ['title' => 'Belum ada produk','description' => 'Klik tombol \'+ Produk\' untuk menambahkan produk pertama Anda','icon' => 'package','actionUrl' => ''.e(route('products.create')).'','actionText' => 'Tambah Produk']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile-empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Belum ada produk','description' => 'Klik tombol \'+ Produk\' untuk menambahkan produk pertama Anda','icon' => 'package','action-url' => ''.e(route('products.create')).'','action-text' => 'Tambah Produk']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaled75e76121cf44ad18b999cf45dd9612)): ?>
<?php $attributes = $__attributesOriginaled75e76121cf44ad18b999cf45dd9612; ?>
<?php unset($__attributesOriginaled75e76121cf44ad18b999cf45dd9612); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaled75e76121cf44ad18b999cf45dd9612)): ?>
<?php $component = $__componentOriginaled75e76121cf44ad18b999cf45dd9612; ?>
<?php unset($__componentOriginaled75e76121cf44ad18b999cf45dd9612); ?>
<?php endif; ?>
            <?php endif; ?>
        </div>

        
        <?php if($products->hasPages()): ?>
            <div class="mt-4">
                <?php if (isset($component)) { $__componentOriginalbb6fb69940a738690f438442b338aa0a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbb6fb69940a738690f438442b338aa0a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile-pagination','data' => ['paginator' => $products]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($products)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbb6fb69940a738690f438442b338aa0a)): ?>
<?php $attributes = $__attributesOriginalbb6fb69940a738690f438442b338aa0a; ?>
<?php unset($__attributesOriginalbb6fb69940a738690f438442b338aa0a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbb6fb69940a738690f438442b338aa0a)): ?>
<?php $component = $__componentOriginalbb6fb69940a738690f438442b338aa0a; ?>
<?php unset($__componentOriginalbb6fb69940a738690f438442b338aa0a); ?>
<?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    
    <div id="modal-add-product" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
                <h3 class="font-semibold text-gray-900">Tambah Produk</h3>
                <button onclick="document.getElementById('modal-add-product').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('products.store')); ?>" enctype="multipart/form-data"
                class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Foto
                            Produk</label>
                        <div class="flex items-center gap-4">
                            <div id="add-img-preview"
                                class="w-16 h-16 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden shrink-0">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <label
                                class="cursor-pointer px-3 py-2 text-xs border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                                Pilih Gambar
                                <input type="file" name="image" accept="image/*" class="hidden"
                                    onchange="previewImage(this,'add-img-preview')">
                            </label>
                            <span class="text-xs text-gray-400">JPG, PNG, WebP. Maks 2MB</span>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Produk
                            *</label>
                        <input type="text" name="name" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">SKU
                            (opsional)</label>
                        <input type="text" name="sku"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
                        <input type="text" name="category" list="cat-list-add"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <datalist id="cat-list-add">
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($c); ?>">
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Satuan
                            *</label>
                        <input type="text" name="unit" value="pcs" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Harga Jual
                            *</label>
                        <input type="number" name="price_sell" min="0" step="100" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Harga
                            Beli</label>
                        <input type="number" name="price_buy" min="0" step="100"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Stok
                            Minimum</label>
                        <input type="number" name="stock_min" value="5" min="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Stok
                            Awal</label>
                        <input type="number" name="initial_stock" min="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Gudang (untuk
                            stok awal)</label>
                        <select name="warehouse_id"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Gudang --</option>
                            <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($wh->id); ?>"><?php echo e($wh->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                        <textarea name="description" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button"
                        onclick="document.getElementById('modal-add-product').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-edit-product" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
                <h3 class="font-semibold text-gray-900">Edit Produk</h3>
                <button onclick="document.getElementById('modal-edit-product').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-edit-product" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Foto
                            Produk</label>
                        <div class="flex items-center gap-4">
                            <div id="edit-img-preview"
                                class="w-16 h-16 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden shrink-0">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <label
                                class="cursor-pointer px-3 py-2 text-xs border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                                Ganti Gambar
                                <input type="file" name="image" accept="image/*" class="hidden"
                                    onchange="previewImage(this,'edit-img-preview')">
                            </label>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Produk
                            *</label>
                        <input type="text" id="edit-name" name="name" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">SKU</label>
                        <input type="text" id="edit-sku" name="sku"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
                        <input type="text" id="edit-category" name="category" list="cat-list-edit"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <datalist id="cat-list-edit">
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($c); ?>">
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Satuan
                            *</label>
                        <input type="text" id="edit-unit" name="unit" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Harga Jual
                            *</label>
                        <input type="number" id="edit-price-sell" name="price_sell" min="0" step="100"
                            required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Harga
                            Beli</label>
                        <input type="number" id="edit-price-buy" name="price_buy" min="0" step="100"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Stok
                            Minimum</label>
                        <input type="number" id="edit-stock-min" name="stock_min" min="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2 flex items-center gap-2">
                        <input type="checkbox" id="edit-is-active" name="is_active" value="1" class="rounded">
                        <label for="edit-is-active" class="text-sm text-gray-700">Produk
                            Aktif</label>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                        <textarea id="edit-description" name="description" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button"
                        onclick="document.getElementById('modal-edit-product').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function previewImage(input, previewId) {
                const preview = document.getElementById(previewId);
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">';
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function openEditProduct(id, name, sku, category, unit, priceSell, priceBuy, stockMin, isActive, image,
                description) {
                const form = document.getElementById('form-edit-product');
                form.action = '/products/' + id;
                document.getElementById('edit-name').value = name;
                document.getElementById('edit-sku').value = sku;
                document.getElementById('edit-category').value = category;
                document.getElementById('edit-unit').value = unit;
                document.getElementById('edit-price-sell').value = priceSell;
                document.getElementById('edit-price-buy').value = priceBuy;
                document.getElementById('edit-stock-min').value = stockMin;
                document.getElementById('edit-is-active').checked = isActive;
                document.getElementById('edit-description').value = description;

                const preview = document.getElementById('edit-img-preview');
                if (image) {
                    preview.innerHTML = '<img src="' + image + '" class="w-full h-full object-cover">';
                } else {
                    preview.innerHTML =
                        '<svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
                }

                document.getElementById('modal-edit-product').classList.remove('hidden');
            }

            // Barcode printing functions
            function printBarcode(productId) {
                window.open(`/barcode/products/${productId}`, '_blank');
            }

            function openBatchPrint() {
                const checkboxes = document.querySelectorAll('input[name="product_ids[]"]:checked');
                const ids = Array.from(checkboxes).map(cb => cb.value);

                if (ids.length === 0) {
                    alert('Pilih minimal 1 produk!');
                    return;
                }

                document.getElementById('batch-product-ids').value = ids.join(',');
                document.getElementById('modal-batch-print').classList.remove('hidden');
            }

            function closeBatchPrint() {
                document.getElementById('modal-batch-print').classList.add('hidden');
            }

            // Add checkbox functionality to table
            document.addEventListener('DOMContentLoaded', function() {
                // Select All checkbox
                const selectAll = document.getElementById('select-all-products');
                if (selectAll) {
                    selectAll.addEventListener('change', function() {
                        document.querySelectorAll('.product-checkbox')
                            .forEach(cb => cb.checked = this.checked);
                    });
                }

                // Sync select-all state when individual checkboxes change
                document.querySelectorAll('.product-checkbox').forEach(cb => {
                    cb.addEventListener('change', function() {
                        const all = document.querySelectorAll('.product-checkbox');
                        const checked = document.querySelectorAll('.product-checkbox:checked');
                        if (selectAll) selectAll.checked = all.length === checked.length;
                    });
                });
            });
        </script>
    <?php $__env->stopPush(); ?>

    
    <div id="modal-batch-print" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full border border-gray-200 shadow-2xl">
            <h3 class="text-lg font-bold mb-4 text-gray-900">
                🖨️ Print Barcode Labels
            </h3>

            <form action="<?php echo e(route('barcode.print')); ?>" method="POST" target="_blank">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="product_ids" id="batch-product-ids">

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2 text-gray-700">
                        Label Template
                    </label>
                    <select name="template"
                        class="w-full px-3 py-2 border border-gray-200 rounded-xl bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="thermal">Thermal Printer (50×25mm) - Recommended</option>
                        <option value="avery">Avery A4 Sheet (21 labels)</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="button" onclick="closeBatchPrint()"
                        class="flex-1 px-4 py-2 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium transition">
                        Print Labels
                    </button>
                </div>
            </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/products/index.blade.php ENDPATH**/ ?>