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
     <?php $__env->slot('header', null, []); ?> Inventori & Produk <?php $__env->endSlot(); ?>

    <?php $tid = auth()->user()->tenant_id; ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <?php
            $totalProducts = \App\Models\Product::where('tenant_id',$tid)->count();
            $activeProducts = \App\Models\Product::where('tenant_id',$tid)->where('is_active',true)->count();
            $totalStock = \App\Models\ProductStock::whereHas('product',fn($q)=>$q->where('tenant_id',$tid))->sum('quantity');
        ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Produk</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e($totalProducts); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Produk Aktif</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1"><?php echo e($activeProducts); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Stok</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1"><?php echo e(number_format($totalStock)); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Stok Menipis</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1"><?php echo e($lowCount); ?></p>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama / SKU..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="category" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Kategori</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cat); ?>" <?php if(request('category')===$cat): echo 'selected'; endif; ?>><?php echo e($cat); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="active" <?php if(request('status')==='active'): echo 'selected'; endif; ?>>Aktif</option>
                    <option value="inactive" <?php if(request('status')==='inactive'): echo 'selected'; endif; ?>>Nonaktif</option>
                    <option value="low" <?php if(request('status')==='low'): echo 'selected'; endif; ?>>Stok Menipis</option>
                </select>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
            </form>
            <div class="flex gap-2">
                <a href="<?php echo e(route('inventory.movements')); ?>" class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Riwayat Stok</a>
                <a href="<?php echo e(route('inventory.warehouses')); ?>" class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Gudang</a>
                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'inventory', 'create')): ?>
                <button onclick="document.getElementById('modal-add-product').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Produk</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">SKU</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Kategori</th>
                        <th class="px-4 py-3 text-right">Harga Jual</th>
                        <th class="px-4 py-3 text-right">Stok</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Prediksi AI</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $totalStock = $product->productStocks->sum('quantity'); $isLow = $totalStock <= $product->stock_min; ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white"><?php echo e($product->name); ?></p>
                            <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($product->unit); ?></p>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500 dark:text-slate-400 font-mono text-xs"><?php echo e($product->sku); ?></td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500 dark:text-slate-400"><?php echo e($product->category ?? '-'); ?></td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp <?php echo e(number_format($product->price_sell,0,',','.')); ?></td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-semibold <?php echo e($isLow ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'); ?>"><?php echo e($totalStock); ?></span>
                            <?php if($isLow): ?><span class="ml-1 text-xs text-red-500">⚠</span><?php endif; ?>
                        </td>
                        
                        <td class="px-4 py-3 text-center hidden lg:table-cell">
                            <div id="ai-inv-<?php echo e($product->id); ?>" class="text-xs text-slate-500 italic">—</div>
                        </td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell">
                            <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($product->is_active ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400'); ?>">
                                <?php echo e($product->is_active ? 'Aktif' : 'Nonaktif'); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'inventory', 'create')): ?>
                                <button onclick="openAddStock(<?php echo e($product->id); ?>, '<?php echo e(addslashes($product->name)); ?>', '<?php echo e($product->unit); ?>')"
                                    class="p-1.5 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10" title="Tambah Stok">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                                <?php endif; ?>
                                <button onclick="openAiDetail(<?php echo e($product->id); ?>, '<?php echo e(addslashes($product->name)); ?>', '<?php echo e($product->unit); ?>')"
                                    class="p-1.5 rounded-lg text-purple-500 hover:bg-purple-50 dark:hover:bg-purple-500/10" title="Analisis AI">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.364.364A4.004 4.004 0 0112 16a4.004 4.004 0 01-2.772-1.1l-.364-.364z"/></svg>
                                </button>
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'inventory', 'edit')): ?>
                                <button onclick="openEditProduct(<?php echo e($product->id); ?>, '<?php echo e(addslashes($product->name)); ?>', '<?php echo e($product->sku); ?>', '<?php echo e(addslashes($product->category ?? '')); ?>', '<?php echo e($product->unit); ?>', <?php echo e($product->price_sell); ?>, <?php echo e($product->price_buy); ?>, <?php echo e($product->stock_min); ?>, <?php echo e($product->is_active ? 'true' : 'false'); ?>, '<?php echo e($product->image); ?>')"
                                    class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <?php endif; ?>
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'inventory', 'delete')): ?>
                                <form method="POST" action="<?php echo e(route('inventory.destroy', $product)); ?>" onsubmit="return confirm('Hapus produk ini?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada produk. Tambahkan produk pertama Anda.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($products->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($products->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-add-product" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Produk</h3>
                <button onclick="document.getElementById('modal-add-product').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('inventory.store')); ?>" enctype="multipart/form-data" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Foto Produk</label>
                        <div class="flex items-center gap-4">
                            <div id="add-img-preview" class="w-16 h-16 rounded-xl bg-gray-100 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 flex items-center justify-center overflow-hidden shrink-0">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <label class="cursor-pointer px-3 py-2 text-xs border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                                Pilih Gambar
                                <input type="file" name="image" accept="image/*" class="hidden" onchange="previewImage(this, 'add-img-preview')">
                            </label>
                            <span class="text-xs text-gray-400">JPG, PNG, WebP. Maks 2MB</span>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Produk *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">SKU (opsional)</label>
                        <input type="text" name="sku" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kategori</label>
                        <input type="text" name="category" list="cat-list" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <datalist id="cat-list"><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c); ?>"><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></datalist>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Satuan *</label>
                        <input type="text" name="unit" value="pcs" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Harga Jual *</label>
                        <input type="number" name="price_sell" min="0" step="100" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Harga Beli</label>
                        <input type="number" name="price_buy" min="0" step="100" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Stok Minimum</label>
                        <input type="number" name="stock_min" value="5" min="0" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Stok Awal</label>
                        <input type="number" name="initial_stock" min="0" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Gudang (untuk stok awal)</label>
                        <select name="warehouse_id" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Gudang --</option>
                            <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($wh->id); ?>"><?php echo e($wh->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-product').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-edit-product" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Edit Produk</h3>
                <button onclick="document.getElementById('modal-edit-product').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-edit-product" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Foto Produk</label>
                        <div class="flex items-center gap-4">
                            <div id="edit-img-preview" class="w-16 h-16 rounded-xl bg-gray-100 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 flex items-center justify-center overflow-hidden shrink-0">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <label class="cursor-pointer px-3 py-2 text-xs border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                                Ganti Gambar
                                <input type="file" name="image" accept="image/*" class="hidden" onchange="previewImage(this, 'edit-img-preview')">
                            </label>
                            <span class="text-xs text-gray-400">JPG, PNG, WebP. Maks 2MB</span>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Produk *</label>
                        <input type="text" id="edit-name" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kategori</label>
                        <input type="text" id="edit-category" name="category" list="cat-list" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Satuan *</label>
                        <input type="text" id="edit-unit" name="unit" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Harga Jual *</label>
                        <input type="number" id="edit-price-sell" name="price_sell" min="0" step="100" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Harga Beli</label>
                        <input type="number" id="edit-price-buy" name="price_buy" min="0" step="100" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Stok Minimum</label>
                        <input type="number" id="edit-stock-min" name="stock_min" min="0" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2 flex items-center gap-2">
                        <input type="checkbox" id="edit-is-active" name="is_active" value="1" class="rounded">
                        <label for="edit-is-active" class="text-sm text-gray-700 dark:text-slate-300">Produk Aktif</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit-product').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-add-stock" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Stok</h3>
                <button onclick="document.getElementById('modal-add-stock').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-add-stock" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <p id="stock-product-name" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Gudang *</label>
                    <select name="warehouse_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($wh->id); ?>"><?php echo e($wh->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah *</label>
                    <input type="number" name="quantity" min="1" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                    <input type="text" name="notes" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-stock').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-ai-inventory" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 id="ai-modal-title" class="font-semibold text-gray-900 dark:text-white text-sm">Analisis AI Stok</h3>
                <button onclick="document.getElementById('modal-ai-inventory').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <div id="ai-modal-content" class="p-6 space-y-4"></div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    // ── Toast Notification ────────────────────────────────────────
    function showToast(message, type = 'success') {
        const colors = {
            success: 'bg-green-600',
            error:   'bg-red-600',
            warning: 'bg-yellow-500',
            info:    'bg-blue-600',
        };
        const icons = {
            success: '✓',
            error:   '✕',
            warning: '⚠',
            info:    'ℹ',
        };
        const toast = document.createElement('div');
        toast.className = `fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-4 py-3 rounded-2xl text-white text-sm font-medium shadow-xl transition-all duration-300 translate-y-4 opacity-0 ${colors[type] || colors.success}`;
        toast.innerHTML = `<span class="text-base">${icons[type] || icons.success}</span><span>${message}</span>`;
        document.body.appendChild(toast);
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-4', 'opacity-0');
        });
        setTimeout(() => {
            toast.classList.add('translate-y-4', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // Auto-show flash messages as toast
    <?php if(session('success')): ?>
        showToast(<?php echo json_encode(session('success'), 15, 512) ?>, 'success');
    <?php endif; ?>
    <?php if(session('error')): ?>
        showToast(<?php echo json_encode(session('error'), 15, 512) ?>, 'error');
    <?php endif; ?>
    <?php if($errors->any()): ?>
        showToast(<?php echo json_encode($errors->first(), 15, 512) ?>, 'error');
    <?php endif; ?>

    // ── Image Preview ─────────────────────────────────────────────
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded-xl">`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function openAddStock(id, name, unit) {
        document.getElementById('stock-product-name').textContent = name + ' (' + unit + ')';
        document.getElementById('form-add-stock').action = '<?php echo e(url("inventory")); ?>/' + id + '/stock';
        document.getElementById('modal-add-stock').classList.remove('hidden');
    }

    function openEditProduct(id, name, sku, category, unit, priceSell, priceBuy, stockMin, isActive, image) {
        document.getElementById('form-edit-product').action = '<?php echo e(url("inventory")); ?>/' + id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-category').value = category;
        document.getElementById('edit-unit').value = unit;
        document.getElementById('edit-price-sell').value = priceSell;
        document.getElementById('edit-price-buy').value = priceBuy;
        document.getElementById('edit-stock-min').value = stockMin;
        document.getElementById('edit-is-active').checked = isActive;

        // Set image preview
        const preview = document.getElementById('edit-img-preview');
        if (image) {
            preview.innerHTML = `<img src="${image}" class="w-full h-full object-cover rounded-xl">`;
        } else {
            preview.innerHTML = `<svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`;
        }

        document.getElementById('modal-edit-product').classList.remove('hidden');
    }

    // ── AI: Batch analyze all products on page load ───────────────
    const analyzeAllUrl  = '<?php echo e(route("inventory.ai.analyze-all")); ?>';
    const stockoutBase   = '/inventory/ai/stockout/';
    const reorderBase    = '/inventory/ai/reorder/';

    const urgencyBadge = {
        critical: 'px-2 py-0.5 rounded-full bg-red-500/20 text-red-400 border border-red-500/20',
        warning:  'px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/20',
        ok:       'px-2 py-0.5 rounded-full bg-green-500/20 text-green-400 border border-green-500/20',
        unknown:  'px-2 py-0.5 rounded-full bg-white/10 text-slate-400',
    };
    const urgencyLabel = {
        critical: '🔴 Kritis',
        warning:  '🟡 Perhatian',
        ok:       '✓ Aman',
        unknown:  '— Belum ada data',
    };
    const trendIcon = { increasing: '↑', stable: '→', decreasing: '↓', unknown: '—' };

    async function loadBatchAnalysis() {
        try {
            const res  = await fetch(analyzeAllUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            const analysis = data.analysis ?? {};

            for (const [id, info] of Object.entries(analysis)) {
                const el = document.getElementById(`ai-inv-${id}`);
                if (!el) continue;
                const badge = urgencyBadge[info.urgency] ?? urgencyBadge.unknown;
                const label = urgencyLabel[info.urgency] ?? '—';
                const days  = info.days_remaining != null ? ` · ${info.days_remaining}h` : '';
                el.className = `text-xs ${badge}`;
                el.textContent = label + days;
                el.title = info.days_remaining != null
                    ? `Stok habis ~${info.days_remaining} hari lagi (avg keluar: ${info.avg_daily_out}/hari)`
                    : 'Tidak ada data penjualan';
            }
        } catch (e) { /* silent */ }
    }

    async function openAiDetail(productId, productName, unit) {
        document.getElementById('ai-modal-title').textContent = 'Analisis AI — ' + productName;
        document.getElementById('ai-modal-content').innerHTML =
            '<div class="animate-pulse text-slate-500 text-sm text-center py-4">Menganalisis...</div>';
        document.getElementById('modal-ai-inventory').classList.remove('hidden');

        try {
            const [predRes, reorderRes] = await Promise.all([
                fetch(stockoutBase + productId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
                fetch(reorderBase  + productId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
            ]);
            const predData   = await predRes.json();
            const reorderData = await reorderRes.json();
            const p = predData.prediction;
            const r = reorderData.suggestion;

            const urgColors = { critical: 'text-red-400 bg-red-500/10 border-red-500/20', warning: 'text-amber-400 bg-amber-500/10 border-amber-500/20', ok: 'text-green-400 bg-green-500/10 border-green-500/20', unknown: 'text-slate-400 bg-white/5 border-white/10' };
            const confColor = { high: 'text-green-400', medium: 'text-yellow-400', low: 'text-slate-400' };
            const urg = urgColors[p.urgency] ?? urgColors.unknown;

            let html = `
                <div class="p-3 rounded-xl border ${urg} text-sm mb-1">
                    <div class="font-medium mb-0.5">${urgencyLabel[p.urgency] ?? '—'}</div>
                    <div class="text-xs opacity-80">${esc(p.message)}</div>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="bg-white/5 rounded-xl p-2 border border-white/10">
                        <p class="text-xs text-slate-400">Stok Saat Ini</p>
                        <p class="font-bold text-white">${p.current_stock} <span class="text-xs font-normal text-slate-400">${esc(unit)}</span></p>
                    </div>
                    <div class="bg-white/5 rounded-xl p-2 border border-white/10">
                        <p class="text-xs text-slate-400">Hari Tersisa</p>
                        <p class="font-bold text-white">${p.days_remaining ?? '—'}</p>
                    </div>
                    <div class="bg-white/5 rounded-xl p-2 border border-white/10">
                        <p class="text-xs text-slate-400">Tren</p>
                        <p class="font-bold text-white">${trendIcon[p.trend] ?? '—'} <span class="text-xs font-normal text-slate-400">${p.trend ?? ''}</span></p>
                    </div>
                </div>
                ${p.stockout_date ? `<p class="text-xs text-slate-400 text-center">Estimasi habis: <span class="text-white font-medium">${p.stockout_date}</span></p>` : ''}
                <hr class="border-white/10">
                <div>
                    <p class="text-xs text-slate-400 mb-2 font-medium uppercase tracking-wide">Saran Reorder</p>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-purple-500/10 rounded-xl p-3 border border-purple-500/20 text-center">
                            <p class="text-xs text-slate-400">Qty Reorder</p>
                            <p class="text-xl font-bold text-purple-300">${r.reorder_qty}</p>
                            <p class="text-xs text-slate-500">${esc(unit)}</p>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3 border border-white/10 text-center">
                            <p class="text-xs text-slate-400">Safety Stock</p>
                            <p class="text-xl font-bold text-white">${r.safety_stock}</p>
                            <p class="text-xs text-slate-500">${esc(unit)}</p>
                        </div>
                    </div>
                    <div class="mt-2 text-xs space-y-0.5">
                        <p class="${confColor[r.confidence] ?? 'text-slate-400'}">${esc(r.basis)}</p>
                        <p class="text-slate-500">Lead time: ${r.lead_time_days} hari · Cover: ${r.cover_days} hari · EOQ: ${r.economic_order} ${esc(unit)}</p>
                    </div>
                </div>
                <button onclick="prefillAddStock(${productId}, ${r.reorder_qty}, '${esc(productName)}', '${esc(unit)}')"
                    class="w-full py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl mt-1">
                    + Tambah Stok (${r.reorder_qty} ${esc(unit)})
                </button>`;

            document.getElementById('ai-modal-content').innerHTML = html;
        } catch (e) {
            document.getElementById('ai-modal-content').innerHTML =
                '<p class="text-red-400 text-sm">Gagal memuat analisis AI.</p>';
        }
    }

    function prefillAddStock(productId, qty, name, unit) {
        document.getElementById('modal-ai-inventory').classList.add('hidden');
        document.getElementById('stock-product-name').textContent = name + ' (' + unit + ')';
        document.getElementById('form-add-stock').action = '<?php echo e(url("inventory")); ?>/' + productId + '/stock';
        document.querySelector('#modal-add-stock input[name="quantity"]').value = qty;
        document.getElementById('modal-add-stock').classList.remove('hidden');
    }

    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/'/g,'&#39;');
    }

    document.addEventListener('DOMContentLoaded', loadBatchAnalysis);
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/inventory/index.blade.php ENDPATH**/ ?>