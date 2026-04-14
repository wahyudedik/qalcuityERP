@props([
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden ' . $class]) }}>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            {{ $slot }}
        </table>
    </div>
</div>
