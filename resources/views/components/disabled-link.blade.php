@props([
    'label' => '',
    'tooltip' => 'Fitur akan segera tersedia',
    'class' => '',
])

<span x-data="{ showTooltip: false }" class="relative inline-block">
    <span @mouseenter="showTooltip = true" @mouseleave="showTooltip = false"
        class="text-gray-400 cursor-not-allowed {{ $class }}">
        {{ $label }}{{ $slot }}
    </span>
    <span x-show="showTooltip" x-transition
        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 text-xs text-white bg-gray-800 rounded-lg whitespace-nowrap z-50">
        {{ $tooltip }}
    </span>
</span>
