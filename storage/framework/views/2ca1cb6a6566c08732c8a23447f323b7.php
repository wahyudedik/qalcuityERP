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
     <?php $__env->slot('header', null, []); ?> Invoice <?php echo e($invoice->number); ?> <?php $__env->endSlot(); ?>

     <?php $__env->slot('pageHeader', null, []); ?> 
        <div class="flex items-center gap-2">
            
            <?php if($invoice->isDraft()): ?>
            <form method="POST" action="<?php echo e(route('invoices.post', $invoice)); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Posting
                </button>
            </form>
            <button onclick="document.getElementById('modal-cancel-invoice').classList.remove('hidden')"
                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-red-100 text-red-700 text-sm font-medium hover:bg-red-200 transition">
                Batalkan
            </button>
            <?php elseif($invoice->isPosted() && $invoice->paid_amount == 0): ?>
            <button onclick="document.getElementById('modal-void-invoice').classList.remove('hidden')"
                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-100 text-orange-700 text-sm font-medium hover:bg-orange-200 transition">
                Void Invoice
            </button>
            <?php endif; ?>

            <a href="<?php echo e(route('invoices.pdf', $invoice)); ?>"
               class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 text-sm text-gray-700 hover:bg-gray-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download PDF
            </a>
            <a href="<?php echo e(route('sign.pad', ['Invoice', $invoice->id])); ?>"
               class="flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-100 text-sm text-indigo-700 hover:bg-indigo-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Tanda Tangani
            </a>
            <?php if($invoice->customer?->email): ?>
            <form method="POST" action="<?php echo e(route('invoices.send-email', $invoice)); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Kirim ke Email
                </button>
            </form>
            <?php endif; ?>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        
        <div class="lg:col-span-2 space-y-5">

            
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">No. Invoice</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e($invoice->number); ?></p>
                    </div>
                    <?php
                        $isOverdue = $invoice->status !== 'paid' && $invoice->due_date < now();
                        $statusColor = match($invoice->status) {
                            'paid'    => 'bg-green-100 text-green-700',
                            'partial' => 'bg-amber-100 text-amber-700',
                            default   => 'bg-red-100 text-red-700',
                        };
                        $statusLabel = match($invoice->status) {
                            'paid'    => 'Lunas',
                            'partial' => 'Sebagian',
                            default   => 'Belum Dibayar',
                        };
                    ?>
                    <span class="inline-flex px-3 py-1.5 rounded-full text-sm font-semibold <?php echo e($statusColor); ?>"><?php echo e($statusLabel); ?></span>
                    
                    <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-semibold <?php echo e($invoice->postingStatusColor()); ?>">
                        <?php echo e($invoice->postingStatusLabel()); ?>

                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Customer</p>
                        <p class="font-semibold text-gray-900"><?php echo e($invoice->customer?->name ?? '-'); ?></p>
                        <?php if($invoice->customer?->company): ?><p class="text-gray-500 text-xs"><?php echo e($invoice->customer->company); ?></p><?php endif; ?>
                        <?php if($invoice->customer?->email): ?><p class="text-gray-500 text-xs"><?php echo e($invoice->customer->email); ?></p><?php endif; ?>
                        <?php if($invoice->customer?->phone): ?><p class="text-gray-500 text-xs"><?php echo e($invoice->customer->phone); ?></p><?php endif; ?>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Tanggal Invoice</p>
                        <p class="font-medium text-gray-900"><?php echo e($invoice->created_at->format('d M Y')); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Jatuh Tempo</p>
                        <p class="font-medium <?php echo e($isOverdue ? 'text-red-600' : 'text-gray-900'); ?>">
                            <?php echo e($invoice->due_date?->format('d M Y') ?? '-'); ?>

                        </p>
                        <?php if($isOverdue): ?><p class="text-xs text-red-500">Terlambat <?php echo e($invoice->daysOverdue()); ?> hari</p><?php endif; ?>
                    </div>
                    <?php if($invoice->salesOrder): ?>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Sales Order</p>
                        <p class="font-medium text-gray-900"><?php echo e($invoice->salesOrder->number); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if($invoice->currency_code && $invoice->currency_code !== 'IDR'): ?>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Mata Uang</p>
                        <p class="font-medium text-gray-900">
                            <?php echo e($invoice->currency_code); ?>

                            <span class="text-xs text-gray-400">(Kurs: Rp <?php echo e(number_format($invoice->currency_rate, 0, ',', '.')); ?>)</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Ekuivalen IDR</p>
                        <p class="font-medium text-green-600">Rp <?php echo e(number_format($invoice->total_amount * $invoice->currency_rate, 0, ',', '.')); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <?php if($invoice->salesOrder && $invoice->salesOrder->items->count()): ?>
            <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <p class="font-semibold text-gray-900 text-sm">Item Pesanan</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Produk</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Harga</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__currentLoopData = $invoice->salesOrder->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-4 py-3 text-gray-900">
                                    <?php echo e($item->product?->name ?? '-'); ?>

                                    <span class="sm:hidden text-xs text-gray-400 block"><?php echo e($item->quantity); ?> × Rp <?php echo e(number_format($item->price, 0, ',', '.')); ?></span>
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600 hidden sm:table-cell"><?php echo e($item->quantity); ?></td>
                                <td class="px-4 py-3 text-right text-gray-600 hidden sm:table-cell">Rp <?php echo e(number_format($item->price, 0, ',', '.')); ?></td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">Rp <?php echo e(number_format($item->total, 0, ',', '.')); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 space-y-1.5">
                    <?php if($invoice->salesOrder->discount > 0): ?>
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>Diskon</span><span>- Rp <?php echo e(number_format($invoice->salesOrder->discount, 0, ',', '.')); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if($invoice->salesOrder->tax > 0): ?>
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>Pajak</span><span>Rp <?php echo e(number_format($invoice->salesOrder->tax, 0, ',', '.')); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-base font-bold text-gray-900 pt-1 border-t border-gray-100">
                        <span>Total</span><span>Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            
            <?php if($invoice->notes): ?>
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Catatan</p>
                <p class="text-sm text-gray-700"><?php echo e($invoice->notes); ?></p>
            </div>
            <?php endif; ?>
        </div>

        
        <div class="space-y-5">

            
            <div class="rounded-2xl border border-gray-200 bg-white p-6 space-y-3">
                <p class="font-semibold text-gray-900 text-sm">Ringkasan Pembayaran</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total Tagihan</span>
                        <span class="font-semibold text-gray-900">Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Terbayar</span>
                        <span class="font-semibold text-green-600">Rp <?php echo e(number_format($invoice->paid_amount, 0, ',', '.')); ?></span>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-2">
                        <span class="font-semibold text-gray-700">Sisa Tagihan</span>
                        <span class="font-bold text-lg <?php echo e($invoice->remaining_amount > 0 ? 'text-red-600' : 'text-green-600'); ?>">
                            Rp <?php echo e(number_format($invoice->remaining_amount, 0, ',', '.')); ?>

                        </span>
                    </div>
                </div>

                
                <?php $pct = $invoice->total_amount > 0 ? min(100, ($invoice->paid_amount / $invoice->total_amount) * 100) : 0; ?>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full transition-all" style="width: <?php echo e($pct); ?>%"></div>
                </div>
                <p class="text-xs text-gray-400 text-right"><?php echo e(number_format($pct, 0)); ?>% terbayar</p>
            </div>

            

            
            <?php
                $invoiceSigs = \App\Models\DigitalSignature::where('model_type', 'App\\Models\\Invoice')
                    ->where('model_id', $invoice->id)
                    ->with('user')
                    ->latest('signed_at')
                    ->get();
            ?>
            <?php if($invoiceSigs->isNotEmpty()): ?>
            <div class="rounded-2xl border border-gray-200 bg-white p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Tanda Tangan Digital</p>
                <div class="space-y-3">
                    <?php $__currentLoopData = $invoiceSigs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sig): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-3">
                        <img src="<?php echo e($sig->signature_data); ?>" alt="TTD" class="h-10 border border-gray-200 rounded-lg bg-white">
                        <div>
                            <p class="text-sm font-medium text-gray-900"><?php echo e($sig->user?->name); ?></p>
                            <p class="text-xs text-gray-400"><?php echo e($sig->signed_at?->format('d M Y H:i')); ?> · <?php echo e($sig->ip_address); ?></p>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if($invoice->status !== 'paid'): ?>
            
            <?php if($invoice->customer_id): ?>
            <div class="rounded-2xl border border-gray-200 bg-white p-4" id="ai-payment-risk">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-5 h-5 rounded-md bg-indigo-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-3 h-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-900">Prediksi Pembayaran AI</p>
                </div>
                <div id="ai-risk-content" class="text-xs text-gray-400">Memuat analisis...</div>
            </div>
            <?php endif; ?>
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <p class="font-semibold text-gray-900 text-sm mb-4">Catat Pembayaran</p>
                <form method="POST" action="<?php echo e(route('invoices.payment', $invoice)); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Jumlah (Rp)</label>
                        <input type="number" name="amount" min="1" max="<?php echo e($invoice->remaining_amount); ?>"
                            value="<?php echo e(old('amount', $invoice->remaining_amount)); ?>" required
                            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Metode</label>
                        <select name="method" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Catatan</label>
                        <input type="text" name="notes" placeholder="Opsional"
                            class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-medium transition">
                        Simpan Pembayaran
                    </button>
                </form>
            </div>
            <?php endif; ?>

            
            <?php if($invoice->payments->count()): ?>
            <div class="rounded-2xl border border-gray-200 bg-white p-6">
                <p class="font-semibold text-gray-900 text-sm mb-3">Riwayat Pembayaran</p>
                <div class="space-y-2">
                    <?php $__currentLoopData = $invoice->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-green-50 text-sm">
                        <div>
                            <p class="font-medium text-green-700">Rp <?php echo e(number_format($pay->amount, 0, ',', '.')); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e($pay->payment_date?->format('d M Y')); ?> · <?php echo e(strtoupper($pay->payment_method ?? '-')); ?></p>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <a href="<?php echo e(route('invoices.index')); ?>" class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke daftar invoice
            </a>
        </div>
    </div>

    
    <div id="modal-cancel-invoice" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Batalkan Invoice</h3>
            <form method="POST" action="<?php echo e(route('invoices.cancel', $invoice)); ?>">
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Pembatalan</label>
                    <textarea name="reason" rows="3" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-500"
                        placeholder="Masukkan alasan pembatalan..."></textarea>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="document.getElementById('modal-cancel-invoice').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-gray-600 hover:bg-gray-100 transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition">Batalkan Invoice</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-void-invoice" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Void Invoice</h3>
            <p class="text-sm text-gray-500 mb-4">Invoice yang di-void akan membuat jurnal pembalik otomatis. Tindakan ini tidak bisa dibatalkan.</p>
            <form method="POST" action="<?php echo e(route('invoices.void', $invoice)); ?>">
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Void</label>
                    <textarea name="reason" rows="3" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500"
                        placeholder="Masukkan alasan void..."></textarea>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="document.getElementById('modal-void-invoice').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-gray-600 hover:bg-gray-100 transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium transition">Void Invoice</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <?php if($invoice->status !== 'paid' && $invoice->customer_id): ?>
    <script>
    (function() {
        const RISK_URL   = "<?php echo e(route('sales.ai.late-payment-risk')); ?>?customer_id=<?php echo e($invoice->customer_id); ?>";
        const container  = document.getElementById('ai-risk-content');
        if (!container) return;

        fetch(RISK_URL)
            .then(r => r.json())
            .then(data => {
                const riskColors = {
                    high:   { bg: 'bg-red-500/10 border-red-500/30',    text: 'text-red-400',    label: 'Risiko Tinggi',   bar: 'bg-red-500' },
                    medium: { bg: 'bg-yellow-500/10 border-yellow-500/30', text: 'text-yellow-400', label: 'Risiko Sedang', bar: 'bg-yellow-500' },
                    low:    { bg: 'bg-green-500/10 border-green-500/30',  text: 'text-green-400',  label: 'Risiko Rendah',  bar: 'bg-green-500' },
                };
                const c = riskColors[data.risk] || riskColors.low;

                const tips = (data.tips || []).map(t => `<li class="flex gap-1"><span>•</span><span>${t}</span></li>`).join('');

                container.innerHTML = `
                    <div class="rounded-lg border ${c.bg} p-2.5 mb-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="${c.text} font-semibold">${c.label}</span>
                            <span class="${c.text} font-bold">${data.probability}%</span>
                        </div>
                        <div class="w-full bg-white/10 rounded-full h-1.5 mb-1.5">
                            <div class="${c.bar} h-1.5 rounded-full" style="width:${data.probability}%"></div>
                        </div>
                        <p class="text-gray-400 leading-snug">${data.reason}</p>
                    </div>
                    ${tips ? `<ul class="text-gray-400 space-y-0.5 leading-snug">${tips}</ul>` : ''}
                `;

                // Update border warna widget jika high risk
                if (data.risk === 'high') {
                    document.getElementById('ai-payment-risk')?.classList.add('border-red-500/30');
                }
            })
            .catch(() => {
                container.textContent = 'Tidak dapat memuat prediksi.';
            });
    })();
    </script>
    <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\invoices\show.blade.php ENDPATH**/ ?>