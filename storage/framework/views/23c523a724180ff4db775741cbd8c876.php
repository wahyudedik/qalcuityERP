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
     <?php $__env->slot('title', null, []); ?> Goods Receipt — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> Goods Receipt (GR) <?php $__env->endSlot(); ?>
     <?php $__env->slot('pageHeader', null, []); ?> 
        <?php if($openPos->count()): ?>
            <button onclick="document.getElementById('modal-add-gr').classList.remove('hidden')"
                class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Catat Penerimaan
            </button>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor GR</th>
                        <th class="px-4 py-3 text-left">PO / Supplier</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Gudang</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Surat Jalan</th>
                        <th class="px-4 py-3 text-center">Tgl Terima</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $receipts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <p class="font-mono text-xs font-semibold text-gray-900 dark:text-white">
                                    <?php echo e($gr->number); ?></p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-white text-xs">
                                    <?php echo e($gr->purchaseOrder->number); ?></p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    <?php echo e($gr->purchaseOrder->supplier->name); ?></p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell text-gray-500 dark:text-slate-400">
                                <?php echo e($gr->warehouse->name); ?></td>
                            <td class="px-4 py-3 hidden lg:table-cell text-gray-500 dark:text-slate-400 text-xs">
                                <?php echo e($gr->delivery_note ?? '—'); ?></td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500 dark:text-slate-400">
                                <?php echo e($gr->receipt_date->format('d M Y')); ?></td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs <?php echo e($gr->status === 'confirmed' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400'); ?>">
                                    <?php echo e($gr->status === 'confirmed' ? 'Dikonfirmasi' : 'Draft'); ?>

                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum
                                ada Goods Receipt.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($receipts->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($receipts->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-add-gr" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-3xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Catat Penerimaan Barang</h3>
                <button onclick="document.getElementById('modal-add-gr').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('purchasing.goods-receipts.store')); ?>" class="p-6 space-y-5"
                id="form-gr">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Purchase Order
                            *</label>
                        <select name="purchase_order_id" id="gr-po-select" required onchange="loadPoItems()"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="">Pilih PO...</option>
                            <?php $__currentLoopData = $openPos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($po->id); ?>"
                                    data-items="<?php echo e(json_encode($po->items->map(fn($i) => ['id' => $i->id, 'product_id' => $i->product_id, 'product' => $i->product->name ?? '-', 'qty_ordered' => $i->quantity_ordered, 'qty_received' => $i->quantity_received, 'remaining' => $i->quantity_ordered - $i->quantity_received]))); ?>">
                                    <?php echo e($po->number); ?> — <?php echo e($po->supplier->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Gudang *</label>
                        <select name="warehouse_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="">Pilih gudang...</option>
                            <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($w->id); ?>"><?php echo e($w->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Terima
                            *</label>
                        <input type="date" name="receipt_date" value="<?php echo e(today()->format('Y-m-d')); ?>" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">No. Surat
                            Jalan</label>
                        <input type="text" name="delivery_note"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                        <input type="text" name="notes"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                </div>

                
                <div id="gr-scanner-wrap" class="hidden">
                    <div
                        class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-semibold text-blue-900 dark:text-blue-200">Scan Barcode Barang Masuk
                            </p>
                            <span id="gr-scan-counter"
                                class="text-xs px-2 py-0.5 bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-300 rounded-full">0
                                item discan</span>
                        </div>
                        <div class="flex gap-2">
                            <input type="text" id="gr-barcode-input"
                                placeholder="Scan barcode atau ketik SKU, lalu Enter..."
                                class="flex-1 px-3 py-2 text-sm rounded-xl border border-blue-200 dark:border-blue-700 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                autocomplete="off">
                            <button type="button" onclick="openGrScanner()"
                                class="px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-sm flex items-center gap-1.5 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M3 9V6a1 1 0 011-1h3M3 15v3a1 1 0 001 1h3m11-4v3a1 1 0 01-1 1h-3m4-11h-3a1 1 0 00-1 1v3M9 3H6a1 1 0 00-1 1v3m0 6v3a1 1 0 001 1h3m6-10h3a1 1 0 011 1v3" />
                                </svg>
                                Kamera
                            </button>
                        </div>
                        <p id="gr-scan-feedback" class="text-xs mt-1.5 text-blue-600 dark:text-blue-300 min-h-[16px]">
                        </p>
                    </div>
                </div>

                
                <div id="gr-items-wrap" class="hidden">
                    <p class="text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-2">
                        Detail Penerimaan</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400">
                                <tr>
                                    <th class="px-3 py-2 text-left">Produk</th>
                                    <th class="px-3 py-2 text-center">Dipesan</th>
                                    <th class="px-3 py-2 text-center">Sisa</th>
                                    <th class="px-3 py-2 text-center">Diterima</th>
                                    <th class="px-3 py-2 text-center">Diterima (QC)</th>
                                    <th class="px-3 py-2 text-center">Ditolak</th>
                                </tr>
                            </thead>
                            <tbody id="gr-items-body" class="divide-y divide-gray-100 dark:divide-white/5"></tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-gr').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan
                        GR</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="gr-scanner-modal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Scan Barang Masuk</h3>
                <button onclick="closeGrScanner()" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-4">
                <video id="gr-barcode-video" class="w-full rounded-xl bg-black aspect-video"></video>
                <p class="text-xs text-center text-gray-500 dark:text-slate-400 mt-3">Arahkan kamera ke barcode produk
                </p>
                <div class="mt-3 border-t border-gray-100 dark:border-white/10 pt-3">
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Atau ketik manual:</p>
                    <div class="flex gap-2">
                        <input type="text" id="gr-manual-barcode" placeholder="Barcode / SKU"
                            class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button onclick="grSubmitManual()"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
        <script>
            // ── GR barcode scanner state ──────────────────────────────────
            let grCodeReader = null;
            let grScannedCount = 0;

            // Map: product_id (string) → row index in items table
            const grProductIndexMap = {};

            function loadPoItems() {
                const sel = document.getElementById('gr-po-select');
                const opt = sel.options[sel.selectedIndex];
                if (!opt.value) {
                    document.getElementById('gr-items-wrap').classList.add('hidden');
                    document.getElementById('gr-scanner-wrap').classList.add('hidden');
                    return;
                }

                const items = JSON.parse(opt.dataset.items || '[]');
                const tbody = document.getElementById('gr-items-body');
                tbody.innerHTML = '';
                Object.keys(grProductIndexMap).forEach(k => delete grProductIndexMap[k]);
                grScannedCount = 0;
                updateGrScanCounter();

                items.forEach((item, i) => {
                    const remaining = Math.max(0, item.remaining);
                    // Store mapping product_id → index
                    grProductIndexMap[String(item.product_id)] = i;

                    tbody.innerHTML += `
            <tr id="gr-row-${i}" data-product-id="${item.product_id}">
                <td class="px-3 py-2 text-gray-900 dark:text-white">${item.product}
                    <input type="hidden" name="items[${i}][purchase_order_item_id]" value="${item.id}">
                    <input type="hidden" name="items[${i}][product_id]" value="${item.product_id}">
                </td>
                <td class="px-3 py-2 text-center text-gray-500 dark:text-slate-400">${item.qty_ordered}</td>
                <td class="px-3 py-2 text-center font-semibold ${remaining > 0 ? 'text-amber-600' : 'text-green-600'}">${remaining}</td>
                <td class="px-3 py-2 text-center">
                    <input type="number" id="recv-${i}" name="items[${i}][quantity_received]" value="${remaining}" min="0" max="${remaining}" step="0.01"
                        oninput="syncAccepted(this, ${i})"
                        class="w-20 px-2 py-1 text-sm text-center rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                </td>
                <td class="px-3 py-2 text-center">
                    <input type="number" id="accepted-${i}" name="items[${i}][quantity_accepted]" value="${remaining}" min="0" step="0.01"
                        oninput="syncRejected(this, ${i})"
                        class="w-20 px-2 py-1 text-sm text-center rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                </td>
                <td class="px-3 py-2 text-center">
                    <input type="number" id="rejected-${i}" name="items[${i}][quantity_rejected]" value="0" min="0" step="0.01" readonly
                        class="w-20 px-2 py-1 text-sm text-center rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-red-500 dark:text-red-400">
                </td>
            </tr>`;
                });

                document.getElementById('gr-items-wrap').classList.remove('hidden');
                document.getElementById('gr-scanner-wrap').classList.remove('hidden');
            }

            // ── Barcode scanning logic ────────────────────────────────────

            function processGrBarcode(barcode) {
                const feedback = document.getElementById('gr-scan-feedback');

                // Try direct product lookup
                fetch(`<?php echo e(route('inventory.movements.lookup-barcode')); ?>?barcode=${encodeURIComponent(barcode)}`)
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) {
                            feedback.textContent = `Barcode "${barcode}" tidak dikenali.`;
                            feedback.className = 'text-xs mt-1.5 text-red-500 min-h-[16px]';
                            return;
                        }

                        const productId = String(data.data.id);
                        const rowIndex = grProductIndexMap[productId];

                        if (rowIndex === undefined) {
                            feedback.textContent = `${data.data.name} tidak ada di PO ini.`;
                            feedback.className = 'text-xs mt-1.5 text-amber-600 min-h-[16px]';
                            return;
                        }

                        const row = document.getElementById(`gr-row-${rowIndex}`);
                        const recvInput = document.getElementById(`recv-${rowIndex}`);
                        const acceptedInput = document.getElementById(`accepted-${rowIndex}`);

                        if (!row || !recvInput) return;

                        // Highlight row
                        row.classList.add('bg-green-50', 'dark:bg-green-500/10');
                        setTimeout(() => row.classList.remove('bg-green-50', 'dark:bg-green-500/10'), 1500);

                        // Increment quantity_received by 1 (up to max)
                        const max = parseFloat(recvInput.max) || Infinity;
                        const current = parseFloat(recvInput.value) || 0;
                        const newVal = Math.min(current + 1, max);
                        recvInput.value = newVal;
                        if (acceptedInput) acceptedInput.value = newVal;
                        syncRejected(acceptedInput, rowIndex);

                        grScannedCount++;
                        updateGrScanCounter();

                        feedback.textContent = `Scan: ${data.data.name} (${newVal} / ${max})`;
                        feedback.className = 'text-xs mt-1.5 text-green-600 dark:text-green-400 min-h-[16px]';

                        // Scroll to row
                        row.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                        playGrSuccessSound();
                    })
                    .catch(() => {
                        feedback.textContent = 'Gagal mencari produk.';
                        feedback.className = 'text-xs mt-1.5 text-red-500 min-h-[16px]';
                    });

                setTimeout(() => {
                    document.getElementById('gr-barcode-input').value = '';
                    document.getElementById('gr-barcode-input').focus();
                }, 150);
            }

            function updateGrScanCounter() {
                document.getElementById('gr-scan-counter').textContent = `${grScannedCount} item discan`;
            }

            function openGrScanner() {
                // Check if browser supports getUserMedia
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert(
                        'Browser Anda tidak mendukung akses kamera. Gunakan browser modern (Chrome, Firefox, Safari) dengan HTTPS.'
                        );
                    return;
                }

                document.getElementById('gr-scanner-modal').classList.remove('hidden');
                const videoEl = document.getElementById('gr-barcode-video');

                try {
                    grCodeReader = new ZXing.BrowserMultiFormatReader();
                    grCodeReader.decodeFromVideoDevice(null, videoEl, (result, err) => {
                        if (result) {
                            grCodeReader.reset();
                            grCodeReader = null;
                            closeGrScanner();
                            processGrBarcode(result.text);
                        }
                        if (err && !(err instanceof ZXing.NotFoundException)) {
                            console.error('Scanner error:', err);
                        }
                    }).catch((error) => {
                        console.error('Failed to access camera:', error);
                        if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                            alert('Akses kamera ditolak. Mohon izinkan akses kamera di browser settings.');
                        } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
                            alert('Tidak ada kamera yang ditemukan di perangkat Anda.');
                        } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
                            alert('Kamera sedang digunakan oleh aplikasi lain.');
                        } else {
                            alert('Gagal mengakses kamera: ' + error.message);
                        }
                        closeGrScanner();
                    });
                } catch (error) {
                    console.error('Scanner initialization error:', error);
                    alert('Gagal menginisialisasi scanner: ' + error.message);
                    closeGrScanner();
                }
            }

            function closeGrScanner() {
                document.getElementById('gr-scanner-modal').classList.add('hidden');
                if (grCodeReader) {
                    grCodeReader.reset();
                    grCodeReader = null;
                }
            }

            function grSubmitManual() {
                const val = document.getElementById('gr-manual-barcode').value.trim();
                if (val) {
                    closeGrScanner();
                    processGrBarcode(val);
                }
            }

            function playGrSuccessSound() {
                try {
                    const ctx = new(window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.frequency.value = 880;
                    osc.type = 'sine';
                    gain.gain.setValueAtTime(0.07, ctx.currentTime);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.08);
                } catch (e) {}
            }

            // Hardware scanner Enter key on GR barcode input
            document.getElementById('gr-barcode-input').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const v = this.value.trim();
                    if (v) processGrBarcode(v);
                }
            });
            document.getElementById('gr-manual-barcode').addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    grSubmitManual();
                }
            });

            // ── Existing sync helpers ─────────────────────────────────────
            function syncAccepted(input, i) {
                const accepted = document.getElementById('accepted-' + i);
                const receivedInput = document.getElementById('recv-' + i);
                const remaining = parseFloat(receivedInput.max);

                // BUG-PO-002 FIX: Validate accepted doesn't exceed received
                if (parseFloat(accepted.value) > parseFloat(input.value)) {
                    accepted.value = input.value;
                }

                // BUG-PO-002 FIX: Validate doesn't exceed remaining
                if (parseFloat(accepted.value) > remaining) {
                    accepted.value = remaining;
                    showValidationError(`Quantity accepted cannot exceed remaining quantity (${remaining})`);
                }

                syncRejected(accepted, i);
            }

            // BUG-PO-002 FIX: Real-time quantity validation
            function validateQuantity(input, index, maxAllowed) {
                const value = parseFloat(input.value);

                if (value < 0) {
                    input.value = 0;
                    showValidationError('Quantity cannot be negative');
                    return false;
                }

                if (value > maxAllowed) {
                    input.value = maxAllowed;
                    showValidationError(`Over-acceptance prevented! Maximum allowed: ${maxAllowed}`);
                    return false;
                }

                clearValidationError();
                return true;
            }

            function showValidationError(message) {
                let feedback = document.getElementById('gr-validation-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.id = 'gr-validation-feedback';
                    feedback.className =
                        'mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg';
                    document.getElementById('modal-add-gr').querySelector('form').prepend(feedback);
                }
                feedback.innerHTML = `
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm text-red-800 dark:text-red-300">${message}</p>
                    </div>
                `;
            }

            function clearValidationError() {
                const feedback = document.getElementById('gr-validation-feedback');
                if (feedback) {
                    feedback.remove();
                }
            }

            function syncRejected(acceptedInput, i) {
                const receivedInput = document.querySelector(`[name="items[${i}][quantity_received]"]`);
                const rejected = document.getElementById('rejected-' + i);
                const diff = parseFloat(receivedInput?.value || 0) - parseFloat(acceptedInput?.value || 0);
                rejected.value = Math.max(0, diff).toFixed(2);
            }
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\purchasing\goods-receipts.blade.php ENDPATH**/ ?>