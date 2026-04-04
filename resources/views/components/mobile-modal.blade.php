@props([
    'maxHeight' => '90vh',
    'stickyHeader' => true,
    'stickyFooter' => true,
    'fullScreenMobile' => false,
])

{{-- 
    Mobile-Optimized Modal
    - Prevents overflow on small screens
    - Sticky header/footer on mobile
    - Optional full-screen mode on mobile
    - Touch-friendly scrolling
--}}

<div {{ $attributes->merge(['class' => 'modal-content-mobile ' . ($fullScreenMobile ? 'modal-mobile-full' : '')]) }}
    style="max-height: {{ $maxHeight }};">
    @if ($stickyHeader)
        <div class="modal-header-sticky px-4 py-3 sm:px-6">
            {{ $header ?? '' }}
        </div>
    @else
        {{ $header ?? '' }}
    @endif

    <div class="modal-body px-4 py-3 sm:px-6 {{ $stickyHeader ? 'pt-2' : '' }} {{ $stickyFooter ? 'pb-2' : '' }}">
        {{ $slot }}
    </div>

    @if ($stickyFooter && isset($footer))
        <div class="modal-footer-sticky px-4 py-3 sm:px-6">
            {{ $footer }}
        </div>
    @elseif(isset($footer))
        {{ $footer }}
    @endif
</div>
