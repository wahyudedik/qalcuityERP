<x-app-layout>
    <x-slot name="header">Picking List</x-slot>

    <style>
        .mob-pick-page {
            min-height: 100vh;
            background: #030712;
            padding-bottom: 2rem;
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

        .mob-topbar-title {
            font-size: 1rem;
            font-weight: 700;
            color: #f1f5f9;
            flex: 1;
        }

        .mob-topbar-count {
            font-size: 0.7rem;
            color: #475569;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 999px;
            padding: 0.2rem 0.55rem;
        }

        /* ── Filter Tabs ── */
        .mob-filter-wrap {
            display: flex;
            gap: 0.5rem;
            padding: 0.75rem 1rem 0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        .mob-filter-wrap::-webkit-scrollbar {
            display: none;
        }

        .mob-filter-tab {
            flex-shrink: 0;
            padding: 0.4rem 0.875rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: transparent;
            color: #64748b;
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
            -webkit-tap-highlight-color: transparent;
        }

        .mob-filter-tab:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
        }

        .mob-filter-tab.active {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        /* ── Card List ── */
        .mob-card-list {
            padding: 0.875rem 1rem 0;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .mob-pick-card {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.125rem;
            padding: 1rem 1rem 0.875rem;
            text-decoration: none;
            display: block;
            -webkit-tap-highlight-color: transparent;
            transition: background 0.15s, border-color 0.15s, transform 0.1s;
            position: relative;
            overflow: hidden;
        }

        .mob-pick-card:active {
            transform: scale(0.985);
        }

        .mob-pick-card:hover {
            background: #263449;
            border-color: rgba(255, 255, 255, 0.12);
        }

        .mob-pick-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .mob-pick-number {
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            font-weight: 700;
            color: #f1f5f9;
            line-height: 1.2;
        }

        .mob-pick-warehouse {
            font-size: 0.7rem;
            color: #475569;
            margin-top: 0.15rem;
        }

        /* Status badge */
        .mob-status-badge {
            flex-shrink: 0;
            padding: 0.2rem 0.6rem;
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

        .mob-pick-meta {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            font-size: 0.72rem;
            color: #475569;
        }

        .mob-pick-meta-item {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .mob-pick-meta-val {
            color: #94a3b8;
            font-weight: 600;
        }

        /* Progress bar */
        .mob-progress-wrap {
            margin-top: 0.625rem;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 999px;
            height: 4px;
            overflow: hidden;
        }

        .mob-progress-bar {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #3b82f6, #22d3ee);
            transition: width 0.3s;
        }

        .mob-progress-bar.complete {
            background: linear-gradient(90deg, #22c55e, #4ade80);
        }

        /* Assignee chip */
        .mob-pick-assignee {
            margin-top: 0.5rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.68rem;
            color: #818cf8;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 999px;
            padding: 0.175rem 0.55rem;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        /* Arrow indicator */
        .mob-pick-arrow {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.875rem;
            color: #334155;
        }

        /* ── Empty State ── */
        .mob-empty {
            text-align: center;
            padding: 3.5rem 1.25rem;
        }

        .mob-empty-icon {
            font-size: 3rem;
            margin-bottom: 0.75rem;
        }

        .mob-empty-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #475569;
        }

        .mob-empty-sub {
            font-size: 0.78rem;
            color: #334155;
            margin-top: 0.35rem;
        }

        /* ── Pagination ── */
        .mob-pagination {
            padding: 1rem 1rem 0;
        }
    </style>

    <div class="mob-pick-page">

        {{-- ── Top Bar ── --}}
        <div class="mob-topbar">
            <a href="{{ route('mobile.hub') }}" class="mob-back-btn">←</a>
            <span class="mob-topbar-title">🛒 Picking List</span>
            <span class="mob-topbar-count">{{ $pickingLists->total() }} task</span>
        </div>

        {{-- ── Filter Tabs ── --}}
        <div class="mob-filter-wrap">
            @foreach (['' => 'Semua', 'pending' => 'Pending', 'in_progress' => 'Proses', 'completed' => 'Selesai'] as $v => $l)
                <a href="?status={{ $v }}"
                    class="mob-filter-tab {{ request('status') === $v ? 'active' : '' }}">
                    {{ $l }}
                </a>
            @endforeach
        </div>

        {{-- ── Session Messages ── --}}
        @if (session('success'))
            <div
                style="margin:0.75rem 1rem 0; padding:0.625rem 0.875rem; background:rgba(34,197,94,0.1); border:1px solid rgba(34,197,94,0.2); border-radius:0.875rem; font-size:0.78rem; color:#4ade80;">
                ✓ {{ session('success') }}
            </div>
        @endif

        {{-- ── Card List ── --}}
        <div class="mob-card-list">
            @forelse($pickingLists as $list)
                @php
                    $totalItems = $list->items->count();
                    $doneItems = $list->items->whereIn('status', ['picked', 'short'])->count();
                    $progress = $totalItems > 0 ? round(($doneItems / $totalItems) * 100) : 0;
                    $statusClass = 'mob-status-' . str_replace('-', '_', $list->status);
                    $statusLabel =
                        [
                            'pending' => 'Pending',
                            'in_progress' => 'Proses',
                            'completed' => 'Selesai',
                            'cancelled' => 'Batal',
                        ][$list->status] ?? ucfirst($list->status);
                @endphp
                <a href="{{ route('mobile.picking.show', $list->id) }}" class="mob-pick-card">
                    <div class="mob-pick-card-top">
                        <div>
                            <div class="mob-pick-number">{{ $list->number }}</div>
                            <div class="mob-pick-warehouse">{{ $list->warehouse->name ?? '-' }}</div>
                        </div>
                        <span class="mob-status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>

                    <div class="mob-pick-meta">
                        <span class="mob-pick-meta-item">
                            📦 <span class="mob-pick-meta-val">{{ $totalItems }}</span> item
                        </span>
                        <span class="mob-pick-meta-item">
                            ✅ <span class="mob-pick-meta-val">{{ $doneItems }}</span> selesai
                        </span>
                        <span class="mob-pick-meta-item">
                            🕐 <span class="mob-pick-meta-val">{{ $list->created_at->diffForHumans() }}</span>
                        </span>
                    </div>

                    @if ($list->assignee)
                        <div class="mob-pick-assignee">
                            👤 {{ $list->assignee->name }}
                        </div>
                    @endif

                    @if ($totalItems > 0)
                        <div class="mob-progress-wrap" style="margin-top:0.625rem">
                            <div class="mob-progress-bar {{ $progress === 100 ? 'complete' : '' }}"
                                style="width:{{ $progress }}%"></div>
                        </div>
                    @endif

                    <span class="mob-pick-arrow">›</span>
                </a>
            @empty
                <div class="mob-empty">
                    <div class="mob-empty-icon">📭</div>
                    <div class="mob-empty-title">Belum ada picking list</div>
                    <div class="mob-empty-sub">Picking list akan muncul di sini setelah dibuat dari menu WMS</div>
                </div>
            @endforelse
        </div>

        {{-- ── Pagination ── --}}
        @if ($pickingLists->hasPages())
            <div class="mob-pagination">
                {{ $pickingLists->links() }}
            </div>
        @endif

    </div>

</x-app-layout>
