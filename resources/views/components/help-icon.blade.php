@props(['topic', 'title' => 'Bantuan', 'position' => 'right'])

{{-- 
    Help Icon Component
    Usage: <x-help-icon topic="customer-selection" />
--}}

<span class="inline-flex items-center" {{ $attributes }}>
    <button type="button"
        class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-full"
        title="{{ $title }}" x-data @click="$dispatch('show-help', { topic: '{{ $topic }}' })"
        aria-label="{{ $title }}">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </button>
</span>
