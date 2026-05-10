@props([
    'label' => '',
    'tooltip' => 'Fitur akan segera tersedia',
    'type' => 'button',
    'class' => '',
])

<span x-data="{ showTooltip: false }" class="relative inline-block">
    @if ($type === 'link')
        <span @mouseenter="showTooltip = true" @mouseleave="showTooltip = false"
            class="text-gray-400 cursor-not-allowed opacity-50 {{ $class }}">
            {{ $label }}{{ $slot }}
        </span>
    @else
        <button disabled @mouseenter="showTooltip = true" @mouseleave="showTooltip = false"
            class="px-4 py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed opacity-50 {{ $class }}">
            {{ $label }}{{ $slot }}
        </button>
    @endif
    <span x-show="showTooltip" x-transition
        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 text-xs text-white bg-gray-800 rounded-lg whitespace-nowrap z-50">
        {{ $tooltip }}
    </span>
</span>
