<x-app-layout>
    <x-slot name="header">Jadwal Maintenance Aset</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
        <form method="GET" class="flex gap-2 flex-1">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <option value="pending" @selected(request('status')==='pending')>Pending</option>
                <option value="in_progress" @selected(request('status')==='in_progress')>Dalam Proses</option>
                <option value="completed" @selected(request('status')==='completed')>Selesai</option>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('assets.index') }}" class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">← Daftar Aset</a>
            <button onclick="document.getElementById('modal-add-maintenance').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Jadwalkan</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Aset</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tipe</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Jadwal</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Biaya</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($maintenances as $m)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $m->asset->name ?? '-' }}</td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500 dark:text-slate-400 capitalize">{{ $m->type }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300 text-xs">{{ Str::limit($m->description, 50) }}</td>
                        <td class="px-4 py-3 hidden md:table-cell text-xs text-gray-500 dark:text-slate-400">{{ $m->scheduled_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-700 dark:text-slate-300">{{ $m->cost > 0 ? 'Rp '.number_format($m->cost,0,',','.') : '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @php $mc = ['pending'=>'amber','in_progress'=>'blue','completed'=>'green'][$m->status] ?? 'gray'; @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $mc }}-100 text-{{ $mc }}-700 dark:bg-{{ $mc }}-500/20 dark:text-{{ $mc }}-400">
                                {{ ['pending'=>'Pending','in_progress'=>'Proses','completed'=>'Selesai'][$m->status] ?? $m->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($m->status !== 'completed')
                            <form method="POST" action="{{ route('assets.maintenance.status', $m) }}" class="inline">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="text-xs px-2 py-1 rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                                    <option value="pending" @selected($m->status==='pending')>Pending</option>
                                    <option value="in_progress" @selected($m->status==='in_progress')>Proses</option>
                                    <option value="completed" @selected($m->status==='completed')>Selesai</option>
                                </select>
                            </form>
                            @else
                            <span class="text-xs text-gray-400 dark:text-slate-500">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada jadwal maintenance.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($maintenances->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $maintenances->links() }}</div>
        @endif
    </div>

    {{-- Modal Tambah Maintenance --}}
    <div id="modal-add-maintenance" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Jadwalkan Maintenance</h3>
                <button onclick="document.getElementById('modal-add-maintenance').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('assets.maintenance.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Aset *</label>
                    <select name="asset_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Aset --</option>
                        @foreach($assets as $a)<option value="{{ $a->id }}">{{ $a->name }} ({{ $a->asset_code }})</option>@endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe</label>
                        <select name="type" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="scheduled">Terjadwal</option>
                            <option value="preventive">Preventif</option>
                            <option value="corrective">Korektif</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal *</label>
                        <input type="date" name="scheduled_date" value="{{ today()->format('Y-m-d') }}" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi Pekerjaan *</label>
                    <textarea name="description" rows="2" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Estimasi Biaya</label>
                        <input type="number" name="cost" min="0" step="10000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Vendor/Bengkel</label>
                        <input type="text" name="vendor" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-maintenance').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
