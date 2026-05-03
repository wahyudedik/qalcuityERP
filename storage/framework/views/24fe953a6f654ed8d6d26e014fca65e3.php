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
     <?php $__env->slot('header', null, []); ?> Pengaturan AI Routing <?php $__env->endSlot(); ?>

    <div class="max-w-6xl mx-auto space-y-4" x-data="aiRoutingSettings()">

        
        <?php if(session('success')): ?>
            <div
                class="flex items-center gap-3 bg-green-500/10 border border-green-500/30 text-green-400 rounded-2xl px-4 py-3 text-sm mb-4">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        
        <?php if($errors->any()): ?>
            <div
                class="flex items-start gap-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-2xl px-4 py-3 text-sm mb-4">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div>
                    <p class="font-medium text-red-300">Terjadi kesalahan:</p>
                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        
        <div
            class="flex items-start gap-3 bg-violet-500/10 border border-violet-500/20 rounded-2xl px-4 py-3 text-sm text-violet-400 mb-4">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
            </svg>
            <div>
                <p class="font-medium text-violet-300">Routing AI per Use Case</p>
                <p class="text-violet-400/80 mt-0.5">Anda dapat meng-override routing rule global untuk use case
                    tertentu. Override akan menggunakan provider dan model yang Anda pilih, berbeda dari konfigurasi
                    platform. Plan Anda saat ini: <strong class="text-violet-300"><?php echo e(ucfirst($tenantPlan)); ?></strong>
                </p>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-violet-100">
                        <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-gray-900 text-sm">Routing Rules yang Berlaku</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Global rules + override tenant Anda</p>
                    </div>
                </div>
                <button type="button" @click="showOverrideForm = !showOverrideForm"
                    class="px-4 py-2 bg-violet-600 hover:bg-violet-500 text-white rounded-xl text-sm font-medium transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span x-text="showOverrideForm ? 'Tutup Form' : 'Buat Override'"></span>
                </button>
            </div>

            
            <div x-show="showOverrideForm" x-collapse style="display:none"
                class="px-6 py-5 bg-violet-50/50 border-b border-violet-100">
                <form method="POST" action="<?php echo e(route('settings.ai-routing.store')); ?>" class="space-y-4">
                    <?php echo csrf_field(); ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <div>
                            <label for="use_case" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Use Case
                            </label>
                            <select id="use_case" name="use_case" required
                                class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-500 transition">
                                <option value="">Pilih use case...</option>
                                <?php $__currentLoopData = $useCases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $useCase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($useCase->value); ?>">
                                        <?php echo e(ucwords(str_replace('_', ' ', $useCase->value))); ?>

                                        <?php if($useCase->isHeavyweight()): ?>
                                            (Heavyweight)
                                        <?php else: ?>
                                            (Lightweight)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <p class="text-xs text-gray-400 mt-1.5">Pilih use case yang ingin Anda override</p>
                        </div>

                        
                        <div>
                            <label for="provider" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Provider
                            </label>
                            <select id="provider" name="provider" required
                                class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-500 transition">
                                <option value="">Pilih provider...</option>
                                <?php $__currentLoopData = $availableProviders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($provider); ?>">
                                        <?php echo e($provider === 'gemini' ? 'Gemini (Google)' : 'Anthropic (Claude)'); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <p class="text-xs text-gray-400 mt-1.5">Provider yang tersedia untuk plan Anda</p>
                        </div>
                    </div>

                    
                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Model (Opsional)
                        </label>
                        <input type="text" id="model" name="model" placeholder="Contoh: gemini-2.5-flash"
                            class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-500 transition">
                        <p class="text-xs text-gray-400 mt-1.5">Kosongkan untuk menggunakan model default provider</p>
                    </div>

                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Deskripsi (Opsional)
                        </label>
                        <textarea id="description" name="description" rows="2" placeholder="Catatan tentang override ini..."
                            class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-500 transition"></textarea>
                    </div>

                    
                    <div class="flex items-center justify-end gap-3 pt-2 border-t border-violet-100">
                        <button type="button" @click="showOverrideForm = false"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-medium transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-violet-600 hover:bg-violet-500 text-white rounded-xl text-sm font-medium transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Simpan Override
                        </button>
                    </div>
                </form>
            </div>

            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Use Case
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Provider
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Model
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Biaya Bulan Ini
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $routingRules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-900">
                                            <?php echo e(ucwords(str_replace('_', ' ', $rule->use_case))); ?>

                                        </span>
                                        <?php if(isset($rule->is_override) && $rule->is_override): ?>
                                            <span
                                                class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs rounded-full font-medium">
                                                Override Aktif
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($rule->description): ?>
                                        <p class="text-xs text-gray-400 mt-0.5"><?php echo e($rule->description); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-700">
                                        <?php echo e($rule->provider === 'gemini' ? 'Gemini' : 'Anthropic'); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600 font-mono">
                                        <?php echo e($rule->model ?? '-'); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                        $cost = $costByUseCase->get($rule->use_case);
                                    ?>
                                    <?php if($cost): ?>
                                        <div class="text-sm">
                                            <span class="font-medium text-gray-900">
                                                Rp <?php echo e(number_format($cost->total_cost, 2, ',', '.')); ?>

                                            </span>
                                            <span class="text-xs text-gray-400 block">
                                                <?php echo e(number_format($cost->request_count)); ?> request
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($rule->is_active): ?>
                                        <span
                                            class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-medium">
                                            Aktif
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="px-2 py-1 bg-gray-100 text-gray-500 text-xs rounded-full font-medium">
                                            Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <?php if(isset($rule->is_override) && $rule->is_override): ?>
                                        <form method="POST"
                                            action="<?php echo e(route('settings.ai-routing.destroy', $rule->id)); ?>"
                                            class="inline"
                                            onsubmit="return confirm('Hapus override untuk <?php echo e($rule->use_case); ?>? Akan kembali menggunakan routing rule global.')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-500 text-sm font-medium transition">
                                                Hapus Override
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400">Global Rule</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-400">
                                    Tidak ada routing rules yang tersedia.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <?php if(!in_array($tenantPlan, ['professional', 'enterprise'])): ?>
            <div
                class="flex items-start gap-3 bg-blue-500/10 border border-blue-500/20 rounded-2xl px-4 py-3 text-sm text-blue-400">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <div>
                    <p class="font-medium text-blue-300">Upgrade untuk Akses Lebih Banyak Provider</p>
                    <p class="text-blue-400/80 mt-0.5">
                        Plan Anda saat ini (<?php echo e(ucfirst($tenantPlan)); ?>) hanya dapat menggunakan provider Gemini.
                        Upgrade ke plan <strong>Professional</strong> atau <strong>Enterprise</strong> untuk mengakses
                        Anthropic Claude dan fitur analitik berat lainnya.
                    </p>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function aiRoutingSettings() {
                return {
                    showOverrideForm: false,
                };
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\settings\ai-routing.blade.php ENDPATH**/ ?>