<x-app-layout title="Rawat Inap Dashboard">
    <x-slot name="header">Rawat Inap</x-slot>

    <x-slot name="pageTitle">Dashboard Rawat Inap</x-slot>

    {{-- Statistics --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Pasien Aktif</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $statistics['total_active'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Masuk Hari Ini</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $statistics['today_admissions'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Keluar Hari Ini</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $statistics['today_discharges'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Menunggu Discharge</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $statistics['pending_discharge'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Rata-rata LOS</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">
                {{ number_format($statistics['avg_length_of_stay'] ?? 0, 1) }} hari</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Recent Admissions --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Admisi Terbaru</h3>
            </div>
            @if ($recentAdmissions->isEmpty())
                <div class="p-6 text-center text-gray-500 text-sm">Tidak ada data</div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach ($recentAdmissions as $admission)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-900">
                                    {{ $admission->patient?->full_name ?? ($admission->admission_number ?? '-') }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $admission->bed?->ward?->name ?? '-' }} &middot;
                                    {{ $admission->admission_date ? \Carbon\Carbon::parse($admission->admission_date)->format('d M Y') : '-' }}
                                </p>
                            </div>
                            @php
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-700',
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                    'discharged' => 'bg-gray-100 text-gray-700',
                                    'transferred' => 'bg-blue-100 text-blue-700',
                                    'pending_discharge' => 'bg-orange-100 text-orange-700',
                                ];
                            @endphp
                            <span
                                class="text-xs px-2 py-0.5 rounded-full {{ $statusColors[$admission->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst(str_replace('_', ' ', $admission->status ?? 'unknown')) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Pending Discharges --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Menunggu Discharge</h3>
            </div>
            @if ($pendingDischarges->isEmpty())
                <div class="p-6 text-center text-gray-500 text-sm">Tidak ada pasien menunggu discharge</div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach ($pendingDischarges as $admission)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-900">{{ $admission->patient?->full_name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $admission->bed?->ward?->name ?? '-' }} &middot;
                                    Masuk:
                                    {{ $admission->admission_date ? \Carbon\Carbon::parse($admission->admission_date)->format('d M Y') : '-' }}
                                </p>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">
                                Pending
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
