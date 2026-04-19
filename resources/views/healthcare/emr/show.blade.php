<x-app-layout>
    <x-slot name="header">Rekam Medis - {{ $patient->full_name }}</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Data Pasien', 'url' => route('healthcare.patients.index')],
        ['label' => $patient->full_name, 'url' => route('healthcare.patients.show', $patient)],
        ['label' => 'Rekam Medis'],
    ]" />

    {{-- Patient Info Banner --}}
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl p-6 mb-6 text-white">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold">{{ $patient->full_name }}</h2>
                    <p class="text-sm text-white/80">
                        RM: {{ $patient->medical_record_number }}
                        @if($patient->birth_date)
                            | {{ $patient->birth_date->age }} tahun
                        @endif
                        | {{ $patient->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('healthcare.emr.dashboard', $patient->id) }}"
                    class="px-4 py-2 text-sm bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl hover:bg-white/30">
                    Dashboard EMR
                </a>
                <a href="{{ route('healthcare.patients.show', $patient) }}"
                    class="px-4 py-2 text-sm bg-white text-blue-600 rounded-xl hover:bg-white/90 font-medium">
                    Profil Pasien
                </a>
            </div>
        </div>
    </div>

    {{-- Medical Records List --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Rekam Medis</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Dokter</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Keluhan</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($records as $record)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $record->created_at ? $record->created_at->format('d M Y') : '-' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $record->created_at ? $record->created_at->format('H:i') : '' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-slate-300">
                                {{ $record->doctor?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-400 hidden md:table-cell max-w-xs truncate">
                                {{ $record->chief_complaint ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                @php $status = $record->status ?? 'completed'; @endphp
                                @if($status === 'completed')
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Selesai</span>
                                @elseif($status === 'in_progress')
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Berlangsung</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{ $status }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('healthcare.emr.history', $record->id) }}"
                                    class="p-1.5 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg inline-flex"
                                    title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <p>Belum ada rekam medis</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
