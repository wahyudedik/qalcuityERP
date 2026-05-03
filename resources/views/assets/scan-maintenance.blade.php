<x-app-layout>
    <x-slot name="header">Scan Asset untuk Maintenance</x-slot>

    <div class="max-w-3xl mx-auto space-y-4">

        {{-- Scanner Card --}}
        <div class="bg-blue-50 rounded-2xl border border-blue-200 p-5">
            <p class="text-sm font-semibold text-blue-900 mb-3">Scan Barcode Asset</p>
            <div class="flex gap-2">
                <input type="text" id="barcode-input"
                    placeholder="Scan barcode asset atau ketik asset code, lalu Enter..."
                    class="flex-1 px-4 py-2.5 rounded-xl border border-blue-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    autocomplete="off" autofocus>
                <button type="button" onclick="openBarcodeScanner()"
                    class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition flex items-center gap-2 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 9V6a1 1 0 011-1h3M3 15v3a1 1 0 001 1h3m11-4v3a1 1 0 01-1 1h-3m4-11h-3a1 1 0 00-1 1v3M9 3H6a1 1 0 00-1 1v3m0 6v3a1 1 0 001 1h3m6-10h3a1 1 0 011 1v3" />
                    </svg>
                    Kamera
                </button>
            </div>
            <p id="scan-feedback" class="text-xs mt-2 text-blue-600 min-h-[16px]"></p>
        </div>

        {{-- Asset Details Card (hidden until scanned) --}}
        <div id="asset-details-card"
            class="hidden bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 id="asset-name" class="text-lg font-bold text-gray-900">-</h2>
                    <p id="asset-code" class="text-xs font-mono text-gray-500 mt-0.5">-</p>
                </div>
                <span id="asset-status-badge"
                    class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">-</span>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm mb-5">
                <div>
                    <span class="text-gray-500 text-xs block">Kategori</span>
                    <span id="asset-category" class="font-medium text-gray-900">-</span>
                </div>
                <div>
                    <span class="text-gray-500 text-xs block">Lokasi</span>
                    <span id="asset-location" class="font-medium text-gray-900">-</span>
                </div>
                @if (auth()->user()->can('edit', 'assets'))
                    <div>
                        <span class="text-gray-500 text-xs block">Brand</span>
                        <span id="asset-brand" class="font-medium text-gray-900">-</span>
                    </div>
                    <div>
                        <span class="text-gray-500 text-xs block">Model</span>
                        <span id="asset-model" class="font-medium text-gray-900">-</span>
                    </div>
                @endif
            </div>

            {{-- Quick Maintenance Form --}}
            <form method="POST" action="{{ route('assets.maintenance.store') }}"
                class="border-t border-gray-100 pt-4">
                @csrf
                <input type="hidden" name="asset_id" id="form-asset-id">

                <h3 class="text-sm font-semibold text-gray-900 mb-3">Buat Maintenance Record</h3>

                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tipe</label>
                        <select name="type" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="preventive">Preventive</option>
                            <option value="corrective">Corrective</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tanggal Jadwal</label>
                        <input type="date" name="scheduled_date" required value="{{ date('Y-m-d') }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">Deskripsi Pekerjaan</label>
                    <textarea name="description" rows="3" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Jelaskan pekerjaan maintenance yang akan dilakukan..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Estimasi Biaya (Rp)</label>
                        <input type="number" name="cost" step="0.01"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Vendor/Teknisi</label>
                        <input type="text" name="vendor"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Nama vendor/teknisi">
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition">
                        Buat Jadwal Maintenance
                    </button>
                    <button type="button" onclick="resetScan()"
                        class="px-4 py-2.5 border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition">
                        Scan Ulang
                    </button>
                </div>
            </form>
        </div>

        {{-- Maintenance History Preview --}}
        <div id="maintenance-history"
            class="hidden bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Riwayat Maintenance Terakhir</h3>
            <div id="history-list" class="space-y-2">
                {{-- Populated via JS --}}
            </div>
        </div>
    </div>

    {{-- Camera Scanner Modal --}}
    <div id="barcode-scanner-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900 text-sm">Scan Barcode Asset</h3>
                <button onclick="closeBarcodeScanner()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-4">
                <video id="barcode-video" class="w-full rounded-xl bg-black aspect-video"></video>
                <p class="text-xs text-center text-gray-500 mt-3">Arahkan kamera ke barcode asset
                </p>
                <div class="mt-3 border-t border-gray-100 pt-3">
                    <p class="text-xs text-gray-500 mb-1">Atau ketik manual:</p>
                    <div class="flex gap-2">
                        <input type="text" id="manual-barcode" placeholder="Asset Code / Serial Number"
                            class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button onclick="submitManualBarcode()"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
        <script>
            let codeReader = null;
            let currentAsset = null;

            function handleBarcodeScanned(barcode) {
                stopScanner();
                closeBarcodeScanner();
                document.getElementById('barcode-input').value = barcode;
                processBarcode(barcode);
            }

            function processBarcode(barcode) {
                const feedback = document.getElementById('scan-feedback');
                feedback.textContent = 'Mencari asset...';
                feedback.className = 'text-xs mt-2 text-blue-600 min-h-[16px]';

                fetch(`{{ route('assets.lookup-barcode') }}?barcode=${encodeURIComponent(barcode)}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            currentAsset = data.data;
                            displayAsset(data.data);
                            feedback.textContent = `Asset ditemukan: ${data.name}`;
                            feedback.className = 'text-xs mt-2 text-green-600 min-h-[16px]';
                            playSuccessSound();
                            loadMaintenanceHistory(data.id);
                        } else {
                            feedback.textContent = 'Asset tidak ditemukan.';
                            feedback.className = 'text-xs mt-2 text-red-500 min-h-[16px]';
                        }
                    })
                    .catch(() => {
                        feedback.textContent = 'Gagal mencari asset.';
                        feedback.className = 'text-xs mt-2 text-red-500 min-h-[16px]';
                    });
            }

            function displayAsset(asset) {
                document.getElementById('asset-name').textContent = asset.name;
                document.getElementById('asset-code').textContent =
                    `${asset.asset_code} ${asset.serial_number ? '• S/N: ' + asset.serial_number : ''}`;
                document.getElementById('asset-category').textContent = ucfirst(asset.category);
                document.getElementById('asset-location').textContent = asset.location || '-';
                document.getElementById('asset-brand').textContent = asset.brand || '-';
                document.getElementById('asset-model').textContent = asset.model || '-';

                // Status badge
                const badge = document.getElementById('asset-status-badge');
                const colors = {
                    active: 'green',
                    maintenance: 'amber',
                    disposed: 'red',
                    retired: 'gray'
                };
                const color = colors[asset.status] || 'gray';
                badge.className =
                    `px-2 py-0.5 rounded-full text-xs bg-${color}-100 text-${color}-700`;
                badge.textContent = ucfirst(asset.status);

                // Set form asset_id
                document.getElementById('form-asset-id').value = asset.id;

                // Show card
                document.getElementById('asset-details-card').classList.remove('hidden');
            }

            function loadMaintenanceHistory(assetId) {
                fetch(`/api/assets/${assetId}/maintenances`)
                    .then(r => r.ok ? r.json() : [])
                    .then(history => {
                        const container = document.getElementById('history-list');
                        if (history.length > 0) {
                            document.getElementById('maintenance-history').classList.remove('hidden');
                            container.innerHTML = history.slice(0, 5).map(m => `
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div class="flex-1">
                                <p class="text-xs font-medium text-gray-900">${ucfirst(m.type)} Maintenance</p>
                                <p class="text-xs text-gray-500 mt-0.5">${new Date(m.scheduled_date).toLocaleDateString('id-ID')}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] bg-${m.status === 'completed' ? 'green' : m.status === 'in_progress' ? 'blue' : 'gray'}-100 text-${m.status === 'completed' ? 'green' : m.status === 'in_progress' ? 'blue' : 'gray'}-700">
                                ${ucfirst(m.status)}
                            </span>
                        </div>
                    `).join('');
                        }
                    })
                    .catch(() => {});
            }

            function resetScan() {
                currentAsset = null;
                document.getElementById('barcode-input').value = '';
                document.getElementById('asset-details-card').classList.add('hidden');
                document.getElementById('maintenance-history').classList.add('hidden');
                document.getElementById('scan-feedback').textContent = '';
                document.getElementById('barcode-input').focus();
            }

            function openBarcodeScanner() {
                document.getElementById('barcode-scanner-modal').classList.remove('hidden');
                startScanner();
            }

            function closeBarcodeScanner() {
                document.getElementById('barcode-scanner-modal').classList.add('hidden');
                stopScanner();
            }

            function startScanner() {
                // Check if browser supports getUserMedia
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert('Browser Anda tidak mendukung akses kamera. Gunakan browser modern dengan HTTPS.');
                    return;
                }

                const videoEl = document.getElementById('barcode-video');

                try {
                    codeReader = new ZXing.BrowserMultiFormatReader();
                    codeReader.decodeFromVideoDevice(null, videoEl, (result, err) => {
                        if (result) handleBarcodeScanned(result.text);
                        if (err && !(err instanceof ZXing.NotFoundException)) {
                            console.error('Scanner error:', err);
                        }
                    }).catch((error) => {
                        console.error('Failed to access camera:', error);
                        handleCameraError(error);
                        stopScanner();
                    });
                } catch (error) {
                    console.error('Scanner initialization error:', error);
                    alert('Gagal menginisialisasi scanner: ' + error.message);
                    stopScanner();
                }
            }

            function handleCameraError(error) {
                if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                    alert('Akses kamera ditolak. Mohon izinkan akses kamera di browser settings.');
                } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
                    alert('Tidak ada kamera yang ditemukan di perangkat Anda.');
                } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
                    alert('Kamera sedang digunakan oleh aplikasi lain.');
                } else {
                    alert('Gagal mengakses kamera: ' + error.message);
                }
            }

            function stopScanner() {
                if (codeReader) {
                    codeReader.reset();
                    codeReader = null;
                }
            }

            function submitManualBarcode() {
                const val = document.getElementById('manual-barcode').value.trim();
                if (val) handleBarcodeScanned(val);
            }

            function playSuccessSound() {
                try {
                    const ctx = new(window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.frequency.value = 880;
                    osc.type = 'sine';
                    gain.gain.setValueAtTime(0.08, ctx.currentTime);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.1);
                } catch (e) {}
            }

            function ucfirst(str) {
                return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
            }

            // Hardware scanner / Enter key
            document.getElementById('barcode-input').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const val = this.value.trim();
                    if (val) processBarcode(val);
                }
            });

            document.getElementById('manual-barcode').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitManualBarcode();
                }
            });
        </script>
    @endpush
</x-app-layout>
