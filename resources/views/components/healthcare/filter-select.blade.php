{{-- 
    Healthcare Filter Select Component
    Usage: <x-healthcare.filter-select name="status" label="Status" :options="$options" />
--}}

@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => '',
    'placeholder' => '-- Pilih --',
    'required' => false,
])

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">
            {{ $label }}
        </label>
    @endif
    <select name="{{ $name }}" id="{{ $name }}" @if ($required) required @endif
        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(old($name, $value) == $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
</div>
