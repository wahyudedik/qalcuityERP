<x-app-layout>
    <x-slot name="header">E-Commerce</x-slot>

    <div class="space-y-6">

        {{-- Channels --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Channel Marketplace</h2>

            @if($channels->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                @foreach($channels as $ch)
                <div class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] rounded-2xl p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-gray-900 dark:text-white font-bold text-sm
                        {{ $ch->platform === 'shopee' ? 'bg-orange-500' : ($ch->platform === 'tokopedia' ? 'bg-green-500' : 'bg-red-500') }}">
                        {{ strtoupper(substr($ch->platform, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $ch->shop_name }}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400 capitalize">{{ $ch->platform }}</p>
                        @if($ch->last_sync_at)
                        <p class="text-xs text-gray-400 dark:text-slate-500">Sync: {{ $ch->last_sync_at->diffForHumans() }}</p>
                        @endif
                    </div>
                    <div class="flex flex-col gap-1 items-end">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $ch->is_active ? 'bg-green-500/20 text-green-400' : 'bg-[#f8f8f8] dark:bg-white/10 text-gray-500 dark:text-slate-400' }}">
                            {{ $ch->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        <form method="POST" action="{{ route('ecommerce.channels.sync', $ch) }}">
                            @csrf
                            <button type="submit" class="text-xs text-blue-400 hover:underline">Sync</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Add Channel --}}
            <details class="border border-gray-200 dark:border-white/10 rounded-xl">
                <summary class="px-4 py-3 cursor-pointer text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-white/5 rounded-xl">
                    + Tambah Channel Marketplace
                </summary>
                <form method="POST" action="{{ route('ecommerce.channels.store') }}" class="px-4 pb-4 pt-2 space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">Platform</label>
                            <select name="platform" class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                                <option value="shopee">Shopee</option>
                                <option value="tokopedia">Tokopedia</option>
                                <option value="lazada">Lazada</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">Nama Toko</label>
                            <input type="text" name="shop_name" required placeholder="Nama toko Anda"
                                class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-slate-600 focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">API Key / Partner ID</label>
                            <input type="text" name="api_key" required
                                class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">API Secret</label>
                            <input type="text" name="api_secret" required
                                class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded border-white/20 bg-gray-50 dark:bg-[#0f172a] text-blue-500">
                        <span class="text-sm text-gray-700 dark:text-slate-300">Aktifkan channel</span>
                    </label>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-gray-900 dark:text-white rounded-xl text-sm font-medium hover:bg-blue-500 transition">
                        Simpan Channel
                    </button>
                </form>
            </details>
        </div>

        {{-- Orders --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <h2 class="font-semibold text-gray-900 dark:text-white">Order dari Marketplace</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Platform</th>
                            <th class="px-6 py-3 text-left">No. Order</th>
                            <th class="px-6 py-3 text-left">Pelanggan</th>
                            <th class="px-6 py-3 text-right">Total</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $order->channel?->platform === 'shopee' ? 'bg-orange-500/20 text-orange-400' : ($order->channel?->platform === 'tokopedia' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400') }}">
                                    {{ ucfirst($order->channel?->platform ?? '—') }}
                                </span>
                            </td>
                            <td class="px-6 py-3 font-mono text-xs text-gray-500 dark:text-slate-400">{{ $order->external_order_id }}</td>
                            <td class="px-6 py-3 text-gray-900 dark:text-white">{{ $order->customer_name }}</td>
                            <td class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400">{{ $order->status }}</span>
                            </td>
                            <td class="px-6 py-3 text-gray-400 dark:text-slate-500">{{ $order->ordered_at?->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400 dark:text-slate-500">Belum ada order. Tambahkan channel dan lakukan sync.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">{{ $orders->links() }}</div>
        </div>
    </div>
</x-app-layout>
