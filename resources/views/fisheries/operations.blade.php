<x-app-layout>
    <x-slot name="header">⚓ Fishing Operations</x-slot>

    @if (session('success'))
        <div
            class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            {{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Kapal</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['total_vessels'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Trip Aktif</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $stats['active_trips'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Trip Hari Ini</p>
            <p class="text-2xl font-bold text-cyan-600">{{ $stats['trips_today'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Tangkapan</p>
            <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_catch_weight'] ?? 0, 1) }} kg
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Estimasi Nilai</p>
            <p class="text-2xl font-bold text-orange-600">Rp
                {{ number_format($stats['total_estimated_value'] ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filter + Actions --}}
    <div class="flex items-center justify-between mb-4">
        <form class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari trip..."
                class="px-3 py-2 text-sm rounded-lg border border-gray-200 bg-white text-gray-900 w-48">
            <select name="status" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                @foreach (['planned' => 'Direncanakan', 'departed' => 'Berangkat', 'fishing' => 'Menangkap', 'returning' => 'Pulang', 'completed' => 'Selesai'] as $v => $l)
                    <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </form>
        <button onclick="document.getElementById('newTripModal').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition flex items-center gap-2">
            <span>🚢</span> Buat Trip Baru
        </button>
    </div>

    {{-- Trips List --}}
    @if (empty($trips) || count($trips) === 0)
        <div
            class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
            <p class="text-4xl mb-3">⚓</p>
            <p class="text-sm text-gray-500">Belum ada trip penangkapan. Buat trip pertama Anda.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($trips as $trip)
                @php
                    $statusColors = [
                        'planned' => 'gray',
                        'departed' => 'blue',
                        'fishing' => 'emerald',
                        'returning' => 'yellow',
                        'completed' => 'green',
                        'cancelled' => 'red',
                    ];
                    $statusLabels = [
                        'planned' => 'Direncanakan',
                        'departed' => 'Berangkat',
                        'fishing' => 'Menangkap',
                        'returning' => 'Pulang',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ];
                    $color = $statusColors[$trip->status] ?? 'gray';
                    $label = $statusLabels[$trip->status] ?? $trip->status;
                @endphp
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition"
                    x-data="{ showCatchForm: false }">

                    {{-- Header --}}
                    <div
                        class="px-5 py-4 flex items-start justify-between border-b border-gray-100">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <span
                                    class="text-lg font-bold text-gray-900">{{ $trip->trip_number }}</span>
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full bg-{{ $color  }}-100 text-{{ $color }}-700 $color }}-500/20 $color }}-400">
                                    {{ $label }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                🚢 {{ $trip->vessel?->name ?? 'N/A' }} | 👨‍✈️ {{ $trip->captain?->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Berangkat</p>
                            <p class="text-sm font-medium text-gray-700">
                                {{ $trip->departure_time ? $trip->departure_time->format('d M Y, H:i') : '-' }}
                            </p>
                        </div>
                    </div>

                    {{-- Trip Details --}}
                    <div class="px-5 py-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            @if ($trip->fishing_zone)
                                <div>
                                    <span class="text-gray-400 text-xs block">Zona Penangkapan</span>
                                    <span
                                        class="text-gray-700 font-medium">{{ $trip->fishing_zone?->name ?? $trip->fishing_zone }}</span>
                                </div>
                            @endif
                            @if ($trip->expected_return)
                                <div>
                                    <span class="text-gray-400 text-xs block">Kembali (Rencana)</span>
                                    <span
                                        class="text-gray-700 font-medium">{{ $trip->expected_return->format('d M Y, H:i') }}</span>
                                </div>
                            @endif
                            <div>
                                <span class="text-gray-400 text-xs block">Total Tangkapan</span>
                                <span
                                    class="text-emerald-600 font-bold">{{ number_format($trip->total_catch_weight, 1) }}
                                    kg</span>
                            </div>
                            <div>
                                <span class="text-gray-400 text-xs block">Estimasi Nilai</span>
                                <span class="text-orange-600 font-bold">Rp
                                    {{ number_format($trip->estimated_value, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Crew Info --}}
                        @if ($trip->crew_count > 0)
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <span class="text-xs text-gray-400">Awak Kapal:</span>
                                <span class="text-sm text-gray-700 ml-1">{{ $trip->crew_count }}
                                    orang</span>
                            </div>
                        @endif

                        {{-- Catch Recording Form --}}
                        <div x-show="showCatchForm" x-transition
                            class="mt-4 p-4 bg-emerald-50 rounded-lg border border-emerald-200">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">🐟 Catat Tangkapan</h4>
                            <form :action="'{{ route('fisheries.operations.record-catch', $trip->id) }}'"
                                method="POST" class="space-y-3">
                                @csrf
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1">Spesies
                                            *</label>
                                        <select name="species_id" required
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                            <option value="">Pilih Spesies</option>
                                            @foreach ($species_list ?? [] as $sp)
                                                <option value="{{ $sp->id }}">{{ $sp->common_name }}
                                                    ({{ $sp->scientific_name }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1">Jumlah
                                            (ekor)</label>
                                        <input type="number" name="quantity" required step="1" min="0"
                                            placeholder="100"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1">Berat
                                            Total (kg) *</label>
                                        <input type="number" name="total_weight" required step="0.01" min="0"
                                            placeholder="250.5"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1">Grade
                                            Kualitas</label>
                                        <select name="grade_id"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                            <option value="">Pilih Grade</option>
                                            @foreach ($grades ?? [] as $grade)
                                                <option value="{{ $grade->id }}">{{ $grade->grade_code }} -
                                                    {{ $grade->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 mb-1">Skor
                                            Kesegaran (0-10)</label>
                                        <input type="number" name="freshness_score" step="0.1" min="0"
                                            max="10" placeholder="8.5"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit"
                                        class="flex-1 px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                                        💾 Simpan Tangkapan
                                    </button>
                                    <button type="button" @click="showCatchForm = false"
                                        class="px-3 py-1.5 text-xs bg-gray-200 text-gray-700 rounded-lg transition">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Footer Actions --}}
                    <div
                        class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex gap-2">
                            @if ($trip->status === 'planned')
                                <form :action="'{{ route('fisheries.operations.depart-trip', $trip->id) }}'"
                                    method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                        🚀 Berangkat
                                    </button>
                                </form>
                            @endif

                            @if (in_array($trip->status, ['departed', 'fishing']))
                                <button @click="showCatchForm = !showCatchForm"
                                    class="px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                                    🐟 Catat Tangkapan
                                </button>
                            @endif

                            @if (in_array($trip->status, ['fishing', 'returning']))
                                <form :action="'{{ route('fisheries.operations.complete-trip', $trip->id) }}'"
                                    method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="px-3 py-1.5 text-xs bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                                        ✅ Selesai
                                    </button>
                                </form>
                            @endif
                        </div>
                        <a href="{{ route('fisheries.operations.show', $trip->id) }}"
                            class="text-blue-600 hover:underline text-sm">
                            Detail Lengkap →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $trips->links() }}</div>
    @endif

    {{-- New Trip Modal --}}
    <div id="newTripModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">🚢 Buat Trip Penangkapan Baru</h3>
                <button onclick="document.getElementById('newTripModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('fisheries.operations.plan-trip') }}" class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; @endphp

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kapal *</label>
                        <select name="vessel_id" required class="{{ $cls }}">
                            <option value="">Pilih Kapal</option>
                            @foreach ($vessels ?? [] as $vessel)
                                <option value="{{ $vessel->id }}">{{ $vessel->name }}
                                    ({{ $vessel->registration_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nakhoda
                            *</label>
                        <select name="captain_id" required class="{{ $cls }}">
                            <option value="">Pilih Nakhoda</option>
                            @foreach ($captains ?? [] as $captain)
                                <option value="{{ $captain->id }}">{{ $captain->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Waktu Berangkat
                            *</label>
                        <input type="datetime-local" name="departure_time" required class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kembali
                            (Rencana)</label>
                        <input type="datetime-local" name="expected_return" class="{{ $cls }}">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Zona
                        Penangkapan</label>
                    <select name="fishing_zone_id" class="{{ $cls }}">
                        <option value="">Pilih Zona</option>
                        @foreach ($zones ?? [] as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }} - {{ $zone->location }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Awak Kapal (IDs,
                        pisahkan dengan koma)</label>
                    <input type="text" name="crew_ids" placeholder="1,2,3,4" class="{{ $cls }}">
                    <p class="text-xs text-gray-500 mt-1">Masukkan ID anggota awak kapal</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Target spesies, strategi, dll." class="{{ $cls }}"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                        🚀 Buat Trip
                    </button>
                    <button type="button" onclick="document.getElementById('newTripModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
