@props([
    'label' => '',
    'name' => '',
    'required' => false,
    'error' => null,
    'help' => '',
    'type' => 'text',
])

@php
    // TASK 6.3: Form group dengan label jelas, error per field, placeholder informatif
    $errorMessage = $error ?? $errors->first($name);
    $hasError = !empty($errorMessage);
@endphp

<div {{ $attributes->merge(['class' => 'form-group mb-4']) }}>
    @if($label)
        <x-input-label :for="$name" :required="$required">
            {{ $label }}
        </x-input-label>
    @endif
    
    <div class="relative">
        {{ $slot }}
    </div>
    
    @if($hasError)
        <x-input-error :messages="$errorMessage" class="mt-1" />
    @endif
    
    @if($help && !$hasError)
        <p class="mt-1 text-xs text-gray-500">
            {{ $help }}
        </p>
    @endif
</div>
