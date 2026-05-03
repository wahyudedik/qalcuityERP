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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Telemedicine Feedback')); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.telemedicine.consultations')); ?>"
            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Detail Konsultasi</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Dokter: <span class="font-medium text-gray-900">Dr.
                                <?php echo e($consultation->doctor?->name ?? '-'); ?></span></p>
                        <p class="text-sm text-gray-600">Tanggal: <span
                                class="font-medium text-gray-900"><?php echo e($consultation->scheduled_time ? $consultation->scheduled_time->format('l, d F Y') : '-'); ?></span>
                        </p>
                        <p class="text-sm text-gray-600">Durasi: <span
                                class="font-medium text-gray-900"><?php echo e($consultation->scheduled_duration ?? 30); ?>

                                menit</span></p>
                    </div>
                </div>

                <form method="POST" action="<?php echo e(route('healthcare.telemedicine.feedback.store', $consultation)); ?>">
                    <?php echo csrf_field(); ?>

                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pengalaman
                            Keseluruhan *</label>
                        <div class="flex items-center gap-2" id="star-rating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <button type="button"
                                    class="star text-3xl text-gray-300 hover:text-yellow-400 transition-colors"
                                    data-value="<?php echo e($i); ?>">
                                    ★
                                </button>
                            <?php endfor; ?>
                            <span id="rating-text" class="ml-2 text-sm text-gray-600">Pilih
                                rating</span>
                        </div>
                        <input type="hidden" name="rating" id="rating-value" required />
                        <?php $__errorArgs = ['rating'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Profesionalisme
                                Dokter *</label>
                            <select name="doctor_rating" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih rating</option>
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo e($i); ?>"><?php echo e($i); ?> -
                                        <?php echo e(['Buruk', 'Cukup', 'Baik', 'Sangat Baik', 'Luar Biasa'][$i - 1]); ?></option>
                                <?php endfor; ?>
                            </select>
                            <?php $__errorArgs = ['doctor_rating'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kualitas
                                Video/Audio</label>
                            <select name="video_quality"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih rating (opsional)</option>
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo e($i); ?>"><?php echo e($i); ?> -
                                        <?php echo e(['Buruk', 'Cukup', 'Baik', 'Sangat Baik', 'Luar Biasa'][$i - 1]); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kemudahan
                                Platform</label>
                            <select name="platform_rating"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih rating (opsional)</option>
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo e($i); ?>"><?php echo e($i); ?> -
                                        <?php echo e(['Buruk', 'Cukup', 'Baik', 'Sangat Baik', 'Luar Biasa'][$i - 1]); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ulasan
                            Anda</label>
                        <textarea name="feedback" rows="4"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Ceritakan pengalaman konsultasi Anda..."><?php echo e(old('feedback')); ?></textarea>
                    </div>

                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Apa yang
                                baik?</label>
                            <textarea name="positive_feedback" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Aspek positif..."><?php echo e(old('positive_feedback')); ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Apa yang
                                perlu diperbaiki?</label>
                            <textarea name="negative_feedback" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Area perbaikan..."><?php echo e(old('negative_feedback')); ?></textarea>
                        </div>
                    </div>

                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Saran</label>
                        <textarea name="suggestions" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Saran untuk perbaikan..."><?php echo e(old('suggestions')); ?></textarea>
                    </div>

                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="flex items-center">
                            <input type="checkbox" name="would_recommend" id="would_recommend" value="1"
                                <?php echo e(old('would_recommend', true) ? 'checked' : ''); ?>

                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="would_recommend" class="ml-2 block text-sm text-gray-700">
                                Apakah Anda merekomendasikan dokter ini?
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="needs_followup" id="needs_followup" value="1"
                                <?php echo e(old('needs_followup') ? 'checked' : ''); ?>

                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="needs_followup" class="ml-2 block text-sm text-gray-700">
                                Apakah Anda memerlukan konsultasi lanjutan?
                            </label>
                        </div>
                    </div>

                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Lanjutan
                            (jika diperlukan)</label>
                        <textarea name="followup_notes" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Catatan tambahan untuk tindak lanjut..."><?php echo e(old('followup_notes')); ?></textarea>
                    </div>

                    
                    <div class="flex items-center justify-end gap-3">
                        <a href="<?php echo e(route('healthcare.telemedicine.consultations')); ?>"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Lewati Feedback
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                            <i class="fas fa-paper-plane mr-2"></i>Kirim Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            const stars = document.querySelectorAll('.star');
            const ratingValue = document.getElementById('rating-value');
            const ratingText = document.getElementById('rating-text');
            const ratingLabels = ['Buruk', 'Cukup', 'Baik', 'Sangat Baik', 'Luar Biasa'];

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const value = parseInt(this.dataset.value);
                    ratingValue.value = value;
                    stars.forEach((s, index) => {
                        s.classList.toggle('text-yellow-400', index < value);
                        s.classList.toggle('text-gray-300', index >= value);
                    });
                    ratingText.textContent = ratingLabels[value - 1];
                });

                star.addEventListener('mouseenter', function() {
                    const value = parseInt(this.dataset.value);
                    stars.forEach((s, index) => {
                        s.classList.toggle('text-yellow-300', index < value);
                    });
                });

                star.addEventListener('mouseleave', function() {
                    const currentValue = parseInt(ratingValue.value) || 0;
                    stars.forEach((s, index) => {
                        s.classList.remove('text-yellow-300');
                        s.classList.toggle('text-yellow-400', index < currentValue);
                        s.classList.toggle('text-gray-300', index >= currentValue);
                    });
                });
            });
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\telemedicine\feedback.blade.php ENDPATH**/ ?>