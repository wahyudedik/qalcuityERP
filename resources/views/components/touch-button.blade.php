@props([
    'size' => 'md', // sm, md, lg, xl
    'block' => false,
    'touch' => true,
    'iconOnly' => false,
])

{{-- 
    Touch-Friendly Button
    - Minimum 44x44px tap target (WCAG AAA)
    - Responsive sizing
    - Optional block layout on mobile
--}}

@php
    $sizes = [
        'sm' => 'min-h-[40px] min-w-[40px] px-3 py-2 text-xs',
        'md' => 'min-h-[44px] px-4 py-2.5 text-sm',
        'lg' => 'min-h-[48px] px-5 py-3 text-base',
        'xl' => 'min-h-[52px] px-6 py-4 text-lg',
    ];

    $baseClasses =
        'inline-flex items-center justify-center gap-2 font-medium rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';
    $touchClasses = $touch ? ' touch-target btn-touch' : '';
    $blockClasses = $block ? ' w-full' : '';
@endphp

<button
    {{ $attributes->merge(['class' => $baseClasses . $touchClasses . $blockClasses . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . $class]) }}
    type="button">
    @if ($iconOnly)
        <span class="{{ $size === 'sm' ? 'w-4 h-4' : ($size === 'lg' ? 'w-6 h-6' : 'w-5 h-5') }}">
            {{ $slot }}
        </span>
    @else
        {{ $slot }}
    @endif
</button>
