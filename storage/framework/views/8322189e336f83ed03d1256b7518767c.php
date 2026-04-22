<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> Scan Material — <?php echo e($workOrder->number); ?> <?php $__env->endSlot(); ?>

    <?php
        $totalMaterials = count($requiredMaterials);
        $scannedMaterials = 0;
        foreach ($requiredMaterials as $m) {
            if ($m['quantity_scanned'] >= $m['quantity_required']) {
                $scannedMaterials++;
            }
        }
        $pct = $totalMaterials > 0 ? round(($scannedMaterials / $totalMaterials) * 100) : 0;
    ?>

    <div class="max-w-3xl mx-auto space-y-4">

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="font-mono font-bold text-gray-900 dark:text-white"><?php echo e($workOrder->number); ?></p>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                        Produk: <?php echo e($workOrder->product->name ?? '-'); ?>

                        &bull; Target: <?php echo e(number_format($workOrder->target_quantity, 2)); ?> <?php echo e($workOrder->unit); ?>

                    </p>
                </div>
                <?php $sc = ['pending'=>'amber','in_progress'=>'blue','completed'=>'green','cancelled'=>'gray'][$workOrder->status] ?? 'gray'; ?>
                <span
                    class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 dark:bg-<?php echo e($sc); ?>-500/20 dark:text-<?php echo e($sc); ?>-400">
                    <?php echo e(ucfirst($workOrder->status)); ?>

                </span>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="flex-1 h-2 bg-gray-100 dark:bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full bg-green-500 rounded-full transition-all duration-300"
                        style="width: <?php echo e($pct); ?>%"></div>
                </div>
                <span class="text-xs font-semibold text-gray-700 dark:text-slate-300 whitespace-nowrap">
                    <?php echo e($scannedMaterials); ?> / <?php echo e($totalMaterials); ?> material
                </span>
            </div>
        </div>

        
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-200 dark:border-blue-800 p-5">
            <p class="text-sm font-semibold text-blue-900 dark:text-blue-200 mb-3">Scan Barcode Material</p>
            <div class="flex gap-2">
                <input type="text" id="barcode-input"
                    placeholder="Scan barcode material atau ketik SKU, lalu Enter..."
                    class="flex-1 px-4 py-2.5 rounded-xl border border-blue-200 dark:border-blue-700 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
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
            <p id="scan-feedback" class="text-xs mt-2 text-blue-600 dark:text-blue-300 min-h-[16px]"></p>
        </div>

        
        <form method="POST" action="<?php echo e(route('manufacturing.work-orders.consume-scanned', $workOrder)); ?>"
            id="consume-form">
            <?php echo csrf_field(); ?>
            <div class="space-y-2" id="materials-list">
                <?php $__currentLoopData = $requiredMaterials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $productId => $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isComplete = $material['quantity_scanned'] >= $material['quantity_required'];
                        $borderCls = $isComplete
                            ? 'border-green-300 dark:border-green-500/40 bg-green-50 dark:bg-green-500/5'
                            : 'border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b]';
                    ?>
                    <div id="material-row-<?php echo e($productId); ?>"
                        class="rounded-2xl border <?php echo e($borderCls); ?> p-4 transition-all duration-300"
                        data-product-id="<?php echo e($productId); ?>" data-barcode="<?php echo e($material['barcode']); ?>"
                        data-required="<?php echo e($material['quantity_required']); ?>">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-sm text-gray-900 dark:text-white truncate">
                                    <?php echo e($material['product']->name ?? '-'); ?>

                                </p>
                                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                    <span class="text-xs font-mono text-gray-400 dark:text-slate-500">
                                        <?php echo e($material['product']->sku ?? '-'); ?>

                                    </span>
                                    <?php if($material['barcode']): ?>
                                        <span
                                            class="text-xs px-1.5 py-0.5 rounded bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300 font-mono">
                                            Barcode: <?php echo e($material['barcode']); ?>

                                        </span>
                                    <?php endif; ?>
                                    <?php if($isComplete): ?>
                                        <span
                                            class="px-1.5 py-0.5 rounded text-[10px] bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">
                                            ✓ Complete
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs text-gray-500 dark:text-slate-400">Diperlukan</p>
                                <p class="font-bold text-gray-900 dark:text-white">
                                    <?php echo e(number_format($material['quantity_required'], 2)); ?> <?php echo e($material['unit']); ?>

                                </p>
                                <input type="hidden" name="scanned_materials[<?php echo e($productId); ?>][product_id]"
                                    value="<?php echo e($productId); ?>">
                                <input type="hidden" name="scanned_materials[<?php echo e($productId); ?>][quantity]"
                                    id="scanned-qty-<?php echo e($productId); ?>" value="<?php echo e($material['quantity_required']); ?>">
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div class="mt-4 flex items-center justify-between">
                <a href="<?php echo e(route('production.index')); ?>"
                    class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl shadow transition disabled:opacity-50 disabled:cursor-not-allowed"
                    id="submit-btn" <?php echo e($scannedMaterials < $totalMaterials ? 'disabled' : ''); ?>>
                    Konsumsi Material (<?php echo e($scannedMaterials); ?>/<?php echo e($totalMaterials); ?>)
                </button>
            </div>
        </form>

        
        <div id="barcode-scanner-modal"
            class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Scan Barcode Material</h3>
                    <button onclick="closeBarcodeScanner()"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <video id="barcode-video" class="w-full rounded-xl bg-black aspect-video"></video>
                    <p class="text-xs text-center text-gray-500 dark:text-slate-400 mt-3">Arahkan kamera ke barcode
                        material</p>
                    <div class="mt-3 border-t border-gray-100 dark:border-white/10 pt-3">
                        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Atau ketik manual:</p>
                        <div class="flex gap-2">
                            <input type="text" id="manual-barcode" placeholder="Barcode / SKU"
                                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button onclick="submitManualBarcode()"
                                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
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
                    row.classList.add('border-green-300', 'dark:border-green-500/40', 'bg-green-50', 'dark:bg-green-500/5');
                    row.classList.remove('border-gray-200', 'dark:border-white/10', 'bg-white', 'dark:bg-[#1e293b]');

                    if (!row.querySelector('[class*="green-100"]')) {
                        const badge = document.createElement('span');
                        badge.className =
                            'px-1.5 py-0.5 rounded text-[10px] bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400 ml-2';
                        badge.textContent = '✓ Complete';
                        row.querySelector('.flex-wrap').appendChild(badge);
                    }

                    feedback.textContent = `Material discan: ${row.querySelector('.font-medium')?.innerText}`;
                    feedback.className = 'text-xs mt-2 text-green-600 dark:text-green-400 min-h-[16px]';

                    row.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    playSuccessSound();
                    updateSubmitButton();
                } else {
                    // Fallback: lookup via API
                    fetch(`<?php echo e(route('inventory.movements.lookup-barcode')); ?>?barcode=${encodeURIComponent(barcode)}`)
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
                                    feedback.className = 'text-xs mt-2 text-green-600 dark:text-green-400 min-h-[16px]';
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
    <?php $__env->stopPush(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\manufacturing\work-orders\scan-materials.blade.php ENDPATH**/ ?>