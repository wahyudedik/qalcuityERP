@props([
    'class' => '',
    'padding' => 'p-6',
    'noPadding' => false,
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm ' . ($noPadding ? '' : $padding) . ' ' . $class]) }}>
    {{ $slot }}
</div>
