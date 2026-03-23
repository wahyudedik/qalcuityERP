<x-app-layout>
<x-slot name="title">Monitoring — Qalcuity ERP</x-slot>
<x-slot name="header">Monitoring & Log</x-slot>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-500/20 border border-green-500/30 text-green-400 text-sm rounded-xl">{{ session('success') }}</div>
@endif

{{-- UNIVERSAL DETAIL MODAL --}}
<div x-data="detailModal()" x-cloak>

<div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm" @click="close()"></div>
<div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="bg-[#0f172a] border border-white/10 rounded-2xl w-full max-w-3xl max-h-[90vh] flex flex-col shadow-2xl">
        <div class="flex items-start justify-between gap-4 px-6 py-4 border-b border-white/10 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full shrink-0 border" :class="badgeClass()">
                    <span x-text="badgeLabel()"></span>
                </span>
                <p class="text-sm font-semibold text-slate-100 truncate" x-text="title()"></p>
            </div>
            <button @click="close()" class="shrink-0 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto flex-1 px-6 py-5 space-y-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <template x-for="m in metaFields()" :key="m.label">
                    <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-3">
                        <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-1" x-text="m.label"></p>
                        <p class="text-xs font-medium break-all" :class="m.color||'text-slate-200'" x-text="m.value||'—'"></p>
                    </div>
                </template>
            </div>
            <template x-if="type==='error' && d.file">
                <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-3">
                    <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">File</p>
                    <p class="text-xs text-amber-300 font-mono break-all" x-text="d.file+':'+d.line"></p>
                </div>
            </template>
            <template x-if="(type==='error'||type==='activity') && (d.url||d.ip||d.ip_address)">
                <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-3">
                    <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">Request</p>
                    <template x-if="d.url">
                        <p class="text-xs text-slate-200 font-mono break-all">
                            <span class="text-blue-400 font-bold mr-2" x-text="d.method"></span><span x-text="d.url"></span>
                        </p>
                    </template>
                    <template x-if="d.ip||d.ip_address">
                        <p class="text-[10px] text-slate-500 mt-1">IP: <span class="text-slate-400 font-mono" x-text="d.ip||d.ip_address"></span></p>
                    </template>
                    <template x-if="d.user_agent">
                        <p class="text-[10px] text-slate-500 mt-0.5 break-all">UA: <span class="text-slate-400" x-text="d.user_agent"></span></p>
                    </template>
                </div>
            </template>
            <template x-if="type==='activity' && (d.old_values||d.new_values)">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <template x-if="d.old_values">
                        <div class="bg-red-500/5 border border-red-500/20 rounded-xl px-4 py-3">
                            <p class="text-[10px] text-red-400 uppercase tracking-wider mb-2">Nilai Lama</p>
                            <pre class="text-[11px] text-slate-300 font-mono whitespace-pre-wrap break-all" x-text="JSON.stringify(d.old_values,null,2)"></pre>
                        </div>
                    </template>
                    <template x-if="d.new_values">
                        <div class="bg-green-500/5 border border-green-500/20 rounded-xl px-4 py-3">
                            <p class="text-[10px] text-green-400 uppercase tracking-wider mb-2">Nilai Baru</p>
                            <pre class="text-[11px] text-slate-300 font-mono whitespace-pre-wrap break-all" x-text="JSON.stringify(d.new_values,null,2)"></pre>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="type==='ai' && d.breakdown && d.breakdown.length">
                <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-3">
                    <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-3">Riwayat Bulanan</p>
                    <div class="space-y-2">
                        <template x-for="b in d.breakdown" :key="b.month">
                            <div class="flex items-center gap-3">
                                <span class="text-[11px] text-slate-400 w-16 shrink-0" x-text="b.month"></span>
                                <div class="flex-1 h-1.5 bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 rounded-full" :style="'width:'+b.pct+'%'"></div>
                                </div>
                                <span class="text-[11px] text-slate-300 w-24 text-right shrink-0" x-text="Number(b.messages).toLocaleString()+' msg / '+Number(b.tokens).toLocaleString()+' tok'"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
            <template x-if="type==='anomaly' && d.description">
                <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-3">
                    <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-2">Deskripsi Lengkap</p>
                    <p class="text-sm text-slate-200 leading-relaxed" x-text="d.description"></p>
                </div>
            </template>
            <template x-if="type==='anomaly' && d.data">
                <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-3">
                    <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-2">Data Payload</p>
                    <pre class="text-[11px] text-slate-300 font-mono whitespace-pre-wrap break-all" x-text="JSON.stringify(d.data,null,2)"></pre>
                </div>
            </template>
            <template x-if="type==='error' && d.context">
                <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-3">
                    <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-2">Context</p>
                    <pre class="text-[11px] text-slate-300 font-mono whitespace-pre-wrap break-all" x-text="JSON.stringify(d.context,null,2)"></pre>
                </div>
            </template>
            <template x-if="type==='error' && d.trace">
                <div class="bg-black/40 border border-white/10 rounded-xl px-4 py-3">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[10px] text-slate-500 uppercase tracking-wider">Stack Trace</p>
                        <button @click="copy(d.trace)" class="text-[10px] text-blue-400 hover:text-blue-300 border border-blue-500/30 px-2 py-0.5 rounded-lg transition" x-text="copied?'✓ Tersalin':'Salin'"></button>
                    </div>
                    <pre class="text-[11px] text-slate-400 font-mono whitespace-pre-wrap break-all leading-relaxed" x-text="d.trace"></pre>
                </div>
            </template>
        </div>
        <div class="px-6 py-4 border-t border-white/10 flex items-center justify-between gap-3 shrink-0">
            <span class="text-[11px] text-slate-500" x-text="footerText()"></span>
            <div class="flex gap-2">
                <template x-if="type==='error' && !d.is_resolved">
                    <form :action="'/super-admin/monitoring/errors/'+d.id+'/resolve'" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-xs bg-green-500/15 hover:bg-green-500/25 text-green-400 border border-green-500/30 rounded-xl transition">✓ Tandai Resolved</button>
                    </form>
                </template>
                <button @click="close()" class="px-4 py-2 text-xs bg-white/5 hover:bg-white/10 text-slate-300 border border-white/10 rounded-xl transition">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- TAB NAVIGATION --}}
<div class="flex gap-1 mb-6 bg-white/5 border border-white/10 rounded-2xl p-1 w-fit flex-wrap">
    @foreach([
        ['tab'=>'errors',   'label'=>'Error Log',     'icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
        ['tab'=>'ai',       'label'=>'AI Usage',      'icon'=>'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2'],
        ['tab'=>'activity', 'label'=>'Activity Log',  'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['tab'=>'anomaly',  'label'=>'Anomali',       'icon'=>'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
        ['tab'=>'health',   'label'=>'System Health', 'icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
    ] as $t)
    <a href="?tab={{ $t['tab'] }}"
       class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition
              {{ $tab === $t['tab'] ? 'bg-blue-600 text-white' : 'text-slate-400 hover:text-white hover:bg-white/10' }}">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $t['icon'] }}"/>
        </svg>
        {{ $t['label'] }}
        @if($t['tab'] === 'errors' && $errorStats['unresolved'] > 0)
            <span class="text-[10px] bg-red-500 text-white px-1.5 py-0.5 rounded-full font-bold">{{ $errorStats['unresolved'] }}</span>
        @endif
        @if($t['tab'] === 'anomaly' && $anomalyStats['critical'] > 0)
            <span class="text-[10px] bg-red-500 text-white px-1.5 py-0.5 rounded-full font-bold">{{ $anomalyStats['critical'] }}</span>
        @endif
    </a>
    @endforeach
</div>

{{-- ═══ TAB: ERROR LOG ═══ --}}
@if($tab === 'errors')
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    @foreach([
        ['label'=>'Total Error',   'value'=>$errorStats['total'],      'color'=>'text-slate-300',  'bg'=>'bg-white/5'],
        ['label'=>'Belum Resolved','value'=>$errorStats['unresolved'], 'color'=>'text-red-400',    'bg'=>'bg-red-500/10'],
        ['label'=>'Hari Ini',      'value'=>$errorStats['today'],      'color'=>'text-orange-400', 'bg'=>'bg-orange-500/10'],
        ['label'=>'Critical',      'value'=>$errorStats['critical'],   'color'=>'text-red-400',    'bg'=>'bg-red-500/10'],
    ] as $s)
    <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-4 text-center {{ $s['bg'] }}">
        <p class="text-2xl font-bold {{ $s['color'] }}">{{ $s['value'] }}</p>
        <p class="text-xs text-slate-500 mt-1">{{ $s['label'] }}</p>
    </div>
    @endforeach
</div>
<div class="flex flex-wrap items-center gap-3 mb-4">
    <form method="GET" action="" class="flex gap-2 flex-wrap flex-1">
        <input type="hidden" name="tab" value="errors">
        <select name="level" class="px-3 py-2 rounded-xl border border-white/10 bg-white/5 text-sm text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Level</option>
            @foreach(['critical','error','warning','info'] as $lv)
            <option value="{{ $lv }}" @selected(request('level')===$lv)>{{ ucfirst($lv) }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm text-slate-400 cursor-pointer">
            <input type="checkbox" name="unresolved" value="1" @checked(request('unresolved')) class="rounded">
            Belum resolved
        </label>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-xl transition">Filter</button>
    </form>
    <div class="flex gap-2">
        <form method="POST" action="{{ route('super-admin.monitoring.resolve-all') }}">
            @csrf
            <button class="px-3 py-2 text-xs bg-green-500/15 hover:bg-green-500/25 text-green-400 border border-green-500/30 rounded-xl transition">✓ Resolve Semua</button>
        </form>
        <form method="POST" action="{{ route('super-admin.monitoring.clear-errors') }}">
            @csrf
            <button class="px-3 py-2 text-xs bg-red-500/15 hover:bg-red-500/25 text-red-400 border border-red-500/30 rounded-xl transition">🗑 Bersihkan Resolved</button>
        </form>
    </div>
</div>
<div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b border-white/10 bg-white/5">
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Level</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Pesan</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">URL</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Waktu</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @forelse($errors as $err)
            <tr class="hover:bg-white/5 transition cursor-pointer {{ $err->is_resolved ? 'opacity-60' : '' }}"
                @click="show('error', rowData($el))">
                <script type="application/json" class="row-data">{!! json_encode([
                    'id'=>$err->id,'level'=>$err->level,'message'=>$err->message,
                    'file'=>$err->file,'line'=>$err->line,'trace'=>$err->trace,
                    'url'=>$err->url,'method'=>$err->method,'ip'=>$err->ip,
                    'user_agent'=>$err->user_agent,'context'=>$err->context,
                    'is_resolved'=>$err->is_resolved,
                    'created_at'=>$err->created_at->format('d M Y H:i:s'),
                    'tenant_id'=>$err->tenant_id,'user_id'=>$err->user_id,
                ]) !!}</script>
                <td class="px-4 py-3">
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $err->levelColor() }}">{{ strtoupper($err->level) }}</span>
                </td>
                <td class="px-4 py-3 max-w-xs">
                    <p class="text-slate-200 text-xs font-medium truncate">{{ $err->message }}</p>
                    @if($err->file)<p class="text-slate-500 text-[10px] truncate mt-0.5">{{ basename($err->file) }}:{{ $err->line }}</p>@endif
                </td>
                <td class="px-4 py-3 hidden md:table-cell">
                    <p class="text-slate-400 text-xs truncate max-w-[180px]">{{ $err->method }} {{ $err->url }}</p>
                    @if($err->ip)<p class="text-slate-500 text-[10px]">{{ $err->ip }}</p>@endif
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    <p class="text-slate-400 text-xs">{{ $err->created_at->format('d M Y') }}</p>
                    <p class="text-slate-500 text-[10px]">{{ $err->created_at->format('H:i:s') }}</p>
                </td>
                <td class="px-4 py-3">
                    @if($err->is_resolved)
                    <span class="text-[11px] text-green-400 bg-green-500/15 px-2 py-0.5 rounded-full">Resolved</span>
                    @else
                    <span class="text-[11px] text-red-400 bg-red-500/15 px-2 py-0.5 rounded-full">Open</span>
                    @endif
                </td>
                <td class="px-4 py-3" @click.stop>
                    <div class="flex items-center justify-end gap-1">
                        <button @click.stop="show('error', rowData($el.closest('tr')))"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 transition" title="Detail">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                        @if(!$err->is_resolved)
                        <form method="POST" action="{{ route('super-admin.monitoring.resolve-error', $err) }}" @click.stop>
                            @csrf
                            <button class="p-1.5 rounded-lg text-slate-400 hover:text-green-400 hover:bg-green-500/10 transition" title="Resolve">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('super-admin.monitoring.delete-error', $err) }}" onsubmit="return confirm('Hapus error log ini?')" @click.stop>
                            @csrf @method('DELETE')
                            <button class="p-1.5 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition" title="Hapus">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500 text-sm">Tidak ada error log.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @if($errors->hasPages())
    <div class="px-6 py-4 border-t border-white/10">{{ $errors->links() }}</div>
    @endif
</div>
@endif

{{-- ═══ TAB: AI USAGE ═══ --}}
@if($tab === 'ai')
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
    @foreach([
        ['label'=>'Pesan Bulan Ini', 'value'=>number_format($aiStats['total_this_month']), 'color'=>'text-blue-400'],
        ['label'=>'Total Token',     'value'=>number_format($aiStats['total_tokens']),     'color'=>'text-purple-400'],
        ['label'=>'Tenant Aktif AI', 'value'=>$aiStats['active_tenants'],                  'color'=>'text-green-400'],
    ] as $s)
    <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-5 text-center">
        <p class="text-2xl font-bold {{ $s['color'] }}">{{ $s['value'] }}</p>
        <p class="text-xs text-slate-500 mt-1">{{ $s['label'] }}</p>
    </div>
    @endforeach
</div>
<div class="bg-[#1e293b] border border-white/10 rounded-2xl p-5 mb-5">
    <p class="text-sm font-semibold text-slate-200 mb-4">Tren Penggunaan AI (6 Bulan)</p>
    <div class="flex items-end gap-2 h-24">
        @php $maxMsg = $aiMonthly->max('total') ?: 1; @endphp
        @foreach($aiMonthly->reverse() as $m)
        @php $pct = round($m->total / $maxMsg * 100); @endphp
        <div class="flex-1 flex flex-col items-center gap-1">
            <span class="text-[10px] text-slate-400">{{ number_format($m->total) }}</span>
            <div class="w-full rounded-t-lg" style="height:{{ max(4, $pct * 0.8) }}px; background: linear-gradient(to top, #3b82f6, #6366f1);"></div>
            <span class="text-[10px] text-slate-500">{{ \Carbon\Carbon::createFromFormat('Y-m', $m->month)->format('M') }}</span>
        </div>
        @endforeach
    </div>
</div>
<div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
    <div class="px-5 py-4 border-b border-white/10">
        <p class="text-sm font-semibold text-slate-200">Penggunaan per Tenant — {{ now()->format('F Y') }}</p>
    </div>
    <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b border-white/10 bg-white/5">
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Tenant</th>
                <th class="px-5 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Pesan</th>
                <th class="px-5 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Token</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">Penggunaan</th>
                <th class="px-5 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Detail</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @php $maxUsage = $aiUsage->max('total_messages') ?: 1; @endphp
            @forelse($aiUsage as $u)
            @php
            $tenantMonthly = \App\Models\AiUsageLog::where('tenant_id', $u->tenant_id)
                ->selectRaw('month, SUM(message_count) as messages, SUM(token_count) as tokens')
                ->groupBy('month')->orderBy('month','desc')->limit(6)->get();
            $maxM = $tenantMonthly->max('messages') ?: 1;
            $breakdown = $tenantMonthly->map(fn($r) => [
                'month'    => $r->month,
                'messages' => (int)$r->messages,
                'tokens'   => (int)$r->tokens,
                'pct'      => round($r->messages / $maxM * 100),
            ])->values()->toArray();
            @endphp
            <tr class="hover:bg-white/5 transition cursor-pointer"
                @click="show('ai', rowData($el))">
                <script type="application/json" class="row-data">{!! json_encode([
                    'tenant'    => $u->tenant?->name ?? 'Tenant #'.$u->tenant_id,
                    'tenant_id' => $u->tenant_id,
                    'messages'  => (int)$u->total_messages,
                    'tokens'    => (int)$u->total_tokens,
                    'month'     => now()->format('F Y'),
                    'breakdown' => $breakdown,
                ]) !!}</script>
                <td class="px-5 py-3">
                    <p class="text-slate-200 font-medium text-xs">{{ $u->tenant?->name ?? 'Tenant #'.$u->tenant_id }}</p>
                </td>
                <td class="px-5 py-3 text-right text-slate-300 text-xs font-semibold">{{ number_format($u->total_messages) }}</td>
                <td class="px-5 py-3 text-right text-slate-400 text-xs">{{ number_format($u->total_tokens) }}</td>
                <td class="px-5 py-3 hidden md:table-cell">
                    @php $pct = round($u->total_messages / $maxUsage * 100); @endphp
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-1.5 bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" style="width:{{ $pct }}%"></div>
                        </div>
                        <span class="text-[10px] text-slate-500 w-8 text-right">{{ $pct }}%</span>
                    </div>
                </td>
                <td class="px-5 py-3 text-right" @click.stop>
                    <button @click.stop="show('ai', rowData($el.closest('tr')))"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 transition" title="Detail">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-6 py-10 text-center text-slate-500 text-sm">Belum ada penggunaan AI bulan ini.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endif

{{-- ═══ TAB: ACTIVITY LOG ═══ --}}
@if($tab === 'activity')
<div class="flex flex-wrap gap-3 mb-4">
    <form method="GET" action="" class="flex gap-2 flex-wrap flex-1">
        <input type="hidden" name="tab" value="activity">
        <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter aksi (login, created, ...)"
            class="flex-1 min-w-[180px] px-3 py-2 rounded-xl border border-white/10 bg-white/5 text-sm text-slate-300 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="tenant_id" class="px-3 py-2 rounded-xl border border-white/10 bg-[#1e293b] text-sm text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Tenant</option>
            @foreach($tenants as $t)
            <option value="{{ $t->id }}" @selected(request('tenant_id')==$t->id)>{{ $t->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-xl transition">Filter</button>
        @if(request()->hasAny(['action','tenant_id']))
        <a href="?tab=activity" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-slate-300 text-sm rounded-xl transition">Reset</a>
        @endif
    </form>
</div>
<div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b border-white/10 bg-white/5">
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Deskripsi</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">User</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">IP</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Waktu</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Detail</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @forelse($activities as $act)
            @php
            $actionColor = match(true) {
                str_starts_with($act->action, 'ai_') => 'text-purple-400 bg-purple-500/15',
                str_contains($act->action, 'delete') => 'text-red-400 bg-red-500/15',
                str_contains($act->action, 'create') => 'text-green-400 bg-green-500/15',
                str_contains($act->action, 'login')  => 'text-blue-400 bg-blue-500/15',
                default                              => 'text-slate-400 bg-white/10',
            };
            $actData = [
                'id'          => $act->id,
                'action'      => $act->action,
                'description' => $act->description,
                'model_type'  => $act->model_type ? class_basename($act->model_type) : null,
                'model_id'    => $act->model_id,
                'user_name'   => $act->user?->name ?? 'System',
                'user_email'  => $act->user?->email,
                'ip_address'  => $act->ip_address,
                'user_agent'  => $act->user_agent ?? null,
                'old_values'  => $act->old_values,
                'new_values'  => $act->new_values,
                'is_ai'       => $act->is_ai_action ?? false,
                'ai_tool'     => $act->ai_tool_name ?? null,
                'created_at'  => $act->created_at->format('d M Y H:i:s'),
                'tenant_id'   => $act->tenant_id,
            ];
            @endphp
            <tr class="hover:bg-white/5 transition cursor-pointer"
                @click="show('activity', rowData($el))">
                <script type="application/json" class="row-data">{!! json_encode($actData) !!}</script>
                <td class="px-4 py-3">
                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full {{ $actionColor }}">{{ $act->action }}</span>
                </td>
                <td class="px-4 py-3 max-w-xs">
                    <p class="text-slate-300 text-xs truncate">{{ $act->description }}</p>
                    @if($act->model_type)
                    <p class="text-slate-500 text-[10px]">{{ class_basename($act->model_type) }} #{{ $act->model_id }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 hidden md:table-cell">
                    <p class="text-slate-300 text-xs">{{ $act->user?->name ?? 'System' }}</p>
                    <p class="text-slate-500 text-[10px]">{{ $act->user?->email }}</p>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell">
                    <p class="text-slate-400 text-xs font-mono">{{ $act->ip_address ?? '-' }}</p>
                </td>
                <td class="px-4 py-3">
                    <p class="text-slate-400 text-xs">{{ $act->created_at->format('d M Y') }}</p>
                    <p class="text-slate-500 text-[10px]">{{ $act->created_at->format('H:i:s') }}</p>
                </td>
                <td class="px-4 py-3 text-right" @click.stop>
                    <button @click.stop="show('activity', rowData($el.closest('tr')))"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 transition" title="Detail">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500 text-sm">Belum ada activity log.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    @if($activities->hasPages())
    <div class="px-6 py-4 border-t border-white/10">{{ $activities->links() }}</div>
    @endif
</div>
@endif

{{-- ═══ TAB: ANOMALI ═══ --}}
@if($tab === 'anomaly')
<div class="grid grid-cols-3 gap-3 mb-5">
    @foreach([
        ['label'=>'Open',     'value'=>$anomalyStats['open'],     'color'=>'text-slate-300',  'bg'=>'bg-white/5'],
        ['label'=>'Critical', 'value'=>$anomalyStats['critical'], 'color'=>'text-red-400',    'bg'=>'bg-red-500/10'],
        ['label'=>'Warning',  'value'=>$anomalyStats['warning'],  'color'=>'text-amber-400',  'bg'=>'bg-amber-500/10'],
    ] as $s)
    <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-4 text-center {{ $s['bg'] }}">
        <p class="text-2xl font-bold {{ $s['color'] }}">{{ $s['value'] }}</p>
        <p class="text-xs text-slate-500 mt-1">{{ $s['label'] }}</p>
    </div>
    @endforeach
</div>
<div class="space-y-3">
    @forelse($anomalies as $a)
    @php
    $sevColor = match($a->severity) {
        'critical' => 'border-red-500/40 bg-red-500/5',
        'warning'  => 'border-amber-500/40 bg-amber-500/5',
        default    => 'border-blue-500/40 bg-blue-500/5',
    };
    $sevBadge = match($a->severity) {
        'critical' => 'text-red-400 bg-red-500/15',
        'warning'  => 'text-amber-400 bg-amber-500/15',
        default    => 'text-blue-400 bg-blue-500/15',
    };
    $anomalyData = [
        'id'          => $a->id,
        'severity'    => $a->severity,
        'type'        => $a->type,
        'title'       => $a->title,
        'description' => $a->description,
        'status'      => $a->status,
        'tenant'      => $a->tenant?->name,
        'tenant_id'   => $a->tenant_id,
        'data'        => $a->data ?? null,
        'created_at'  => $a->created_at->format('d M Y H:i:s'),
        'diff'        => $a->created_at->diffForHumans(),
    ];
    @endphp
    <div class="border rounded-2xl p-4 {{ $sevColor }} cursor-pointer hover:brightness-110 transition"
         @click="show('anomaly', rowData($el))">
        <script type="application/json" class="row-data">{!! json_encode($anomalyData) !!}</script>
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $sevBadge }}">{{ strtoupper($a->severity) }}</span>
                    <span class="text-[11px] text-slate-500 bg-white/5 px-2 py-0.5 rounded-full">{{ $a->type }}</span>
                    <span class="text-[11px] text-slate-500">{{ $a->tenant?->name }}</span>
                </div>
                <p class="text-sm font-semibold text-slate-200">{{ $a->title }}</p>
                <p class="text-xs text-slate-400 mt-0.5 line-clamp-2">{{ $a->description }}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <p class="text-[10px] text-slate-500">{{ $a->created_at->diffForHumans() }}</p>
                <button @click.stop="show('anomaly', rowData($el.closest('[data-anomaly]') || $el.closest('.border.rounded-2xl')))"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 transition" title="Detail">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-12 text-center">
        <p class="text-slate-500 text-sm">Tidak ada anomali yang open.</p>
    </div>
    @endforelse
</div>
@endif

{{-- ═══ TAB: SYSTEM HEALTH ═══ --}}
@if($tab === 'health')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-5">

    {{-- Database --}}
    <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-5 cursor-pointer hover:border-white/20 transition"
         @click="show('health', rowData($el))">
        <script type="application/json" class="row-data">{!! json_encode(['component'=>'Database','status'=>$health['db_ok']?'Online':'Error','ok'=>$health['db_ok'],'created_at'=>now()->format('d M Y H:i:s'),'details'=>[['label'=>'Status','value'=>$health['db_ok']?'Online':'Error'],['label'=>'Latency','value'=>$health['db_latency_ms'].' ms'],['label'=>'Driver','value'=>config('database.default')],['label'=>'Database','value'=>config('database.connections.'.config('database.default').'.database')]]]) !!}</script>
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-semibold text-slate-200">Database</p>
            <span class="text-[11px] px-2 py-0.5 rounded-full {{ $health['db_ok'] ? 'text-green-400 bg-green-500/15' : 'text-red-400 bg-red-500/15' }}">
                {{ $health['db_ok'] ? 'Online' : 'Error' }}
            </span>
        </div>
        <p class="text-2xl font-bold text-slate-100">{{ $health['db_latency_ms'] }} <span class="text-sm font-normal text-slate-400">ms</span></p>
        <p class="text-xs text-slate-500 mt-1">Latency koneksi</p>
    </div>

    {{-- Disk --}}
    <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-5 cursor-pointer hover:border-white/20 transition"
         @click="show('health', rowData($el))">
        <script type="application/json" class="row-data">{!! json_encode(['component'=>'Disk','status'=>$health['disk_used_pct']>85?'Kritis':'Normal','ok'=>$health['disk_used_pct']<=85,'created_at'=>now()->format('d M Y H:i:s'),'details'=>[['label'=>'Terpakai','value'=>$health['disk_used_pct'].'%'],['label'=>'Bebas','value'=>$health['disk_free_gb'].' GB'],['label'=>'Total','value'=>$health['disk_total_gb'].' GB'],['label'=>'Digunakan','value'=>round($health['disk_total_gb']-$health['disk_free_gb'],1).' GB']]]) !!}</script>
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-semibold text-slate-200">Disk</p>
            <span class="text-[11px] px-2 py-0.5 rounded-full {{ $health['disk_used_pct'] > 85 ? 'text-red-400 bg-red-500/15' : 'text-green-400 bg-green-500/15' }}">
                {{ $health['disk_used_pct'] }}% terpakai
            </span>
        </div>
        <div class="h-2 bg-white/10 rounded-full overflow-hidden mb-2">
            <div class="h-full rounded-full {{ $health['disk_used_pct'] > 85 ? 'bg-red-500' : 'bg-blue-500' }}" style="width:{{ $health['disk_used_pct'] }}%"></div>
        </div>
        <p class="text-xs text-slate-500">{{ $health['disk_free_gb'] }} GB bebas dari {{ $health['disk_total_gb'] }} GB</p>
    </div>

    {{-- Log File --}}
    <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-5 cursor-pointer hover:border-white/20 transition"
         @click="show('health', rowData($el))">
        <script type="application/json" class="row-data">{!! json_encode(['component'=>'Log File','status'=>$health['log_size_mb']>100?'Besar':'Normal','ok'=>$health['log_size_mb']<=100,'created_at'=>now()->format('d M Y H:i:s'),'details'=>[['label'=>'Ukuran','value'=>$health['log_size_mb'].' MB'],['label'=>'Path','value'=>'storage/logs/laravel.log'],['label'=>'Status','value'=>$health['log_size_mb']>100?'Perlu dibersihkan':'Normal']]]) !!}</script>
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-semibold text-slate-200">Log File</p>
            <span class="text-[11px] px-2 py-0.5 rounded-full {{ $health['log_size_mb'] > 100 ? 'text-amber-400 bg-amber-500/15' : 'text-green-400 bg-green-500/15' }}">
                {{ $health['log_size_mb'] > 100 ? 'Besar' : 'Normal' }}
            </span>
        </div>
        <p class="text-2xl font-bold text-slate-100">{{ $health['log_size_mb'] }} <span class="text-sm font-normal text-slate-400">MB</span></p>
        <p class="text-xs text-slate-500 mt-1">storage/logs/laravel.log</p>
    </div>

    {{-- Tenants --}}
    <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-5 cursor-pointer hover:border-white/20 transition"
         @click="show('health', rowData($el))">
        <script type="application/json" class="row-data">{!! json_encode(['component'=>'Tenant','status'=>'Info','ok'=>true,'created_at'=>now()->format('d M Y H:i:s'),'details'=>[['label'=>'Total Tenant','value'=>$health['total_tenants']],['label'=>'Tenant Aktif','value'=>$health['active_tenants']],['label'=>'Tenant Nonaktif','value'=>$health['total_tenants']-$health['active_tenants']],['label'=>'Total User','value'=>$health['total_users']]]]) !!}</script>
        <p class="text-sm font-semibold text-slate-200 mb-3">Tenant</p>
        <div class="space-y-2">
            <div class="flex justify-between text-xs"><span class="text-slate-400">Total</span><span class="text-slate-200 font-semibold">{{ $health['total_tenants'] }}</span></div>
            <div class="flex justify-between text-xs"><span class="text-slate-400">Aktif</span><span class="text-green-400 font-semibold">{{ $health['active_tenants'] }}</span></div>
            <div class="flex justify-between text-xs"><span class="text-slate-400">Total User</span><span class="text-slate-200 font-semibold">{{ $health['total_users'] }}</span></div>
        </div>
    </div>

    {{-- Queue --}}
    <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-5 cursor-pointer hover:border-white/20 transition"
         @click="show('health', rowData($el))">
        <script type="application/json" class="row-data">{!! json_encode(['component'=>'Queue','status'=>$health['queue_failed']>0?$health['queue_failed'].' Failed Jobs':'OK','ok'=>$health['queue_failed']===0,'created_at'=>now()->format('d M Y H:i:s'),'details'=>[['label'=>'Failed Jobs','value'=>$health['queue_failed']],['label'=>'Status','value'=>$health['queue_failed']>0?'Ada job gagal, perlu diperiksa':'Semua job berjalan normal']]]) !!}</script>
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-semibold text-slate-200">Queue</p>
            <span class="text-[11px] px-2 py-0.5 rounded-full {{ $health['queue_failed'] > 0 ? 'text-red-400 bg-red-500/15' : 'text-green-400 bg-green-500/15' }}">
                {{ $health['queue_failed'] > 0 ? $health['queue_failed'].' failed' : 'OK' }}
            </span>
        </div>
        <p class="text-2xl font-bold {{ $health['queue_failed'] > 0 ? 'text-red-400' : 'text-slate-100' }}">{{ $health['queue_failed'] }}</p>
        <p class="text-xs text-slate-500 mt-1">Failed jobs</p>
    </div>

    {{-- Server --}}
    <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-5 cursor-pointer hover:border-white/20 transition"
         @click="show('health', rowData($el))">
        <script type="application/json" class="row-data">{!! json_encode(['component'=>'Server','status'=>'Info','ok'=>true,'created_at'=>now()->format('d M Y H:i:s'),'details'=>[['label'=>'PHP','value'=>$health['php_version']],['label'=>'Laravel','value'=>$health['laravel_version']],['label'=>'Uptime','value'=>$health['uptime']],['label'=>'Error Hari Ini','value'=>$health['errors_today']],['label'=>'Environment','value'=>app()->environment()],['label'=>'Debug Mode','value'=>config('app.debug')?'ON':'OFF']]]) !!}</script>
        <p class="text-sm font-semibold text-slate-200 mb-3">Server</p>
        <div class="space-y-2">
            <div class="flex justify-between text-xs"><span class="text-slate-400">PHP</span><span class="text-slate-200 font-mono">{{ $health['php_version'] }}</span></div>
            <div class="flex justify-between text-xs"><span class="text-slate-400">Laravel</span><span class="text-slate-200 font-mono">{{ $health['laravel_version'] }}</span></div>
            <div class="flex justify-between text-xs"><span class="text-slate-400">Uptime</span><span class="text-slate-200 font-mono">{{ $health['uptime'] }}</span></div>
            <div class="flex justify-between text-xs"><span class="text-slate-400">Error Hari Ini</span><span class="{{ $health['errors_today'] > 0 ? 'text-red-400' : 'text-green-400' }} font-semibold">{{ $health['errors_today'] }}</span></div>
        </div>
    </div>

</div>
<div class="flex justify-end">
    <a href="?tab=health" class="flex items-center gap-2 text-sm text-slate-400 hover:text-white border border-white/10 hover:border-white/20 px-4 py-2 rounded-xl transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Refresh
    </a>
</div>
@endif

</div>{{-- end x-data detailModal --}}

@push('scripts')
<script>
function rowData(el) {
    const s = el.querySelector(':scope > script.row-data, script.row-data');
    return s ? JSON.parse(s.textContent) : {};
}
function detailModal() {
    return {
        open: false, type: '', d: {}, copied: false,
        show(type, data) { this.type = type; this.d = data; this.open = true; this.copied = false; },
        close() { this.open = false; },
        copy(text) {
            navigator.clipboard.writeText(text||'').then(() => {
                this.copied = true; setTimeout(() => this.copied = false, 2000);
            });
        },
        title() {
            if (this.type === 'error')    return this.d.message || '';
            if (this.type === 'ai')       return (this.d.tenant || '') + ' — AI Usage';
            if (this.type === 'activity') return this.d.action || '';
            if (this.type === 'anomaly')  return this.d.title || '';
            if (this.type === 'health')   return this.d.component || 'System Health';
            return '';
        },
        badgeLabel() {
            if (this.type === 'error')    return (this.d.level||'').toUpperCase();
            if (this.type === 'ai')       return 'AI';
            if (this.type === 'activity') return this.d.is_ai ? 'AI ACTION' : 'ACTIVITY';
            if (this.type === 'anomaly')  return (this.d.severity||'').toUpperCase();
            if (this.type === 'health')   return this.d.ok ? 'OK' : 'ALERT';
            return '';
        },
        badgeClass() {
            if (this.type === 'error') {
                const m = { critical:'text-red-400 bg-red-500/20 border-red-500/30', error:'text-orange-400 bg-orange-500/20 border-orange-500/30', warning:'text-amber-400 bg-amber-500/20 border-amber-500/30', info:'text-blue-400 bg-blue-500/20 border-blue-500/30' };
                return m[this.d.level] || m.info;
            }
            if (this.type === 'anomaly') {
                const m = { critical:'text-red-400 bg-red-500/20 border-red-500/30', warning:'text-amber-400 bg-amber-500/20 border-amber-500/30' };
                return m[this.d.severity] || 'text-blue-400 bg-blue-500/20 border-blue-500/30';
            }
            if (this.type === 'health') return this.d.ok ? 'text-green-400 bg-green-500/20 border-green-500/30' : 'text-red-400 bg-red-500/20 border-red-500/30';
            if (this.type === 'activity') return this.d.is_ai ? 'text-purple-400 bg-purple-500/20 border-purple-500/30' : 'text-blue-400 bg-blue-500/20 border-blue-500/30';
            return 'text-blue-400 bg-blue-500/20 border-blue-500/30';
        },
        metaFields() {
            if (this.type === 'error') return [
                { label:'Level',    value:(this.d.level||'').toUpperCase() },
                { label:'Waktu',    value:this.d.created_at },
                { label:'User ID',  value:this.d.user_id },
                { label:'Tenant',   value:this.d.tenant_id },
                { label:'IP',       value:this.d.ip },
                { label:'Method',   value:this.d.method },
            ];
            if (this.type === 'ai') return [
                { label:'Tenant',   value:this.d.tenant },
                { label:'Tenant ID',value:this.d.tenant_id },
                { label:'Bulan',    value:this.d.month },
                { label:'Pesan',    value:this.d.messages ? this.d.messages.toLocaleString() : '0', color:'text-blue-400' },
                { label:'Token',    value:this.d.tokens ? this.d.tokens.toLocaleString() : '0',   color:'text-purple-400' },
            ];
            if (this.type === 'activity') return [
                { label:'Aksi',     value:this.d.action },
                { label:'User',     value:this.d.user_name },
                { label:'Email',    value:this.d.user_email },
                { label:'Tenant',   value:this.d.tenant_id },
                { label:'Model',    value:this.d.model_type ? this.d.model_type+' #'+this.d.model_id : null },
                { label:'Waktu',    value:this.d.created_at },
            ];
            if (this.type === 'anomaly') return [
                { label:'Severity', value:(this.d.severity||'').toUpperCase() },
                { label:'Tipe',     value:this.d.type },
                { label:'Status',   value:this.d.status },
                { label:'Tenant',   value:this.d.tenant },
                { label:'Waktu',    value:this.d.created_at },
                { label:'Sejak',    value:this.d.diff },
            ];
            if (this.type === 'health') {
                return (this.d.details || []).map(x => ({ label: x.label, value: String(x.value) }));
            }
            return [];
        },
        footerText() {
            if (this.type === 'error')    return 'ID #' + (this.d.id||'') + ' · ' + (this.d.created_at||'');
            if (this.type === 'ai')       return 'Tenant #' + (this.d.tenant_id||'') + ' · ' + (this.d.month||'');
            if (this.type === 'activity') return 'ID #' + (this.d.id||'') + ' · ' + (this.d.created_at||'');
            if (this.type === 'anomaly')  return 'ID #' + (this.d.id||'') + ' · ' + (this.d.created_at||'');
            if (this.type === 'health')   return 'Diambil: ' + (this.d.created_at||'');
            return '';
        },
    };
}
</script>
@endpush

</x-app-layout>
