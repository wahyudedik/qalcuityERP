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
     <?php $__env->slot('header', null, []); ?> Kontrak — <?php echo e($contract->contract_number); ?> <?php $__env->endSlot(); ?>

    <div class="space-y-6">
        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e($contract->title); ?></h2>
                    <p class="text-sm text-gray-500 dark:text-slate-400"><?php echo e($contract->contract_number); ?> · <?php echo e($contract->party_type === 'customer' ? '👤' : '🏢'); ?> <?php echo e($contract->partyName()); ?></p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <?php
                        $sc = ['draft'=>'gray','active'=>'green','expired'=>'red','terminated'=>'red','renewed'=>'purple'][$contract->status] ?? 'gray';
                        $sl = ['draft'=>'Draft','active'=>'Aktif','expired'=>'Expired','terminated'=>'Terminasi','renewed'=>'Renewed'][$contract->status] ?? $contract->status;
                    ?>
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 dark:bg-<?php echo e($sc); ?>-500/20 dark:text-<?php echo e($sc); ?>-400"><?php echo e($sl); ?></span>
                    <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'contracts', 'edit')): ?>
                    <?php if($contract->status === 'draft'): ?>
                    <form method="POST" action="<?php echo e(route('contracts.activate', $contract)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                        <button type="submit" class="px-3 py-1 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Aktifkan</button>
                    </form>
                    <?php endif; ?>
                    <?php if($contract->status === 'active'): ?>
                    <form method="POST" action="<?php echo e(route('contracts.terminate', $contract)); ?>" onsubmit="return confirm('Terminasi kontrak ini?')"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                        <button type="submit" class="px-3 py-1 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">Terminasi</button>
                    </form>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'contracts', 'create')): ?>
                    <?php if(in_array($contract->status, ['active', 'expired'])): ?>
                    <form method="POST" action="<?php echo e(route('contracts.renew', $contract)); ?>"><?php echo csrf_field(); ?>
                        <button type="submit" class="px-3 py-1 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">Renew</button>
                    </form>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Periode</p><p class="text-gray-900 dark:text-white"><?php echo e($contract->start_date->format('d/m/Y')); ?> — <?php echo e($contract->end_date->format('d/m/Y')); ?></p></div>
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Sisa Hari</p><p class="font-semibold <?php echo e($contract->daysRemaining() < 30 ? 'text-amber-500' : 'text-gray-900 dark:text-white'); ?>"><?php echo e($contract->daysRemaining()); ?> hari</p></div>
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Nilai Kontrak</p><p class="font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($contract->value, 0, ',', '.')); ?></p></div>
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Billing</p><p class="text-gray-900 dark:text-white">Rp <?php echo e(number_format($contract->billing_amount, 0, ',', '.')); ?> / <?php echo e(['monthly'=>'bulan','quarterly'=>'triwulan','semi_annual'=>'semester','annual'=>'tahun','one_time'=>'sekali'][$contract->billing_cycle] ?? $contract->billing_cycle); ?></p></div>
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Next Billing</p><p class="text-gray-900 dark:text-white"><?php echo e($contract->next_billing_date?->format('d/m/Y') ?? '-'); ?></p></div>
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Auto Renew</p><p class="text-gray-900 dark:text-white"><?php echo e($contract->auto_renew ? '✅ Ya' : '❌ Tidak'); ?></p></div>
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Kategori</p><p class="text-gray-900 dark:text-white"><?php echo e(['service'=>'Jasa','lease'=>'Sewa','supply'=>'Supply','maintenance'=>'Maintenance','subscription'=>'Langganan'][$contract->category] ?? $contract->category); ?></p></div>
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Dibuat oleh</p><p class="text-gray-900 dark:text-white"><?php echo e($contract->user->name ?? '-'); ?></p></div>
            </div>

            
            <?php if($contract->sla_response_hours || $contract->sla_resolution_hours || $contract->sla_uptime_pct): ?>
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/10">
                <h4 class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase mb-2">SLA</h4>
                <div class="flex flex-wrap gap-4 text-sm">
                    <?php if($contract->sla_response_hours): ?><span class="text-gray-700 dark:text-slate-300">Response: <span class="font-semibold text-gray-900 dark:text-white"><?php echo e($contract->sla_response_hours); ?>h</span></span><?php endif; ?>
                    <?php if($contract->sla_resolution_hours): ?><span class="text-gray-700 dark:text-slate-300">Resolution: <span class="font-semibold text-gray-900 dark:text-white"><?php echo e($contract->sla_resolution_hours); ?>h</span></span><?php endif; ?>
                    <?php if($contract->sla_uptime_pct): ?><span class="text-gray-700 dark:text-slate-300">Uptime: <span class="font-semibold text-gray-900 dark:text-white"><?php echo e($contract->sla_uptime_pct); ?>%</span></span><?php endif; ?>
                    <?php $compliance = $contract->slaComplianceRate(); ?>
                    <?php if($compliance !== null): ?><span class="text-gray-700 dark:text-slate-300">Compliance: <span class="font-semibold <?php echo e($compliance >= 90 ? 'text-green-500' : 'text-red-500'); ?>"><?php echo e($compliance); ?>%</span></span><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        
        <?php if($contract->status === 'active' && $contract->next_billing_date && $contract->billing_cycle !== 'one_time'): ?>
        <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-blue-700 dark:text-blue-400">Billing berikutnya: <?php echo e($contract->next_billing_date->format('d/m/Y')); ?></p>
                <p class="text-xs text-blue-600 dark:text-blue-300">Rp <?php echo e(number_format($contract->billing_amount, 0, ',', '.')); ?></p>
            </div>
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'contracts', 'create')): ?>
            <form method="POST" action="<?php echo e(route('contracts.billing', $contract)); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Generate Billing</button>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Riwayat Billing</h3>
            </div>
            <?php if($contract->billings->isNotEmpty()): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Periode</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left">Invoice</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $contract->billings->sortByDesc('billing_date'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $bc = ['pending'=>'amber','invoiced'=>'blue','paid'=>'green','cancelled'=>'gray'][$b->status] ?? 'gray'; ?>
                        <tr>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400"><?php echo e($b->billing_date->format('d/m/Y')); ?></td>
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-slate-300"><?php echo e($b->period_start->format('d/m')); ?> — <?php echo e($b->period_end->format('d/m/Y')); ?></td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp <?php echo e(number_format($b->amount, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($bc); ?>-100 text-<?php echo e($bc); ?>-700 dark:bg-<?php echo e($bc); ?>-500/20 dark:text-<?php echo e($bc); ?>-400"><?php echo e(ucfirst($b->status)); ?></span></td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400"><?php echo e($b->invoice->invoice_number ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="px-6 py-8 text-center text-gray-400 dark:text-slate-500 text-sm">Belum ada billing.</div>
            <?php endif; ?>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">SLA Incidents</h3>
                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'contracts', 'create')): ?>
                <button onclick="document.getElementById('modal-sla').classList.remove('hidden')" class="text-xs px-3 py-1 bg-amber-600 text-white rounded-lg hover:bg-amber-700">+ Insiden</button>
                <?php endif; ?>
            </div>
            <?php if($contract->slaLogs->isNotEmpty()): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Tipe</th>
                            <th class="px-4 py-3 text-left">Deskripsi</th>
                            <th class="px-4 py-3 text-center">Dilaporkan</th>
                            <th class="px-4 py-3 text-center">Response</th>
                            <th class="px-4 py-3 text-center">Resolved</th>
                            <th class="px-4 py-3 text-center">SLA</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $contract->slaLogs->sortByDesc('reported_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="px-4 py-3 text-xs text-gray-700 dark:text-slate-300"><?php echo e(['support'=>'Support','downtime'=>'Downtime','delivery_delay'=>'Keterlambatan'][$log->incident_type] ?? $log->incident_type); ?></td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white"><?php echo e(Str::limit($log->description, 40)); ?></td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500 dark:text-slate-400"><?php echo e($log->reported_at->format('d/m H:i')); ?></td>
                            <td class="px-4 py-3 text-center text-xs"><?php echo e($log->responseHours() !== null ? $log->responseHours() . 'h' : '-'); ?></td>
                            <td class="px-4 py-3 text-center text-xs"><?php echo e($log->resolutionHours() !== null ? $log->resolutionHours() . 'h' : '-'); ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php if($log->sla_met === true): ?><span class="text-green-500">✅</span>
                                <?php elseif($log->sla_met === false): ?><span class="text-red-500">❌</span>
                                <?php else: ?><span class="text-gray-400">—</span><?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if(!$log->resolved_at): ?>
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'contracts', 'edit')): ?>
                                <button onclick="openResolve(<?php echo e($log->id); ?>)" class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Resolve</button>
                                <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="px-6 py-8 text-center text-gray-400 dark:text-slate-500 text-sm">Belum ada insiden SLA.</div>
            <?php endif; ?>
        </div>
    </div>

    
    <div id="modal-sla" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Catat Insiden SLA</h3>
                <button onclick="document.getElementById('modal-sla').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('contracts.sla.store', $contract)); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                    <select name="incident_type" required class="<?php echo e($cls); ?>">
                        <option value="support">Support</option><option value="downtime">Downtime</option><option value="delivery_delay">Keterlambatan</option>
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi *</label><input type="text" name="description" required class="<?php echo e($cls); ?>"></div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Waktu Dilaporkan *</label><input type="datetime-local" name="reported_at" required value="<?php echo e(now()->format('Y-m-d\TH:i')); ?>" class="<?php echo e($cls); ?>"></div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-sla').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-amber-600 text-white rounded-xl hover:bg-amber-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-resolve" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Resolve Insiden</h3>
                <button onclick="document.getElementById('modal-resolve').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-resolve" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Waktu Response</label><input type="datetime-local" name="responded_at" class="<?php echo e($cls); ?>"></div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Waktu Resolved *</label><input type="datetime-local" name="resolved_at" required value="<?php echo e(now()->format('Y-m-d\TH:i')); ?>" class="<?php echo e($cls); ?>"></div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label><input type="text" name="notes" class="<?php echo e($cls); ?>"></div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-resolve').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Resolve</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    function openResolve(id) {
        document.getElementById('form-resolve').action = '<?php echo e(url("contracts/sla")); ?>/' + id + '/resolve';
        document.getElementById('modal-resolve').classList.remove('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\contracts\show.blade.php ENDPATH**/ ?>