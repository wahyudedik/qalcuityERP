@extends('layouts.app')

@section('title', 'Edit Device — ' . $device->name)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('iot.devices.show', $device) }}" class="text-muted text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
        <h4 class="mt-2 mb-0">Edit Device: {{ $device->name }}</h4>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('iot.devices.update', $device) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Device <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $device->name) }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipe Device</label>
                                <select name="device_type" class="form-select">
                                    @foreach($deviceTypes as $val => $label)
                                        <option value="{{ $val }}" {{ $device->device_type == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Target Module</label>
                                <select name="target_module" class="form-select">
                                    @foreach($targetModules as $val => $label)
                                        <option value="{{ $val }}" {{ $device->target_module == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lokasi Fisik</label>
                            <input type="text" name="location" class="form-control"
                                value="{{ old('location', $device->location) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipe Sensor</label>
                            <div class="row g-2">
                                @foreach($sensorTypes as $val => $label)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="sensor_types[]"
                                            value="{{ $val }}" id="sensor_{{ $val }}"
                                            {{ in_array($val, old('sensor_types', $device->sensor_types ?? [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="sensor_{{ $val }}">{{ $label }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Versi Firmware</label>
                            <input type="text" name="firmware_version" class="form-control"
                                value="{{ old('firmware_version', $device->firmware_version) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $device->notes) }}</textarea>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                {{ $device->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Device Aktif</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="{{ route('iot.devices.show', $device) }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Info Device</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Device ID</td>
                            <td><code>{{ $device->device_id }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dibuat</td>
                            <td>{{ $device->created_at->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Terakhir Online</td>
                            <td>{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
