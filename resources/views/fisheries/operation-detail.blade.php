<x-app-layout>
    <x-slot name="header">⚓ Trip Detail - {{ $trip->trip_number }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('fisheries.operations.index') }}"
                class="px-3 py-1.5 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                ← Kembali
            </a>
    </div>

    @if (session('success'))
        <div
            class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            {{ session('success') }}</div>
    @endif

    {{-- Trip Header Card --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $trip->trip_number }}</h2>
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
                    <span
                        class="px-3 py-1 text-sm rounded-full bg-{{ $color  }}-100 text-{{ $color }}-700 $color }}-500/20 $color }}-400">
                        {{ $label }}
                    </span>
                </div>
                <p class="text-sm text-gray-500">
                    🚢 {{ $trip->vessel?->name ?? 'N/A' }} | 👨‍✈️ {{ $trip->captain?->name ?? 'N/A' }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500">Estimasi Nilai</p>
                <p class="text-2xl font-bold text-orange-600">Rp
                    {{ number_format($trip->estimated_value, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
            <div>
                <p class="text-xs text-gray-500">Waktu Berangkat</p>
                <p class="text-sm font-medium text-gray-900">
                    {{ $trip->departure_time ? $trip->departure_time->format('d M Y, H:i') : '-' }}
                </p>
            </div>
            @if ($trip->actual_return)
                <div>
                    <p class="text-xs text-gray-500">Waktu Kembali</p>
                    <p class="text-sm font-medium text-gray-900">
                        {{ $trip->actual_return->format('d M Y, H:i') }}
                    </p>
                </div>
            @elseif($trip->expected_return)
                <div>
                    <p class="text-xs text-gray-500">Kembali (Rencana)</p>
                    <p class="text-sm font-medium text-gray-900">
                        {{ $trip->expected_return->format('d M Y, H:i') }}
                    </p>
                </div>
            @endif
            @if ($trip->fishing_zone)
                <div>
                    <p class="text-xs text-gray-500">Zona Penangkapan</p>
                    <p class="text-sm font-medium text-gray-900">
                        {{ $trip->fishing_zone?->name ?? $trip->fishing_zone }}</p>
                </div>
            @endif
            <div>
                <p class="text-xs text-gray-500">Durasi</p>
                <p class="text-sm font-medium text-gray-900">
                    @if ($trip->actual_return && $trip->departure_time)
                        {{ $trip->departure_time->diffForHumans($trip->actual_return, true) }}
                    @elseif($trip->departure_time)
                        {{ $trip->departure_time->diffForHumans() }}
                    @else
                        -
                    @endif
                </p>
            </div>
        </div>

        @if ($trip->notes)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-sm text-gray-600">{{ $trip->notes }}</p>
            </div>
        @endif
    </div>

    {{-- Catch Summary Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Tangkapan</p>
            <p class="text-2xl font-bold text-emerald-600">{{ number_format($trip->total_catch_weight, 1) }} kg</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Jumlah Entry</p>
            <p class="text-2xl font-bold text-blue-600">{{ $catches->total() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Spesies Berbeda</p>
            <p class="text-2xl font-bold text-purple-600">{{ $catches->pluck('species_id')->unique()->count() }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Rata-rata Berat/Entry</p>
            <p class="text-2xl font-bold text-cyan-600">
                {{ $catches->total() > 0 ? number_format($trip->total_catch_weight / $catches->total(), 1) : 0 }} kg
            </p>
        </div>
    </div>

    {{-- Catch Details Table --}}
    <div
        class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900">🐟 Detail Tangkapan</h3>
            @if (in_array($trip->status, ['departed', 'fishing']))
                <button onclick="document.getElementById('addCatchModal').classList.remove('hidden')"
                    class="px-3 py-1.5 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                    ➕ Catat Tangkapan
                </button>
            @endif
        </div>

        @if ($catches->isEmpty())
            <div class="p-12 text-center">
                <p class="text-4xl mb-3">🐟</p>
                <p class="text-sm text-gray-500">Belum ada tangkapan tercatat untuk trip ini.</p>
                @if (in_array($trip->status, ['departed', 'fishing']))
                    <button onclick="document.getElementById('addCatchModal').classList.remove('hidden')"
                        class="mt-3 px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                        Catat Tangkapan Pertama
                    </button>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Waktu</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Spesies</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Berat</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Grade</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kesegaran</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($catches as $catch)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                    {{ $catch->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">
                                        {{ $catch->species?->common_name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 italic">
                                        {{ $catch->species?->scientific_name ?? '' }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                    {{ number_format($catch->quantity, 0) }} ekor
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="font-bold text-emerald-600">{{ number_format($catch->total_weight, 1) }}
                                        kg</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($catch->grade)
                                        <span
                                            class="px-2 py-1 text-xs rounded-full bg-{{ $catch->grade?->color ?? 'purple'  }}-100 text-{{ $catch->grade?->color ?? 'purple' }}-700 $catch->grade?->color ?? 'purple' }}-500/20 $catch->grade?->color ?? 'purple' }}-400">
                                            {{ $catch->grade?->grade_code }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($catch->freshness_score)
                                        <div class="flex items-center gap-1">
                                            <span
                                                class="font-medium {{ $catch->freshness_score >= 8 ? 'text-green-600' : ($catch->freshness_score >= 6 ? 'text-yellow-600' : 'text-red-600') }}">
                                                {{ number_format($catch->freshness_score, 1) }}/10
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-bold text-orange-600">Rp
                                        {{ number_format($catch->estimated_value, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $catches->links() }}
            </div>
        @endif
    </div>

    {{-- Crew List --}}
    @if ($trip->crew && count($trip->crew) > 0)
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">👥 Awak Kapal
                ({{ count($trip->crew) }} orang)</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach ($trip->crew as $member)
                    <div class="px-3 py-2 bg-gray-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                        <p class="text-xs text-gray-500">{{ ucfirst($member->role ?? 'crew') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Trip Actions --}}
    <div class="mt-6 flex gap-3">
        @if ($trip->status === 'planned')
            <form action="{{ route('fisheries.operations.depart-trip', $trip->id) }}" method="POST" class="flex-1">
                @csrf
                <button type="submit"
                    class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition flex items-center justify-center gap-2">
                    <span>🚀</span> Berangkat
                </button>
            </form>
        @endif

        @if (in_array($trip->status, ['fishing', 'returning']))
            <form action="{{ route('fisheries.operations.complete-trip', $trip->id) }}" method="POST" class="flex-1">
                @csrf
                <button type="submit"
                    class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl transition flex items-center justify-center gap-2">
                    <span>✅</span> Selesai Trip
                </button>
            </form>
        @endif

        <a href="{{ route('fisheries.operations.index') }}"
            class="px-4 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition flex items-center justify-center gap-2">
            <span>←</span> Kembali
        </a>
    </div>

    {{-- Add Catch Modal --}}
    <div id="addCatchModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">🐟 Catat Tangkapan</h3>
                <button onclick="document.getElementById('addCatchModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('fisheries.operations.record-catch', $trip->id) }}"
                class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; @endphp

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Spesies *</label>
                    <select name="species_id" required class="{{ $cls }}">
                        <option value="">Pilih Spesies</option>
                        @foreach (\App\Models\FishSpecies::where('tenant_id', auth()->user()->tenant_id)->orderBy('common_name')->get() as $sp)
                            <option value="{{ $sp->id }}">{{ $sp->common_name }} ({{ $sp->scientific_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah (ekor)
                            *</label>
                        <input type="number" name="quantity" required step="1" min="0"
                            placeholder="100" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Berat Total
                            (kg) *</label>
                        <input type="number" name="total_weight" required step="0.01" min="0"
                            placeholder="250.5" class="{{ $cls }}">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Grade
                            Kualitas</label>
                        <select name="grade_id" class="{{ $cls }}">
                            <option value="">Pilih Grade</option>
                            @foreach (\App\Models\QualityGrade::where('tenant_id', auth()->user()->tenant_id)->orderBy('grade_code')->get() as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->grade_code }} - {{ $grade->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Skor Kesegaran
                            (0-10)</label>
                        <input type="number" name="freshness_score" step="0.1" min="0" max="10"
                            placeholder="8.5" class="{{ $cls }}">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Lokasi GPS
                        (opsional)</label>
                    <input type="text" name="gps_location" placeholder="-6.2088, 106.8456"
                        class="{{ $cls }}">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Kondisi ikan, metode penangkapan, dll."
                        class="{{ $cls }}"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                        💾 Simpan Tangkapan
                    </button>
                    <button type="button" onclick="document.getElementById('addCatchModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
