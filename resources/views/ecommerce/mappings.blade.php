<x-app-layout>
    <x-slot name="header">Mapping Produk</x-slot>

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-white">
                    Mapping Produk —
                    <span
                        class="{{ $channel->platform === 'shopee' ? 'text-orange-400' : ($channel->platform === 'tokopedia' ? 'text-green-400' : 'text-red-400') }}">
                        {{ $channel->shop_name }}
                    </span>
                    <span class="text-gray-500 text-base font-normal">({{ ucfirst($channel->platform) }})</span>
                </h1>
                <p class="text-sm text-gray-400 mt-0.5">Kelola pemetaan SKU produk ke marketplace</p>
            </div>
            <a href="{{ route('ecommerce.dashboard') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 border border-white/10 text-gray-300 rounded-xl text-sm hover:bg-white/10 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Dashboard
            </a>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="px-4 py-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Add Mapping Form --}}
        <div x-data="{ open: false }" class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
            <button @click="open = !open"
                class="w-full flex items-center justify-between px-6 py-4 text-sm font-medium text-gray-300 hover:bg-white/5 transition">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Mapping Produk
                </span>
                <svg class="w-4 h-4 text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-transition class="border-t border-white/10">
                <form method="POST" action="{{ route('ecommerce.channels.mappings.store', $channel) }}"
                    class="px-6 py-5 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Produk <span
                                    class="text-red-400">*</span></label>
                            <select name="product_id" required
                                class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30">
                                <option value="" class="bg-[#1e293b] text-gray-400">-- Pilih Produk --</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" class="bg-[#1e293b]">
                                        {{ $product->name }} ({{ $product->sku }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">SKU Marketplace <span
                                    class="text-red-400">*</span></label>
                            <input type="text" name="external_sku" required placeholder="SKU di platform marketplace"
                                class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm placeholder-gray-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30">
                            @error('external_sku')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">ID Produk Marketplace</label>
                            <input type="text" name="external_product_id" placeholder="Opsional"
                                class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm placeholder-gray-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1.5">Harga Override</label>
                            <input type="number" name="price_override" min="0" step="100"
                                placeholder="Kosongkan untuk pakai harga jual"
                                class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-3 py-2.5 text-sm placeholder-gray-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/30">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition">
                            Tambah Mapping
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Mappings Table --}}
        <div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between">
                <h2 class="font-semibold text-white text-sm">Daftar Mapping</h2>
                <span class="text-xs text-gray-500">{{ $mappings->total() }} mapping</span>
            </div>

            @if ($mappings->isEmpty())
                <div class="px-6 py-14 text-center">
                    <div class="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm">Belum ada mapping produk</p>
                    <p class="text-gray-600 text-xs mt-1">Tambah mapping di atas untuk menghubungkan produk ke
                        marketplace</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-white/5 text-xs text-gray-400 uppercase">
                            <tr>
                                <th class="px-6 py-3 text-left">Produk</th>
                                <th class="px-6 py-3 text-left">SKU Internal</th>
                                <th class="px-6 py-3 text-left">SKU Marketplace</th>
                                <th class="px-6 py-3 text-left">ID Marketplace</th>
                                <th class="px-6 py-3 text-right">Harga Override</th>
                                <th class="px-6 py-3 text-left hidden lg:table-cell">Sync Stok</th>
                                <th class="px-6 py-3 text-left hidden lg:table-cell">Sync Harga</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @foreach ($mappings as $mapping)
                                <tr class="hover:bg-white/5">
                                    <td class="px-6 py-3 text-white font-medium">{{ $mapping->product->name ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 font-mono text-xs text-gray-400">
                                        {{ $mapping->product->sku ?? '—' }}</td>
                                    <td class="px-6 py-3 font-mono text-xs text-indigo-300">
                                        {{ $mapping->external_sku }}</td>
                                    <td class="px-6 py-3 font-mono text-xs text-gray-500">
                                        {{ $mapping->external_product_id ?? '—' }}</td>
                                    <td class="px-6 py-3 text-right text-gray-300">
                                        @if ($mapping->price_override)
                                            <span class="text-amber-400">Rp
                                                {{ number_format($mapping->price_override, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-gray-500">Default</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-gray-400 text-xs hidden lg:table-cell">
                                        {{ $mapping->last_stock_sync_at?->diffForHumans() ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-400 text-xs hidden lg:table-cell">
                                        {{ $mapping->last_price_sync_at?->diffForHumans() ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <form method="POST"
                                            action="{{ route('ecommerce.channels.mappings.destroy', $mapping) }}"
                                            onsubmit="return confirm('Hapus mapping ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-3 py-1.5 bg-red-600/20 hover:bg-red-600/40 text-red-400 text-xs font-medium rounded-lg border border-red-500/20 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-white/10">
                    {{ $mappings->links() }}
                </div>
            @endif
        </div>

        {{-- Riwayat Perubahan Harga --}}
        @if (isset($priceHistories) && $priceHistories->isNotEmpty())
            <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-6 mt-8">
                <h3 class="text-lg font-semibold text-white mb-4">Riwayat Perubahan Harga</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-gray-400 border-b border-white/10">
                                <th class="text-left py-2 px-3">Produk</th>
                                <th class="text-left py-2 px-3">Harga Lama</th>
                                <th class="text-left py-2 px-3">Harga Baru</th>
                                <th class="text-left py-2 px-3">Perubahan</th>
                                <th class="text-left py-2 px-3">Order Sebelum (7h)</th>
                                <th class="text-left py-2 px-3">Order Sesudah (7h)</th>
                                <th class="text-left py-2 px-3">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach ($priceHistories->flatten()->sortByDesc('created_at')->take(10) as $ph)
                                @php
                                    $pctChange =
                                        $ph->old_price > 0
                                            ? round((($ph->new_price - $ph->old_price) / $ph->old_price) * 100, 1)
                                            : 0;
                                @endphp
                                <tr>
                                    <td class="py-2 px-3 text-white">{{ $ph->product?->name ?? '-' }}</td>
                                    <td class="py-2 px-3 text-gray-400">Rp
                                        {{ number_format($ph->old_price, 0, ',', '.') }}</td>
                                    <td class="py-2 px-3 text-white">Rp
                                        {{ number_format($ph->new_price, 0, ',', '.') }}</td>
                                    <td class="py-2 px-3 {{ $pctChange >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $pctChange >= 0 ? '+' : '' }}{{ $pctChange }}%
                                    </td>
                                    <td class="py-2 px-3 text-gray-400">{{ $ph->orders_before_7d }}</td>
                                    <td class="py-2 px-3 text-gray-400">{{ $ph->orders_after_7d ?: 'Menunggu...' }}
                                    </td>
                                    <td class="py-2 px-3 text-gray-500">{{ $ph->created_at->format('d M Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
