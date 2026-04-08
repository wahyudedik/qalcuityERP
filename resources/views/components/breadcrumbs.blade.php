{{-- 
    Breadcrumbs Component
    Usage: <x-breadcrumbs :items="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Current Page']]" />
--}}

@props(['items' => []])

@if (count($items) > 0)
    <nav class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
        @foreach ($items as $index => $item)
            @if ($index > 0)
                <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
            @endif

            @if (isset($item['url']) && $index < count($items) - 1)
                <a href="{{ $item['url'] }}" class="hover:text-gray-700 dark:hover:text-gray-300 transition">
                    {{ $item['label'] }}
                </a>
            @else
                <span class="text-gray-700 dark:text-gray-300 font-medium">
                    {{ $item['label'] }}
                </span>
            @endif
        @endforeach
    </nav>
@endif
