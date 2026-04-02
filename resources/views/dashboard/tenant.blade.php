<x-app-layout>
    <x-slot name="title">Dashboard — Qalcuity ERP</x-slot>
    <x-slot name="header">Dashboard</x-slot>

    @push('head')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
        <style>
            .widget-item {
                transition: transform 0.2s, box-shadow 0.2s;
            }

            .widget-item.sortable-ghost {
                opacity: 0.4;
            }

            .widget-item.sortable-drag {
                transform: scale(1.02);
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
                z-index: 50;
            }

            .widget-handle {
                cursor: grab;
            }

            .widget-handle:active {
                cursor: grabbing;
            }
        </style>
    @endpush

    {{-- Offline sync status --}}
    <x-offline-sync-status />

    {{-- Mobile Field-Mode Banner (shown on mobile viewports, dismissible) --}}
    <div id="mobile-field-banner" class="mb-4 rounded-2xl overflow-hidden" style="display:none;">
        <div style="background: linear-gradient(135deg, #4f46e5 0%, #2563eb 100%); padding: 14px 16px;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:10px;">
                <div style="flex:1; min-width:0;">
                    <p style="color:#fff; font-size:13px; font-weight:500; line-height:1.5; margin:0;">
                        📱 Anda menggunakan perangkat mobile. Beralih ke <strong>Mode Lapangan</strong> untuk pengalaman
                        yang lebih baik.
                    </p>
                    <a href="{{ route('mobile.hub') }}"
                        style="display:inline-block; margin-top:8px; padding:6px 14px; background:rgba(255,255,255,0.2); color:#fff; border:1px solid rgba(255,255,255,0.35); border-radius:10px; font-size:12px; font-weight:600; text-decoration:none; backdrop-filter:blur(4px);">
                        🚀 Buka Mode Lapangan
                    </a>
                </div>
                <button onclick="dismissMobileFieldBanner()" aria-label="Tutup"
                    style="flex-shrink:0; width:28px; height:28px; background:rgba(255,255,255,0.15); border:none; border-radius:8px; color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.15s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <script>
        (function() {
            var BANNER_KEY = 'qalcuity_mobile_banner_dismissed';
            var banner = document.getElementById('mobile-field-banner');
            if (banner && window.innerWidth < 1024 && !localStorage.getItem(BANNER_KEY)) {
                banner.style.display = 'block';
            }
            window.dismissMobileFieldBanner = function() {
                localStorage.setItem(BANNER_KEY, '1');
                if (banner) banner.style.display = 'none';
            };
        })();
    </script>

    {{-- Greeting + Customize button --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Selamat datang, {{ auth()->user()->name }}
                👋
            </h2>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <button onclick="document.getElementById('widgetModal').classList.remove('hidden')"
            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-500 dark:text-slate-400 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg hover:bg-gray-50 dark:hover:bg-white/10 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            Kustomisasi
        </button>
    </div>

    {{-- Setup Checklist — tampil jika ada step yang belum selesai --}}
    @php
        $tenant = auth()->user()->tenant;
        $checkSteps = [
            'profile' => [
                'label' => 'Lengkapi profil perusahaan',
                'done' => !empty($tenant?->phone) && !empty($tenant?->address),
                'url' => route('company-profile.index'),
                'icon' => '🏢',
            ],
            'coa' => [
                'label' => 'Load Chart of Accounts',
                'done' => \App\Models\ChartOfAccount::where('tenant_id', $tenant?->id)->count() >= 10,
                'url' => route('settings.accounting') . '?tab=coa',
                'icon' => '📊',
            ],
            'warehouse' => [
                'label' => 'Tambah gudang pertama',
                'done' => \App\Models\Warehouse::where('tenant_id', $tenant?->id)->exists(),
                'url' => route('warehouses.index'),
                'icon' => '🏭',
            ],
            'product' => [
                'label' => 'Tambah produk pertama',
                'done' => \App\Models\Product::where('tenant_id', $tenant?->id)->exists(),
                'url' => route('products.index'),
                'icon' => '📦',
            ],
            'customer' => [
                'label' => 'Tambah customer pertama',
                'done' => \App\Models\Customer::where('tenant_id', $tenant?->id)->exists(),
                'url' => route('customers.index'),
                'icon' => '👤',
            ],
            'so' => [
                'label' => 'Buat Sales Order pertama',
                'done' => \App\Models\SalesOrder::where('tenant_id', $tenant?->id)->exists(),
                'url' => route('sales.create'),
                'icon' => '🧾',
            ],
        ];
        $doneCount = collect($checkSteps)->where('done', true)->count();
        $totalSteps = count($checkSteps);
        $allDone = $doneCount === $totalSteps;
        $pct = $totalSteps > 0 ? round(($doneCount / $totalSteps) * 100) : 0;
    @endphp
    @if (!$allDone && $tenant)
        <div class="mb-6 bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5"
            id="setup-checklist">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-500/20 flex items-center justify-center text-lg">🚀</div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Setup Bisnis Anda</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">{{ $doneCount }}/{{ $totalSteps }}
                            langkah selesai</p>
                    </div>
                </div>
                <button onclick="document.getElementById('setup-checklist').remove()"
                    class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-white"
                    title="Sembunyikan">✕</button>
            </div>
            <div class="w-full h-2 bg-gray-100 dark:bg-white/10 rounded-full mb-4 overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 {{ $pct >= 100 ? 'bg-green-500' : ($pct >= 50 ? 'bg-blue-500' : 'bg-amber-500') }}"
                    style="width:{{ $pct }}%"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach ($checkSteps as $step)
                    <a href="{{ $step['done'] ? '#' : $step['url'] }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                    {{ $step['done']
                        ? 'bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20'
                        : 'bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:border-blue-300 dark:hover:border-blue-500/40 hover:bg-blue-50 dark:hover:bg-blue-500/10' }}">
                        <span class="text-lg shrink-0">{{ $step['done'] ? '✅' : $step['icon'] }}</span>
                        <span
                            class="text-sm {{ $step['done'] ? 'text-green-700 dark:text-green-400 line-through' : 'text-gray-700 dark:text-slate-300 font-medium' }}">
                            {{ $step['label'] }}
                        </span>
                        @if (!$step['done'])
                            <svg class="w-4 h-4 ml-auto text-gray-400 dark:text-slate-500 shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ═══════════ Dynamic Widget Grid ═══════════ --}}
    <div id="widget-grid" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach (collect($userWidgets)->where('visible', true)->sortBy('order') as $w)
            @if (isset($registry[$w['key']]))
                @php
                    $meta = $registry[$w['key']];
                    $colClass = match ((int) $meta['cols']) {
                        1 => 'col-span-1',
                        2 => 'col-span-2',
                        4 => 'col-span-2 lg:col-span-4',
                        default => 'col-span-1',
                    };
                    $partial = 'dashboard.widgets.' . str_replace('_', '-', $w['key']);
                @endphp
                <div class="widget-item {{ $colClass }} relative group" data-key="{{ $w['key'] }}">
                    {{-- Drag handle --}}
                    <div
                        class="widget-handle absolute -top-1.5 left-1/2 -translate-x-1/2 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <div
                            class="flex items-center gap-0.5 bg-gray-200 dark:bg-slate-700 rounded-full px-2.5 py-0.5 shadow-sm">
                            <svg class="w-3 h-3 text-gray-400 dark:text-slate-500" viewBox="0 0 24 24"
                                fill="currentColor">
                                <circle cx="9" cy="6" r="1.5" />
                                <circle cx="15" cy="6" r="1.5" />
                                <circle cx="9" cy="12" r="1.5" />
                                <circle cx="15" cy="12" r="1.5" />
                                <circle cx="9" cy="18" r="1.5" />
                                <circle cx="15" cy="18" r="1.5" />
                            </svg>
                        </div>
                    </div>
                    @include($partial, ['data' => $widgetData[$w['key']] ?? []])
                </div>
            @endif
        @endforeach
    </div>

    {{-- ═══════════ Customize Widget Modal ═══════════ --}}
    <div id="widgetModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-lg max-h-[80vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Kustomisasi Dashboard</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Pilih widget yang ingin ditampilkan.
                        Drag untuk mengubah urutan.</p>
                </div>
                <button onclick="document.getElementById('widgetModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="overflow-y-auto flex-1 px-6 py-4 space-y-2" id="widget-toggle-list">
                @foreach ($availableKeys as $key)
                    @if (isset($registry[$key]))
                        @php $meta = $registry[$key]; @endphp
                        <label
                            class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 cursor-pointer hover:bg-gray-100 dark:hover:bg-white/10 transition">
                            <input type="checkbox"
                                class="widget-toggle rounded border-gray-300 dark:border-white/20 text-blue-600"
                                data-widget-key="{{ $key }}"
                                {{ collect($userWidgets)->where('key', $key)->where('visible', true)->isNotEmpty() ? 'checked' : '' }}>
                            <div
                                class="w-8 h-8 rounded-lg {{ $meta['icon_bg'] }} flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 {{ $meta['icon_color'] }}" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="{{ $meta['icon'] }}" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $meta['title'] }}</p>
                                <p class="text-xs text-gray-400 dark:text-slate-500">
                                    {{ match ((int) $meta['cols']) {1 => 'Kecil',2 => 'Sedang',4 => 'Lebar penuh',default => ''} }}
                                </p>
                            </div>
                        </label>
                    @endif
                @endforeach
            </div>
            <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-white/10">
                <button onclick="resetWidgets()"
                    class="text-xs text-red-400 hover:text-red-300 font-medium transition">Reset ke Default</button>
                <div class="flex gap-2">
                    <button onclick="document.getElementById('widgetModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 transition">Batal</button>
                    <button onclick="saveWidgetConfig()"
                        class="px-4 py-2 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium transition"
                        id="btn-save-widgets">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // ── Constants ──────────────────────────────────────────────────
            const CSRF = '{{ csrf_token() }}';
            const SAVE_URL = "{{ route('dashboard.widgets.save') }}";
            const RESET_URL = "{{ route('dashboard.widgets.reset') }}";
            const REFRESH_URL = "{{ route('dashboard.refresh-insights') }}";
            const ACK_URL_BASE = "{{ url('/dashboard/anomalies') }}";

            // ── Chart global defaults ─────────────────────────────────────
            const isDark = document.getElementById('html-root')?.classList.contains('dark');
            window._chartDefaults = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            };
            window._chartGridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
            window._chartTickColor = isDark ? '#94a3b8' : '#6b7280';
            window._chartTickFont = {
                size: 10,
                family: 'Inter'
            };

            // ── SortableJS — drag-and-drop reorder ────────────────────────
            const grid = document.getElementById('widget-grid');
            if (grid && typeof Sortable !== 'undefined') {
                Sortable.create(grid, {
                    handle: '.widget-handle',
                    animation: 200,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onEnd: function() {
                        autoSaveOrder();
                    }
                });
            }

            function autoSaveOrder() {
                const items = grid.querySelectorAll('.widget-item');
                const widgets = [];
                items.forEach((el, i) => {
                    widgets.push({
                        key: el.dataset.key,
                        order: i,
                        visible: true
                    });
                });
                // Also include hidden widgets
                document.querySelectorAll('.widget-toggle').forEach(cb => {
                    const key = cb.dataset.widgetKey;
                    if (!cb.checked && !widgets.find(w => w.key === key)) {
                        widgets.push({
                            key,
                            order: 999,
                            visible: false
                        });
                    }
                });
                fetch(SAVE_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        widgets
                    }),
                }).catch(e => console.error('Auto-save failed', e));
            }

            // ── Widget toggle save ────────────────────────────────────────
            async function saveWidgetConfig() {
                const btn = document.getElementById('btn-save-widgets');
                btn.disabled = true;
                btn.textContent = 'Menyimpan...';

                const items = grid.querySelectorAll('.widget-item');
                const visibleKeys = new Set();
                items.forEach(el => visibleKeys.add(el.dataset.key));

                const widgets = [];
                let order = 0;

                // Visible widgets in current order
                items.forEach(el => {
                    widgets.push({
                        key: el.dataset.key,
                        order: order++,
                        visible: true
                    });
                });

                // Toggled on/off widgets from modal
                document.querySelectorAll('.widget-toggle').forEach(cb => {
                    const key = cb.dataset.widgetKey;
                    if (cb.checked && !visibleKeys.has(key)) {
                        widgets.push({
                            key,
                            order: order++,
                            visible: true
                        });
                    } else if (!cb.checked) {
                        // Remove from visible if exists, add as hidden
                        const idx = widgets.findIndex(w => w.key === key);
                        if (idx !== -1) widgets[idx].visible = false;
                        else widgets.push({
                            key,
                            order: 999,
                            visible: false
                        });
                    }
                });

                try {
                    await fetch(SAVE_URL, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            widgets
                        }),
                    });
                    window.location.reload();
                } catch (e) {
                    console.error('Save failed', e);
                    btn.disabled = false;
                    btn.textContent = 'Simpan';
                }
            }

            async function resetWidgets() {
                if (!confirm('Reset dashboard ke layout default untuk role Anda?')) return;
                try {
                    await fetch(RESET_URL, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                    });
                    window.location.reload();
                } catch (e) {
                    console.error('Reset failed', e);
                }
            }

            // ── AI Insight Refresh ────────────────────────────────────────
            async function refreshDashboardInsights() {
                const btn = document.getElementById('btn-refresh-insights');
                const icon = document.getElementById('refresh-icon');
                if (!btn) return;

                btn.disabled = true;
                icon.classList.add('animate-spin');

                try {
                    const res = await fetch(REFRESH_URL, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                    });
                    const data = await res.json();

                    const grid = document.getElementById('insights-grid');
                    if (grid && data.insights?.length) {
                        grid.innerHTML = data.insights.map(insight => insightCard(insight)).join('');
                    }

                    const list = document.getElementById('anomaly-list');
                    if (list && data.anomalies?.length) {
                        list.innerHTML = data.anomalies.map(a => anomalyRow(a)).join('');
                        document.getElementById('anomaly-section')?.classList.remove('hidden');
                    } else if (list && !data.anomalies?.length) {
                        document.getElementById('anomaly-section')?.classList.add('hidden');
                    }

                    const ts = document.getElementById('insights-updated-at');
                    if (ts) ts.textContent = `— diperbarui pukul ${data.updated_at}`;
                } catch (e) {
                    console.error('Refresh insights failed', e);
                } finally {
                    btn.disabled = false;
                    icon.classList.remove('animate-spin');
                }
            }

            async function acknowledgeAnomaly(id, btn) {
                btn.disabled = true;
                btn.textContent = '...';
                try {
                    await fetch(`${ACK_URL_BASE}/${id}/acknowledge`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                    });
                    const el = document.getElementById(`anomaly-${id}`);
                    if (el) {
                        el.style.opacity = '0';
                        el.style.transition = 'opacity 0.3s';
                        setTimeout(() => el.remove(), 300);
                    }
                } catch (e) {
                    btn.disabled = false;
                    btn.textContent = '✓ Tinjau';
                }
            }

            function insightCard(insight) {
                const borders = {
                    critical: 'border-red-500/40 bg-red-500/5',
                    warning: 'border-yellow-500/40 bg-yellow-500/5',
                    info: 'border-blue-500/20 bg-blue-500/5'
                };
                const badges = {
                    critical: 'bg-red-500/20 text-red-400',
                    warning: 'bg-yellow-500/20 text-yellow-400',
                    info: 'bg-blue-500/20 text-blue-400'
                };
                const labels = {
                    critical: 'Kritis',
                    warning: 'Perhatian',
                    info: 'Info'
                };
                const sev = insight.severity || 'info';
                const action = insight.action ?
                    `<a href="/chat?q=${encodeURIComponent(insight.action)}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium mt-auto">Tanya AI → ${insight.action}</a>` :
                    '';
                return `<div class="rounded-xl border ${borders[sev] || borders.info} p-4 flex flex-col gap-2">
            <div class="flex items-start justify-between gap-2">
                <p class="text-sm font-semibold text-gray-900 dark:text-white leading-snug">${insight.title}</p>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full shrink-0 ${badges[sev] || badges.info}">${labels[sev] || 'Info'}</span>
            </div>
            <p class="text-xs text-gray-500 dark:text-slate-400 leading-relaxed">${insight.body}</p>
            ${action}
        </div>`;
            }

            function anomalyRow(a) {
                const borders = {
                    critical: 'border-red-500/40 bg-red-500/5',
                    warning: 'border-yellow-500/40 bg-yellow-500/5',
                    info: 'border-orange-500/20 bg-orange-500/5'
                };
                const badges = {
                    critical: 'bg-red-500/20 text-red-400',
                    warning: 'bg-yellow-500/20 text-yellow-400',
                    info: 'bg-orange-500/20 text-orange-400'
                };
                const icons = {
                    critical: 'text-red-400',
                    warning: 'text-yellow-400',
                    info: 'text-orange-400'
                };
                const labels = {
                    critical: 'Kritis',
                    warning: 'Perhatian',
                    info: 'Info'
                };
                const sev = a.severity || 'info';
                return `<div class="rounded-xl border ${borders[sev] || borders.info} p-3.5 flex items-start gap-3" id="anomaly-${a.id}">
            <svg class="w-4 h-4 ${icons[sev]} shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">${a.title}</p>
                    <span class="text-xs font-medium px-1.5 py-0.5 rounded-full shrink-0 ${badges[sev] || badges.info}">${labels[sev] || 'Info'}</span>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400 leading-relaxed">${a.description}</p>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">${a.age}</p>
            </div>
            <button onclick="acknowledgeAnomaly(${a.id}, this)" class="text-xs text-gray-400 hover:text-green-400 transition shrink-0 font-medium" title="Tandai sudah ditinjau">✓ Tinjau</button>
        </div>`;
            }

            // ── Init chart widgets after DOM ready ─────────────────────────
            requestAnimationFrame(() => setTimeout(() => {
                document.dispatchEvent(new Event('widgets-ready'));
            }, 50));

            // ── Offline: cache dashboard stats for offline viewing ─────────
            if (window.ErpOffline) {
                const statsEls = document.querySelectorAll('[data-stat-key]');
                const statsData = {};
                statsEls.forEach(el => {
                    statsData[el.dataset.statKey] = el.textContent.trim();
                });
                if (Object.keys(statsData).length > 0) {
                    window.ErpOffline.cacheData('dashboard:stats', 'dashboard', statsData);
                }
            }
        </script>
    @endpush
</x-app-layout>
