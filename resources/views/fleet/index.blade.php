<x-app-layout>
    <x-slot name="header">Fleet Management</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Kendaraan</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Tersedia</p>
            <p class="text-2xl font-bold text-green-500">{{ $stats['available'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Sedang Dipakai</p>
            <p class="text-2xl font-bold text-blue-500">{{ $stats['in_use'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Maintenance</p>
            <p class="text-2xl font-bold text-amber-500">{{ $stats['maintenance'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">BBM Bulan Ini</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">Rp {{ number_format($stats['fuel_month'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Alerts --}}
    @if(count($alerts) > 0 || $upcomingMaint->isNotEmpty())
    <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 rounded-2xl p-4 mb-4">
        <p class="text-sm font-medium text-amber-700 dark:text-amber-400 mb-1">⚠️ Perhatian</p>
        <ul class="text-xs text-amber-600 dark:text-amber-300 space-y-0.5">
            @foreach($alerts as $a)<li>{{ $a }}</li>@endforeach
            @foreach($upcomingMaint as $m)<li>Maintenance {{ $m->vehicle->plate_number ?? '-' }}: {{ $m->description }} ({{ $m->scheduled_date?->format('d/m') }})</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari plat / nama..."
                class="flex-1 min-w-[150px] px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                @foreach(['available'=>'Tersedia','in_use'=>'Dipakai','maintenance'=>'Maintenance','retired'=>'Pensiun'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        @canmodule('fleet', 'create')
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Kendaraan</button>
        @endcanmodule
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Plat</th>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Tipe</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Odometer</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($vehicles as $v)
                    @php
                        $sc = ['available'=>'green','in_use'=>'blue','maintenance'=>'amber','retired'=>'gray'][$v->status] ?? 'gray';
                        $sl = ['available'=>'Tersedia','in_use'=>'Dipakai','maintenance'=>'Maintenance','retired'=>'Pensiun'][$v->status] ?? $v->status;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900 dark:text-white">{{ $v->plate_number }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300">
                            {{ $v->name }}
                            @if($v->brand) <span class="text-xs text-gray-400">({{ $v->brand }})</span> @endif
                        </td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell text-xs text-gray-500 dark:text-slate-400">
                            {{ ['car'=>'Mobil','truck'=>'Truk','motorcycle'=>'Motor','van'=>'Van'][$v->type] ?? $v->type }}
                        </td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900 dark:text-white">{{ number_format($v->odometer, 0, ',', '.') }} km</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700 dark:bg-{{ $sc }}-500/20 dark:text-{{ $sc }}-400">{{ $sl }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @canmodule('fleet', 'edit')
                                <button onclick='openEdit(@json($v))' class="text-xs px-2 py-1 border border-gray-200 dark:border-white/10 rounded-lg text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Edit</button>
                                @endcanmodule
                                @canmodule('fleet', 'delete')
                                <form method="POST" action="{{ url('fleet/vehicles') }}/{{ $v->id }}" class="inline" onsubmit="return confirm('Hapus kendaraan ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                                </form>
                                @endcanmodule
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada kendaraan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vehicles->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $vehicles->links() }}</div>
        @endif
    </div>

    {{-- Modal Add --}}
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Kendaraan</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('fleet.vehicles.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">No. Plat *</label><input type="text" name="plate_number" required placeholder="B 1234 XYZ" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label><input type="text" name="name" required placeholder="Toyota Avanza 2024" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                        <select name="type" required class="{{ $cls }}">
                            <option value="car">Mobil</option><option value="truck">Truk</option><option value="motorcycle">Motor</option><option value="van">Van</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Merek</label><input type="text" name="brand" placeholder="Toyota" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Model</label><input type="text" name="model" placeholder="Avanza" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tahun</label><input type="number" name="year" min="1990" max="2030" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Warna</label><input type="text" name="color" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Odometer (km)</label><input type="number" name="odometer" min="0" value="0" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">STNK Expired</label><input type="date" name="registration_expiry" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Asuransi Expired</label><input type="date" name="insurance_expiry" class="{{ $cls }}"></div>
                    @if($assets->isNotEmpty())
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Link ke Aset</label>
                        <select name="asset_id" class="{{ $cls }}"><option value="">-- Tanpa Link --</option>
                            @foreach($assets as $a)<option value="{{ $a->id }}">{{ $a->asset_code }} — {{ $a->name }}</option>@endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label><input type="text" name="notes" class="{{ $cls }}"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Edit Kendaraan</h3>
                <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-edit" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">No. Plat *</label><input type="text" name="plate_number" id="e-plate" required class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label><input type="text" name="name" id="e-name" required class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                        <select name="type" id="e-type" required class="{{ $cls }}">
                            <option value="car">Mobil</option><option value="truck">Truk</option><option value="motorcycle">Motor</option><option value="van">Van</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Status</label>
                        <select name="status" id="e-status" class="{{ $cls }}">
                            <option value="available">Tersedia</option><option value="in_use">Dipakai</option><option value="maintenance">Maintenance</option><option value="retired">Pensiun</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Merek</label><input type="text" name="brand" id="e-brand" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Model</label><input type="text" name="model" id="e-model" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Odometer</label><input type="number" name="odometer" id="e-odo" min="0" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">STNK Expired</label><input type="date" name="registration_expiry" id="e-reg" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Asuransi Expired</label><input type="date" name="insurance_expiry" id="e-ins" class="{{ $cls }}"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openEdit(v) {
        document.getElementById('form-edit').action = '{{ url("fleet/vehicles") }}/' + v.id;
        document.getElementById('e-plate').value = v.plate_number;
        document.getElementById('e-name').value = v.name;
        document.getElementById('e-type').value = v.type;
        document.getElementById('e-status').value = v.status;
        document.getElementById('e-brand').value = v.brand || '';
        document.getElementById('e-model').value = v.model || '';
        document.getElementById('e-odo').value = v.odometer;
        document.getElementById('e-reg').value = v.registration_expiry ? v.registration_expiry.substring(0,10) : '';
        document.getElementById('e-ins').value = v.insurance_expiry ? v.insurance_expiry.substring(0,10) : '';
        document.getElementById('modal-edit').classList.remove('hidden');
    }
    </script>
    @endpush
</x-app-layout>
