@props(['topic', 'field' => null])

{{-- 
    Help Form Field Component
    Wraps form field with help icon
    
    Usage:
    <x-help-field topic="customer-selection" field="customer_id">
        <label for="customer_id">Customer</label>
        <select id="customer_id" name="customer_id">...</select>
    </x-help-field>
--}}

<div class="help-field-wrapper" data-help-topic="{{ $topic }}" {{ $attributes }}>
    <div class="flex items-center gap-2 mb-1">
        {{ $slot }}

        @if ($topic)
            <x-help-icon :topic="$topic" class="flex-shrink-0" />
        @endif
    </div>

    @if ($field)
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" id="{{ $field }}-help">
            Klik ikon <span class="text-blue-600">❓</span> untuk bantuan
        </p>
    @endif
</div>
