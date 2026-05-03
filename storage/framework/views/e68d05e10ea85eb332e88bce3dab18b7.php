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
     <?php $__env->slot('header', null, []); ?> ⚓ Trip Detail - <?php echo e($trip->trip_number); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('fisheries.operations.index')); ?>"
                class="px-3 py-1.5 text-sm bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                ← Kembali
            </a>
    </div>

    <?php if(session('success')): ?>
        <div
            class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            <?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-2xl font-bold text-gray-900"><?php echo e($trip->trip_number); ?></h2>
                    <?php
                        $statusColors = [
                            'planned' => 'gray',
                            'departed' => 'blue',
                            'fishing' => 'emerald',
                            'returning' => 'yellow',
                            'completed' => 'green',
                            'cancelled' => 'red',
                        ];
                        $statusLabels = [
                            'planned' => 'Direncanakan',
                            'departed' => 'Berangkat',
                            'fishing' => 'Menangkap',
                            'returning' => 'Pulang',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                        ];
                        $color = $statusColors[$trip->status] ?? 'gray';
                        $label = $statusLabels[$trip->status] ?? $trip->status;
                    ?>
                    <span
                        class="px-3 py-1 text-sm rounded-full bg-<?php echo e($color); ?>-100 text-<?php echo e($color); ?>-700 $color }}-500/20 $color }}-400">
                        <?php echo e($label); ?>

                    </span>
                </div>
                <p class="text-sm text-gray-500">
                    🚢 <?php echo e($trip->vessel->name ?? 'N/A'); ?> | 👨‍✈️ <?php echo e($trip->captain->name ?? 'N/A'); ?>

                </p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500">Estimasi Nilai</p>
                <p class="text-2xl font-bold text-orange-600">Rp
                    <?php echo e(number_format($trip->estimated_value, 0, ',', '.')); ?></p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
            <div>
                <p class="text-xs text-gray-500">Waktu Berangkat</p>
                <p class="text-sm font-medium text-gray-900">
                    <?php echo e($trip->departure_time ? $trip->departure_time->format('d M Y, H:i') : '-'); ?>

                </p>
            </div>
            <?php if($trip->actual_return): ?>
                <div>
                    <p class="text-xs text-gray-500">Waktu Kembali</p>
                    <p class="text-sm font-medium text-gray-900">
                        <?php echo e($trip->actual_return->format('d M Y, H:i')); ?>

                    </p>
                </div>
            <?php elseif($trip->expected_return): ?>
                <div>
                    <p class="text-xs text-gray-500">Kembali (Rencana)</p>
                    <p class="text-sm font-medium text-gray-900">
                        <?php echo e($trip->expected_return->format('d M Y, H:i')); ?>

                    </p>
                </div>
            <?php endif; ?>
            <?php if($trip->fishing_zone): ?>
                <div>
                    <p class="text-xs text-gray-500">Zona Penangkapan</p>
                    <p class="text-sm font-medium text-gray-900">
                        <?php echo e($trip->fishing_zone->name ?? $trip->fishing_zone); ?></p>
                </div>
            <?php endif; ?>
            <div>
                <p class="text-xs text-gray-500">Durasi</p>
                <p class="text-sm font-medium text-gray-900">
                    <?php if($trip->actual_return && $trip->departure_time): ?>
                        <?php echo e($trip->departure_time->diffForHumans($trip->actual_return, true)); ?>

                    <?php elseif($trip->departure_time): ?>
                        <?php echo e($trip->departure_time->diffForHumans()); ?>

                    <?php else: ?>
                        -
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <?php if($trip->notes): ?>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-sm text-gray-600"><?php echo e($trip->notes); ?></p>
            </div>
        <?php endif; ?>
    </div>

    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Tangkapan</p>
            <p class="text-2xl font-bold text-emerald-600"><?php echo e(number_format($trip->total_catch_weight, 1)); ?> kg</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Jumlah Entry</p>
            <p class="text-2xl font-bold text-blue-600"><?php echo e($catches->total()); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Spesies Berbeda</p>
            <p class="text-2xl font-bold text-purple-600"><?php echo e($catches->pluck('species_id')->unique()->count()); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Rata-rata Berat/Entry</p>
            <p class="text-2xl font-bold text-cyan-600">
                <?php echo e($catches->total() > 0 ? number_format($trip->total_catch_weight / $catches->total(), 1) : 0); ?> kg
            </p>
        </div>
    </div>

    
    <div
        class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900">🐟 Detail Tangkapan</h3>
            <?php if(in_array($trip->status, ['departed', 'fishing'])): ?>
                <button onclick="document.getElementById('addCatchModal').classList.remove('hidden')"
                    class="px-3 py-1.5 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                    ➕ Catat Tangkapan
                </button>
            <?php endif; ?>
        </div>

        <?php if($catches->isEmpty()): ?>
            <div class="p-12 text-center">
                <p class="text-4xl mb-3">🐟</p>
                <p class="text-sm text-gray-500">Belum ada tangkapan tercatat untuk trip ini.</p>
                <?php if(in_array($trip->status, ['departed', 'fishing'])): ?>
                    <button onclick="document.getElementById('addCatchModal').classList.remove('hidden')"
                        class="mt-3 px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                        Catat Tangkapan Pertama
                    </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Waktu</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Spesies</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Berat</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Grade</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kesegaran</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__currentLoopData = $catches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                    <?php echo e($catch->created_at->format('d M Y, H:i')); ?>

                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">
                                        <?php echo e($catch->species->common_name ?? 'N/A'); ?></p>
                                    <p class="text-xs text-gray-500 italic">
                                        <?php echo e($catch->species->scientific_name ?? ''); ?></p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                    <?php echo e(number_format($catch->quantity, 0)); ?> ekor
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="font-bold text-emerald-600"><?php echo e(number_format($catch->total_weight, 1)); ?>

                                        kg</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($catch->grade): ?>
                                        <span
                                            class="px-2 py-1 text-xs rounded-full bg-<?php echo e($catch->grade->color ?? 'purple'); ?>-100 text-<?php echo e($catch->grade->color ?? 'purple'); ?>-700 $catch->grade->color ?? 'purple' }}-500/20 $catch->grade->color ?? 'purple' }}-400">
                                            <?php echo e($catch->grade->grade_code); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($catch->freshness_score): ?>
                                        <div class="flex items-center gap-1">
                                            <span
                                                class="font-medium <?php echo e($catch->freshness_score >= 8 ? 'text-green-600' : ($catch->freshness_score >= 6 ? 'text-yellow-600' : 'text-red-600')); ?>">
                                                <?php echo e(number_format($catch->freshness_score, 1)); ?>/10
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-bold text-orange-600">Rp
                                        <?php echo e(number_format($catch->estimated_value, 0, ',', '.')); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($catches->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    
    <?php if($trip->crew && count($trip->crew) > 0): ?>
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">👥 Awak Kapal
                (<?php echo e(count($trip->crew)); ?> orang)</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <?php $__currentLoopData = $trip->crew; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-3 py-2 bg-gray-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-900"><?php echo e($member->name); ?></p>
                        <p class="text-xs text-gray-500"><?php echo e(ucfirst($member->role ?? 'crew')); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="mt-6 flex gap-3">
        <?php if($trip->status === 'planned'): ?>
            <form action="<?php echo e(route('fisheries.operations.depart-trip', $trip->id)); ?>" method="POST" class="flex-1">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition flex items-center justify-center gap-2">
                    <span>🚀</span> Berangkat
                </button>
            </form>
        <?php endif; ?>

        <?php if(in_array($trip->status, ['fishing', 'returning'])): ?>
            <form action="<?php echo e(route('fisheries.operations.complete-trip', $trip->id)); ?>" method="POST" class="flex-1">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl transition flex items-center justify-center gap-2">
                    <span>✅</span> Selesai Trip
                </button>
            </form>
        <?php endif; ?>

        <a href="<?php echo e(route('fisheries.operations.index')); ?>"
            class="px-4 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition flex items-center justify-center gap-2">
            <span>←</span> Kembali
        </a>
    </div>

    
    <div id="addCatchModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">🐟 Catat Tangkapan</h3>
                <button onclick="document.getElementById('addCatchModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('fisheries.operations.record-catch', $trip->id)); ?>"
                class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; ?>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Spesies *</label>
                    <select name="species_id" required class="<?php echo e($cls); ?>">
                        <option value="">Pilih Spesies</option>
                        <?php $__currentLoopData = \App\Models\FishSpecies::where('tenant_id', auth()->user()->tenant_id)->orderBy('common_name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($sp->id); ?>"><?php echo e($sp->common_name); ?> (<?php echo e($sp->scientific_name); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah (ekor)
                            *</label>
                        <input type="number" name="quantity" required step="1" min="0"
                            placeholder="100" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Berat Total
                            (kg) *</label>
                        <input type="number" name="total_weight" required step="0.01" min="0"
                            placeholder="250.5" class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Grade
                            Kualitas</label>
                        <select name="grade_id" class="<?php echo e($cls); ?>">
                            <option value="">Pilih Grade</option>
                            <?php $__currentLoopData = \App\Models\QualityGrade::where('tenant_id', auth()->user()->tenant_id)->orderBy('grade_code')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($grade->id); ?>"><?php echo e($grade->grade_code); ?> - <?php echo e($grade->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Skor Kesegaran
                            (0-10)</label>
                        <input type="number" name="freshness_score" step="0.1" min="0" max="10"
                            placeholder="8.5" class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Lokasi GPS
                        (opsional)</label>
                    <input type="text" name="gps_location" placeholder="-6.2088, 106.8456"
                        class="<?php echo e($cls); ?>">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Kondisi ikan, metode penangkapan, dll."
                        class="<?php echo e($cls); ?>"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                        💾 Simpan Tangkapan
                    </button>
                    <button type="button" onclick="document.getElementById('addCatchModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded-lg transition">
                        Batal
                    </button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\fisheries\operation-detail.blade.php ENDPATH**/ ?>