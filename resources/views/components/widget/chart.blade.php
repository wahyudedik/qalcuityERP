{{--
    Chart Widget Component

    Menampilkan grafik menggunakan Chart.js dengan fitur:
    - Line chart untuk trends dan time series
    - Bar chart untuk comparisons dan categories
    - Pie/Doughnut chart untuk distributions
    - Responsive chart sizing dan mobile optimization
    - Lazy loading dengan intersection observer
    - Loading skeleton dan error states
    - Data caching support

    Usage:
    <x-widget.chart type="line" :data="$chartData" title="Tren Notifikasi 7 Hari" height="200" />

    Data format:
    $chartData = [
        'labels' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
        'datasets' => [
            [
                'label' => 'Notifikasi',
                'data' => [12, 19, 3, 5, 2, 3, 7],
                'borderColor' => '#3B82F6',
                'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
            ]
        ]
    ];

    @see Task 4.2: Create ChartWidget component using Chart.js
    @see Requirements 7 (Sistem Widget Management)
    @see Requirements 8 (Performance dan Loading Optimization)
--}}

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-gray-200 overflow-hidden']) }}
    x-data="chartWidget({
        chartId: '{{ $chartId }}',
        type: '{{ $validatedType }}',
        data: {{ $chartDataJson }},
        options: {{ $chartOptionsJson }},
        mobileOptions: {{ json_encode(\App\View\Components\Widget\Chart::getMobileOptions()) }},
        height: {{ (int) $height }},
        loading: {{ $loading ? 'true' : 'false' }},
        lazyLoad: {{ $lazyLoad ? 'true' : 'false' }},
        cacheKey: {{ $cacheKey ? "'" . e($cacheKey) . "'" : 'null' }},
        cacheTtl: {{ $cacheTtl }}
    })" role="region" aria-label="{{ $getAriaLabel() }}" :aria-busy="loading.toString()">

    {{-- Widget Header --}}
    @if ($title)
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">{{ $title }}</h3>
        </div>
    @endif

    {{-- Loading Skeleton State --}}
    <div x-show="loading" x-cloak class="p-4" aria-hidden="true">
        <div class="animate-pulse" style="height: {{ $getHeightStyle() }}">
            @if (in_array($validatedType, ['pie', 'doughnut']))
                {{-- Circular chart skeleton --}}
                <div class="flex items-center justify-center h-full">
                    <div class="w-32 h-32 bg-gray-200 rounded-full"></div>
                </div>
            @else
                {{-- Bar/Line chart skeleton --}}
                <div class="flex items-end justify-between h-full gap-2 pt-4 pb-6">
                    <div class="w-full h-3/5 bg-gray-200 rounded"></div>
                    <div class="w-full h-4/5 bg-gray-200 rounded"></div>
                    <div class="w-full h-2/5 bg-gray-200 rounded"></div>
                    <div class="w-full h-3/4 bg-gray-200 rounded"></div>
                    <div class="w-full h-1/2 bg-gray-200 rounded"></div>
                    <div class="w-full h-2/3 bg-gray-200 rounded"></div>
                    <div class="w-full h-3/5 bg-gray-200 rounded"></div>
                </div>
            @endif
            <div class="flex justify-center gap-4 mt-2">
                <div class="h-3 bg-gray-200 rounded w-16"></div>
                <div class="h-3 bg-gray-200 rounded w-16"></div>
            </div>
        </div>
    </div>

    {{-- Error State --}}
    <div x-show="error && !loading" x-cloak class="p-6 text-center" role="alert" aria-live="assertive"
        style="min-height: {{ $getHeightStyle() }}">
        <div class="flex flex-col items-center justify-center h-full">
            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
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
    </div>

    {{-- Chart Canvas --}}
    <div x-show="!loading && !error" class="p-4">
        <div x-ref="chartContainer" class="relative" :style="'height: ' + currentHeight + 'px'">
            <canvas x-ref="canvas" :id="chartId" aria-label="{{ $getAriaLabel() }}" role="img">
                {{-- Fallback text for screen readers --}}
                @if (!empty($data['datasets']))
                    <p>{{ $title ?: 'Data grafik' }}: {{ implode(', ', $data['labels'] ?? []) }}</p>
                @endif
            </canvas>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('chartWidget', (config = {}) => ({
                    chartId: config.chartId || 'chart-' + Math.random().toString(36).substr(2, 9),
                    type: config.type || 'line',
                    data: config.data || {
                        labels: [],
                        datasets: []
                    },
                    options: config.options || {},
                    mobileOptions: config.mobileOptions || {},
                    height: config.height || 200,
                    currentHeight: config.height || 200,
                    loading: config.loading || false,
                    error: false,
                    lazyLoad: config.lazyLoad !== undefined ? config.lazyLoad : true,
                    cacheKey: config.cacheKey || null,
                    cacheTtl: config.cacheTtl || 300,
                    chart: null,
                    observer: null,
                    isVisible: false,
                    isInitialized: false,
                    resizeTimeout: null,

                    init() {
                        if (this.lazyLoad) {
                            this.setupIntersectionObserver();
                        } else {
                            this.$nextTick(() => this.initChart());
                        }

                        // Listen for resize to apply mobile optimizations
                        this.handleResize = this.debounce(() => {
                            this.applyResponsiveOptions();
                        }, 250);
                        window.addEventListener('resize', this.handleResize);

                        // Apply initial responsive height
                        this.applyResponsiveHeight();

                        // Try to load from cache
                        if (this.cacheKey) {
                            this.loadFromCache();
                        }
                    },

                    destroy() {
                        if (this.chart) {
                            this.chart.destroy();
                            this.chart = null;
                        }
                        if (this.observer) {
                            this.observer.disconnect();
                            this.observer = null;
                        }
                        if (this.handleResize) {
                            window.removeEventListener('resize', this.handleResize);
                        }
                    },

                    /**
                     * Setup intersection observer for lazy loading
                     */
                    setupIntersectionObserver() {
                        if (!('IntersectionObserver' in window)) {
                            // Fallback: initialize immediately if IO not supported
                            this.$nextTick(() => this.initChart());
                            return;
                        }

                        this.observer = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting && !this.isInitialized) {
                                    this.isVisible = true;
                                    this.initChart();
                                    this.observer.disconnect();
                                }
                            });
                        }, {
                            rootMargin: '50px',
                            threshold: 0.1
                        });

                        this.$nextTick(() => {
                            if (this.$refs.chartContainer) {
                                this.observer.observe(this.$refs.chartContainer);
                            }
                        });
                    },

                    /**
                     * Initialize Chart.js instance
                     */
                    initChart() {
                        if (this.isInitialized || this.loading || this.error) return;
                        if (!this.$refs.canvas) return;

                        // Check if Chart.js is available
                        if (typeof Chart === 'undefined') {
                            console.warn('Chart.js not loaded yet, retrying...');
                            setTimeout(() => this.initChart(), 500);
                            return;
                        }

                        try {
                            const ctx = this.$refs.canvas.getContext('2d');
                            const mergedOptions = this.getMergedOptions();

                            this.chart = new Chart(ctx, {
                                type: this.type,
                                data: JSON.parse(JSON.stringify(this.data)),
                                options: mergedOptions
                            });

                            this.isInitialized = true;

                            // Save to cache if cache key provided
                            if (this.cacheKey) {
                                this.saveToCache();
                            }

                            this.$dispatch('chart-initialized', {
                                chartId: this.chartId
                            });
                        } catch (e) {
                            console.error('Chart initialization failed:', e);
                            this.error = true;
                        }
                    },

                    /**
                     * Get merged options with responsive adjustments
                     */
                    getMergedOptions() {
                        let options = JSON.parse(JSON.stringify(this.options));

                        if (window.innerWidth < 768) {
                            options = this.deepMerge(options, this.mobileOptions);
                        }

                        return options;
                    },

                    /**
                     * Apply responsive height based on screen size
                     */
                    applyResponsiveHeight() {
                        if (window.innerWidth < 768) {
                            // Reduce height on mobile
                            this.currentHeight = Math.max(Math.round(this.height * 0.75), 120);
                        } else if (window.innerWidth < 1024) {
                            // Slightly reduce on tablet
                            this.currentHeight = Math.max(Math.round(this.height * 0.85), 150);
                        } else {
                            this.currentHeight = this.height;
                        }
                    },

                    /**
                     * Apply responsive options on resize
                     */
                    applyResponsiveOptions() {
                        this.applyResponsiveHeight();

                        if (this.chart) {
                            const mergedOptions = this.getMergedOptions();
                            this.chart.options = Chart.helpers?.merge?.(this.chart.options,
                                mergedOptions) || mergedOptions;
                            this.chart.resize();
                        }
                    },

                    /**
                     * Update chart data dynamically
                     */
                    updateData(newData) {
                        this.data = newData;

                        if (this.chart) {
                            this.chart.data = JSON.parse(JSON.stringify(newData));
                            this.chart.update('none');

                            if (this.cacheKey) {
                                this.saveToCache();
                            }
                        }
                    },

                    /**
                     * Retry loading chart after error
                     */
                    retry() {
                        this.loading = true;
                        this.error = false;
                        this.isInitialized = false;

                        if (this.chart) {
                            this.chart.destroy();
                            this.chart = null;
                        }

                        this.$dispatch('chart-widget-retry', {
                            chartId: this.chartId
                        });

                        setTimeout(() => {
                            this.loading = false;
                            this.$nextTick(() => this.initChart());
                        }, 300);
                    },

                    /**
                     * Load chart data from localStorage cache
                     */
                    loadFromCache() {
                        if (!this.cacheKey) return;

                        try {
                            const cached = localStorage.getItem('chart_cache_' + this.cacheKey);
                            if (cached) {
                                const {
                                    data,
                                    timestamp
                                } = JSON.parse(cached);
                                const age = (Date.now() - timestamp) / 1000;

                                if (age < this.cacheTtl) {
                                    this.data = data;
                                }
                            }
                        } catch (e) {
                            // Cache read failed, use provided data
                        }
                    },

                    /**
                     * Save chart data to localStorage cache
                     */
                    saveToCache() {
                        if (!this.cacheKey) return;

                        try {
                            localStorage.setItem('chart_cache_' + this.cacheKey, JSON.stringify({
                                data: this.data,
                                timestamp: Date.now()
                            }));
                        } catch (e) {
                            // Cache write failed (quota exceeded, etc.)
                        }
                    },

                    /**
                     * Deep merge two objects
                     */
                    deepMerge(target, source) {
                        const result = {
                            ...target
                        };
                        for (const key in source) {
                            if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[
                                    key])) {
                                result[key] = this.deepMerge(result[key] || {}, source[key]);
                            } else {
                                result[key] = source[key];
                            }
                        }
                        return result;
                    },

                    /**
                     * Debounce utility
                     */
                    debounce(fn, delay) {
                        let timer;
                        return (...args) => {
                            clearTimeout(timer);
                            timer = setTimeout(() => fn.apply(this, args), delay);
                        };
                    },

                    /**
                     * Set error state externally
                     */
                    setError(message = null) {
                        this.loading = false;
                        this.error = true;
                    },

                    /**
                     * Set loaded state externally
                     */
                    setLoaded() {
                        this.loading = false;
                        this.error = false;
                    }
                }));
            });
        </script>
    @endpush
@endonce
