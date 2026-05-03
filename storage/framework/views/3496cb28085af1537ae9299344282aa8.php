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
     <?php $__env->slot('header', null, []); ?> Survei Kepuasan Pasien <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Kepuasan Pasien', 'url' => route('healthcare.patient-satisfaction.index')],
        ['label' => 'Buat Survei'],
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
        ['label' => 'Kepuasan Pasien', 'url' => route('healthcare.patient-satisfaction.index')],
        ['label' => 'Buat Survei'],
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

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="<?php echo e(route('healthcare.patient-satisfaction.store')); ?>" method="POST"
                class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label for="patient_visit_id"
                        class="block text-sm font-medium text-gray-700 mb-1">Kunjungan Pasien
                        *</label>
                    <select name="patient_visit_id" id="patient_visit_id" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['patient_visit_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <option value="">Pilih kunjungan pasien</option>
                        <?php $__currentLoopData = $visits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($visit->id); ?>"><?php echo e($visit->patient->name ?? 'Unknown'); ?> -
                                <?php echo e($visit->visit_date); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['patient_visit_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label for="overall_rating"
                        class="block text-sm font-medium text-gray-700 mb-1">Penilaian Keseluruhan
                        *</label>
                    <select name="overall_rating" id="overall_rating" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['overall_rating'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <option value="">Pilih penilaian</option>
                        <option value="5">5 - Sangat Baik</option>
                        <option value="4">4 - Baik</option>
                        <option value="3">3 - Cukup</option>
                        <option value="2">2 - Buruk</option>
                        <option value="1">1 - Sangat Buruk</option>
                    </select>
                    <?php $__errorArgs = ['overall_rating'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="doctor_rating"
                            class="block text-sm font-medium text-gray-700 mb-1">Penilaian
                            Dokter</label>
                        <select name="doctor_rating" id="doctor_rating"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih penilaian</option>
                            <option value="5">5 - Sangat Baik</option>
                            <option value="4">4 - Baik</option>
                            <option value="3">3 - Cukup</option>
                            <option value="2">2 - Buruk</option>
                            <option value="1">1 - Sangat Buruk</option>
                        </select>
                    </div>
                    <div>
                        <label for="nurse_rating"
                            class="block text-sm font-medium text-gray-700 mb-1">Penilaian
                            Perawat</label>
                        <select name="nurse_rating" id="nurse_rating"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih penilaian</option>
                            <option value="5">5 - Sangat Baik</option>
                            <option value="4">4 - Baik</option>
                            <option value="3">3 - Cukup</option>
                            <option value="2">2 - Buruk</option>
                            <option value="1">1 - Sangat Buruk</option>
                        </select>
                    </div>
                    <div>
                        <label for="facility_rating"
                            class="block text-sm font-medium text-gray-700 mb-1">Penilaian
                            Fasilitas</label>
                        <select name="facility_rating" id="facility_rating"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih penilaian</option>
                            <option value="5">5 - Sangat Baik</option>
                            <option value="4">4 - Baik</option>
                            <option value="3">3 - Cukup</option>
                            <option value="2">2 - Buruk</option>
                            <option value="1">1 - Sangat Buruk</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="cleanliness_rating"
                        class="block text-sm font-medium text-gray-700 mb-1">Penilaian
                        Kebersihan</label>
                    <select name="cleanliness_rating" id="cleanliness_rating"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih penilaian</option>
                        <option value="5">5 - Sangat Baik</option>
                        <option value="4">4 - Baik</option>
                        <option value="3">3 - Cukup</option>
                        <option value="2">2 - Buruk</option>
                        <option value="1">1 - Sangat Buruk</option>
                    </select>
                </div>
                <div>
                    <label for="comments"
                        class="block text-sm font-medium text-gray-700 mb-1">Komentar</label>
                    <textarea name="comments" id="comments" rows="4"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="would_recommend" id="would_recommend" value="1"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="would_recommend" class="ml-2 block text-sm text-gray-700">Akan
                        merekomendasikan fasilitas ini</label>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="<?php echo e(route('healthcare.patient-satisfaction.index')); ?>"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Simpan Survei
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\patient-satisfaction\create.blade.php ENDPATH**/ ?>