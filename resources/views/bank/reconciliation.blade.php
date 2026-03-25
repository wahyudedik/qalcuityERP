<x-app-layout>
    <x-slot name="header">Rekonsiliasi Bank</x-slot>

    <div class="space-y-6">

        {{-- Summary Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">Total Mutasi</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $summary['total'] }}</p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">Matched</p>
                <p class="text-2xl font-bold text-green-500 mt-1">{{ $summary['matched'] }}</p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">Unmatched</p>
                <p class="text-2xl font-bold text-amber-500 mt-1">{{ $summary['unmatched'] }}</p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">Total Kredit</p>
                <p class="text-lg font-bold text-green-500 mt-1">Rp {{ number_format($summary['credit'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">Total Debit</p>
                <p class="text-lg font-bold text-red-500 mt-1">Rp {{ number_format($summary['debit'], 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Rekening</label>
                    <select name="account_id" class="bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Rekening</option>
                        @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" @selected(request('account_id') == $acc->id)>{{ $acc->bank_name }} — {{ $acc->account_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Status</label>
                    <select name="status" class="bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua</option>
                        <option value="unmatched" @selected(request('status')==='unmatched')>Unmatched</option>
                        <option value="matched" @selected(request('status')==='matched')>Matched</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Dari</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700">Filter</button>
                @if(request()->hasAny(['account_id','status','from','to']))
                <a href="{{ route('bank.reconciliation') }}" class="px-4 py-2 border border-gray-200 dark:border-white/10 rounded-xl text-sm text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Reset</a>
                @endif
            </form>
        </div>

        {{-- Import CSV --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Import Mutasi Rekening</h2>
            <form method="POST" action="{{ route('bank.import') }}" enctype="multipart/form-data" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">Rekening Bank</label>
                    <select name="bank_account_id" required class="bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                        <option value="">Pilih rekening</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->bank_name }} — {{ $acc->account_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">File CSV Mutasi</label>
                    <input type="file" name="csv_file" accept=".csv,.txt" required
                        class="w-full sm:w-auto bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition">Import</button>
            </form>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-2">Format CSV: tanggal, deskripsi, tipe (debit/kredit), jumlah</p>
        </div>

        {{-- AI Auto-Match Banner --}}
        @php $unmatchedCount = $statements->where('status','unmatched')->count(); @endphp
        @if($unmatchedCount > 0)
        <div id="ai-banner" class="bg-purple-50 dark:bg-purple-500/10 border border-purple-200 dark:border-purple-500/20 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347a3.5 3.5 0 01-4.95 0l-.347-.347z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-purple-700 dark:text-purple-300">{{ $unmatchedCount }} transaksi belum dicocokkan</p>
                    <p class="text-xs text-purple-600 dark:text-purple-400">AI mencocokkan berdasarkan jumlah, tanggal, deskripsi, dan referensi</p>
                </div>
            </div>
            <button id="btn-auto-match" onclick="runAutoMatch()"
                class="shrink-0 px-4 py-2 bg-purple-600 text-white text-sm rounded-xl hover:bg-purple-700 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Auto-Match AI
            </button>
        </div>
        @endif

        {{-- Statements Table --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between flex-wrap gap-2">
                <h2 class="font-semibold text-gray-900 dark:text-white">Mutasi Rekening</h2>
                <div class="flex gap-2 text-xs">
                    <span class="px-2 py-1 bg-green-500/20 text-green-400 rounded-full">{{ $statements->where('status','matched')->count() }} matched</span>
                    <span class="px-2 py-1 bg-amber-500/20 text-amber-400 rounded-full">{{ $statements->where('status','unmatched')->count() }} unmatched</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Rekening</th>
                            <th class="px-4 py-3 text-left">Deskripsi</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Tipe</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left">AI Match</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($statements as $stmt)
                        <tr id="row-{{ $stmt->id }}" class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                            <td class="px-4 py-3 text-gray-500 dark:text-slate-400 whitespace-nowrap text-xs">
                                {{ $stmt->transaction_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-slate-400 text-xs hidden sm:table-cell">
                                {{ $stmt->bankAccount?->bank_name }}
                            </td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white max-w-xs">
                                <p class="truncate text-sm">{{ $stmt->description }}</p>
                                @if($stmt->reference)
                                <p class="text-xs text-gray-400 dark:text-slate-500">Ref: {{ $stmt->reference }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $stmt->type === 'credit' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                    {{ $stmt->type === 'credit' ? 'Kredit' : 'Debit' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-medium {{ $stmt->type === 'credit' ? 'text-green-400' : 'text-red-400' }}">
                                Rp {{ number_format($stmt->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span id="status-{{ $stmt->id }}" class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $stmt->status === 'matched' ? 'bg-green-500/20 text-green-400' : 'bg-amber-500/20 text-amber-400' }}">
                                    {{ $stmt->status === 'matched' ? 'Matched' : 'Unmatched' }}
                                </span>
                            </td>
                            {{-- AI Match Column --}}
                            <td class="px-4 py-3 min-w-[200px]">
                                @if($stmt->status === 'matched')
                                <span class="text-xs text-gray-400 dark:text-slate-500">—</span>
                                @else
                                <div id="ai-cell-{{ $stmt->id }}" class="text-xs text-slate-400 italic">Menunggu...</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($stmt->status === 'unmatched')
                                <div class="flex flex-col items-center gap-1">
                                    <button onclick="openMatchModal({{ $stmt->id }})"
                                        id="btn-detail-{{ $stmt->id }}"
                                        class="text-xs text-purple-400 hover:text-purple-300 hover:underline hidden">
                                        Detail AI
                                    </button>
                                    <button onclick="openManualMatch({{ $stmt->id }}, '{{ addslashes($stmt->description) }}', {{ $stmt->amount }})"
                                        class="text-xs text-blue-400 hover:underline">Manual</button>
                                </div>
                                @else
                                <span class="text-xs text-slate-600">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-400 dark:text-slate-500">
                                Belum ada data mutasi. Import file CSV terlebih dahulu.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">
                {{ $statements->links() }}
            </div>
        </div>
    </div>

    {{-- AI Match Detail Modal --}}
    <div id="modal-ai-match" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347a3.5 3.5 0 01-4.95 0l-.347-.347z"/>
                    </svg>
                    AI Match Detail
                </h3>
                <button onclick="document.getElementById('modal-ai-match').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <div id="modal-ai-body" class="p-6">
                <div class="flex items-center justify-center py-8">
                    <svg class="animate-spin w-6 h-6 text-purple-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Manual Match Modal --}}
    <div id="modal-manual-match" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Manual Match</h3>
                <button onclick="document.getElementById('modal-manual-match').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <div class="p-6">
                <div class="mb-4 bg-gray-50 dark:bg-white/5 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Mutasi Bank</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white" id="manual-stmt-desc"></p>
                    <p class="text-sm font-bold text-blue-500 mt-1" id="manual-stmt-amount"></p>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-2 uppercase font-semibold">Pilih Transaksi ERP</p>
                <div class="space-y-2 max-h-60 overflow-y-auto" id="manual-erp-list">
                    @forelse($unmatchedErp ?? collect() as $erp)
                    <button onclick="applyManualMatch({{ $erp['id'] }})"
                        class="w-full flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:border-blue-400 dark:hover:border-blue-500/40 transition text-left">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $erp['number'] }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $erp['date'] }} · {{ Str::limit($erp['description'], 50) }}</p>
                        </div>
                        <span class="text-sm font-medium shrink-0 ml-3 {{ $erp['type'] === 'debit' ? 'text-green-500' : 'text-red-500' }}">
                            Rp {{ number_format($erp['amount'], 0, ',', '.') }}
                        </span>
                    </button>
                    @empty
                    <p class="text-sm text-gray-400 dark:text-slate-500 text-center py-4">Tidak ada transaksi ERP yang tersedia. Pastikan sudah ada jurnal yang diposting.</p>
                    @endforelse
                </div>
                <form id="form-manual-match" method="POST" class="mt-4 hidden">
                    @csrf
                    <input type="hidden" name="transaction_id" id="manual-tx-id">
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let aiResults = {};
    let currentManualStmtId = null;

    function openManualMatch(stmtId, desc, amount) {
        currentManualStmtId = stmtId;
        document.getElementById('manual-stmt-desc').textContent = desc;
        document.getElementById('manual-stmt-amount').textContent = 'Rp ' + parseInt(amount).toLocaleString('id-ID');
        document.getElementById('form-manual-match').action = '{{ url("bank/statements") }}/' + stmtId + '/match';
        document.getElementById('modal-manual-match').classList.remove('hidden');
    }

    function applyManualMatch(txId) {
        if (!currentManualStmtId) return;
        document.getElementById('manual-tx-id').value = txId;
        document.getElementById('form-manual-match').submit();
    }

    // ── Auto-match all unmatched ──────────────────────────────────────
    async function runAutoMatch() {
        const btn = document.getElementById('btn-auto-match');
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> Memproses...`;

        document.querySelectorAll('[id^="ai-cell-"]').forEach(el => {
            el.innerHTML = '<span class="animate-pulse text-slate-400">Menganalisis...</span>';
        });

        try {
            const res = await fetch('{{ route("bank.ai.match-all") }}');
            aiResults = await res.json();
            let autoApplied = 0;

            for (const [id, result] of Object.entries(aiResults)) {
                renderCell(id, result);
                if (result.status === 'matched' && result.confidence >= 85 && result.transaction) {
                    await applyMatch(id, result.transaction.id, true);
                    autoApplied++;
                }
            }

            btn.disabled = false;
            btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Selesai (${autoApplied} auto-applied)`;
            btn.classList.replace('bg-purple-600', 'bg-green-600');
            btn.classList.replace('hover:bg-purple-700', 'hover:bg-green-700');
        } catch(e) {
            btn.disabled = false;
            btn.innerHTML = 'Auto-Match AI';
        }
    }

    function renderCell(id, result) {
        const cell = document.getElementById('ai-cell-' + id);
        const detailBtn = document.getElementById('btn-detail-' + id);
        if (!cell) return;

        if (result.status === 'matched') {
            const tx = result.transaction;
            cell.innerHTML = `
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="px-2 py-0.5 rounded-full text-xs bg-green-500/20 text-green-400 border border-green-500/20 shrink-0">✓ ${result.confidence}%</span>
                    <span class="text-gray-700 dark:text-slate-300 truncate max-w-[130px] text-xs">${tx.number ?? tx.description ?? ''}</span>
                </div>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 leading-tight">${result.reasons.slice(0,2).join(' · ')}</p>`;
        } else if (result.status === 'suggestion') {
            const tx = result.transaction;
            cell.innerHTML = `
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="px-2 py-0.5 rounded-full text-xs bg-amber-500/20 text-amber-400 border border-amber-500/20 shrink-0">~ ${result.confidence}%</span>
                    <span class="text-gray-700 dark:text-slate-300 truncate max-w-[130px] text-xs">${tx.number ?? tx.description ?? ''}</span>
                </div>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 leading-tight">${result.reasons.slice(0,2).join(' · ')}</p>`;
        } else {
            const flag = result.flags?.[0] ?? 'Tidak ditemukan kecocokan';
            cell.innerHTML = `
                <span class="px-2 py-0.5 rounded-full text-xs bg-red-500/20 text-red-400 border border-red-500/20">✗ Tidak cocok</span>
                <p class="text-xs text-red-400/80 mt-0.5 leading-tight">${flag}</p>`;
        }

        if (detailBtn) detailBtn.classList.remove('hidden');
    }

    // ── Apply match ───────────────────────────────────────────────────
    async function applyMatch(stmtId, txId, silent = false) {
        try {
            await fetch('{{ url("bank/ai/apply-match") }}/' + stmtId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ transaction_id: txId }),
            });
            if (!silent) {
                const statusEl = document.getElementById('status-' + stmtId);
                if (statusEl) {
                    statusEl.textContent = 'Matched';
                    statusEl.className = 'px-2 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400';
                }
                document.getElementById('modal-ai-match').classList.add('hidden');
            }
        } catch(e) {}
    }

    // ── Modal detail ──────────────────────────────────────────────────
    async function openMatchModal(id) {
        document.getElementById('modal-ai-match').classList.remove('hidden');

        const cached = aiResults[id];
        if (cached) { renderModal(id, cached); return; }

        document.getElementById('modal-ai-body').innerHTML = `
            <div class="flex items-center justify-center py-8">
                <svg class="animate-spin w-6 h-6 text-purple-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
            </div>`;

        try {
            const res = await fetch('{{ url("bank/ai/match") }}/' + id);
            const data = await res.json();
            aiResults[id] = data;
            renderCell(id, data);
            renderModal(id, data);
        } catch(e) {
            document.getElementById('modal-ai-body').innerHTML = '<p class="text-red-400 text-sm p-4">Gagal memuat data AI.</p>';
        }
    }

    function renderModal(id, result) {
        const tierBadge = {
            high:   'bg-green-500/20 text-green-400 border-green-500/20',
            medium: 'bg-amber-500/20 text-amber-400 border-amber-500/20',
            none:   'bg-red-500/20 text-red-400 border-red-500/20',
        };
        const tierLabel = { high: 'Confidence Tinggi', medium: 'Perlu Konfirmasi', none: 'Tidak Cocok' };

        let txBlock = '';
        if (result.transaction) {
            const tx = result.transaction;
            const canApply = result.status !== 'unmatched';
            txBlock = `
                <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 mb-4 border border-gray-200 dark:border-white/10">
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-2 uppercase font-semibold">Kandidat Terbaik</p>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div><p class="text-xs text-gray-400">No. Transaksi</p><p class="font-medium text-gray-900 dark:text-white">${tx.number ?? '—'}</p></div>
                        <div><p class="text-xs text-gray-400">Tanggal</p><p class="font-medium text-gray-900 dark:text-white">${tx.date ?? '—'}</p></div>
                        <div><p class="text-xs text-gray-400">Jumlah</p><p class="font-medium text-gray-900 dark:text-white">Rp ${Number(tx.amount).toLocaleString('id-ID')}</p></div>
                        <div><p class="text-xs text-gray-400">Tipe</p><p class="font-medium text-gray-900 dark:text-white">${tx.type ?? '—'}</p></div>
                        <div class="col-span-2"><p class="text-xs text-gray-400">Deskripsi</p><p class="font-medium text-gray-900 dark:text-white">${tx.description ?? '—'}</p></div>
                    </div>
                    ${canApply ? `<button onclick="applyMatch(${id}, ${tx.id})"
                        class="mt-3 w-full py-2 bg-green-600 text-white text-sm rounded-xl hover:bg-green-700 transition font-medium">
                        ✓ Terapkan Match Ini
                    </button>` : ''}
                </div>`;
        }

        const reasonsBlock = result.reasons?.length ? `
            <div class="mb-4">
                <p class="text-xs text-gray-500 dark:text-slate-400 uppercase font-semibold mb-2">Alasan Kecocokan</p>
                <ul class="space-y-1">
                    ${result.reasons.map(r => `<li class="flex items-center gap-2 text-sm text-gray-700 dark:text-slate-300"><span class="text-green-400">✓</span>${r}</li>`).join('')}
                </ul>
            </div>` : '';

        const flagsBlock = (result.flags?.length || result.explanation) ? `
            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl p-4 mb-4">
                <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase mb-2">Alasan Tidak Cocok</p>
                ${(result.flags ?? []).map(f => `<p class="text-sm text-red-700 dark:text-red-300 flex items-start gap-2 mb-1"><span class="shrink-0">⚠</span>${f}</p>`).join('')}
                ${result.explanation ? `<p class="text-sm text-red-600 dark:text-red-400 mt-2 italic">${result.explanation}</p>` : ''}
            </div>` : '';

        const altBlock = result.alternatives?.length ? `
            <div>
                <p class="text-xs text-gray-500 dark:text-slate-400 uppercase font-semibold mb-2">Alternatif Lain</p>
                <div class="space-y-2">
                    ${result.alternatives.map(alt => `
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                        <div class="text-sm min-w-0 flex-1 mr-3">
                            <p class="font-medium text-gray-900 dark:text-white truncate">${alt.number ?? alt.description ?? '—'}</p>
                            <p class="text-xs text-gray-400">${alt.date} · Rp ${Number(alt.amount).toLocaleString('id-ID')}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-xs text-amber-400">${alt.score}%</span>
                            <button onclick="applyMatch(${id}, ${alt.id})"
                                class="text-xs px-2 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Pilih</button>
                        </div>
                    </div>`).join('')}
                </div>
            </div>` : '';

        document.getElementById('modal-ai-body').innerHTML = `
            <div class="flex items-center gap-3 mb-5">
                <span class="px-3 py-1 rounded-full text-sm border ${tierBadge[result.tier] ?? tierBadge.none}">
                    ${result.confidence}% — ${tierLabel[result.tier] ?? 'Tidak Diketahui'}
                </span>
            </div>
            ${txBlock}${reasonsBlock}${flagsBlock}${altBlock}`;
    }
    </script>
    @endpush
</x-app-layout>
