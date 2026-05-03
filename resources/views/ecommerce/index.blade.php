<x-app-layout>
    <x-slot name="header">E-Commerce</x-slot>

    <div class="space-y-6">

        {{-- Top Nav --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-white">Daftar Order Marketplace</h1>
                <p class="text-xs text-gray-500 mt-0.5">Semua order dari channel marketplace Anda</p>
            </div>
            <a href="{{ route('ecommerce.dashboard') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Dashboard Marketplace
            </a>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="px-4 py-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Channels --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h2 class="font-semibold text-white mb-4">Channel Marketplace</h2>

            @if ($channels->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    @foreach ($channels as $ch)
                        <div x-data="{ editing: false }"
                            class="border border-gray-200 bg-gray-50 rounded-2xl p-4 space-y-3">

                            {{-- Channel Info Row --}}
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0
                            {{ $ch->platform === 'shopee' ? 'bg-orange-500' : ($ch->platform === 'tokopedia' ? 'bg-green-500' : 'bg-red-500') }}">
                                    {{ strtoupper(substr($ch->platform, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-white text-sm">{{ $ch->shop_name }}</p>
                                    <p class="text-xs text-gray-500 capitalize">{{ $ch->platform }}
                                    </p>
                                    @if ($ch->last_sync_at)
                                        <p class="text-xs text-gray-400">Sync:
                                            {{ $ch->last_sync_at->diffForHumans() }}</p>
                                    @endif
                                </div>
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs {{ $ch->is_active ? 'bg-green-500/20 text-green-400' : 'bg-white/10 text-gray-500' }}">
                                    {{ $ch->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>

                            {{-- Sync Status Badges --}}
                            <div class="flex flex-wrap gap-1.5 text-xs">
                                <span
                                    class="px-2 py-0.5 rounded {{ $ch->stock_sync_enabled ? 'bg-emerald-500/15 text-emerald-400' : 'bg-white/5 text-gray-500' }}">
                                    Stok: {{ $ch->stock_sync_enabled ? 'On' : 'Off' }}
                                </span>
                                <span
                                    class="px-2 py-0.5 rounded {{ $ch->price_sync_enabled ? 'bg-indigo-500/15 text-indigo-400' : 'bg-white/5 text-gray-500' }}">
                                    Harga: {{ $ch->price_sync_enabled ? 'On' : 'Off' }}
                                </span>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex flex-wrap gap-2 pt-1 border-t border-white/5">
                                <form method="POST" action="{{ route('ecommerce.channels.sync', $ch) }}">
                                    @csrf
                                    <button type="submit"
                                        class="px-2.5 py-1 bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-400 text-xs rounded-lg border border-indigo-500/20 transition">Sync</button>
                                </form>
                                <a href="{{ route('ecommerce.channels.mappings', $ch) }}"
                                    class="px-2.5 py-1 bg-white/5 hover:bg-white/10 text-gray-300 text-xs rounded-lg border border-white/10 transition">Kelola
                                    Mapping</a>
                                <button @click="editing = !editing"
                                    class="px-2.5 py-1 bg-white/5 hover:bg-white/10 text-gray-400 text-xs rounded-lg border border-white/10 transition">Edit</button>
                                <form method="POST" action="{{ route('ecommerce.channels.destroy', $ch) }}"
                                    onsubmit="return confirm('Hapus channel {{ $ch->shop_name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="px-2.5 py-1 bg-red-600/15 hover:bg-red-600/30 text-red-400 text-xs rounded-lg border border-red-500/20 transition">Hapus</button>
                                </form>
                            </div>

                            {{-- Inline Edit Form --}}
                            <div x-show="editing" x-transition class="border-t border-white/10 pt-3">
                                <form method="POST" action="{{ route('ecommerce.channels.update', $ch) }}"
                                    class="space-y-2">
                                    @csrf @method('PUT')
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Nama Toko</label>
                                        <input type="text" name="shop_name" value="{{ $ch->shop_name }}" required
                                            class="w-full bg-white/5 border border-white/10 text-white rounded-lg px-2.5 py-1.5 text-xs focus:outline-none focus:border-indigo-500">
                                    </div>
                                    <div class="flex gap-3">
                                        <label class="flex items-center gap-1.5 cursor-pointer text-xs text-gray-400">
                                            <input type="hidden" name="stock_sync_enabled" value="0">
                                            <input type="checkbox" name="stock_sync_enabled" value="1"
                                                {{ $ch->stock_sync_enabled ? 'checked' : '' }}
                                                class="w-3.5 h-3.5 rounded border-white/20 bg-transparent text-emerald-500">
                                            Sync Stok
                                        </label>
                                        <label class="flex items-center gap-1.5 cursor-pointer text-xs text-gray-400">
                                            <input type="hidden" name="price_sync_enabled" value="0">
                                            <input type="checkbox" name="price_sync_enabled" value="1"
                                                {{ $ch->price_sync_enabled ? 'checked' : '' }}
                                                class="w-3.5 h-3.5 rounded border-white/20 bg-transparent text-indigo-500">
                                            Sync Harga
                                        </label>
                                        <label class="flex items-center gap-1.5 cursor-pointer text-xs text-gray-400">
                                            <input type="hidden" name="is_active" value="0">
                                            <input type="checkbox" name="is_active" value="1"
                                                {{ $ch->is_active ? 'checked' : '' }}
                                                class="w-3.5 h-3.5 rounded border-white/20 bg-transparent text-green-500">
                                            Aktif
                                        </label>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit"
                                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-medium rounded-lg transition">Simpan</button>
                                        <button type="button" @click="editing = false"
                                            class="px-3 py-1.5 bg-white/5 hover:bg-white/10 text-gray-400 text-xs rounded-lg transition">Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Add Channel --}}
            <details class="border border-gray-200 rounded-xl">
                <summary
                    class="px-4 py-3 cursor-pointer text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl">
                    + Tambah Channel Marketplace
                </summary>
                <form method="POST" action="{{ route('ecommerce.channels.store') }}" class="px-4 pb-4 pt-2 space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1.5">Platform</label>
                            <select name="platform"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                                <option value="shopee">Shopee</option>
                                <option value="tokopedia">Tokopedia</option>
                                <option value="lazada">Lazada</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1.5">Nama Toko</label>
                            <input type="text" name="shop_name" required placeholder="Nama toko Anda"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1.5">API Key / Partner
                                ID</label>
                            <input type="text" name="api_key" required
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1.5">API Secret</label>
                            <input type="text" name="api_secret" required
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked
                            class="w-4 h-4 rounded border-white/20 bg-gray-50 text-blue-500">
                        <span class="text-sm text-gray-700">Aktifkan channel</span>
                    </label>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-500 transition">
                        Simpan Channel
                    </button>
                </form>
            </details>
        </div>

        {{-- Orders --}}
        <div
            class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-white">Order dari Marketplace</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Platform</th>
                            <th class="px-6 py-3 text-left">No. Order</th>
                            <th class="px-6 py-3 text-left">Pelanggan</th>
                            <th class="px-6 py-3 text-right">Total</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $order->channel?->platform === 'shopee' ? 'bg-orange-500/20 text-orange-400' : ($order->channel?->platform === 'tokopedia' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400') }}">
                                        {{ ucfirst($order->channel?->platform ?? '—') }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 font-mono text-xs text-gray-500">
                                    {{ $order->external_order_id }}</td>
                                <td class="px-6 py-3 text-white">{{ $order->customer_name }}</td>
                                <td class="px-6 py-3 text-right font-medium text-white">Rp
                                    {{ number_format($order->total, 0, ',', '.') }}</td>
                                <td class="px-6 py-3">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400">{{ $order->status }}</span>
                                </td>
                                <td class="px-6 py-3 text-gray-400 hidden sm:table-cell">
                                    {{ $order->ordered_at?->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                    Belum ada order. Tambahkan channel dan lakukan sync.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">{{ $orders->links() }}</div>
        </div>
    </div>
</x-app-layout>
