@props([
    'sortable' => false,
    'sortKey' => '',
    'currentSort' => '',
    'currentDirection' => 'asc',
])

@php
    // TASK 6.4: Header tabel yang jelas dengan sort indicator
    $isSorted = $sortable && $sortKey === $currentSort;
    $nextDirection = $isSorted && $currentDirection === 'asc' ? 'desc' : 'asc';
@endphp

<th {{ $attributes->merge(['class' => 'px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider bg-gray-100 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600']) }}>
    @if($sortable)
        <button 
            type="button"
            onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => $sortKey, 'direction' => $nextDirection]) }}'"
            class="flex items-center gap-1 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
        >
            {{ $slot }}
            @if($isSorted)
                @if($currentDirection === 'asc')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                @endif
            @else
                <svg class="w-4 h-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
            @endif
        </button>
    @else
        {{ $slot }}
    @endif
</th>
