<x-app-layout>
    <x-slot name="header">Dashboard Marketplace</x-slot>

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">Dashboard Marketplace</h1>
                <p class="text-sm text-gray-400 mt-0.5">Ringkasan performa semua channel marketplace Anda</p>
            </div>
            <a href="{{ route('ecommerce.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 text-gray-300 rounded-xl text-sm hover:bg-white/10 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Daftar Order
            </a>
        </div>

        {{-- Stats Row --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Channel Aktif</p>
                <p class="text-2xl font-bold text-white">{{ $channels->where('is_active', true)->count() }}</p>
                <p class="text-xs text-gray-500 mt-1">dari {{ $channels->count() }} total</p>
            </div>
            <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Order Hari Ini</p>
                <p class="text-2xl font-bold text-indigo-400">{{ $todayOrders }}</p>
                <p class="text-xs text-gray-500 mt-1">order baru</p>
            </div>
            <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Order Minggu Ini</p>
                <p class="text-2xl font-bold text-emerald-400">{{ $weekOrders }}</p>
                <p class="text-xs text-gray-500 mt-1">7 hari terakhir</p>
            </div>
            <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Total Revenue</p>
                <p class="text-xl font-bold text-white leading-tight">Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-500 mt-1">semua channel</p>
            </div>
        </div>

        {{-- Channel Cards --}}
        <div>
            <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Channel Marketplace</h2>
            @if ($channels->isEmpty())
                <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-10 text-center text-gray-500">
                    Belum ada channel terdaftar.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($channels as $channel)
                        <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-5 flex flex-col gap-4">

                            {{-- Platform Header --}}
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-11 h-11 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0
                            {{ $channel->platform === 'shopee' ? 'bg-orange-500' : ($channel->platform === 'tokopedia' ? 'bg-green-600' : 'bg-red-600') }}">
                                    {{ strtoupper(substr($channel->platform, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-white text-sm truncate">{{ $channel->shop_name }}</p>
                                    <p class="text-xs text-gray-400 capitalize">{{ $channel->platform }}</p>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs {{ $channel->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-white/10 text-gray-400' }}">
                                        {{ $channel->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                    <span class="text-xs text-gray-500 font-mono">{{ $channel->orders_count }}
                                        order</span>
                                </div>
                            </div>

                            {{-- Sync Status --}}
                            <div class="space-y-2 border-t border-white/5 pt-3">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-400">Sync Order</span>
                                    <span class="text-gray-300">
                                        {{ $channel->last_sync_at ? $channel->last_sync_at->diffForHumans() : 'Belum pernah' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-400">Sync Stok</span>
                                    <div class="flex items-center gap-2">
                                        @if ($channel->stock_sync_enabled)
                                            <span
                                                class="px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-400">Aktif</span>
                                            <span
                                                class="text-gray-400">{{ $channel->last_stock_sync_at ? $channel->last_stock_sync_at->diffForHumans() : '—' }}</span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded bg-white/5 text-gray-500">Nonaktif</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-400">Sync Harga</span>
                                    <div class="flex items-center gap-2">
                                        @if ($channel->price_sync_enabled)
                                            <span
                                                class="px-1.5 py-0.5 rounded bg-indigo-500/15 text-indigo-400">Aktif</span>
                                            <span
                                                class="text-gray-400">{{ $channel->last_price_sync_at ? $channel->last_price_sync_at->diffForHumans() : '—' }}</span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded bg-white/5 text-gray-500">Nonaktif</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <span
                                        class="w-2 h-2 rounded-full {{ $channel->webhook_enabled ? 'bg-emerald-400' : 'bg-gray-600' }}"></span>
                                    <span class="text-gray-400">Webhook
                                        {{ $channel->webhook_enabled ? 'Aktif' : 'Nonaktif' }}</span>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex flex-wrap gap-2 border-t border-white/5 pt-3">
                                <form method="POST" action="{{ route('ecommerce.channels.sync', $channel) }}">
                                    @csrf
                                    <button type="submit"
                                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium rounded-lg transition">
                                        Sync Order
                                    </button>
                                </form>

                                @if ($channel->stock_sync_enabled)
                                    <form method="POST"
                                        action="{{ route('ecommerce.channels.sync-stock', $channel) }}">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-medium rounded-lg transition">
                                            Sync Stok
                                        </button>
                                    </form>
                                @endif

                                @if ($channel->price_sync_enabled)
                                    <form method="POST"
                                        action="{{ route('ecommerce.channels.sync-prices', $channel) }}">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-xs font-medium rounded-lg transition">
                                            Sync Harga
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('ecommerce.channels.mappings', $channel) }}"
                                    class="px-3 py-1.5 bg-white/5 hover:bg-white/10 border border-white/10 text-gray-300 text-xs font-medium rounded-lg transition">
                                    Kelola Mapping
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Sync Log --}}
        <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-6 mt-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white">Log Sinkronisasi</h3>
                @if (($failedCount ?? 0) > 0)
                    <span class="px-3 py-1 text-xs font-medium bg-red-500/20 text-red-400 rounded-full">
                        {{ $failedCount }} gagal
                    </span>
                @endif
            </div>

            @if (isset($syncLogs) && $syncLogs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-gray-400 border-b border-white/10">
                                <th class="text-left py-2 px-3">Waktu</th>
                                <th class="text-left py-2 px-3">Channel</th>
                                <th class="text-left py-2 px-3">Tipe</th>
                                <th class="text-left py-2 px-3">Produk</th>
                                <th class="text-left py-2 px-3">Status</th>
                                <th class="text-left py-2 px-3">Percobaan</th>
                                <th class="text-left py-2 px-3 hidden lg:table-cell">Pesan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach ($syncLogs as $log)
                                <tr>
                                    <td class="py-2 px-3 text-gray-400">{{ $log->created_at->diffForHumans() }}</td>
                                    <td class="py-2 px-3 text-white">{{ $log->channel?->shop_name ?? '-' }}</td>
                                    <td class="py-2 px-3">
                                        <span
                                            class="px-2 py-0.5 text-xs rounded-full {{ $log->type === 'stock' ? 'bg-blue-500/20 text-blue-400' : 'bg-purple-500/20 text-purple-400' }}">
                                            {{ ucfirst($log->type) }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-white">{{ $log->mapping?->product?->name ?? '-' }}</td>
                                    <td class="py-2 px-3">
                                        @if ($log->status === 'success')
                                            <span
                                                class="px-2 py-0.5 text-xs rounded-full bg-emerald-500/20 text-emerald-400">Berhasil</span>
                                        @elseif($log->status === 'failed')
                                            <span
                                                class="px-2 py-0.5 text-xs rounded-full bg-red-500/20 text-red-400">Gagal</span>
                                        @elseif($log->status === 'abandoned')
                                            <span
                                                class="px-2 py-0.5 text-xs rounded-full bg-gray-500/20 text-gray-400">Diabaikan</span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 text-xs rounded-full bg-yellow-500/20 text-yellow-400">Retry</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-gray-400">{{ $log->attempt_count }}</td>
                                    <td class="py-2 px-3 text-gray-500 hidden lg:table-cell truncate max-w-xs">
                                        {{ $log->error_message ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-sm">Belum ada log sinkronisasi.</p>
            @endif
        </div>

        {{-- Recent Errors --}}
        <div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between">
                <h2 class="font-semibold text-white text-sm">Error Sinkronisasi Terbaru</h2>
                @if ($recentErrors->isNotEmpty())
                    <span class="text-xs text-gray-500">{{ $recentErrors->count() }} error</span>
                @endif
            </div>
            @if ($recentErrors->isEmpty())
                <div class="px-6 py-10 text-center text-gray-500 text-sm">
                    Tidak ada error terbaru. Semua sinkronisasi berjalan lancar.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-white/5 text-xs text-gray-400 uppercase">
                            <tr>
                                <th class="px-6 py-3 text-left">Waktu</th>
                                <th class="px-6 py-3 text-left">Channel</th>
                                <th class="px-6 py-3 text-left">Tipe</th>
                                <th class="px-6 py-3 text-left">Pesan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @foreach ($recentErrors as $err)
                                <tr class="hover:bg-white/5">
                                    <td class="px-6 py-3 text-gray-400 whitespace-nowrap text-xs">
                                        {{ isset($err['time']) ? \Carbon\Carbon::parse($err['time'])->diffForHumans() : '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-300">{{ $err['channel'] ?? '—' }}</td>
                                    <td class="px-6 py-3">
                                        <span
                                            class="px-2 py-0.5 rounded bg-red-500/15 text-red-400 text-xs">{{ $err['type'] ?? 'error' }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-gray-400 text-xs max-w-sm truncate">
                                        {{ $err['message'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
