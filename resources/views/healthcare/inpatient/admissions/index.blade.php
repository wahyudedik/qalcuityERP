<x-app-layout>
    <x-slot name="header">Daftar Pasien Rawat Inap</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
            $totalAdmissions = \App\Models\Admission::where('tenant_id', $tid)->count();
            $currentAdmissions = \App\Models\Admission::where('tenant_id', $tid)->where('status', 'admitted')->count();
            $todayAdmissions = \App\Models\Admission::where('tenant_id', $tid)
                ->whereDate('admission_date', today())
                ->count();
            $todayDischarges = \App\Models\Admission::where('tenant_id', $tid)
                ->whereDate('discharge_date', today())
                ->count();
        @endphp
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Rawat Inap</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalAdmissions) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Sedang Rawat</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $currentAdmissions }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Masuk Hari Ini</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $todayAdmissions }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Pulang Hari Ini</p>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $todayDischarges }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari pasien / No. RM..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="admitted" @selected(request('status') === 'admitted')>Sedang Rawat</option>
                    <option value="discharged" @selected(request('status') === 'discharged')>Sudah Pulang</option>
                    <option value="transferred" @selected(request('status') === 'transferred')>Dipindahkan</option>
                    <option value="against_medical_advice" @selected(request('status') === 'against_medical_advice')>Pulang Paksa</option>
                </select>
                <select name="ward"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Ruang</option>
                    @php
                        $wards = \App\Models\Ward::where('tenant_id', $tid)->get();
                    @endphp
                    @foreach ($wards as $ward)
                        <option value="{{ $ward->id }}" @selected(request('ward') == $ward->id)>{{ $ward->name }}</option>
                    @endforeach
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('healthcare.inpatient.admissions.create') }}"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Rawat Inap</a>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Ruang / Bed</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Dokter Penanggung Jawab</th>
                        <th class="px-4 py-3 text-left">Tanggal Masuk</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($admissions ?? [] as $admission)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $admission->patient ? $admission->patient->full_name : '-' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400">
                                            {{ $admission->patient ? $admission->patient->medical_record_number : '-' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-900 dark:text-white">
                                    {{ $admission->ward ? $admission->ward->name : '-' }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">Bed
                                    {{ $admission->bed ? $admission->bed->bed_number : '-' }}</p>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <p class="text-gray-900 dark:text-white">
                                    {{ $admission->doctor ? $admission->doctor->name : '-' }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $admission->doctor ? $admission->doctor->specialization : '' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-gray-900 dark:text-white">
                                    {{ $admission->admission_date ? \Carbon\Carbon::parse($admission->admission_date)->format('d M Y') : '-' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $admission->admission_date ? \Carbon\Carbon::parse($admission->admission_date)->format('H:i') : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                @if ($admission->status === 'admitted')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Sedang
                                        Rawat</span>
                                @elseif($admission->status === 'discharged')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Sudah
                                        Pulang</span>
                                @elseif($admission->status === 'transferred')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">Dipindahkan</span>
                                @elseif($admission->status === 'against_medical_advice')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Pulang
                                        Paksa</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('healthcare.inpatient.admissions.show', $admission) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg"
                                        title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                    @if ($admission->status === 'admitted')
                                        <a href="{{ route('healthcare.inpatient.admissions.discharge', $admission) }}"
                                            class="p-1.5 text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/30 rounded-lg"
                                            title="Proses Pulang">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-slate-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                <p>Belum ada data rawat inap</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if (isset($admissions) && $admissions->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $admissions->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
