<x-app-layout>
    <x-slot name="header">Audit Trail</x-slot>

    <div class="space-y-4">

        {{-- Retention & Stats Bar --}}
        <div
            class="flex flex-wrap items-center justify-between gap-3 bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
                <span><strong class="text-gray-900">{{ number_format($totalLogs) }}</strong> total
                    log</span>
                @if ($oldestLog)
                    <span>Sejak {{ $oldestLog->format('d M Y') }}</span>
                @endif
                <span class="px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 font-medium">Retensi:
                    {{ $retentionDays }} hari</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1">
                    <a href="{{ route('audit.export', array_merge(request()->only(['date_from', 'date_to', 'module']), ['format' => 'csv'])) }}"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        CSV
                    </a>
                    <a href="{{ route('audit.export', array_merge(request()->only(['date_from', 'date_to', 'module']), ['format' => 'xlsx'])) }}"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border border-green-300 text-green-600 hover:bg-green-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Excel
                    </a>
                </div>
                @if (in_array(auth()->user()->role, ['admin', 'manager', 'super_admin']))
                    <button onclick="openComplianceModal()"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium border border-indigo-300 text-indigo-600 hover:bg-indigo-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Compliance Report
                    </button>
                @endif
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="text-xs font-semibold text-gray-500 uppercase">Quick Filter:</span>
                <a href="{{ route('audit.index') }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ !request()->hasAny(['is_ai', 'action', 'user_id', 'module']) ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Semua
                </a>
                <a href="{{ request()->fullUrlWithQuery(['is_ai' => '1', 'page' => null]) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request('is_ai') === '1' ? 'bg-purple-600 text-white' : 'bg-purple-100 text-purple-700 hover:bg-purple-200' }}">
                    🤖 AI ({{ $aiCount }} hari ini)
                </a>
                <a href="{{ request()->fullUrlWithQuery(['is_ai' => '0', 'page' => null]) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request('is_ai') === '0' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    👤 Manual
                </a>
                <a href="{{ request()->fullUrlWithQuery(['action' => 'rollback', 'page' => null]) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request('action') === 'rollback' ? 'bg-amber-600 text-white' : 'bg-amber-100 text-amber-700 hover:bg-amber-200' }}">
                    ↩ Rollback
                </a>
            </div>
            <form method="GET" class="flex flex-wrap gap-2">
                @if (request('is_ai') !== null)
                    <input type="hidden" name="is_ai" value="{{ request('is_ai') }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi..."
                    class="flex-1 min-w-[150px] px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="user_id"
                    class="px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900">
                    <option value="">Semua User</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}
                            ({{ $u->role }})
                        </option>
                    @endforeach
                </select>
                <select name="module"
                    class="px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900">
                    <option value="">Semua Modul</option>
                    @foreach ($modules as $m)
                        <option value="{{ $m }}" @selected(request('module') === $m)>{{ $m }}</option>
                    @endforeach
                </select>
                <select name="action"
                    class="px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900">
                    <option value="">Semua Aksi</option>
                    @foreach ($actions as $a)
                        <option value="{{ $a }}" @selected(request('action') === $a)>{{ $a }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700">Filter</button>
                @if (request()->hasAny(['search', 'user_id', 'module', 'action', 'date_from', 'date_to', 'is_ai']))
                    <a href="{{ route('audit.index') }}"
                        class="px-4 py-2 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50">Reset</a>
                @endif
            </form>
        </div>

        {{-- Log Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Waktu</th>
                            <th class="px-4 py-3 text-left">User</th>
                            <th class="px-4 py-3 text-left">Aksi</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Modul</th>
                            <th class="px-4 py-3 text-left">Deskripsi</th>
                            <th class="px-4 py-3 text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 {{ $log->is_ai_action ? 'bg-purple-50/30' : '' }} {{ $log->rolled_back_at ? 'opacity-60' : '' }}"
                                id="row-{{ $log->id }}">
                                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                    {{ $log->created_at->format('d/m/Y') }}<br>
                                    <span
                                        class="text-gray-400">{{ $log->created_at->format('H:i:s') }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="w-5 h-5 rounded-full {{ $log->is_ai_action ? 'bg-purple-100' : 'bg-gray-100' }} flex items-center justify-center text-xs">
                                            {{ $log->is_ai_action ? '🤖' : '👤' }}
                                        </span>
                                        <span
                                            class="text-xs font-medium text-gray-900">{{ $log->user?->name ?? 'System' }}</span>
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
                                <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500">
                                    {{ $log->model_type ? class_basename($log->model_type) : '—' }}
                                    @if ($log->model_id)
                                        <span class="text-gray-400">#{{ $log->model_id }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-700 max-w-xs">
                                    <span class="line-clamp-2">{{ $log->description }}</span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <button onclick="openDetail({{ $log->id }})"
                                        class="text-xs text-blue-500 hover:text-blue-400 hover:underline">Detail</button>
                                    @if ($log->old_values || $log->new_values)
                                        <span class="text-gray-300 mx-0.5">·</span>
                                        <button onclick="openDiff({{ $log->id }})"
                                            class="text-xs text-emerald-500 hover:text-emerald-400 hover:underline">Diff</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                    Tidak ada log aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
        </div>
    </div>

    {{-- ═══════════ Detail + Diff Modal ═══════════ --}}
    <div id="modal-detail"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-3xl shadow-xl max-h-[90vh] flex flex-col">
            {{-- Header --}}
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                <div>
                    <h3 class="font-semibold text-gray-900 text-sm" id="detail-title">Detail Audit Log
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5" id="detail-subtitle"></p>
                </div>
                <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="flex border-b border-gray-100 px-6 shrink-0">
                <button onclick="showTab('diff')" id="tab-diff"
                    class="detail-tab px-4 py-2.5 text-xs font-medium border-b-2 border-blue-500 text-blue-500 -mb-px">Perubahan</button>
                <button onclick="showTab('meta')" id="tab-meta"
                    class="detail-tab px-4 py-2.5 text-xs font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 -mb-px">Metadata</button>
                <button onclick="showTab('timeline')" id="tab-timeline"
                    class="detail-tab px-4 py-2.5 text-xs font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 -mb-px">Timeline</button>
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
            <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100 shrink-0"
                id="detail-footer" style="display:none">
                <div id="rollback-status" class="text-xs text-gray-400"></div>
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

    {{-- ═══════════ Compliance Report Modal ═══════════ --}}
    <div id="modal-compliance"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div>
                    <h3 class="font-semibold text-gray-900 text-sm">Compliance Report (SOX)</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Export audit trail untuk keperluan
                        kepatuhan / auditor eksternal</p>
                </div>
                <button onclick="closeComplianceModal()"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="form-compliance" action="{{ route('audit.compliance-report') }}" method="GET"
                class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Dari
                            Tanggal</label>
                        <input type="date" name="date_from" id="compliance-date-from" required
                            value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}"
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sampai
                            Tanggal</label>
                        <input type="date" name="date_to" id="compliance-date-to" required
                            value="{{ request('date_to', now()->format('Y-m-d')) }}"
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div
                    class="bg-indigo-50 rounded-xl p-3 text-xs text-indigo-700 space-y-1">
                    <p class="font-semibold">Format laporan mencakup:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-indigo-600">
                        <li>Semua event dengan timestamp ISO 8601</li>
                        <li>Nama user, role, dan IP address</li>
                        <li>Field yang berubah + nilai before/after (JSON)</li>
                        <li>Flag AI-generated action</li>
                        <li>Rollback history</li>
                        <li>Integrity hash (SHA-256) per baris</li>
                    </ul>
                </div>
                <div class="flex items-center justify-end gap-3 pt-1">
                    <button type="button" onclick="closeComplianceModal()"
                        class="px-4 py-2 rounded-xl text-sm border border-gray-200 text-gray-600 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download CSV
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════ Rollback Conflict Modal ═══════════ --}}
    <div id="modal-conflict"
        class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div
            class="bg-white rounded-2xl border border-amber-300 w-full max-w-lg shadow-xl">
            <div class="px-6 py-4 border-b border-amber-100 flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-amber-500/20 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-amber-700 text-sm">Konflik Terdeteksi</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Field berikut telah diubah setelah log
                        ini
                        dicatat. Rollback akan menimpa perubahan terbaru.</p>
                </div>
            </div>
            <div class="p-6">
                <div id="conflict-table" class="overflow-x-auto mb-5"></div>
                <div class="flex items-center justify-end gap-3">
                    <button onclick="closeConflictModal()"
                        class="px-4 py-2 rounded-xl text-sm border border-gray-200 text-gray-600 hover:bg-gray-50">
                        Batal
                    </button>
                    <button onclick="forceRollback()"
                        class="flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-medium transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                        Tetap Rollback
                    </button>
                </div>
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

        function openComplianceModal() {
            document.getElementById('modal-compliance').classList.remove('hidden');
        }

        function closeComplianceModal() {
            document.getElementById('modal-compliance').classList.add('hidden');
        }

        function closeConflictModal() {
            document.getElementById('modal-conflict').classList.add('hidden');
        }

        function showTab(tab) {
            document.querySelectorAll('.detail-tab').forEach(t => {
                t.classList.remove('border-blue-500', 'text-blue-500');
                t.classList.add('border-transparent', 'text-gray-500');
            });
            const active = document.getElementById('tab-' + tab);
            active.classList.add('border-blue-500', 'text-blue-500');
            active.classList.remove('border-transparent', 'text-gray-500');

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
                } else if (data.conflict) {
                    // Show conflict modal instead of plain alert
                    btn.disabled = false;
                    btn.textContent = '↩ Rollback Perubahan Ini';
                    showConflictModal(data.conflicts);
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

        function showConflictModal(conflicts) {
            let html = `<table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="text-gray-500 bg-gray-50">
                        <th class="text-left py-2 px-3 font-semibold rounded-tl-lg">Field</th>
                        <th class="text-left py-2 px-3 font-semibold">Dicatat (saat perubahan)</th>
                        <th class="text-left py-2 px-3 font-semibold rounded-tr-lg">Nilai Saat Ini</th>
                    </tr>
                </thead>
                <tbody>`;

            for (const [field, vals] of Object.entries(conflicts)) {
                html += `<tr class="border-b border-gray-50">
                    <td class="py-2 px-3 font-medium text-gray-700">${field}</td>
                    <td class="py-2 px-3 text-red-500 line-through break-all">${vals.recorded_at_time_of_change ?? '—'}</td>
                    <td class="py-2 px-3 text-amber-500 font-medium break-all">${vals.current_value ?? '—'}</td>
                </tr>`;
            }
            html += '</tbody></table>';

            document.getElementById('conflict-table').innerHTML = html;
            document.getElementById('modal-conflict').classList.remove('hidden');
        }

        async function forceRollback() {
            closeConflictModal();
            const btn = document.getElementById('btn-rollback');
            btn.disabled = true;
            btn.textContent = 'Rolling back...';

            try {
                const res = await fetch(`/audit/${currentLogId}/rollback`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        force: 1
                    }),
                });
                const data = await res.json();

                if (data.ok) {
                    btn.style.display = 'none';
                    document.getElementById('rollback-status').innerHTML =
                        '<span class="text-green-400">✓ Rollback berhasil (force)!</span>';
                    const row = document.getElementById('row-' + currentLogId);
                    if (row) row.classList.add('opacity-60');
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
                    <tr class="text-gray-500">
                        <th class="text-left py-2.5 px-3 bg-gray-50 rounded-tl-lg font-semibold w-1/4">Field</th>
                        <th class="text-left py-2.5 px-3 bg-red-50 font-semibold w-[37.5%]">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-400"></span>Sebelum</span>
                        </th>
                        <th class="text-left py-2.5 px-3 bg-green-50 rounded-tr-lg font-semibold w-[37.5%]">
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
                let oldClass = 'text-gray-500';
                let newClass = 'text-gray-500';

                if (changed) {
                    rowBg = 'bg-amber-50/50';
                    oldClass = isRemoved ? 'text-red-500 bg-red-50 line-through rounded px-1' : (isNew ?
                        'text-gray-300' :
                        'text-red-500 bg-red-50 line-through rounded px-1');
                    newClass = isNew ? 'text-green-600 bg-green-50 font-medium rounded px-1' : (
                        isRemoved ? 'text-gray-300' :
                        'text-green-600 bg-green-50 font-medium rounded px-1');
                }

                html += `<tr class="${rowBg} border-b border-gray-50">
                <td class="py-2 px-3 font-medium text-gray-700 whitespace-nowrap align-top">${key} ${changed ? '<span class="text-amber-400 ml-1">●</span>' : ''}</td>
                <td class="py-2 px-3 align-top"><span class="${oldClass} break-all">${ov || '<span class="text-gray-300 italic">kosong</span>'}</span></td>
                <td class="py-2 px-3 align-top"><span class="${newClass} break-all">${nv || '<span class="text-gray-300 italic">kosong</span>'}</span></td>
            </tr>`;
            }

            html += '</tbody></table></div>';

            const changedCount = allKeys.filter(k => formatVal(oldV[k]) !== formatVal(newV[k])).length;
            html = `<div class="flex items-center gap-2 mb-3">
            <span class="text-xs text-gray-400">${changedCount} field berubah dari ${allKeys.length} total</span>
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
                html += `<div class="flex items-start gap-3 py-2 border-b border-gray-50">
                <span class="text-xs font-medium text-gray-500 w-28 shrink-0">${label}</span>
                <span class="text-xs text-gray-900 break-all">${value ?? '—'}</span>
            </div>`;
            }
            html += '</div>';
            return html;
        }

        function renderTimelineTab(data) {
            if (!data?.timeline?.length)
                return '<p class="text-sm text-gray-400 text-center py-8">Tidak ada timeline untuk entry ini.</p>';

            let html = '<div class="relative pl-6">';
            html += '<div class="absolute left-2 top-0 bottom-0 w-px bg-gray-200"></div>';

            for (const t of data.timeline) {
                const active = t.is_current;
                const dotClass = active ? 'bg-blue-500 ring-2 ring-blue-500/30' : (t.has_diff ? 'bg-emerald-500' :
                    'bg-gray-300');

                html += `<div class="relative flex items-start gap-3 pb-4 ${active ? '' : 'opacity-70'}">
                <div class="absolute left-[-16px] top-1 w-2.5 h-2.5 rounded-full ${dotClass}"></div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-gray-900">${t.action}</span>
                        ${active ? '<span class="text-xs bg-blue-500/20 text-blue-400 px-1.5 py-0.5 rounded-full">current</span>' : ''}
                        ${t.has_diff ? `<button onclick="openDetail(${t.id})" class="text-xs text-blue-400 hover:underline">lihat</button>` : ''}
                    </div>
                    <p class="text-xs text-gray-400">${t.user_name} · ${t.created_at}</p>
                </div>
            </div>`;
            }

            html += '</div>';
            return html;
        }

        function renderSection(title, data) {
            if (!data || Object.keys(data).length === 0) return '';
            let html = `<p class="text-xs font-semibold text-gray-500 uppercase mb-2">${title}</p>`;
            html +=
                '<div class="bg-gray-50 rounded-xl p-3 mb-4 font-mono text-xs text-gray-700 overflow-auto max-h-48">';
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
