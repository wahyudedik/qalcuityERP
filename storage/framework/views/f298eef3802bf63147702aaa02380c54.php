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
     <?php $__env->slot('header', null, []); ?> Knowledge Base <?php $__env->endSlot(); ?>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari artikel..."
                class="flex-1 min-w-[150px] px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="category" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Kategori</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cat); ?>" <?php if(request('category')===$cat): echo 'selected'; endif; ?>><?php echo e(ucfirst($cat)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <a href="<?php echo e(route('helpdesk.index')); ?>" class="px-3 py-2 text-sm text-gray-500">← Helpdesk</a>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'helpdesk', 'create')): ?>
        <button onclick="document.getElementById('modal-add-kb').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Artikel</button>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php $__empty_1 = true; $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-start justify-between mb-2">
                <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700"><?php echo e(ucfirst($a->category)); ?></span>
                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'helpdesk', 'delete')): ?>
                <form method="POST" action="<?php echo e(route('helpdesk.kb.destroy', $a)); ?>" onsubmit="return confirm('Hapus?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="text-gray-400 hover:text-red-500 text-xs">✕</button>
                </form>
                <?php endif; ?>
            </div>
            <h3 class="font-semibold text-gray-900 mb-2"><?php echo e($a->title); ?></h3>
            <p class="text-xs text-gray-500 line-clamp-3"><?php echo e(Str::limit(strip_tags($a->body), 150)); ?></p>
            <p class="text-xs text-gray-400 mt-2"><?php echo e($a->views); ?> views · <?php echo e($a->created_at->format('d/m/Y')); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-span-full text-center py-12 text-gray-400">Belum ada artikel.</div>
        <?php endif; ?>
    </div>
    <?php if($articles->hasPages()): ?><div class="mt-4"><?php echo e($articles->links()); ?></div><?php endif; ?>

    
    <div id="modal-add-kb" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Buat Artikel</h3>
                <button onclick="document.getElementById('modal-add-kb').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('helpdesk.kb.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; ?>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Judul *</label><input type="text" name="title" required class="<?php echo e($cls); ?>"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Kategori *</label>
                    <select name="category" required class="<?php echo e($cls); ?>">
                        <option value="general">Umum</option><option value="billing">Billing</option><option value="technical">Teknis</option><option value="delivery">Pengiriman</option><option value="product">Produk</option><option value="faq">FAQ</option>
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Isi Artikel *</label><textarea name="body" required rows="6" class="<?php echo e($cls); ?>"></textarea></div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-kb').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\helpdesk\knowledge-base.blade.php ENDPATH**/ ?>