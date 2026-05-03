{{-- 
    Healthcare Filter Input Component (Text)
    Usage: <x-healthcare.filter-input name="search" label="Cari" value="" placeholder="..." />
--}}

@props(['name', 'label' => null, 'value' => '', 'placeholder' => '', 'type' => 'text', 'required' => false])

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-xs font-medium text-gray-500 mb-1">
            {{ $label }}
        </label>
    @endif
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}" @if ($required) required @endif
        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
</div>
