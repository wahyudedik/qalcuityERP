{{--
    Responsive Grid Layout Component

    Menyediakan grid layout responsif yang menyesuaikan dengan breakpoint:
    - Mobile (<768px): single column stacked
    - Tablet (768-1024px): configurable columns
    - Desktop (>1024px): full multi-column layout

    Usage (percentage columns):
    <x-layout.responsive-grid :columns="[60, 40]" gap="md">
        <x-slot:column1>Main content (60%)</x-slot:column1>
        <x-slot:column2>Sidebar (40%)</x-slot:column2>
    </x-layout.responsive-grid>

    Usage (auto-grid):
    <x-layout.responsive-grid
        :columns="['auto']"
        :breakpoints="['mobile' => ['columns' => 1], 'tablet' => ['columns' => 2], 'desktop' => ['columns' => 4]]"
        gap="lg">
        {{ $slot }}
    </x-layout.responsive-grid>

    @see Requirements 6 (Responsive Layout Universal)
    @see Design Document: Layout Engine
--}}

<div x-data="responsiveGrid()"
    {{ $attributes->merge([
        'class' => trim($gridClasses . ' ' . $gapClasses),
        'role' => $getRole(),
    ]) }}
    @if ($getAriaLabel()) aria-label="{{ $getAriaLabel() }}" @endif
    x-on:resize.window.debounce.150ms="updateBreakpoint()">
    @if ($isAutoGrid)
        {{-- Auto-grid mode: render slot content directly in CSS Grid --}}
        {{ $slot }}
    @else
        {{-- Percentage-based columns: render named slots or default slot --}}
        @foreach ($processedColumns as $index => $widthClass)
            @php
                $slotName = 'column' . ($index + 1);
                $columnNumber = $index + 1;
                $totalColumns = count($processedColumns);
            @endphp
            <div class="w-full {{ $widthClass }} min-w-0" role="group"
                aria-label="Kolom {{ $columnNumber }} dari {{ $totalColumns }}"
                @if ($mobileStack === 'stack' && $index > 0) x-show="$store.layout?.breakpoint !== 'mobile' || showAllColumns"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0" @endif>
                @if (isset(${$slotName}) && ${$slotName}->isNotEmpty())
                    {{ ${$slotName} }}
                @elseif($loop->first)
                    {{ $slot }}
                @endif
            </div>
        @endforeach

        {{-- Mobile toggle button for collapsed columns --}}
        @if ($mobileStack === 'stack' && count($processedColumns) > 1)
            <div class="w-full lg:hidden mt-2" x-show="$store.layout?.breakpoint === 'mobile' && !showAllColumns">
                <button type="button"
                    class="w-full py-2 px-4 text-sm text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    x-on:click="showAllColumns = true" aria-expanded="false"
                    x-bind:aria-expanded="showAllColumns.toString()" aria-controls="responsive-grid-columns">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                        Tampilkan lebih banyak
                    </span>
                </button>
            </div>
        @endif
    @endif
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                // Register layout store if not already registered
                if (!Alpine.store('layout')) {
                    Alpine.store('layout', {
                        breakpoint: 'desktop',
                        sidebarVisible: true,
                        sidebarCollapsed: false,

                        init() {
                            this.updateBreakpoint();
                        },

                        updateBreakpoint() {
                            const width = window.innerWidth;
                            if (width < 768) this.breakpoint = 'mobile';
                            else if (width < 1024) this.breakpoint = 'tablet';
                            else this.breakpoint = 'desktop';
                        }
                    });
                }

                // Register responsiveGrid component
                Alpine.data('responsiveGrid', () => ({
                    showAllColumns: false,

                    init() {
                        this.updateBreakpoint();
                    },

                    updateBreakpoint() {
                        if (Alpine.store('layout')) {
                            Alpine.store('layout').updateBreakpoint();
                        }
                        // Reset column visibility on larger screens
                        if (window.innerWidth >= 1024) {
                            this.showAllColumns = false;
                        }
                    }
                }));
            });
        </script>
    @endpush
@endonce
