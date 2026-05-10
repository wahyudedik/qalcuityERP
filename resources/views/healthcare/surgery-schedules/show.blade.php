<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-procedures mr-2"></i>Detail Jadwal Operasi - {{ $schedule->surgery_number }}
            </h2>
            <span
                class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                @if ($schedule->status === 'scheduled') bg-yellow-100 text-yellow-800
                @elseif($schedule->status === 'in_progress') bg-red-100 text-red-800
                @elseif($schedule->status === 'completed') bg-green-100 text-green-800
                @elseif($schedule->status === 'cancelled') bg-gray-100 text-gray-800
                @else bg-gray-100 text-gray-800 @endif">
                {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Action Buttons --}}
            <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
                <a href="{{ route('healthcare.surgery-schedules.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                </a>
                <a href="{{ route('healthcare.surgery-schedules.edit', $schedule) }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Patient Information --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-user text-blue-500 mr-2"></i>Informasi Pasien
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Nama Pasien</p>
                            <p class="text-sm text-gray-900">{{ $schedule->patient?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">No. Rekam Medis</p>
                            <p class="text-sm text-gray-900">{{ $schedule->patient?->medical_record_number ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tanggal Lahir</p>
                            <p class="text-sm text-gray-900">
                                {{ $schedule->patient?->date_of_birth ? \Carbon\Carbon::parse($schedule->patient->date_of_birth)->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Jenis Kelamin</p>
                            <p class="text-sm text-gray-900">{{ $schedule->patient?->gender ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Surgeon Information --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-user-md text-green-500 mr-2"></i>Informasi Dokter Bedah
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Nama Dokter</p>
                            <p class="text-sm text-gray-900">{{ $schedule->surgeon?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Spesialisasi</p>
                            <p class="text-sm text-gray-900">{{ $schedule->surgeon?->specialization ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">No. Telepon</p>
                            <p class="text-sm text-gray-900">{{ $schedule->surgeon?->phone ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Operating Room Details --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-hospital text-purple-500 mr-2"></i>Ruang Operasi
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Nama Ruangan</p>
                            <p class="text-sm text-gray-900">{{ $schedule->operatingRoom?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Nomor Ruangan</p>
                            <p class="text-sm text-gray-900">{{ $schedule->operatingRoom?->room_number ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Lantai</p>
                            <p class="text-sm text-gray-900">{{ $schedule->operatingRoom?->floor ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Schedule Details --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-calendar-alt text-indigo-500 mr-2"></i>Detail Jadwal
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tanggal Operasi</p>
                            <p class="text-sm text-gray-900">
                                {{ $schedule->scheduled_date ? \Carbon\Carbon::parse($schedule->scheduled_date)->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Waktu Mulai</p>
                            <p class="text-sm text-gray-900">
                                {{ $schedule->start_time ?? ($schedule->scheduled_start_time ?? '-') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Estimasi Durasi</p>
                            <p class="text-sm text-gray-900">
                                {{ $schedule->estimated_duration ? $schedule->estimated_duration . ' menit' : '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Jenis Operasi</p>
                            <p class="text-sm text-gray-900">{{ $schedule->surgery_type ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Prioritas</p>
                            <p class="text-sm text-gray-900">{{ ucfirst($schedule->priority ?? '-') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Diagnosis --}}
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-stethoscope text-red-500 mr-2"></i>Diagnosis
                </h3>
                <p class="text-sm text-gray-900 whitespace-pre-line">
                    {{ $schedule->diagnosis ?? ($schedule->pre_operative_diagnosis ?? 'Tidak ada data diagnosis.') }}</p>
            </div>

            {{-- Procedure Notes --}}
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-clipboard-list text-orange-500 mr-2"></i>Catatan Prosedur
                </h3>
                <p class="text-sm text-gray-900 whitespace-pre-line">
                    {{ $schedule->procedure_notes ?? ($schedule->procedure_description ?? 'Tidak ada catatan prosedur.') }}
                </p>
            </div>

            {{-- Surgery Team --}}
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-users text-teal-500 mr-2"></i>Tim Operasi
                </h3>
                @if (count($schedule->surgeryTeam?->toArray() ?? []) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Peran
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($schedule->surgeryTeam ?? [] as $member)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            {{ $member->name ?? ($member->staff_name ?? '-') }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $member->role ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Belum ada tim operasi yang ditugaskan.</p>
                @endif
            </div>

            {{-- Equipment --}}
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-tools text-gray-600 mr-2"></i>Peralatan
                </h3>
                @if (count($schedule->equipment?->toArray() ?? []) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama
                                        Peralatan</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jumlah
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($schedule->equipment ?? [] as $item)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            {{ $item->name ?? ($item->equipment_name ?? '-') }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $item->quantity ?? '-' }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $item->status ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Belum ada peralatan yang ditugaskan.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
