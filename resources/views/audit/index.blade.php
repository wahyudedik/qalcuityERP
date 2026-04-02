<x-app-layout>
    <x-slot name="header">Audit Trail</x-slot>

    <div class="space-y-4">

        {{-- Retention & Stats Bar --}}
        <div
            class="flex flex-wrap items-center justify-between gap-3 bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-slate-400">
                <span><strong class="text-gray-900 dark:text-white">{{ number_format($totalLogs) }}</strong> total
                    log</span>
                @if ($oldestLog)
                    <span>Sejak {{ $oldestLog->format('d M Y') }}</span>
                @endif
                <span class="px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 font-medium">Retensi:
                    {{ $retentionDays }} hari</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('audit.export', request()->only(['date_from', 'date_to', 'module'])) }}"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </a>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">Quick Filter:</span>
                <a href="{{ route('audit.index') }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ !request()->hasAny(['is_ai', 'action', 'user_id', 'module']) ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-white/15' }}">
                    Semua
                </a>
                <a href="{{ request()->fullUrlWithQuery(['is_ai' => '1', 'page' => null]) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request('is_ai') === '1' ? 'bg-purple-600 text-white' : 'bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 hover:bg-purple-200' }}">
                    🤖 AI ({{ $aiCount }} hari ini)
                </a>
                <a href="{{ request()->fullUrlWithQuery(['is_ai' => '0', 'page' => null]) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request('is_ai') === '0' ? 'bg-gray-600 text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-white/15' }}">
                    👤 Manual
                </a>
                <a href="{{ request()->fullUrlWithQuery(['action' => 'rollback', 'page' => null]) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request('action') === 'rollback' ? 'bg-amber-600 text-white' : 'bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 hover:bg-amber-200' }}">
                    ↩ Rollback
                </a>
            </div>
            <form method="GET" class="flex flex-wrap gap-2">
                @if (request('is_ai') !== null)
                    <input type="hidden" name="is_ai" value="{{ request('is_ai') }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi..."
                    class="flex-1 min-w-[150px] px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="user_id"
                    class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white">
                    <option value="">Semua User</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}
                            ({{ $u->role }})</option>
                    @endforeach
                </select>
                <select name="module"
                    class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white">
                    <option value="">Semua Modul</option>
                    @foreach ($modules as $m)
                        <option value="{{ $m }}" @selected(request('module') === $m)>{{ $m }}</option>
                    @endforeach
                </select>
                <select name="action"
                    class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white">
                    <option value="">Semua Aksi</option>
                    @foreach ($actions as $a)
                        <option value="{{ $a }}" @selected(request('action') === $a)>{{ $a }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700">Filter</button>
                @if (request()->hasAny(['search', 'user_id', 'module', 'action', 'date_from', 'date_to', 'is_ai']))
                    <a href="{{ route('audit.index') }}"
                        class="px-4 py-2 border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Reset</a>
                @endif
            </form>
        </div>

        {{-- Log Table --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Waktu</th>
                            <th class="px-4 py-3 text-left">User</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Modul</th>
                            <th class="px-4 py-3 text-left">Deskripsi</th>
                            <th class="px-4 py-3 text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 {{ $log->is_ai_action ? 'bg-purple-50/30 dark:bg-purple-900/5' : '' }} {{ $log->rolled_back_at ? 'opacity-60' : '' }}"
                                id="row-{{ $log->id }}">
                                <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400 whitespace-nowrap">
                                    {{ $log->created_at->format('d/m/Y') }}<br>
                                    <span
                                        class="text-gray-400 dark:text-slate-500">{{ $log->created_at->format('H:i:s') }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="w-5 h-5 rounded-full {{ $log->is_ai_action ? 'bg-purple-100 dark:bg-purple-900/40' : 'bg-gray-100 dark:bg-white/10' }} flex items-center justify-center text-xs">
                                            {{ $log->is_ai_action ? '🤖' : '👤' }}
                                        </span>
                                        <span
                                            class="text-xs font-medium text-gray-900 dark:text-white">{{ $log->user?->name ?? 'System' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($log->rolled_back_at)
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/20 text-amber-400">↩
                                            rolled back</span>
                                    @elseif($log->action === 'rollback')
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/20 text-amber-400">↩
                                            rollback</span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs font-medium {{ $log->is_ai_action ? 'bg-purple-500/20 text-purple-400' : 'bg-blue-500/20 text-blue-400' }}">
                                            {{ $log->ai_tool_name ?? $log->action }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500 dark:text-slate-400">
                                    {{ $log->model_type ? class_basename($log->model_type) : '—' }}
                                    @if ($log->model_id)
                                        <span class="text-gray-400">#{{ $log->model_id }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-700 dark:text-slate-300 max-w-xs">
                                    <span class="line-clamp-2">{{ $log->description }}</span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <button onclick="openDetail({{ $log->id }})"
                                        class="text-xs text-blue-500 hover:text-blue-400 hover:underline">Detail</button>
                                    @if ($log->old_values || $log->new_values)
                                        <span class="text-gray-300 dark:text-slate-600 mx-0.5">·</span>
                                        <button onclick="openDiff({{ $log->id }})"
                                            class="text-xs text-emerald-500 hover:text-emerald-400 hover:underline">Diff</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">
                                    Tidak ada log aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $logs->links() }}</div>
        </div>
    </div>

    {{-- ═══════════ Detail + Diff Modal ═══════════ --}}
    <div id="modal-detail"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-3xl shadow-xl max-h-[90vh] flex flex-col">
            {{-- Header --}}
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 shrink-0">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm" id="detail-title">Detail Audit Log
                    </h3>
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5" id="detail-subtitle"></p>
                </div>
                <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="flex border-b border-gray-100 dark:border-white/10 px-6 shrink-0">
                <button onclick="showTab('diff')" id="tab-diff"
                    class="detail-tab px-4 py-2.5 text-xs font-medium border-b-2 border-blue-500 text-blue-500 -mb-px">Perubahan</button>
                <button onclick="showTab('meta')" id="tab-meta"
                    class="detail-tab px-4 py-2.5 text-xs font-medium border-b-2 border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white -mb-px">Metadata</button>
                <button onclick="showTab('timeline')" id="tab-timeline"
                    class="detail-tab px-4 py-2.5 text-xs font-medium border-b-2 border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white -mb-px">Timeline</button>
            </div>

            {{-- Body --}}
            <div class="overflow-y-auto flex-1 p-6" id="detail-body">
                <div class="flex items-center justify-center py-12">
                    <svg class="w-5 h-5 animate-spin text-blue-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4" />
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                </div>
            </div>

            {{-- Footer with Rollback --}}
            <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100 dark:border-white/10 shrink-0"
                id="detail-footer" style="display:none">
                <div id="rollback-status" class="text-xs text-gray-400 dark:text-slate-500"></div>
                <button onclick="performRollback()" id="btn-rollback" style="display:none"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-medium bg-amber-600 hover:bg-amber-700 text-white transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                    Rollback Perubahan Ini
                </button>
            </div>
        </div>
    </div>

    @php
        $diffData = $logs->mapWithKeys(
            fn($log) => [
                $log->id => [
                    'old' => $log->old_values,
                    'new' => $log->new_values,
                    'action' => $log->action,
                    'is_ai' => $log->is_ai_action,
                    'rollbackable' => $log->isRollbackable(),
                    'rolled_back_at' => $log->rolled_back_at?->format('d/m/Y H:i'),
                ],
            ],
        );
    @endphp

    <script>
        const CSRF = '{{ csrf_token() }}';
        const diffData = @json($diffData);
        let currentLogId = null;
        let currentLogData = null;

        // ── Modal helpers ─────────────────────────────────────────────
        function closeDetailModal() {
            document.getElementById('modal-detail').classList.add('hidden');
            currentLogId = null;
            currentLogData = null;
        }

        function showTab(tab) {
            document.querySelectorAll('.detail-tab').forEach(t => {
                t.classList.remove('border-blue-500', 'text-blue-500');
                t.classList.add('border-transparent', 'text-gray-500', 'dark:text-slate-400');
            });
            const active = document.getElementById('tab-' + tab);
            active.classList.add('border-blue-500', 'text-blue-500');
            active.classList.remove('border-transparent', 'text-gray-500', 'dark:text-slate-400');

            const body = document.getElementById('detail-body');
            if (tab === 'diff') body.innerHTML = renderDiffTab(currentLogData);
            else if (tab === 'meta') body.innerHTML = renderMetaTab(currentLogData);
            else if (tab === 'timeline') body.innerHTML = renderTimelineTab(currentLogData);
        }

        // ── Open detail via AJAX ──────────────────────────────────────
        async function openDetail(id) {
            currentLogId = id;
            document.getElementById('modal-detail').classList.remove('hidden');
            document.getElementById('detail-body').innerHTML =
                '<div class="flex items-center justify-center py-12"><svg class="w-5 h-5 animate-spin text-blue-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg></div>';
            document.getElementById('detail-footer').style.display = 'none';

            try {
                const res = await fetch(`/audit/${id}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                currentLogData = data;

                document.getElementById('detail-title').textContent = data.log.description;
                document.getElementById('detail-subtitle').textContent =
                    `${data.log.user_name} · ${data.log.created_at} (${data.log.ago})`;

                // Show diff tab by default
                showTab('diff');

                // Footer
                const footer = document.getElementById('detail-footer');
                const rollbackBtn = document.getElementById('btn-rollback');
                const rollbackStatus = document.getElementById('rollback-status');
                footer.style.display = 'flex';

                if (data.log.rolled_back_at) {
                    rollbackBtn.style.display = 'none';
                    rollbackStatus.innerHTML =
                        `<span class="text-amber-400">↩ Di-rollback pada ${data.log.rolled_back_at} oleh ${data.log.rolled_back_by || 'unknown'}</span>`;
                } else if (data.log.is_rollbackable) {
                    rollbackBtn.style.display = 'flex';
                    rollbackBtn.disabled = false;
                    rollbackBtn.textContent = '↩ Rollback Perubahan Ini';
                    rollbackStatus.textContent = '';
                } else {
                    rollbackBtn.style.display = 'none';
                    rollbackStatus.textContent = data.log.is_ai_action ? 'AI action — tidak dapat di-rollback' :
                        'Entry ini tidak dapat di-rollback';
                }
            } catch (e) {
                document.getElementById('detail-body').innerHTML =
                    '<p class="text-sm text-red-400 text-center py-8">Gagal memuat detail.</p>';
            }
        }

        // ── Quick diff modal (inline, no AJAX) ────────────────────────
        function openDiff(id) {
            currentLogId = id;
            const data = diffData[id];
            if (!data) return;

            currentLogData = {
                log: {
                    ...data,
                    old_values: data.old,
                    new_values: data.new,
                    is_ai_action: data.is_ai,
                    is_rollbackable: data.rollbackable,
                    rolled_back_at: data.rolled_back_at
                },
                timeline: []
            };
            document.getElementById('modal-detail').classList.remove('hidden');
            document.getElementById('detail-title').textContent = 'Detail Perubahan';
            document.getElementById('detail-subtitle').textContent = data.action;
            showTab('diff');

            const footer = document.getElementById('detail-footer');
            const rollbackBtn = document.getElementById('btn-rollback');
            const rollbackStatus = document.getElementById('rollback-status');
            footer.style.display = 'flex';

            if (data.rolled_back_at) {
                rollbackBtn.style.display = 'none';
                rollbackStatus.innerHTML = `<span class="text-amber-400">↩ Di-rollback pada ${data.rolled_back_at}</span>`;
            } else if (data.rollbackable) {
                rollbackBtn.style.display = 'flex';
                rollbackBtn.disabled = false;
                rollbackStatus.textContent = '';
            } else {
                rollbackBtn.style.display = 'none';
                rollbackStatus.textContent = '';
            }
        }

        // ── Rollback action ───────────────────────────────────────────
        async function performRollback() {
            if (!currentLogId) return;
            if (!confirm('Yakin ingin me-rollback perubahan ini? Data akan dikembalikan ke nilai sebelumnya.')) return;

            const btn = document.getElementById('btn-rollback');
            btn.disabled = true;
            btn.textContent = 'Rolling back...';

            try {
                const res = await fetch(`/audit/${currentLogId}/rollback`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json'
                    },
                });
                const data = await res.json();

                if (data.ok) {
                    btn.style.display = 'none';
                    document.getElementById('rollback-status').innerHTML =
                        '<span class="text-green-400">✓ Rollback berhasil!</span>';
                    // Dim the row in the table
                    const row = document.getElementById('row-' + currentLogId);
                    if (row) row.classList.add('opacity-60');
                    // Refresh after short delay
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    alert(data.message || 'Rollback gagal.');
                    btn.disabled = false;
                    btn.textContent = '↩ Rollback Perubahan Ini';
                }
            } catch (e) {
                alert('Rollback gagal. Coba lagi.');
                btn.disabled = false;
                btn.textContent = '↩ Rollback Perubahan Ini';
            }
        }

        // ── Render functions ──────────────────────────────────────────
        function renderDiffTab(data) {
            if (!data?.log) return '<p class="text-sm text-gray-400 text-center py-4">No data</p>';

            const log = data.log;
            const oldV = log.old_values || {};
            const newV = log.new_values || {};

            if (log.is_ai_action) {
                return renderSection('📥 Input (Args)', oldV) + renderSection('📤 Output (Result)', newV);
            }

            const allKeys = [...new Set([...Object.keys(oldV), ...Object.keys(newV)])].sort();
            if (allKeys.length === 0) {
                return '<p class="text-sm text-gray-400 text-center py-4">Tidak ada data perubahan tercatat.</p>';
            }

            let html = `<div class="overflow-x-auto">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="text-gray-500 dark:text-slate-400">
                        <th class="text-left py-2.5 px-3 bg-gray-50 dark:bg-white/5 rounded-tl-lg font-semibold w-1/4">Field</th>
                        <th class="text-left py-2.5 px-3 bg-red-50 dark:bg-red-500/5 font-semibold w-[37.5%]">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-400"></span>Sebelum</span>
                        </th>
                        <th class="text-left py-2.5 px-3 bg-green-50 dark:bg-green-500/5 rounded-tr-lg font-semibold w-[37.5%]">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-400"></span>Sesudah</span>
                        </th>
                    </tr>
                </thead>
                <tbody>`;

            for (const key of allKeys) {
                const ov = formatVal(oldV[key]);
                const nv = formatVal(newV[key]);
                const changed = ov !== nv;
                const isNew = ov === '' && nv !== '';
                const isRemoved = ov !== '' && nv === '';

                let rowBg = '';
                let oldClass = 'text-gray-500 dark:text-slate-400';
                let newClass = 'text-gray-500 dark:text-slate-400';

                if (changed) {
                    rowBg = 'bg-amber-50/50 dark:bg-amber-500/5';
                    oldClass = isRemoved ? 'text-red-500 bg-red-50 dark:bg-red-500/10 line-through rounded px-1' : (isNew ?
                        'text-gray-300 dark:text-slate-600' :
                        'text-red-500 bg-red-50 dark:bg-red-500/10 line-through rounded px-1');
                    newClass = isNew ? 'text-green-600 bg-green-50 dark:bg-green-500/10 font-medium rounded px-1' : (
                        isRemoved ? 'text-gray-300 dark:text-slate-600' :
                        'text-green-600 bg-green-50 dark:bg-green-500/10 font-medium rounded px-1');
                }

                html += `<tr class="${rowBg} border-b border-gray-50 dark:border-white/5">
                <td class="py-2 px-3 font-medium text-gray-700 dark:text-slate-300 whitespace-nowrap align-top">${key} ${changed ? '<span class="text-amber-400 ml-1">●</span>' : ''}</td>
                <td class="py-2 px-3 align-top"><span class="${oldClass} break-all">${ov || '<span class="text-gray-300 dark:text-slate-600 italic">kosong</span>'}</span></td>
                <td class="py-2 px-3 align-top"><span class="${newClass} break-all">${nv || '<span class="text-gray-300 dark:text-slate-600 italic">kosong</span>'}</span></td>
            </tr>`;
            }

            html += '</tbody></table></div>';

            const changedCount = allKeys.filter(k => formatVal(oldV[k]) !== formatVal(newV[k])).length;
            html = `<div class="flex items-center gap-2 mb-3">
            <span class="text-xs text-gray-400 dark:text-slate-500">${changedCount} field berubah dari ${allKeys.length} total</span>
        </div>` + html;

            return html;
        }

        function renderMetaTab(data) {
            if (!data?.log) return '';
            const l = data.log;
            const rows = [
                ['ID', l.id],
                ['Aksi', l.action],
                ['User', `${l.user_name} (${l.user_role || '-'})`],
                ['Model', l.model_type ? `${l.model_type} #${l.model_id}` : '—'],
                ['IP Address', l.ip_address || '—'],
                ['User Agent', l.user_agent || '—'],
                ['AI Action', l.is_ai_action ? `Ya (${l.ai_tool_name || '-'})` : 'Tidak'],
                ['Waktu', l.created_at],
                ['Rolled Back', l.rolled_back_at ? `${l.rolled_back_at} oleh ${l.rolled_back_by || '-'}` : 'Tidak'],
            ];

            let html = '<div class="space-y-2">';
            for (const [label, value] of rows) {
                html += `<div class="flex items-start gap-3 py-2 border-b border-gray-50 dark:border-white/5">
                <span class="text-xs font-medium text-gray-500 dark:text-slate-400 w-28 shrink-0">${label}</span>
                <span class="text-xs text-gray-900 dark:text-white break-all">${value ?? '—'}</span>
            </div>`;
            }
            html += '</div>';
            return html;
        }

        function renderTimelineTab(data) {
            if (!data?.timeline?.length)
            return '<p class="text-sm text-gray-400 text-center py-8">Tidak ada timeline untuk entry ini.</p>';

            let html = '<div class="relative pl-6">';
            html += '<div class="absolute left-2 top-0 bottom-0 w-px bg-gray-200 dark:bg-white/10"></div>';

            for (const t of data.timeline) {
                const active = t.is_current;
                const dotClass = active ? 'bg-blue-500 ring-2 ring-blue-500/30' : (t.has_diff ? 'bg-emerald-500' :
                    'bg-gray-300 dark:bg-slate-600');

                html += `<div class="relative flex items-start gap-3 pb-4 ${active ? '' : 'opacity-70'}">
                <div class="absolute left-[-16px] top-1 w-2.5 h-2.5 rounded-full ${dotClass}"></div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-gray-900 dark:text-white">${t.action}</span>
                        ${active ? '<span class="text-xs bg-blue-500/20 text-blue-400 px-1.5 py-0.5 rounded-full">current</span>' : ''}
                        ${t.has_diff ? `<button onclick="openDetail(${t.id})" class="text-xs text-blue-400 hover:underline">lihat</button>` : ''}
                    </div>
                    <p class="text-xs text-gray-400 dark:text-slate-500">${t.user_name} · ${t.created_at}</p>
                </div>
            </div>`;
            }

            html += '</div>';
            return html;
        }

        function renderSection(title, data) {
            if (!data || Object.keys(data).length === 0) return '';
            let html = `<p class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase mb-2">${title}</p>`;
            html +=
                '<div class="bg-gray-50 dark:bg-white/5 rounded-xl p-3 mb-4 font-mono text-xs text-gray-700 dark:text-slate-300 overflow-auto max-h-48">';
            html += '<pre>' + JSON.stringify(data, null, 2) + '</pre></div>';
            return html;
        }

        function formatVal(v) {
            if (v === null || v === undefined) return '';
            if (typeof v === 'object') return JSON.stringify(v);
            return String(v);
        }
    </script>
</x-app-layout>
