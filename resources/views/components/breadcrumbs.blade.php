{{-- 
    Breadcrumbs Component
    Usage: <x-breadcrumbs :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Current Page']]" />

    Fix 1.12: Mobile breadcrumb — shows active page indicator on small screens (sm:hidden)
    Fix 1.13: Tooltip on hover for breadcrumb items with text longer than 20 characters
--}}

@props(['items' => []])

@if (count($items) > 0)
    @php $lastItem = $items[count($items) - 1]; @endphp

    {{-- Mobile: show only the active (last) breadcrumb item — Fix 1.12 --}}
    <nav class="flex sm:hidden items-center text-xs text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
        <span class="text-gray-700 dark:text-gray-300 font-medium truncate max-w-[200px]">
            {{ $lastItem['label'] }}
        </span>
    </nav>

    {{-- Desktop: full breadcrumb trail with tooltips — Fix 1.12 + 1.13 --}}
    <nav class="hidden sm:flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
        @foreach ($items as $index => $item)
            @if ($index > 0)
                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
            @endif

            {{-- Breadcrumb item with Alpine.js tooltip — Fix 1.13 --}}
            <span
                x-data="{ show: false }"
                class="relative"
                @mouseenter="show = true"
                @mouseleave="show = false"
                title="{{ $item['label'] }}"
            >
                @if (isset($item['url']) && $index < count($items) - 1)
                    <a href="{{ $item['url'] }}" class="hover:text-gray-700 dark:hover:text-gray-300 transition truncate max-w-[180px] inline-block">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-gray-700 dark:text-gray-300 font-medium truncate max-w-[180px] inline-block">
                        {{ $item['label'] }}
                    </span>
                @endif

                {{-- Tooltip: only shown when text length > 20 chars — Fix 1.13 --}}
                @if (strlen($item['label']) > 20)
                    <span
                        x-show="show"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute bottom-full left-0 mb-1 z-50 px-2 py-1 text-xs text-white bg-gray-800 dark:bg-gray-700 rounded shadow-lg whitespace-nowrap pointer-events-none"
                        role="tooltip"
                    >
                        {{ $item['label'] }}
                    </span>
                @endif
            </span>
        @endforeach
    </nav>
@endif
