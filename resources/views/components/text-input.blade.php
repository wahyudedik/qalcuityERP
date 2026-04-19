@props(['disabled' => false, 'type' => 'text'])

{{-- TASK 6.3: Input dengan dark mode support dan placeholder informatif --}}
<input 
    type="{{ $type }}"
    @disabled($disabled) 
    {{ $attributes->merge([
        'class' => 'w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500 dark:focus:border-blue-400 dark:focus:ring-blue-400 rounded-lg shadow-sm disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed transition-colors duration-200'
    ]) }}
>
