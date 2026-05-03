<x-app-layout>
    <x-slot name="header">🐠 Pond Detail - {{ $pond->code }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('fisheries.aquaculture.index') }}"
                class="px-3 py-1.5 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                ← Kembali
            </a>
    </div>

    @if (session('success'))
        <div
            class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            {{ session('success') }}</div>
    @endif

    {{-- Pond Info Card --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $pond->code }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $pond->name }}</p>
                @if ($pond->location)
                    <p class="text-xs text-gray-400 mt-1">📍 {{ $pond->location }}</p>
                @endif
            </div>
            @php
                $statusColors = [
                    'active' => 'emerald',
                    'preparing' => 'blue',
                    'resting' => 'gray',
                    'maintenance' => 'yellow',
                ];
                $color = $statusColors[$pond->status] ?? 'gray';
            @endphp
            <span
                class="px-3 py-1 text-sm rounded-full bg-{{ $color }}-100 text-{{ $color }}-700 $color }}-500/20 $color }}-400">
                {{ $pond->status_label }}
            </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
            <div>
                <p class="text-xs text-gray-500">Luas Area</p>
                <p class="text-lg font-medium text-gray-900">{{ number_format($pond->area_size, 1) }}
                    m²</p>
            </div>
            @if ($pond->depth)
                <div>
                    <p class="text-xs text-gray-500">Kedalaman</p>
                    <p class="text-lg font-medium text-gray-900">{{ number_format($pond->depth, 1) }} m
                    </p>
                </div>
            @endif
            @if ($pond->pond_type)
                <div>
                    <p class="text-xs text-gray-500">Tipe Kolam</p>
                    <p class="text-lg font-medium text-gray-900">
                        {{ ucfirst(str_replace('_', ' ', $pond->pond_type)) }}</p>
                </div>
            @endif
            @if ($pond->water_source)
                <div>
                    <p class="text-xs text-gray-500">Sumber Air</p>
                    <p class="text-lg font-medium text-gray-900">{{ ucfirst($pond->water_source) }}</p>
                </div>
            @endif
        </div>

        {{-- Current Stock --}}
        @if ($pond->current_stock_species)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-gray-700">Stok Saat Ini</p>
                    <span class="text-xs text-gray-500">Sejak
                        {{ $pond->stocked_at?->format('d M Y') ?? '-' }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🐟</span>
                    <div>
                        <p class="text-base font-bold text-gray-900">{{ $pond->current_stock_species }}
                        </p>
                        <p class="text-sm text-gray-600">
                            {{ number_format($pond->current_stock_count, 0) }} ekor</p>
                    </div>
                </div>
                @if ($pond->utilization_percentage)
                    <div class="mt-3">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-500">Utilisasi Kapasitas</span>
                            <span
                                class="font-medium text-gray-700">{{ number_format($pond->utilization_percentage, 1) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-cyan-600 h-2 rounded-full transition-all"
                                style="width: {{ min($pond->utilization_percentage, 100) }}%"></div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Latest Water Quality --}}
    @if ($pond->latestWaterQuality)
        <div
            class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl border border-blue-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-900">💧 Kualitas Air Terakhir</h3>
                <span
                    class="text-xs text-gray-500">{{ $pond->latestWaterQuality->logged_at->format('d M Y, H:i') }}</span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="text-center">
                    <p class="text-xs text-gray-500 mb-1">pH</p>
                    <p
                        class="text-2xl font-bold {{ $pond->latestWaterQuality->ph >= 6.5 && $pond->latestWaterQuality->ph <= 8.5 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($pond->latestWaterQuality->ph, 1) }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 mb-1">Oksigen (mg/L)</p>
                    <p
                        class="text-2xl font-bold {{ $pond->latestWaterQuality->dissolved_oxygen >= 5 ? 'text-green-600' : 'text-yellow-600' }}">
                        {{ number_format($pond->latestWaterQuality->dissolved_oxygen, 1) }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 mb-1">Suhu (°C)</p>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ number_format($pond->latestWaterQuality->temperature ?? 0, 1) }}
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 mb-1">Amonia (mg/L)</p>
                    <p
                        class="text-2xl font-bold {{ ($pond->latestWaterQuality->ammonia ?? 0) <= 0.02 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($pond->latestWaterQuality->ammonia ?? 0, 2) }}
                    </p>
                </div>
                @if ($pond->latestWaterQuality->salinity)
                    <div class="text-center">
                        <p class="text-xs text-gray-500 mb-1">Salinitas (ppt)</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ number_format($pond->latestWaterQuality->salinity, 1) }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Water Quality History --}}
    <div
        class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900">Riwayat Kualitas Air</h2>
            <button onclick="document.getElementById('addWaterQualityModal').classList.remove('hidden')"
                class="px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                ➕ Log Baru
            </button>
        </div>

        @if ($waterQualityLogs->isEmpty())
            <div class="p-12 text-center">
                <p class="text-4xl mb-3">💧</p>
                <p class="text-sm text-gray-500">Belum ada data kualitas air.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Waktu</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                pH</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Oksigen</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Suhu</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Amonia</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($waterQualityLogs as $log)
                            @php
                                $isGood =
                                    $log->ph >= 6.5 &&
                                    $log->ph <= 8.5 &&
                                    $log->dissolved_oxygen >= 5 &&
                                    ($log->ammonia ?? 0) <= 0.02;
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                    {{ $log->logged_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="font-medium {{ $log->ph >= 6.5 && $log->ph <= 8.5 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($log->ph, 1) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                    {{ number_format($log->dissolved_oxygen, 1) }} mg/L
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                    {{ number_format($log->temperature ?? 0, 1) }}°C
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="{{ ($log->ammonia ?? 0) <= 0.02 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($log->ammonia ?? 0, 2) }} mg/L
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-xs rounded-full {{ $isGood ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $isGood ? 'Baik' : 'Perlu Perhatian' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $waterQualityLogs->links() }}
            </div>
        @endif
    </div>

    {{-- Feeding History --}}
    @if ($feedings->total() > 0)
        <div
            class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900">Riwayat Pemberian Pakan</h2>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach ($feedings as $feeding)
                    <div class="px-6 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ number_format($feeding->feed_quantity, 2) }} kg
                                    @if ($feeding->feed_type)
                                        <span class="text-gray-500">-
                                            {{ $feeding->feed_type }}</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $feeding->feeding_time->format('d M Y, H:i') }}
                                </p>
                            </div>
                            @if ($feeding->feed_cost)
                                <div class="text-right">
                                    <p class="text-sm font-bold text-orange-600">Rp
                                        {{ number_format($feeding->feed_cost, 0, ',', '.') }}</p>
                                </div>
                            @endif
                        </div>
                        @if ($feeding->notes)
                            <p class="text-xs text-gray-500 mt-2">{{ $feeding->notes }}</p>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $feedings->links() }}
            </div>
        </div>
    @endif

    {{-- Quick Actions --}}
    <div class="mt-6 flex gap-3">
        <button onclick="document.getElementById('addWaterQualityModal').classList.remove('hidden')"
            class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition flex items-center justify-center gap-2">
            <span>💧</span> Log Kualitas Air
        </button>
        <button onclick="document.getElementById('addFeedingModal').classList.remove('hidden')"
            class="flex-1 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition flex items-center justify-center gap-2">
            <span>🍽️</span> Catat Pakan
        </button>
    </div>

    {{-- Add Water Quality Modal --}}
    <div id="addWaterQualityModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">💧 Log Kualitas Air</h3>
                <button onclick="document.getElementById('addWaterQualityModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('fisheries.aquaculture.log-water-quality', $pond->id) }}"
                class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; @endphp

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">pH *</label>
                        <input type="number" name="ph" required step="0.1" min="0" max="14"
                            placeholder="7.0" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Oksigen
                            Terlarut (mg/L) *</label>
                        <input type="number" name="dissolved_oxygen" required step="0.1" min="0"
                            placeholder="6.5" class="{{ $cls }}">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Suhu Air
                            (°C)</label>
                        <input type="number" name="temperature" step="0.1" placeholder="28.5"
                            class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Amonia
                            (mg/L)</label>
                        <input type="number" name="ammonia" step="0.01" min="0" placeholder="0.02"
                            class="{{ $cls }}">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Salinitas
                        (ppt)</label>
                    <input type="number" name="salinity" step="0.1" min="0" placeholder="15.0"
                        class="{{ $cls }}">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        💾 Simpan
                    </button>
                    <button type="button"
                        onclick="document.getElementById('addWaterQualityModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Feeding Modal --}}
    <div id="addFeedingModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-gray-900">Catat Pemberian Pakan</h2>
                <button onclick="document.getElementById('addFeedingModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('fisheries.aquaculture.log-feeding', $pond->id) }}"
                class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Pakan (kg)
                        *</label>
                    <input type="number" name="feed_quantity" required step="0.01" min="0"
                        placeholder="5.5" class="{{ $cls }}">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Biaya Pakan
                        (Rp)</label>
                    <input type="number" name="feed_cost" step="100" min="0" placeholder="50000"
                        class="{{ $cls }}">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Pakan</label>
                    <input type="text" name="feed_type" placeholder="Pelet 781-2" class="{{ $cls }}">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Waktu pemberian, kondisi ikan, dll."
                        class="{{ $cls }}"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                        💾 Simpan
                    </button>
                    <button type="button"
                        onclick="document.getElementById('addFeedingModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
