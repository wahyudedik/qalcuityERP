<x-app-layout>
    <x-slot name="header">Okupansi Tempat Tidur</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        @php
            $totalBeds = \App\Models\Bed::where('tenant_id', $tid)->count();
            $availableBeds = \App\Models\Bed::where('tenant_id', $tid)->where('status', 'available')->count();
            $occupiedBeds = \App\Models\Bed::where('tenant_id', $tid)->where('status', 'occupied')->count();
            $maintenanceBeds = \App\Models\Bed::where('tenant_id', $tid)->where('status', 'maintenance')->count();
            $reservedBeds = \App\Models\Bed::where('tenant_id', $tid)->where('status', 'reserved')->count();
            $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0;
        @endphp
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Bed</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalBeds }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Tersedia</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $availableBeds }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Terisi</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $occupiedBeds }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Maintenance</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $maintenanceBeds }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Okupansi</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $occupancyRate }}%</p>
        </div>
    </div>

    {{-- Occupancy Rate Bar --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-900">Tingkat Okupansi Keseluruhan</h3>
            <span class="text-2xl font-bold text-blue-600">{{ $occupancyRate }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
            <div class="h-full transition-all duration-500 rounded-full
                @if ($occupancyRate >= 90) bg-red-500
                @elseif($occupancyRate >= 70) bg-amber-500
                @else bg-green-500 @endif"
                style="width: {{ $occupancyRate }}%"></div>
        </div>
        <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
            <span>0%</span>
            <span>50%</span>
            <span>100%</span>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <select name="ward"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Ruang</option>
                    @php
                        $wards = \App\Models\Ward::where('tenant_id', $tid)->get();
                    @endphp
                    @foreach ($wards as $ward)
                        <option value="{{ $ward->id }}" @selected(request('ward') == $ward->id)>{{ $ward->name }}</option>
                    @endforeach
                </select>
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="available" @selected(request('status') === 'available')>Tersedia</option>
                    <option value="occupied" @selected(request('status') === 'occupied')>Terisi</option>
                    <option value="maintenance" @selected(request('status') === 'maintenance')>Maintenance</option>
                    <option value="reserved" @selected(request('status') === 'reserved')>Direservasi</option>
                </select>
                <select name="ward_type"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Tipe</option>
                    <option value="VIP" @selected(request('ward_type') === 'VIP')>VIP</option>
                    <option value="Kelas 1" @selected(request('ward_type') === 'Kelas 1')>Kelas 1</option>
                    <option value="Kelas 2" @selected(request('ward_type') === 'Kelas 2')>Kelas 2</option>
                    <option value="Kelas 3" @selected(request('ward_type') === 'Kelas 3')>Kelas 3</option>
                    <option value="ICU" @selected(request('ward_type') === 'ICU')>ICU</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            </form>
        </div>
    </div>

    {{-- Beds Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Bed</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Ruang</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Tipe</th>
                        @if (request('status') === 'occupied' || !request('status'))
                            <th class="px-4 py-3 text-left hidden md:table-cell">Pasien</th>
                        @endif
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($beds ?? [] as $bed)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl 
                                        @if ($bed->status === 'available') bg-green-100
                                        @elseif($bed->status === 'occupied') bg-red-100
                                        @elseif($bed->status === 'maintenance') bg-amber-100
                                        @else bg-gray-100 @endif flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 
                                            @if ($bed->status === 'available') text-green-600
                                            @elseif($bed->status === 'occupied') text-red-600
                                            @elseif($bed->status === 'maintenance') text-amber-600
                                            @else text-gray-500 @endif"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                            </path>
                                        </svg>
                                    </div>
                                    <span
                                        class="font-semibold text-gray-900">{{ $bed->bed_number }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-900">{{ $bed->ward ? $bed->ward?->name : '-' }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $bed->ward ? $bed->ward?->floor : '' }}</p>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700">
                                    {{ $bed->ward ? $bed->ward?->ward_type : '-' }}
                                </span>
                            </td>
                            @if (request('status') === 'occupied' || !request('status'))
                                <td class="px-4 py-3 hidden md:table-cell">
                                    @if ($bed->status === 'occupied' && $bed->admission)
                                        <p class="text-gray-900 font-medium">
                                            {{ $bed->admission?->patient ? $bed->admission?->patient->full_name : '-' }}
                                        </p>
                                        <p class="text-xs text-gray-500">Sejak
                                            {{ $bed->admission?->admission_date ? \Carbon\Carbon::parse($bed->admission?->admission_date)->format('d M') : '-' }}
                                        </p>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            @endif
                            <td class="px-4 py-3 text-center">
                                @if ($bed->status === 'available')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Tersedia</span>
                                @elseif($bed->status === 'occupied')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Terisi</span>
                                @elseif($bed->status === 'maintenance')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">Maintenance</span>
                                @elseif($bed->status === 'reserved')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">Direservasi</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">{{ $bed->status }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if ($bed->status === 'available')
                                        <a href="{{ route('healthcare.inpatient.admissions.create', ['bed_id' => $bed->id]) }}"
                                            class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg"
                                            title="Terima Pasien">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                                                </path>
                                            </svg>
                                        </a>
                                    @elseif($bed->status === 'occupied' && $bed->admission)
                                        <a href="{{ route('healthcare.inpatient.admissions.show', $bed->admission) }}"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
                                            title="Detail Pasien">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </a>
                                    @endif
                                    <button onclick="updateBedStatus({{ $bed->id }})"
                                        class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg"
                                        title="Update Status">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                    </path>
                                </svg>
                                <p>Belum ada data tempat tidur</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if (isset($beds) && $beds->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $beds->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function updateBedStatus(bedId) {
                // Implement bed status update modal
                alert('Update status bed ID: ' + bedId);
            }
        </script>
    @endpush
</x-app-layout>
