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
     <?php $__env->slot('header', null, []); ?> Slip Gaji — <?php echo e($item->payrollRun?->period ?? '—'); ?> <?php $__env->endSlot(); ?>

    <div class="flex flex-col lg:flex-row gap-5">

        
        <div class="lg:w-52 shrink-0 space-y-3 print:hidden">
            <button onclick="window.print()"
                class="w-full py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </button>
            <a href="<?php echo e(route('payroll.slip.pdf', $item)); ?>"
                class="w-full py-2.5 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Unduh PDF
            </a>
            <a href="<?php echo e(route('payroll.slip.index')); ?>"
                class="block text-center py-2.5 text-sm border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5">
                ← Kembali
            </a>

            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 text-center">
                <p class="text-xs text-gray-400 dark:text-slate-500 mb-1">Status</p>
                <span class="px-3 py-1 rounded-full text-sm font-medium
                    <?php echo e($item->status === 'paid'
                        ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400'
                        : 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400'); ?>">
                    <?php echo e($item->status === 'paid' ? '✓ Sudah Dibayar' : 'Diproses'); ?>

                </span>
                <?php if($item->payrollRun?->processed_at): ?>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-2">
                    <?php echo e($item->payrollRun->processed_at->format('d M Y')); ?>

                </p>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="flex-1 min-w-0">
            <div id="slip-print"
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-8
                       print:shadow-none print:border-none print:rounded-none print:p-6 print:bg-white print:text-black">

                
                <div class="flex items-start justify-between border-b-2 border-gray-800 dark:border-white/20 pb-5 mb-6 print:border-gray-800">
                    <div>
                        <?php if($profile?->logo_path): ?>
                        <img src="<?php echo e(asset('storage/'.$profile->logo_path)); ?>" alt="Logo" class="h-10 mb-2 object-contain">
                        <?php endif; ?>
                        <p class="text-lg font-black text-gray-900 dark:text-white print:text-black uppercase">
                            <?php echo e($profile?->company_name ?? $companyName); ?>

                        </p>
                        <?php if($profile?->address): ?>
                        <p class="text-xs text-gray-500 dark:text-slate-400 print:text-gray-600 mt-0.5"><?php echo e($profile->address); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-700 dark:text-slate-200 print:text-gray-800 uppercase tracking-wide">Slip Gaji</p>
                        <?php
                            $period = $item->payrollRun?->period ?? '—';
                            [$yr, $mo] = str_contains($period, '-') ? explode('-', $period) : [$period, ''];
                            $monthName = $mo ? \Carbon\Carbon::createFromFormat('m', $mo)->locale('id')->translatedFormat('F Y') : $period;
                        ?>
                        <p class="text-xl font-black text-blue-600 dark:text-blue-400 print:text-blue-700 capitalize"><?php echo e($monthName); ?></p>
                        <p class="text-xs text-gray-400 dark:text-slate-500 print:text-gray-500 mt-1">
                            Dicetak: <?php echo e(now()->format('d M Y H:i')); ?>

                        </p>
                    </div>
                </div>

                
                <div class="grid grid-cols-2 gap-x-8 gap-y-1.5 mb-6 text-sm">
                    <?php $__currentLoopData = [
                        'Nama'         => $item->employee?->name ?? '-',
                        'NIK'          => $item->employee?->employee_id ?? '-',
                        'Jabatan'      => $item->employee?->position ?? '-',
                        'Departemen'   => $item->employee?->department ?? '-',
                        'Bank'         => $item->employee?->bank_name ?? '-',
                        'No. Rekening' => $item->employee?->bank_account ?? '-',
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex gap-2">
                        <span class="w-28 shrink-0 text-gray-500 dark:text-slate-400 print:text-gray-500"><?php echo e($label); ?></span>
                        <span class="text-gray-900 dark:text-white print:text-black font-medium">: <?php echo e($value); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">

                    
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400 print:text-gray-500 mb-2">Pendapatan</p>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5 print:divide-gray-200">
                                <tr>
                                    <td class="py-1.5 text-gray-600 dark:text-slate-300 print:text-gray-700">Gaji Pokok</td>
                                    <td class="py-1.5 text-right font-medium text-gray-900 dark:text-white print:text-black">Rp <?php echo e(number_format($item->base_salary, 0, ',', '.')); ?></td>
                                </tr>
                                <?php $compAllowances = $item->components->where('type','allowance'); ?>
                                <?php if($compAllowances->count()): ?>
                                    <?php $__currentLoopData = $compAllowances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="py-1.5 text-gray-600 dark:text-slate-300 print:text-gray-700"><?php echo e($c->name); ?></td>
                                        <td class="py-1.5 text-right font-medium text-gray-900 dark:text-white print:text-black">Rp <?php echo e(number_format($c->amount, 0, ',', '.')); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php elseif(($item->allowances ?? 0) > 0): ?>
                                <tr>
                                    <td class="py-1.5 text-gray-600 dark:text-slate-300 print:text-gray-700">Tunjangan</td>
                                    <td class="py-1.5 text-right font-medium text-gray-900 dark:text-white print:text-black">Rp <?php echo e(number_format($item->allowances, 0, ',', '.')); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if(($item->overtime_pay ?? 0) > 0): ?>
                                <tr>
                                    <td class="py-1.5 text-gray-600 dark:text-slate-300 print:text-gray-700">
                                        Upah Lembur
                                        <?php if($overtimes->count()): ?>
                                        <span class="text-xs text-gray-400">(<?php echo e($overtimes->count()); ?>x)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-1.5 text-right font-medium text-green-600 dark:text-green-400 print:text-green-700">+Rp <?php echo e(number_format($item->overtime_pay, 0, ',', '.')); ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="border-t-2 border-gray-300 dark:border-white/20 print:border-gray-400">
                                    <td class="py-2 font-semibold text-gray-900 dark:text-white print:text-black">Total Pendapatan</td>
                                    <td class="py-2 text-right font-bold text-gray-900 dark:text-white print:text-black">
                                        Rp <?php echo e(number_format($item->base_salary + ($item->allowances ?? 0) + ($item->overtime_pay ?? 0), 0, ',', '.')); ?>

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400 print:text-gray-500 mb-2">Potongan</p>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5 print:divide-gray-200">
                                <?php if(($item->deduction_absent ?? 0) > 0): ?>
                                <tr>
                                    <td class="py-1.5 text-gray-600 dark:text-slate-300 print:text-gray-700">Potongan Absen (<?php echo e($item->absent_days); ?>h)</td>
                                    <td class="py-1.5 text-right font-medium text-red-600 dark:text-red-400 print:text-red-700">-Rp <?php echo e(number_format($item->deduction_absent, 0, ',', '.')); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if(($item->deduction_late ?? 0) > 0): ?>
                                <tr>
                                    <td class="py-1.5 text-gray-600 dark:text-slate-300 print:text-gray-700">Potongan Terlambat (<?php echo e($item->late_days); ?>h)</td>
                                    <td class="py-1.5 text-right font-medium text-red-600 dark:text-red-400 print:text-red-700">-Rp <?php echo e(number_format($item->deduction_late, 0, ',', '.')); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if(($item->bpjs_employee ?? 0) > 0): ?>
                                <tr>
                                    <td class="py-1.5 text-gray-600 dark:text-slate-300 print:text-gray-700">BPJS (Kesehatan + Ketenagakerjaan)</td>
                                    <td class="py-1.5 text-right font-medium text-red-600 dark:text-red-400 print:text-red-700">-Rp <?php echo e(number_format($item->bpjs_employee, 0, ',', '.')); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if(($item->tax_pph21 ?? 0) > 0): ?>
                                <tr>
                                    <td class="py-1.5 text-gray-600 dark:text-slate-300 print:text-gray-700">PPh 21</td>
                                    <td class="py-1.5 text-right font-medium text-red-600 dark:text-red-400 print:text-red-700">-Rp <?php echo e(number_format($item->tax_pph21, 0, ',', '.')); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if(($item->deduction_other ?? 0) > 0): ?>
                                <?php $compDeductions = $item->components->where('type','deduction'); ?>
                                <?php if($compDeductions->count()): ?>
                                    <?php $__currentLoopData = $compDeductions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="py-1.5 text-gray-600 dark:text-slate-300 print:text-gray-700"><?php echo e($c->name); ?></td>
                                        <td class="py-1.5 text-right font-medium text-red-600 dark:text-red-400 print:text-red-700">-Rp <?php echo e(number_format($c->amount, 0, ',', '.')); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                <tr>
                                    <td class="py-1.5 text-gray-600 dark:text-slate-300 print:text-gray-700">Potongan Lain</td>
                                    <td class="py-1.5 text-right font-medium text-red-600 dark:text-red-400 print:text-red-700">-Rp <?php echo e(number_format($item->deduction_other, 0, ',', '.')); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php endif; ?>
                                <?php $totalDeduct = ($item->deduction_absent ?? 0) + ($item->deduction_late ?? 0) + ($item->bpjs_employee ?? 0) + ($item->tax_pph21 ?? 0) + ($item->deduction_other ?? 0); ?>
                                <tr class="border-t-2 border-gray-300 dark:border-white/20 print:border-gray-400">
                                    <td class="py-2 font-semibold text-gray-900 dark:text-white print:text-black">Total Potongan</td>
                                    <td class="py-2 text-right font-bold text-red-600 dark:text-red-400 print:text-red-700">-Rp <?php echo e(number_format($totalDeduct, 0, ',', '.')); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <?php if($overtimes->count()): ?>
                <div class="mb-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400 print:text-gray-500 mb-2">Rincian Lembur</p>
                    <table class="w-full text-xs border border-gray-100 dark:border-white/10 print:border-gray-200 rounded-xl overflow-hidden">
                        <thead class="bg-gray-50 dark:bg-white/5 print:bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left text-gray-500 dark:text-slate-400 print:text-gray-600">Tanggal</th>
                                <th class="px-3 py-2 text-center text-gray-500 dark:text-slate-400 print:text-gray-600">Waktu</th>
                                <th class="px-3 py-2 text-center text-gray-500 dark:text-slate-400 print:text-gray-600">Durasi</th>
                                <th class="px-3 py-2 text-right text-gray-500 dark:text-slate-400 print:text-gray-600">Upah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5 print:divide-gray-200">
                            <?php $__currentLoopData = $overtimes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-3 py-1.5 text-gray-700 dark:text-slate-300 print:text-gray-700"><?php echo e($ot->date->format('d M Y')); ?></td>
                                <td class="px-3 py-1.5 text-center text-gray-600 dark:text-slate-400 print:text-gray-600"><?php echo e(substr($ot->start_time, 0, 5)); ?> – <?php echo e(substr($ot->end_time, 0, 5)); ?></td>
                                <td class="px-3 py-1.5 text-center text-gray-700 dark:text-slate-300 print:text-gray-700"><?php echo e($ot->durationLabel()); ?></td>
                                <td class="px-3 py-1.5 text-right text-green-600 dark:text-green-400 print:text-green-700">Rp <?php echo e(number_format($ot->overtime_pay, 0, ',', '.')); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                
                <div class="bg-blue-50 dark:bg-blue-500/10 print:bg-blue-50 border border-blue-200 dark:border-blue-500/30 print:border-blue-200 rounded-2xl p-5 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 dark:text-blue-400 print:text-blue-700 font-medium">Take Home Pay (Gaji Bersih)</p>
                        <p class="text-xs text-blue-400 dark:text-blue-500 print:text-blue-500 mt-0.5">
                            Kehadiran: <?php echo e($item->present_days); ?>/<?php echo e($item->working_days); ?> hari
                        </p>
                    </div>
                    <p class="text-2xl font-black text-blue-700 dark:text-blue-300 print:text-blue-800">
                        Rp <?php echo e(number_format($item->net_salary, 0, ',', '.')); ?>

                    </p>
                </div>

                
                <div class="mt-8 pt-4 border-t border-gray-100 dark:border-white/10 print:border-gray-200 text-xs text-gray-400 dark:text-slate-500 print:text-gray-500 text-center">
                    Slip gaji ini diterbitkan secara otomatis oleh sistem. Tidak memerlukan tanda tangan.
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('head'); ?>
    <style>
    @media print {
        body > * { display: none !important; }
        #slip-print { display: block !important; position: fixed; top: 0; left: 0; width: 100%; background: white !important; color: black !important; }
        #slip-print * { color: inherit !important; }
    }
    </style>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\payroll\slip-show.blade.php ENDPATH**/ ?>