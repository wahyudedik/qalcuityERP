@props([
    'searchToggle' => false,
    'filterUrl' => null,
    'sortUrl' => null,
    'createUrl' => null,
    'createText' => 'Tambah',
    'bulkActions' => null,
    'selectedCount' => 0,
])

{{-- 
    Mobile Toolbar Component
    Sticky toolbar with search, filter, sort, and create actions
    
    Usage:
    <x-mobile-toolbar 
        search-toggle
        filter-url="{{ route('products.index') }}"
        create-url="{{ route('products.create') }}"
        create-text="Produk"
        :selected-count="$selectedCount"
        :bulk-actions="view('products._bulk-actions')"
    />
--}}

<div
    class="md:hidden sticky top-0 z-30 bg-white border-b border-gray-200 shadow-sm">
    {{-- Bulk Actions Bar --}}
    @if ($selectedCount > 0)
        <div class="px-4 py-3 bg-blue-50 border-b border-blue-200">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-blue-700">
                    {{ $selectedCount }} dipilih
                </span>
                @if ($bulkActions)
                    <div class="flex items-center gap-2">
                        @if (is_callable($bulkActions))
                            {!! $bulkActions() !!}
                        @else
                            {!! $bulkActions !!}
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Main Toolbar --}}
    <div class="px-4 py-3">
        <div class="flex items-center justify-between gap-3">
            {{-- Left: Search & Filter --}}
            <div class="flex items-center gap-2">
                @if ($searchToggle)
                    <button type="button" onclick="document.getElementById('mobile-search').classList.toggle('hidden')"
                        class="p-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition min-w-[44px] min-h-[44px] flex items-center justify-center"
                        aria-label="Toggle search">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                @endif

                @if ($filterUrl)
                    <a href="{{ $filterUrl }}"
                        class="p-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition min-w-[44px] min-h-[44px] flex items-center justify-center"
                        aria-label="Filter">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                    </a>
                @endif

                @if ($sortUrl)
                    <a href="{{ $sortUrl }}"
                        class="p-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition min-w-[44px] min-h-[44px] flex items-center justify-center"
                        aria-label="Sort">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                        </svg>
                    </a>
                @endif
            </div>

            {{-- Right: Create Button --}}
            @if ($createUrl)
                <a href="{{ $createUrl }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition shadow-sm min-h-[44px]"
                    role="button">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>{{ $createText }}</span>
                </a>
            @endif
        </div>

        {{-- Search Bar (Toggleable) --}}
        @if ($searchToggle)
            <div id="mobile-search" class="hidden mt-3">
                <form method="GET" class="flex gap-2">
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari..."
                        class="flex-1 px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[44px]"
                        aria-label="Search input">
                    <button type="submit"
                        class="px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition min-h-[44px]">
                        Cari
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
