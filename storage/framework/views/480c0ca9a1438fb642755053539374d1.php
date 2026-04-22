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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Species & Grading Catalog</h1>
        </div>
     <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
        <div
            class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">
            <?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 mb-6"
        x-data="{ tab: '<?php echo e(request('tab', 'species')); ?>' }">
        <div class="flex border-b border-gray-200 dark:border-white/10">
            <button @click="tab = 'species'; window.location.href = '?tab=species'"
                :class="tab === 'species' ? 'border-b-2 border-purple-600 text-purple-600' : 'text-gray-500 dark:text-slate-400'"
                class="flex-1 px-4 py-3 text-sm font-medium transition">
                🐟 Spesies Ikan
            </button>
            <button @click="tab = 'grades'; window.location.href = '?tab=grades'"
                :class="tab === 'grades' ? 'border-b-2 border-purple-600 text-purple-600' :
                    'text-gray-500 dark:text-slate-400'"
                class="flex-1 px-4 py-3 text-sm font-medium transition">
                ⭐ Grade Kualitas
            </button>
        </div>

        
        <div x-show="tab === 'species'" class="p-4">
            <div class="flex items-center justify-between mb-4">
                <form class="flex items-center gap-2">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari spesies..."
                        class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white w-48">
                    <select name="category" onchange="this.form.submit()"
                        class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="">Semua Kategori</option>
                        <option value="marine" <?php if(request('category') === 'marine'): echo 'selected'; endif; ?>">Laut</option>
                        <option value="freshwater" <?php if(request('category') === 'freshwater'): echo 'selected'; endif; ?>">Air Tawar</option>
                        <option value="brackish" <?php if(request('category') === 'brackish'): echo 'selected'; endif; ?>">Payau</option>
                    </select>
                </form>
                <button onclick="document.getElementById('addSpeciesModal').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition flex items-center gap-2">
                    <span>➕</span> Tambah Spesies
                </button>
            </div>

            <?php if(empty($species) || count($species) === 0): ?>
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
                    <p class="text-4xl mb-3">🐟</p>
                    <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada data spesies. Tambahkan spesies
                        pertama Anda.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <?php $__currentLoopData = $species; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div
                            class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-5 hover:shadow-lg transition">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white"><?php echo e($sp->common_name); ?>

                                    </h4>
                                    <p class="text-xs italic text-gray-500 dark:text-slate-400">
                                        <?php echo e($sp->scientific_name); ?></p>
                                </div>
                                <span
                                    class="text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400">
                                    <?php echo e(ucfirst($sp->category)); ?>

                                </span>
                            </div>

                            <div class="space-y-2 text-sm">
                                <?php if($sp->market_price_per_kg): ?>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-500 dark:text-slate-400">Harga Pasar:</span>
                                        <span class="font-semibold text-emerald-600">Rp
                                            <?php echo e(number_format($sp->market_price_per_kg, 0, ',', '.')); ?>/kg</span>
                                    </div>
                                <?php endif; ?>
                                <?php if($sp->average_weight_kg): ?>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-500 dark:text-slate-400">Berat Rata-rata:</span>
                                        <span
                                            class="text-gray-700 dark:text-slate-300"><?php echo e(number_format($sp->average_weight_kg, 2)); ?>

                                            kg</span>
                                    </div>
                                <?php endif; ?>
                                <?php if($sp->habitat): ?>
                                    <div>
                                        <span class="text-gray-500 dark:text-slate-400">Habitat:</span>
                                        <span class="text-gray-700 dark:text-slate-300 ml-1"><?php echo e($sp->habitat); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if($sp->description): ?>
                                <p
                                    class="text-xs text-gray-500 dark:text-slate-400 mt-3 pt-3 border-t border-gray-100 dark:border-white/5">
                                    <?php echo e(Str::limit($sp->description, 100)); ?>

                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="mt-4"><?php echo e($species->links()); ?></div>
            <?php endif; ?>
        </div>

        
        <div x-show="tab === 'grades'" class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-medium text-gray-700 dark:text-slate-300">Sistem Grading Kualitas</h3>
                <button onclick="document.getElementById('addGradeModal').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition flex items-center gap-2">
                    <span>➕</span> Tambah Grade
                </button>
            </div>

            <?php if(empty($grades) || count($grades) === 0): ?>
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
                    <p class="text-4xl mb-3">⭐</p>
                    <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada grade kualitas. Tambahkan grade
                        pertama Anda.</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php $__currentLoopData = $grades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div
                            class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-5 hover:shadow-lg transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-16 h-16 rounded-xl bg-gradient-to-br from-<?php echo e($grade->color ?? 'purple'); ?>-100 to-<?php echo e($grade->color ?? 'purple'); ?>-200 dark:from-<?php echo e($grade->color ?? 'purple'); ?>-500/20 dark:to-<?php echo e($grade->color ?? 'purple'); ?>-500/30 flex items-center justify-center">
                                        <span
                                            class="text-2xl font-bold text-<?php echo e($grade->color ?? 'purple'); ?>-600 dark:text-<?php echo e($grade->color ?? 'purple'); ?>-400"><?php echo e($grade->grade_code); ?></span>
                                    </div>
                                    <div>
                                        <h4 class="text-base font-bold text-gray-900 dark:text-white">
                                            <?php echo e($grade->name); ?></h4>
                                        <p class="text-sm text-gray-500 dark:text-slate-400"><?php echo e($grade->description); ?>

                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 dark:text-slate-400">Price Multiplier</p>
                                    <p class="text-2xl font-bold text-emerald-600">
                                        <?php echo e(number_format($grade->price_multiplier, 2)); ?>x</p>
                                </div>
                            </div>

                            <?php if($grade->min_weight_kg || $grade->max_weight_kg || $grade->quality_criteria): ?>
                                <div
                                    class="mt-3 pt-3 border-t border-gray-100 dark:border-white/5 grid grid-cols-3 gap-4 text-xs">
                                    <?php if($grade->min_weight_kg): ?>
                                        <div>
                                            <span class="text-gray-400">Berat Min:</span>
                                            <span
                                                class="text-gray-700 dark:text-slate-300 ml-1"><?php echo e(number_format($grade->min_weight_kg, 2)); ?>

                                                kg</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if($grade->max_weight_kg): ?>
                                        <div>
                                            <span class="text-gray-400">Berat Max:</span>
                                            <span
                                                class="text-gray-700 dark:text-slate-300 ml-1"><?php echo e(number_format($grade->max_weight_kg, 2)); ?>

                                                kg</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if($grade->quality_criteria): ?>
                                        <div class="col-span-3">
                                            <span class="text-gray-400">Kriteria:</span>
                                            <span
                                                class="text-gray-700 dark:text-slate-300 ml-1"><?php echo e($grade->quality_criteria); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div id="addSpeciesModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">🐟 Tambah Spesies Ikan</h3>
                <button onclick="document.getElementById('addSpeciesModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('fisheries.species.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Umum
                            *</label>
                        <input type="text" name="common_name" required placeholder="Udang Vaname"
                            class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Ilmiah
                            *</label>
                        <input type="text" name="scientific_name" required placeholder="Litopenaeus vannamei"
                            class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kategori</label>
                        <select name="category" class="<?php echo e($cls); ?>">
                            <option value="marine">Laut</option>
                            <option value="freshwater">Air Tawar</option>
                            <option value="brackish">Payau</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Harga Pasar
                            (Rp/kg)</label>
                        <input type="number" name="market_price_per_kg" step="100" min="0"
                            placeholder="50000" class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Berat Rata-rata
                            (kg)</label>
                        <input type="number" name="average_weight_kg" step="0.01" min="0"
                            placeholder="0.5" class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Habitat</label>
                        <input type="text" name="habitat" placeholder="Perairan tropis"
                            class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi</label>
                    <textarea name="description" rows="3" placeholder="Karakteristik, habitat, musim tangkap, dll."
                        class="<?php echo e($cls); ?>"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                        💾 Simpan Spesies
                    </button>
                    <button type="button"
                        onclick="document.getElementById('addSpeciesModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="addGradeModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">⭐ Tambah Grade Kualitas</h3>
                <button onclick="document.getElementById('addGradeModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('fisheries.species.store-grade')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kode Grade
                            *</label>
                        <input type="text" name="grade_code" required placeholder="A, B, C atau Super, Premium"
                            class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label>
                        <input type="text" name="name" required placeholder="Grade A - Premium"
                            class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Price Multiplier
                        *</label>
                    <input type="number" name="price_multiplier" required step="0.01" min="0.1"
                        value="1.0" placeholder="1.5" class="<?php echo e($cls); ?>">
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Pengali harga dari harga dasar (contoh:
                        1.5 = 150% harga dasar)</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Berat Min
                            (kg)</label>
                        <input type="number" name="min_weight_kg" step="0.01" min="0" placeholder="0.5"
                            class="<?php echo e($cls); ?>">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Berat Max
                            (kg)</label>
                        <input type="number" name="max_weight_kg" step="0.01" min="0" placeholder="2.0"
                            class="<?php echo e($cls); ?>">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Warna Badge</label>
                    <select name="color" class="<?php echo e($cls); ?>">
                        <option value="green">Hijau</option>
                        <option value="blue">Biru</option>
                        <option value="purple">Ungu</option>
                        <option value="yellow">Kuning</option>
                        <option value="orange">Oranye</option>
                        <option value="red">Merah</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kriteria
                        Kualitas</label>
                    <textarea name="quality_criteria" rows="3" placeholder="Ukuran seragam, warna cerah, tidak ada cacat, dll."
                        class="<?php echo e($cls); ?>"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" placeholder="Penjelasan detail tentang grade ini"
                        class="<?php echo e($cls); ?>"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                        💾 Simpan Grade
                    </button>
                    <button type="button" onclick="document.getElementById('addGradeModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition">
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\fisheries\species.blade.php ENDPATH**/ ?>