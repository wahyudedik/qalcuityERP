@props(['number', 'title' => ''])

{{-- 
    Wizard Step Component
    Individual step in a form wizard
    
    Usage:
    <x-wizard-step number="1" title="Informasi Dasar">
        <!-- Form fields for this step -->
    </x-wizard-step>
--}}

<div data-step="{{ $number }}" data-step-title="{{ $title }}" class="wizard-step"
    @if ($number == 1) style="display: block;" @else style="display: none;" @endif>
    @if ($title)
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
            @isset($description)
                <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
            @endisset
        </div>
    @endif

    <div class="space-y-5">
        {{ $slot }}
    </div>
</div>
