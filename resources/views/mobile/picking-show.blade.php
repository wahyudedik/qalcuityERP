<x-app-layout>
    <x-slot name="header">Picking Detail</x-slot>

    <style>
        .mob-pick-show {
            min-height: 100vh;
            background: #030712;
            padding-bottom: 6rem;
            /* space for fixed bottom bar */
        }

        /* ── Top Bar ── */
        .mob-topbar {
            position: sticky;
            top: 0;
            z-index: 30;
            background: #0f172a;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .mob-back-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.625rem;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #94a3b8;
            text-decoration: none;
            font-size: 1rem;
            flex-shrink: 0;
            transition: background 0.15s;
            -webkit-tap-highlight-color: transparent;
        }

        .mob-back-btn:active {
            background: rgba(255, 255, 255, 0.12);
        }

        .mob-topbar-info {
            flex: 1;
            min-width: 0;
        }

        .mob-topbar-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #f1f5f9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mob-topbar-sub {
            font-size: 0.7rem;
            color: #475569;
            margin-top: 0.1rem;
        }

        /* Status badge */
        .mob-status-badge {
            flex-shrink: 0;
            padding: 0.25rem 0.65rem;
            border-radius: 999px;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .mob-status-pending {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.25);
        }

        .mob-status-in_progress {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.25);
        }

        .mob-status-completed {
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }

        .mob-status-cancelled {
            background: rgba(100, 116, 139, 0.15);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.25);
        }

        /* ── Progress Section ── */
        .mob-progress-section {
            background: #0f172a;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            padding: 0.875rem 1rem;
        }

        .mob-progress-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .mob-progress-label {
            font-size: 0.75rem;
            color: #64748b;
        }

        .mob-progress-count {
            font-size: 0.8rem;
            font-weight: 700;
            color: #f1f5f9;
        }

        .mob-progress-track {
            background: rgba(255, 255, 255, 0.07);
            border-radius: 999px;
            height: 6px;
            overflow: hidden;
        }

        .mob-progress-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #3b82f6, #22d3ee);
            transition: width 0.4s;
        }

        .mob-progress-fill.done {
            background: linear-gradient(90deg, #22c55e, #4ade80);
        }

        /* ── Scanner Section ── */
        .mob-scanner-section {
            padding: 0.875rem 1rem 0;
        }

        .mob-scanner-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            margin-bottom: 0.5rem;
        }

        .mob-scanner-box {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.125rem;
            padding: 0.875rem;
        }

        /* Make scanner button full-width */
        .mob-scanner-box .barcode-scanner-wrapper {
            display: block;
        }

        .mob-scanner-box .barcode-scanner-wrapper>button {
            width: 100%;
            justify-content: center;
            height: 3rem;
            font-size: 0.9rem;
            border-radius: 0.875rem;
        }

        .mob-scanner-box .barcode-scanner-wrapper>div {
            /* manual input row */
            margin-top: 0.625rem;
        }

        .mob-scanner-box .barcode-scanner-wrapper>div input {
            flex: 1;
        }

        .mob-scan-hint {
            font-size: 0.68rem;
            color: #334155;
            margin-top: 0.5rem;
            text-align: center;
        }

        /* ── Items Section ── */
        .mob-items-section {
            padding: 0.875rem 1rem 0;
        }

        .mob-items-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            margin-bottom: 0.625rem;
        }

        /* ── Item Card ── */
        .mob-item-card {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.125rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            position: relative;
        }

        .mob-item-card.highlight {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .mob-item-card.done-card {
            border-color: rgba(34, 197, 94, 0.2);
            opacity: 0.7;
        }

        .mob-item-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.5rem;
            margin-bottom: 0.625rem;
        }

        .mob-item-name {
            font-size: 0.9rem;
            font-weight: 700;
            color: #f1f5f9;
            line-height: 1.3;
            flex: 1;
        }

        .mob-item-badge {
            flex-shrink: 0;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            font-size: 0.6rem;
            font-weight: 700;
        }

        .mb-pending {
            background: rgba(251, 191, 36, 0.12);
            color: #fbbf24;
        }

        .mb-picked {
            background: rgba(34, 197, 94, 0.12);
            color: #4ade80;
        }

        .mb-short {
            background: rgba(239, 68, 68, 0.12);
            color: #f87171;
        }

        .mob-item-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            font-size: 0.7rem;
            color: #475569;
            margin-bottom: 0.875rem;
        }

        .mob-item-meta-chip {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 0.5rem;
            padding: 0.2rem 0.5rem;
        }

        .mob-item-meta-chip span {
            color: #94a3b8;
            font-weight: 600;
        }

        /* ── Qty stepper + confirm form ── */
        .mob-item-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .mob-qty-stepper {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            flex: 1;
        }

        .mob-stepper-btn {
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: rgba(255, 255, 255, 0.06);
            color: #f1f5f9;
            font-size: 1.25rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background 0.15s;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }

        .mob-stepper-btn:hover {
            background: rgba(255, 255, 255, 0.12);
        }

        .mob-stepper-btn:active {
            background: rgba(255, 255, 255, 0.18);
        }

        .mob-qty-input {
            height: 3rem;
            width: 5rem;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.75rem;
            color: #f1f5f9;
            -moz-appearance: textfield;
            flex: 1;
        }

        .mob-qty-input::-webkit-inner-spin-button,
        .mob-qty-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
        }

        .mob-qty-input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .mob-confirm-btn {
            height: 3rem;
            padding: 0 1.125rem;
            background: #059669;
            border: none;
            border-radius: 0.875rem;
            color: #fff;
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            transition: background 0.15s;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }

        .mob-confirm-btn:hover {
            background: #047857;
        }

        .mob-confirm-btn:active {
            background: #065f46;
        }

        .mob-item-done-mark {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.15);
            border-radius: 0.75rem;
            font-size: 0.78rem;
            color: #4ade80;
            font-weight: 600;
        }

        /* ── Fixed Bottom Bar ── */
        .mob-bottom-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 40;
            background: rgba(3, 7, 18, 0.95);
            backdrop-filter: blur(16px);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 0.75rem 1rem;
            padding-bottom: max(0.75rem, env(safe-area-inset-bottom));
        }

        .mob-finish-btn {
            width: 100%;
            height: 3.25rem;
            border-radius: 1rem;
            border: none;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            -webkit-tap-highlight-color: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .mob-finish-btn.active-btn {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
        }

        .mob-finish-btn.active-btn:active {
            transform: scale(0.98);
        }

        .mob-finish-btn.disabled-btn {
            background: rgba(255, 255, 255, 0.06);
            color: #334155;
            cursor: not-allowed;
        }

        /* ── Flash Message ── */
        .mob-flash {
            margin: 0.75rem 1rem 0;
            padding: 0.625rem 0.875rem;
            border-radius: 0.875rem;
            font-size: 0.78rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .mob-flash-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #4ade80;
        }

        .mob-flash-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
        }
    </style>

    @php
        $statusClass = 'mob-status-' . str_replace('-', '_', $pickingList->status);
        $statusLabel =
            ['pending' => 'Pending', 'in_progress' => 'Proses', 'completed' => 'Selesai', 'cancelled' => 'Batal'][
                $pickingList->status
            ] ?? ucfirst($pickingList->status);
        $progressPct = $totalCount > 0 ? round(($pickedCount / $totalCount) * 100) : 0;
        $allDone = $pickedCount >= $totalCount && $totalCount > 0;
    @endphp

    <div class="mob-pick-show">

        {{-- ── Top Bar ── --}}
        <div class="mob-topbar">
            <a href="{{ route('mobile.picking') }}" class="mob-back-btn">←</a>
            <div class="mob-topbar-info">
                <div class="mob-topbar-title">{{ $pickingList->number }}</div>
                <div class="mob-topbar-sub">{{ $pickingList->warehouse->name ?? '-' }}</div>
            </div>
            <span class="mob-status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>

        {{-- ── Flash ── --}}
        @if (session('success'))
            <div class="mob-flash mob-flash-success">✓ {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mob-flash mob-flash-error">✕ {{ session('error') }}</div>
        @endif

        {{-- ── Progress ── --}}
        <div class="mob-progress-section">
            <div class="mob-progress-row">
                <span class="mob-progress-label">Progress Picking</span>
                <span class="mob-progress-count">{{ $pickedCount }} / {{ $totalCount }} item</span>
            </div>
            <div class="mob-progress-track">
                <div class="mob-progress-fill {{ $allDone ? 'done' : '' }}" style="width:{{ $progressPct }}%"></div>
            </div>
        </div>

        {{-- ── Barcode Scanner ── --}}
        <div class="mob-scanner-section">
            <div class="mob-scanner-label">Scan Barcode</div>
            <div class="mob-scanner-box">
                <x-barcode-scanner on-scan="onPickScan" button-label="Scan Barcode Produk" />
            </div>
            <p class="mob-scan-hint">Scan untuk langsung highlight item di daftar bawah</p>
        </div>

        {{-- ── Items ── --}}
        <div class="mob-items-section">
            <div class="mob-items-label">Daftar Item ({{ $totalCount }})</div>

            @forelse($items as $item)
                @php
                    $isDone = in_array($item->status, ['picked', 'short']);
                    $badgeClass =
                        ['pending' => 'mb-pending', 'picked' => 'mb-picked', 'short' => 'mb-short'][$item->status] ??
                        'mb-pending';
                    $badgeLabel =
                        ['pending' => 'Pending', 'picked' => 'Picked', 'short' => 'Kurang'][$item->status] ??
                        ucfirst($item->status);
                    $sku = $item->product->sku ?? ($item->product->barcode ?? '-');
                @endphp

                <div class="mob-item-card {{ $isDone ? 'done-card' : '' }}" id="item-card-{{ $item->id }}"
                    data-barcode="{{ $item->product->barcode ?? '' }}" data-sku="{{ $item->product->sku ?? '' }}">

                    <div class="mob-item-top">
                        <div class="mob-item-name">{{ $item->product->name ?? 'Produk Tidak Diketahui' }}</div>
                        <span class="mob-item-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                    </div>

                    <div class="mob-item-meta">
                        <span class="mob-item-meta-chip">SKU <span>{{ $sku }}</span></span>
                        @if ($item->bin)
                            <span class="mob-item-meta-chip">🗃 Bin <span>{{ $item->bin->code }}</span></span>
                        @endif
                        <span class="mob-item-meta-chip">Diminta
                            <span>{{ number_format($item->quantity_requested, 0) }}</span></span>
                        @if ($item->quantity_picked > 0)
                            <span class="mob-item-meta-chip">Diambil
                                <span>{{ number_format($item->quantity_picked, 0) }}</span></span>
                        @endif
                    </div>

                    @if ($isDone)
                        <div class="mob-item-done-mark">
                            ✓ Selesai diambil: {{ number_format($item->quantity_picked, 0) }} unit
                        </div>
                    @else
                        <form method="POST" action="{{ route('mobile.picking.confirm', $item->id) }}"
                            class="mob-item-form" id="form-item-{{ $item->id }}">
                            @csrf
                            <div class="mob-qty-stepper">
                                <button type="button" class="mob-stepper-btn"
                                    onclick="stepQty('qty-{{ $item->id }}', -1)">−</button>
                                <input type="number" id="qty-{{ $item->id }}" name="quantity_picked"
                                    value="{{ $item->quantity_requested }}" min="0" step="1"
                                    class="mob-qty-input">
                                <button type="button" class="mob-stepper-btn"
                                    onclick="stepQty('qty-{{ $item->id }}', 1)">+</button>
                            </div>
                            <button type="submit" class="mob-confirm-btn">
                                ✓ Konfirmasi
                            </button>
                        </form>
                    @endif

                </div>
            @empty
                <div style="text-align:center;padding:2rem;color:#334155;font-size:0.875rem">
                    Tidak ada item dalam picking list ini.
                </div>
            @endforelse
        </div>

    </div>

    {{-- ── Fixed Bottom Bar ── --}}
    <div class="mob-bottom-bar">
        @if ($allDone)
            <a href="{{ route('mobile.picking') }}" class="mob-finish-btn active-btn">
                ✅ Picking Selesai — Kembali ke List
            </a>
        @else
            <button type="button" class="mob-finish-btn {{ $pickedCount > 0 ? 'active-btn' : 'disabled-btn' }}"
                onclick="if({{ $pickedCount > 0 ? 'true' : 'false' }}) window.location='{{ route('mobile.picking') }}'">
                🛒 Selesai Picking ({{ $pickedCount }}/{{ $totalCount }})
            </button>
        @endif
    </div>

    @push('scripts')
        <script>
            // ── Qty stepper ──────────────────────────────────────────────
            function stepQty(inputId, delta) {
                const el = document.getElementById(inputId);
                if (!el) return;
                const val = (parseFloat(el.value) || 0) + delta;
                el.value = Math.max(0, val);
            }

            // ── Barcode scan handler ─────────────────────────────────────
            function onPickScan(code) {
                if (!code) return;
                code = code.trim();

                // Find matching item card by barcode or SKU
                const cards = document.querySelectorAll('.mob-item-card');
                let matched = null;

                for (const card of cards) {
                    const barcode = (card.dataset.barcode || '').trim();
                    const sku = (card.dataset.sku || '').trim();
                    if (barcode === code || sku === code) {
                        matched = card;
                        break;
                    }
                }

                if (matched) {
                    // Remove all existing highlights
                    document.querySelectorAll('.mob-item-card.highlight')
                        .forEach(c => c.classList.remove('highlight'));

                    // Highlight the matched card
                    matched.classList.add('highlight');

                    // Scroll to card smoothly
                    matched.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    // Haptic feedback (if not already done by scanner component)
                    if ('vibrate' in navigator) navigator.vibrate([80]);

                    // Focus on its qty input if pending
                    const inputId = matched.id.replace('item-card-', 'qty-');
                    const input = document.getElementById(inputId);
                    if (input) {
                        setTimeout(() => input.focus(), 400);
                    }
                } else {
                    // No match — flash a brief error indicator
                    const flash = document.createElement('div');
                    flash.style.cssText =
                        'position:fixed;top:5rem;left:50%;transform:translateX(-50%);background:rgba(239,68,68,0.9);color:#fff;padding:0.5rem 1rem;border-radius:0.75rem;font-size:0.8rem;font-weight:700;z-index:9999;transition:opacity 0.5s';
                    flash.textContent = '⚠ Barcode tidak ditemukan: ' + code;
                    document.body.appendChild(flash);
                    setTimeout(() => {
                        flash.style.opacity = '0';
                        setTimeout(() => flash.remove(), 500);
                    }, 2000);
                }
            }
        </script>
    @endpush

</x-app-layout>
