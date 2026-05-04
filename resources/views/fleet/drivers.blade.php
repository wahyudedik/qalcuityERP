<x-app-layout>
    <x-slot name="header">Driver / Pengemudi</x-slot>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / SIM..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        @canmodule('fleet', 'create')
        <button onclick="document.getElementById('modal-add-driver').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Driver</button>
        @endcanmodule
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">No. SIM</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Tipe SIM</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Telepon</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($drivers as $d)
                        @php
                            $dc =
                                ['active' => 'green', 'on_trip' => 'blue', 'off_duty' => 'amber', 'inactive' => 'gray'][
                                    $d->status
                                ] ?? 'gray';
                            $dl =
                                [
                                    'active' => 'Aktif',
                                    'on_trip' => 'Dalam Trip',
                                    'off_duty' => 'Libur',
                                    'inactive' => 'Nonaktif',
                                ][$d->status] ?? $d->status;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-900">{{ $d->name }}
                                @if ($d->employee)
                                    <span class="text-xs text-gray-400">({{ $d->employee?->employee_id ?? '' }})</span>
                                @endif
                            </td>
                            <td
                                class="px-4 py-3 hidden sm:table-cell text-gray-700 font-mono text-xs">
                                {{ $d->license_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell text-gray-700">
                                {{ $d->license_type ?? '-' }}</td>
                            <td class="px-4 py-3 hidden md:table-cell text-gray-500">
                                {{ $d->phone ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-{{ $dc  }}-100 text-{{ $dc }}-700 $dc }}-500/20 $dc }}-400">{{ $dl }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @canmodule('fleet', 'edit')
                                    <button onclick='openEditDriver(@json($d))'
                                        class="text-xs px-2 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Edit</button>
                                    @endcanmodule
                                    @canmodule('fleet', 'delete')
                                    <form method="POST" action="{{ route('fleet.drivers.destroy', $d) }}"
                                        class="inline" onsubmit="return confirm('Hapus driver ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                                    </form>
                                    @endcanmodule
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum
                                ada driver.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($drivers->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $drivers->links() }}</div>
        @endif
    </div>

    @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500'; @endphp

    {{-- Modal Add Driver --}}
    <div id="modal-add-driver" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Tambah Driver</h3>
                <button onclick="document.getElementById('modal-add-driver').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('fleet.drivers.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2"><label
                            class="block text-xs font-medium text-gray-600 mb-1">Nama
                            *</label><input type="text" name="name" required class="{{ $cls }}"></div>
                    @if ($employees->isNotEmpty())
                        <div class="sm:col-span-2"><label
                                class="block text-xs font-medium text-gray-600 mb-1">Link
                                Karyawan</label>
                            <select name="employee_id" class="{{ $cls }}">
                                <option value="">-- Tanpa Link --</option>
                                @foreach ($employees as $e)
                                    <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">No.
                            SIM</label><input type="text" name="license_number" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Tipe
                            SIM</label>
                        <select name="license_type" class="{{ $cls }}">
                            <option value="">-</option>
                            <option value="A">A</option>
                            <option value="B1">B1</option>
                            <option value="B2">B2</option>
                            <option value="C">C</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">SIM
                            Expired</label><input type="date" name="license_expiry" class="{{ $cls }}">
                    </div>
                    <div><label
                            class="block text-xs font-medium text-gray-600 mb-1">Telepon</label><input
                            type="text" name="phone" class="{{ $cls }}"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-driver').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Driver --}}
    <div id="modal-edit-driver" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Edit Driver</h3>
                <button onclick="document.getElementById('modal-edit-driver').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-edit-driver" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2"><label
                            class="block text-xs font-medium text-gray-600 mb-1">Nama
                            *</label><input type="text" name="name" id="ed-name" required
                            class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">No.
                            SIM</label><input type="text" name="license_number" id="ed-license"
                            class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Tipe
                            SIM</label>
                        <select name="license_type" id="ed-license-type" class="{{ $cls }}">
                            <option value="">-</option>
                            <option value="A">A</option>
                            <option value="B1">B1</option>
                            <option value="B2">B2</option>
                            <option value="C">C</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">SIM
                            Expired</label><input type="date" name="license_expiry" id="ed-license-expiry"
                            class="{{ $cls }}"></div>
                    <div><label
                            class="block text-xs font-medium text-gray-600 mb-1">Telepon</label><input
                            type="text" name="phone" id="ed-phone" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                        <select name="status" id="ed-status" class="{{ $cls }}">
                            <option value="active">Aktif</option>
                            <option value="on_trip">Dalam Trip</option>
                            <option value="off_duty">Libur</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button"
                        onclick="document.getElementById('modal-edit-driver').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openEditDriver(d) {
                document.getElementById('form-edit-driver').action = '{{ url('fleet/drivers') }}/' + d.id;
                document.getElementById('ed-name').value = d.name || '';
                document.getElementById('ed-license').value = d.license_number || '';
                document.getElementById('ed-license-type').value = d.license_type || '';
                document.getElementById('ed-license-expiry').value = d.license_expiry ? d.license_expiry.substring(0, 10) : '';
                document.getElementById('ed-phone').value = d.phone || '';
                document.getElementById('ed-status').value = d.status || 'active';
                document.getElementById('modal-edit-driver').classList.remove('hidden');
            }
        </script>
    @endpush
</x-app-layout>
