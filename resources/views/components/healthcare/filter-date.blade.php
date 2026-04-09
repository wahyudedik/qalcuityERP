{{-- 
    Healthcare Filter Date Component
    Usage: <x-healthcare.filter-date name="from" label="Dari Tanggal" value="" />
--}}

@props(['name', 'label' => null, 'value' => '', 'required' => false])

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">
            {{ $label }}
        </label>
    @endif
    <input type="date" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $value) }}"
        @if ($required) required @endif
        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
</div>
