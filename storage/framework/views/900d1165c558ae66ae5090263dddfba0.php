<!DOCTYPE html>
<html lang="id" class="h-full dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Catat Aktivitas Lahan — <?php echo e(config('app.name')); ?></title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        .target-card {
            transition: all 0.15s ease;
        }

        .target-card.selected {
            ring: 2px;
        }

        .activity-pill {
            transition: all 0.15s ease;
        }

        .photo-slot {
            transition: all 0.2s ease;
        }

        input[type="file"]:focus+label {
            outline: 2px solid #3b82f6;
        }

        .preview-img {
            object-fit: cover;
        }
    </style>
</head>

<body class="bg-gray-950 min-h-screen text-white">

    <div x-data="{
        targetType: 'plot',
        selectedTarget: null,
        activityType: null,
        photos: [],
        photoFiles: [],
    
        selectTarget(id) {
            this.selectedTarget = (this.selectedTarget === id) ? null : id;
        },
    
        selectActivity(type) {
            this.activityType = (this.activityType === type) ? null : type;
        },
    
        previewPhoto(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (this.photos.length >= 3) {
                alert('Maksimal 3 foto yang dapat diunggah.');
                event.target.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                this.photos.push(e.target.result);
                this.photoFiles.push(file);
            };
            reader.readAsDataURL(file);
        },
    
        removePhoto(index) {
            this.photos.splice(index, 1);
            this.photoFiles.splice(index, 1);
        },
    
        plotActivities: ['Penyiraman', 'Pemupukan', 'Penyemprotan', 'Penanaman', 'Panen', 'Pemangkasan', 'Inspeksi'],
        livestockActivities: ['Pemberian Pakan', 'Pemeriksaan Kesehatan', 'Vaksinasi', 'Pemindahan', 'Penjualan', 'Pencatatan'],
    
        get currentActivities() {
            return this.targetType === 'plot' ? this.plotActivities : this.livestockActivities;
        }
    }" class="pb-28">

        
        <div class="sticky top-0 z-40 bg-gray-950/95 backdrop-blur-sm border-b border-white/10">
            <div class="flex items-center gap-3 px-4 py-4">
                <a href="<?php echo e(route('mobile.hub')); ?>"
                    class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 transition text-slate-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-base font-bold text-white leading-tight">Catat Aktivitas Lahan</h1>
                    <p class="text-xs text-slate-400">Rekam aktivitas kebun & ternak</p>
                </div>
            </div>
        </div>

        
        <form method="POST" action="<?php echo e(route('mobile.farm-activity.store')); ?>" enctype="multipart/form-data"
            id="farmActivityForm">
            <?php echo csrf_field(); ?>

            <div class="px-4 pt-5 space-y-5">

                
                <div class="bg-[#1e293b] rounded-2xl border border-white/10 p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">1. Pilih Target</p>

                    
                    <div class="flex bg-gray-900/60 rounded-xl p-1 mb-4">
                        <button type="button" @click="targetType = 'plot'; selectedTarget = null; activityType = null"
                            :class="targetType === 'plot'
                                ?
                                'bg-blue-600 text-white shadow-lg' :
                                'text-slate-400 hover:text-white'"
                            class="flex-1 h-11 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-2">
                            <span>🌾</span> Lahan
                        </button>
                        <button type="button"
                            @click="targetType = 'livestock'; selectedTarget = null; activityType = null"
                            :class="targetType === 'livestock'
                                ?
                                'bg-blue-600 text-white shadow-lg' :
                                'text-slate-400 hover:text-white'"
                            class="flex-1 h-11 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-2">
                            <span>🐄</span> Ternak
                        </button>
                    </div>

                    
                    <div x-show="targetType === 'plot'" x-transition>
                        <?php $plots = $plots ?? collect(); ?>
                        <?php if($plots->isEmpty()): ?>
                            <div class="text-center py-8">
                                <p class="text-3xl mb-2">🌾</p>
                                <p class="text-sm text-slate-400">Belum ada lahan terdaftar.</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                <?php $__currentLoopData = $plots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="target-card flex items-center gap-3 p-3 rounded-xl border cursor-pointer min-h-[56px]"
                                        :class="selectedTarget === '<?php echo e($plot->id); ?>'
                                            ?
                                            'border-blue-500/70 bg-blue-600/10 ring-2 ring-blue-500' :
                                            'border-white/10 bg-gray-900/40 hover:border-white/20'"
                                        @click="selectTarget('<?php echo e($plot->id); ?>')">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-emerald-600/20 flex items-center justify-center flex-shrink-0">
                                            <span class="text-lg">🌿</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-semibold text-sm text-white"><?php echo e($plot->code); ?> —
                                                <?php echo e($plot->name); ?></div>
                                            <div class="text-xs text-slate-400 flex items-center gap-2 mt-0.5">
                                                <span><?php echo e(number_format($plot->area_size, 1)); ?>

                                                    <?php echo e($plot->area_unit ?? 'ha'); ?></span>
                                                <?php if($plot->current_crop): ?>
                                                    <span class="text-slate-500">•</span>
                                                    <span><?php echo e($plot->current_crop); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div x-show="selectedTarget === '<?php echo e($plot->id); ?>'" class="flex-shrink-0">
                                            <div
                                                class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center">
                                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <input type="hidden" name="farm_plot_id" x-show="targetType === 'plot'"
                                :value="targetType === 'plot' ? selectedTarget : ''">
                        <?php endif; ?>
                    </div>

                    
                    <div x-show="targetType === 'livestock'" x-transition>
                        <?php $livestockGroups = $livestockGroups ?? collect(); ?>
                        <?php if($livestockGroups->isEmpty()): ?>
                            <div class="text-center py-8">
                                <p class="text-3xl mb-2">🐄</p>
                                <p class="text-sm text-slate-400">Belum ada kelompok ternak terdaftar.</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                <?php $__currentLoopData = $livestockGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $herd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $animalEmoji = explode(
                                            ' ',
                                            \App\Models\LivestockHerd::ANIMAL_TYPES[$herd->animal_type] ?? '🐾 Hewan',
                                        )[0];
                                    ?>
                                    <div class="target-card flex items-center gap-3 p-3 rounded-xl border cursor-pointer min-h-[56px]"
                                        :class="selectedTarget === 'livestock_<?php echo e($herd->id); ?>'
                                            ?
                                            'border-blue-500/70 bg-blue-600/10 ring-2 ring-blue-500' :
                                            'border-white/10 bg-gray-900/40 hover:border-white/20'"
                                        @click="selectTarget('livestock_<?php echo e($herd->id); ?>')">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-amber-600/20 flex items-center justify-center flex-shrink-0">
                                            <span class="text-lg"><?php echo e($animalEmoji); ?></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-semibold text-sm text-white"><?php echo e($herd->code); ?> —
                                                <?php echo e($herd->name); ?></div>
                                            <div class="text-xs text-slate-400 flex items-center gap-2 mt-0.5">
                                                <span><?php echo e(number_format($herd->current_count)); ?> ekor</span>
                                                <span class="text-slate-500">•</span>
                                                <span><?php echo e($herd->animal_type); ?></span>
                                            </div>
                                        </div>
                                        <div x-show="selectedTarget === 'livestock_<?php echo e($herd->id); ?>'"
                                            class="flex-shrink-0">
                                            <div
                                                class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center">
                                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <input type="hidden" name="livestock_herd_id" x-show="targetType === 'livestock'"
                                :value="targetType === 'livestock' ? (selectedTarget ? selectedTarget.replace('livestock_',
                                    '') : '') : ''">
                        <?php endif; ?>
                    </div>

                    <input type="hidden" name="target_type" :value="targetType">

                </div>

                
                <div class="bg-[#1e293b] rounded-2xl border border-white/10 p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">2. Jenis Aktivitas
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="act in currentActivities" :key="act">
                            <button type="button" @click="selectActivity(act)"
                                :class="activityType === act ?
                                    'bg-blue-600 text-white border-blue-500 shadow-lg shadow-blue-500/20' :
                                    'bg-gray-900/60 text-slate-300 border-white/10 hover:border-white/25 hover:text-white'"
                                class="activity-pill min-h-[48px] px-4 rounded-xl text-sm font-semibold border"
                                x-text="act"></button>
                        </template>
                    </div>
                    <input type="hidden" name="activity_type" :value="activityType">
                </div>

                
                <div class="bg-[#1e293b] rounded-2xl border border-white/10 p-4 space-y-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">3. Detail Aktivitas</p>

                    
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Jumlah / Volume</label>
                        <div class="flex gap-2">
                            <input type="number" name="quantity" step="0.01" min="0" placeholder="0"
                                class="h-14 flex-1 px-4 text-xl font-semibold rounded-xl border border-white/10 bg-gray-900/60 text-white placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                            <select name="quantity_unit"
                                class="h-14 px-3 rounded-xl border border-white/10 bg-gray-900/60 text-slate-300 text-sm focus:outline-none focus:border-blue-500 transition">
                                <option value="">Satuan</option>
                                <option value="kg">kg</option>
                                <option value="liter">liter</option>
                                <option value="ekor">ekor</option>
                                <option value="gram">gram</option>
                                <option value="ml">ml</option>
                                <option value="karung">karung</option>
                                <option value="unit">unit</option>
                            </select>
                        </div>
                    </div>

                    
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Catatan Tambahan</label>
                        <textarea name="notes" rows="3" placeholder="Catatan tambahan tentang aktivitas ini..."
                            class="w-full min-h-[100px] text-base p-4 rounded-xl border border-white/10 bg-gray-900/60 text-white placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition resize-none"></textarea>
                    </div>

                    
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Tanggal & Waktu</label>
                        <input type="datetime-local" name="activity_date" value="<?php echo e(now()->format('Y-m-d\TH:i')); ?>"
                            class="w-full h-14 px-4 rounded-xl border border-white/10 bg-gray-900/60 text-white text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    </div>
                </div>

                
                <div class="bg-[#1e293b] rounded-2xl border border-white/10 p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">4. Foto Dokumentasi
                    </p>

                    
                    <div x-show="photos.length > 0" class="flex gap-3 mb-3 flex-wrap">
                        <template x-for="(photo, index) in photos" :key="index">
                            <div
                                class="relative w-24 h-24 rounded-xl overflow-hidden border border-white/10 flex-shrink-0">
                                <img :src="photo" class="preview-img w-full h-full object-cover">
                                <button type="button" @click="removePhoto(index)"
                                    class="absolute top-1 right-1 w-6 h-6 bg-red-500/90 hover:bg-red-600 rounded-full flex items-center justify-center text-white shadow-lg transition">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <div class="absolute bottom-1 left-1 bg-black/60 text-white text-[9px] px-1.5 py-0.5 rounded-full"
                                    x-text="'Foto ' + (index + 1)"></div>
                            </div>
                        </template>
                    </div>

                    
                    <div x-show="photos.length < 3">
                        <label for="photo-capture"
                            class="photo-slot flex flex-col items-center justify-center w-full h-40 bg-gray-900/60 border-2 border-dashed border-white/20 rounded-2xl cursor-pointer hover:border-blue-500/50 hover:bg-blue-950/20 transition group">
                            <div class="flex flex-col items-center pointer-events-none">
                                <div
                                    class="w-14 h-14 rounded-2xl bg-blue-600/10 group-hover:bg-blue-600/20 flex items-center justify-center mb-2 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-7 h-7 text-blue-400 group-hover:text-blue-300 transition"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <span class="text-sm text-slate-400 group-hover:text-slate-300 font-medium transition">
                                    <span
                                        x-text="photos.length === 0 ? 'Tap untuk ambil foto' : 'Tambah foto lagi'"></span>
                                </span>
                                <span class="text-xs text-slate-500 mt-0.5">atau pilih dari galeri</span>
                                <span class="text-xs text-slate-600 mt-1"
                                    x-text="'(' + (3 - photos.length) + ' foto tersisa)'"></span>
                            </div>
                            <input id="photo-capture" type="file" name="photos[]" accept="image/*"
                                capture="environment" class="hidden" @change="previewPhoto($event)">
                        </label>
                    </div>

                    
                    <template x-if="photos.length >= 1 && photos.length < 3">
                        <div class="mt-3">
                            <label for="photo-capture-2"
                                class="photo-slot flex items-center justify-center gap-3 w-full h-12 bg-gray-900/40 border border-dashed border-white/15 rounded-xl cursor-pointer hover:border-blue-500/40 hover:bg-blue-950/10 transition group">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 text-slate-500 group-hover:text-blue-400 transition" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="text-xs text-slate-500 group-hover:text-slate-400 transition">Tambah foto
                                    lagi</span>
                                <input id="photo-capture-2" type="file" name="photos[]" accept="image/*"
                                    capture="environment" class="hidden" @change="previewPhoto($event)">
                            </label>
                        </div>
                    </template>

                    <p class="text-xs text-slate-600 mt-3 text-center">Maks. 3 foto • JPG, PNG, WEBP</p>
                </div>

                
                <?php if($errors->any()): ?>
                    <div class="bg-red-500/10 border border-red-500/30 rounded-2xl p-4">
                        <p class="text-sm font-semibold text-red-400 mb-2">Terdapat kesalahan:</p>
                        <ul class="space-y-1">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="text-xs text-red-300 flex items-start gap-2">
                                    <span class="mt-0.5 flex-shrink-0">•</span>
                                    <span><?php echo e($error); ?></span>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if(session('success')): ?>
                    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-4">
                        <p class="text-sm text-emerald-400">✅ <?php echo e(session('success')); ?></p>
                    </div>
                <?php endif; ?>

            </div>
        </form>

        
        <div class="fixed bottom-0 inset-x-0 z-50 bg-gray-950/95 backdrop-blur-sm border-t border-white/10">
            <div class="px-4 py-4">
                <button type="submit" form="farmActivityForm" :disabled="!selectedTarget || !activityType"
                    :class="(!selectedTarget || !activityType) ?
                    'bg-gray-700 text-slate-500 cursor-not-allowed' :
                    'bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white shadow-lg shadow-blue-500/25'"
                    class="h-14 w-full rounded-xl text-base font-bold transition-all duration-200 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span
                        x-text="(!selectedTarget || !activityType) ? 'Pilih target & aktivitas dahulu' : 'Simpan Aktivitas'"></span>
                </button>
                <p class="text-center text-[10px] text-slate-600 mt-2" x-show="!selectedTarget || !activityType"
                    x-text="!selectedTarget ? 'Pilih lahan atau ternak terlebih dahulu' : 'Pilih jenis aktivitas terlebih dahulu'">
                </p>
            </div>
        </div>

    </div>

</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\mobile\farm-activity.blade.php ENDPATH**/ ?>