<x-app-layout>

    <div class="container-fluid">
        <div class="flex items-start justify-between mb-4">
            <div>
                <a href="{{ route('iot.devices.index') }}" class="text-gray-500 no-underline text-sm">
                    <i class="fas fa-argrid grid-cols-1 md:grid-cols-2 gap-6-left mr-1"></i> IoT Devices
                </a>
                <h4 class="mt-1 mb-0">{{ $device->name }}</h4>
                <small class="text-gray-500">
                    {{ \App\Models\IotDevice::deviceTypes()[$device->device_type] ?? $device->device_type }}
                    @if ($device->location)
                        · {{ $device->location }}
                    @endif
                </small>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('iot.devices.edit', $device) }}"
                    class="px-3 py-1.5 border border-gray-400 text-gray-600 hover:bg-gray-50 rounded-lg text-xs transition">Edit</a>
                <form action="{{ route('iot.devices.destroy', $device) }}" method="POST"
                    data-confirm="Hapus device ini?" data-confirm-type="danger">
                    @csrf @method('DELETE')
                    <button
                        class="px-3 py-1.5 border border-red-500 text-red-600 hover:bg-red-50 rounded-lg text-xs transition">Hapus</button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 g-3 mb-4">
            {{-- Token Card --}}
            <div class="w-full">
                <div class="bg-white rounded-2xl border border-amber-300 border-0 shadow-sm bg-warning-subtle">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold mb-1"><i class="fas fa-key mr-1"></i> Device Token (Firmware
                                    Secret)</div>
                                <code class="fs-6" id="deviceToken">{{ $device->device_token }}</code>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    class="px-3 py-1.5 border border-gray-400 text-gray-600 hover:bg-gray-50 rounded-lg text-xs transition"
                                    onclick="navigator.clipboard.writeText('{{ $device->device_token }}');this.textContent='Tersalin!'">
                                    Salin
                                </button>
                                <button
                                    class="px-3 py-1.5 border border-red-500 text-red-600 hover:bg-red-50 rounded-lg text-xs transition"
                                    id="btnRegenToken">Regenerate</button>
                            </div>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            Endpoint: <code>POST {{ url('/api/webhooks/iot/telemetry') }}</code>
                            · Header: <code>X-Device-Token: [token]</code>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Cards --}}
            <div class="w-full md:w-1/4">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm text-center py-3">
                    <div class="mb-1">
                        @if ($device->is_connected)
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 fs-6">Online</span>
                        @else
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 fs-6">Offline</span>
                        @endif
                    </div>
                    <div class="text-gray-500 text-sm">Status Koneksi</div>
                </div>
            </div>
            <div class="w-full md:w-1/4">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm text-center py-3">
                    <div class="font-bold">
                        {{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Belum pernah' }}</div>
                    <div class="text-gray-500 text-sm">Terakhir Online</div>
                </div>
            </div>
            <div class="w-full md:w-1/4">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm text-center py-3">
                    <div class="font-bold">{{ \App\Models\IotDevice::targetModules()[$device->target_module] ?? '-' }}
                    </div>
                    <div class="text-gray-500 text-sm">Target Module</div>
                </div>
            </div>
            <div class="w-full md:w-1/4">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm text-center py-3">
                    <div class="font-bold">{{ $device->firmware_version ?? '-' }}</div>
                    <div class="text-gray-500 text-sm">Firmware</div>
                </div>
            </div>
        </div>

        {{-- Sensor Stats --}}
        @if ($stats->count())
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-4">
                <div class="px-5 py-4 border-b border-gray-200 font-semibold">Statistik Sensor</div>
                <div class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left mb-0">
                            <thead class="w-full text-sm text-left-light">
                                <tr>
                                    <th>Sensor</th>
                                    <th class="text-right">Total Log</th>
                                    <th class="text-right">Rata-rata</th>
                                    <th class="text-right">Min</th>
                                    <th class="text-right">Max</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stats ?? [] as $stat)
                                    <tr>
                                        <td>
                                            {{ \App\Models\IotDevice::sensorTypes()[$stat->sensor_type] ?? $stat->sensor_type }}
                                        </td>
                                        <td class="text-right">{{ number_format($stat->total) }}</td>
                                        <td class="text-right">{{ number_format($stat->avg_value, 2) }}</td>
                                        <td class="text-right">{{ number_format($stat->min_value, 2) }}</td>
                                        <td class="text-right">{{ number_format($stat->max_value, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Recent Telemetry --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center font-semibold">
                <span>Log Telemetry Terbaru</span>
                <small class="text-gray-500">50 data terakhir</small>
            </div>
            <div class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left w-full text-sm text-left-hover mb-0">
                        <thead class="w-full text-sm text-left-light">
                            <tr>
                                <th>Waktu</th>
                                <th>Sensor</th>
                                <th class="text-right">Nilai</th>
                                <th>Satuan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLogs as $log)
                                <tr>
                                    <td><small>{{ $log->recorded_at->format('d/m H:i:s') }}</small></td>
                                    <td>{{ \App\Models\IotDevice::sensorTypes()[$log->sensor_type] ?? $log->sensor_type }}
                                    </td>
                                    <td class="text-right font-semibold">{{ $log->value }}</td>
                                    <td><small class="text-gray-500">{{ $log->unit }}</small></td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $log->status === 'received' ? 'emerald-500' : 'secondary' }}-subtle
                                    text-{{ $log->status === 'received' ? 'success' : 'secondary' }} small">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-6 text-gray-400">
                                        Belum ada data telemetry. Pastikan device sudah mengirim data.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('btnRegenToken')?.addEventListener('click', async function() {
                const confirmed = await Dialog.danger(
                    'Regenerate token? Firmware lama tidak bisa kirim data sampai token diupdate.');
                if (!confirmed) return;
                fetch('{{ route('iot.devices.regenerate-token', $device) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('deviceToken').textContent = data.device_token;
                            Dialog.success('Token baru: ' + data.device_token +
                                '\n\nUpdate firmware Anda sekarang.');
                        }
                    });
            });
        </script>
    @endpush
</x-app-layout>
