{{-- 
    Toast Notification Component
    Usage: <x-toast type="success" message="Data berhasil disimpan!" :duration="4000" />
--}}

@props([
    'type' => 'success', // success, error, warning, info
    'message' => '',
    'duration' => 4000,
])

@php
    $typeClasses = [
        'success' =>
            'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-800 dark:text-green-300',
        'error' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-800 dark:text-red-300',
        'warning' =>
            'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300',
        'info' =>
            'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800 text-purple-800 dark:text-purple-300',
    ];

    $icons = [
        'success' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'error' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'warning' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
        'info' =>
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
    ];
@endphp

<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, {{ $duration }})" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform translate-y-2" class="fixed top-4 right-4 z-50 max-w-md">
    <div class="flex items-start p-4 border rounded-xl shadow-lg {{ $typeClasses[$type] }}">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $icons[$type] !!}
        </svg>
        <p class="text-sm flex-1">{{ $message }}</p>
        <button @click="show = false" class="ml-3 opacity-60 hover:opacity-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>
