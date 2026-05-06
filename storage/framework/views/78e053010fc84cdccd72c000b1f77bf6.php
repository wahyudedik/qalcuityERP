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
     <?php $__env->slot('title', null, []); ?> Popup Iklan — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> Popup Iklan <?php $__env->endSlot(); ?>
     <?php $__env->slot('pageHeader', null, []); ?> 
        <a href="<?php echo e(route('super-admin.popup-ads.create')); ?>"
            class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Buat Iklan
        </a>
     <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Judul</th>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">
                            Target</th>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">
                            Frekuensi</th>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            Periode</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $ads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $statusLabel = $ad->status_label;
                            $statusClass = match ($statusLabel) {
                                'Aktif' => 'text-green-700 bg-green-100 border-green-200',
                                'Nonaktif' => 'text-gray-500 bg-gray-100 border-gray-200',
                                'Terjadwal' => 'text-blue-700 bg-blue-100 border-blue-200',
                                'Kedaluwarsa' => 'text-red-700 bg-red-100 border-red-200',
                                default => 'text-gray-500 bg-gray-100 border-gray-200',
                            };
                        ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <?php if($ad->image_path): ?>
                                        <img src="<?php echo e(Storage::url($ad->image_path)); ?>"
                                            class="w-10 h-10 rounded-lg object-cover shrink-0 border border-gray-200">
                                    <?php else: ?>
                                        <div
                                            class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($ad->title); ?></p>
                                        <?php if($ad->button_label): ?>
                                            <p class="text-xs text-gray-400 truncate">CTA: <?php echo e($ad->button_label); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 hidden md:table-cell">
                                <?php if($ad->target === 'all'): ?>
                                    <span class="text-xs text-gray-600">Semua Tenant</span>
                                <?php else: ?>
                                    <span class="text-xs text-blue-600"><?php echo e(count($ad->tenant_ids ?? [])); ?> tenant</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 hidden md:table-cell">
                                <span class="text-xs text-gray-600">
                                    <?php echo e(match ($ad->frequency) {
                                        'once' => 'Sekali saja',
                                        'daily' => 'Setiap hari',
                                        'always' => 'Selalu',
                                        default => $ad->frequency,
                                    }); ?>

                                </span>
                            </td>
                            <td class="px-5 py-4 hidden lg:table-cell">
                                <p class="text-xs text-gray-500">
                                    <?php echo e($ad->starts_at ? $ad->starts_at->format('d M Y') : '—'); ?>

                                    <?php if($ad->ends_at): ?>
                                        <span class="text-gray-300 mx-1">→</span>
                                        <?php echo e($ad->ends_at->format('d M Y')); ?>

                                    <?php endif; ?>
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border <?php echo e($statusClass); ?>">
                                    <?php echo e($statusLabel); ?>

                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="<?php echo e(route('super-admin.popup-ads.edit', $ad)); ?>"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition"
                                        title="Edit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="<?php echo e(route('super-admin.popup-ads.toggle', $ad)); ?>"
                                        class="inline">
                                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                        <button type="submit"
                                            class="p-1.5 rounded-lg transition <?php echo e($ad->is_active ? 'text-green-600 hover:text-gray-500 hover:bg-gray-100' : 'text-gray-400 hover:text-green-600 hover:bg-green-50'); ?>"
                                            title="<?php echo e($ad->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="<?php echo e($ad->is_active ? 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'); ?>" />
                                            </svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="<?php echo e(route('super-admin.popup-ads.destroy', $ad)); ?>"
                                        onsubmit="return confirm('Hapus popup iklan ini?')" class="inline">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition"
                                            title="Hapus">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <svg class="w-10 h-10 opacity-30" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                    </svg>
                                    <p class="text-sm">Belum ada popup iklan.</p>
                                    <a href="<?php echo e(route('super-admin.popup-ads.create')); ?>"
                                        class="text-sm text-blue-600 hover:text-blue-700 font-medium">+ Buat iklan
                                        pertama</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($ads->hasPages()): ?>
            <div class="px-6 py-4 border-t border-gray-200"><?php echo e($ads->links()); ?></div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/super-admin/popup-ads/index.blade.php ENDPATH**/ ?>