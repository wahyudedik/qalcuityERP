<x-app-layout>
    <x-slot name="header">Partner Konsinyasi</x-slot>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari partner..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <a href="{{ route('consignment.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">? Konsinyasi</a>
        @canmodule('consignment', 'create')
        <button onclick="document.getElementById('modal-add-partner').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Partner</button>
        @endcanmodule
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Kontak</th>
                        <th class="px-4 py-3 text-right">Komisi %</th>
                        <th class="px-4 py-3 text-center">Pengiriman</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($partners as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-900">{{ $p->name }}</td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500 text-xs">{{ $p->contact_person ?? '-' }} · {{ $p->phone ?? '-' }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ $p->commission_pct }}%</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $p->shipments_count }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($p->is_active)<span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Aktif</span>
                            @else<span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Nonaktif</span>@endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @canmodule('consignment', 'delete')
                            <form method="POST" action="{{ route('consignment.partners.destroy', $p) }}" class="inline" data-confirm="Hapus partner ini?" data-confirm-type="danger">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                            </form>
                            @endcanmodule
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada partner.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($partners->hasPages())<div class="px-4 py-3 border-t border-gray-100">{{ $partners->links() }}</div>@endif
    </div>

    {{-- Modal Add Partner --}}
    <div id="modal-add-partner" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Tambah Partner</h3>
                <button onclick="document.getElementById('modal-add-partner').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">?</button>
            </div>
            <form method="POST" action="{{ route('consignment.partners.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 mb-1">Nama Toko/Outlet *</label><input type="text" name="name" required class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Kontak</label><input type="text" name="contact_person" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Telepon</label><input type="text" name="phone" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Email</label><input type="email" name="email" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Komisi (%)</label><input type="number" name="commission_pct" min="0" max="100" step="0.01" value="10" class="{{ $cls }}"></div>
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 mb-1">Alamat</label><input type="text" name="address" class="{{ $cls }}"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-partner').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
