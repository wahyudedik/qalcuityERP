@extends('layouts.app')
@section('title', 'Pengaturan API & Webhook')

@section('content')
<div class="p-6 space-y-8 max-w-4xl">

    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">API & Webhook</h1>
        <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Kelola token REST API dan webhook outbound untuk integrasi pihak ketiga.</p>
    </div>

    @if(session('success'))
    <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-sm text-emerald-700 dark:text-emerald-300">
        {{ session('success') }}
    </div>
    @endif

    @if(session('new_token'))
    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-700">
        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300 mb-2">Token baru — salin sekarang, tidak akan ditampilkan lagi:</p>
        <div class="flex items-center gap-2">
            <code class="flex-1 text-xs bg-white dark:bg-black/30 border border-amber-200 dark:border-amber-700 rounded-lg px-3 py-2 font-mono text-amber-900 dark:text-amber-200 break-all">{{ session('new_token') }}</code>
            <button onclick="navigator.clipboard.writeText('{{ session('new_token') }}')"
                    class="px-3 py-2 text-xs bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition shrink-0">Salin</button>
        </div>
    </div>
    @endif

    {{-- API Base URL Info --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
        <p class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-1">Base URL REST API</p>
        <code class="text-xs text-blue-700 dark:text-blue-400 font-mono">{{ url('/api/v1') }}</code>
        <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">Autentikasi: <code class="bg-blue-100 dark:bg-blue-900/40 px-1 rounded">Authorization: Bearer &lt;token&gt;</code> atau header <code class="bg-blue-100 dark:bg-blue-900/40 px-1 rounded">X-API-Token</code></p>
        <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs text-blue-700 dark:text-blue-400">
            @foreach(['GET /stats','GET /products','GET /orders','POST /orders','GET /invoices','GET /customers','POST /customers'] as $ep)
            <code class="bg-blue-100 dark:bg-blue-900/40 px-2 py-1 rounded">{{ $ep }}</code>
            @endforeach
        </div>
    </div>

    {{-- ── API Tokens ── --}}
    <div class="bg-white dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-slate-300">Token API</h2>
            <button onclick="document.getElementById('addTokenModal').classList.remove('hidden')"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Token
            </button>
        </div>
        @if($tokens->isEmpty())
        <div class="p-8 text-center text-sm text-gray-400 dark:text-slate-500">Belum ada token API.</div>
        @else
        <div class="divide-y divide-gray-100 dark:divide-white/5">
            @foreach($tokens as $token)
            <div class="px-5 py-4 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $token->name }}</p>
                        @if(!$token->is_active)
                        <span class="text-xs bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 px-2 py-0.5 rounded-full">Dicabut</span>
                        @elseif($token->expires_at && $token->expires_at->isPast())
                        <span class="text-xs bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-slate-400 px-2 py-0.5 rounded-full">Kadaluarsa</span>
                        @else
                        <span class="text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded-full">Aktif</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">
                        Izin: {{ implode(', ', $token->abilities ?? []) }}
                        @if($token->expires_at) · Exp: {{ $token->expires_at->format('d M Y') }} @endif
                        @if($token->last_used_at) · Terakhir: {{ $token->last_used_at->diffForHumans() }} @endif
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    @if($token->is_active)
                    <form method="POST" action="{{ route('api-settings.tokens.revoke', $token) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="text-xs text-amber-500 hover:text-amber-600 transition">Cabut</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('api-settings.tokens.destroy', $token) }}" onsubmit="return confirm('Hapus token?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-500 transition">Hapus</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ── Webhooks ── --}}
    <div class="bg-white dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-slate-300">Webhook Outbound</h2>
            <button onclick="document.getElementById('addWebhookModal').classList.remove('hidden')"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Webhook
            </button>
        </div>
        @if($webhooks->isEmpty())
        <div class="p-8 text-center text-sm text-gray-400 dark:text-slate-500">Belum ada webhook subscription.</div>
        @else
        <div class="divide-y divide-gray-100 dark:divide-white/5">
            @foreach($webhooks as $wh)
            <div class="px-5 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $wh->name }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $wh->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-slate-400' }}">
                                {{ $wh->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 truncate max-w-xs">{{ $wh->url }}</p>
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Events: {{ implode(', ', $wh->events ?? []) }}</p>
                        @if($wh->last_triggered_at)
                        <p class="text-xs text-gray-400 dark:text-slate-500">Terakhir: {{ $wh->last_triggered_at->diffForHumans() }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <form method="POST" action="{{ route('api-settings.webhooks.test', $wh) }}">
                            @csrf
                            <button type="submit" class="text-xs text-blue-400 hover:text-blue-500 transition">Test</button>
                        </form>
                        <form method="POST" action="{{ route('api-settings.webhooks.toggle', $wh) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs text-amber-500 hover:text-amber-600 transition">
                                {{ $wh->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('api-settings.webhooks.destroy', $wh) }}" onsubmit="return confirm('Hapus webhook?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-500 transition">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- Add Token Modal --}}
<div id="addTokenModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Buat Token API</h3>
            <button onclick="document.getElementById('addTokenModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('api-settings.tokens.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Token</label>
                <input type="text" name="name" required placeholder="Contoh: Integrasi Tokopedia"
                       class="w-full px-3 py-2 rounded-lg text-sm bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Izin</label>
                <div class="space-y-2">
                    @foreach(['read' => 'Read — baca data', 'write' => 'Write — buat & update data', 'delete' => 'Delete — hapus data', '*' => 'Full Access'] as $val => $label)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="abilities[]" value="{{ $val }}" {{ $val === 'read' ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-white/20 text-blue-600">
                        <span class="text-sm text-gray-700 dark:text-slate-300">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kadaluarsa (opsional)</label>
                <input type="date" name="expires_at"
                       class="w-full px-3 py-2 rounded-lg text-sm bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('addTokenModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 transition">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium transition">Buat Token</button>
            </div>
        </form>
    </div>
</div>

{{-- Add Webhook Modal --}}
<div id="addWebhookModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Tambah Webhook</h3>
            <button onclick="document.getElementById('addWebhookModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('api-settings.webhooks.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama</label>
                <input type="text" name="name" required placeholder="Contoh: Notifikasi Order ke Slack"
                       class="w-full px-3 py-2 rounded-lg text-sm bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">URL Endpoint</label>
                <input type="url" name="url" required placeholder="https://hooks.example.com/..."
                       class="w-full px-3 py-2 rounded-lg text-sm bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Events</label>
                <div class="space-y-1.5">
                    @foreach($availableEvents as $ev)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="events[]" value="{{ $ev }}"
                               class="rounded border-gray-300 dark:border-white/20 text-blue-600">
                        <span class="text-sm text-gray-700 dark:text-slate-300 font-mono text-xs">{{ $ev }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('addWebhookModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 transition">Batal</button>
                <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium transition">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
