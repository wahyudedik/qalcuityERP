<x-app-layout>
    <x-slot name="header">Konsultasi Telemedicine</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Telemedicine', 'url' => route('healthcare.telemedicine.index')],
        ['label' => 'Konsultasi'],
    ]" />

    {{-- Stats - Data from Controller (no more queries in Blade) --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Konsultasi</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">
                {{ number_format($statistics['total_consultations'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Terjadwal</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $statistics['scheduled'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Berlangsung</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $statistics['in_progress'] ?? 0 }}
            </p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Selesai Hari Ini</p>
            <p class="text-2xl font-bold text-purple-600 mt-1">
                {{ $statistics['completed_today'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari pasien / dokter..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                    <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>
                <input type="date" name="date" value="{{ request('date') }}"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            </form>
        </div>
    </div>

    {{-- Consultations List --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @forelse($consultations ?? [] as $consultation)
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 mb-1">
                                {{ $consultation->patient ? $consultation->patient?->full_name : '-' }}</h3>
                            <p class="text-sm text-gray-500">Dokter:
                                {{ $consultation->doctor ? $consultation->doctor?->name : '-' }}</p>
                        </div>
                        <div>
                            @if ($consultation->status === 'scheduled')
                                <span
                                    class="px-3 py-1 text-xs font-bold bg-blue-500 text-white rounded-lg">Scheduled</span>
                            @elseif($consultation->status === 'in_progress')
                                <span class="px-3 py-1 text-xs font-bold bg-green-500 text-white rounded-lg">In
                                    Progress</span>
                            @elseif($consultation->status === 'completed')
                                <span
                                    class="px-3 py-1 text-xs font-bold bg-purple-500 text-white rounded-lg">Completed</span>
                            @elseif($consultation->status === 'cancelled')
                                <span
                                    class="px-3 py-1 text-xs font-bold bg-red-500 text-white rounded-lg">Cancelled</span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-2 mb-4">
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span>{{ $consultation->scheduled_time ? $consultation->scheduled_time->format('d M Y H:i') : '-' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z">
                                </path>
                            </svg>
                            <span>{{ $consultation->chief_complaint ?? 'Konsultasi Umum' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span>Video Call</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if (
                            $consultation->status === 'in_progress' ||
                                ($consultation->status === 'scheduled' &&
                                    $consultation->scheduled_time &&
                                    $consultation->scheduled_time->isPast()))
                            <a href="{{ route('healthcare.telemedicine.video-room', $consultation) }}"
                                class="flex-1 px-4 py-2.5 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 font-medium text-center flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                    </path>
                                </svg>
                                Join Call
                            </a>
                        @endif
                        <a href="{{ route('healthcare.telemedicine.consultations.show', $consultation) }}"
                            class="px-4 py-2.5 text-sm border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50">
                            Detail
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div
                class="col-span-full bg-white rounded-2xl border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                    </path>
                </svg>
                <p class="text-gray-500">Belum ada konsultasi</p>
            </div>
        @endforelse
    </div>

    @if (isset($consultations) && $consultations->hasPages())
        <div class="mt-6">
            {{ $consultations->links() }}
        </div>
    @endif
</x-app-layout>
