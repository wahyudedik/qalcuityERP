<x-app-layout>
    <x-slot name="header">Manajemen Gudang</x-slot>

    <div class="flex flex-col sm:flex-row gap-6">
        {{-- Form Tambah --}}
        <div class="w-full sm:w-80 shrink-0">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Tambah Gudang</h3>
                <form method="POST" action="{{ route('inventory.warehouses.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Gudang *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kode (opsional)</label>
                        <input type="text" name="code" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Alamat</label>
                        <textarea name="address" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tambah Gudang</button>
                </form>
            </div>
        </div>

        {{-- List --}}
        <div class="flex-1">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Daftar Gudang</h3>
                    <a href="{{ route('inventory.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">← Kembali ke Produk</a>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($warehouses as $wh)
                    @php $stockCount = $wh->productStocks->sum('quantity'); @endphp
                    <div class="px-5 py-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $wh->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">Kode: {{ $wh->code }} @if($wh->address) · {{ $wh->address }}@endif</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($wh->productStocks->sum('quantity')) }}</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">total stok</p>
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-10 text-center text-gray-400 dark:text-slate-500 text-sm">Belum ada gudang.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
