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
    'imageField' => null,
    'badgeField' => null,
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
    'imageField' => null,
    'badgeField' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>



<div class="md:hidden space-y-3" role="list" aria-label="Mobile card view">
    <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-sm transition-all duration-200 hover:shadow-md touch-ripple"
            role="listitem" aria-label="Item <?php echo e($index + 1); ?>">
            
            <div class="px-4 py-3 border-b border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        
                        <?php if($imageField && data_get($item, $imageField)): ?>
                            <img src="<?php echo e(data_get($item, $imageField)); ?>" alt=""
                                class="w-10 h-10 rounded-xl object-cover shrink-0 border border-gray-200 dark:border-white/10"
                                loading="lazy">
                        <?php endif; ?>

                        <div class="flex-1 min-w-0">
                            <?php if($clickUrl): ?>
                                <a href="<?php echo e(is_callable($clickUrl) ? $clickUrl($item, $index) : (str_contains($clickUrl, ':id') ? str_replace(':id', $item->id ?? ($item['id'] ?? ''), $clickUrl) : route($clickUrl, $item->id ?? ($item['id'] ?? '')))); ?>"
                                    class="block" aria-label="View details">
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
                    </div>

                    
                    <?php if($statusField && data_get($item, $statusField)): ?>
                        <?php
                            $status = data_get($item, $statusField);
                            $statusClass = match (strtolower($status)) {
                                'active',
                                'aktif',
                                'published',
                                'completed',
                                'paid',
                                'lunas',
                                'success'
                                    => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                                'inactive',
                                'nonaktif',
                                'draft',
                                'pending',
                                'menunggu'
                                    => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                                'cancelled',
                                'rejected',
                                'failed',
                                'gagal',
                                'expired',
                                'kadaluarsa'
                                    => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                                'processing',
                                'diproses',
                                'in_progress'
                                    => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                                'sent',
                                'terkirim',
                                'shipped'
                                    => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
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
                        $type = $field['type'] ?? 'text';

                        // Format currency
                        if ($type === 'currency' && is_numeric($value)) {
                            $displayValue = 'Rp ' . number_format($value, 0, ',', '.');
                        }

                        // Format date
                        if ($type === 'date' && $value) {
                            $displayValue = is_string($value)
                                ? $value
                                : (method_exists($value, 'format')
                                    ? $value->format('d/m/Y')
                                    : $value);
                        }

                        // Format datetime
                        if ($type === 'datetime' && $value) {
                            $displayValue = is_string($value)
                                ? $value
                                : (method_exists($value, 'format')
                                    ? $value->format('d/m/Y H:i')
                                    : $value);
                        }

                        // Format number
                        if ($type === 'number' && is_numeric($value)) {
                            $displayValue = number_format($value, 0, ',', '.');
                        }

                        // Format percentage
                        if ($type === 'percentage' && is_numeric($value)) {
                            $displayValue = number_format($value, 1) . '%';
                        }

                        // Determine display class
                        $valueClass = $field['class'] ?? '';
                        if ($type === 'currency' && $value > 0) {
                            $valueClass .= ' font-semibold text-green-600 dark:text-green-400';
                        }
                        if ($type === 'currency' && $value < 0) {
                            $valueClass .= ' font-semibold text-red-600 dark:text-red-400';
                        }
                    ?>

                    <div class="flex items-start justify-between gap-3">
                        <span class="text-sm text-gray-500 dark:text-slate-400 shrink-0">
                            <?php echo e($field['label']); ?>

                        </span>

                        <?php if($type === 'tel' && $value): ?>
                            <a href="tel:<?php echo e($value); ?>"
                                class="text-sm text-blue-600 dark:text-blue-400 text-right break-words hover:underline">
                                <?php echo e($displayValue); ?>

                            </a>
                        <?php elseif($type === 'email' && $value): ?>
                            <a href="mailto:<?php echo e($value); ?>"
                                class="text-sm text-blue-600 dark:text-blue-400 text-right break-words hover:underline">
                                <?php echo e($displayValue); ?>

                            </a>
                        <?php elseif($type === 'link' && $value && isset($field['url'])): ?>
                            <a href="<?php echo e(is_callable($field['url']) ? $field['url']($item) : $field['url']); ?>"
                                class="text-sm text-blue-600 dark:text-blue-400 text-right break-words hover:underline">
                                <?php echo e($displayValue); ?>

                            </a>
                        <?php elseif($type === 'badge' && $value): ?>
                            <span
                                class="px-2 py-0.5 rounded-full text-xs <?php echo e($field['badgeClass'] ?? 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-slate-400'); ?>">
                                <?php echo e($displayValue); ?>

                            </span>
                        <?php elseif($type === 'boolean'): ?>
                            <span
                                class="text-sm <?php echo e($value ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'); ?>">
                                <?php echo e($value ? 'Ya' : 'Tidak'); ?>

                            </span>
                        <?php elseif($type === 'progress' && is_numeric($value)): ?>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-gray-900 dark:text-white"><?php echo e($displayValue); ?></span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-white/10 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                        style="width: <?php echo e(min(100, max(0, $value))); ?>%"></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <span
                                class="text-sm text-gray-900 dark:text-white text-right break-words <?php echo e($valueClass); ?>">
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\mobile-table.blade.php ENDPATH**/ ?>