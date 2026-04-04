@props([
    'class' => '',
    'smooth' => true,
    'indicator' => false,
])

{{-- 
    Responsive Table Container
    - Adds horizontal scrolling on mobile
    - Optional smooth scroll behavior
    - Optional scroll indicator arrow
--}}

<div {{ $attributes->merge(['class' => 'table-responsive ' . ($smooth ? 'scroll-smooth' : '') . ' ' . $class]) }}
    @if ($indicator) data-scroll-indicator @endif>
    {{ $slot }}

    @if ($indicator)
        <div class="table-scroll-indicator pointer-events-none"></div>
    @endif
</div>
