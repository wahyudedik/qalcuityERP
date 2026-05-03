<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'stats' => [],
    'columns' => 2, // 2, 3, or 4
    'showIcons' => true,
    'showTrend' => false,
    'size' => 'md', // sm, md, lg
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
    'stats' => [],
    'columns' => 2, // 2, 3, or 4
    'showIcons' => true,
    'showTrend' => false,
    'size' => 'md', // sm, md, lg
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>



<?php
    $gridCols = match ($columns) {
        3 => 'grid-cols-2 md:grid-cols-3',
        4 => 'grid-cols-2 md:grid-cols-4',
        default => 'grid-cols-2',
    };

    $sizeClasses = [
        'sm' => [
            'card' => 'p-3',
            'icon' => 'w-8 h-8',
            'value' => 'text-xl',
            'label' => 'text-xs',
            'trend' => 'text-xs',
        ],
        'md' => [
            'card' => 'p-4',
            'icon' => 'w-10 h-10',
            'value' => 'text-2xl',
            'label' => 'text-xs',
            'trend' => 'text-xs',
        ],
        'lg' => [
            'card' => 'p-5',
            'icon' => 'w-12 h-12',
            'value' => 'text-3xl',
            'label' => 'text-sm',
            'trend' => 'text-sm',
        ],
    ];

    $currentSize = $sizeClasses[$size] ?? $sizeClasses['md'];

    $iconPaths = [
        'chart' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
        'document' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
        'users' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />',
        'currency' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'shopping' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />',
        'package' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />',
        'trend-up' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />',
        'trend-down' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />',
        'warning' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
        'check' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'clock' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />',
    ];

    $colorClasses = [
        'blue' => [
            'bg' => 'bg-blue-50',
            'icon' => 'bg-blue-100 text-blue-600',
            'value' => 'text-blue-600',
        ],
        'green' => [
            'bg' => 'bg-green-50',
            'icon' => 'bg-green-100 text-green-600',
            'value' => 'text-green-600',
        ],
        'amber' => [
            'bg' => 'bg-amber-50',
            'icon' => 'bg-amber-100 text-amber-600',
            'value' => 'text-amber-600',
        ],
        'red' => [
            'bg' => 'bg-red-50',
            'icon' => 'bg-red-100 text-red-600',
            'value' => 'text-red-600',
        ],
        'purple' => [
            'bg' => 'bg-purple-50',
            'icon' => 'bg-purple-100 text-purple-600',
            'value' => 'text-purple-600',
        ],
        'gray' => [
            'bg' => 'bg-gray-50',
            'icon' => 'bg-gray-100 text-gray-600',
            'value' => 'text-gray-900',
        ],
    ];
?>

<div class="<?php echo e($gridCols); ?> gap-3" role="group" aria-label="Statistics">
    <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $color = $stat['color'] ?? 'gray';
            $colors = $colorClasses[$color] ?? $colorClasses['gray'];
            $icon = $stat['icon'] ?? 'chart';
            $iconSvg = $iconPaths[$icon] ?? $iconPaths['chart'];
        ?>

        <div
            class="rounded-2xl border border-gray-200 <?php echo e($currentSize['card']); ?> <?php echo e($colors['bg']); ?> transition-all duration-200 hover:shadow-md">
            <div class="flex items-start justify-between mb-2">
                <?php if($showIcons): ?>
                    <div
                        class="<?php echo e($currentSize['icon']); ?> <?php echo e($colors['icon']); ?> rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <?php echo $iconSvg; ?>

                        </svg>
                    </div>
                <?php endif; ?>

                <?php if($showTrend && isset($stat['trend'])): ?>
                    <?php
                        $trendUp = $stat['trendUp'] ?? true;
                    ?>
                    <div
                        class="flex items-center gap-1 <?php echo e($currentSize['trend']); ?> <?php echo e($trendUp ? 'text-green-600' : 'text-red-600'); ?> font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <?php if($trendUp): ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            <?php else: ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            <?php endif; ?>
                        </svg>
                        <span><?php echo e($stat['trend']); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <p class="<?php echo e($currentSize['value']); ?> font-bold <?php echo e($colors['value']); ?> mb-1">
                <?php echo e($stat['value']); ?>

            </p>
            <p class="<?php echo e($currentSize['label']); ?> text-gray-500">
                <?php echo e($stat['label']); ?>

            </p>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\mobile-stats.blade.php ENDPATH**/ ?>