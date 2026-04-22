<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'data' => [],
    'fields' => [],
    'actions' => null,
    'titleField' => 'name',
    'subtitleField' => null,
    'statusField' => null,
    'emptyMessage' => 'Tidak ada data',
    'clickUrl' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'data' => [],
    'fields' => [],
    'actions' => null,
    'titleField' => 'name',
    'subtitleField' => null,
    'statusField' => null,
    'emptyMessage' => 'Tidak ada data',
    'clickUrl' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>



<div class="md:hidden space-y-3">
    <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-sm">
            
            <div class="px-4 py-3 border-b border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <?php if($clickUrl): ?>
                            <a href="<?php echo e(is_callable($clickUrl) ? $clickUrl($item, $index) : str_replace(':id', $item->id ?? $item['id'], $clickUrl)); ?>"
                                class="block">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white truncate">
                                    <?php echo e(data_get($item, $titleField, '-')); ?>

                                </h3>
                            </a>
                        <?php else: ?>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white truncate">
                                <?php echo e(data_get($item, $titleField, '-')); ?>

                            </h3>
                        <?php endif; ?>

                        <?php if($subtitleField && data_get($item, $subtitleField)): ?>
                            <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5 truncate">
                                <?php echo e(data_get($item, $subtitleField)); ?>

                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if($statusField && data_get($item, $statusField)): ?>
                        <?php
                            $status = data_get($item, $statusField);
                            $statusClass = match (strtolower($status)) {
                                'active',
                                'aktif',
                                'published',
                                'completed',
                                'paid'
                                    => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                                'inactive',
                                'nonaktif',
                                'draft',
                                'pending'
                                    => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                                'cancelled',
                                'rejected',
                                'failed'
                                    => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                                default => 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-slate-400',
                            };
                        ?>
                        <span
                            class="px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap <?php echo e($statusClass); ?>">
                            <?php echo e(is_string($status) ? ucfirst($status) : $status); ?>

                        </span>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="px-4 py-3 space-y-2.5">
                <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $value = data_get($item, $field['key']);
                        $displayValue = $value ?? '-';

                        // Format currency
                        if (($field['type'] ?? '') === 'currency' && is_numeric($value)) {
                            $displayValue = 'Rp ' . number_format($value, 0, ',', '.');
                        }

                        // Format date
                        if (($field['type'] ?? '') === 'date' && $value) {
                            $displayValue = is_string($value) ? $value : $value->format('d/m/Y');
                        }
                    ?>

                    <div class="flex items-start justify-between gap-3">
                        <span class="text-sm text-gray-500 dark:text-slate-400 shrink-0">
                            <?php echo e($field['label']); ?>

                        </span>

                        <?php if(($field['type'] ?? '') === 'tel' && $value): ?>
                            <a href="tel:<?php echo e($value); ?>"
                                class="text-sm text-gray-900 dark:text-white text-right break-words hover:text-blue-600 dark:hover:text-blue-400">
                                <?php echo e($displayValue); ?>

                            </a>
                        <?php elseif(($field['type'] ?? '') === 'email' && $value): ?>
                            <a href="mailto:<?php echo e($value); ?>"
                                class="text-sm text-gray-900 dark:text-white text-right break-words hover:text-blue-600 dark:hover:text-blue-400">
                                <?php echo e($displayValue); ?>

                            </a>
                        <?php elseif(($field['type'] ?? '') === 'badge' && $value): ?>
                            <span
                                class="px-2 py-0.5 rounded-full text-xs <?php echo e($field['badgeClass'] ?? 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-slate-400'); ?>">
                                <?php echo e($displayValue); ?>

                            </span>
                        <?php else: ?>
                            <span
                                class="text-sm text-gray-900 dark:text-white text-right break-words <?php echo e($field['class'] ?? ''); ?>">
                                <?php echo e($displayValue); ?>

                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <?php if($actions): ?>
                <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
                    <div class="flex items-center justify-end gap-2">
                        <?php if(is_callable($actions)): ?>
                            <?php echo $actions($item, $index); ?>

                        <?php else: ?>
                            <?php echo $actions; ?>

                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="mt-4 text-sm text-gray-500 dark:text-slate-400"><?php echo e($emptyMessage); ?></p>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\mobile-card.blade.php ENDPATH**/ ?>