<x-app-layout>
    <x-slot name="header">Scan Material — {{ $workOrder->number }}</x-slot>

    @php
        $totalMaterials = count($requiredMaterials);
        $scannedMaterials = 0;
        foreach ($requiredMaterials as $m) {
            if ($m['quantity_scanned'] >= $m['quantity_required']) {
                $scannedMaterials++;
            }
        }
        $pct = $totalMaterials > 0 ? round(($scannedMaterials / $totalMaterials) * 100) : 0;
    @endphp

    <div class="max-w-3xl mx-auto space-y-4">

        {{-- Header Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="font-mono font-bold text-gray-900">{{ $workOrder->number }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Produk: {{ $workOrder->product?->name ?? '-' }}
                        &bull; Target: {{ number_format($workOrder->target_quantity, 2) }} {{ $workOrder->unit }}
                    </p>
                </div>
                @php $sc = ['pending'=>'amber','in_progress'=>'blue','completed'=>'green','cancelled'=>'gray'][$workOrder->status] ?? 'gray'; @endphp
                <span
                    class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700 $sc }}-500/20 $sc }}-400">
                    {{ ucfirst($workOrder->status) }}
                </span>
            </div>
            {{-- Progress bar --}}
            <div class="flex items-center gap-3">
                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-green-500 rounded-full transition-all duration-300"
                        style="width: {{ $pct }}%"></div>
                </div>
                <span class="text-xs font-semibold text-gray-700 whitespace-nowrap">
                    {{ $scannedMaterials }} / {{ $totalMaterials }} material
                </span>
            </div>
        </div>

        {{-- Barcode Scanner Card --}}
        <div class="bg-blue-50 rounded-2xl border border-blue-200 p-5">
            <p class="text-sm font-semibold text-blue-900 mb-3">Scan Barcode Material</p>
            <div class="flex gap-2">
                <input type="text" id="barcode-input"
                    placeholder="Scan barcode material atau ketik SKU, lalu Enter..."
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

        {{-- Materials List --}}
        <form method="POST" action="{{ route('manufacturing.work-orders.consume-scanned', $workOrder) }}"
            id="consume-form">
            @csrf
            <div class="space-y-2" id="materials-list">
                @foreach ($requiredMaterials as $productId => $material)
                    @php
                        $isComplete = $material['quantity_scanned'] >= $material['quantity_required'];
                        $borderCls = $isComplete ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-white';
                    @endphp
                    <div id="material-row-{{ $productId }}"
                        class="rounded-2xl border {{ $borderCls }} p-4 transition-all duration-300"
                        data-product-id="{{ $productId }}" data-barcode="{{ $material['barcode'] }}"
                        data-required="{{ $material['quantity_required'] }}">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-sm text-gray-900 truncate">
                                    {{ $material['product']->name ?? '-' }}
                                </p>
                                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                    <span class="text-xs font-mono text-gray-400">
                                        {{ $material['product']->sku ?? '-' }}
                                    </span>
                                    @if ($material['barcode'])
                                        <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-mono">
                                            Barcode: {{ $material['barcode'] }}
                                        </span>
                                    @endif
                                    @if ($isComplete)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-green-100 text-green-700">
                                            ✓ Complete
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs text-gray-500">Diperlukan</p>
                                <p class="font-bold text-gray-900">
                                    {{ number_format($material['quantity_required'], 2) }} {{ $material['unit'] }}
                                </p>
                                <input type="hidden" name="scanned_materials[{{ $productId }}][product_id]"
                                    value="{{ $productId }}">
                                <input type="hidden" name="scanned_materials[{{ $productId }}][quantity]"
                                    id="scanned-qty-{{ $productId }}" value="{{ $material['quantity_required'] }}">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Submit button --}}
            <div class="mt-4 flex items-center justify-between">
                <a href="{{ route('production.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl shadow transition disabled:opacity-50 disabled:cursor-not-allowed"
                    id="submit-btn" {{ $scannedMaterials < $totalMaterials ? 'disabled' : '' }}>
                    Konsumsi Material ({{ $scannedMaterials }}/{{ $totalMaterials }})
                </button>
            </div>
        </form>

        {{-- Camera Scanner Modal --}}
        <div id="barcode-scanner-modal"
            class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80">
            <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900 text-sm">Scan Barcode Material</h3>
                    <button onclick="closeBarcodeScanner()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <video id="barcode-video" class="w-full rounded-xl bg-black aspect-video"></video>
                    <p class="text-xs text-center text-gray-500 mt-3">Arahkan kamera ke barcode
                        material</p>
                    <div class="mt-3 border-t border-gray-100 pt-3">
                        <p class="text-xs text-gray-500 mb-1">Atau ketik manual:</p>
                        <div class="flex gap-2">
                            <input type="text" id="manual-barcode" placeholder="Barcode / SKU"
                                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button onclick="submitManualBarcode()"
                                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
        <script>
            let codeReader = null;
            const materialRows = document.querySelectorAll('#materials-list [data-product-id]');
            const barcodeMap = {}; // barcode → product_id

            materialRows.forEach(row => {
                const bc = row.dataset.barcode?.toLowerCase().trim();
                if (bc) barcodeMap[bc] = row.dataset.productId;
            });

            function handleBarcodeScanned(barcode) {
                stopScanner();
                closeBarcodeScanner();
                document.getElementById('barcode-input').value = barcode;
                processBarcode(barcode);
            }

            function processBarcode(barcode) {
                const key = barcode.toLowerCase().trim();
                const feedback = document.getElementById('scan-feedback');
                const matchedProductId = barcodeMap[key];

                if (matchedProductId) {
                    const row = document.getElementById(`material-row-${matchedProductId}`);
                    const required = parseFloat(row.dataset.required);
                    const qtyInput = document.getElementById(`scanned-qty-${matchedProductId}`);

                    // Highlight the row
                    row.classList.add('ring-2', 'ring-blue-500');
                    setTimeout(() => row.classList.remove('ring-2', 'ring-blue-500'), 1500);

                    // Auto-set quantity to required (for now - could be enhanced to increment)
                    qtyInput.value = required;

                    // Update UI to show complete
                    row.classList.add('border-green-300', 'bg-green-50');
                    row.classList.remove('border-gray-200', 'bg-white');

                    if (!row.querySelector('[class*="green-100"]')) {
                        const badge = document.createElement('span');
                        badge.className =
                            'px-1.5 py-0.5 rounded text-[10px] bg-green-100 text-green-700 ml-2';
                        badge.textContent = '✓ Complete';
                        row.querySelector('.flex-wrap').appendChild(badge);
                    }

                    feedback.textContent = `Material discan: ${row.querySelector('.font-medium')?.innerText}`;
                    feedback.className = 'text-xs mt-2 text-green-600 min-h-[16px]';

                    row.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    playSuccessSound();
                    updateSubmitButton();
                } else {
                    // Fallback: lookup via API
                    fetch(`{{ route('inventory.movements.lookup-barcode') }}?barcode=${encodeURIComponent(barcode)}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                const productId = data.data.id;
                                const apiRow = document.querySelector(`[data-product-id="${productId}"]`);
                                if (apiRow) {
                                    const required = parseFloat(apiRow.dataset.required);
                                    const qtyInput = document.getElementById(`scanned-qty-${productId}`);
                                    qtyInput.value = required;

                                    apiRow.classList.add('ring-2', 'ring-blue-500');
                                    setTimeout(() => apiRow.classList.remove('ring-2', 'ring-blue-500'), 1500);
                                    apiRow.scrollIntoView({
                                        behavior: 'smooth',
                                        block: 'center'
                                    });
                                    feedback.textContent = `Material ditemukan: ${data.data.name}`;
                                    feedback.className = 'text-xs mt-2 text-green-600 min-h-[16px]';
                                    playSuccessSound();
                                    updateSubmitButton();
                                } else {
                                    feedback.textContent = 'Material tidak ada di BOM work order ini.';
                                    feedback.className = 'text-xs mt-2 text-red-500 min-h-[16px]';
                                }
                            } else {
                                feedback.textContent = 'Barcode tidak dikenali.';
                                feedback.className = 'text-xs mt-2 text-red-500 min-h-[16px]';
                            }
                        })
                        .catch(() => {
                            feedback.textContent = 'Gagal mencari material.';
                            feedback.className = 'text-xs mt-2 text-red-500 min-h-[16px]';
                        });
                }

                setTimeout(() => {
                    document.getElementById('barcode-input').value = '';
                    document.getElementById('barcode-input').focus();
                }, 200);
            }

            function updateSubmitButton() {
                let scannedCount = 0;
                materialRows.forEach(row => {
                    const required = parseFloat(row.dataset.required);
                    const productId = row.dataset.productId;
                    const qtyInput = document.getElementById(`scanned-qty-${productId}`);
                    const scanned = parseFloat(qtyInput.value) || 0;
                    if (scanned >= required) scannedCount++;
                });

                const total = materialRows.length;
                const btn = document.getElementById('submit-btn');
                btn.textContent = `Konsumsi Material (${scannedCount}/${total})`;
                btn.disabled = scannedCount < total;
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
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    Dialog.warning('Browser Anda tidak mendukung akses kamera. Gunakan browser modern dengan HTTPS.');
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
