@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])

@php
// TASK 6.7: Dropdown dengan posisi benar, tidak terpotong di tepi layar
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    'bottom-left' => 'ltr:origin-bottom-left rtl:origin-bottom-right start-0 bottom-full mb-2',
    'bottom-right' => 'ltr:origin-bottom-right rtl:origin-bottom-left end-0 bottom-full mb-2',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    '56' => 'w-56',
    '64' => 'w-64',
    '72' => 'w-72',
    'auto' => 'w-auto min-w-[12rem]',
    default => $width,
};
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    {{-- TASK 6.7: Dropdown dengan z-index tinggi dan max-height untuk mobile --}}
    <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute z-50 mt-2 {{ $width }} rounded-lg shadow-lg {{ $alignmentClasses }} max-h-[80vh] overflow-y-auto"
            style="display: none;"
            @click="open = false"
            x-cloak>
        <div class="rounded-lg ring-1 ring-black/5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
