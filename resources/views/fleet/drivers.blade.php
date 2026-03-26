<x-app-layout>
    <x-slot name="header">Driver / Pengemudi</x-slot>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / SIM..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        @canmodule('fleet', 'create')
        <button onclick="document.getElementById('modal-add-driver').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Driver</button>
        @endcanmodule
    </div>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">No. SIM</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Tipe SIM</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Telepon</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($drivers as $d)
                    @php
                        $dc = ['active'=>'green','on_trip'=>'blue','off_duty'=>'amber','inactive'=>'gray'][$d->status] ?? 'gray';
                        $dl = ['active'=>'Aktif','on_trip'=>'Dalam Trip','off_duty'=>'Libur','inactive'=>'Nonaktif'][$d->status] ?? $d->status;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $d->name }}
                            @if($d->employee) <span class="text-xs text-gray-400">({{ $d->employee->employee_id ?? '' }})</span> @endif
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-700 dark:text-slate-300 font-mono text-xs">{{ $d->license_number ?? '-' }}</td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell text-gray-700 dark:text-slate-300">{{ $d->license_type ?? '-' }}</td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500 dark:text-slate-400">{{ $d->phone ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $dc }}-100 text-{{ $dc }}-700 dark:bg-{{ $dc }}-500/20 dark:text-{{ $dc }}-400">{{ $dl }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @canmodule('fleet', 'delete')
                            <form method="POST" action="{{ url('fleet/drivers') }}/{{ $d->id }}" class="inline" onsubmit="return confirm('Hapus driver ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                            </form>
                            @endcanmodule
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada driver.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($drivers->hasPages())<div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $drivers->links() }}</div>@endif
    </div>

    {{-- Modal Add Driver --}}
    <div id="modal-add-driver" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Driver</h3>
                <button onclick="document.getElementById('modal-add-driver').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('fleet.drivers.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label><input type="text" name="name" required class="{{ $cls }}"></div>
                    @if($employees->isNotEmpty())
                    <div class="col-span-2"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Link Karyawan</label>
                        <select name="employee_id" class="{{ $cls }}"><option value="">-- Tanpa Link --</option>
                            @foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }} ({{ $e->employee_id }})</option>@endforeach
                        </select>
                    </div>
                    @endif
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">No. SIM</label><input type="text" name="license_number" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe SIM</label>
                        <select name="license_type" class="{{ $cls }}"><option value="">-</option><option value="A">A</option><option value="B1">B1</option><option value="B2">B2</option><option value="C">C</option></select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">SIM Expired</label><input type="date" name="license_expiry" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Telepon</label><input type="text" name="phone" class="{{ $cls }}"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-driver').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
