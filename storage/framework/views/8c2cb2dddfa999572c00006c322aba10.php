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
     <?php $__env->slot('header', null, []); ?> Input Hasil Laboratorium <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Laboratorium', 'url' => route('healthcare.laboratory.orders')],
        ['label' => 'Input Hasil'],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Laboratorium', 'url' => route('healthcare.laboratory.orders')],
        ['label' => 'Input Hasil'],
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $attributes = $__attributesOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__attributesOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $component = $__componentOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__componentOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>

    <?php $tid = auth()->user()->tenant_id; ?>

    <?php if(!isset($order)): ?>
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-6 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                <div>
                    <p class="font-bold text-amber-800 dark:text-amber-200">Pilih Pesanan Lab</p>
                    <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">Silakan pilih pesanan laboratorium dari
                        daftar untuk menginput hasil.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(isset($order)): ?>
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
            <div
                class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Order:
                            <?php echo e($order->order_number ?? '-'); ?></h3>
                        <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">
                            <?php echo e($order->patient ? $order->patient->full_name : '-'); ?> |
                            <?php echo e($order->patient ? $order->patient->medical_record_number : '-'); ?></p>
                    </div>
                    <a href="<?php echo e(route('healthcare.laboratory.orders')); ?>"
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5">
                        Kembali
                    </a>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Jenis Test</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                        <?php echo e(str_replace('_', ' ', ucfirst($order->test_type ?? '-'))); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Dokter</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                        <?php echo e($order->doctor ? $order->doctor->name : '-'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Tanggal Order</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                        <?php echo e($order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d M Y H:i') : '-'); ?>

                    </p>
                </div>
            </div>
        </div>

        
        <form action="<?php echo e(route('healthcare.laboratory.results.store', $order)); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hasil Test</h3>
                </div>
                <div class="p-6 space-y-4">
                    <?php if($order->test_type === 'blood_test'): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hemoglobin
                                    (g/dL)</label>
                                <input type="number" step="0.1" name="results[hb]" value="<?php echo e(old('results.hb')); ?>"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Normal: 12-16 g/dL</p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hematocrit
                                    (%)</label>
                                <input type="number" step="0.1" name="results[hct]"
                                    value="<?php echo e(old('results.hct')); ?>"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Normal: 36-46%</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">WBC
                                    (10³/μL)</label>
                                <input type="number" step="0.1" name="results[wbc]"
                                    value="<?php echo e(old('results.wbc')); ?>"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Normal: 4.5-11.0</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Platelet
                                    (10³/μL)</label>
                                <input type="number" step="0.1" name="results[platelet]"
                                    value="<?php echo e(old('results.platelet')); ?>"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Normal: 150-400</p>
                            </div>
                        </div>
                    <?php elseif($order->test_type === 'liver_function'): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SGOT/AST
                                    (U/L)</label>
                                <input type="number" step="0.1" name="results[sgot]"
                                    value="<?php echo e(old('results.sgot')); ?>"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Normal: 10-40 U/L</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SGPT/ALT
                                    (U/L)</label>
                                <input type="number" step="0.1" name="results[sgpt]"
                                    value="<?php echo e(old('results.sgpt')); ?>"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Normal: 7-56 U/L</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bilirubin
                                    Total (mg/dL)</label>
                                <input type="number" step="0.01" name="results[bilirubin]"
                                    value="<?php echo e(old('results.bilirubin')); ?>"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Normal: 0.1-1.2 mg/dL</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Albumin
                                    (g/dL)</label>
                                <input type="number" step="0.1" name="results[albumin]"
                                    value="<?php echo e(old('results.albumin')); ?>"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Normal: 3.5-5.5 g/dL</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hasil Test
                                (JSON)</label>
                            <textarea name="results[custom]" rows="6"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder='{"parameter": "value"}'><?php echo e(old('results.custom')); ?></textarea>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Catatan & Interpretasi</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Interpretasi
                            Hasil</label>
                        <textarea name="interpretation" rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Interpretasi hasil test..."><?php echo e(old('interpretation')); ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catatan
                            Lab</label>
                        <textarea name="notes" rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Catatan tambahan..."><?php echo e(old('notes')); ?></textarea>
                    </div>
                </div>
            </div>

            
            <div class="flex items-center justify-end gap-3">
                <a href="<?php echo e(route('healthcare.laboratory.orders')); ?>"
                    class="px-6 py-2.5 text-sm border border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                    Simpan Hasil
                </button>
            </div>
        </form>
    <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\laboratory\results.blade.php ENDPATH**/ ?>