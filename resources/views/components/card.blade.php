@props([
    'class' => '',
    'padding' => 'p-6',
    'noPadding' => false,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-gray-200 shadow-sm ' . ($noPadding ? '' : $padding) . ' ' . $class]) }}>
    {{ $slot }}
</div>
