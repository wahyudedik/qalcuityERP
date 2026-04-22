<?php
    $componentId = 'bs_' . Str::random(8);
?>

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'onScan' => 'onBarcodeScan',
    'buttonLabel' => 'Scan Barcode',
    'buttonClass' => '',
    'inputPlaceholder' => 'Ketik atau scan barcode...',
    'showManualInput' => true,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'onScan' => 'onBarcodeScan',
    'buttonLabel' => 'Scan Barcode',
    'buttonClass' => '',
    'inputPlaceholder' => 'Ketik atau scan barcode...',
    'showManualInput' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<div class="barcode-scanner-wrapper" data-scanner-id="<?php echo e($componentId); ?>">

    <button type="button" onclick="openBarcodeScanner_<?php echo e($componentId); ?>()"
        class="inline-flex items-center gap-2 min-h-[44px] px-4 py-2.5 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white text-sm font-medium rounded-xl transition select-none touch-manipulation <?php echo e($buttonClass); ?>">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M3 9V6a1 1 0 011-1h3M3 15v3a1 1 0 001 1h3m11-4v3a1 1 0 01-1 1h-3m4-11h-3a1 1 0 00-1 1v3M9 3H6a1 1 0 00-1 1v3m0 6v3a1 1 0 001 1h3m6-10h3a1 1 0 011 1v3" />
        </svg>
        <span><?php echo e($buttonLabel); ?></span>
    </button>

    
    <?php if($showManualInput): ?>
        <div class="flex gap-2 mt-2">
            <input type="text" id="manualBarcode_<?php echo e($componentId); ?>" placeholder="<?php echo e($inputPlaceholder); ?>"
                autocomplete="off"
                class="flex-1 h-12 text-lg bg-gray-800 border border-gray-700 rounded-xl px-4 text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 touch-manipulation"
                onkeydown="if(event.key==='Enter'){event.preventDefault();submitManualBarcode_<?php echo e($componentId); ?>();}">
            <button type="button" onclick="submitManualBarcode_<?php echo e($componentId); ?>()"
                class="h-12 px-5 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white text-sm font-semibold rounded-xl transition touch-manipulation min-w-[56px]">
                OK
            </button>
        </div>
    <?php endif; ?>

</div>


<div id="scannerModal_<?php echo e($componentId); ?>"
    class="scanner-modal-<?php echo e($componentId); ?> fixed inset-0 bg-gray-900/95 z-[9999] hidden items-center justify-center p-4"
    onclick="(function(e){if(e.target===e.currentTarget)closeBarcodeScanner_<?php echo e($componentId); ?>();})(event)">

    <div class="bg-gray-900 rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl border border-gray-800">

        
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-800">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M3 9V6a1 1 0 011-1h3M3 15v3a1 1 0 001 1h3m11-4v3a1 1 0 01-1 1h-3m4-11h-3a1 1 0 00-1 1v3M9 3H6a1 1 0 00-1 1v3m0 6v3a1 1 0 001 1h3m6-10h3a1 1 0 011 1v3" />
                </svg>
                <p class="text-sm font-semibold text-white">Scan Barcode via Kamera</p>
            </div>
            <button type="button" onclick="closeBarcodeScanner_<?php echo e($componentId); ?>()"
                class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition touch-manipulation">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        
        <div class="relative bg-black" style="aspect-ratio:4/3">
            <video id="scannerVideo_<?php echo e($componentId); ?>" autoplay playsinline muted class="w-full h-full object-cover">
            </video>

            
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="relative w-56 h-36">
                    
                    <div class="absolute top-0 left-0 w-7 h-7 border-t-2 border-l-2 border-blue-400 rounded-tl"></div>
                    <div class="absolute top-0 right-0 w-7 h-7 border-t-2 border-r-2 border-blue-400 rounded-tr"></div>
                    <div class="absolute bottom-0 left-0 w-7 h-7 border-b-2 border-l-2 border-blue-400 rounded-bl">
                    </div>
                    <div class="absolute bottom-0 right-0 w-7 h-7 border-b-2 border-r-2 border-blue-400 rounded-br">
                    </div>
                    
                    <div class="scanner-line-<?php echo e($componentId); ?> absolute left-1 right-1 h-0.5 bg-blue-400/80 rounded shadow-[0_0_6px_#60a5fa]"
                        style="top:10%"></div>
                </div>
            </div>

            
            <div id="scannerStatus_<?php echo e($componentId); ?>" class="absolute bottom-3 left-0 right-0 text-center">
                <span class="text-xs text-white/70 bg-black/50 px-3 py-1.5 rounded-full">
                    Arahkan kamera ke barcode...
                </span>
            </div>
        </div>

        
        <div class="px-4 pt-2.5 pb-1">
            <p class="text-[11px] text-gray-600 text-center">
                Format: EAN-13, EAN-8, Code 128, Code 39, QR Code, UPC
            </p>
        </div>

        
        <div class="px-4 pt-1.5 pb-4 border-t border-gray-800 mt-2">
            <p class="text-xs text-gray-500 mb-2">Atau ketik barcode secara manual:</p>
            <div class="flex gap-2">
                <input type="text" id="modalManualBarcode_<?php echo e($componentId); ?>" placeholder="Masukkan barcode..."
                    autocomplete="off"
                    class="flex-1 h-11 bg-gray-800 border border-gray-700 rounded-xl px-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 touch-manipulation"
                    onkeydown="if(event.key==='Enter'){event.preventDefault();submitModalManualBarcode_<?php echo e($componentId); ?>();}">
                <button type="button" onclick="submitModalManualBarcode_<?php echo e($componentId); ?>()"
                    class="h-11 px-4 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white text-sm font-medium rounded-xl transition touch-manipulation">
                    Cari
                </button>
            </div>
        </div>

    </div>
</div>


<style>
    @keyframes scanLine_<?php echo e($componentId); ?> {
        0% {
            top: 10%;
        }

        50% {
            top: 82%;
        }

        100% {
            top: 10%;
        }
    }

    .scanner-line-<?php echo e($componentId); ?> {
        animation: scanLine_<?php echo e($componentId); ?> 1.8s ease-in-out infinite;
    }

    #scannerModal_<?php echo e($componentId); ?>.flex {
        display: flex !important;
    }

    /* Prevent body scroll when modal is open */
    body.scanner-open-<?php echo e($componentId); ?> {
        overflow: hidden;
    }

    /* Responsive: fullscreen on small screens */
    @media (max-width: 480px) {
        #scannerModal_<?php echo e($componentId); ?> .bg-gray-900.rounded-2xl {
            border-radius: 0;
            max-width: 100%;
            height: 100dvh;
            display: flex;
            flex-direction: column;
        }

        #scannerModal_<?php echo e($componentId); ?> .relative.bg-black {
            flex: 1;
            aspect-ratio: unset;
        }

        #scannerModal_<?php echo e($componentId); ?> video {
            height: 100%;
        }
    }
</style>


<script>
    (function() {
        const CID = '<?php echo e($componentId); ?>';
        const CALLBACK = '<?php echo e($onScan); ?>';

        let _stream = null;
        let _detector = null;
        let _rafId = null;
        let _zxReader = null;
        let _scanning = false;

        // ── Helpers ──────────────────────────────────────────────────────────────

        function modal() {
            return document.getElementById('scannerModal_' + CID);
        }

        function video() {
            return document.getElementById('scannerVideo_' + CID);
        }

        function status() {
            return document.getElementById('scannerStatus_' + CID);
        }

        function setStatus(msg, type) {
            const el = status();
            if (!el) return;
            const color = type === 'error' ? 'text-red-400' :
                type === 'success' ? 'text-green-400' :
                'text-white/70';
            el.innerHTML = `<span class="text-xs ${color} bg-black/50 px-3 py-1.5 rounded-full">${msg}</span>`;
        }

        function fireCallback(value) {
            if (typeof window[CALLBACK] === 'function') {
                window[CALLBACK](value);
            } else {
                console.warn('[BarcodeScanner] Callback not found:', CALLBACK);
            }
        }

        // ── Open / Close ─────────────────────────────────────────────────────────

        window['openBarcodeScanner_' + CID] = async function() {
            const m = modal();
            if (!m) return;
            m.classList.remove('hidden');
            m.classList.add('flex');
            document.body.classList.add('scanner-open-' + CID);
            setStatus('Arahkan kamera ke barcode...');
            await _startCamera();
        };

        window['closeBarcodeScanner_' + CID] = function() {
            _stopCamera();
            const m = modal();
            if (m) {
                m.classList.add('hidden');
                m.classList.remove('flex');
            }
            document.body.classList.remove('scanner-open-' + CID);
            // Clear modal input
            const inp = document.getElementById('modalManualBarcode_' + CID);
            if (inp) inp.value = '';
        };

        // ── Camera ───────────────────────────────────────────────────────────────

        async function _startCamera() {
            try {
                _stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment',
                        width: {
                            ideal: 1280
                        },
                        height: {
                            ideal: 720
                        },
                    }
                });
                const v = video();
                v.srcObject = _stream;
                await v.play();

                // Choose detection engine
                if ('BarcodeDetector' in window) {
                    await _initBarcodeDetector();
                    _startDetectionLoop();
                } else {
                    _loadZxing();
                }
            } catch (err) {
                setStatus('Kamera tidak dapat diakses: ' + err.message, 'error');
            }
        }

        function _stopCamera() {
            _scanning = false;
            if (_rafId) {
                cancelAnimationFrame(_rafId);
                _rafId = null;
            }
            if (_zxReader) {
                try {
                    _zxReader.reset();
                } catch {}
                _zxReader = null;
            }
            if (_stream) {
                _stream.getTracks().forEach(t => t.stop());
                _stream = null;
            }
            const v = video();
            if (v) v.srcObject = null;
        }

        // ── BarcodeDetector API ──────────────────────────────────────────────────

        async function _initBarcodeDetector() {
            try {
                const formats = await BarcodeDetector.getSupportedFormats();
                _detector = new BarcodeDetector({
                    formats
                });
            } catch {
                _detector = new BarcodeDetector({
                    formats: ['ean_13', 'ean_8', 'code_128', 'code_39', 'qr_code', 'upc_a', 'upc_e',
                        'aztec', 'data_matrix', 'pdf417'
                    ]
                });
            }
        }

        function _startDetectionLoop() {
            _scanning = true;
            const v = video();
            setStatus('Arahkan kamera ke barcode...');

            async function detect() {
                if (!_scanning) return;
                if (v.readyState === v.HAVE_ENOUGH_DATA) {
                    try {
                        const barcodes = await _detector.detect(v);
                        if (barcodes.length > 0) {
                            _scanning = false;
                            const code = barcodes[0].rawValue;
                            setStatus('✓ Barcode terdeteksi: ' + code, 'success');
                            _onScanSuccess(code);
                            return;
                        }
                    } catch {
                        /* continue */ }
                }
                _rafId = requestAnimationFrame(detect);
            }
            _rafId = requestAnimationFrame(detect);
        }

        // ── ZXing Fallback ───────────────────────────────────────────────────────

        function _loadZxing() {
            setStatus('Memuat scanner fallback...');
            if (window.ZXing) {
                _startZxing();
                return;
            }
            const s = document.createElement('script');
            s.src = 'https://unpkg.com/@zxing/library@0.21.3/umd/index.min.js';
            s.onload = () => _startZxing();
            s.onerror = () => setStatus('Scanner tidak tersedia. Gunakan input manual.', 'error');
            document.head.appendChild(s);
        }

        function _startZxing() {
            try {
                const v = video();
                setStatus('Arahkan kamera ke barcode...');
                const reader = new ZXing.BrowserMultiFormatReader();
                _zxReader = reader;

                reader.decodeFromVideoElement(v, (result, err) => {
                    if (result && _scanning) {
                        _scanning = false;
                        const code = result.getText();
                        setStatus('✓ Barcode terdeteksi: ' + code, 'success');
                        reader.reset();
                        _zxReader = null;
                        _onScanSuccess(code);
                    }
                });
                _scanning = true;
            } catch (e) {
                setStatus('Scanner error: ' + e.message, 'error');
            }
        }

        // ── On Successful Scan ───────────────────────────────────────────────────

        function _onScanSuccess(code) {
            // Haptic feedback
            if ('vibrate' in navigator) navigator.vibrate([100, 50, 100]);
            // Close modal
            window['closeBarcodeScanner_' + CID]();
            // Fire callback
            fireCallback(code);
        }

        // ── Manual Submit (standalone input) ─────────────────────────────────────

        window['submitManualBarcode_' + CID] = function() {
            const inp = document.getElementById('manualBarcode_' + CID);
            if (!inp) return;
            const code = inp.value.trim();
            if (!code) return;
            inp.value = '';
            fireCallback(code);
        };

        // ── Manual Submit (inside modal) ─────────────────────────────────────────

        window['submitModalManualBarcode_' + CID] = function() {
            const inp = document.getElementById('modalManualBarcode_' + CID);
            if (!inp) return;
            const code = inp.value.trim();
            if (!code) return;
            inp.value = '';
            window['closeBarcodeScanner_' + CID]();
            fireCallback(code);
        };

        // ── Keyboard: Escape closes modal ────────────────────────────────────────
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const m = modal();
                if (m && !m.classList.contains('hidden')) {
                    window['closeBarcodeScanner_' + CID]();
                }
            }
        });

    })();
</script>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\components\barcode-scanner.blade.php ENDPATH**/ ?>