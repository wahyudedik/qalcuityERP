@extends('layouts.app')

@section('title', $device->name . ' — IoT Device')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <a href="{{ route('iot.devices.index') }}" class="text-muted text-decoration-none small">
                <i class="fas fa-arrow-left me-1"></i> IoT Devices
            </a>
            <h4 class="mt-1 mb-0">{{ $device->name }}</h4>
            <small class="text-muted">
                {{ \App\Models\IotDevice::deviceTypes()[$device->device_type] ?? $device->device_type }}
                @if($device->location) · {{ $device->location }} @endif
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('iot.devices.edit', $device) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
            <form action="{{ route('iot.devices.destroy', $device) }}" method="POST"
                onsubmit="return confirm('Hapus device ini?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm">Hapus</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        {{-- Token Card --}}
        <div class="col-12">
            <div class="card border-warning border-0 shadow-sm bg-warning-subtle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold mb-1"><i class="fas fa-key me-1"></i> Device Token (Firmware Secret)</div>
                            <code class="fs-6" id="deviceToken">{{ $device->device_token }}</code>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary"
                                onclick="navigator.clipboard.writeText('{{ $device->device_token }}');this.textContent='Tersalin!'">
                                Salin
                            </button>
                            <button class="btn btn-sm btn-outline-danger" id="btnRegenToken">Regenerate</button>
                        </div>
                    </div>
                    <div class="mt-2 small text-muted">
                        Endpoint: <code>POST {{ url('/api/webhooks/iot/telemetry') }}</code>
                        · Header: <code>X-Device-Token: [token]</code>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Cards --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="mb-1">
                    @if($device->is_connected)
                        <span class="badge bg-success fs-6">Online</span>
                    @else
                        <span class="badge bg-secondary fs-6">Offline</span>
                    @endif
                </div>
                <div class="text-muted small">Status Koneksi</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fw-bold">{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Belum pernah' }}</div>
                <div class="text-muted small">Terakhir Online</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fw-bold">{{ \App\Models\IotDevice::targetModules()[$device->target_module] ?? '-' }}</div>
                <div class="text-muted small">Target Module</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fw-bold">{{ $device->firmware_version ?? '-' }}</div>
                <div class="text-muted small">Firmware</div>
            </div>
        </div>
    </div>

    {{-- Sensor Stats --}}
    @if($stats->count())
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">Statistik Sensor</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sensor</th>
                            <th class="text-end">Total Log</th>
                            <th class="text-end">Rata-rata</th>
                            <th class="text-end">Min</th>
                            <th class="text-end">Max</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats as $stat)
                        <tr>
                            <td>
                                {{ \App\Models\IotDevice::sensorTypes()[$stat->sensor_type] ?? $stat->sensor_type }}
                            </td>
                            <td class="text-end">{{ number_format($stat->total) }}</td>
                            <td class="text-end">{{ number_format($stat->avg_value, 2) }}</td>
                            <td class="text-end">{{ number_format($stat->min_value, 2) }}</td>
                            <td class="text-end">{{ number_format($stat->max_value, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Recent Telemetry --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold d-flex justify-content-between">
            <span>Log Telemetry Terbaru</span>
            <small class="text-muted">50 data terakhir</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Sensor</th>
                            <th class="text-end">Nilai</th>
                            <th>Satuan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLogs as $log)
                        <tr>
                            <td><small>{{ $log->recorded_at->format('d/m H:i:s') }}</small></td>
                            <td>{{ \App\Models\IotDevice::sensorTypes()[$log->sensor_type] ?? $log->sensor_type }}</td>
                            <td class="text-end fw-semibold">{{ $log->value }}</td>
                            <td><small class="text-muted">{{ $log->unit }}</small></td>
                            <td>
                                <span class="badge bg-{{ $log->status === 'received' ? 'success' : 'secondary' }}-subtle
                                    text-{{ $log->status === 'received' ? 'success' : 'secondary' }} small">
                                    {{ $log->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
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
document.getElementById('btnRegenToken')?.addEventListener('click', function() {
    if (!confirm('Regenerate token? Firmware lama tidak bisa kirim data sampai token diupdate.')) return;
    fetch('{{ route('iot.devices.regenerate-token', $device) }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'}
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('deviceToken').textContent = data.device_token;
            alert('Token baru: ' + data.device_token + '\n\nUpdate firmware Anda sekarang.');
        }
    });
});
</script>
@endpush
@endsection
