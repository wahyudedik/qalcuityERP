@extends('layouts.app')

@section('title', 'Detail Perangkat Fingerprint')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="mb-6">
            <a href="{{ route('hrm.fingerprint.devices.index') }}"
                class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Daftar Perangkat
            </a>
            <h1 class="text-2xl font-bold text-gray-800">{{ $device->name }}</h1>
        </div>

        <!-- Status Card -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-600">Status</div>
                <div class="mt-2">
                    <span
                        class="px-3 py-1 rounded-full text-sm font-semibold {{ $device->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $device->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-600">Koneksi</div>
                <div class="mt-2">
                    <span
                        class="px-3 py-1 rounded-full text-sm font-semibold {{ $device->is_connected ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $device->is_connected ? 'Terhubung' : 'Tidak Terhubung' }}
                    </span>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-600">Scan Hari Ini</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $status['today_scans'] ?? 0 }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-600">Karyawan Terdaftar</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">
                    {{ $status['registered_employees'] ?? 0 }}</div>
            </div>
        </div>

        <!-- Device Info -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Perangkat</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-gray-600">Device ID</label>
                    <p class="text-gray-900 font-medium">{{ $device->device_id }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Vendor</label>
                    <p class="text-gray-900 font-medium">{{ ucfirst($device->vendor) }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Model</label>
                    <p class="text-gray-900 font-medium">{{ $device->model ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Protokol</label>
                    <p class="text-gray-900 font-medium">{{ strtoupper($device->protocol) }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">IP Address</label>
                    <p class="text-gray-900 font-medium">{{ $device->ip_address ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Port</label>
                    <p class="text-gray-900 font-medium">{{ $device->port }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Terakhir Sync</label>
                    <p class="text-gray-900 font-medium">
                        {{ $device->last_sync_at ? $device->last_sync_at->format('d M Y H:i:s') : 'Belum pernah' }}</p>
                </div>
            </div>
            @if ($device->notes)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <label class="text-sm text-gray-600">Catatan</label>
                    <p class="text-gray-900 mt-1">{{ $device->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="flex gap-3 mb-6">
            <button onclick="testConnection({{ $device->id }})"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                Test Koneksi
            </button>
            <button onclick="syncAttendance({{ $device->id }})"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                Sinkronisasi Absensi
            </button>
            <a href="{{ route('hrm.fingerprint.devices.edit', $device) }}"
                class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg">
                Edit Perangkat
            </a>
        </div>

        <!-- Recent Logs -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Log Absensi Terbaru</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Karyawan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentLogs as $log)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $log->scan_time->format('d M Y H:i:s') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $log->employee?->name ?? $log->employee_uid }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span
                                    class="px-2 py-1 rounded text-xs font-semibold
                            {{ $log->scan_type === 'check_in' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                    {{ $log->scan_type === 'check_in' ? 'Check In' : 'Check Out' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if ($log->is_processed)
                                    <span class="text-green-600">✓ Diproses</span>
                                @else
                                    <span class="text-yellow-600">⏳ Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                Belum ada data absensi
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script>
            function testConnection(deviceId) {
                fetch(`/api/fingerprint/devices/${deviceId}/test-connection`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message);
                    })
                    .catch(err => {
                        alert('Error: ' + err.message);
                    });
            }

            function syncAttendance(deviceId) {
                if (!confirm('Mulai sinkronisasi data absensi dari perangkat?')) return;

                fetch(`/api/fingerprint/devices/${deviceId}/sync-attendance`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message);
                        location.reload();
                    })
                    .catch(err => {
                        alert('Error: ' + err.message);
                    });
            }
        </script>
    @endpush
@endsection
