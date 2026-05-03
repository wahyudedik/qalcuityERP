{{-- 
    Skeleton Loading Component
    Usage: <x-skeleton type="card" count="3" />
    Types: card, table, stats, list, form
--}}

@props(['type' => 'card', 'count' => 1])

@php
    $skeletonClass = 'animate-pulse bg-gray-200 rounded';
@endphp

{{-- Stats Skeleton --}}
@if ($type === 'stats')
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @for ($i = 0; $i < $count; $i++)
            <div class="bg-white rounded-2xl p-4 border border-gray-200">
                <div class="{{ $skeletonClass }} h-3 w-20 mb-2"></div>
                <div class="{{ $skeletonClass }} h-8 w-16 mb-1"></div>
                <div class="{{ $skeletonClass }} h-2 w-24"></div>
            </div>
        @endfor
    </div>

    {{-- Table Skeleton --}}
@elseif ($type === 'table')
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        {{-- Table Header --}}
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <div class="flex gap-4">
                <div class="{{ $skeletonClass }} h-4 flex-1"></div>
                <div class="{{ $skeletonClass }} h-4 flex-1"></div>
                <div class="{{ $skeletonClass }} h-4 w-20"></div>
                <div class="{{ $skeletonClass }} h-4 w-20"></div>
            </div>
        </div>

        {{-- Table Rows --}}
        @for ($i = 0; $i < $count; $i++)
            <div class="px-4 py-3 border-b border-gray-100 last:border-0">
                <div class="flex items-center gap-4">
                    <div class="{{ $skeletonClass }} h-9 w-9 rounded-xl"></div>
                    <div class="flex-1 space-y-2">
                        <div class="{{ $skeletonClass }} h-4 w-32"></div>
                        <div class="{{ $skeletonClass }} h-3 w-24"></div>
                    </div>
                    <div class="{{ $skeletonClass }} h-6 w-16 rounded-lg"></div>
                    <div class="{{ $skeletonClass }} h-8 w-8 rounded-lg"></div>
                </div>
            </div>
        @endfor
    </div>

    {{-- Card Skeleton --}}
@elseif ($type === 'card')
    @for ($i = 0; $i < $count; $i++)
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex items-start gap-4 mb-4">
                <div class="{{ $skeletonClass }} h-12 w-12 rounded-xl"></div>
                <div class="flex-1 space-y-2">
                    <div class="{{ $skeletonClass }} h-5 w-3/4"></div>
                    <div class="{{ $skeletonClass }} h-4 w-1/2"></div>
                </div>
            </div>
            <div class="space-y-3">
                <div class="{{ $skeletonClass }} h-4 w-full"></div>
                <div class="{{ $skeletonClass }} h-4 w-5/6"></div>
                <div class="{{ $skeletonClass }} h-4 w-4/6"></div>
            </div>
            <div class="flex gap-2 mt-4 pt-4 border-t border-gray-100">
                <div class="{{ $skeletonClass }} h-9 flex-1 rounded-xl"></div>
                <div class="{{ $skeletonClass }} h-9 flex-1 rounded-xl"></div>
            </div>
        </div>
    @endfor

    {{-- List Skeleton --}}
@elseif ($type === 'list')
    <div class="space-y-3">
        @for ($i = 0; $i < $count; $i++)
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="{{ $skeletonClass }} h-10 w-10 rounded-xl"></div>
                    <div class="flex-1 space-y-2">
                        <div class="{{ $skeletonClass }} h-4 w-2/3"></div>
                        <div class="{{ $skeletonClass }} h-3 w-1/2"></div>
                    </div>
                    <div class="{{ $skeletonClass }} h-6 w-16 rounded-lg"></div>
                </div>
            </div>
        @endfor
    </div>

    {{-- Form Skeleton --}}
@elseif ($type === 'form')
    <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
        @for ($i = 0; $i < $count; $i++)
            <div class="space-y-2">
                <div class="{{ $skeletonClass }} h-4 w-24"></div>
                <div class="{{ $skeletonClass }} h-10 w-full rounded-xl"></div>
            </div>
        @endfor
        <div class="flex gap-3 pt-4">
            <div class="{{ $skeletonClass }} h-10 w-24 rounded-xl"></div>
            <div class="{{ $skeletonClass }} h-10 w-32 rounded-xl"></div>
        </div>
    </div>

    {{-- Text Skeleton --}}
@elseif ($type === 'text')
    <div class="space-y-2">
        @for ($i = 0; $i < $count; $i++)
            <div class="{{ $skeletonClass }} h-4 w-full"></div>
        @endfor
        <div class="{{ $skeletonClass }} h-4 w-3/4"></div>
    </div>

    {{-- Custom Skeleton --}}
@elseif ($type === 'custom')
    {{ $slot }}

@endif
