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
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                🧮 Mix Design Beton - Kalkulator Mutu Beton SNI
            </h2>
            <div class="flex gap-2">
                <?php if($calculation): ?>
                    <form method="POST" action="<?php echo e(route('manufacturing.mix-design.export-pdf')); ?>" target="_blank"
                        class="inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="mix_design_id" value="<?php echo e($selectedMix->id); ?>">
                        <input type="hidden" name="volume" value="<?php echo e($calculation['adjusted']['volume_m3']); ?>">
                        <input type="hidden" name="waste_percent"
                            value="<?php echo e($calculation['adjusted']['waste_percent']); ?>">
                        <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                            📄 Export PDF
                        </button>
                    </form>
                <?php endif; ?>
                <button onclick="document.getElementById('addMixModal').showModal()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    + Tambah Custom Mix
                </button>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            
            <?php if(session('success')): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📊 Kalkulator Kebutuhan Material</h3>

                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Mutu Beton</label>
                            <select name="mix_design_id" class="w-full border rounded px-3 py-2" required>
                                <option value="">-- Pilih Mutu --</option>
                                <?php $__currentLoopData = $mixDesigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mix): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($mix->id); ?>"
                                        <?php echo e(request('mix_design_id') == $mix->id ? 'selected' : ''); ?>>
                                        <?php echo e($mix->grade); ?> - <?php echo e($mix->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Volume (m³)</label>
                            <input type="number" name="volume" step="0.1" min="0.1"
                                value="<?php echo e(request('volume', 1)); ?>" class="w-full border rounded px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Waste Factor (%)</label>
                            <input type="number" name="waste_percent" step="0.5" min="0" max="50"
                                value="<?php echo e(request('waste_percent', 5)); ?>" class="w-full border rounded px-3 py-2">
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                🔍 Hitung Kebutuhan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            
            <?php if($calculation): ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">📦 Kebutuhan Material</h3>

                            <div class="space-y-3">
                                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                    <span class="font-medium">🏭 Semen</span>
                                    <div class="text-right">
                                        <div class="font-bold">
                                            <?php echo e(number_format($calculation['adjusted']['cement_kg'], 1)); ?> kg</div>
                                        <div class="text-sm text-gray-500"><?php echo e($calculation['adjusted']['cement_sak']); ?>

                                            sak (@50kg)</div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                    <span class="font-medium">💧 Air</span>
                                    <div class="text-right">
                                        <div class="font-bold">
                                            <?php echo e(number_format($calculation['adjusted']['water_liter'], 1)); ?> liter</div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                    <span class="font-medium">🪨 Pasir (Agregat Halus)</span>
                                    <div class="text-right">
                                        <div class="font-bold">
                                            <?php echo e(number_format($calculation['adjusted']['fine_agg_kg'], 1)); ?> kg</div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo e($calculation['adjusted']['fine_agg_m3']); ?> m³</div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                    <span class="font-medium">🪨 Split (Agregat Kasar)</span>
                                    <div class="text-right">
                                        <div class="font-bold">
                                            <?php echo e(number_format($calculation['adjusted']['coarse_agg_kg'], 1)); ?> kg</div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo e($calculation['adjusted']['coarse_agg_m3']); ?> m³</div>
                                    </div>
                                </div>
                                <?php if($calculation['adjusted']['admixture_liter'] > 0): ?>
                                    <div
                                        class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                        <span class="font-medium">⚗️ Admixture</span>
                                        <div class="text-right">
                                            <div class="font-bold">
                                                <?php echo e(number_format($calculation['adjusted']['admixture_liter'], 2)); ?>

                                                liter</div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900 rounded text-sm">
                                <strong>Volume:</strong> <?php echo e($calculation['adjusted']['volume_m3']); ?> m³ |
                                <strong>Waste:</strong> <?php echo e($calculation['adjusted']['waste_percent']); ?>% |
                                <strong>Grade:</strong> <?php echo e($calculation['adjusted']['grade']); ?>

                            </div>
                        </div>
                    </div>

                    
                    <?php if($costAnalysis): ?>
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4">💰 Analisis Biaya</h3>

                                <div class="text-center mb-4">
                                    <div class="text-3xl font-bold text-green-600">
                                        Rp <?php echo e(number_format($costAnalysis['total_cost'], 0, ',', '.')); ?>

                                    </div>
                                    <div class="text-sm text-gray-500">Total Biaya</div>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span>Biaya per m³</span>
                                        <span class="font-semibold">Rp
                                            <?php echo e(number_format($costAnalysis['cost_per_m3']['total'], 0, ',', '.')); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Biaya per sak semen</span>
                                        <span class="font-semibold">Rp
                                            <?php echo e(number_format($costAnalysis['cost_per_sack_cement'], 0, ',', '.')); ?></span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <h4 class="font-medium mb-2">Breakdown Biaya:</h4>
                                    <div class="space-y-2">
                                        <?php $__currentLoopData = $costAnalysis['cost_per_m3']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item => $cost): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($item !== 'total' && $cost > 0): ?>
                                                <div>
                                                    <div class="flex justify-between text-sm mb-1">
                                                        <span><?php echo e(ucfirst(str_replace('_', ' ', $item))); ?></span>
                                                        <span>Rp <?php echo e(number_format($cost, 0, ',', '.')); ?></span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="bg-blue-600 h-2 rounded-full"
                                                            style="width: <?php echo e($costAnalysis['breakdown_percent'][$item]); ?>%">
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                
                <?php if($availability): ?>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">
                                📋 Ketersediaan Material
                                <?php if($availability['all_available']): ?>
                                    <span class="ml-2 px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">✓
                                        Semua Tersedia</span>
                                <?php else: ?>
                                    <span class="ml-2 px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm">✗ Ada
                                        Kekurangan</span>
                                <?php endif; ?>
                            </h3>

                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Material</th>
                                            <th class="px-4 py-2 text-right">Dibutuhkan</th>
                                            <th class="px-4 py-2 text-right">Tersedia</th>
                                            <th class="px-4 py-2 text-right">Kekurangan</th>
                                            <th class="px-4 py-2 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $availability['availability']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="border-t">
                                                <td class="px-4 py-3 font-medium">
                                                    <?php echo e(ucfirst(str_replace('_', ' ', $material))); ?></td>
                                                <td class="px-4 py-3 text-right">
                                                    <?php echo e(number_format($data['required'], 1)); ?> <?php echo e($data['unit']); ?></td>
                                                <td class="px-4 py-3 text-right">
                                                    <?php echo e(number_format($data['available'], 1)); ?> <?php echo e($data['unit']); ?>

                                                </td>
                                                <td
                                                    class="px-4 py-3 text-right <?php echo e($data['shortage'] > 0 ? 'text-red-600 font-bold' : ''); ?>">
                                                    <?php echo e($data['shortage'] > 0 ? number_format($data['shortage'], 1) : '-'); ?>

                                                    <?php echo e($data['unit']); ?>

                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <?php if($data['sufficient']): ?>
                                                        <span
                                                            class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">✓
                                                            Cukup</span>
                                                    <?php else: ?>
                                                        <span
                                                            class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">✗
                                                            Kurang</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">🎯 Rekomendasi Mix Design</h3>

                    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Mutu Required (K)</label>
                            <input type="number" name="required_strength" step="1" min="100"
                                placeholder="e.g. 300" class="w-full border rounded px-3 py-2"
                                value="<?php echo e(request('required_strength')); ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Volume (m³)</label>
                            <input type="number" name="rec_volume" step="0.1" min="0.1"
                                placeholder="e.g. 10" class="w-full border rounded px-3 py-2"
                                value="<?php echo e(request('rec_volume')); ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Max Budget (Rp/m³)</label>
                            <input type="number" name="max_budget" step="1000" min="0"
                                placeholder="Optional" class="w-full border rounded px-3 py-2"
                                value="<?php echo e(request('max_budget')); ?>">
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                                🎯 Cari Rekomendasi
                            </button>
                        </div>
                        <div class="flex items-end">
                            <button type="button" onclick="document.getElementById('compareModal').showModal()"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                                ⚖️ Bandingkan
                            </button>
                        </div>
                    </form>

                    <?php if($recommendation && $recommendation['status'] === 'success'): ?>
                        <div class="mt-4 p-4 bg-green-50 dark:bg-green-900 rounded border border-green-300">
                            <h4 class="font-bold text-green-800 mb-2">✓ Rekomendasi Terbaik</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <div class="text-gray-600">Grade</div>
                                    <div class="font-bold"><?php echo e($recommendation['recommended_mix']->grade); ?></div>
                                </div>
                                <div>
                                    <div class="text-gray-600">Kuat Tekan</div>
                                    <div class="font-bold"><?php echo e($recommendation['recommended_mix']->target_strength); ?> K
                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-600">Biaya/m³</div>
                                    <div class="font-bold">Rp
                                        <?php echo e(number_format($recommendation['cost_analysis']['cost_per_m3']['total'], 0, ',', '.')); ?>

                                    </div>
                                </div>
                                <div>
                                    <div class="text-gray-600">Total Biaya</div>
                                    <div class="font-bold">Rp
                                        <?php echo e(number_format($recommendation['cost_analysis']['total_cost'], 0, ',', '.')); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📚 Daftar Mix Design</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php $__currentLoopData = $mixDesigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mix): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div
                                class="border rounded-lg p-4 hover:shadow-md transition <?php echo e($mix->is_standard ? 'bg-blue-50 dark:bg-blue-900' : 'bg-white dark:bg-gray-700'); ?>">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-lg"><?php echo e($mix->grade); ?></h4>
                                    <?php if($mix->is_standard): ?>
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Standar
                                            SNI</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-3"><?php echo e($mix->name); ?></p>

                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span>Kuat Tekan:</span>
                                        <span class="font-semibold"><?php echo e($mix->target_strength); ?>

                                            <?php echo e($mix->strength_unit); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>W/C Ratio:</span>
                                        <span class="font-semibold"><?php echo e($mix->water_cement_ratio); ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Semen:</span>
                                        <span class="font-semibold"><?php echo e($mix->cement_kg); ?> kg/m³</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Slump:</span>
                                        <span class="font-semibold"><?php echo e($mix->slump_min); ?> - <?php echo e($mix->slump_max); ?>

                                            cm</span>
                                    </div>
                                </div>

                                <?php if(!$mix->is_standard): ?>
                                    <div class="mt-3 flex gap-2">
                                        <button onclick="editMixDesign(<?php echo e($mix->id); ?>)"
                                            class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">
                                            Edit
                                        </button>
                                        <button onclick="deleteMixDesign(<?php echo e($mix->id); ?>)"
                                            class="flex-1 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                            Hapus
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <dialog id="addMixModal" class="modal">
        <div class="modal-box max-w-2xl">
            <h3 class="font-bold text-lg mb-4">Tambah Custom Mix Design</h3>
            <form method="POST" action="<?php echo e(route('manufacturing.mix-design.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Grade *</label>
                        <input type="text" name="grade" required class="w-full border rounded px-3 py-2"
                            placeholder="e.g. K-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama *</label>
                        <input type="text" name="name" required class="w-full border rounded px-3 py-2"
                            placeholder="e.g. Beton K-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kuat Tekan (K) *</label>
                        <input type="number" name="target_strength" step="1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Satuan Kuat *</label>
                        <select name="strength_unit" required class="w-full border rounded px-3 py-2">
                            <option value="K">K</option>
                            <option value="MPa">MPa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Semen (kg/m³) *</label>
                        <input type="number" name="cement_kg" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Air (liter/m³) *</label>
                        <input type="number" name="water_liter" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">W/C Ratio *</label>
                        <input type="number" name="water_cement_ratio" step="0.01" min="0"
                            max="1" required class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tipe Semen *</label>
                        <select name="cement_type" required class="w-full border rounded px-3 py-2">
                            <option value="PCC">PCC</option>
                            <option value="OPC">OPC</option>
                            <option value="Type I">Type I</option>
                            <option value="Type II">Type II</option>
                            <option value="Type III">Type III</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Pasir (kg/m³) *</label>
                        <input type="number" name="fine_agg_kg" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Split (kg/m³) *</label>
                        <input type="number" name="coarse_agg_kg" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Admixture (liter/m³)</label>
                        <input type="number" name="admixture_liter" step="0.001" value="0"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Ukuran Agregat *</label>
                        <select name="agg_max_size" required class="w-full border rounded px-3 py-2">
                            <option value="10mm">10mm</option>
                            <option value="20mm">20mm</option>
                            <option value="40mm">40mm</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Slump Min (cm)</label>
                        <input type="number" name="slump_min" step="0.1" value="8"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Slump Max (cm)</label>
                        <input type="number" name="slump_max" step="0.1" value="12"
                            class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                </div>
                <div class="modal-action">
                    <button type="button" onclick="document.getElementById('addMixModal').close()"
                        class="btn">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        function editMixDesign(id) {
            // Fetch mix design data
            fetch(`/api/mix-design/${id}`)
                .then(response => response.json())
                .then(data => {
                    // Populate edit form
                    document.getElementById('edit_mix_id').value = data.id;
                    document.getElementById('editMixForm').action = `/manufacturing/mix-design/${id}`;
                    document.getElementById('edit_grade').value = data.grade;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_target_strength').value = data.target_strength;
                    document.getElementById('edit_strength_unit').value = data.strength_unit;
                    document.getElementById('edit_cement_kg').value = data.cement_kg;
                    document.getElementById('edit_water_liter').value = data.water_liter;
                    document.getElementById('edit_water_cement_ratio').value = data.water_cement_ratio;
                    document.getElementById('edit_cement_type').value = data.cement_type;
                    document.getElementById('edit_fine_agg_kg').value = data.fine_agg_kg;
                    document.getElementById('edit_coarse_agg_kg').value = data.coarse_agg_kg;
                    document.getElementById('edit_admixture_liter').value = data.admixture_liter || 0;
                    document.getElementById('edit_agg_max_size').value = data.agg_max_size;
                    document.getElementById('edit_slump_min').value = data.slump_min || 8;
                    document.getElementById('edit_slump_max').value = data.slump_max || 12;
                    document.getElementById('edit_notes').value = data.notes || '';

                    // Set version history link
                    document.getElementById('viewVersionsLink').href = `/manufacturing/mix-design/${data.id}/versions`;

                    // Show modal
                    document.getElementById('editMixModal').showModal();
                })
                .catch(error => {
                    alert('Error loading mix design data');
                    console.error(error);
                });
        }

        function deleteMixDesign(id) {
            if (confirm('Yakin ingin menghapus mix design ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/manufacturing/mix-design/${id}`;
                form.innerHTML = `<?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>`;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    
    <dialog id="editMixModal" class="modal">
        <div class="modal-box max-w-2xl">
            <h3 class="font-bold text-lg mb-4">Edit Custom Mix Design</h3>
            <form method="POST" id="editMixForm">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" id="edit_mix_id" name="mix_design_id">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Grade *</label>
                        <input type="text" id="edit_grade" name="grade" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama *</label>
                        <input type="text" id="edit_name" name="name" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Kuat Tekan (K) *</label>
                        <input type="number" id="edit_target_strength" name="target_strength" step="1"
                            required class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Satuan Kuat *</label>
                        <select id="edit_strength_unit" name="strength_unit" required
                            class="w-full border rounded px-3 py-2">
                            <option value="K">K</option>
                            <option value="MPa">MPa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Semen (kg/m³) *</label>
                        <input type="number" id="edit_cement_kg" name="cement_kg" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Air (liter/m³) *</label>
                        <input type="number" id="edit_water_liter" name="water_liter" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">W/C Ratio *</label>
                        <input type="number" id="edit_water_cement_ratio" name="water_cement_ratio" step="0.01"
                            min="0" max="1" required class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tipe Semen *</label>
                        <select id="edit_cement_type" name="cement_type" required
                            class="w-full border rounded px-3 py-2">
                            <option value="PCC">PCC</option>
                            <option value="OPC">OPC</option>
                            <option value="Type I">Type I</option>
                            <option value="Type II">Type II</option>
                            <option value="Type III">Type III</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Pasir (kg/m³) *</label>
                        <input type="number" id="edit_fine_agg_kg" name="fine_agg_kg" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Split (kg/m³) *</label>
                        <input type="number" id="edit_coarse_agg_kg" name="coarse_agg_kg" step="0.1" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Admixture (liter/m³)</label>
                        <input type="number" id="edit_admixture_liter" name="admixture_liter" step="0.001"
                            value="0" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Ukuran Agregat *</label>
                        <select id="edit_agg_max_size" name="agg_max_size" required
                            class="w-full border rounded px-3 py-2">
                            <option value="10mm">10mm</option>
                            <option value="20mm">20mm</option>
                            <option value="40mm">40mm</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Slump Min (cm)</label>
                        <input type="number" id="edit_slump_min" name="slump_min" step="0.1" value="8"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Slump Max (cm)</label>
                        <input type="number" id="edit_slump_max" name="slump_max" step="0.1" value="12"
                            class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1">Catatan</label>
                    <textarea id="edit_notes" name="notes" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                </div>
                <div
                    class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <label class="block text-sm font-medium mb-1 text-yellow-800 dark:text-yellow-400">
                        ⚠️ Change Reason (Required for Version Tracking)
                    </label>
                    <input type="text" id="edit_change_reason" name="change_reason" required
                        placeholder="e.g., Adjusted cement ratio for better strength"
                        class="w-full border border-yellow-300 dark:border-yellow-700 rounded px-3 py-2">
                    <p class="text-xs text-yellow-600 dark:text-yellow-500 mt-1">
                        A new version will be created to track this change
                    </p>
                </div>
                <div class="modal-action">
                    <button type="button" onclick="document.getElementById('editMixModal').close()"
                        class="btn">Batal</button>
                    <a href="#" id="viewVersionsLink" target="_blank" class="btn btn-outline btn-info">📋 View
                        Versions</a>
                    <button type="submit" class="btn btn-primary">Update & Create Version</button>
                </div>
            </form>
        </div>
    </dialog>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/manufacturing/mix-design.blade.php ENDPATH**/ ?>