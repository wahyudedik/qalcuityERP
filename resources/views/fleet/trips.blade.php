<x-app-layout>
    <x-slot name="header">Trip / Penugasan Kendaraan</x-slot>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari trip..."
                class="flex-1 min-w-[150px] px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                @foreach(['planned'=>'Direncanakan','in_progress'=>'Berjalan','completed'=>'Selesai','cancelled'=>'Batal'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        @canmodule('fleet', 'create')
        <button onclick="document.getElementById('modal-add-trip').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Trip</button>
        @endcanmodule
    </div>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Trip</th>
                        <th class="px-4 py-3 text-left">Kendaraan</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Driver</th>
                        <th class="px-4 py-3 text-left">Tujuan</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Jarak</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($trips as $t)
                    @php
                        $tc = ['planned'=>'amber','in_progress'=>'blue','completed'=>'green','cancelled'=>'red'][$t->status] ?? 'gray';
                        $tl = ['planned'=>'Direncanakan','in_progress'=>'Berjalan','completed'=>'Selesai','cancelled'=>'Batal'][$t->status] ?? $t->status;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900 dark:text-white">{{ $t->trip_number }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300">{{ $t->vehicle->plate_number ?? '-' }}</td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-700 dark:text-slate-300">{{ $t->driver->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $t->purpose }}
                            @if($t->destination) <span class="text-xs text-gray-400">→ {{ $t->destination }}</span> @endif
                        </td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900 dark:text-white">
                            {{ $t->distanceKm() !== null ? number_format($t->distanceKm(), 0, ',', '.') . ' km' : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $tc }}-100 text-{{ $tc }}-700 dark:bg-{{ $tc }}-500/20 dark:text-{{ $tc }}-400">{{ $tl }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($t->status === 'in_progress')
                            @canmodule('fleet', 'edit')
                            <button onclick="openComplete({{ $t->id }}, '{{ $t->trip_number }}', {{ $t->odometer_start ?? 0 }})"
                                class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Selesai</button>
                            @endcanmodule
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada trip.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($trips->hasPages())<div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $trips->links() }}</div>@endif
    </div>

    {{-- Modal Add Trip --}}
    <div id="modal-add-trip" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Trip</h3>
                <button onclick="document.getElementById('modal-add-trip').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('fleet.trips.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kendaraan *</label>
                        <select name="vehicle_id" required class="{{ $cls }}"><option value="">-- Pilih --</option>
                            @foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->plate_number }} — {{ $v->name }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Driver</label>
                        <select name="driver_id" class="{{ $cls }}"><option value="">-- Tanpa Driver --</option>
                            @foreach($drivers as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tujuan *</label><input type="text" name="purpose" required placeholder="Kirim barang ke customer" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Asal</label><input type="text" name="origin" placeholder="Gudang Pusat" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tujuan</label><input type="text" name="destination" placeholder="Jakarta Selatan" class="{{ $cls }}"></div>
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Waktu Berangkat</label><input type="datetime-local" name="departed_at" class="{{ $cls }}"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-trip').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat Trip</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Complete Trip --}}
    <div id="modal-complete" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Selesaikan Trip</h3>
                <button onclick="document.getElementById('modal-complete').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-complete" method="POST" class="p-6 space-y-4">
                @csrf @method('PATCH')
                <p class="text-sm text-gray-600 dark:text-slate-400">Trip: <span id="c-trip" class="font-mono font-semibold text-gray-900 dark:text-white"></span></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Odometer Akhir (km) *</label>
                    <input type="number" name="odometer_end" id="c-odo" required min="0" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-complete').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Selesai</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openComplete(id, number, odoStart) {
        document.getElementById('form-complete').action = '{{ url("fleet/trips") }}/' + id + '/complete';
        document.getElementById('c-trip').textContent = number;
        document.getElementById('c-odo').value = odoStart;
        document.getElementById('c-odo').min = odoStart;
        document.getElementById('modal-complete').classList.remove('hidden');
    }
    </script>
    @endpush
</x-app-layout>
