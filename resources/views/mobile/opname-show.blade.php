<x-app-layout>
    @section('title', 'Opname — ' . $opname->number)

    @php
        $s = $opname;
        $totalItems = $items->count();
        $countedItems = $items->whereNotNull('actual_qty')->count();
        $progressPct = $totalItems > 0 ? round(($countedItems / $totalItems) * 100) : 0;
        $isCompleted = $s->status === 'completed';
    @endphp

    {{-- ── Page ─────────────────────────────────────────────────────────── --}}
    <div class="min-h-screen bg-gray-950 pb-36">

        {{-- ── Sticky header ───────────────────────────────────────────── --}}
        <div class="sticky top-0 z-20 bg-gray-900/95 backdrop-blur border-b border-white/10">
            <div class="px-4 py-3 flex items-center gap-3">
                <a href="{{ route('mobile.opname') }}"
                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 active:scale-95 transition touch-manipulation flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div class="flex-1 min-w-0">
                    <h1 class="text-base font-bold text-white truncate">{{ $s->number }}</h1>
                    <p class="text-xs text-slate-400">{{ $s->warehouse->name ?? '-' }} ·
                        {{ $s->opname_date->format('d/m/Y') }}</p>
                </div>
                @php
                    $hbg = match ($s->status) {
                        'in_progress' => 'bg-blue-500/20 text-blue-400',
                        'completed' => 'bg-emerald-500/20 text-emerald-400',
                        default => 'bg-gray-500/20 text-gray-400',
                    };
                    $hlbl = match ($s->status) {
                        'in_progress' => 'Aktif',
                        'completed' => 'Selesai',
                        default => 'Draft',
                    };
                @endphp
                <span
                    class="flex-shrink-0 px-2.5 py-1 rounded-full text-xs font-medium {{ $hbg }}">{{ $hlbl }}</span>
            </div>

            {{-- ── Progress strip ───────────────────────────────────────── --}}
            <div class="px-4 pb-3">
                <div class="flex justify-between text-xs mb-1.5">
                    <span class="text-slate-400 font-medium">Progress Pencacahan</span>
                    <span class="font-bold {{ $progressPct === 100 ? 'text-emerald-400' : 'text-white' }}">
                        {{ $countedItems }} / {{ $totalItems }} item &nbsp;·&nbsp; {{ $progressPct }}%
                    </span>
                </div>
                <div class="w-full h-3 bg-white/5 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700
                    {{ $progressPct === 100 ? 'bg-emerald-500' : 'bg-blue-500' }}"
                        style="width: {{ $progressPct }}%">
                    </div>
                </div>
            </div>
        </div>

        <div class="px-4 pt-4 space-y-2">

            {{-- ── Barcode Scanner ─────────────────────────────────────── --}}
            @if (!$isCompleted)
                <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Scan Barcode Produk
                    </p>
                    <x-barcode-scanner on-scan="onOpnameScan" button-label="Buka Kamera Scan"
                        button-class="w-full justify-center h-14 text-base rounded-2xl"
                        input-placeholder="Ketik SKU atau barcode produk..." />
                </div>
            @endif

            {{-- ── Item filter tabs ─────────────────────────────────────── --}}
            <div class="flex gap-2 py-1" id="filter-tabs">
                <button onclick="filterItems('all')" id="tab-all"
                    class="filter-tab active-tab flex-1 h-9 rounded-xl text-xs font-semibold transition touch-manipulation">Semua
                    ({{ $totalItems }})</button>
                <button onclick="filterItems('uncounted')" id="tab-uncounted"
                    class="filter-tab flex-1 h-9 rounded-xl text-xs font-semibold transition touch-manipulation">Belum
                    Dihitung</button>
                <button onclick="filterItems('mismatch')" id="tab-mismatch"
                    class="filter-tab flex-1 h-9 rounded-xl text-xs font-semibold transition touch-manipulation">Selisih</button>
            </div>

            {{-- ── Item cards ───────────────────────────────────────────── --}}
            @forelse($items as $item)
                @php
                    $actualQty = $item->actual_qty;
                    $systemQty = $item->system_qty;
                    $isCounted = $actualQty !== null;
                    $isMatch = $isCounted && (int) $actualQty === (int) $systemQty;
                    $isMismatch = $isCounted && (int) $actualQty !== (int) $systemQty;

                    $cardBorder = $isMatch
                        ? 'border-emerald-500/50'
                        : ($isMismatch
                            ? 'border-red-500/50'
                            : 'border-white/10');

                    $productName = $item->product->name ?? 'Produk Tidak Dikenal';
                    $sku = $item->product->sku ?? '';
                    $barcode = $item->product->barcode ?? '';
                    $binCode = $item->bin->code ?? '-';
                    $initialQty = $actualQty ?? 0;
                    $diff = $isCounted ? (int) $actualQty - (int) $systemQty : null;
                @endphp

                <div class="item-card bg-[#1e293b] rounded-2xl border {{ $cardBorder }} p-4 transition-all duration-200"
                    data-sku="{{ $sku }}" data-barcode="{{ $barcode }}"
                    data-counted="{{ $isCounted ? 'true' : 'false' }}"
                    data-mismatch="{{ $isMismatch ? 'true' : 'false' }}" x-data="{ qty: {{ $initialQty }} }">

                    {{-- Item header --}}
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-base font-semibold text-white leading-snug">{{ $productName }}</p>
                            <div class="flex items-center flex-wrap gap-x-3 gap-y-0.5 mt-1">
                                @if ($sku)
                                    <span class="text-xs text-slate-400 font-mono">SKU: {{ $sku }}</span>
                                @endif
                                <span class="text-xs text-slate-400">Bin: <span
                                        class="font-mono text-slate-300">{{ $binCode }}</span></span>
                            </div>
                        </div>
                        {{-- Status badge --}}
                        @if ($isMatch)
                            <span
                                class="status-badge-text flex-shrink-0 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-400">✓
                                Sesuai</span>
                        @elseif($isMismatch)
                            <span
                                class="status-badge-text flex-shrink-0 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-500/20 text-red-400">≠
                                Selisih</span>
                        @else
                            <span
                                class="status-badge-text flex-shrink-0 px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-500/20 text-gray-400">Belum</span>
                        @endif
                    </div>

                    {{-- System qty info row --}}
                    <div class="flex items-center justify-between bg-white/5 rounded-xl px-3 py-2 mb-3">
                        <span class="text-xs text-slate-400">Stok Sistem:</span>
                        <span class="text-sm font-bold text-white">{{ number_format($systemQty, 0) }}</span>
                        @if ($diff !== null)
                            <span
                                class="text-xs font-semibold ml-2 {{ $diff > 0 ? 'text-green-400' : ($diff < 0 ? 'text-red-400' : 'text-gray-400') }}">
                                {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 0) }}
                            </span>
                        @endif
                    </div>

                    @if (!$isCompleted)
                        {{-- ── Quantity stepper ──────────────────────────────────── --}}
                        <form method="POST" action="{{ route('mobile.opname.update', $item) }}"
                            class="space-y-2 opname-item-form">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="actual_qty" :value="qty">

                            <label class="block text-xs text-slate-400 font-medium mb-1">Jumlah Aktual (Hasil
                                Hitung):</label>

                            <div class="flex items-stretch gap-2">
                                {{-- Decrement --}}
                                <button type="button" @click="qty = Math.max(0, qty - 1)"
                                    class="flex-shrink-0 w-14 h-14 flex items-center justify-center rounded-xl bg-white/5 hover:bg-white/10 active:bg-white/20 active:scale-95 text-white text-2xl font-bold transition touch-manipulation border border-white/10 select-none">
                                    −
                                </button>

                                {{-- Quantity input --}}
                                <input type="number" x-model.number="qty" name="_qty_display" min="0"
                                    step="1"
                                    class="flex-1 h-14 text-xl text-center font-bold bg-[#0f172a] border border-white/15 rounded-xl text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50 touch-manipulation"
                                    inputmode="numeric" @change="qty = Math.max(0, parseInt($event.target.value) || 0)">

                                {{-- Increment --}}
                                <button type="button" @click="qty++"
                                    class="flex-shrink-0 w-14 h-14 flex items-center justify-center rounded-xl bg-white/5 hover:bg-white/10 active:bg-white/20 active:scale-95 text-white text-2xl font-bold transition touch-manipulation border border-white/10 select-none">
                                    +
                                </button>
                            </div>

                            <button type="submit"
                                class="w-full h-12 bg-blue-600 hover:bg-blue-500 active:scale-[0.98] text-white font-semibold rounded-xl transition touch-manipulation text-sm">
                                Simpan Jumlah
                            </button>
                        </form>
                    @else
                        {{-- Completed view: show actual qty read-only --}}
                        <div class="bg-white/5 rounded-xl px-3 py-3 text-center">
                            <p class="text-xs text-slate-400 mb-1">Jumlah Aktual</p>
                            <p
                                class="text-2xl font-bold {{ $isMatch ? 'text-emerald-400' : ($isMismatch ? 'text-red-400' : 'text-slate-400') }}">
                                {{ $actualQty !== null ? number_format($actualQty, 0) : '—' }}
                            </p>
                        </div>
                    @endif

                </div>
            @empty
                <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-10 text-center">
                    <p class="text-white font-semibold mb-1">Tidak Ada Item</p>
                    <p class="text-sm text-slate-400">Sesi ini belum memiliki item opname.</p>
                </div>
            @endforelse

        </div>
    </div>

    {{-- ── Fixed bottom action bar ─────────────────────────────────────── --}}
    @if (!$isCompleted)
        <div
            class="fixed bottom-0 left-0 right-0 z-30 bg-gray-900/95 backdrop-blur border-t border-white/10 p-4 safe-area-bottom">
            <form method="POST" action="{{ route('mobile.opname.complete', $s) }}"
                onsubmit="return confirm('Selesaikan opname ini? Stok bin akan diperbarui sesuai hasil hitung.')">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="flex items-center justify-center gap-2 w-full h-14 bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] text-white font-bold rounded-2xl transition touch-manipulation text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    Selesaikan Opname
                    <span class="text-sm font-normal opacity-75">({{ $progressPct }}% selesai)</span>
                </button>
            </form>
        </div>
    @else
        <div
            class="fixed bottom-0 left-0 right-0 z-30 bg-gray-900/95 backdrop-blur border-t border-white/10 p-4 safe-area-bottom">
            <a href="{{ route('mobile.opname') }}"
                class="flex items-center justify-center gap-2 w-full h-14 bg-[#1e293b] border border-white/10 text-white font-semibold rounded-2xl transition touch-manipulation text-base">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke Daftar Opname
            </a>
        </div>
    @endif

    @push('head')
        <style>
            /* Filter tabs */
            .filter-tab {
                background: rgba(255, 255, 255, 0.04);
                color: #94a3b8;
                border: 1px solid rgba(255, 255, 255, 0.08);
            }

            .filter-tab.active-tab {
                background: rgba(59, 130, 246, 0.15);
                color: #60a5fa;
                border-color: rgba(59, 130, 246, 0.3);
            }

            /* Barcode scan highlight ring animation */
            @keyframes ringPulse {
                0% {
                    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.6);
                }

                70% {
                    box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
                }

                100% {
                    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
                }
            }

            .scan-found {
                animation: ringPulse 0.6s ease-out 2;
            }

            /* Safe area for devices with home indicators */
            .safe-area-bottom {
                padding-bottom: max(1rem, env(safe-area-inset-bottom));
            }

            /* Remove number input spinners for cleaner look */
            input[type=number]::-webkit-inner-spin-button,
            input[type=number]::-webkit-outer-spin-button {
                -webkit-appearance: none;
            }

            input[type=number] {
                -moz-appearance: textfield;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // ── Barcode scan callback ─────────────────────────────────────────────
            function onOpnameScan(barcode) {
                const trimmed = barcode.trim();

                // Try matching by data-barcode or data-sku
                const card = document.querySelector(
                    `[data-barcode="${CSS.escape(trimmed)}"], [data-sku="${CSS.escape(trimmed)}"]`
                );

                if (card) {
                    // Scroll into view
                    card.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    // Highlight with ring
                    card.classList.add('ring-2', 'ring-blue-500', 'scan-found');
                    setTimeout(() => {
                        card.classList.remove('ring-2', 'ring-blue-500', 'scan-found');
                    }, 3000);

                    // Focus the qty input
                    const input = card.querySelector('input[type="number"]');
                    if (input) {
                        setTimeout(() => {
                            input.focus();
                            input.select();
                        }, 400); // wait for scroll to finish
                    }
                } else {
                    // Not found feedback
                    if ('vibrate' in navigator) navigator.vibrate([200, 100, 200]);
                    alert('Produk dengan barcode "' + trimmed + '" tidak ditemukan di sesi ini.');
                }
            }

            // ── Item filter tabs ──────────────────────────────────────────────────
            function filterItems(type) {
                // Update tab styles
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active-tab'));
                document.getElementById('tab-' + type).classList.add('active-tab');

                // Show/hide cards
                document.querySelectorAll('.item-card').forEach(card => {
                    let show = true;
                    if (type === 'uncounted') {
                        show = card.dataset.counted === 'false';
                    } else if (type === 'mismatch') {
                        show = card.dataset.mismatch === 'true';
                    }
                    card.style.display = show ? '' : 'none';
                });
            }

            // ── Offline-aware opname item submit ──────────────────────────────────
            // Intercepts the per-item form submit. When offline: queues to IndexedDB
            // and optimistically updates the card UI without a page reload.
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.opname-item-form').forEach(form => {
                    form.addEventListener('submit', async function(e) {
                        if (navigator.onLine) return; // let it submit normally when online

                        e.preventDefault();

                        const hiddenQty = this.querySelector('input[name="actual_qty"]');
                        const qty = hiddenQty ? parseFloat(hiddenQty.value) : 0;
                        const url = this.action;
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ||
                            '';
                        const card = this.closest('.item-card');
                        const itemId = url.split('/').slice(-2)[0]; // /mobile/opname/{id}/update

                        // Queue in IndexedDB via ErpOffline
                        if (window.ErpOffline) {
                            await window.ErpOffline.queue(
                                'opname',
                                url,
                                'POST', {
                                    actual_qty: qty,
                                    _method: 'PATCH'
                                }
                            );
                        }

                        // Optimistic UI update
                        if (card) {
                            card.dataset.counted = 'true';
                            const badge = card.querySelector('.status-badge-text');
                            if (badge) {
                                badge.textContent = '✏ Antri';
                                badge.className = badge.className
                                    .replace(/bg-\w+-\d+\/20 text-\w+-\d+/g, '')
                                    .trim() + ' bg-amber-500/20 text-amber-400';
                            }
                            const submitBtn = this.querySelector('button[type="submit"]');
                            if (submitBtn) {
                                submitBtn.textContent = '✓ Tersimpan (offline)';
                                submitBtn.classList.replace('bg-blue-600', 'bg-amber-600');
                                submitBtn.disabled = true;
                            }
                        }

                        showMobileToast('Disimpan offline. Akan dikirim saat online.', 'warning');
                    });
                });
            });

            // ── Background sync on reconnect ──────────────────────────────────────
            window.addEventListener('online', async () => {
                if (!window.ErpOffline) return;
                const pending = await window.ErpOffline.pendingCount('opname');
                if (pending > 0) {
                    showMobileToast(`Menyinkronkan ${pending} perubahan opname...`, 'info');
                    const synced = await window.ErpOffline.flush();
                    if (synced > 0) {
                        showMobileToast(`${synced} perubahan berhasil disinkronisasi. Memuat ulang...`, 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    }
                }
            });

            // ── Simple mobile toast ───────────────────────────────────────────────
            function showMobileToast(msg, type) {
                const colors = {
                    success: '#059669',
                    warning: '#d97706',
                    error: '#dc2626',
                    info: '#2563eb'
                };
                const t = document.createElement('div');
                t.style.cssText = `position:fixed;bottom:5.5rem;left:1rem;right:1rem;z-index:9999;
                    padding:0.875rem 1rem;border-radius:1rem;color:#fff;font-size:0.875rem;font-weight:500;
                    background:${colors[type]||colors.info};box-shadow:0 4px 20px rgba(0,0,0,0.4);
                    opacity:0;transition:opacity 0.25s;text-align:center;`;
                t.textContent = msg;
                document.body.appendChild(t);
                requestAnimationFrame(() => {
                    t.style.opacity = '1';
                });
                setTimeout(() => {
                    t.style.opacity = '0';
                    setTimeout(() => t.remove(), 300);
                }, 3500);
            }

            // ── Batch mode toggle & logic ─────────────────────────────────────────
            let batchMode = false;
            const selectedItems = new Set();

            window.toggleBatchMode = function(enable) {
                batchMode = enable;
                const bar = document.getElementById('batch-action-bar');
                const btn = document.getElementById('btn-toggle-batch');
                if (!bar || !btn) return;

                if (enable) {
                    bar.style.display = 'block';
                    btn.style.display = 'none';
                    addCheckboxesToCards();
                } else {
                    bar.style.display = 'none';
                    btn.style.display = 'block';
                    removeCheckboxesFromCards();
                    selectedItems.clear();
                    updateBatchCount();
                }
            };

            function addCheckboxesToCards() {
                document.querySelectorAll('.item-card').forEach(card => {
                    if (card.querySelector('.batch-checkbox')) return;
                    const cb = document.createElement('input');
                    cb.type = 'checkbox';
                    cb.className =
                        'batch-checkbox absolute top-3 left-3 h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500';
                    cb.dataset.itemId = card.dataset.sku; // using SKU as identifier
                    cb.addEventListener('change', () => {
                        if (cb.checked) selectedItems.add(cb.dataset.itemId);
                        else selectedItems.delete(cb.dataset.itemId);
                        updateBatchCount();
                    });
                    card.appendChild(cb);
                });
            }

            function removeCheckboxesFromCards() {
                document.querySelectorAll('.batch-checkbox').forEach(cb => cb.remove());
            }

            function updateBatchCount() {
                document.getElementById('batch-count-label').textContent = selectedItems.size;
                document.getElementById('batch-btn-count').textContent = selectedItems.size;
            }

            // Prepare batch form data before submit
            document.getElementById('batch-form')?.addEventListener('submit', function(e) {
                if (selectedItems.size === 0) {
                    e.preventDefault();
                    showMobileToast('Pilih minimal 1 item untuk update batch', 'warning');
                    return false;
                }
                // Encode selected SKUs into JSON for batch processing
                const jsonInput = document.getElementById('batch-items-json');
                if (jsonInput) {
                    jsonInput.innerHTML =
                        `<input type="hidden" name="selected_skus" value="${JSON.stringify(Array.from(selectedItems))}">`;
                }
            });
        </script>
    @endpush

    {{-- Include batch mode UI --}}
    @include('mobile.partials.opname-batch-ui')

</x-app-layout>
