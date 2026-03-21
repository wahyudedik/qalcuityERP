<x-app-layout>
    <x-slot name="header">Absensi Karyawan</x-slot>

    {{-- Date Picker --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-6">
        <form method="GET" class="flex items-center gap-2">
            <label class="text-sm text-gray-600 dark:text-slate-400">Tanggal:</label>
            <input type="date" name="date" value="{{ $date->format('Y-m-d') }}"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tampilkan</button>
        </form>
        <a href="{{ route('hrm.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">← Kembali ke Karyawan</a>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        @foreach(['present'=>['Hadir','green'],'late'=>['Terlambat','yellow'],'leave'=>['Izin','blue'],'sick'=>['Sakit','orange'],'absent'=>['Absen','red']] as $key=>[$label,$color])
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-3 border border-gray-200 dark:border-white/10 text-center">
            <p class="text-xs text-gray-500 dark:text-slate-400">{{ $label }}</p>
            <p class="text-xl font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400 mt-1">{{ $summary[$key] ?? 0 }}</p>
        </div>
        @endforeach
    </div>

    {{-- Form Absensi --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10">
            <h3 class="font-semibold text-gray-900 dark:text-white">Absensi {{ $date->format('d F Y') }}</h3>
        </div>
        <form method="POST" action="{{ route('hrm.attendance.store') }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Karyawan</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Jabatan</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($employees as $i => $emp)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <input type="hidden" name="records[{{ $i }}][employee_id]" value="{{ $emp->id }}">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $emp->name }}</p>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-500 dark:text-slate-400">{{ $emp->position ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <select name="records[{{ $i }}][status]" class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @foreach(['present'=>'Hadir','late'=>'Terlambat','leave'=>'Izin','sick'=>'Sakit','absent'=>'Absen'] as $val=>$lbl)
                                    <option value="{{ $val }}" @selected(($attendances[$emp->id] ?? 'present') === $val)>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <input type="text" name="records[{{ $i }}][notes]" placeholder="Keterangan..."
                                    class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-gray-400 dark:text-slate-500">Belum ada karyawan aktif.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($employees->count() > 0)
            <div class="px-5 py-4 border-t border-gray-100 dark:border-white/10 flex justify-end">
                <button type="submit" class="px-6 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan Absensi</button>
            </div>
            @endif
        </form>
    </div>
</x-app-layout>
