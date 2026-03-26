<x-app-layout>
    <x-slot name="header">Maintenance Kendaraan</x-slot>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <select name="vehicle_id" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Kendaraan</option>
                @foreach($vehicles as $v)<option value="{{ $v->id }}" @selected(request('vehicle_id')==$v->id)>{{ $v->plate_number }}</option>@endforeach
            </select>
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                @foreach(['scheduled'=>'Terjadwal','in_progress'=>'Dikerjakan','completed'=>'Selesai','cancelled'=>'Batal'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
        @canmodule('fleet', 'create')
        <button onclick="document.getElementById('modal-add-maint').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Jadwal</button>
        @endcanmodule
    </div>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Kendaraan</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Jadwal</th>
                        <th class="px-4 py-3 text-right hidden sm:table-cell">Biaya</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($maintenances as $m)
                    @php
                        $mc = ['scheduled'=>'amber','in_progress'=>'blue','completed'=>'green','cancelled'=>'gray'][$m->status] ?? 'gray';
                        $ml = ['scheduled'=>'Terjadwal','in_progress'=>'Dikerjakan','completed'=>'Selesai','cancelled'=>'Batal'][$m->status] ?? $m->status;
                        $tl = ['routine'=>'Rutin','repair'=>'Perbaikan','inspection'=>'Inspeksi','tire'=>'Ban','oil_change'=>'Ganti Oli'][$m->type] ?? $m->type;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs text-gray-900 dark:text-white">{{ $m->vehicle->plate_number ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400">{{ $tl }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300">{{ $m->description }}</td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell text-xs text-gray-500 dark:text-slate-400">{{ $m->scheduled_date?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-4 py-3 text-right hidden sm:table-cell text-gray-900 dark:text-white">Rp {{ number_format($m->cost, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $mc }}-100 text-{{ $mc }}-700 dark:bg-{{ $mc }}-500/20 dark:text-{{ $mc }}-400">{{ $ml }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @if($m->status === 'scheduled' || $m->status === 'in_progress')
                                @canmodule('fleet', 'edit')
                                <button onclick="openCompleteMaint({{ $m->id }})" class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Selesai</button>
                                @endcanmodule
                                @endif
                                @canmodule('fleet', 'delete')
                                <form method="POST" action="{{ url('fleet/maintenance') }}/{{ $m->id }}" class="inline" onsubmit="return confirm('Hapus?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                                </form>
                                @endcanmodule
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada jadwal maintenance.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($maintenances->hasPages())<div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $maintenances->links() }}</div>@endif
    </div>

    {{-- Modal Add Maintenance --}}
    <div id="modal-add-maint" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Jadwal Maintenance</h3>
                <button onclick="document.getElementById('modal-add-maint').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('fleet.maintenance.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kendaraan *</label>
                        <select name="vehicle_id" required class="{{ $cls }}"><option value="">-- Pilih --</option>
                            @foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->plate_number }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                        <select name="type" required class="{{ $cls }}">
                            <option value="routine">Rutin</option><option value="repair">Perbaikan</option><option value="inspection">Inspeksi</option><option value="tire">Ban</option><option value="oil_change">Ganti Oli</option>
                        </select>
                    </div>
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi *</label><input type="text" name="description" required placeholder="Service rutin 10.000 km" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Jadwal</label><input type="date" name="scheduled_date" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Estimasi Biaya</label><input type="number" name="cost" min="0" step="1000" value="0" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Vendor/Bengkel</label><input type="text" name="vendor" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Next di KM</label><input type="number" name="next_km" min="0" class="{{ $cls }}"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-maint').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Complete Maintenance --}}
    <div id="modal-complete-maint" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Selesaikan Maintenance</h3>
                <button onclick="document.getElementById('modal-complete-maint').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-complete-maint" method="POST" class="p-6 space-y-4">
                @csrf @method('PATCH')
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Biaya Aktual *</label><input type="number" name="cost" required min="0" step="1000" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Selesai *</label><input type="date" name="completed_date" required value="{{ date('Y-m-d') }}" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Odometer</label><input type="number" name="odometer_at" min="0" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Vendor</label><input type="text" name="vendor" class="{{ $cls }}"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-complete-maint').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Selesai</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openCompleteMaint(id) {
        document.getElementById('form-complete-maint').action = '{{ url("fleet/maintenance") }}/' + id + '/complete';
        document.getElementById('modal-complete-maint').classList.remove('hidden');
    }
    </script>
    @endpush
</x-app-layout>
