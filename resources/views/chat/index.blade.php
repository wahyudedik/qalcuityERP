@extends('layouts.app')

@section('title', 'AI Chat')

@push('head')
<style>
    body { overflow: hidden !important; }
    body.chat-page > div > div > main { overflow: hidden !important; }

    /* ── AI message text & markdown — dark mode aware ── */
    .prose-chat { color: #1e293b; }
    .dark .prose-chat { color: #e2e8f0; }

    .prose-chat p, .prose-chat li { color: inherit; }
    .prose-chat h1,.prose-chat h2,.prose-chat h3 { color: #0f172a; }
    .dark .prose-chat h1,.dark .prose-chat h2,.dark .prose-chat h3 { color: #f1f5f9; }
    .prose-chat strong { color: #0f172a; }
    .dark .prose-chat strong { color: #f1f5f9; }
    .prose-chat em { color: #475569; }
    .dark .prose-chat em { color: #94a3b8; }
    .prose-chat a { color: #2563eb; }
    .dark .prose-chat a { color: #60a5fa; }
    .prose-chat hr { border-color: #e2e8f0; }
    .dark .prose-chat hr { border-color: rgba(255,255,255,0.1); }
    .prose-chat blockquote { border-color: #60a5fa; color: #475569; background: rgba(59,130,246,0.06); }
    .dark .prose-chat blockquote { color: #94a3b8; background: rgba(59,130,246,0.1); }

    /* Tables inside AI messages */
    .prose-chat table th { background: #f8fafc; color: #64748b; }
    .dark .prose-chat table th { background: rgba(255,255,255,0.05); color: #94a3b8; }
    .prose-chat table td { color: #334155; }
    .dark .prose-chat table td { color: #cbd5e1; }
    .prose-chat table tbody { background: #fff; }
    .dark .prose-chat table tbody { background: rgba(255,255,255,0.03); }
    .prose-chat table { border-color: #e2e8f0; }
    .dark .prose-chat table { border-color: rgba(255,255,255,0.08); }
    .prose-chat table tr:nth-child(even) { background: rgba(248,250,252,0.8); }
    .dark .prose-chat table tr:nth-child(even) { background: rgba(255,255,255,0.03); }
    .prose-chat .divide-y > * { border-color: #f1f5f9; }
    .dark .prose-chat .divide-y > * { border-color: rgba(255,255,255,0.06); }

    /* Special blocks (chart, grid, kpi, invoice, letter) */
    .dark .chat-block-white { background: #1e293b !important; border-color: rgba(255,255,255,0.08) !important; }
    .dark .chat-block-header { background: rgba(255,255,255,0.04) !important; border-color: rgba(255,255,255,0.08) !important; }
    .dark .chat-block-title { color: #e2e8f0 !important; }
    .dark .chat-block-muted { color: #94a3b8 !important; }
    .dark .chat-block-body { color: #cbd5e1 !important; }

    /* KPI cards */
    .dark .kpi-card { background: rgba(255,255,255,0.05) !important; border-color: rgba(255,255,255,0.08) !important; }
    .dark .kpi-card .kpi-label { color: #94a3b8 !important; }
    .dark .kpi-card .kpi-value { color: #f1f5f9 !important; }
    .dark .kpi-card .kpi-sub { color: #64748b !important; }

    /* Action badges */
    .dark .action-badge-write { background: rgba(34,197,94,0.15) !important; color: #86efac !important; }
    .dark .action-badge-read { background: rgba(255,255,255,0.06) !important; color: #94a3b8 !important; }

    /* Error/warning blocks */
    .dark .chat-error-block { background: rgba(239,68,68,0.1) !important; border-color: rgba(239,68,68,0.2) !important; color: #fca5a5 !important; }
    .dark .chat-warn-block { background: rgba(245,158,11,0.1) !important; border-color: rgba(245,158,11,0.2) !important; color: #fcd34d !important; }
</style>
@endpush

@section('content')
{{-- Negative margins cancel the p-4/sm:p-6 from layouts/app.blade.php main wrapper --}}
<div class="flex -m-4 sm:-m-6" style="height: calc(100vh - 4rem);">

    {{-- ── CHAT SIDEBAR (conversation list) ───────────────── --}}
    <div id="chat-sidebar-overlay" class="fixed inset-0 z-[45] bg-black/40 hidden md:hidden" onclick="toggleChatSidebar()"></div>

    <aside id="chat-sidebar"
        class="fixed top-16 right-0 bottom-0 z-[46] w-72
               md:static md:w-56 md:z-auto md:top-auto
               bg-white dark:bg-[#1e293b] border-l md:border-l-0 md:border-r border-gray-100 dark:border-white/10
               flex flex-col shrink-0
               translate-x-full md:translate-x-0
               transition-transform duration-300 ease-in-out
               shadow-2xl md:shadow-none">

        <div class="px-4 pt-4 pb-3 border-b border-gray-100 dark:border-white/10 shrink-0 space-y-2.5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Percakapan</p>
                <button onclick="toggleChatSidebar()" class="md:hidden w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-white/10 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <button id="btn-new-chat" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl border border-dashed border-gray-200 dark:border-white/10 text-sm text-gray-500 dark:text-slate-400 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Percakapan Baru
            </button>
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input id="session-search" type="text" placeholder="Cari percakapan..."
                    class="w-full pl-8 pr-3 py-1.5 text-xs bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-100 transition placeholder-gray-400 dark:text-slate-300">
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-2 py-2 space-y-0.5" id="session-list">
            @forelse($sessions as $s)
            <div class="session-item group flex items-center rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition cursor-pointer"
                 data-session="{{ $s->id }}" data-title="{{ $s->title ?? 'Percakapan baru' }}">
                <button class="flex-1 text-left px-3 py-2.5 text-sm text-gray-600 dark:text-slate-300 truncate session-btn leading-snug">{{ $s->title ?? 'Percakapan baru' }}</button>
                <div class="hidden group-hover:flex items-center gap-0.5 pr-1.5 shrink-0">
                    <button class="session-rename w-6 h-6 flex items-center justify-center rounded text-gray-300 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition" data-session="{{ $s->id }}" title="Ganti nama">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button class="session-delete w-6 h-6 flex items-center justify-center rounded text-gray-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition" data-session="{{ $s->id }}" title="Hapus">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            @empty
            <p class="text-xs text-gray-400 px-3 py-4 text-center" id="empty-sessions-msg">Belum ada percakapan</p>
            @endforelse
        </div>

        <div class="px-3 py-3 border-t border-gray-100 dark:border-white/10 shrink-0">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-2 py-2 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition group">
                <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover shrink-0 ring-2 ring-gray-100 dark:ring-white/10">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-gray-700 dark:text-slate-200 truncate group-hover:text-blue-600 transition">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }}</p>
                </div>
                <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-blue-400 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </aside>

    {{-- ── MAIN CHAT AREA ───────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#f5f6fa] dark:bg-[#0f172a]">

        {{-- Top bar --}}
        <div class="h-14 bg-white dark:bg-[#1e293b] border-b border-gray-100 dark:border-white/10 px-4 flex items-center justify-between shrink-0 gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 dark:text-white truncate leading-tight" id="chat-title">Percakapan Baru</p>
                    <p class="text-xs text-gray-400 leading-tight" id="model-label">Qalcuity AI · Siap membantu</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @php
                    $tenant = auth()->user()->tenant;
                    $maxAi  = $tenant?->maxAiMessages() ?? 20;
                    $usedAi = $tenant ? \App\Models\AiUsageLog::tenantMonthlyCount($tenant->id) : 0;
                    $quotaPercent = $maxAi > 0 ? min(100, round($usedAi / $maxAi * 100)) : 0;
                @endphp
                @if($tenant && $maxAi !== -1)
                <div class="hidden sm:flex items-center gap-2">
                    <span class="text-xs text-gray-400 tabular-nums whitespace-nowrap">{{ $usedAi }}/{{ $maxAi }} pesan</span>
                    <div class="w-14 h-1.5 bg-gray-100 dark:bg-white/10 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all {{ $quotaPercent >= 90 ? 'bg-red-400' : ($quotaPercent >= 70 ? 'bg-amber-400' : 'bg-blue-400') }}" style="width:{{ $quotaPercent }}%"></div>
                    </div>
                </div>
                @endif
                <div id="typing-indicator" class="hidden items-center gap-1.5 text-xs text-blue-500 font-medium">
                    <span class="flex gap-0.5">
                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce" style="animation-delay:0s"></span>
                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce" style="animation-delay:.15s"></span>
                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce" style="animation-delay:.3s"></span>
                    </span>
                    <span class="hidden sm:inline">Memproses...</span>
                </div>
                {{-- Mobile: buka chat sidebar --}}
                <button onclick="toggleChatSidebar()" class="md:hidden w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-white/10 transition" title="Daftar percakapan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div id="chat-messages" class="flex-1 overflow-y-auto scrollbar-accent px-4 py-6">
            <div id="empty-state" class="flex flex-col items-center justify-center h-full text-center py-12">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center mb-4 shadow-lg shadow-blue-200/60">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h3 class="text-base font-semibold text-gray-700 dark:text-slate-200 mb-1">Halo, {{ explode(' ', auth()->user()->name)[0] }}!</h3>
                <p class="text-sm text-gray-400 max-w-xs leading-relaxed mb-5">Tanyakan apa saja tentang bisnis Anda — stok, penjualan, keuangan, atau SDM.</p>
                <div class="grid grid-cols-2 gap-2 w-full max-w-sm">
                    @foreach([
                        ['icon'=>'📊','text'=>'Grafik omzet 7 hari'],
                        ['icon'=>'💰','text'=>'KPI bisnis hari ini'],
                        ['icon'=>'📋','text'=>'Daftar semua produk'],
                        ['icon'=>'📈','text'=>'Laba rugi bulan ini'],
                        ['icon'=>'🏭','text'=>'Stok semua gudang'],
                        ['icon'=>'👥','text'=>'Absensi karyawan hari ini'],
                    ] as $hint)
                    <button class="hint-btn flex items-center gap-2 text-left text-xs bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2.5 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition shadow-sm text-gray-600 dark:text-slate-300 font-medium">
                        <span class="text-base shrink-0">{{ $hint['icon'] }}</span>
                        <span>{{ $hint['text'] }}</span>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="bg-gradient-to-t from-white dark:from-[#1e293b] via-white dark:via-[#1e293b] to-transparent px-4 pt-2 pb-4 shrink-0">
            <div class="max-w-3xl mx-auto">
                <div id="file-preview-strip" class="hidden flex gap-2 mb-2 flex-wrap px-1"></div>
                <div class="relative bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-white/10 rounded-2xl shadow-lg shadow-gray-100/80 dark:shadow-none focus-within:border-blue-400 focus-within:shadow-blue-100/60 focus-within:shadow-xl transition-all duration-200">
                    <textarea id="chat-input" rows="1"
                        placeholder="Tanya apa saja..."
                        class="w-full resize-none bg-transparent text-sm text-gray-800 dark:text-slate-200 placeholder-gray-400 focus:outline-none leading-relaxed px-4 pt-3.5 pb-12 max-h-40 overflow-y-auto"></textarea>
                    <div class="absolute bottom-0 left-0 right-0 flex items-center justify-between px-3 pb-2.5">
                        <div class="flex items-center gap-1.5">
                            <label for="file-input" title="Lampirkan file"
                                class="flex items-center gap-1.5 text-xs text-gray-400 hover:text-blue-500 cursor-pointer transition px-2 py-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-500/10 group">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <span class="hidden sm:inline group-hover:text-blue-500">Lampirkan</span>
                            </label>
                            <input type="file" id="file-input" class="hidden" multiple accept="image/*,.pdf,.txt,.csv,.xlsx,.docx">
                            <span class="hidden sm:inline text-xs text-gray-300">·</span>
                            <span class="hidden sm:inline text-xs text-gray-300">Shift+Enter baris baru</span>
                        </div>
                        <button type="button" id="btn-send"
                            class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-medium px-3.5 py-2 rounded-xl transition-all duration-150 shadow-sm shadow-blue-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <span>Kirim</span>
                        </button>
                    </div>
                </div>
                <p class="text-[11px] text-gray-300 mt-2 text-center tracking-wide">
                    Qalcuity AI dapat membuat kesalahan. Verifikasi informasi penting.
                </p>
            </div>
        </div>
    </div>{{-- end main chat area --}}
</div>
@endsection

@push('scripts')
<script>
document.body.classList.add('chat-page');

function toggleChatSidebar() {
    const cs = document.getElementById('chat-sidebar');
    const co = document.getElementById('chat-sidebar-overlay');
    cs.classList.toggle('translate-x-full');
    co.classList.toggle('hidden');
}
</script>
@vite(['resources/js/chat.js'])
@endpush
