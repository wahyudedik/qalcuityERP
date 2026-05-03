<x-app-layout>
    <x-slot name="header">Work Center</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode / nama..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        @canmodule('manufacturing', 'create')
        <button onclick="document.getElementById('modal-create-wc').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Tambah Work Center</button>
        @endcanmodule
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-right">Biaya/Jam</th>
                        <th class="px-4 py-3 text-center">Kapasitas/Hari</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($workCenters as $wc)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900">{{ $wc->code }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $wc->name }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rp {{ number_format($wc->cost_per_hour, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center text-gray-900">{{ $wc->capacity_per_day }} jam</td>
                        <td class="px-4 py-3 text-center">
                            @if($wc->is_active)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Aktif</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @canmodule('manufacturing', 'edit')
                                <button onclick="openEditWc({{ $wc->id }}, '{{ $wc->code }}', '{{ addslashes($wc->name) }}', {{ $wc->cost_per_hour }}, {{ $wc->capacity_per_day }}, {{ $wc->is_active ? 'true' : 'false' }}, '{{ addslashes($wc->notes ?? '') }}')"
                                    class="text-xs px-2 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Edit</button>
                                @endcanmodule
                                @canmodule('manufacturing', 'delete')
                                <form method="POST" action="{{ url('manufacturing/work-centers') }}/{{ $wc->id }}" class="inline" onsubmit="return confirm('Hapus work center ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                                </form>
                                @endcanmodule
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada work center.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($workCenters->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $workCenters->links() }}</div>
        @endif
    </div>

    {{-- Modal Create --}}
    <div id="modal-create-wc" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Tambah Work Center</h3>
                <button onclick="document.getElementById('modal-create-wc').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('manufacturing.work-centers.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kode *</label>
                        <input type="text" name="code" required maxlength="20" placeholder="WC-01" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama *</label>
                        <input type="text" name="name" required placeholder="Mesin CNC 1" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Biaya/Jam (Rp)</label>
                        <input type="number" name="cost_per_hour" min="0" step="1000" value="0" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kapasitas/Hari (jam)</label>
                        <input type="number" name="capacity_per_day" min="1" max="24" value="8" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                        <input type="text" name="notes" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-create-wc').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div id="modal-edit-wc" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Edit Work Center</h3>
                <button onclick="document.getElementById('modal-edit-wc').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-edit-wc" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kode *</label>
                        <input type="text" name="code" id="edit-wc-code" required maxlength="20" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama *</label>
                        <input type="text" name="name" id="edit-wc-name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Biaya/Jam (Rp)</label>
                        <input type="number" name="cost_per_hour" id="edit-wc-cost" min="0" step="1000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kapasitas/Hari (jam)</label>
                        <input type="number" name="capacity_per_day" id="edit-wc-cap" min="1" max="24" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" id="edit-wc-active" value="1" class="rounded">
                            <span class="text-sm text-gray-700">Aktif</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                        <input type="text" name="notes" id="edit-wc-notes" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-edit-wc').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openEditWc(id, code, name, cost, cap, active, notes) {
        document.getElementById('form-edit-wc').action = '{{ url("manufacturing/work-centers") }}/' + id;
        document.getElementById('edit-wc-code').value = code;
        document.getElementById('edit-wc-name').value = name;
        document.getElementById('edit-wc-cost').value = cost;
        document.getElementById('edit-wc-cap').value = cap;
        document.getElementById('edit-wc-active').checked = active;
        document.getElementById('edit-wc-notes').value = notes;
        document.getElementById('modal-edit-wc').classList.remove('hidden');
    }
    </script>
    @endpush
</x-app-layout>
