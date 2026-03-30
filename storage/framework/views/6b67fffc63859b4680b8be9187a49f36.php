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
     <?php $__env->slot('header', null, []); ?> Manajemen Dokumen <?php $__env->endSlot(); ?>

    <div class="space-y-6">

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Unggah Dokumen Baru</h2>
            <form method="POST" action="<?php echo e(route('documents.store')); ?>" enctype="multipart/form-data"
                  class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Judul Dokumen *</label>
                    <input type="text" name="title" required placeholder="Nama dokumen..."
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Kategori</label>
                    <input type="text" name="category" placeholder="Kontrak, Invoice, SOP..."
                        list="category-list"
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                    <datalist id="category-list">
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cat); ?>">
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <option value="Kontrak">
                        <option value="Invoice">
                        <option value="SOP">
                        <option value="Laporan">
                        <option value="Lainnya">
                    </datalist>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Deskripsi</label>
                    <input type="text" name="description" placeholder="Keterangan singkat..."
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">File * (maks 20MB)</label>
                    <input type="file" name="file" required
                        class="w-full text-sm text-gray-500 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer">
                </div>
                <div class="sm:col-span-2 lg:col-span-4 flex justify-end">
                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">
                        Unggah Dokumen
                    </button>
                </div>
            </form>
        </div>

        
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari judul atau deskripsi..."
                class="flex-1 min-w-48 bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
            <select name="category"
                class="bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                <option value="">Semua Kategori</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($cat); ?>" <?php echo e(request('category') === $cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">Filter</button>
            <?php if(request()->hasAny(['search','category'])): ?>
            <a href="<?php echo e(route('documents.index')); ?>" class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 text-sm font-medium rounded-xl hover:bg-gray-100 dark:hover:bg-white/5 transition">Reset</a>
            <?php endif; ?>
        </form>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <?php if($documents->isEmpty()): ?>
                <div class="px-6 py-16 text-center text-gray-400 dark:text-slate-500 text-sm">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Belum ada dokumen.
                </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Dokumen</th>
                            <th class="px-6 py-3 text-left hidden sm:table-cell">Kategori</th>
                            <th class="px-6 py-3 text-left hidden md:table-cell">Ukuran</th>
                            <th class="px-6 py-3 text-left hidden lg:table-cell">Diunggah oleh</th>
                            <th class="px-6 py-3 text-left hidden sm:table-cell">Tanggal</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center shrink-0">
                                        <?php
                                            $icon = match(true) {
                                                str_contains($doc->file_type, 'pdf') => '📄',
                                                str_contains($doc->file_type, 'image') => '🖼️',
                                                str_contains($doc->file_type, 'spreadsheet') || str_contains($doc->file_name, '.xls') => '📊',
                                                str_contains($doc->file_type, 'word') || str_contains($doc->file_name, '.doc') => '📝',
                                                default => '📎',
                                            };
                                        ?>
                                        <span class="text-sm"><?php echo e($icon); ?></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white"><?php echo e($doc->title); ?></p>
                                        <?php if($doc->description): ?>
                                        <p class="text-xs text-gray-400 dark:text-slate-500 truncate max-w-xs"><?php echo e($doc->description); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 hidden sm:table-cell">
                                <span class="px-2 py-0.5 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-full text-xs font-medium">
                                    <?php echo e($doc->category ?? 'Umum'); ?>

                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-500 dark:text-slate-400 hidden md:table-cell"><?php echo e($doc->file_size_human); ?></td>
                            <td class="px-6 py-3 text-gray-500 dark:text-slate-400 hidden lg:table-cell"><?php echo e($doc->uploader?->name ?? '-'); ?></td>
                            <td class="px-6 py-3 text-gray-400 dark:text-slate-500 hidden sm:table-cell"><?php echo e($doc->created_at->format('d M Y')); ?></td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo e(route('documents.download', $doc)); ?>"
                                        class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-medium rounded-lg transition">
                                        Unduh
                                    </a>
                                    <?php if(auth()->user()->hasRole(['admin','manager']) || $doc->uploaded_by === auth()->id()): ?>
                                    <form method="POST" action="<?php echo e(route('documents.destroy', $doc)); ?>"
                                          onsubmit="return confirm('Hapus dokumen ini?')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit"
                                            class="px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 text-xs font-medium rounded-lg transition">
                                            Hapus
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5">
                <?php echo e($documents->links()); ?>

            </div>
            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\documents\index.blade.php ENDPATH**/ ?>