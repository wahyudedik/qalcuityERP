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
     <?php $__env->slot('header', null, []); ?> <?php echo e($companyGroup->name); ?> <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <form method="GET" class="flex items-center gap-2">
            <label class="text-sm text-gray-500">Periode:</label>
            <input type="month" name="period" value="<?php echo e($period); ?>"
                class="rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 px-3 py-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700">Tampilkan</button>
        </form>
        <a href="<?php echo e(route('company-groups.export', [$companyGroup, 'period' => $period])); ?>"
           class="px-4 py-2 border border-gray-200 text-gray-600 rounded-xl text-sm hover:bg-gray-50 transition">
            📥 Export CSV
        </a>
        <a href="<?php echo e(route('company-groups.index')); ?>" class="text-sm text-gray-400 hover:text-gray-600 ml-auto">← Kembali</a>
    </div>

    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <?php $profit = $report['consolidated_profit'] ?? 0; ?>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Omzet</p>
            <p class="text-lg font-bold text-green-500 mt-1"><?php echo e($report['formatted']['total_revenue']); ?></p>
            <p class="text-[10px] text-gray-400"><?php echo e(count($report['revenues'])); ?> perusahaan</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Biaya</p>
            <p class="text-lg font-bold text-red-500 mt-1"><?php echo e($report['formatted']['total_expense']); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-amber-200 p-4">
            <p class="text-xs text-amber-600">Eliminasi IC</p>
            <p class="text-lg font-bold text-amber-500 mt-1"><?php echo e($report['formatted']['elimination']); ?></p>
            <p class="text-[10px] text-gray-400"><?php echo e(count($report['elimination']['items'] ?? [])); ?> transaksi</p>
        </div>
        <div class="bg-white rounded-xl border <?php echo e($profit >= 0 ? 'border-blue-200' : 'border-red-200'); ?> p-4">
            <p class="text-xs text-gray-500">Laba Konsolidasi</p>
            <p class="text-lg font-bold <?php echo e($profit >= 0 ? 'text-blue-500' : 'text-red-500'); ?> mt-1"><?php echo e($report['formatted']['cons_profit']); ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        
        <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900 text-sm">📊 Laba Rugi per Perusahaan</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr><th class="px-4 py-3 text-left">Perusahaan</th><th class="px-4 py-3 text-right">Omzet</th><th class="px-4 py-3 text-right">Biaya</th><th class="px-4 py-3 text-right">Laba</th><th class="px-4 py-3 text-right">Margin</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__currentLoopData = $report['revenues']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tid => $rev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $exp = $report['expenses'][$tid]['amount'] ?? 0; $p = $rev['amount'] - $exp; $margin = $rev['amount'] > 0 ? round($p / $rev['amount'] * 100, 1) : 0; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($rev['name']); ?></td>
                        <td class="px-4 py-3 text-right text-green-500 font-mono">Rp <?php echo e(number_format($rev['amount'], 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right text-red-500 font-mono">Rp <?php echo e(number_format($exp, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right font-mono font-medium <?php echo e($p >= 0 ? 'text-blue-500' : 'text-red-500'); ?>">Rp <?php echo e(number_format($p, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right text-xs <?php echo e($margin >= 20 ? 'text-green-500' : ($margin >= 0 ? 'text-amber-500' : 'text-red-500')); ?>"><?php echo e($margin); ?>%</td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <tr class="bg-gray-50 font-bold">
                        <td class="px-4 py-3 text-gray-900">TOTAL KONSOLIDASI</td>
                        <td class="px-4 py-3 text-right text-green-500 font-mono"><?php echo e($report['formatted']['total_revenue']); ?></td>
                        <td class="px-4 py-3 text-right text-red-500 font-mono"><?php echo e($report['formatted']['total_expense']); ?></td>
                        <td class="px-4 py-3 text-right font-mono <?php echo e(($report['consolidated_profit'] ?? 0) >= 0 ? 'text-blue-500' : 'text-red-500'); ?>"><?php echo e($report['formatted']['cons_profit']); ?></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 text-sm mb-4">💧 Arus Kas Konsolidasi</h3>
            <div class="space-y-3">
                <?php $__currentLoopData = $cashFlow['per_member'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="p-3 rounded-xl bg-gray-50">
                    <p class="text-xs font-medium text-gray-700"><?php echo e($cf['name']); ?></p>
                    <div class="flex justify-between mt-1 text-xs">
                        <span class="text-green-500">+Rp <?php echo e(number_format($cf['inflow'], 0, ',', '.')); ?></span>
                        <span class="text-red-500">-Rp <?php echo e(number_format($cf['outflow'], 0, ',', '.')); ?></span>
                        <span class="font-bold <?php echo e($cf['net'] >= 0 ? 'text-blue-500' : 'text-red-500'); ?>">= Rp <?php echo e(number_format($cf['net'], 0, ',', '.')); ?></span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="border-t border-gray-100 pt-3">
                    <div class="flex justify-between text-sm font-bold">
                        <span class="text-gray-900">Net Cash</span>
                        <span class="<?php echo e(($cashFlow['net_change'] ?? 0) >= 0 ? 'text-blue-500' : 'text-red-500'); ?>">Rp <?php echo e(number_format($cashFlow['net_change'] ?? 0, 0, ',', '.')); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <?php if(!empty($report['balance_sheet'])): ?>
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900 text-sm">🏦 Neraca Konsolidasi</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100">
            <?php $__currentLoopData = $report['balance_sheet']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="p-5">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3"><?php echo e($data['label']); ?></p>
                <p class="text-xl font-black text-gray-900 mb-3">Rp <?php echo e(number_format($data['total'], 0, ',', '.')); ?></p>
                <div class="space-y-1.5">
                    <?php $__currentLoopData = $data['per_member']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500"><?php echo e($m['name']); ?></span>
                        <span class="font-mono text-gray-700">Rp <?php echo e(number_format($m['amount'], 0, ',', '.')); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if(!empty($trend)): ?>
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6">
        <h3 class="font-semibold text-gray-900 text-sm mb-4">📈 Tren 6 Bulan Terakhir</h3>
        <div class="space-y-2">
            <?php $maxRev = collect($trend)->max('revenue') ?: 1; ?>
            <?php $__currentLoopData = $trend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400 w-16 shrink-0"><?php echo e($t['label']); ?></span>
                <div class="flex-1 bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full bg-blue-500" style="width:<?php echo e(min(100, round($t['revenue'] / $maxRev * 100))); ?>%"></div>
                </div>
                <span class="text-xs font-mono text-gray-600 w-28 text-right">Rp <?php echo e(number_format($t['revenue'], 0, ',', '.')); ?></span>
                <span class="text-xs <?php echo e($t['profit'] >= 0 ? 'text-green-500' : 'text-red-500'); ?> w-24 text-right"><?php echo e($t['profit'] >= 0 ? '+' : ''); ?>Rp <?php echo e(number_format($t['profit'], 0, ',', '.')); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 text-sm mb-4">🏢 Anggota Grup (<?php echo e($companyGroup->members->count()); ?>)</h3>
            <div class="space-y-2 mb-4">
                <?php $__currentLoopData = $companyGroup->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <div>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($member->tenant?->name ?? 'Unknown'); ?></p>
                        <p class="text-xs text-gray-400"><?php echo e(ucfirst($member->role)); ?> · ID #<?php echo e($member->tenant_id); ?></p>
                    </div>
                    <?php if($member->role !== 'owner'): ?>
                    <form method="POST" action="<?php echo e(route('company-groups.members.remove', [$companyGroup, $member->tenant])); ?>" onsubmit="return confirm('Hapus dari grup?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="text-xs text-red-400 hover:text-red-600">Hapus</button>
                    </form>
                    <?php else: ?>
                    <span class="text-xs text-green-500">Owner</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <form method="POST" action="<?php echo e(route('company-groups.members.add', $companyGroup)); ?>" class="space-y-2">
                <?php echo csrf_field(); ?>
                <select name="tenant_id" required class="w-full rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 px-3 py-2">
                    <option value="">— Pilih perusahaan —</option>
                    <?php $__currentLoopData = $availableTenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($t->id); ?>"><?php echo e($t->name); ?> (#<?php echo e($t->id); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700">+ Tambah Anggota</button>
            </form>
        </div>

        
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 text-sm mb-4">🔄 Catat Transaksi Intercompany</h3>
            <form method="POST" action="<?php echo e(route('company-groups.transactions.store', $companyGroup)); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 px-3 py-2'; ?>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Dari Perusahaan *</label>
                        <select name="from_tenant_id" required class="<?php echo e($cls); ?>">
                            <option value="">Pilih...</option>
                            <?php $__currentLoopData = $companyGroup->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m->id); ?>"><?php echo e($m->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Ke Perusahaan *</label>
                        <select name="to_tenant_id" required class="<?php echo e($cls); ?>">
                            <option value="">Pilih...</option>
                            <?php $__currentLoopData = $companyGroup->members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m->id); ?>"><?php echo e($m->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe *</label>
                        <select name="type" required class="<?php echo e($cls); ?>">
                            <option value="sale">🛒 Penjualan Antar Entitas</option>
                            <option value="loan">💰 Pinjaman Antar Entitas</option>
                            <option value="expense_allocation">📊 Alokasi Biaya</option>
                            <option value="dividend">💵 Dividen</option>
                            <option value="management_fee">🏢 Management Fee</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah (Rp) *</label>
                        <input type="number" name="amount" min="1" step="1000" required placeholder="0" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal *</label>
                        <input type="date" name="date" value="<?php echo e(today()->toDateString()); ?>" required class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                        <input type="text" name="description" placeholder="Opsional" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">Catat Transaksi</button>
            </form>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mt-6">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 text-sm">Riwayat Transaksi Intercompany</h3>
            <div class="flex gap-2 text-xs">
                <span class="px-2 py-1 bg-green-500/20 text-green-400 rounded-full"><?php echo e($transactions->where('status','posted')->count()); ?> posted</span>
                <span class="px-2 py-1 bg-amber-500/20 text-amber-400 rounded-full"><?php echo e($transactions->where('status','pending')->count()); ?> pending</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Dari → Ke</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-left">Ref</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $typeLabels = ['sale'=>'🛒 Penjualan','loan'=>'💰 Pinjaman','expense_allocation'=>'📊 Alokasi','dividend'=>'💵 Dividen','management_fee'=>'🏢 Mgmt Fee']; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap"><?php echo e($tx->date->format('d M Y')); ?></td>
                        <td class="px-4 py-3 text-xs">
                            <span class="font-medium text-gray-900"><?php echo e($tx->fromTenant?->name ?? '#'.$tx->from_tenant_id); ?></span>
                            <span class="text-gray-400 mx-1">→</span>
                            <span class="font-medium text-gray-900"><?php echo e($tx->toTenant?->name ?? '#'.$tx->to_tenant_id); ?></span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500"><?php echo e($typeLabels[$tx->type] ?? $tx->type); ?></td>
                        <td class="px-4 py-3 text-xs font-mono text-gray-400"><?php echo e($tx->reference); ?></td>
                        <td class="px-4 py-3 text-right font-mono font-medium text-gray-900">Rp <?php echo e(number_format($tx->amount, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($tx->status === 'posted' ? 'bg-green-100 text-green-700' : ($tx->status === 'voided' ? 'bg-gray-100 text-gray-500' : 'bg-amber-100 text-amber-700')); ?>">
                                <?php echo e(ucfirst($tx->status)); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if($tx->status === 'pending'): ?>
                            <div class="flex items-center justify-center gap-1">
                                <form method="POST" action="<?php echo e(route('company-groups.transactions.post', $tx)); ?>"><?php echo csrf_field(); ?>
                                    <button class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Post</button>
                                </form>
                                <form method="POST" action="<?php echo e(route('company-groups.transactions.void', $tx)); ?>" onsubmit="return confirm('Void?')"><?php echo csrf_field(); ?>
                                    <button class="text-xs px-2 py-1 text-red-400 hover:text-red-600">Void</button>
                                </form>
                            </div>
                            <?php elseif($tx->status === 'posted'): ?>
                            <span class="text-xs text-green-500">✓</span>
                            <?php else: ?>
                            <span class="text-xs text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Belum ada transaksi intercompany.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3"><?php echo e($transactions->links()); ?></div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/company-groups/show.blade.php ENDPATH**/ ?>