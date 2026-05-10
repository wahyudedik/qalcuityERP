

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label' => 'Total',
    'value' => '0',
    'color' => 'blue', // blue, green, amber, red, purple, orange
    'icon' => null, // optional SVG icon name
    'trend' => null, // optional: '+12%' or '-5%'
    'trendUp' => true, // true = green (good), false = red (bad)
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
    'label' => 'Total',
    'value' => '0',
    'color' => 'blue', // blue, green, amber, red, purple, orange
    'icon' => null, // optional SVG icon name
    'trend' => null, // optional: '+12%' or '-5%'
    'trendUp' => true, // true = green (good), false = red (bad)
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $colorClasses = [
        'blue' => [
            'text' => 'text-blue-600',
            'bg' => 'bg-blue-100',
            'icon' => 'text-blue-600',
        ],
        'green' => [
            'text' => 'text-green-600',
            'bg' => 'bg-green-100',
            'icon' => 'text-green-600',
        ],
        'amber' => [
            'text' => 'text-amber-600',
            'bg' => 'bg-amber-100',
            'icon' => 'text-amber-600',
        ],
        'red' => [
            'text' => 'text-red-600',
            'bg' => 'bg-red-100',
            'icon' => 'text-red-600',
        ],
        'purple' => [
            'text' => 'text-purple-600',
            'bg' => 'bg-purple-100',
            'icon' => 'text-purple-600',
        ],
        'orange' => [
            'text' => 'text-orange-600',
            'bg' => 'bg-orange-100',
            'icon' => 'text-orange-600',
        ],
    ];

    $colors = $colorClasses[$color] ?? $colorClasses['blue'];
?>

<div
    class="bg-white rounded-2xl p-4 border border-gray-200 hover:shadow-lg transition-shadow">
    <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
            <p class="text-xs text-gray-500 truncate"><?php echo e($label); ?></p>
            <p class="text-2xl font-bold <?php echo e($colors['text']); ?> mt-1"><?php echo e($value); ?></p>

            <?php if($trend): ?>
                <p
                    class="text-xs mt-1 <?php echo e($trendUp ? 'text-green-600' : 'text-red-600'); ?>">
                    <?php echo e($trendUp ? '↑' : '↓'); ?> <?php echo e($trend); ?> from last period
                </p>
            <?php endif; ?>
        </div>

        <?php if($icon): ?>
            <div class="flex-shrink-0 w-12 h-12 <?php echo e($colors['bg']); ?> rounded-xl flex items-center justify-center">
                <?php switch($icon):
                    case ('users'): ?>
                        <svg class="w-6 h-6 <?php echo e($colors['icon']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                    <?php break; ?>

                    <?php case ('calendar'): ?>
                        <svg class="w-6 h-6 <?php echo e($colors['icon']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    <?php break; ?>

                    <?php case ('user-md'): ?>
                        <svg class="w-6 h-6 <?php echo e($colors['icon']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    <?php break; ?>

                    <?php case ('flask'): ?>
                        <svg class="w-6 h-6 <?php echo e($colors['icon']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                            </path>
                        </svg>
                    <?php break; ?>

                    <?php case ('pill'): ?>
                        <svg class="w-6 h-6 <?php echo e($colors['icon']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                            </path>
                        </svg>
                    <?php break; ?>

                    <?php case ('bed'): ?>
                        <svg class="w-6 h-6 <?php echo e($colors['icon']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                    <?php break; ?>

                    <?php case ('clipboard'): ?>
                        <svg class="w-6 h-6 <?php echo e($colors['icon']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                    <?php break; ?>

                    <?php case ('clock'): ?>
                        <svg class="w-6 h-6 <?php echo e($colors['icon']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    <?php break; ?>

                    <?php case ('check'): ?>
                        <svg class="w-6 h-6 <?php echo e($colors['icon']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    <?php break; ?>

                    <?php case ('x'): ?>
                        <svg class="w-6 h-6 <?php echo e($colors['icon']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    <?php break; ?>

                    <?php case ('chart'): ?>
                        <svg class="w-6 h-6 <?php echo e($colors['icon']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    <?php break; ?>

                    <?php default: ?>
                        <svg class="w-6 h-6 <?php echo e($colors['icon']); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                <?php endswitch; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/components/healthcare/stats-card.blade.php ENDPATH**/ ?>