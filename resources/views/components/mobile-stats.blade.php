@props([
    'stats' => [],
    'columns' => 2, // 2, 3, or 4
    'showIcons' => true,
    'showTrend' => false,
    'size' => 'md', // sm, md, lg
])

{{-- 
    Mobile Stats Grid Component
    Responsive statistics cards with icons and trend indicators
    
    Usage:
    <x-mobile-stats :stats="[
        [
            'label' => 'Total Penjualan',
            'value' => 'Rp 15.5M',
            'icon' => 'chart',
            'color' => 'blue',
            'trend' => '+12.5%',
            'trendUp' => true,
        ],
        [
            'label' => 'Invoice Pending',
            'value' => '23',
            'icon' => 'document',
            'color' => 'amber',
            'trend' => '-5.2%',
            'trendUp' => false,
        ],
    ]" :columns="2" />
--}}

@php
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
            'bg' => 'bg-blue-50 dark:bg-blue-900/20',
            'icon' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400',
            'value' => 'text-blue-600 dark:text-blue-400',
        ],
        'green' => [
            'bg' => 'bg-green-50 dark:bg-green-900/20',
            'icon' => 'bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400',
            'value' => 'text-green-600 dark:text-green-400',
        ],
        'amber' => [
            'bg' => 'bg-amber-50 dark:bg-amber-900/20',
            'icon' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400',
            'value' => 'text-amber-600 dark:text-amber-400',
        ],
        'red' => [
            'bg' => 'bg-red-50 dark:bg-red-900/20',
            'icon' => 'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400',
            'value' => 'text-red-600 dark:text-red-400',
        ],
        'purple' => [
            'bg' => 'bg-purple-50 dark:bg-purple-900/20',
            'icon' => 'bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400',
            'value' => 'text-purple-600 dark:text-purple-400',
        ],
        'gray' => [
            'bg' => 'bg-gray-50 dark:bg-white/5',
            'icon' => 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-400',
            'value' => 'text-gray-900 dark:text-white',
        ],
    ];
@endphp

<div class="{{ $gridCols }} gap-3" role="group" aria-label="Statistics">
    @foreach ($stats as $stat)
        @php
            $color = $stat['color'] ?? 'gray';
            $colors = $colorClasses[$color] ?? $colorClasses['gray'];
            $icon = $stat['icon'] ?? 'chart';
            $iconSvg = $iconPaths[$icon] ?? $iconPaths['chart'];
        @endphp

        <div
            class="rounded-2xl border border-gray-200 dark:border-white/10 {{ $currentSize['card'] }} {{ $colors['bg'] }} transition-all duration-200 hover:shadow-md">
            <div class="flex items-start justify-between mb-2">
                @if ($showIcons)
                    <div
                        class="{{ $currentSize['icon'] }} {{ $colors['icon'] }} rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $iconSvg !!}
                        </svg>
                    </div>
                @endif

                @if ($showTrend && isset($stat['trend']))
                    @php
                        $trendUp = $stat['trendUp'] ?? true;
                    @endphp
                    <div
                        class="flex items-center gap-1 {{ $currentSize['trend'] }} {{ $trendUp ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if ($trendUp)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            @endif
                        </svg>
                        <span>{{ $stat['trend'] }}</span>
                    </div>
                @endif
            </div>

            <p class="{{ $currentSize['value'] }} font-bold {{ $colors['value'] }} mb-1">
                {{ $stat['value'] }}
            </p>
            <p class="{{ $currentSize['label'] }} text-gray-500 dark:text-slate-400">
                {{ $stat['label'] }}
            </p>
        </div>
    @endforeach
</div>
