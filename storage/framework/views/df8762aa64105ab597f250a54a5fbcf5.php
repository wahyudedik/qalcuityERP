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
     <?php $__env->slot('header', null, []); ?> 
        Sales Order — <?php echo e($salesOrder->number); ?>

     <?php $__env->endSlot(); ?>

    <div class="max-w-4xl mx-auto space-y-5">

        <?php if(session('success')): ?>
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">
                <?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">
                <?php echo e(session('error')); ?></div>
        <?php endif; ?>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Nomor SO</p>
                    <p class="text-xl font-bold font-mono text-gray-900 dark:text-white"><?php echo e($salesOrder->number); ?></p>
                    <?php
                        $statusColors = [
                            'pending' => 'bg-yellow-500/20 text-yellow-400',
                            'confirmed' => 'bg-blue-500/20 text-blue-400',
                            'processing' => 'bg-purple-500/20 text-purple-400',
                            'shipped' => 'bg-indigo-500/20 text-indigo-400',
                            'completed' => 'bg-green-500/20 text-green-400',
                            'cancelled' => 'bg-red-500/20 text-red-400',
                        ];
                    ?>
                    <span
                        class="mt-2 inline-block px-3 py-1 rounded-full text-xs <?php echo e($statusColors[$salesOrder->status] ?? ''); ?>">
                        <?php echo e(ucfirst($salesOrder->status)); ?>

                    </span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <?php if(!in_array($salesOrder->status, ['completed', 'cancelled'])): ?>
                        <form method="POST" action="<?php echo e(route('sales.status', $salesOrder)); ?>" class="flex gap-2">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <select name="status"
                                class="bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none">
                                <?php
                                    // BUG-SALES-001 FIX: Only show valid transitions
                                    $validTransitions = [
                                        'pending' => ['confirmed', 'cancelled'],
                                        'confirmed' => ['processing', 'cancelled'],
                                        'processing' => ['shipped', 'cancelled'],
                                        'shipped' => ['completed', 'cancelled'],
                                        'completed' => [],
                                        'cancelled' => [],
                                    ];
                                    $allowedStatuses = $validTransitions[$salesOrder->status] ?? [];
                                ?>
                                <?php $__currentLoopData = ['pending', 'confirmed', 'processing', 'shipped', 'completed', 'cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(in_array($s, $allowedStatuses)): ?>
                                        <option value="<?php echo e($s); ?>"><?php echo e(ucfirst($s)); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <button type="submit"
                                class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm transition">Update</button>
                        </form>
                    <?php endif; ?>
                    <?php if(!$salesOrder->invoices()->where('status', '!=', 'cancelled')->exists()): ?>
                        <form method="POST" action="<?php echo e(route('sales.invoice', $salesOrder)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm transition">
                                Buat Invoice
                            </button>
                        </form>
                    <?php else: ?>
                        <?php $inv = $salesOrder->invoices()->where('status', '!=', 'cancelled')->first(); ?>
                        <a href="<?php echo e(route('invoices.show', $inv)); ?>"
                            class="px-3 py-2 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white rounded-xl text-sm hover:bg-gray-200 dark:hover:bg-white/20 transition">
                            Lihat Invoice
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo e(route('sales.index')); ?>"
                        class="px-3 py-2 bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300 rounded-xl text-sm hover:bg-gray-200 dark:hover:bg-white/20 transition">
                        ← Kembali
                    </a>
                    <a href="<?php echo e(route('sign.pad', ['SalesOrder', $salesOrder->id])); ?>"
                        class="px-3 py-2 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-400 rounded-xl text-sm hover:bg-indigo-200 dark:hover:bg-indigo-500/30 transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        TTD
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100 dark:border-white/10">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Customer</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">
                        <?php echo e($salesOrder->customer->name ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Tanggal</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">
                        <?php echo e($salesOrder->date->format('d/m/Y')); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Pengiriman</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">
                        <?php echo e($salesOrder->delivery_date?->format('d/m/Y') ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Pembayaran</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">
                        <?php echo e($salesOrder->payment_type === 'credit' ? 'Kredit' : 'Tunai'); ?>

                        <?php if($salesOrder->due_date): ?>
                            — <?php echo e($salesOrder->due_date->format('d/m/Y')); ?>

                        <?php endif; ?>
                    </p>
                </div>
                <?php if($salesOrder->quotation): ?>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Dari Quotation</p>
                        <p class="text-sm font-medium text-blue-400 mt-0.5"><?php echo e($salesOrder->quotation->number); ?></p>
                    </div>
                <?php endif; ?>
                <?php if($salesOrder->currency_code && $salesOrder->currency_code !== 'IDR'): ?>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Mata Uang</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">
                            <?php echo e($salesOrder->currency_code); ?>

                            <span class="text-xs text-gray-400 dark:text-slate-500">(Kurs: Rp
                                <?php echo e(number_format($salesOrder->currency_rate, 0, ',', '.')); ?>)</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Ekuivalen IDR</p>
                        <p class="text-sm font-medium text-green-600 dark:text-green-400 mt-0.5">Rp
                            <?php echo e(number_format($salesOrder->total * $salesOrder->currency_rate, 0, ',', '.')); ?></p>
                    </div>
                <?php endif; ?>
                <?php if($salesOrder->shipping_address): ?>
                    <div class="col-span-2">
                        <p class="text-xs text-gray-500 dark:text-slate-400">Alamat Pengiriman</p>
                        <p class="text-sm text-gray-700 dark:text-slate-300 mt-0.5"><?php echo e($salesOrder->shipping_address); ?>

                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h2 class="font-semibold text-gray-900 dark:text-white">Item Produk</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-white/5 text-xs text-gray-500 dark:text-slate-400">
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right">Harga</th>
                        <th class="px-4 py-3 text-right">Diskon</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                    <?php $__currentLoopData = $salesOrder->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="px-4 py-3 text-gray-700 dark:text-slate-300"><?php echo e($item->product->name ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3 text-right"><?php echo e($item->quantity); ?> <?php echo e($item->product->unit ?? ''); ?>

                            </td>
                            <td class="px-4 py-3 text-right">Rp <?php echo e(number_format($item->price, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right text-red-400">
                                <?php echo e($item->discount > 0 ? '-Rp ' . number_format($item->discount, 0, ',', '.') : '-'); ?>

                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp
                                <?php echo e(number_format($item->total, 0, ',', '.')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot class="border-t border-gray-100 dark:border-white/10 text-sm">
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right text-gray-500 dark:text-slate-400">Subtotal</td>
                        <td class="px-4 py-2 text-right font-medium text-gray-900 dark:text-white">Rp
                            <?php echo e(number_format($salesOrder->subtotal, 0, ',', '.')); ?></td>
                    </tr>
                    <?php if($salesOrder->discount > 0): ?>
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-right text-red-400">Diskon</td>
                            <td class="px-4 py-2 text-right text-red-400">-Rp
                                <?php echo e(number_format($salesOrder->discount, 0, ',', '.')); ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if($salesOrder->tax_amount > 0): ?>
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-right text-gray-500 dark:text-slate-400">Pajak</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-slate-300">Rp
                                <?php echo e(number_format($salesOrder->tax_amount, 0, ',', '.')); ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr class="font-bold">
                        <td colspan="4" class="px-4 py-3 text-right text-gray-900 dark:text-white">Total</td>
                        <td class="px-4 py-3 text-right text-blue-400 text-base">Rp
                            <?php echo e(number_format($salesOrder->total, 0, ',', '.')); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if($salesOrder->notes): ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Catatan</p>
                <p class="text-sm text-gray-700 dark:text-slate-300"><?php echo e($salesOrder->notes); ?></p>
            </div>
        <?php endif; ?>

        
        <?php
            $signatures = \App\Models\DigitalSignature::where('model_type', 'App\\Models\\SalesOrder')
                ->where('model_id', $salesOrder->id)
                ->with('user')
                ->latest('signed_at')
                ->get();
        ?>
        <?php if($signatures->isNotEmpty()): ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <p class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase mb-3">Tanda Tangan Digital
                </p>
                <div class="flex flex-wrap gap-4">
                    <?php $__currentLoopData = $signatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sig): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div
                            class="flex items-center gap-3 bg-gray-50 dark:bg-white/5 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <img src="<?php echo e($sig->signature_data); ?>" alt="TTD"
                                class="h-10 border border-gray-200 dark:border-white/10 rounded-lg bg-white">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($sig->user?->name); ?>

                                </p>
                                <p class="text-xs text-gray-400 dark:text-slate-500">
                                    <?php echo e($sig->signed_at?->format('d M Y H:i')); ?></p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\sales\show.blade.php ENDPATH**/ ?>