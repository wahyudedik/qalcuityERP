<x-app-layout>

<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('iot.devices.show', $device) }}" class="text-gray-500 no-underline">
            <i class="fas fa-argrid grid-cols-1 md:grid-cols-2 gap-6-left mr-1"></i> Kembali
        </a>
        <h4 class="mt-2 mb-0">Edit Device: {{ $device->name }}</h4>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="col-lg-7">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
                <div class="p-5">
                    <form action="{{ route('iot.devices.update', $device) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label font-semibold">Nama Device <span class="text-red-600">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $device->name) }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 g-3 mb-3">
                            <div class="w-full md:w-1/2">
                                <label class="form-label font-semibold">Tipe Device</label>
                                <select name="device_type" class="form-select">
                                    @foreach($deviceTypes ?? [] as $val => $label)
                                        <option value="{{ $val }}" {{ $device->device_type == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label font-semibold">Target Module</label>
                                <select name="target_module" class="form-select">
                                    @foreach($targetModules ?? [] as $val => $label)
                                        <option value="{{ $val }}" {{ $device->target_module == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-semibold">Lokasi Fisik</label>
                            <input type="text" name="location" class="form-control"
                                value="{{ old('location', $device->location) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-semibold">Tipe Sensor</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 g-2">
                                @foreach($sensorTypes ?? [] as $val => $label)
                                <div class="w-full md:w-1/3">
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
                            <label class="form-label font-semibold">Versi Firmware</label>
                            <input type="text" name="firmware_version" class="form-control"
                                value="{{ old('firmware_version', $device->firmware_version) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-semibold">Catatan</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $device->notes) }}</textarea>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                {{ $device->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Device Aktif</label>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Simpan Perubahan</button>
                            <a href="{{ route('iot.devices.show', $device) }}" class="px-4 py-2 border border-gray-400 text-gray-600 hover:bg-gray-50 rounded-xl text-sm transition">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-200 font-semibold">Info Device</div>
                <div class="p-5">
                    <table class="w-full text-sm text-left w-full text-sm text-left-borderless mb-0">
                        <tr>
                            <td class="text-gray-500">Device ID</td>
                            <td><code>{{ $device->device_id }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-gray-500">Dibuat</td>
                            <td>{{ $device->created_at->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-gray-500">Terakhir Online</td>
                            <td>{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
