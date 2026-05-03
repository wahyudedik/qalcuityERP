@props(['totalSteps', 'draftKey' => null, 'showProgress' => true, 'allowStepJump' => false])

{{-- 
    Form Wizard Component
    Wraps form with multi-step wizard functionality
    
    Usage:
    <x-form-wizard :total-steps="3" draft-key="invoice_create">
        <x-wizard-step number="1" title="Informasi Dasar">
            <!-- Step 1 fields -->
        </x-wizard-step>
        
        <x-wizard-step number="2" title="Tambah Items">
            <!-- Step 2 fields -->
        </x-wizard-step>
        
        <x-wizard-step number="3" title="Review & Kirim">
            <!-- Step 3 fields -->
        </x-wizard-step>
        
        <x-slot name="navigation">
            <x-wizard-navigation />
        </x-slot>
    </x-form-wizard>
--}}

@php
    $draftKeyAttr = $draftKey ? "data-draft-key=\"{$draftKey}\"" : '';
@endphp

<form
    {{ $attributes->merge(['class' => 'form-wizard', 'data-wizard' => '', 'data-steps' => $totalSteps, $draftKeyAttr => '']) }}>
    @csrf

    {{-- Progress Bar --}}
    @if ($showProgress)
        <div class="wizard-progress-container mb-6">
            <div class="flex items-center justify-between">
                @for ($i = 1; $i <= $totalSteps; $i++)
                    <div class="flex flex-col items-center flex-1">
                        <div class="wizard-step-indicator w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                            data-step-indicator="{{ $i }}">
                            <span class="step-number text-sm font-semibold">{{ $i }}</span>
                            <svg class="step-check w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="step-label mt-2 text-xs text-center text-gray-600">
                            {{ $slot->where('number', $i)->first()?->attributes['title'] ?? "Step {$i}" }}
                        </div>
                    </div>

                    @if ($i < $totalSteps)
                        <div class="wizard-step-connector flex-1 h-0.5 bg-gray-200 mx-2 mt-[-20px] transition-all duration-300"
                            data-connector="{{ $i }}">
                        </div>
                    @endif
                @endfor
            </div>
        </div>
    @endif

    {{-- Steps Container --}}
    <div class="wizard-steps-container">
        {{ $slot }}
    </div>

    {{-- Navigation --}}
    @isset($navigation)
        <div class="wizard-navigation mt-6 pt-6 border-t border-gray-200">
            {{ $navigation }}
        </div>
    @else
        <x-wizard-navigation />
    @endisset
</form>

@push('styles')
    <style>
        /* Wizard Progress Bar Styles */
        .wizard-step-indicator {
            @apply border-gray-300 bg-white text-gray-600;
        }

        .wizard-step-indicator.active {
            @apply border-blue-500 bg-blue-500 text-white;
        }

        .wizard-step-indicator.completed {
            @apply border-green-500 bg-green-500 text-white;
        }

        .wizard-step-connector.completed {
            @apply bg-green-500;
        }

        .wizard-error {
            @apply border-red-500 focus:ring-red-500;
        }

        .wizard-error-message {
            @apply text-red-500 text-xs mt-1;
        }

        .wizard-save-indicator {
            @apply fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg opacity-0 transition-opacity duration-300 z-50;
        }

        .wizard-save-indicator.show {
            @apply opacity-100;
        }

        /* Step transitions */
        [data-step] {
            @apply transition-all duration-300;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .step-label {
                @apply text-[10px];
            }

            .wizard-step-indicator {
                @apply w-8 h-8;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Initialize wizard when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('[data-wizard]');
            if (form && !form._wizardInitialized) {
                new FormWizard(form, {
                    allowStepJump: {{ $allowStepJump ? 'true' : 'false' }},
                    enableAutoSave: true,
                    autoSaveInterval: 30000
                });
                form._wizardInitialized = true;
            }
        });
    </script>
@endpush
