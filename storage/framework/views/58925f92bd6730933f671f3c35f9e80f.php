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
     <?php $__env->slot('header', null, []); ?> Penggajian (Payroll) <?php $__env->endSlot(); ?>

    <div class="flex flex-col lg:flex-row gap-6">

        
        <div class="w-full lg:w-72 shrink-0 space-y-4">

            
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'payroll', 'create')): ?>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-4">Proses Penggajian</h3>
                <form method="POST" action="<?php echo e(route('payroll.process')); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Periode *</label>
                        <input type="month" name="period" value="<?php echo e($period); ?>" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Hari Kerja</label>
                        <input type="number" name="working_days" value="26" min="1" max="31"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="include_bpjs" id="include_bpjs" value="1" checked class="rounded">
                        <label for="include_bpjs" class="text-sm text-gray-700">Hitung BPJS (3%)</label>
                    </div>
                    <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Hitung Gaji
                    </button>
                </form>
            </div>
            <?php endif; ?>

            
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-900">Riwayat Periode</p>
                </div>
                <div class="divide-y divide-gray-100 max-h-64 overflow-y-auto">
                    <?php $__empty_1 = true; $__currentLoopData = $runs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="<?php echo e(route('payroll.index', ['period' => $r->period])); ?>"
                        class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 <?php echo e($r->period === $period ? 'bg-blue-50' : ''); ?>">
                        <div>
                            <p class="text-sm font-medium text-gray-900"><?php echo e($r->period); ?></p>
                            <p class="text-xs text-gray-500"><?php echo e($r->items()->count()); ?> karyawan</p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full
                            <?php echo e($r->status === 'paid' ? 'bg-green-100 text-green-700' :
                               ($r->status === 'processed' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500')); ?>">
                            <?php echo e(ucfirst($r->status)); ?>

                        </span>
                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-4 py-6 text-center text-sm text-gray-400">Belum ada data.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="flex-1">
            <?php if($run): ?>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                <div class="bg-white rounded-2xl p-4 border border-gray-200">
                    <p class="text-xs text-gray-500">Karyawan</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e($items->count()); ?></p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-gray-200">
                    <p class="text-xs text-gray-500">Total Kotor</p>
                    <p class="text-lg font-bold text-gray-900 mt-1">Rp <?php echo e(number_format($run->total_gross,0,',','.')); ?></p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-gray-200">
                    <p class="text-xs text-gray-500">Total Potongan</p>
                    <p class="text-lg font-bold text-red-600 mt-1">Rp <?php echo e(number_format($run->total_deductions,0,',','.')); ?></p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-gray-200">
                    <p class="text-xs text-gray-500">Total Bersih</p>
                    <p class="text-lg font-bold text-green-600 mt-1">Rp <?php echo e(number_format($run->total_net,0,',','.')); ?></p>
                </div>
            </div>

            
            <div class="mb-4 space-y-2">
                
                <div class="px-4 py-3 rounded-xl border flex items-center justify-between gap-3
                    <?php echo e($run->journal_entry_id ? 'bg-green-50 border-green-200' : 'bg-amber-50 border-amber-200'); ?>">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <?php if($run->journal_entry_id): ?>
                            <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-green-800">Jurnal Beban Gaji ✓</p>
                                <p class="text-xs text-green-600 truncate"><?php echo e($run->journalEntry->number); ?> · Dr Beban Gaji Rp <?php echo e(number_format($run->total_gross,0,',','.')); ?> · Cr Hutang Gaji + PPh21 + BPJS</p>
                            </div>
                        <?php else: ?>
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            <div>
                                <p class="text-sm font-medium text-amber-800">Jurnal Beban Gaji Belum Ada</p>
                                <p class="text-xs text-amber-600">Dr Beban Gaji / Cr Hutang Gaji + PPh21 + BPJS belum diposting ke GL.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="shrink-0">
                        <?php if($run->journal_entry_id): ?>
                            <a href="<?php echo e(route('journals.show', $run->journalEntry)); ?>" class="px-3 py-1.5 text-xs border border-green-300 text-green-700 rounded-xl hover:bg-green-100">Lihat</a>
                        <?php else: ?>
                            <form method="POST" action="<?php echo e(route('payroll.gl-journal', $run)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="px-3 py-1.5 text-xs bg-amber-600 text-white rounded-xl hover:bg-amber-700">Buat Jurnal</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                
                <?php if($run->status === 'paid'): ?>
                <div class="px-4 py-3 rounded-xl border flex items-center justify-between gap-3
                    <?php echo e($run->payment_journal_entry_id ? 'bg-green-50 border-green-200' : 'bg-amber-50 border-amber-200'); ?>">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <?php if($run->payment_journal_entry_id): ?>
                            <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-green-800">Jurnal Pembayaran Gaji ✓</p>
                                <p class="text-xs text-green-600 truncate"><?php echo e($run->paymentJournalEntry->number); ?> · Dr Hutang Gaji Rp <?php echo e(number_format($run->total_net,0,',','.')); ?> · Cr Bank</p>
                            </div>
                        <?php else: ?>
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            <div>
                                <p class="text-sm font-medium text-amber-800">Jurnal Pembayaran Belum Ada</p>
                                <p class="text-xs text-amber-600">Dr Hutang Gaji / Cr Bank belum diposting.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="shrink-0">
                        <?php if($run->payment_journal_entry_id): ?>
                            <a href="<?php echo e(route('journals.show', $run->paymentJournalEntry)); ?>" class="px-3 py-1.5 text-xs border border-green-300 text-green-700 rounded-xl hover:bg-green-100">Lihat</a>
                        <?php else: ?>
                            <form method="POST" action="<?php echo e(route('payroll.gl-payment-journal', $run)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="px-3 py-1.5 text-xs bg-amber-600 text-white rounded-xl hover:bg-amber-700">Buat Jurnal</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">Detail Gaji — <?php echo e($period); ?></h3>
                <div class="flex items-center gap-2">
                    <a href="<?php echo e(route('payroll.components.index')); ?>" class="px-3 py-2 text-sm border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 4a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        Komponen Gaji
                    </a>
                    <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'payroll', 'edit')): ?>
                    <?php if($run->status === 'processed'): ?>
                    <form method="POST" action="<?php echo e(route('payroll.paid', $run)); ?>" onsubmit="return confirm('Tandai semua gaji periode ini sebagai sudah dibayar?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                        <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">
                            ✓ Tandai Dibayar
                        </button>
                    </form>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Karyawan</th>
                                <th class="px-4 py-3 text-right hidden sm:table-cell">Gaji Pokok</th>
                                <th class="px-4 py-3 text-center hidden md:table-cell">Hadir</th>
                                <th class="px-4 py-3 text-center hidden md:table-cell">Absen</th>
                                <th class="px-4 py-3 text-right hidden lg:table-cell">Lembur</th>
                                <th class="px-4 py-3 text-right hidden sm:table-cell">Potongan</th>
                                <th class="px-4 py-3 text-right">Gaji Bersih</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900"><?php echo e($item->employee->name ?? '-'); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo e($item->employee->position ?? ''); ?></p>
                                </td>
                                <td class="px-4 py-3 text-right hidden sm:table-cell text-gray-700">Rp <?php echo e(number_format($item->base_salary,0,',','.')); ?></td>
                                <td class="px-4 py-3 text-center hidden md:table-cell text-gray-700"><?php echo e($item->present_days); ?>h</td>
                                <td class="px-4 py-3 text-center hidden md:table-cell text-red-600"><?php echo e($item->absent_days); ?>h</td>
                                <td class="px-4 py-3 text-right hidden lg:table-cell">
                                    <?php if($item->overtime_pay > 0): ?>
                                    <span class="text-green-600 font-medium">+Rp <?php echo e(number_format($item->overtime_pay,0,',','.')); ?></span>
                                    <?php else: ?>
                                    <span class="text-gray-300">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-right hidden sm:table-cell text-red-600">
                                    Rp <?php echo e(number_format($item->bpjs_employee + $item->tax_pph21 + $item->deduction_absent + $item->deduction_late,0,',','.')); ?>

                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp <?php echo e(number_format($item->net_salary,0,',','.')); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($item->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'); ?>">
                                        <?php echo e($item->status === 'paid' ? 'Dibayar' : 'Pending'); ?>

                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">Tidak ada data.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php else: ?>
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                <p class="text-gray-400 text-sm">Belum ada data penggajian untuk periode <span class="font-medium text-gray-700"><?php echo e($period); ?></span>.</p>
                <p class="text-gray-400 text-xs mt-1">Gunakan form di sebelah kiri untuk menghitung gaji.</p>
                <p class="text-gray-400 text-xs mt-1">Total karyawan aktif: <span class="font-medium text-gray-700"><?php echo e($totalEmployees); ?></span></p>
                <a href="<?php echo e(route('payroll.components.index')); ?>" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 text-sm border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 4a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    Atur Komponen Gaji
                </a>
            </div>
            <?php endif; ?>
        </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\payroll\index.blade.php ENDPATH**/ ?>