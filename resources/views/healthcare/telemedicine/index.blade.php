<x-app-layout>
    <x-slot name="header">{{ __('Telemedicine Consultations') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.telemedicine.consultations') }}"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
            <i class="fas fa-list mr-2"></i>Daftar Konsultasi
        </a>
        <a href="{{ route('healthcare.telemedicine.settings.index') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
            <i class="fas fa-cog mr-2"></i>Pengaturan
        </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <i class="fas fa-clock text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Terjadwal</p>
                            <p class="text-2xl font-semibold text-yellow-600">
                                {{ $statistics['scheduled'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <i class="fas fa-video text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Berlangsung</p>
                            <p class="text-2xl font-semibold text-blue-600">
                                {{ $statistics['in_progress'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <i class="fas fa-check-circle text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Selesai</p>
                            <p class="text-2xl font-semibold text-green-600">
                                {{ $statistics['completed'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <i class="fas fa-laptop-medical text-white text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total</p>
                            <p class="text-2xl font-semibold text-purple-600">
                                {{ $statistics['total'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Telemedicine Table -->
            <div
                class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No. Konsultasi</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pasien</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dokter</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipe</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jadwal</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($consultations ?? [] as $consultation)
                                <tr class="hover:bg-gray-50">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $consultation->consultation_number ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $consultation->patient?->full_name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $consultation->doctor?->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ ucfirst($consultation->consultation_type ?? ($consultation->platform ?? '-')) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $consultation->scheduled_time ? $consultation->scheduled_time->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if ($consultation->status === 'scheduled') bg-yellow-100 text-yellow-800
                                        @elseif($consultation->status === 'in_progress') bg-blue-100 text-blue-800
                                        @elseif($consultation->status === 'completed') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $consultation->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('healthcare.telemedicine.consultations.show', $consultation) }}"
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if ($consultation->status === 'scheduled')
                                            <a href="{{ route('healthcare.telemedicine.video-room', $consultation) }}"
                                                class="text-green-600 hover:text-green-900 mr-3"
                                                title="Join Call">
                                                <i class="fas fa-video"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        Belum ada konsultasi telemedicine</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (isset($consultations) && $consultations->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $consultations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
