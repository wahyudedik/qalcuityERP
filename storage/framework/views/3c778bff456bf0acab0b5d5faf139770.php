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
     <?php $__env->slot('header', null, []); ?> <?php echo e($livestockHerd->code); ?> — <?php echo e($livestockHerd->name); ?> <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
        <div
            class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">
            <?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div
            class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl text-sm text-red-700 dark:text-red-400">
            <?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <div class="flex items-center justify-between mb-4">
        <a href="<?php echo e(route('farm.livestock')); ?>" class="text-sm text-blue-500 hover:text-blue-600">← Daftar Ternak</a>
        <?php if($livestockHerd->status === 'active'): ?>
            <button onclick="document.getElementById('movementModal').classList.remove('hidden')"
                class="px-3 py-2 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">Catat
                Perubahan</button>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <div class="flex items-center gap-3 mb-4">
                    <span
                        class="text-3xl"><?php echo e(explode(' ', \App\Models\LivestockHerd::ANIMAL_TYPES[$livestockHerd->animal_type] ?? '🐾')[0]); ?></span>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-white text-lg"><?php echo e($livestockHerd->name); ?></p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">
                            <?php echo e($livestockHerd->breed ?? $livestockHerd->animal_type); ?> ·
                            <?php echo e($livestockHerd->plot?->code ?? 'Tanpa kandang'); ?></p>
                    </div>
                    <span
                        class="ml-auto text-4xl font-black text-emerald-600"><?php echo e(number_format($livestockHerd->current_count)); ?></span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Awal</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                            <?php echo e(number_format($livestockHerd->initial_count)); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Umur</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                            <?php echo e($livestockHerd->ageDays() ?? '-'); ?> hari</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Mortalitas</p>
                        <p class="text-lg font-bold text-red-500"><?php echo e(abs($livestockHerd->mortalityCount())); ?>

                            (<?php echo e($livestockHerd->mortalityRate()); ?>%)</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Terjual</p>
                        <p class="text-lg font-bold text-blue-600">
                            <?php echo e($livestockHerd->soldCount() + $livestockHerd->harvestedCount()); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Revenue</p>
                        <p class="text-lg font-bold text-emerald-600">Rp
                            <?php echo e(number_format($livestockHerd->totalRevenue(), 0, ',', '.')); ?></p>
                    </div>
                </div>
                <?php if($livestockHerd->isHarvestOverdue()): ?>
                    <div
                        class="mt-3 px-3 py-2 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-xs text-red-600 dark:text-red-400">
                        ⚠️ Target panen sudah lewat (<?php echo e($livestockHerd->target_harvest_date->format('d M Y')); ?>)
                    </div>
                <?php elseif($livestockHerd->daysUntilHarvest()): ?>
                    <p class="mt-3 text-xs text-gray-400">🎯 Target panen:
                        <?php echo e($livestockHerd->target_harvest_date->format('d M Y')); ?>

                        (<?php echo e($livestockHerd->daysUntilHarvest()); ?> hari lagi)</p>
                <?php endif; ?>
            </div>

            
            <?php
                $fcr = $livestockHerd->fcr();
                $totalFeed = $livestockHerd->totalFeedKg();
                $totalFeedCost = $livestockHerd->totalFeedCost();
                $latestWeight = $livestockHerd->latestBodyWeight();
                $weightGain = $livestockHerd->weightGain();
                $avgDailyFeed = $livestockHerd->avgDailyFeed();
                $feedCostPerKg = $livestockHerd->feedCostPerKgGain();
            ?>
            <?php if($totalFeed > 0): ?>
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">📊 Feed Conversion Ratio (FCR)</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div
                            class="p-3 rounded-xl <?php echo e($fcr && $fcr <= 1.8 ? 'bg-green-50 dark:bg-green-500/10' : ($fcr && $fcr <= 2.2 ? 'bg-amber-50 dark:bg-amber-500/10' : 'bg-gray-50 dark:bg-white/5')); ?>">
                            <p class="text-xs text-gray-500 dark:text-slate-400">FCR</p>
                            <p
                                class="text-2xl font-black <?php echo e($fcr && $fcr <= 1.8 ? 'text-green-600' : ($fcr && $fcr <= 2.2 ? 'text-amber-600' : 'text-gray-900 dark:text-white')); ?>">
                                <?php echo e($fcr ?? '-'); ?></p>
                            <p class="text-[10px] text-gray-400">
                                <?php echo e($fcr ? ($fcr <= 1.6 ? 'Sangat baik' : ($fcr <= 1.8 ? 'Baik' : ($fcr <= 2.2 ? 'Cukup' : 'Perlu perbaikan'))) : 'Belum cukup data'); ?>

                            </p>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-white/5">
                            <p class="text-xs text-gray-500 dark:text-slate-400">Total Pakan</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">
                                <?php echo e(number_format($totalFeed, 0)); ?> kg</p>
                            <p class="text-[10px] text-gray-400">Biaya: Rp
                                <?php echo e(number_format($totalFeedCost, 0, ',', '.')); ?></p>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-white/5">
                            <p class="text-xs text-gray-500 dark:text-slate-400">Berat Rata-rata</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">
                                <?php echo e($latestWeight ? number_format($latestWeight, 2) . ' kg' : '-'); ?></p>
                            <?php if($weightGain): ?>
                                <p class="text-[10px] text-emerald-600">+<?php echo e(number_format($weightGain, 3)); ?> kg gain
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-white/5">
                            <p class="text-xs text-gray-500 dark:text-slate-400">Biaya Pakan/kg Gain</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">
                                <?php echo e($feedCostPerKg ? 'Rp ' . number_format($feedCostPerKg, 0, ',', '.') : '-'); ?></p>
                            <?php if($avgDailyFeed): ?>
                                <p class="text-[10px] text-gray-400"><?php echo e($avgDailyFeed); ?> kg/hari</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white">🌾 Catatan Pakan</h3>
                    <?php if($livestockHerd->status === 'active'): ?>
                        <button onclick="document.getElementById('feedModal').classList.remove('hidden')"
                            class="text-xs px-3 py-1.5 bg-amber-600 text-white rounded-lg hover:bg-amber-700">+ Catat
                            Pakan</button>
                    <?php endif; ?>
                </div>
                <?php if($livestockHerd->feedLogs->isEmpty()): ?>
                    <div class="p-6 text-center text-sm text-gray-400">Belum ada catatan pakan. Catat pemberian pakan
                        harian untuk menghitung FCR.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-2 text-left">Tanggal</th>
                                    <th class="px-4 py-2 text-left">Jenis</th>
                                    <th class="px-4 py-2 text-right">Jumlah</th>
                                    <th class="px-4 py-2 text-right">g/ekor</th>
                                    <th class="px-4 py-2 text-right">Berat</th>
                                    <th class="px-4 py-2 text-right">Biaya</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                <?php $__currentLoopData = $livestockHerd->feedLogs->take(14); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-4 py-2 text-xs text-gray-500"><?php echo e($fl->date->format('d M')); ?></td>
                                        <td class="px-4 py-2 text-gray-700 dark:text-slate-300"><?php echo e($fl->feed_type); ?>

                                        </td>
                                        <td class="px-4 py-2 text-right font-mono">
                                            <?php echo e(number_format($fl->quantity_kg, 1)); ?> kg</td>
                                        <td class="px-4 py-2 text-right font-mono text-xs text-gray-400">
                                            <?php echo e($fl->feedPerHead()); ?>g</td>
                                        <td
                                            class="px-4 py-2 text-right font-mono text-xs <?php echo e($fl->avg_body_weight_kg > 0 ? 'text-emerald-600' : 'text-gray-300'); ?>">
                                            <?php echo e($fl->avg_body_weight_kg > 0 ? number_format($fl->avg_body_weight_kg, 2) . ' kg' : '-'); ?>

                                        </td>
                                        <td class="px-4 py-2 text-right text-xs text-gray-500">
                                            <?php echo e($fl->cost > 0 ? 'Rp ' . number_format($fl->cost, 0, ',', '.') : '-'); ?>

                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Riwayat Populasi
                        (<?php echo e($livestockHerd->movements->count()); ?>)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-2 text-left">Tanggal</th>
                                <th class="px-4 py-2 text-left">Jenis</th>
                                <th class="px-4 py-2 text-right">Jumlah</th>
                                <th class="px-4 py-2 text-right">Populasi</th>
                                <th class="px-4 py-2 text-left">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <?php $__currentLoopData = $livestockHerd->movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-4 py-2 text-xs text-gray-500"><?php echo e($mv->date->format('d M Y')); ?></td>
                                    <td class="px-4 py-2 text-xs"><?php echo e($mv->typeLabel()); ?></td>
                                    <td
                                        class="px-4 py-2 text-right font-mono font-medium <?php echo e($mv->quantity > 0 ? 'text-emerald-600' : 'text-red-500'); ?>">
                                        <?php echo e($mv->quantity > 0 ? '+' : ''); ?><?php echo e($mv->quantity); ?></td>
                                    <td class="px-4 py-2 text-right font-mono text-gray-700 dark:text-slate-300">
                                        <?php echo e(number_format($mv->count_after)); ?></td>
                                    <td class="px-4 py-2 text-xs text-gray-400">
                                        <?php echo e($mv->reason ?? ($mv->destination ?? '')); ?>

                                        <?php if($mv->price_total > 0): ?>
                                            · Rp <?php echo e(number_format($mv->price_total, 0, ',', '.')); ?>

                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">💉 Jadwal Vaksinasi</h3>
                <div class="flex gap-2">
                    <form method="POST" action="<?php echo e(route('farm.livestock.vaccinations.generate', $livestockHerd)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                            class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Auto-Generate</button>
                    </form>
                </div>
            </div>
            <?php if($livestockHerd->vaccinations->isEmpty()): ?>
                <div class="p-6 text-center text-sm text-gray-400">Belum ada jadwal vaksinasi. Klik "Auto-Generate"
                    untuk jadwal otomatis.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-2 text-left">Vaksin</th>
                                <th class="px-4 py-2 text-center">Hari ke-</th>
                                <th class="px-4 py-2 text-center">Jadwal</th>
                                <th class="px-4 py-2 text-center">Status</th>
                                <th class="px-4 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <?php $__currentLoopData = $livestockHerd->vaccinations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vax): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $overdue = $vax->isOverdue(); ?>
                                <tr class="<?php echo e($overdue ? 'bg-red-50/50 dark:bg-red-500/5' : ''); ?>">
                                    <td class="px-4 py-2 text-gray-900 dark:text-white font-medium">
                                        <?php echo e($vax->vaccine_name); ?></td>
                                    <td class="px-4 py-2 text-center text-xs text-gray-500"><?php echo e($vax->dose_age_days); ?>

                                    </td>
                                    <td
                                        class="px-4 py-2 text-center text-xs <?php echo e($overdue ? 'text-red-500 font-medium' : 'text-gray-500'); ?>">
                                        <?php echo e($vax->scheduled_date->format('d M Y')); ?><?php echo e($overdue ? ' ⚠️' : ''); ?></td>
                                    <td class="px-4 py-2 text-center">
                                        <?php if($vax->status === 'completed'): ?>
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">✅
                                                Selesai</span>
                                        <?php elseif($overdue): ?>
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">Terlambat</span>
                                        <?php else: ?>
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400">Dijadwalkan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <?php if($vax->status === 'scheduled'): ?>
                                            <form method="POST"
                                                action="<?php echo e(route('farm.livestock.vaccinations.record', $vax)); ?>"
                                                class="inline">
                                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                                <input type="hidden" name="administered_date"
                                                    value="<?php echo e(date('Y-m-d')); ?>">
                                                <input type="hidden" name="vaccinated_count"
                                                    value="<?php echo e($livestockHerd->current_count); ?>">
                                                <button type="submit"
                                                    class="text-xs text-blue-500 hover:text-blue-600">Catat
                                                    Selesai</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white">🏥 Catatan Kesehatan</h3>
                <?php if($livestockHerd->status === 'active'): ?>
                    <button onclick="document.getElementById('healthModal').classList.remove('hidden')"
                        class="text-xs px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700">+ Catat</button>
                <?php endif; ?>
            </div>
            <?php if($livestockHerd->healthRecords->isEmpty()): ?>
                <div class="p-6 text-center text-sm text-gray-400">Belum ada catatan kesehatan.</div>
            <?php else: ?>
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__currentLoopData = $livestockHerd->healthRecords->take(20); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $sc = \App\Models\LivestockHealthRecord::SEVERITY_COLORS[$hr->severity] ?? 'gray'; ?>
                        <div class="px-5 py-3">
                            <div class="flex items-center gap-2 mb-1">
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 dark:bg-<?php echo e($sc); ?>-500/20 dark:text-<?php echo e($sc); ?>-400"><?php echo e(ucfirst($hr->severity)); ?></span>
                                <span
                                    class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($hr->condition); ?></span>
                                <span class="text-xs text-gray-400 ml-auto"><?php echo e($hr->date->format('d M Y')); ?></span>
                            </div>
                            <div class="flex flex-wrap gap-x-3 text-xs text-gray-500 dark:text-slate-400">
                                <span><?php echo e($hr->typeLabel()); ?></span>
                                <?php if($hr->affected_count > 0): ?>
                                    <span>Terdampak: <?php echo e($hr->affected_count); ?></span>
                                <?php endif; ?>
                                <?php if($hr->death_count > 0): ?>
                                    <span class="text-red-500">Mati: <?php echo e($hr->death_count); ?></span>
                                <?php endif; ?>
                                <?php if($hr->medication): ?>
                                    <span>Obat: <?php echo e($hr->medication); ?></span>
                                <?php endif; ?>
                                <?php if($hr->medication_cost > 0): ?>
                                    <span>Biaya: Rp <?php echo e(number_format($hr->medication_cost, 0, ',', '.')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 h-fit">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Info Ternak</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Kode</span><span
                    class="font-mono"><?php echo e($livestockHerd->code); ?></span></div>
            <div class="flex justify-between"><span
                    class="text-gray-500">Jenis</span><span><?php echo e($livestockHerd->animalLabel()); ?></span></div>
            <?php if($livestockHerd->breed): ?>
                <div class="flex justify-between"><span
                        class="text-gray-500">Ras</span><span><?php echo e($livestockHerd->breed); ?></span></div>
            <?php endif; ?>
            <div class="flex justify-between"><span
                    class="text-gray-500">Masuk</span><span><?php echo e($livestockHerd->entry_date?->format('d M Y')); ?></span>
            </div>
            <?php if($livestockHerd->entry_weight_kg > 0): ?>
                <div class="flex justify-between"><span class="text-gray-500">Berat
                        Masuk</span><span><?php echo e($livestockHerd->entry_weight_kg); ?> kg/ekor</span></div>
            <?php endif; ?>
            <?php if($livestockHerd->purchase_price > 0): ?>
                <div class="flex justify-between"><span class="text-gray-500">Harga Beli</span><span>Rp
                        <?php echo e(number_format($livestockHerd->purchase_price, 0, ',', '.')); ?></span></div>
            <?php endif; ?>
            <?php if($livestockHerd->plot): ?>
                <div class="flex justify-between"><span
                        class="text-gray-500">Kandang</span><span><?php echo e($livestockHerd->plot->code); ?></span></div>
            <?php endif; ?>
            <div class="flex justify-between"><span class="text-gray-500">Status</span><span
                    class="font-medium"><?php echo e(ucfirst($livestockHerd->status)); ?></span></div>
        </div>
    </div>
    </div>

    
    <div id="feedModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">🌾 Catat Pemberian Pakan</h3>
                <button onclick="document.getElementById('feedModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('farm.livestock.feed.store', $livestockHerd)); ?>"
                class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jenis Pakan
                            *</label>
                        <input type="text" name="feed_type" required placeholder="Starter, Grower, Finisher"
                            class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal
                            *</label>
                        <input type="date" name="date" required value="<?php echo e(date('Y-m-d')); ?>"
                            class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah (kg)
                            *</label>
                        <input type="number" name="quantity_kg" required step="0.001" min="0.001"
                            class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Biaya
                            (Rp)</label>
                        <input type="number" name="cost" step="1" min="0"
                            class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Berat Rata-rata
                        Saat Ini (kg/ekor) — untuk hitung FCR</label>
                    <input type="number" name="avg_body_weight_kg" step="0.001"
                        placeholder="Timbang sampling, misal 1.250" class="<?php echo e($cls); ?>">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('feedModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300">Batal</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg text-sm bg-amber-600 hover:bg-amber-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="healthModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">🏥 Catat Kesehatan</h3>
                <button onclick="document.getElementById('healthModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('farm.livestock.health.store', $livestockHerd)); ?>"
                class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jenis *</label>
                        <select name="type" required class="<?php echo e($cls); ?>">
                            <?php $__currentLoopData = \App\Models\LivestockHealthRecord::TYPE_LABELS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($v); ?>"><?php echo e($l); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Severity</label>
                        <select name="severity" class="<?php echo e($cls); ?>">
                            <option value="low">Rendah</option>
                            <option value="medium" selected>Sedang</option>
                            <option value="high">Tinggi</option>
                            <option value="critical">Kritis</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kondisi / Penyakit
                        *</label>
                    <input type="text" name="condition" required placeholder="CRD, Snot, Diare, dll"
                        class="<?php echo e($cls); ?>">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal *</label>
                    <input type="date" name="date" required value="<?php echo e(date('Y-m-d')); ?>"
                        class="<?php echo e($cls); ?>">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Terdampak (ekor)</label>
                        <input type="number" name="affected_count" min="0" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Kematian (ekor)</label>
                        <input type="number" name="death_count" min="0" value="0"
                            class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Gejala</label>
                    <input type="text" name="symptoms" placeholder="Ngorok, lesu, nafsu makan turun"
                        class="<?php echo e($cls); ?>">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Obat / Treatment</label>
                        <input type="text" name="medication" placeholder="Antibiotik, vitamin"
                            class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Biaya Obat (Rp)</label>
                        <input type="number" name="medication_cost" step="1" min="0"
                            class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('healthModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300">Batal</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg text-sm bg-red-600 hover:bg-red-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="movementModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">📝 Catat Perubahan Populasi</h3>
                <button onclick="document.getElementById('movementModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <p class="text-xs text-gray-400 mb-4">Populasi saat ini: <span
                    class="font-bold text-gray-900 dark:text-white"><?php echo e($livestockHerd->current_count); ?> ekor</span>
            </p>
            <form method="POST" action="<?php echo e(route('farm.livestock.movement', $livestockHerd)); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jenis *</label>
                        <select name="type" required class="<?php echo e($cls); ?>">
                            <?php $__currentLoopData = \App\Models\LivestockMovement::TYPE_LABELS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($v !== 'purchase'): ?>
                                    <option value="<?php echo e($v); ?>"><?php echo e($l); ?></option>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah
                            *</label>
                        <input type="number" name="quantity" required min="1" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal *</label>
                    <input type="date" name="date" required value="<?php echo e(date('Y-m-d')); ?>"
                        class="<?php echo e($cls); ?>">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Berat Total (kg)</label>
                        <input type="number" name="weight_kg" step="0.001" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Nilai (Rp)</label>
                        <input type="number" name="price_total" step="1" class="<?php echo e($cls); ?>">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Alasan / Tujuan</label>
                    <input type="text" name="reason" placeholder="Penyakit, pembeli, kandang tujuan..."
                        class="<?php echo e($cls); ?>">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('movementModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300">Batal</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium">Simpan</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\farm\livestock-show.blade.php ENDPATH**/ ?>