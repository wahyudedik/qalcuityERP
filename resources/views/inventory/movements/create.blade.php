<x-app-layout>
    <x-slot name="header">Stock Movement - Tambah Transaksi</x-slot>

    <div class="max-w-4xl mx-auto">
        {{-- Form Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">📦 Stock Movement</h3>

            <form method="POST" action="{{ route('inventory.movements.store') }}" id="movement-form">
                @csrf

                {{-- Barcode Scanner Section --}}
                <div class="mb-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <label class="block text-sm font-medium text-blue-900 mb-2">
                        📷 Scan Barcode Produk
                    </label>
                    <div class="flex gap-3">
                        <input type="text" id="barcode-input" placeholder="Scan barcode atau ketik SKU..."
                            class="flex-1 px-4 py-2.5 rounded-xl border border-blue-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            autocomplete="off">
                        <button type="button" onclick="openBarcodeScanner()"
                            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M3 9V6a1 1 0 011-1h3M3 15v3a1 1 0 001 1h3m11-4v3a1 1 0 01-1 1h-3m4-11h-3a1 1 0 00-1 1v3M9 3H6a1 1 0 00-1 1v3m0 6v3a1 1 0 001 1h3m6-10h3a1 1 0 011 1v3" />
                            </svg>
                            Scan
                        </button>
                    </div>
                    <p class="text-xs text-blue-600 mt-2">
                        💡 Tip: Gunakan hardware barcode scanner untuk hasil terbaik, atau klik tombol Scan untuk
                        menggunakan kamera
                    </p>
                </div>

                {{-- Product Selection (Auto-filled by barcode) --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Produk *
                    </label>
                    <div class="relative">
                        <select name="product_id" id="product-select" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            onchange="loadProductStock()">
                            <option value="">-- Pilih Produk --</option>
                        </select>
                        <div id="product-info" class="hidden mt-2 p-3 bg-green-50 rounded-xl border border-green-100">
                            <p class="text-sm font-medium text-green-900" id="product-name"></p>
                            <p class="text-xs text-green-700" id="product-sku"></p>
                        </div>
                    </div>
                </div>

                {{-- Warehouse Selection --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Gudang *
                    </label>
                    <select name="warehouse_id" id="warehouse-select" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="loadProductStock()">
                        <option value="">-- Pilih Gudang --</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Current Stock Display --}}
                <div class="mb-4 hidden" id="current-stock-card">
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                        <p class="text-xs text-gray-500 mb-1">Stok Saat Ini:</p>
                        <p class="text-2xl font-bold text-blue-600">
                            <span id="current-stock-value">0</span>
                            <span class="text-sm font-normal text-gray-500 ml-1" id="stock-unit">pcs</span>
                        </p>
                    </div>
                </div>

                {{-- Movement Type --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Transaksi *
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="in" class="peer sr-only" checked
                                onchange="toggleNotes()">
                            <div
                                class="p-3 rounded-xl border border-gray-200 bg-gray-50 text-center peer-checked:bg-green-600 peer-checked:text-white peer-checked:border-green-600 transition">
                                <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                <p class="text-xs font-medium">Stok Masuk</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="out" class="peer sr-only"
                                onchange="toggleNotes()">
                            <div
                                class="p-3 rounded-xl border border-gray-200 bg-gray-50 text-center peer-checked:bg-red-600 peer-checked:text-white peer-checked:border-red-600 transition">
                                <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 12H4" />
                                </svg>
                                <p class="text-xs font-medium">Stok Keluar</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="adjustment" class="peer sr-only"
                                onchange="toggleNotes()">
                            <div
                                class="p-3 rounded-xl border border-gray-200 bg-gray-50 text-center peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 transition">
                                <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                </svg>
                                <p class="text-xs font-medium">Adjustment</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="transfer" class="peer sr-only"
                                onchange="toggleNotes()">
                            <div
                                class="p-3 rounded-xl border border-gray-200 bg-gray-50 text-center peer-checked:bg-purple-600 peer-checked:text-white peer-checked:border-purple-600 transition">
                                <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <p class="text-xs font-medium">Transfer</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Quantity --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah *
                    </label>
                    <input type="number" name="quantity" id="quantity-input" min="1" step="1"
                        required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Masukkan jumlah">
                </div>

                {{-- Notes --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan
                    </label>
                    <textarea name="notes" id="notes-input" rows="3"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Catatan tambahan (opsional)"></textarea>
                </div>

                {{-- Reference --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Referensi
                    </label>
                    <input type="text" name="reference" id="reference-input"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="No. PO / SO / DO (opsional)">
                </div>

                {{-- Submit Buttons --}}
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('inventory.movements.index') }}"
                        class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-center text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-medium transition">
                        💾 Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Barcode Scanner Modal --}}
    <div id="barcode-scanner-modal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">
            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <h4 class="font-semibold text-gray-900">📷 Scan Barcode</h4>
                <button onclick="closeBarcodeScanner()" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            {{-- Camera Viewfinder --}}
            <div class="relative bg-black aspect-square">
                <video id="barcode-video" class="w-full h-full object-cover" autoplay playsinline muted></video>

                {{-- Scan overlay --}}
                <div class="absolute inset-4 border-2 border-blue-500/50 rounded-2xl">
                    {{-- Corner markers --}}
                    <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-blue-400 rounded-tl-lg">
                    </div>
                    <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-blue-400 rounded-tr-lg">
                    </div>
                    <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-blue-400 rounded-bl-lg">
                    </div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-blue-400 rounded-br-lg">
                    </div>

                    {{-- Scan line --}}
                    <div class="absolute left-2 right-2 h-0.5 bg-blue-400/70 rounded"
                        style="top:50%;animation:scanline 1.8s ease-in-out infinite"></div>
                </div>

                {{-- Status --}}
                <div id="scanner-status" class="absolute bottom-3 left-0 right-0 text-center">
                    <span class="text-xs text-white/70 bg-black/50 px-3 py-1.5 rounded-full">
                        Arahkan kamera ke barcode...
                    </span>
                </div>
            </div>

            {{-- Manual Input --}}
            <div class="px-4 py-3 border-t border-gray-100">
                <p class="text-xs text-gray-500 mb-2">Atau ketik manual:</p>
                <div class="flex gap-2">
                    <input type="text" id="manual-barcode" placeholder="Ketik barcode..."
                        class="flex-1 px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-gray-900 text-sm focus:outline-none"
                        onkeypress="if(event.key==='Enter') submitManualBarcode()">
                    <button onclick="submitManualBarcode()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-sm font-medium transition">
                        Cari
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes scanline {

            0%,
            100% {
                transform: translateY(-100%);
            }

            50% {
                transform: translateY(100%);
            }
        }
    </style>

    <script src="https://unpkg.com/@zxing/library@latest"></script>
    <script>
        let codeReader;
        let selectedInterval;

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts();
        });

        // Load products dropdown
        function loadProducts() {
            fetch(`/api/products?tenant_id={{ auth()->user()->tenant_id }}`)
                .then(res => res.json())
                .then(data => {
                    const select = document.getElementById('product-select');
                    data.forEach(p => {
                        const option = document.createElement('option');
                        option.value = p.id;
                        option.textContent = `${p.name} (${p.sku})`;
                        option.dataset.barcode = p.barcode || '';
                        select.appendChild(option);
                    });
                });
        }

        // Barcode scanner functions
        function openBarcodeScanner() {
            document.getElementById('barcode-scanner-modal').classList.remove('hidden');
            startScanner();
        }

        function closeBarcodeScanner() {
            document.getElementById('barcode-scanner-modal').classList.add('hidden');
            stopScanner();
        }

        function startScanner() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                Dialog.warning('Browser Anda tidak mendukung akses kamera. Gunakan browser modern dengan HTTPS.');
                return;
            }

            const videoElement = document.getElementById('barcode-video');

            try {
                codeReader = new ZXing.BrowserMultiFormatReader();

                codeReader.decodeFromVideoDevice(null, videoElement, (result, err) => {
                    if (result) {
                        const barcode = result.text;
                        handleBarcodeScanned(barcode);
                    }
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
                Dialog.warning('Gagal menginisialisasi scanner: ' + error.message);
                stopScanner();
            }
        }

        function handleCameraError(error) {
            if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                Dialog.warning('Akses kamera ditolak. Mohon izinkan akses kamera di browser settings.');
            } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
                Dialog.warning('Tidak ada kamera yang ditemukan di perangkat Anda.');
            } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
                Dialog.warning('Kamera sedang digunakan oleh aplikasi lain.');
            } else {
                Dialog.warning('Gagal mengakses kamera: ' + error.message);
            }
        }

        function stopScanner() {
            if (codeReader) {
                codeReader.reset();
            }
        }

        function handleBarcodeScanned(barcode) {
            // Stop scanning
            stopScanner();

            // Close modal
            closeBarcodeScanner();

            // Lookup product
            lookupProductByBarcode(barcode);
        }

        function submitManualBarcode() {
            const barcode = document.getElementById('manual-barcode').value.trim();
            if (barcode) {
                lookupProductByBarcode(barcode);
            }
        }

        function lookupProductByBarcode(barcode) {
            fetch(`{{ route('inventory.movements.lookup-barcode') }}?barcode=${encodeURIComponent(barcode)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Select product in dropdown
                        const select = document.getElementById('product-select');
                        select.value = data.data.id;

                        // Show product info
                        document.getElementById('product-name').textContent = data.data.name;
                        document.getElementById('product-sku').textContent = `SKU: ${data.data.sku}`;
                        document.getElementById('product-info').classList.remove('hidden');

                        // Auto-focus to quantity
                        document.getElementById('quantity-input').focus();

                        // Play success sound
                        playSuccessSound();
                    } else {
                        Dialog.warning('Produk tidak ditemukan!');
                        document.getElementById('barcode-input').value = '';
                        document.getElementById('barcode-input').focus();
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    Dialog.warning('Terjadi kesalahan saat mencari produk');
                });
        }

        function loadProductStock() {
            const productId = document.getElementById('product-select').value;
            const warehouseId = document.getElementById('warehouse-select').value;

            if (!productId || !warehouseId) return;

            fetch(`{{ route('inventory.movements.stock') }}?product_id=${productId}&warehouse_id=${warehouseId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('current-stock-value').textContent = data.data.quantity;
                        document.getElementById('stock-unit').textContent = document.getElementById('product-select')
                            .selectedOptions[0]?.text.split('(').pop()?.replace(')', '') || 'pcs';
                        document.getElementById('current-stock-card').classList.remove('hidden');
                    }
                });
        }

        function toggleNotes() {
            const type = document.querySelector('input[name="type"]:checked').value;
            const notesInput = document.getElementById('notes-input');
            const referenceInput = document.getElementById('reference-input');

            if (type === 'in') {
                notesInput.placeholder = 'Contoh: Pembelian dari supplier, Retur penjualan, dll';
                referenceInput.placeholder = 'No. PO / Invoice / DO';
            } else if (type === 'out') {
                notesInput.placeholder = 'Contoh: Penjualan, Damage/Expired, dll';
                referenceInput.placeholder = 'No. SO / Order / Request';
            } else {
                notesInput.placeholder = 'Alasan adjustment';
                referenceInput.placeholder = 'No. Reference (opsional)';
            }
        }

        function playSuccessSound() {
            // Optional: Add beep sound for successful scan
            const audioContext = new(window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        }

        // Handle Enter key in barcode input
        document.getElementById('barcode-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const barcode = this.value.trim();
                if (barcode) {
                    lookupProductByBarcode(barcode);
                }
            }
        });
    </script>
</x-app-layout>
