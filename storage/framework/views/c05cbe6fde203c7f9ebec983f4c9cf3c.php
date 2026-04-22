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
     <?php $__env->slot('header', null, []); ?> Mapping Produk <?php $__env->endSlot(); ?>

    <div class="space-y-6">

        
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">
                    Mapping Produk —
                    <span
                        class="<?php echo e($channel->platform === 'shopee' ? 'text-orange-400' : ($channel->platform === 'tokopedia' ? 'text-green-400' : 'text-red-400')); ?>">
                        <?php echo e($channel->shop_name); ?>

                    </span>
                    <span class="text-gray-500 text-base font-normal">(<?php echo e(ucfirst($channel->platform)); ?>)</span>
                </h1>
                <p class="text-sm text-gray-400 mt-0.5">Kelola pemetaan SKU produk ke marketplace</p>
            </div>
            <a href="<?php echo e(route('ecommerce.dashboard')); ?>"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 text-gray-300 rounded-xl text-sm hover:bg-white/10 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Dashboard
            </a>
        </div>

        
        <?php if(session('success')): ?>
            <div class="px-4 py-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl text-sm">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        
        <div x-data="{ open: false }" class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
            <button @click="open = !open"
                class="w-full flex items-center justify-between px-6 py-4 text-sm font-medium text-gray-300 hover:bg-white/5 transition">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Mapping Produk
                </span>
                <svg class="w-4 h-4 text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-transition class="border-t border-white/10">
                <form method="POST" action="<?php echo e(route('ecommerce.channels.mappings.store', $channel)); ?>"
                    class="px-6 py-5 space-y-4">
                    <?php echo csrf_field(); ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Produk <span
                                    class="text-red-400">*</span></label>
                            <select name="product_id" required
                                class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30">
                                <option value="" class="bg-[#1e293b] text-gray-400">-- Pilih Produk --</option>
                                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($product->id); ?>" class="bg-[#1e293b]">
                                        <?php echo e($product->name); ?> (<?php echo e($product->sku); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">SKU Marketplace <span
                                    class="text-red-400">*</span></label>
                            <input type="text" name="external_sku" required placeholder="SKU di platform marketplace"
                                class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm placeholder-gray-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30">
                            <?php $__errorArgs = ['external_sku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-red-400 text-xs mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">ID Produk Marketplace</label>
                            <input type="text" name="external_product_id" placeholder="Opsional"
                                class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm placeholder-gray-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Harga Override</label>
                            <input type="number" name="price_override" min="0" step="100"
                                placeholder="Kosongkan untuk pakai harga jual"
                                class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm placeholder-gray-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition">
                            Tambah Mapping
                        </button>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between">
                <h2 class="font-semibold text-white text-sm">Daftar Mapping</h2>
                <span class="text-xs text-gray-500"><?php echo e($mappings->total()); ?> mapping</span>
            </div>

            <?php if($mappings->isEmpty()): ?>
                <div class="px-6 py-14 text-center">
                    <div class="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm">Belum ada mapping produk</p>
                    <p class="text-gray-600 text-xs mt-1">Tambah mapping di atas untuk menghubungkan produk ke
                        marketplace</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-white/5 text-xs text-gray-400 uppercase">
                            <tr>
                                <th class="px-6 py-3 text-left">Produk</th>
                                <th class="px-6 py-3 text-left">SKU Internal</th>
                                <th class="px-6 py-3 text-left">SKU Marketplace</th>
                                <th class="px-6 py-3 text-left">ID Marketplace</th>
                                <th class="px-6 py-3 text-right">Harga Override</th>
                                <th class="px-6 py-3 text-left hidden lg:table-cell">Sync Stok</th>
                                <th class="px-6 py-3 text-left hidden lg:table-cell">Sync Harga</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <?php $__currentLoopData = $mappings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mapping): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-white/5">
                                    <td class="px-6 py-3 text-white font-medium"><?php echo e($mapping->product->name ?? '—'); ?>

                                    </td>
                                    <td class="px-6 py-3 font-mono text-xs text-gray-400">
                                        <?php echo e($mapping->product->sku ?? '—'); ?></td>
                                    <td class="px-6 py-3 font-mono text-xs text-indigo-300">
                                        <?php echo e($mapping->external_sku); ?></td>
                                    <td class="px-6 py-3 font-mono text-xs text-gray-500">
                                        <?php echo e($mapping->external_product_id ?? '—'); ?></td>
                                    <td class="px-6 py-3 text-right text-gray-300">
                                        <?php if($mapping->price_override): ?>
                                            <span class="text-amber-400">Rp
                                                <?php echo e(number_format($mapping->price_override, 0, ',', '.')); ?></span>
                                        <?php else: ?>
                                            <span class="text-gray-500">Default</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-3 text-gray-400 text-xs hidden lg:table-cell">
                                        <?php echo e($mapping->last_stock_sync_at?->diffForHumans() ?? '—'); ?>

                                    </td>
                                    <td class="px-6 py-3 text-gray-400 text-xs hidden lg:table-cell">
                                        <?php echo e($mapping->last_price_sync_at?->diffForHumans() ?? '—'); ?>

                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <form method="POST"
                                            action="<?php echo e(route('ecommerce.channels.mappings.destroy', $mapping)); ?>"
                                            onsubmit="return confirm('Hapus mapping ini?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit"
                                                class="px-3 py-1.5 bg-red-600/20 hover:bg-red-600/40 text-red-400 text-xs font-medium rounded-lg border border-red-500/20 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-white/10">
                    <?php echo e($mappings->links()); ?>

                </div>
            <?php endif; ?>
        </div>

        
        <?php if(isset($priceHistories) && $priceHistories->isNotEmpty()): ?>
            <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-6 mt-8">
                <h3 class="text-lg font-semibold text-white mb-4">Riwayat Perubahan Harga</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-gray-400 border-b border-white/10">
                                <th class="text-left py-2 px-3">Produk</th>
                                <th class="text-left py-2 px-3">Harga Lama</th>
                                <th class="text-left py-2 px-3">Harga Baru</th>
                                <th class="text-left py-2 px-3">Perubahan</th>
                                <th class="text-left py-2 px-3">Order Sebelum (7h)</th>
                                <th class="text-left py-2 px-3">Order Sesudah (7h)</th>
                                <th class="text-left py-2 px-3">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php $__currentLoopData = $priceHistories->flatten()->sortByDesc('created_at')->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ph): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $pctChange =
                                        $ph->old_price > 0
                                            ? round((($ph->new_price - $ph->old_price) / $ph->old_price) * 100, 1)
                                            : 0;
                                ?>
                                <tr>
                                    <td class="py-2 px-3 text-white"><?php echo e($ph->product?->name ?? '-'); ?></td>
                                    <td class="py-2 px-3 text-gray-400">Rp
                                        <?php echo e(number_format($ph->old_price, 0, ',', '.')); ?></td>
                                    <td class="py-2 px-3 text-white">Rp
                                        <?php echo e(number_format($ph->new_price, 0, ',', '.')); ?></td>
                                    <td class="py-2 px-3 <?php echo e($pctChange >= 0 ? 'text-emerald-400' : 'text-red-400'); ?>">
                                        <?php echo e($pctChange >= 0 ? '+' : ''); ?><?php echo e($pctChange); ?>%
                                    </td>
                                    <td class="py-2 px-3 text-gray-400"><?php echo e($ph->orders_before_7d); ?></td>
                                    <td class="py-2 px-3 text-gray-400"><?php echo e($ph->orders_after_7d ?: 'Menunggu...'); ?>

                                    </td>
                                    <td class="py-2 px-3 text-gray-500"><?php echo e($ph->created_at->format('d M Y H:i')); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\ecommerce\mappings.blade.php ENDPATH**/ ?>