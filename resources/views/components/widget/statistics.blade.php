{{--
    Statistics Widget Component

    Menampilkan statistik numerik dengan fitur:
    - Number formatting dengan suffix K, M, B
    - Trend indicators (up/down arrows dengan persentase)
    - Color coding untuk positive/negative values
    - Loading skeleton dan error states
    - Grid layout untuk multiple stat items

    Usage:
    <x-widget.statistics :stats="$stats" title="Ringkasan" />

    Stats format:
    $stats = [
        ['label' => 'Total Notifikasi', 'value' => 1250, 'trend' => 12.5, 'icon' => 'bell'],
        ['label' => 'Belum Dibaca', 'value' => 45, 'trend' => -5.2, 'icon' => 'envelope'],
    ];

    @see Task 4.1: Create StatisticsWidget component
    @see Requirements 7 (Sistem Widget Management)
--}}

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-gray-200 overflow-hidden']) }}
    x-data="statisticsWidget({ loading: {{ $loading ? 'true' : 'false' }}, error: {{ $error ? 'true' : 'false' }} })" role="region" aria-label="{{ $title ? "Statistik: {$title}" : 'Widget Statistik' }}">

    {{-- Widget Header --}}
    @if ($title)
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">{{ $title }}</h3>
        </div>
    @endif

    {{-- Loading Skeleton State --}}
    <div x-show="loading" x-cloak class="p-4" aria-hidden="true">
        <div class="grid {{ $gridClasses }} gap-4">
            @for ($i = 0; $i < max(count($stats), 2); $i++)
                <div class="animate-pulse space-y-3 p-3">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 bg-gray-200 rounded-lg"></div>
                        <div class="h-3 bg-gray-200 rounded w-20"></div>
                    </div>
                    <div class="h-7 bg-gray-200 rounded w-16"></div>
                    <div class="h-4 bg-gray-200 rounded w-14"></div>
                </div>
            @endfor
        </div>
    </div>

    {{-- Error State --}}
    <div x-show="error && !loading" x-cloak class="p-6 text-center" role="alert" aria-live="assertive">
        <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
            </path>
        </svg>
        <p class="text-sm text-gray-500 mb-3">{{ $getErrorMessage() }}</p>
        <button type="button" x-on:click="retry()"
            class="text-blue-600 text-sm hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded px-2 py-1">
            Coba Lagi
        </button>
    </div>

    {{-- Statistics Content --}}
    <div x-show="!loading && !error" class="p-4">
        @if (count($formattedStats) > 0)
            <div class="grid {{ $gridClasses }} gap-4">
                @foreach ($formattedStats as $stat)
                    <div class="relative p-3 rounded-xl bg-gray-50/50 hover:bg-gray-50 transition-colors">
                        {{-- Icon and Label --}}
                        <div class="flex items-center gap-2 mb-2">
                            @if ($stat['icon'])
                                <div
                                    class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                    @include('components.widget.partials.stat-icon', [
                                        'icon' => $stat['icon'],
                                    ])
                                </div>
                            @endif
                            <span class="text-xs font-medium text-gray-500 truncate">{{ $stat['label'] }}</span>
                        </div>

                        {{-- Value --}}
                        <div class="flex items-baseline gap-1">
                            @if ($stat['prefix'])
                                <span class="text-sm text-gray-500">{{ $stat['prefix'] }}</span>
                            @endif
                            <span class="text-2xl font-bold text-gray-900"
                                aria-label="{{ $stat['label'] }}: {{ number_format($stat['value']) }}">
                                {{ $stat['formattedValue'] }}
                            </span>
                            @if ($stat['suffix'])
                                <span class="text-sm text-gray-500">{{ $stat['suffix'] }}</span>
                            @endif
                        </div>

                        {{-- Trend Indicator --}}
                        @if ($stat['trend'] != 0)
                            <div class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $stat['trendColorClass'] }} {{ $stat['trendBgClass'] }}"
                                aria-label="Tren: {{ $stat['formattedTrend'] }}">
                                @if ($stat['trendDirection'] === 'up')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 15l7-7 7 7"></path>
                                    </svg>
                                @elseif ($stat['trendDirection'] === 'down')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                @endif
                                <span>{{ $stat['formattedTrend'] }}</span>
                            </div>
                        @else
                            <div class="mt-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium text-gray-500 bg-gray-50"
                                aria-label="Tren: tidak berubah">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14">
                                    </path>
                                </svg>
                                <span>0%</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6">
                <p class="text-sm text-gray-500">Tidak ada data statistik</p>
            </div>
        @endif
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('statisticsWidget', (config = {}) => ({
                    loading: config.loading || false,
                    error: config.error || false,

                    retry() {
                        this.loading = true;
                        this.error = false;
                        this.$dispatch('statistics-widget-retry');

                        // Fallback: reset loading after timeout if no external handler
                        setTimeout(() => {
                            if (this.loading) {
                                this.loading = false;
                            }
                        }, 5000);
                    },

                    setLoaded() {
                        this.loading = false;
                        this.error = false;
                    },

                    setError() {
                        this.loading = false;
                        this.error = true;
                    }
                }));
            });
        </script>
    @endpush
@endonce
