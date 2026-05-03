{{-- 
    TASK 6.5: Toast Notification — posisi konsisten (top-right), warna sesuai, auto-dismiss 5 detik
    Usage: <x-toast type="success" message="Data berhasil disimpan!" />
--}}

@props([
    'type' => 'success', // success, error, warning, info
    'message' => '',
    'duration' => 5000, // TASK 6.5: Default 5 detik
    'position' => 'top-right', // top-right, top-left, bottom-right, bottom-left
])

@php
    // TASK 6.5: Warna sesuai — hijau=sukses, merah=error, kuning=warning, biru=info
    $typeClasses = [
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'error' => 'bg-red-50 border-red-200 text-red-800',
        'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
        'info' => 'bg-blue-50 border-blue-200 text-blue-800',
    ];

    $icons = [
        'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'error' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
        'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
    ];
    
    // TASK 6.5: Posisi konsisten
    $positionClasses = [
        'top-right' => 'top-4 right-4',
        'top-left' => 'top-4 left-4',
        'bottom-right' => 'bottom-4 right-4',
        'bottom-left' => 'bottom-4 left-4',
    ];
@endphp

<div 
    x-data="{ show: true }" 
    x-show="show" 
    x-init="setTimeout(() => show = false, {{ $duration }})" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0" 
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform translate-y-2" 
    class="fixed {{ $positionClasses[$position] ?? $positionClasses['top-right'] }} z-50 max-w-md w-full mx-4 sm:mx-0"
>
    <div class="flex items-start p-4 border rounded-lg shadow-lg {{ $typeClasses[$type] }}">
        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $icons[$type] !!}
        </svg>
        <p class="text-sm flex-1 leading-relaxed">{{ $message }}</p>
        <button @click="show = false" class="ml-3 opacity-60 hover:opacity-100 transition-opacity p-1 rounded hover:bg-black/5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>
