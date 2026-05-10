<x-app-layout>
    <x-slot name="header">Rekam Medis Elektronik (EMR)</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Rekam Medis'],
    ]" />

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Rekam Medis Elektronik</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar seluruh rekam medis pasien</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('healthcare.emr.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="patient_id" value="{{ request('patient_id') }}"
                    placeholder="Filter by Patient ID"
                    class="w-full rounded-xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex-1">
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full rounded-xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Dari tanggal">
            </div>
            <div class="flex-1">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="w-full rounded-xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Sampai tanggal">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 transition">
                    Filter
                </button>
                <a href="{{ route('healthcare.emr.index') }}"
                    class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-200 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Records Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Daftar Rekam Medis</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left">Dokter</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Keluhan Utama</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($records as $record)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">
                                    {{ $record->created_at ? $record->created_at->format('d M Y') : '-' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $record->created_at ? $record->created_at->format('H:i') : '' }}
                                </p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $record->patient?->full_name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $record->patient?->medical_record_number ?? '' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $record->doctor?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 hidden md:table-cell max-w-xs truncate">
                                {{ $record->chief_complaint ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                @php $status = $record->status ?? 'draft'; @endphp
                                @if ($status === 'completed')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Selesai</span>
                                @elseif($status === 'amended')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-yellow-100 text-yellow-700">Diubah</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($record->patient)
                                    <a href="{{ route('healthcare.emr.show', $record->patient->id) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg inline-flex"
                                        title="Lihat Rekam Medis Pasien">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
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

        @if ($records->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
