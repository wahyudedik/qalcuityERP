<x-app-layout>
    <x-slot name="header">❄️ Cold Storage Detail - {{ $unit->unit_code }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('fisheries.cold-chain.index') }}"
                class="px-3 py-1.5 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                ← Kembali
            </a>
    </div>

    @if (session('success'))
        <div
            class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            {{ session('success') }}</div>
    @endif

    {{-- Unit Info Card --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $unit->unit_code }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $unit->name }}</p>
                @if ($unit->location)
                    <p class="text-xs text-gray-400 mt-1">📍 {{ $unit->location }}</p>
                @endif
            </div>
            <div class="text-right">
                <span
                    class="inline-block px-3 py-1 text-sm rounded-full {{ $unit->isTemperatureSafe() ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $unit->isTemperatureSafe() ? '✅ Suhu Normal' : '⚠️ Warning' }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
            <div>
                <p class="text-xs text-gray-500">Suhu Saat Ini</p>
                <p class="text-3xl font-bold {{ $unit->isTemperatureSafe() ? 'text-green-600' : 'text-red-600' }}">
                    {{ $unit->current_temperature ? number_format($unit->current_temperature, 1) . '°C' : 'N/A' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Range Aman</p>
                <p class="text-lg font-medium text-gray-700">
                    {{ number_format($unit->min_temperature, 1) }}° - {{ number_format($unit->max_temperature, 1) }}°C
                </p>
            </div>
            @if ($unit->capacity_kg)
                <div>
                    <p class="text-xs text-gray-500">Kapasitas</p>
                    <p class="text-lg font-medium text-gray-700">
                        {{ number_format($unit->capacity_kg, 0) }} kg</p>
                </div>
            @endif
            @if ($unit->utilization_percentage)
                <div>
                    <p class="text-xs text-gray-500">Utilisasi</p>
                    <p class="text-lg font-medium text-gray-700">
                        {{ number_format($unit->utilization_percentage, 1) }}%</p>
                </div>
            @endif
        </div>

        @if ($unit->description)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-sm text-gray-600">{{ $unit->description }}</p>
            </div>
        @endif
    </div>

    {{-- Temperature History Chart Placeholder --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-900">Riwayat Suhu (24 Jam Terakhir)</h2>
            <form class="flex items-center gap-2">
                <select name="period" onchange="this.form.submit()"
                    class="px-3 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                    <option value="24h" @selected(request('period', '24h') === '24h')>24 Jam</option>
                    <option value="7d" @selected(request('period') === '7d')>7 Hari</option>
                    <option value="30d" @selected(request('period') === '30d')>30 Hari</option>
                </select>
            </form>
        </div>

        {{-- Chart.js placeholder - can be enhanced with actual chart --}}
        <div
            class="h-64 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl flex items-center justify-center">
            <div class="text-center">
                <p class="text-4xl mb-2">🌡️</p>
                <p class="text-sm text-gray-600">Temperature trend visualization</p>
                <p class="text-xs text-gray-500 mt-1">{{ $temperatureLogs->total() }} readings
                    recorded</p>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-100">
            <div class="text-center">
                <p class="text-xs text-gray-500">Rata-rata</p>
                <p class="text-lg font-bold text-blue-600">
                    {{ $temperatureLogs->avg('temperature') ? number_format($temperatureLogs->avg('temperature'), 1) . '°C' : 'N/A' }}
                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500">Minimum</p>
                <p class="text-lg font-bold text-cyan-600">
                    {{ $temperatureLogs->min('temperature') ? number_format($temperatureLogs->min('temperature'), 1) . '°C' : 'N/A' }}
                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500">Maximum</p>
                <p class="text-lg font-bold text-orange-600">
                    {{ $temperatureLogs->max('temperature') ? number_format($temperatureLogs->max('temperature'), 1) . '°C' : 'N/A' }}
                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-gray-500">Breach Count</p>
                <p class="text-lg font-bold text-red-600">
                    {{ $temperatureLogs->filter(fn($log) => !$log->is_within_range)->count() }}
                </p>
            </div>
        </div>
    </div>

    {{-- Temperature Logs Table --}}
    <div
        class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-900">Log Suhu Detail</h2>
        </div>

        @if ($temperatureLogs->isEmpty())
            <div class="p-12 text-center">
                <p class="text-4xl mb-3">🌡️</p>
                <p class="text-sm text-gray-500">Belum ada data suhu tercatat.</p>
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
                                Suhu</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kelembaban</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Sensor ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($temperatureLogs as $log)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                    {{ $log->logged_at->format('d M Y, H:i:s') }}
                                    <p class="text-xs text-gray-500">
                                        {{ $log->logged_at->diffForHumans() }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="font-bold {{ $log->is_within_range ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($log->temperature, 1) }}°C
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                    {{ $log->humidity ? number_format($log->humidity, 1) . '%' : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-xs rounded-full {{ $log->is_within_range ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $log->is_within_range ? 'Normal' : 'Out of Range' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                    {{ $log->sensor_id ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $temperatureLogs->links() }}
            </div>
        @endif
    </div>

    {{-- Alerts History --}}
    @if ($alerts->total() > 0)
        <div
            class="bg-white rounded-2xl border border-red-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-red-200 bg-red-50">
                <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <span>🚨</span> Alert History ({{ $alerts->total() }})
                </h3>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach ($alerts as $alert)
                    <div class="px-6 py-4 hover:bg-gray-50 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-full bg-{{ $alert->severity === 'critical' ? 'red' : ($alert->severity === 'amber-500' ? 'yellow' : 'blue')  }}-100 text-{{ $alert->severity === 'critical' ? 'red' : ($alert->severity === 'warning' ? 'yellow' : 'blue') }}-700 $alert->severity === 'critical' ? 'red' : ($alert->severity === 'warning' ? 'yellow' : 'blue') }}-500/20 $alert->severity === 'critical' ? 'red' : ($alert->severity === 'warning' ? 'yellow' : 'blue') }}-400">
                                        {{ ucfirst($alert->severity) }}
                                    </span>
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-full {{ $alert->status === 'active' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        {{ ucfirst($alert->status) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-900 font-medium">{{ $alert->message }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Suhu: {{ number_format($alert->current_temperature, 1) }}°C |
                                    Threshold: {{ number_format($alert->threshold_min, 1) }}° -
                                    {{ number_format($alert->threshold_max, 1) }}°C
                                </p>
                            </div>
                            <div class="text-right ml-4">
                                <p class="text-xs text-gray-500">
                                    {{ $alert->created_at->format('d M Y, H:i') }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $alert->created_at->diffForHumans() }}</p>
                                @if ($alert->acknowledged_at)
                                    <p class="text-xs text-green-600 mt-1">
                                        Acknowledged {{ $alert->acknowledged_at->diffForHumans() }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $alerts->links() }}
            </div>
        </div>
    @endif

    {{-- Quick Actions --}}
    <div class="mt-6 flex gap-3">
        <button onclick="document.getElementById('logTempModal').classList.remove('hidden')"
            class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition flex items-center justify-center gap-2">
            <span>🌡️</span> Log Temperature
        </button>
        <a href="{{ route('fisheries.cold-chain.index') }}"
            class="px-4 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition flex items-center justify-center gap-2">
            <span>←</span> Back to List
        </a>
    </div>

    {{-- Log Temperature Modal --}}
    <div id="logTempModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-gray-900">Log Temperature Reading</h2>
                <button onclick="document.getElementById('logTempModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('fisheries.cold-chain.log-temperature', $unit->id) }}"
                class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; @endphp

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Temperature (°C)
                        *</label>
                    <input type="number" name="temperature" required step="0.1" placeholder="-18.5"
                        class="{{ $cls }}">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Humidity
                        (%)</label>
                    <input type="number" name="humidity" step="0.1" min="0" max="100"
                        placeholder="85.0" class="{{ $cls }}">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sensor ID
                        (Optional)</label>
                    <input type="text" name="sensor_id" placeholder="SENSOR-001" class="{{ $cls }}">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        💾 Save Reading
                    </button>
                    <button type="button" onclick="document.getElementById('logTempModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
