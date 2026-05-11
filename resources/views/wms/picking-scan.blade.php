<x-app-layout>
    <x-slot name="header">Scan & Pick — {{ $pickingList->number }}</x-slot>

    @php
        $totalItems = $pickingList->items->count();
        $pickedItems = $pickingList->items->whereIn('status', ['picked', 'short'])->count();
        $pct = $totalItems > 0 ? round(($pickedItems / $totalItems) * 100) : 0;
    @endphp

    <div class="max-w-2xl mx-auto space-y-4">

        {{-- Header Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="font-mono font-bold text-gray-900">{{ $pickingList->number }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Gudang: {{ $pickingList->warehouse?->name ?? '-' }}
                        @if ($pickingList->assignee)
                            &bull; Picker: {{ $pickingList->assignee?->name }}
                        @endif
                    </p>
                </div>
                @php $sc = ['pending'=>'amber','in_progress'=>'blue','completed'=>'green','cancelled'=>'gray'][$pickingList->status] ?? 'gray'; @endphp
                <span
                    class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700 $sc }}-500/20 $sc }}-400">
                    {{ ucfirst(str_replace('_', ' ', $pickingList->status)) }}
                </span>
            </div>
            {{-- Progress bar --}}
            <div class="flex items-center gap-3">
                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-green-500 rounded-full transition-all duration-300"
                        style="width: {{ $pct }}%"></div>
                </div>
                <span class="text-xs font-semibold text-gray-700 whitespace-nowrap">
                    {{ $pickedItems }} / {{ $totalItems }} item
                </span>
            </div>
        </div>

        {{-- Barcode Scanner Card --}}
        <div class="bg-blue-50 rounded-2xl border border-blue-200 p-5">
            <p class="text-sm font-semibold text-blue-900 mb-3">Scan Barcode Produk</p>
            <div class="flex gap-2">
                <input type="text" id="barcode-input" placeholder="Scan barcode atau ketik SKU, lalu Enter..."
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

        {{-- Items List --}}
        <div class="space-y-2" id="items-list">
            @foreach ($pickingList->items as $item)
                @php
                    $isPicked = in_array($item->status, ['picked', 'short']);
                    $borderCls = $isPicked ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-white';
                    $ic = ['pending' => 'amber', 'picked' => 'green', 'short' => 'red'][$item->status] ?? 'gray';
                @endphp
                <div id="item-row-{{ $item->id }}"
                    class="rounded-2xl border {{ $borderCls }} p-4 transition-all duration-300"
                    data-product-id="{{ $item->product_id }}"
                    data-barcode="{{ $item->product?->barcode ?? ($item->product?->sku ?? '') }}"
                    data-item-id="{{ $item->id }}">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm text-gray-900 truncate">
                                {{ $item->product?->name ?? '-' }}
                            </p>
                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                <span class="text-xs font-mono text-gray-400">
                                    {{ $item->product?->sku ?? '-' }}
                                </span>
                                @if ($item->bin)
                                    <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 font-mono">
                                        Bin: {{ $item->bin?->code }}
                                    </span>
                                @endif
                                <span
                                    class="px-1.5 py-0.5 rounded text-[10px] bg-{{ $ic }}-100 text-{{ $ic }}-700 $ic }}-500/20 $ic }}-400">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs text-gray-500">Diminta</p>
                            <p class="font-bold text-gray-900">
                                {{ number_format($item->quantity_requested, 0) }}</p>
                            @if ($isPicked)
                                <p class="text-xs text-green-600">
                                    Diambil: {{ number_format($item->quantity_picked, 0) }}
                                </p>
                            @endif
                        </div>
                    </div>
                    {{-- Inline confirm form (visible when pending) --}}
                    @if ($item->status === 'pending')
                        @canmodule('wms', 'edit')
                        <form method="POST" action="{{ route('wms.picking.confirm', $item) }}"
                            class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2 confirm-form"
                            id="confirm-form-{{ $item->id }}">
                            @csrf
                            @method('PATCH')
                            <input type="number" name="quantity_picked" id="qty-input-{{ $item->id }}"
                                value="{{ $item->quantity_requested }}" min="0" step="1"
                                class="w-24 px-3 py-1.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <button type="submit"
                                class="flex-1 px-4 py-1.5 text-sm bg-green-600 hover:bg-green-700 text-white rounded-xl font-medium transition">
                                Konfirmasi Pick
                            </button>
                        </form>
                        @endcanmodule
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Back button --}}
        <div class="pb-6">
            <a href="{{ route('wms.picking') }}"
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke Picking List
            </a>
        </div>
    </div>

    {{-- Camera Scanner Modal --}}
    <div id="barcode-scanner-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900 text-sm">Scan Barcode Produk</h3>
                <button onclick="closeBarcodeScanner()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-4">
                <video id="barcode-video" class="w-full rounded-xl bg-black aspect-video"></video>
                <p class="text-xs text-center text-gray-500 mt-3">
                    Arahkan kamera ke barcode produk
                </p>
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

    @push('scripts')
        <script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
        <script>
            let codeReader = null;

            // Build a product→item map from data attributes for fast lookup
            const itemRows = document.querySelectorAll('#items-list [data-item-id]');
            const barcodeMap = {}; // barcode/sku → item DOM element
            itemRows.forEach(row => {
                const bc = row.dataset.barcode?.toLowerCase().trim();
                if (bc) barcodeMap[bc] = row;
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

                // Try direct match in item map
                const matchedRow = barcodeMap[key];

                if (matchedRow) {
                    const itemId = matchedRow.dataset.itemId;
                    const status = matchedRow.querySelector('[class*="rounded"]')?.innerText?.toLowerCase().trim();

                    // Highlight the row
                    matchedRow.classList.add('ring-2', 'ring-blue-500');
                    setTimeout(() => matchedRow.classList.remove('ring-2', 'ring-blue-500'), 1500);

                    // Auto-focus qty input if form is visible
                    const qtyInput = document.getElementById(`qty-input-${itemId}`);
                    if (qtyInput) {
                        qtyInput.focus();
                        qtyInput.select();
                        feedback.textContent = `Produk ditemukan: ${matchedRow.querySelector('.font-medium')?.innerText}`;
                        feedback.className = 'text-xs mt-2 text-green-600 min-h-[16px]';
                    } else {
                        feedback.textContent = `Item sudah di-pick.`;
                        feedback.className = 'text-xs mt-2 text-gray-500 min-h-[16px]';
                    }

                    // Scroll to item
                    matchedRow.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    playSuccessSound();
                } else {
                    // Fallback: lookup via API in case barcode differs from stored data
                    fetch(`{{ route('inventory.movements.lookup-barcode') }}?barcode=${encodeURIComponent(barcode)}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                const productId = data.data.id;
                                const apiRow = document.querySelector(`[data-product-id="${productId}"]`);
                                if (apiRow) {
                                    apiRow.classList.add('ring-2', 'ring-blue-500');
                                    setTimeout(() => apiRow.classList.remove('ring-2', 'ring-blue-500'), 1500);
                                    const qtyInput = document.getElementById(`qty-input-${apiRow.dataset.itemId}`);
                                    if (qtyInput) {
                                        qtyInput.focus();
                                        qtyInput.select();
                                    }
                                    apiRow.scrollIntoView({
                                        behavior: 'smooth',
                                        block: 'center'
                                    });
                                    feedback.textContent = `Produk ditemukan: ${data.data.name}`;
                                    feedback.className = 'text-xs mt-2 text-green-600 min-h-[16px]';
                                    playSuccessSound();
                                } else {
                                    feedback.textContent = 'Produk tidak ada di picking list ini.';
                                    feedback.className = 'text-xs mt-2 text-red-500 min-h-[16px]';
                                }
                            } else {
                                feedback.textContent = 'Barcode tidak dikenali.';
                                feedback.className = 'text-xs mt-2 text-red-500 min-h-[16px]';
                            }
                        })
                        .catch(() => {
                            feedback.textContent = 'Gagal mencari produk.';
                            feedback.className = 'text-xs mt-2 text-red-500 min-h-[16px]';
                        });
                }

                // Clear input
                setTimeout(() => {
                    document.getElementById('barcode-input').value = '';
                    document.getElementById('barcode-input').focus();
                }, 200);
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

            // Manual barcode Enter key
            document.getElementById('manual-barcode').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitManualBarcode();
                }
            });
        </script>
    @endpush
</x-app-layout>
