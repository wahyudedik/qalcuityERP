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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Detail Konsultasi Telemedicine')); ?> -
        <?php echo e($telemedicine->consultation_number ?? ''); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.telemedicine.index')); ?>"
            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                class="fas fa-arrow-left mr-2"></i>Kembali</a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <?php if(isset($telemedicine) && $telemedicine->status === 'scheduled'): ?>
                <div
                    class="bg-green-600 text-white p-4 rounded-lg mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <p class="text-sm">Konsultasi ini dijadwalkan. Anda dapat bergabung dengan video call.</p>
                    <a href="<?php echo e(route('healthcare.telemedicine.video-room', $telemedicine)); ?>"
                        class="inline-flex items-center px-6 py-3 bg-white text-green-600 rounded-md font-semibold hover:bg-gray-100">
                        <i class="fas fa-video mr-2"></i>Gabung Video Call
                    </a>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-blue-600"></i>Informasi Konsultasi
                    </h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">No. Konsultasi</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">
                                <?php echo e($telemedicine->consultation_number ?? '-'); ?>

                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Pasien</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($telemedicine->patient?->full_name ?? ($telemedicine->patient?->name ?? 'N/A')); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Dokter</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($telemedicine->doctor?->name ?? 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tipe</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800"><?php echo e(ucfirst($telemedicine->consultation_type ?? ($telemedicine->platform ?? '-'))); ?></span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full
                                    <?php if($telemedicine->status === 'completed'): ?> bg-green-100 text-green-800
                                    <?php elseif($telemedicine->status === 'in_progress'): ?> bg-blue-100 text-blue-800
                                    <?php elseif($telemedicine->status === 'cancelled'): ?> bg-red-100 text-red-800
                                    <?php else: ?> bg-yellow-100 text-yellow-800 <?php endif; ?>"><?php echo e(ucfirst(str_replace('_', ' ', $telemedicine->status))); ?></span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-clock mr-2 text-purple-600"></i>Timeline</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Dijadwalkan</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($telemedicine->scheduled_time ? $telemedicine->scheduled_time->format('d/m/Y H:i') : '-'); ?>

                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Dimulai</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($telemedicine->actual_start_time ? $telemedicine->actual_start_time->format('d/m/Y H:i') : '-'); ?>

                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Selesai</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($telemedicine->actual_end_time ? $telemedicine->actual_end_time->format('d/m/Y H:i') : '-'); ?>

                            </dd>
                        </div>
                        <?php if($telemedicine->actual_duration): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Durasi</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <?php echo e($telemedicine->actual_duration); ?> menit</dd>
                            </div>
                        <?php endif; ?>
                        <?php if($telemedicine->payment_status): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status Pembayaran</dt>
                                <dd class="mt-1">
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full
                                        <?php if($telemedicine->payment_status === 'paid'): ?> bg-green-100 text-green-800
                                        <?php else: ?> bg-yellow-100 text-yellow-800 <?php endif; ?>"><?php echo e(ucfirst($telemedicine->payment_status)); ?></span>
                                </dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-notes-medical mr-2 text-red-600"></i>Informasi Klinis</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Keluhan Utama</dt>
                            <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                <?php echo e($telemedicine->chief_complaint ?? '-'); ?></dd>
                        </div>
                        <?php if($telemedicine->diagnosis): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Diagnosis</dt>
                                <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                    <?php echo e($telemedicine->diagnosis); ?></dd>
                            </div>
                        <?php endif; ?>
                        <?php if($telemedicine->treatment_plan): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Rencana Perawatan</dt>
                                <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                    <?php echo e($telemedicine->treatment_plan); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-prescription mr-2 text-green-600"></i>Resep & Catatan</h3>
                    <?php if($telemedicine->doctor_notes): ?>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Catatan Dokter</dt>
                                <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                    <?php echo e($telemedicine->doctor_notes); ?></dd>
                            </div>
                        </dl>
                    <?php endif; ?>
                    <?php if($telemedicine->notes): ?>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <dt class="text-sm font-medium text-gray-500">Catatan Tambahan</dt>
                            <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                <?php echo e($telemedicine->notes); ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if(!$telemedicine->doctor_notes && !$telemedicine->notes): ?>
                        <p class="text-sm text-gray-500">Belum ada catatan</p>
                    <?php endif; ?>
                </div>
            </div>

            
            <?php if($telemedicine->feedback): ?>
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-star mr-2 text-yellow-500"></i>Feedback Pasien</h3>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-2xl text-yellow-400"><?php echo e($telemedicine->feedback->getRatingStars()); ?></span>
                        <span
                            class="text-sm text-gray-600">(<?php echo e($telemedicine->feedback->rating); ?>/5)</span>
                    </div>
                    <?php if($telemedicine->feedback->feedback): ?>
                        <p class="text-sm text-gray-700"><?php echo e($telemedicine->feedback->feedback); ?></p>
                    <?php endif; ?>
                </div>
            <?php elseif($telemedicine->status === 'completed'): ?>
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200 mb-6 text-center">
                    <p class="text-sm text-gray-500 mb-3">Belum ada feedback untuk konsultasi ini</p>
                    <a href="<?php echo e(route('healthcare.telemedicine.feedback.show', $telemedicine)); ?>"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fas fa-star mr-2"></i>Berikan Feedback
                    </a>
                </div>
            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\telemedicine\show.blade.php ENDPATH**/ ?>