<x-app-layout>
    <x-slot name="header">Cold Chain Management</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <button onclick="document.getElementById('addColdStorageModal').classList.remove('hidden')"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium whitespace-nowrap">
            + Add Cold Storage
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            {{ session('success') }}</div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6" x-data="{ units: @js($stats['units'] ?? []), alerts: @js($stats['alerts'] ?? []) }">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Unit</p>
            <p class="text-2xl font-bold text-blue-600" x-text="units.length">{{ count($stats['units'] ?? []) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Suhu Normal</p>
            <p class="text-2xl font-bold text-green-600" x-text="units.filter(u => u.is_safe).length">
                {{ $stats['safe_units'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Alert Aktif</p>
            <p class="text-2xl font-bold text-red-600" x-text="alerts.length">{{ $stats['active_alerts'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Utilisasi Rata-rata</p>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['avg_utilization'] ?? 0 }}%</p>
        </div>
    </div>

    {{-- Action Bar --}}
    <div class="flex items-center justify-between mb-4">
        <form class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari unit..."
                class="px-3 py-2 text-sm rounded-lg border border-gray-200 bg-white text-gray-900 w-48">
            <select name="status" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                <option value="safe" @selected(request('status') === 'safe')">Suhu Normal</option>
                <option value="warning" @selected(request('status') === 'warning')">Warning</option>
                <option value="critical" @selected(request('status') === 'critical')">Critical</option>
            </select>
        </form>
        <button onclick="document.getElementById('addUnitModal').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
            <span>➕</span> Tambah Unit
        </button>
    </div>

    {{-- Cold Storage Units Grid --}}
    @if (empty($storageUnits) || count($storageUnits) === 0)
        <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
            <p class="text-4xl mb-3">❄️</p>
            <p class="text-sm text-gray-500">Belum ada unit cold storage. Tambahkan unit pertama
                Anda.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($storageUnits as $unit)
                @php
                    $tempClass = $unit->isTemperatureSafe()
                        ? 'green'
                        : ($unit->current_temperature > $unit->max_temperature
                            ? 'red'
                            : 'yellow');
                    $tempColor = $unit->isTemperatureSafe()
                        ? 'text-green-600'
                        : ($unit->current_temperature > $unit->max_temperature
                            ? 'text-red-600'
                            : 'text-yellow-600');
                @endphp
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition group"
                    x-data="{ showTempForm: false, currentTemp: @js($unit->current_temperature) }">

                    {{-- Header --}}
                    <div class="px-5 py-4 flex items-start justify-between border-b border-gray-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-bold text-gray-900">{{ $unit->unit_code }}</span>
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full bg-{{ $tempClass  }}-100 text-{{ $tempClass }}-700 $tempClass }}-500/20 $tempClass }}-400">
                                    {{ $unit->isTemperatureSafe() ? 'Normal' : 'Warning' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-0.5">{{ $unit->name }}</p>
                        </div>
                        <button @click="showTempForm = !showTempForm"
                            class="px-3 py-1 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            🌡️ Update Suhu
                        </button>
                    </div>

                    {{-- Temperature Display --}}
                    <div class="px-5 py-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-xs text-gray-500">Suhu Saat Ini</p>
                                <p class="text-3xl font-bold {{ $tempColor }}"
                                    x-text="currentTemp ? currentTemp.toFixed(1) + '°C' : 'N/A'">
                                    {{ $unit->current_temperature ? number_format($unit->current_temperature, 1) . '°C' : 'N/A' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">Range Aman</p>
                                <p class="text-sm font-medium text-gray-700">
                                    {{ number_format($unit->min_temperature, 1) }}° -
                                    {{ number_format($unit->max_temperature, 1) }}°C
                                </p>
                            </div>
                        </div>

                        {{-- Temperature Form --}}
                        <div x-show="showTempForm" x-transition class="mt-3 p-3 bg-gray-50 rounded-lg">
                            <form :action="'{{ route('fisheries.cold-chain.log-temperature', $unit->id) }}'"
                                method="POST" class="space-y-2">
                                @csrf
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Suhu
                                            (°C)
                                        </label>
                                        <input type="number" name="temperature" required step="0.1"
                                            placeholder="-18.5"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Kelembaban
                                            (%)</label>
                                        <input type="number" name="humidity" step="0.1" placeholder="85.0"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">ID
                                        Sensor (opsional)</label>
                                    <input type="text" name="sensor_id" placeholder="SENSOR-001"
                                        class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-white text-gray-900">
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit"
                                        class="flex-1 px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                        Simpan
                                    </button>
                                    <button type="button" @click="showTempForm = false"
                                        class="px-3 py-1.5 text-xs bg-gray-200 text-gray-700 rounded-lg transition">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Additional Info --}}
                        <div class="grid grid-cols-2 gap-2 text-xs mt-3">
                            @if ($unit->capacity_kg)
                                <div>
                                    <span class="text-gray-400">Kapasitas:</span>
                                    <span class="text-gray-700">{{ number_format($unit->capacity_kg, 0) }}
                                        kg</span>
                                </div>
                            @endif
                            @if ($unit->utilization_percentage)
                                <div>
                                    <span class="text-gray-400">Utilisasi:</span>
                                    <span
                                        class="text-gray-700">{{ number_format($unit->utilization_percentage, 1) }}%</span>
                                </div>
                            @endif
                            @if ($unit->location)
                                <div class="col-span-2">
                                    <span class="text-gray-400">Lokasi:</span>
                                    <span class="text-gray-700">{{ $unit->location }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div
                        class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs">
                        <span class="text-gray-500">
                            Terakhir update:
                            {{ $unit->last_temperature_update ? $unit->last_temperature_update->diffForHumans() : 'Belum pernah' }}
                        </span>
                        <a href="{{ route('fisheries.cold-chain.show', $unit->id) }}"
                            class="text-blue-600 hover:underline">
                            Detail →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $storageUnits->links() }}</div>
    @endif

    {{-- Active Alerts Section --}}
    @if (!empty($alerts) && count($alerts) > 0)
        <div class="mt-6 bg-white rounded-2xl border border-red-200 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <span class="text-red-500">🚨</span> Alert Suhu Aktif ({{ count($alerts) }})
            </h3>
            <div class="space-y-3">
                @foreach ($alerts as $alert)
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-red-50 border border-red-200">
                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-sm">
                            ⚠️</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $alert['title'] }}</p>
                            <p class="text-xs text-gray-600">{{ $alert['description'] }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $alert['time'] }}</p>
                        </div>
                        <span
                            class="text-xs px-2 py-1 rounded-full bg-{{ $alert['severity_color']  }}-100 text-{{ $alert['severity_color'] }}-700 $alert['severity_color'] }}-500/20 $alert['severity_color'] }}-400">
                            {{ ucfirst($alert['severity']) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Add Unit Modal --}}
    <div id="addUnitModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl border border-gray-200 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-gray-900">Tambah Cold Storage Unit</h2>
                <button onclick="document.getElementById('addUnitModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('fisheries.cold-chain.store-storage') }}" class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; @endphp

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kode Unit
                            *</label>
                        <input type="text" name="unit_code" required placeholder="CS-001"
                            class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama *</label>
                        <input type="text" name="name" required placeholder="Cold Room A"
                            class="{{ $cls }}">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Suhu Min (°C)
                            *</label>
                        <input type="number" name="min_temperature" required step="0.1" value="-18"
                            class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Suhu Max (°C)
                            *</label>
                        <input type="number" name="max_temperature" required step="0.1" value="-15"
                            class="{{ $cls }}">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kapasitas
                            (kg)</label>
                        <input type="number" name="capacity_kg" step="0.01" placeholder="5000"
                            class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe</label>
                        <select name="type" class="{{ $cls }}">
                            <option value="cold_room">Cold Room</option>
                            <option value="freezer">Freezer</option>
                            <option value="chiller">Chiller</option>
                            <option value="blast_freezer">Blast Freezer</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Lokasi</label>
                    <input type="text" name="location" placeholder="Gudang A, Lantai 1"
                        class="{{ $cls }}">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" placeholder="Spesifikasi dan catatan tambahan"
                        class="{{ $cls }}"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        💾 Simpan Unit
                    </button>
                    <button type="button" onclick="document.getElementById('addUnitModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Auto-refresh for real-time updates --}}
    <script>
        // Refresh page every 30 seconds for live temperature updates
        setInterval(() => {
            window.location.reload();
        }, 30000);
    </script>
</x-app-layout>
