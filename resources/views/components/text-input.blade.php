@props(['disabled' => false, 'type' => 'text'])

{{-- TASK 6.3: Input dengan dark mode support dan placeholder informatif --}}
<input 
    type="{{ $type }}"
    @disabled($disabled) 
    {{ $attributes->merge([
        'class' => 'w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm disabled:bg-gray-100 disabled:cursor-not-allowed transition-colors duration-200'
    ]) }}
>
