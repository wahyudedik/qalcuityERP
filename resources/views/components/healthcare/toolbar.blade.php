{{-- 
    Healthcare Toolbar Component
    Usage: <x-healthcare.toolbar> with slots for filters and actions
--}}

@props([
    'title' => null, // Optional toolbar title
    'collapsible' => false, // Whether filters can be collapsed
    'defaultCollapsed' => false, // Start collapsed or not
])

<div class="bg-white rounded-2xl border border-gray-200 mb-4">
    @if ($title)
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
        </div>
    @endif

    <div class="p-4">
        {{-- Filters Slot --}}
        @if (isset($filters))
            <div class="space-y-4">
                {{ $filters }}
            </div>
        @endif

        {{-- Actions Slot (buttons) --}}
        @if (isset($actions))
            <div
                class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mt-4 pt-4 border-t border-gray-200">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
