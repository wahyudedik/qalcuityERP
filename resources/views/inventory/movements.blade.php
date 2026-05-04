<x-app-layout>
    <x-slot name="header">Riwayat Pergerakan Stok</x-slot>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">Semua Pergerakan Stok</h3>
            <a href="{{ route('inventory.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Gudang</th>
                        <th class="px-4 py-3 text-center">Tipe</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Sebelum</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Sesudah</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Catatan</th>
                        <th class="px-4 py-3 text-left">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($movements as $m)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $m->product?->name ?? '-' }}</td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500">{{ $m->warehouse?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $m->type === 'in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $m->type === 'in' ? 'Masuk' : 'Keluar' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold {{ $m->type === 'in' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $m->type === 'in' ? '+' : '-' }}{{ $m->quantity }}
                        </td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-500">{{ $m->quantity_before }}</td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900">{{ $m->quantity_after }}</td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500 text-xs">{{ $m->notes ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $m->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">Belum ada pergerakan stok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $movements->links() }}</div>
        @endif
    </div>
</x-app-layout>
