@props(['paginator', 'simple' => false])

{{-- 
    Mobile Pagination Component
    Touch-friendly pagination for mobile devices
    
    Usage:
    <x-mobile-pagination :paginator="$items" />
--}}

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="md:hidden">
        <div class="flex items-center justify-between gap-3 py-4">
            {{-- Previous Button --}}
            @if ($paginator->onFirstPage())
                <span
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-400 bg-gray-50 cursor-not-allowed min-h-[44px]"
                    aria-disabled="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-700 bg-white hover:bg-gray-50 transition min-h-[44px]"
                    aria-label="Previous page">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>Sebelumnya</span>
                </a>
            @endif

            {{-- Page Indicator --}}
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600">
                    Halaman
                </span>
                <span
                    class="px-3 py-2 rounded-lg bg-blue-50 text-sm font-semibold text-blue-600 min-w-[44px] text-center">
                    {{ $paginator->currentPage() }}
                </span>
                <span class="text-sm text-gray-600">
                    dari {{ $paginator->lastPage() }}
                </span>
            </div>

            {{-- Next Button --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-700 bg-white hover:bg-gray-50 transition min-h-[44px]"
                    aria-label="Next page">
                    <span>Selanjutnya</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-400 bg-gray-50 cursor-not-allowed min-h-[44px]"
                    aria-disabled="true">
                    <span>Selanjutnya</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>

        {{-- Page Info --}}
        <div class="text-center text-xs text-gray-500 pb-2">
            Menampilkan {{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }}
            dari {{ $paginator->total() }} data
        </div>
    </nav>
@endif
