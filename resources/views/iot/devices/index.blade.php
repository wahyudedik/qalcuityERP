@extends('layouts.app')

@section('title', 'IoT Device Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">IoT Device Management</h4>
            <small class="text-muted">ESP32 · Arduino · Raspberry Pi</small>
        </div>
        <a href="{{ route('iot.devices.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Device
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-primary">{{ $devices->total() }}</div>
                <div class="text-muted small">Total Device</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-success">{{ $devices->where('is_connected', true)->count() }}</div>
                <div class="text-muted small">Online</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-warning">{{ $devices->where('is_active', true)->where('is_connected', false)->count() }}</div>
                <div class="text-muted small">Aktif / Offline</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-secondary">{{ $devices->where('is_active', false)->count() }}</div>
                <div class="text-muted small">Nonaktif</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Device</th>
                            <th>Tipe</th>
                            <th>Lokasi</th>
                            <th>Module</th>
                            <th>Status</th>
                            <th>Terakhir Online</th>
                            <th>Log</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices as $device)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $device->name }}</div>
                                <small class="text-muted font-monospace">{{ $device->device_id }}</small>
                            </td>
                            <td>
                                @php
                                    $icons = ['esp32'=>'🔌','arduino'=>'⚡','raspberry_pi'=>'🍓','generic'=>'📡'];
                                @endphp
                                {{ $icons[$device->device_type] ?? '📡' }}
                                {{ \App\Models\IotDevice::deviceTypes()[$device->device_type] ?? $device->device_type }}
                            </td>
                            <td>{{ $device->location ?? '-' }}</td>
                            <td>
                                <span class="badge bg-info-subtle text-info">
                                    {{ \App\Models\IotDevice::targetModules()[$device->target_module] ?? $device->target_module }}
                                </span>
                            </td>
                            <td>
                                @if(!$device->is_active)
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @elseif($device->is_connected)
                                    <span class="badge bg-success">Online</span>
                                @else
                                    <span class="badge bg-warning text-dark">Offline</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Belum pernah' }}</small>
                            </td>
                            <td><small class="text-muted">{{ number_format($device->telemetry_logs_count) }}</small></td>
                            <td>
                                <a href="{{ route('iot.devices.show', $device) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-microchip fa-2x mb-2 d-block"></i>
                                Belum ada device IoT. <a href="{{ route('iot.devices.create') }}">Tambah sekarang</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $devices->links() }}</div>
</div>
@endsection
