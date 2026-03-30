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
     <?php $__env->slot('header', null, []); ?> Project Billing — <?php echo e($project->name); ?> <?php $__env->endSlot(); ?>

    <?php $config = $project->billingConfig; ?>
    <div class="space-y-6">
        
        <div class="grid grid-cols-2 sm:grid-cols-6 gap-4">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Tipe Billing</p>
                <p class="font-semibold text-gray-900 dark:text-white"><?php echo e(['time_material'=>'T&M','milestone'=>'Milestone','retainer'=>'Retainer','fixed_price'=>'Fixed','termin'=>'Termin'][$config->billing_type ?? ''] ?? 'Belum diset'); ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Unbilled Hours</p>
                <p class="text-xl font-bold text-amber-500"><?php echo e(number_format($unbilledHours, 1)); ?>h</p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Unbilled Amount</p>
                <p class="text-lg font-bold text-amber-500">Rp <?php echo e(number_format($unbilledAmount, 0, ',', '.')); ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Billed</p>
                <p class="text-lg font-bold text-blue-500">Rp <?php echo e(number_format($totalBilled, 0, ',', '.')); ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Paid</p>
                <p class="text-lg font-bold text-green-500">Rp <?php echo e(number_format($totalPaid, 0, ',', '.')); ?></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-amber-200 dark:border-amber-500/20 p-4">
                <p class="text-xs text-amber-600 dark:text-amber-400 mb-1">Retensi Ditahan</p>
                <?php $totalRetention = $project->projectInvoices->sum('retention_amount') - $project->projectInvoices->sum('retention_released'); ?>
                <p class="text-lg font-bold text-amber-500">Rp <?php echo e(number_format(max(0, $totalRetention), 0, ',', '.')); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'project_billing', 'edit')): ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Konfigurasi Billing</h3>
                <form method="POST" action="<?php echo e(route('project-billing.config', $project)); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                            <select name="billing_type" required class="<?php echo e($cls); ?>">
                                <?php $__currentLoopData = ['time_material'=>'Time & Material','milestone'=>'Milestone','retainer'=>'Retainer','fixed_price'=>'Fixed Price','termin'=>'Termin (Progress)']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($v); ?>" <?php if(($config->billing_type ?? '')===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Hourly Rate</label>
                            <input type="number" name="hourly_rate" min="0" step="1000" value="<?php echo e($config->hourly_rate ?? 0); ?>" class="<?php echo e($cls); ?>">
                        </div>
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Retainer/Bulan</label>
                            <input type="number" name="retainer_amount" min="0" step="100000" value="<?php echo e($config->retainer_amount ?? 0); ?>" class="<?php echo e($cls); ?>">
                        </div>
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Next Billing</label>
                            <input type="date" name="next_billing_date" value="<?php echo e($config->next_billing_date?->format('Y-m-d') ?? ''); ?>" class="<?php echo e($cls); ?>">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nilai Kontrak</label>
                            <input type="number" name="contract_value" min="0" step="100000" value="<?php echo e($config->contract_value ?? $project->budget ?? 0); ?>" class="<?php echo e($cls); ?>">
                        </div>
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Retensi (%)</label>
                            <input type="number" name="retention_pct" min="0" max="100" step="0.5" value="<?php echo e($config->retention_pct ?? 5); ?>" class="<?php echo e($cls); ?>">
                        </div>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan Config</button>
                </form>
            </div>
            <?php endif; ?>

            
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'project_billing', 'create')): ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Generate Invoice</h3>
                <?php if(($config->billing_type ?? '') === 'time_material' || !$config): ?>
                <form method="POST" action="<?php echo e(route('project-billing.time-material', $project)); ?>" class="space-y-3 mb-4">
                    <?php echo csrf_field(); ?>
                    <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                    <p class="text-sm text-gray-500 dark:text-slate-400">Time & Material — <?php echo e(number_format($unbilledHours, 1)); ?>h unbilled</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Dari</label><input type="date" name="period_start" required class="<?php echo e($cls); ?>"></div>
                        <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Sampai</label><input type="date" name="period_end" required value="<?php echo e(date('Y-m-d')); ?>" class="<?php echo e($cls); ?>"></div>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Generate T&M Invoice</button>
                </form>
                <?php endif; ?>
                <?php if(($config->billing_type ?? '') === 'retainer'): ?>
                <form method="POST" action="<?php echo e(route('project-billing.retainer', $project)); ?>">
                    <?php echo csrf_field(); ?>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mb-2">Retainer: Rp <?php echo e(number_format($config->retainer_amount ?? 0, 0, ',', '.')); ?> / <?php echo e($config->retainer_cycle ?? 'monthly'); ?></p>
                    <button type="submit" class="w-full px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">Generate Retainer Invoice</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        
        <?php if(($config->billing_type ?? '') === 'termin'): ?>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'project_billing', 'create')): ?>
        <?php
            $contractVal = (float) ($config->contract_value ?: $project->budget);
            $totalGrossBilled = $project->projectInvoices->whereIn('billing_type', ['termin', 'milestone'])->sum('gross_amount');
            $totalRetentionHeld = $project->projectInvoices->sum('retention_amount') - $project->projectInvoices->sum('retention_released');
            $billedPct = $contractVal > 0 ? round($totalGrossBilled / $contractVal * 100, 1) : 0;
            $terminCount = $project->projectInvoices->where('billing_type', 'termin')->count();
        ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Termin / Progress Payment</h3>

            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-white/5">
                    <p class="text-[10px] text-gray-500 dark:text-slate-400 uppercase">Nilai Kontrak</p>
                    <p class="text-sm font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($contractVal, 0, ',', '.')); ?></p>
                </div>
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-white/5">
                    <p class="text-[10px] text-gray-500 dark:text-slate-400 uppercase">Sudah Billed</p>
                    <p class="text-sm font-bold text-blue-600">Rp <?php echo e(number_format($totalGrossBilled, 0, ',', '.')); ?> <span class="text-xs font-normal text-gray-400">(<?php echo e($billedPct); ?>%)</span></p>
                </div>
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-white/5">
                    <p class="text-[10px] text-gray-500 dark:text-slate-400 uppercase">Retensi Ditahan</p>
                    <p class="text-sm font-bold text-amber-500">Rp <?php echo e(number_format(max(0, $totalRetentionHeld), 0, ',', '.')); ?></p>
                </div>
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-white/5">
                    <p class="text-[10px] text-gray-500 dark:text-slate-400 uppercase">Termin Ke-</p>
                    <p class="text-sm font-bold text-gray-900 dark:text-white"><?php echo e($terminCount + 1); ?></p>
                </div>
            </div>

            
            <div class="w-full bg-gray-200 dark:bg-white/10 rounded-full h-2 mb-4">
                <div class="h-2 rounded-full bg-blue-500 transition-all" style="width:<?php echo e(min(100, $billedPct)); ?>%"></div>
            </div>

            
            <form method="POST" action="<?php echo e(route('project-billing.termin', $project)); ?>" class="space-y-3">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Progress Kumulatif (%)</label>
                        <input type="number" name="progress_pct" required min="0.01" max="100" step="0.01" value="<?php echo e($project->progress); ?>" class="<?php echo e($cls); ?>">
                        <p class="text-[10px] text-gray-400 mt-1">Progress proyek saat ini: <?php echo e($project->progress); ?>%</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Keterangan</label>
                        <input type="text" name="description" placeholder="Termin pekerjaan struktur" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <p class="text-xs text-gray-400 dark:text-slate-500">
                    Retensi: <?php echo e($config->retention_pct); ?>% · Sisa kontrak: Rp <?php echo e(number_format(max(0, $contractVal - $totalGrossBilled), 0, ',', '.')); ?>

                </p>
                <button type="submit" class="w-full px-4 py-2 text-sm bg-indigo-600 text-white rounded-xl hover:bg-indigo-700">Generate Termin #<?php echo e($terminCount + 1); ?></button>
            </form>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        
        <?php $retentionInvoices = $project->projectInvoices->filter(fn($pi) => $pi->retention_amount > 0 && !$pi->isRetentionReleased()); ?>
        <?php if($retentionInvoices->isNotEmpty()): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-amber-200 dark:border-amber-500/20 overflow-hidden">
            <div class="px-6 py-4 border-b border-amber-100 dark:border-amber-500/10 bg-amber-50 dark:bg-amber-500/5">
                <h3 class="font-semibold text-amber-800 dark:text-amber-400">Retensi Belum Dirilis</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-white/5">
                <?php $__currentLoopData = $retentionInvoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ri): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            <?php echo e($ri->termin_number ? "Termin #{$ri->termin_number}" : ($ri->invoice?->number ?? 'Invoice')); ?>

                        </p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">
                            Retensi: Rp <?php echo e(number_format($ri->retention_amount, 0, ',', '.')); ?>

                            · Dirilis: Rp <?php echo e(number_format($ri->retention_released, 0, ',', '.')); ?>

                            · Sisa: Rp <?php echo e(number_format($ri->retentionOutstanding(), 0, ',', '.')); ?>

                        </p>
                    </div>
                    <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'project_billing', 'edit')): ?>
                    <form method="POST" action="<?php echo e(route('project-billing.release-retention', $ri)); ?>" class="flex items-center gap-2">
                        <?php echo csrf_field(); ?>
                        <input type="number" name="amount" step="1" min="1" max="<?php echo e($ri->retentionOutstanding()); ?>" value="<?php echo e($ri->retentionOutstanding()); ?>" class="w-32 px-2 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <button type="submit" class="px-3 py-1.5 text-xs bg-amber-600 text-white rounded-lg hover:bg-amber-700">Rilis</button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if(($config->billing_type ?? '') === 'milestone'): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">Milestones</h3>
                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'project_billing', 'create')): ?>
                <button onclick="document.getElementById('modal-ms').classList.remove('hidden')" class="text-xs px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Milestone</button>
                <?php endif; ?>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr><th class="px-4 py-3 text-left">Milestone</th><th class="px-4 py-3 text-right">Nilai</th><th class="px-4 py-3 text-center">Due</th><th class="px-4 py-3 text-center">Status</th><th class="px-4 py-3 text-center">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $project->milestones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $mc = ['pending'=>'gray','completed'=>'amber','invoiced'=>'green'][$ms->status] ?? 'gray'; ?>
                        <tr>
                            <td class="px-4 py-3 text-gray-900 dark:text-white"><?php echo e($ms->name); ?></td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp <?php echo e(number_format($ms->amount, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500"><?php echo e($ms->due_date?->format('d/m/Y') ?? '-'); ?></td>
                            <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($mc); ?>-100 text-<?php echo e($mc); ?>-700 dark:bg-<?php echo e($mc); ?>-500/20 dark:text-<?php echo e($mc); ?>-400"><?php echo e(ucfirst($ms->status)); ?></span></td>
                            <td class="px-4 py-3 text-center">
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'project_billing', 'edit')): ?>
                                <?php if($ms->status === 'pending'): ?>
                                <form method="POST" action="<?php echo e(route('project-billing.milestones.complete', $ms)); ?>" class="inline"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="text-xs px-2 py-1 bg-amber-600 text-white rounded-lg">Complete</button>
                                </form>
                                <?php elseif($ms->status === 'completed'): ?>
                                <form method="POST" action="<?php echo e(route('project-billing.milestones.invoice', $ms)); ?>" class="inline"><?php echo csrf_field(); ?>
                                    <button type="submit" class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg">Invoice</button>
                                </form>
                                <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if($unbilledTimesheets->isNotEmpty()): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Timesheet Unbilled</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr><th class="px-4 py-3 text-left">Tanggal</th><th class="px-4 py-3 text-left">Staff</th><th class="px-4 py-3 text-left">Deskripsi</th><th class="px-4 py-3 text-right">Jam</th><th class="px-4 py-3 text-right">Rate</th><th class="px-4 py-3 text-right">Total</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $unbilledTimesheets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="px-4 py-3 text-xs text-gray-500"><?php echo e($ts->date->format('d/m/Y')); ?></td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white"><?php echo e($ts->user->name ?? '-'); ?></td>
                            <td class="px-4 py-3 text-gray-500 dark:text-slate-400 text-xs"><?php echo e(Str::limit($ts->description, 40)); ?></td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white"><?php echo e($ts->hours); ?>h</td>
                            <td class="px-4 py-3 text-right text-gray-500">Rp <?php echo e(number_format($ts->hourly_rate ?: ($config->hourly_rate ?? 0), 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp <?php echo e(number_format($ts->laborCost(), 0, ',', '.')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if($project->projectInvoices->isNotEmpty()): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Riwayat Invoice</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr><th class="px-4 py-3 text-left">Invoice</th><th class="px-4 py-3 text-center">Tipe</th><th class="px-4 py-3 text-right">Gross</th><th class="px-4 py-3 text-right">Retensi</th><th class="px-4 py-3 text-right">Tagihan</th><th class="px-4 py-3 text-center">Status</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $project->projectInvoices->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $ic = ['draft'=>'gray','invoiced'=>'blue','paid'=>'green'][$pi->status] ?? 'gray'; ?>
                        <tr>
                            <td class="px-4 py-3">
                                <span class="text-gray-900 dark:text-white font-mono text-xs"><?php echo e($pi->invoice->number ?? '-'); ?></span>
                                <?php if($pi->termin_number): ?><span class="ml-1 text-[10px] text-blue-500">T#<?php echo e($pi->termin_number); ?></span><?php endif; ?>
                                <?php if($pi->billing_type === 'retention_release'): ?><span class="ml-1 text-[10px] text-amber-500">Rilis Retensi</span><?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500"><?php echo e(['time_material'=>'T&M','milestone'=>'Milestone','retainer'=>'Retainer','fixed_price'=>'Fixed','termin'=>'Termin','retention_release'=>'Retensi'][$pi->billing_type] ?? $pi->billing_type); ?></td>
                            <td class="px-4 py-3 text-right text-xs text-gray-500 font-mono"><?php echo e($pi->gross_amount > 0 ? 'Rp '.number_format($pi->gross_amount, 0, ',', '.') : '-'); ?></td>
                            <td class="px-4 py-3 text-right text-xs <?php echo e($pi->retention_amount > 0 ? 'text-amber-500' : 'text-gray-400'); ?> font-mono"><?php echo e($pi->retention_amount > 0 ? 'Rp '.number_format($pi->retention_amount, 0, ',', '.') : '-'); ?></td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp <?php echo e(number_format($pi->total_amount, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($ic); ?>-100 text-<?php echo e($ic); ?>-700 dark:bg-<?php echo e($ic); ?>-500/20 dark:text-<?php echo e($ic); ?>-400"><?php echo e(ucfirst($pi->status)); ?></span></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    
    <div id="modal-ms" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Milestone</h3>
                <button onclick="document.getElementById('modal-ms').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('project-billing.milestones.store', $project)); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label><input type="text" name="name" required class="<?php echo e($cls); ?>"></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nilai (Rp) *</label><input type="number" name="amount" required min="0" step="1000" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Due Date</label><input type="date" name="due_date" class="<?php echo e($cls); ?>"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-ms').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\project-billing\show.blade.php ENDPATH**/ ?>