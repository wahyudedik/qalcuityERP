<x-app-layout>
    <x-slot name="header">Putaway Rules</x-slot>

    <div class="flex justify-end mb-4">
        @canmodule('wms', 'create')
        <button onclick="document.getElementById('modal-rule').classList.remove('hidden')" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Rule</button>
        @endcanmodule
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr><th class="px-4 py-3 text-left">Gudang</th><th class="px-4 py-3 text-left">Produk/Kategori</th><th class="px-4 py-3 text-left">Zone</th><th class="px-4 py-3 text-left">Bin</th><th class="px-4 py-3 text-center">Prioritas</th><th class="px-4 py-3 text-center">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rules as $r)
                    <tr>
                        <td class="px-4 py-3 text-gray-900">{{ $r->warehouse?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $r->product?->name ?? $r->product_category ?? 'Semua' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $r->zone?->name ?? '-' }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $r->bin?->code ?? 'Auto' }}</td>
                        <td class="px-4 py-3 text-center text-gray-900">{{ $r->priority }}</td>
                        <td class="px-4 py-3 text-center">
                            @canmodule('wms', 'delete')
                            <form method="POST" action="{{ route('wms.putaway-rules.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:underline">Hapus</button>
                            </form>
                            @endcanmodule
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada putaway rule.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="modal-rule" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Tambah Putaway Rule</h3>
                <button onclick="document.getElementById('modal-rule').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('wms.putaway-rules.store') }}" class="p-6 space-y-3">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; @endphp
                <div><label class="block text-xs text-gray-600 mb-1">Gudang *</label>
                    <select name="warehouse_id" required class="{{ $cls }}">@foreach($warehouses ?? [] as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select>
                </div>
                <div><label class="block text-xs text-gray-600 mb-1">Produk (opsional)</label>
                    <select name="product_id" class="{{ $cls }}"><option value="">-- Semua --</option>@foreach($products ?? [] as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
                </div>
                <div><label class="block text-xs text-gray-600 mb-1">Kategori Produk (opsional)</label><input type="text" name="product_category" placeholder="elektronik, makanan..." class="{{ $cls }}"></div>
                <div><label class="block text-xs text-gray-600 mb-1">Prioritas</label><input type="number" name="priority" min="0" value="0" class="{{ $cls }}"></div>
                <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
            </form>
        </div>
    </div>
</x-app-layout>
