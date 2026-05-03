<x-app-layout>
    <x-slot name="header">{{ __('Pengaturan Telemedicine') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <button type="button" onclick="resetToDefault()"
            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
            <i class="fas fa-undo mr-2"></i>Reset Default
        </button>
        <a href="{{ route('healthcare.telemedicine.index') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('healthcare.telemedicine.settings.update') }}" id="settings-form">
                @csrf

                {{-- Jitsi Configuration --}}
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-server text-blue-600 mr-2"></i>Konfigurasi Jitsi Meet
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Konfigurasi server Jitsi Meet Anda. Biarkan default untuk menggunakan server publik gratis
                        (meet.jit.si).
                    </p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">URL Server
                                Jitsi *</label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input type="url" name="jitsi_server_url" id="jitsi_server_url"
                                    value="{{ old('jitsi_server_url', $settings->jitsi_server_url) }}"
                                    placeholder="https://meet.jit.si"
                                    class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required />
                                <button type="button" onclick="testConnection()"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 whitespace-nowrap">
                                    <i class="fas fa-plug mr-2"></i>Test Koneksi
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Default: https://meet.jit.si (gratis). Untuk self-hosted: https://your-jitsi-domain.com
                            </p>
                            <div id="connection-test-result" class="mt-2 hidden"></div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jitsi App
                                    ID (opsional)</label>
                                <input type="text" name="jitsi_app_id"
                                    value="{{ old('jitsi_app_id', $settings->jitsi_app_id) }}"
                                    placeholder="Kosongkan untuk server publik"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <p class="mt-1 text-xs text-gray-500">Untuk Jitsi self-hosted dengan
                                    autentikasi</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jitsi
                                    Secret (opsional)</label>
                                <input type="password" name="jitsi_secret"
                                    value="{{ old('jitsi_secret', $settings->jitsi_secret) }}"
                                    placeholder="JWT secret key"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <p class="mt-1 text-xs text-gray-500">JWT secret untuk autentikasi
                                    berbasis token</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Features --}}
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-toggle-on text-green-600 mr-2"></i>Fitur
                    </h3>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Aktifkan Rekaman</p>
                                <p class="text-sm text-gray-600">Izinkan perekaman konsultasi</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_recording" value="1"
                                    {{ old('enable_recording', $settings->enable_recording) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Ruang Tunggu Virtual</p>
                                <p class="text-sm text-gray-600">Pasien menunggu sebelum diterima</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_waiting_room" value="1"
                                    {{ old('enable_waiting_room', $settings->enable_waiting_room) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Chat</p>
                                <p class="text-sm text-gray-600">Aktifkan chat teks dalam panggilan
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_chat" value="1"
                                    {{ old('enable_chat', $settings->enable_chat) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Berbagi Layar</p>
                                <p class="text-sm text-gray-600">Izinkan peserta berbagi layar</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_screen_share" value="1"
                                    {{ old('enable_screen_share', $settings->enable_screen_share) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Appointment Reminders --}}
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-bell text-yellow-600 mr-2"></i>Pengingat Jadwal
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Aktifkan Pengingat</p>
                                <p class="text-sm text-gray-600">Kirim pengingat email sebelum
                                    konsultasi</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="reminder_enabled" value="1"
                                    {{ old('reminder_enabled', $settings->reminder_enabled) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Waktu
                                    Pengingat (menit sebelum)</label>
                                <input type="number" name="reminder_minutes_before"
                                    value="{{ old('reminder_minutes_before', $settings->reminder_minutes_before) }}"
                                    min="5" max="1440"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <p class="mt-1 text-xs text-gray-500">Default: 30 menit</p>
                            </div>
                            <div
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">Pengingat Email</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="send_email_reminder" value="1"
                                        {{ old('send_email_reminder', $settings->send_email_reminder) ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Feedback Settings --}}
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-star text-purple-600 mr-2"></i>Feedback & Rating
                    </h3>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Aktifkan Feedback</p>
                                <p class="text-sm text-gray-600">Izinkan pasien mengirim feedback
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_feedback" value="1"
                                    {{ old('enable_feedback', $settings->enable_feedback) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">Wajibkan Feedback</p>
                                <p class="text-sm text-gray-600">Feedback wajib setelah konsultasi
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="require_feedback" value="1"
                                    {{ old('require_feedback', $settings->require_feedback) ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Consultation Limits --}}
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-clock text-red-600 mr-2"></i>Batas Konsultasi
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Timeout
                                (menit)</label>
                            <input type="number" name="consultation_timeout_minutes"
                                value="{{ old('consultation_timeout_minutes', $settings->consultation_timeout_minutes) }}"
                                min="15" max="240"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <p class="mt-1 text-xs text-gray-500">Default: 60 menit</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Maks
                                Peserta</label>
                            <input type="number" name="max_participants"
                                value="{{ old('max_participants', $settings->max_participants) }}" min="2"
                                max="50"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <p class="mt-1 text-xs text-gray-500">Default: 10 peserta</p>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('healthcare.telemedicine.index') }}"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                            <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function testConnection() {
                const url = document.getElementById('jitsi_server_url').value;
                const resultDiv = document.getElementById('connection-test-result');

                if (!url) {
                    alert('Masukkan URL server Jitsi terlebih dahulu');
                    return;
                }

                resultDiv.innerHTML =
                    '<p class="text-sm text-blue-600"><i class="fas fa-spinner fa-spin mr-2"></i>Menguji koneksi...</p>';
                resultDiv.classList.remove('hidden');

                fetch('{{ route('healthcare.telemedicine.settings.test-connection') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            jitsi_server_url: url
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            resultDiv.innerHTML =
                                `<p class="text-sm text-green-600"><i class="fas fa-check-circle mr-2"></i>${data.message}</p>`;
                        } else {
                            resultDiv.innerHTML =
                                `<p class="text-sm text-red-600"><i class="fas fa-times-circle mr-2"></i>${data.message}</p>`;
                        }
                    })
                    .catch(error => {
                        resultDiv.innerHTML =
                            `<p class="text-sm text-red-600"><i class="fas fa-times-circle mr-2"></i>Koneksi gagal</p>`;
                    });
            }

            function resetToDefault() {
                if (confirm('Apakah Anda yakin ingin mereset semua pengaturan ke default?')) {
                    fetch('{{ route('healthcare.telemedicine.settings.reset') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => {
                            window.location.reload();
                        });
                }
            }
        </script>
    @endpush
</x-app-layout>
