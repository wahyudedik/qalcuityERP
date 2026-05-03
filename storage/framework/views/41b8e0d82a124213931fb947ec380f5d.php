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
     <?php $__env->slot('header', null, []); ?> Jadwal Depresiasi — <?php echo e($asset->name); ?> <?php $__env->endSlot(); ?>

    
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-500">Kode Aset</p>
                <p class="font-mono text-sm font-medium text-gray-900 mt-0.5"><?php echo e($asset->asset_code); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Harga Perolehan</p>
                <p class="text-sm font-medium text-gray-900 mt-0.5">Rp <?php echo e(number_format($asset->purchase_price, 0, ',', '.')); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Nilai Buku Saat Ini</p>
                <p class="text-sm font-medium text-blue-600 mt-0.5">Rp <?php echo e(number_format($asset->current_value, 0, ',', '.')); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Nilai Sisa / Metode</p>
                <p class="text-sm font-medium text-gray-900 mt-0.5">
                    Rp <?php echo e(number_format($asset->salvage_value, 0, ',', '.')); ?> /
                    <?php echo e($asset->depreciation_method === 'straight_line' ? 'Garis Lurus' : 'Saldo Menurun'); ?>

                </p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Tanggal Beli</p>
                <p class="text-sm font-medium text-gray-900 mt-0.5"><?php echo e($asset->purchase_date->format('d M Y')); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Umur Ekonomis</p>
                <p class="text-sm font-medium text-gray-900 mt-0.5"><?php echo e($asset->useful_life_years); ?> tahun</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Depresiasi/Bulan</p>
                <p class="text-sm font-medium text-gray-900 mt-0.5">Rp <?php echo e(number_format($asset->monthlyDepreciation(), 0, ',', '.')); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Status</p>
                <?php $sc = ['active'=>'green','maintenance'=>'yellow','disposed'=>'red','retired'=>'gray'][$asset->status] ?? 'gray'; ?>
                <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 $sc }}-500/20 $sc }}-400">
                    <?php echo e(ucfirst($asset->status)); ?>

                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        
        <div>
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Riwayat Depresiasi (<?php echo e($depreciations->count()); ?> periode)</h2>
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <?php if($depreciations->isEmpty()): ?>
                <div class="px-4 py-10 text-center text-sm text-gray-400">
                    Belum ada depresiasi yang dicatat.
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Periode</th>
                                <th class="px-4 py-3 text-right">Depresiasi</th>
                                <th class="px-4 py-3 text-right">Nilai Buku</th>
                                <th class="px-4 py-3 text-center">Jurnal GL</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__currentLoopData = $depreciations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 font-mono text-xs text-gray-700"><?php echo e($dep->period); ?></td>
                                <td class="px-4 py-2.5 text-right text-red-600">
                                    (Rp <?php echo e(number_format($dep->depreciation_amount, 0, ',', '.')); ?>)
                                </td>
                                <td class="px-4 py-2.5 text-right font-medium text-gray-900">
                                    Rp <?php echo e(number_format($dep->book_value_after, 0, ',', '.')); ?>

                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <?php if($dep->journal_entry_id): ?>
                                        <a href="<?php echo e(route('journals.show', $dep->journalEntry)); ?>"
                                            class="text-xs text-green-600 hover:underline font-mono">
                                            <?php echo e($dep->journalEntry->number); ?>

                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-amber-500" title="Jurnal GL belum dibuat untuk periode ini">⚠ Belum</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                        <tfoot class="bg-gray-50 text-xs font-medium">
                            <tr>
                                <td class="px-4 py-2.5 text-gray-600">Total</td>
                                <td class="px-4 py-2.5 text-right text-red-600">
                                    (Rp <?php echo e(number_format($depreciations->sum('depreciation_amount'), 0, ',', '.')); ?>)
                                </td>
                                <td class="px-4 py-2.5 text-right text-gray-900">—</td>
                                <td class="px-4 py-2.5 text-center text-xs text-gray-400">
                                    <?php echo e($depreciations->whereNotNull('journal_entry_id')->count()); ?>/<?php echo e($depreciations->count()); ?> diposting
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div>
            <h2 class="text-sm font-semibold text-gray-900 mb-3">
                Proyeksi Sisa
                <?php if(count($projected) >= 60): ?>
                <span class="text-xs font-normal text-gray-400">(ditampilkan 60 periode)</span>
                <?php endif; ?>
            </h2>
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <?php if(empty($projected)): ?>
                <div class="px-4 py-10 text-center text-sm text-gray-400">
                    <?php if($asset->status !== 'active'): ?>
                    Aset tidak aktif — tidak ada proyeksi.
                    <?php else: ?>
                    Aset sudah mencapai nilai sisa.
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto max-h-96 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left">Periode</th>
                                <th class="px-4 py-3 text-right">Depresiasi</th>
                                <th class="px-4 py-3 text-right">Proyeksi Nilai Buku</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__currentLoopData = $projected; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 font-mono text-xs text-gray-500"><?php echo e($proj['period']); ?></td>
                                <td class="px-4 py-2.5 text-right text-orange-600">
                                    (Rp <?php echo e(number_format($proj['amount'], 0, ',', '.')); ?>)
                                </td>
                                <td class="px-4 py-2.5 text-right text-gray-700">
                                    Rp <?php echo e(number_format($proj['book_value'], 0, ',', '.')); ?>

                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div class="mt-4">
        <a href="<?php echo e(route('assets.index')); ?>" class="text-sm text-gray-500 hover:text-blue-500">← Kembali ke Daftar Aset</a>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\assets\schedule.blade.php ENDPATH**/ ?>