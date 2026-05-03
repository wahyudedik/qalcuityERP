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
     <?php $__env->slot('title', null, []); ?> Edit Routing Rule — <?php echo e($route->use_case); ?> — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> Edit Routing Rule — <?php echo e($route->use_case); ?> <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
        <div
            class="mb-4 p-4 bg-green-50 border border-green-200 rounded-2xl text-sm text-green-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('warning')): ?>
        <div
            class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-2xl text-sm text-amber-700 flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <?php echo e(session('warning')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-700">
            <p class="font-semibold mb-2">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside space-y-1">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="mb-4">
        <a href="<?php echo e(route('super-admin.ai.routing.index')); ?>"
            class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Routing Rules
        </a>
    </div>

    <form method="POST" action="<?php echo e(route('super-admin.ai.routing.update', $route)); ?>" x-data="{
        selectedProvider: '<?php echo e(old('provider', $route->provider)); ?>',
        knownModels: {
            'gemini': ['gemini-2.5-flash', 'gemini-2.5-flash-lite', 'gemini-1.5-flash', 'gemini-1.5-pro'],
            'anthropic': ['claude-3-5-sonnet-20241022', 'claude-3-haiku-20240307', 'claude-3-opus-20240229']
        },
        modelInput: '<?php echo e(old('model', $route->model)); ?>',
        showModelWarning: false,
        checkModelValidity() {
            if (!this.modelInput) {
                this.showModelWarning = false;
                return;
            }
            const models = this.knownModels[this.selectedProvider] || [];
            this.showModelWarning = !models.includes(this.modelInput);
        }
    }"
        x-init="checkModelValidity()">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-6">
            
            <div>
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Konfigurasi Routing Rule
                </h2>
                <p class="text-xs text-gray-400 mt-1">
                    Edit routing rule untuk use case <span
                        class="font-semibold text-gray-600"><?php echo e($route->use_case); ?></span>
                </p>
            </div>

            
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Status Provider</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <?php $__currentLoopData = $providerStatus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-gray-200">
                            <span class="text-sm font-medium text-gray-900"><?php echo e($status['label']); ?></span>
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-2 h-2 rounded-full <?php echo e($status['status_color'] === 'green' ? 'bg-green-500' : ($status['status_color'] === 'amber' ? 'bg-amber-500' : 'bg-gray-400')); ?>"></span>
                                <span
                                    class="text-xs font-medium <?php echo e($status['status_color'] === 'green' ? 'text-green-600' : ($status['status_color'] === 'amber' ? 'text-amber-600' : 'text-gray-500')); ?>">
                                    <?php echo e($status['status_label']); ?>

                                </span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            
            <div class="grid md:grid-cols-2 gap-4">
                
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                        Provider <span class="text-red-400">*</span>
                    </label>
                    <select name="provider" x-model="selectedProvider" @change="checkModelValidity()" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php $__currentLoopData = $availableProviders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($provider); ?>" <?php if(old('provider', $route->provider) === $provider): echo 'selected'; endif; ?>>
                                <?php echo e(ucfirst($provider)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Provider AI yang akan digunakan untuk use case ini</p>
                </div>

                
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                        Model
                    </label>
                    <input type="text" name="model" x-model="modelInput" @input="checkModelValidity()"
                        value="<?php echo e(old('model', $route->model)); ?>" placeholder="Kosongkan untuk model default provider"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Model spesifik yang akan digunakan (opsional)</p>

                    
                    <div x-show="showModelWarning" x-transition
                        class="mt-2 p-2 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-700 flex items-start gap-2">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Model ini tidak dikenal untuk provider yang dipilih. Pastikan model valid sebelum
                            menyimpan.</span>
                    </div>
                </div>

                
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                        Min Plan
                    </label>
                    <select name="min_plan"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Plan</option>
                        <?php $__currentLoopData = $availablePlans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($plan); ?>" <?php if(old('min_plan', $route->min_plan) === $plan): echo 'selected'; endif; ?>>
                                <?php echo e(ucfirst($plan)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Plan minimum yang diperlukan untuk menggunakan use case ini
                    </p>
                </div>

                
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                        Fallback Chain
                    </label>
                    <input type="text" name="fallback_chain"
                        value="<?php echo e(old('fallback_chain', is_array($route->fallback_chain) ? implode(', ', $route->fallback_chain) : '')); ?>"
                        placeholder="gemini, anthropic"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Urutan provider fallback (comma-separated). Kosongkan untuk
                        default.</p>
                </div>
            </div>

            
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    Deskripsi
                </label>
                <textarea name="description" rows="3" placeholder="Deskripsi use case dan tujuan routing rule ini..."
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"><?php echo e(old('description', $route->description)); ?></textarea>
                <p class="text-xs text-gray-400 mt-1">Deskripsi opsional untuk dokumentasi internal</p>
            </div>

            
            <div class="border-t border-gray-100 pt-4">
                <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-4">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        <?php if(old('is_active', $route->is_active) == 1): echo 'checked'; endif; ?> class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                    <label for="is_active" class="text-sm text-gray-700">
                        <span class="font-semibold">Aktifkan routing rule</span>
                        <span class="block text-xs text-gray-400 mt-0.5">Jika dinonaktifkan, sistem akan menggunakan
                            routing rule global default</span>
                    </label>
                </div>
            </div>

            
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <a href="<?php echo e(route('super-admin.ai.routing.index')); ?>"
                    class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    
    <div class="mt-6 bg-blue-50 rounded-2xl border border-blue-200 p-5">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm text-blue-700">
                <p class="font-semibold mb-1">Catatan Penting:</p>
                <ul class="list-disc list-inside space-y-1 text-xs">
                    <li>Perubahan routing rule akan berlaku segera setelah disimpan</li>
                    <li>Cache routing rules akan di-invalidate otomatis</li>
                    <li>Model yang tidak dikenal akan menampilkan warning tetapi tetap dapat disimpan</li>
                    <li>Fallback chain digunakan ketika provider utama tidak tersedia</li>
                </ul>
            </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\super-admin\ai-routing\edit.blade.php ENDPATH**/ ?>