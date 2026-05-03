@extends('layouts.app')

@section('title', 'Registrasi Fingerprint Karyawan')

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('hrm.fingerprint.employees.index') }}"
                class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Registrasi Fingerprint</h1>
            <p class="text-sm text-gray-600 mt-1">{{ $employee->name }} - {{ $employee->position }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            @if ($employee->fingerprint_registered)
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <strong>Fingerprint sudah terdaftar</strong><br>
                    UID: {{ $employee->fingerprint_uid }}
                </div>
                <button onclick="removeFingerprint({{ $employee->id }})"
                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                    Hapus Registrasi Fingerprint
                </button>
            @else
                <form id="registerForm" onsubmit="event.preventDefault(); registerFingerprint();">
                    @csrf
                    <div class="mb-4">
                        <label for="device_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih Perangkat <span class="text-red-500">*</span>
                        </label>
                        <select name="device_id" id="device_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white text-gray-900">
                            <option value="">Pilih Perangkat</option>
                            @foreach ($devices as $device)
                                <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->device_id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="uid" class="block text-sm font-medium text-gray-700 mb-2">
                            Fingerprint UID <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="uid" id="uid" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white text-gray-900"
                            placeholder="Masukkan UID dari perangkat fingerprint">
                        <p class="mt-1 text-xs text-gray-500">
                            Mintakan karyawan untuk menempelkan jari pada perangkat, lalu masukkan UID yang muncul
                        </p>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Daftarkan Fingerprint
                    </button>
                </form>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            function registerFingerprint() {
                const form = document.getElementById('registerForm');
                const formData = new FormData(form);

                fetch('{{ route('hrm.fingerprint.employees.register.store', $employee) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(Object.fromEntries(formData))
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            window.location.reload();
                        }
                    })
                    .catch(err => {
                        alert('Error: ' + err.message);
                    });
            }

            function removeFingerprint(employeeId) {
                if (!confirm('Yakin ingin menghapus registrasi fingerprint karyawan ini?')) return;

                fetch(`/hrm/fingerprint/employees/${employeeId}/remove-registration`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            window.location.reload();
                        }
                    })
                    .catch(err => {
                        alert('Error: ' + err.message);
                    });
            }
        </script>
    @endpush
@endsection
