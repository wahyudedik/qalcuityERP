@props([
    'closeable' => true,
])

{{-- TASK 6.6: Modal header dengan tombol X untuk close --}}
<div {{ $attributes->merge(['class' => 'flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-slate-700']) }}>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
        {{ $slot }}
    </h3>
    
    @if($closeable)
        <button 
            type="button"
            @click="show = false"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700"
            aria-label="Tutup"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
